<?php
/**
 * Modelo de pedidos: consultas, alta con detalle/stock, cierre "realizado" e historial auditable.
 */
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
     * Suma de importe por mes en un bloque de tiempo.
     * - $meses: ancho del bloque (en este proyecto usamos 6).
     * - $offsetMeses: cuántos meses retroceder desde el mes actual (0, 6, 12...).
     * Meses sin ventas devuelven 0.
     */
    public function getVentasTotalesPorMes(int $meses = 6, int $offsetMeses = 0): array {
        if ($meses < 1) {
            return ['labels' => [], 'data' => []];
        }
        if ($offsetMeses < 0) {
            $offsetMeses = 0;
        }
        $mesesCortos = [
            '01' => 'Ene', '02' => 'Feb', '03' => 'Mar', '04' => 'Abr',
            '05' => 'May', '06' => 'Jun', '07' => 'Jul', '08' => 'Ago',
            '09' => 'Sep', '10' => 'Oct', '11' => 'Nov', '12' => 'Dic',
        ];
        // Mes final visible del bloque (si offset=6 y hoy es abril, termina en octubre anterior).
        $first = new DateTime('first day of this month');
        if ($offsetMeses > 0) {
            $first->modify('-' . $offsetMeses . ' months');
        }
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
        $hasta = (clone $first)->modify('+1 month')->format('Y-m-01 00:00:00');
        $sql = 'SELECT DATE_FORMAT(p.fecha, \'%Y-%m\') AS ym,
                       COALESCE(SUM(d.cantidad * d.precio_unitario), 0) AS total
                FROM ' . $this->table . ' p
                INNER JOIN detalle_pedidos d ON d.pedido_id = p.id
                WHERE p.fecha >= :desde
                  AND p.fecha < :hasta
                GROUP BY ym';
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':desde' => $desde, ':hasta' => $hasta]);
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
     * Top productos más vendidos (por unidades) dentro del mismo bloque temporal del gráfico.
     */
    public function getProductosMasVendidos(int $limite = 5, int $meses = 6, int $offsetMeses = 0): array
    {
        if ($limite < 1) {
            $limite = 5;
        }
        if ($meses < 1) {
            $meses = 6;
        }
        if ($offsetMeses < 0) {
            $offsetMeses = 0;
        }

        $first = new DateTime('first day of this month');
        if ($offsetMeses > 0) {
            $first->modify('-' . $offsetMeses . ' months');
        }
        $desde = (clone $first)->modify('-' . ($meses - 1) . ' months')->format('Y-m-01 00:00:00');
        $hasta = (clone $first)->modify('+1 month')->format('Y-m-01 00:00:00');

        $sql = 'SELECT pr.id AS producto_id,
                       pr.nombre AS producto_nombre,
                       SUM(d.cantidad) AS unidades_vendidas,
                       SUM(d.cantidad * d.precio_unitario) AS total_facturado
                FROM detalle_pedidos d
                INNER JOIN ' . $this->table . ' p ON p.id = d.pedido_id
                INNER JOIN productos pr ON pr.id = d.producto_id
                WHERE p.fecha >= :desde
                  AND p.fecha < :hasta
                GROUP BY pr.id, pr.nombre
                ORDER BY unidades_vendidas DESC, total_facturado DESC
                LIMIT ' . (int) $limite;

        $stmt = $this->conn->prepare($sql);
        $stmt->execute([
            ':desde' => $desde,
            ':hasta' => $hasta,
        ]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Etiquetas y conteos por estado solo para tramos con COUNT > 0 (gráfico circular).
     * Incluye el estado terminal "realizado" (pedido confirmado/cerrado).
     */
    public function getDistribucionEstadosGrafico(): array {
        $orden = ['pendiente', 'en_proceso', 'enviado', 'entregado', 'cancelado', 'realizado'];
        $etiquetas = [
            'pendiente' => 'Pendiente',
            'en_proceso' => 'En proceso',
            'enviado' => 'Enviado',
            'entregado' => 'Entregado',
            'cancelado' => 'Cancelado',
            'realizado' => 'Realizado',
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
     * Listado con cliente, total, método de pago, fecha de cierre y teléfono (para enlaces WhatsApp).
     */
    public function getAllResumen(): array {
        $sql = 'SELECT p.id, p.cliente_id, p.fecha, p.estado, p.metodo_pago, p.fecha_realizado,
                       c.nombre AS cliente_nombre, c.telefono AS cliente_telefono,
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
     * Si $creadoPorUsuarioId no es null, registra fila en pedido_historial (trazabilidad del alta).
     */
    public function createWithDetalle(int $clienteId, int $productoId, int $cantidad, string $metodoPago, float $precioUnitario, ?int $creadoPorUsuarioId = null): int {
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

            if ($creadoPorUsuarioId !== null) {
                $this->insertHistorialPedido($pedidoId, $creadoPorUsuarioId, 'pedido_creado', 'Pedido registrado en el sistema.');
            }

            $this->conn->commit();
            return $pedidoId;
        } catch (Throwable $e) {
            $this->conn->rollBack();
            throw $e;
        }
    }

    /**
     * Marca el pedido como realizado y fija fecha_realizado. No aplica si ya está cancelado o realizado.
     * Admin y empleado pueden invocar (la restricción de rol va en el controlador si se desea cambiar).
     */
    public function marcarRealizado(int $pedidoId, int $usuarioId): bool {
        $this->conn->beginTransaction();
        try {
            $sel = $this->conn->prepare('SELECT estado FROM ' . $this->table . ' WHERE id = :id FOR UPDATE');
            $sel->execute([':id' => $pedidoId]);
            $row = $sel->fetch(PDO::FETCH_ASSOC);
            if (!$row) {
                $this->conn->rollBack();
                return false;
            }
            $estadoAnterior = (string) $row['estado'];
            if ($estadoAnterior === 'cancelado' || $estadoAnterior === 'realizado') {
                $this->conn->rollBack();
                return false;
            }

            $upd = $this->conn->prepare(
                "UPDATE {$this->table} SET estado = 'realizado', fecha_realizado = NOW() WHERE id = :id"
            );
            $upd->execute([':id' => $pedidoId]);

            $this->insertHistorialPedido(
                $pedidoId,
                $usuarioId,
                'pedido_realizado',
                'Estado anterior: ' . $estadoAnterior
            );

            $this->conn->commit();
            return true;
        } catch (Throwable $e) {
            $this->conn->rollBack();
            throw $e;
        }
    }

    /**
     * Inserta un evento en el historial del pedido (fuera de transacción externa si ya está commiteado).
     */
    public function insertHistorialPedido(int $pedidoId, ?int $usuarioId, string $accion, ?string $detalle = null): void {
        $sql = 'INSERT INTO pedido_historial (pedido_id, usuario_id, accion, detalle) VALUES (:pid, :uid, :acc, :det)';
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([
            ':pid' => $pedidoId,
            ':uid' => $usuarioId,
            ':acc' => $accion,
            ':det' => $detalle,
        ]);
    }

    /**
     * Línea de tiempo de un pedido para la vista de historial.
     */
    public function getHistorialByPedidoId(int $pedidoId): array {
        $sql = 'SELECT h.id, h.pedido_id, h.usuario_id, h.accion, h.detalle, h.fecha,
                       u.nombre AS usuario_nombre
                FROM pedido_historial h
                LEFT JOIN usuarios u ON u.id = h.usuario_id
                WHERE h.pedido_id = :pid
                ORDER BY h.fecha ASC, h.id ASC';
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':pid' => $pedidoId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
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
        $sql = 'SELECT d.id, d.producto_id, d.cantidad, d.precio_unitario, pr.nombre AS producto_nombre
                FROM detalle_pedidos d
                INNER JOIN productos pr ON pr.id = d.producto_id
                WHERE d.pedido_id = :pid';
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':pid' => $pedidoId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Permite editar datos de factura solo si el pedido no está realizado.
     * Devuelve false cuando el pedido ya está cerrado o no existe.
     */
    public function updateFacturaEditable(int $pedidoId, string $metodoPago, array $lineasInput, ?int $usuarioId = null): bool
    {
        $allowed = ['efectivo', 'tarjeta', 'transferencia'];
        if (!in_array($metodoPago, $allowed, true)) {
            $metodoPago = 'efectivo';
        }

        $this->conn->beginTransaction();
        try {
            $sel = $this->conn->prepare('SELECT estado FROM ' . $this->table . ' WHERE id = :id FOR UPDATE');
            $sel->execute([':id' => $pedidoId]);
            $pedido = $sel->fetch(PDO::FETCH_ASSOC);
            if (!$pedido) {
                $this->conn->rollBack();
                return false;
            }
            if ((string) ($pedido['estado'] ?? '') === 'realizado') {
                $this->conn->rollBack();
                return false;
            }

            $updPedido = $this->conn->prepare('UPDATE ' . $this->table . ' SET metodo_pago = :metodo WHERE id = :id');
            $updPedido->execute([
                ':metodo' => $metodoPago,
                ':id' => $pedidoId,
            ]);

            $updLinea = $this->conn->prepare(
                'UPDATE detalle_pedidos
                 SET cantidad = :cantidad, precio_unitario = :precio
                 WHERE id = :linea_id AND pedido_id = :pedido_id'
            );

            foreach ($lineasInput as $lineaId => $data) {
                $cantidad = max(1, (int) ($data['cantidad'] ?? 1));
                $precio = (float) ($data['precio_unitario'] ?? 0);
                if ($precio < 0) {
                    $precio = 0;
                }
                $updLinea->execute([
                    ':cantidad' => $cantidad,
                    ':precio' => $precio,
                    ':linea_id' => (int) $lineaId,
                    ':pedido_id' => $pedidoId,
                ]);
            }

            if ($usuarioId !== null) {
                $this->insertHistorialPedido(
                    $pedidoId,
                    $usuarioId,
                    'factura_editada',
                    'Se actualizaron método de pago y/o líneas antes del cierre.'
                );
            }

            $this->conn->commit();
            return true;
        } catch (Throwable $e) {
            $this->conn->rollBack();
            throw $e;
        }
    }

    /**
     * Historial mensual de ventas (importe, pedidos y ticket medio) para los últimos N meses.
     */
    public function getResumenMensualVentasHistorico(int $meses = 36): array
    {
        if ($meses < 1) {
            $meses = 36;
        }
        $desde = (new DateTime('first day of this month'))
            ->modify('-' . ($meses - 1) . ' months')
            ->format('Y-m-01 00:00:00');

        $sql = 'SELECT DATE_FORMAT(p.fecha, \'%Y-%m\') AS ym,
                       COUNT(DISTINCT p.id) AS pedidos_count,
                       COALESCE(SUM(d.cantidad * d.precio_unitario), 0) AS total_ventas
                FROM ' . $this->table . ' p
                INNER JOIN detalle_pedidos d ON d.pedido_id = p.id
                WHERE p.fecha >= :desde
                GROUP BY ym
                ORDER BY ym DESC';
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':desde' => $desde]);

        $out = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $pedidos = (int) ($row['pedidos_count'] ?? 0);
            $total = (float) ($row['total_ventas'] ?? 0);
            $out[] = [
                'ym' => (string) $row['ym'],
                'pedidos_count' => $pedidos,
                'total_ventas' => round($total, 2),
                'ticket_medio' => $pedidos > 0 ? round($total / $pedidos, 2) : 0.0,
            ];
        }
        return $out;
    }

    /**
     * Detalle de pedidos vendidos en un mes concreto (YYYY-MM).
     */
    public function getVentasDetallePorMes(int $year, int $month): array
    {
        if ($year < 2000 || $year > 2100) {
            return [];
        }
        if ($month < 1 || $month > 12) {
            return [];
        }
        $inicio = sprintf('%04d-%02d-01 00:00:00', $year, $month);
        $inicioDt = new DateTime($inicio);
        $fin = (clone $inicioDt)->modify('+1 month')->format('Y-m-d H:i:s');

        $sql = 'SELECT p.id, p.fecha, p.estado, p.metodo_pago, p.fecha_realizado,
                       c.nombre AS cliente_nombre,
                       COALESCE(SUM(d.cantidad * d.precio_unitario), 0) AS total
                FROM ' . $this->table . ' p
                INNER JOIN clientes c ON c.id = p.cliente_id
                INNER JOIN detalle_pedidos d ON d.pedido_id = p.id
                WHERE p.fecha >= :inicio AND p.fecha < :fin
                GROUP BY p.id, p.fecha, p.estado, p.metodo_pago, p.fecha_realizado, c.nombre
                ORDER BY p.fecha DESC, p.id DESC';
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':inicio' => $inicio, ':fin' => $fin]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Historial de facturación usando pedidos como origen de factura.
     * estadoFactura: pendiente (pedido no realizado) o realizada (pedido realizado).
     */
    public function getFacturasHistorial(string $estadoFactura = 'todos', ?string $ym = null): array
    {
        $filtro = '';
        $params = [];
        if ($estadoFactura === 'realizada') {
            $filtro = " AND p.estado = 'realizado'";
        } elseif ($estadoFactura === 'pendiente') {
            $filtro = " AND p.estado != 'realizado'";
        }
        if ($ym !== null && preg_match('/^(\d{4})-(\d{2})$/', $ym, $m)) {
            $year = (int) $m[1];
            $month = (int) $m[2];
            if ($year >= 2000 && $year <= 2100 && $month >= 1 && $month <= 12) {
                $inicio = sprintf('%04d-%02d-01 00:00:00', $year, $month);
                $inicioDt = new DateTime($inicio);
                $fin = (clone $inicioDt)->modify('+1 month')->format('Y-m-d H:i:s');
                $filtro .= ' AND p.fecha >= :inicio_mes AND p.fecha < :fin_mes';
                $params[':inicio_mes'] = $inicio;
                $params[':fin_mes'] = $fin;
            }
        }

        $sql = 'SELECT p.id, p.fecha, p.estado, p.metodo_pago, p.fecha_realizado,
                       c.nombre AS cliente_nombre, c.email AS cliente_email,
                       COALESCE(SUM(d.cantidad * d.precio_unitario), 0) AS total
                FROM ' . $this->table . ' p
                INNER JOIN clientes c ON c.id = p.cliente_id
                INNER JOIN detalle_pedidos d ON d.pedido_id = p.id
                WHERE 1=1 ' . $filtro . '
                GROUP BY p.id, p.fecha, p.estado, p.metodo_pago, p.fecha_realizado, c.nombre, c.email
                ORDER BY p.fecha DESC, p.id DESC
                LIMIT 300';
        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Elimina pedido:
     * - Si está realizado, no permite borrado.
     * - Si no está realizado, repone stock y luego borra el pedido.
     */
    public function delete(int $id): bool {
        $this->conn->beginTransaction();
        try {
            $sel = $this->conn->prepare('SELECT estado FROM ' . $this->table . ' WHERE id = :id FOR UPDATE');
            $sel->execute([':id' => $id]);
            $pedido = $sel->fetch(PDO::FETCH_ASSOC);
            if (!$pedido) {
                $this->conn->rollBack();
                return false;
            }
            if ((string) ($pedido['estado'] ?? '') === 'realizado') {
                $this->conn->rollBack();
                return false;
            }

            $lineas = $this->conn->prepare('SELECT producto_id, cantidad FROM detalle_pedidos WHERE pedido_id = :id');
            $lineas->execute([':id' => $id]);
            $rows = $lineas->fetchAll(PDO::FETCH_ASSOC);

            $updStock = $this->conn->prepare('UPDATE productos SET stock = stock + :cantidad WHERE id = :producto_id');
            foreach ($rows as $row) {
                $updStock->execute([
                    ':cantidad' => (int) ($row['cantidad'] ?? 0),
                    ':producto_id' => (int) ($row['producto_id'] ?? 0),
                ]);
            }

            $del = $this->conn->prepare('DELETE FROM ' . $this->table . ' WHERE id = :id');
            $del->execute([':id' => $id]);
            if ($del->rowCount() < 1) {
                $this->conn->rollBack();
                return false;
            }

            $this->conn->commit();
            return true;
        } catch (Throwable $e) {
            $this->conn->rollBack();
            throw $e;
        }
    }
}
