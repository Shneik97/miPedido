<?php
require_once __DIR__ . '/../../config/database.php';

class Pedido {
    private $conn;
    private $table = 'pedidos';

    public function __construct() {
        $database = new Database();
        $this->conn = $database->connect();
    }

    public function countPendientes(): int {
        $sql = 'SELECT COUNT(*) FROM ' . $this->table . " WHERE estado = 'pendiente'";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return (int) $stmt->fetchColumn();
    }

    /**
     * Suma de importe (líneas de detalle) por mes, últimos $meses meses (incluye el actual).
     * Meses sin ventas devuelven 0.
     */
    public function getVentasTotalesPorMes(int $meses = 6): array {
        if ($meses < 1) {
            return ['labels' => [], 'data' => []];
        }
        $mesesCortos = [
            '01' => 'Ene', '02' => 'Feb', '03' => 'Mar', '04' => 'Abr',
            '05' => 'May', '06' => 'Jun', '07' => 'Jul', '08' => 'Ago',
            '09' => 'Sep', '10' => 'Oct', '11' => 'Nov', '12' => 'Dic',
        ];
        $first = new DateTime('first day of this month');
        $keys = [];
        $labels = [];
        $totals = [];
        for ($i = $meses - 1; $i >= 0; $i--) {
            $d = (clone $first)->modify("-{$i} months");
            $ym = $d->format('Y-m');
            $keys[] = $ym;
            $mnum = $d->format('m');
            $labels[] = ($mesesCortos[$mnum] ?? $mnum) . ' ' . $d->format('Y');
            $totals[$ym] = 0.0;
        }
        $desde = (clone $first)->modify('-' . ($meses - 1) . ' months')->format('Y-m-01 00:00:00');
        $sql = 'SELECT DATE_FORMAT(p.fecha, \'%Y-%m\') AS ym,
                       COALESCE(SUM(d.cantidad * d.precio_unitario), 0) AS total
                FROM ' . $this->table . ' p
                INNER JOIN detalle_pedidos d ON d.pedido_id = p.id
                WHERE p.fecha >= :desde
                GROUP BY ym';
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':desde' => $desde]);
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $ym = (string) $row['ym'];
            if (array_key_exists($ym, $totals)) {
                $totals[$ym] = (float) $row['total'];
            }
        }
        $data = [];
        foreach ($keys as $ym) {
            $data[] = round($totals[$ym], 2);
        }
        return ['labels' => $labels, 'data' => $data];
    }

    /**
     * Etiquetas y conteos por estado solo para tramos con COUNT > 0 (gráfico circular).
     */
    public function getDistribucionEstadosGrafico(): array {
        $orden = ['pendiente', 'en_proceso', 'enviado', 'entregado', 'cancelado'];
        $etiquetas = [
            'pendiente' => 'Pendiente',
            'en_proceso' => 'En proceso',
            'enviado' => 'Enviado',
            'entregado' => 'Entregado',
            'cancelado' => 'Cancelado',
        ];
        $sql = 'SELECT estado, COUNT(*) AS n FROM ' . $this->table . ' GROUP BY estado';
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        $porEstado = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $porEstado[(string) $row['estado']] = (int) $row['n'];
        }
        $labels = [];
        $data = [];
        foreach ($orden as $estado) {
            $n = $porEstado[$estado] ?? 0;
            if ($n > 0) {
                $labels[] = $etiquetas[$estado];
                $data[] = $n;
            }
        }
        return ['labels' => $labels, 'data' => $data];
    }

    /**
     * Listado con cliente, total calculado y método de pago.
     */
    public function getAllResumen(): array {
        $sql = 'SELECT p.id, p.cliente_id, p.fecha, p.estado, p.metodo_pago,
                       c.nombre AS cliente_nombre,
                       (SELECT COALESCE(SUM(d.cantidad * d.precio_unitario), 0)
                        FROM detalle_pedidos d WHERE d.pedido_id = p.id) AS total
                FROM ' . $this->table . ' p
                INNER JOIN clientes c ON c.id = p.cliente_id
                ORDER BY p.fecha DESC, p.id DESC';
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Crea pedido con una línea de detalle y descuenta stock.
     */
    public function createWithDetalle(int $clienteId, int $productoId, int $cantidad, string $metodoPago, float $precioUnitario): int {
        if ($cantidad < 1) {
            throw new InvalidArgumentException('La cantidad debe ser al menos 1.');
        }

        $this->conn->beginTransaction();
        try {
            $sqlP = 'INSERT INTO ' . $this->table . ' (cliente_id, estado, metodo_pago)
                     VALUES (:cliente_id, \'pendiente\', :metodo_pago)';
            $st = $this->conn->prepare($sqlP);
            $st->execute([
                ':cliente_id' => $clienteId,
                ':metodo_pago' => $metodoPago,
            ]);
            $pedidoId = (int) $this->conn->lastInsertId();

            $sqlD = 'INSERT INTO detalle_pedidos (pedido_id, producto_id, cantidad, precio_unitario)
                     VALUES (:pedido_id, :producto_id, :cantidad, :precio_unitario)';
            $stD = $this->conn->prepare($sqlD);
            $stD->execute([
                ':pedido_id' => $pedidoId,
                ':producto_id' => $productoId,
                ':cantidad' => $cantidad,
                ':precio_unitario' => $precioUnitario,
            ]);

            $sqlStock = 'UPDATE productos SET stock = stock - :q WHERE id = :id AND stock >= :q2';
            $stS = $this->conn->prepare($sqlStock);
            $stS->bindValue(':q', $cantidad, PDO::PARAM_INT);
            $stS->bindValue(':id', $productoId, PDO::PARAM_INT);
            $stS->bindValue(':q2', $cantidad, PDO::PARAM_INT);
            $stS->execute();
            if ($stS->rowCount() === 0) {
                throw new RuntimeException('Stock insuficiente para este producto.');
            }

            $this->conn->commit();
            return $pedidoId;
        } catch (Throwable $e) {
            $this->conn->rollBack();
            throw $e;
        }
    }

    public function getById(int $id): ?array {
        $sql = 'SELECT p.*, c.nombre AS cliente_nombre, c.email AS cliente_email, c.telefono AS cliente_telefono, c.direccion AS cliente_direccion
                FROM ' . $this->table . ' p
                INNER JOIN clientes c ON c.id = p.cliente_id
                WHERE p.id = :id';
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public function getLineasFactura(int $pedidoId): array {
        $sql = 'SELECT d.cantidad, d.precio_unitario, pr.nombre AS producto_nombre
                FROM detalle_pedidos d
                INNER JOIN productos pr ON pr.id = d.producto_id
                WHERE d.pedido_id = :pid';
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':pid' => $pedidoId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function delete(int $id): bool {
        $sql = 'DELETE FROM ' . $this->table . ' WHERE id = :id';
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([':id' => $id]);
    }
}
