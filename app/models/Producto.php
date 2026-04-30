<?php
require_once __DIR__ . '/../../config/database.php';

/**
 * Modelo de productos: catálogo y stock.
 * Nivel estudiante:
 * - CRUD básico sobre `productos`.
 * - El descuento/reposición de stock avanzado se hace desde `Pedido`.
 */
class Producto {
    private $conn;
    private $table = 'productos';

    public function __construct() {
        $database = new Database();
        $this->conn = $database->connect();
    }

    /**
     * Lista productos ordenados por nombre.
     * Ejemplo:
     * - getAll('ws_demo') -> [ ['id'=>1,'nombre'=>'Pan', ...], ... ]
     */
    public function getAll(string $workspaceKey = '') {
        $sql = 'SELECT * FROM ' . $this->table . ' WHERE (:ws = \'\' OR workspace_key = :ws) ORDER BY nombre ASC';
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':ws' => $workspaceKey]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function count(string $workspaceKey = ''): int {
        $sql = 'SELECT COUNT(*) FROM ' . $this->table . ' WHERE (:ws = \'\' OR workspace_key = :ws)';
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':ws' => $workspaceKey]);
        return (int) $stmt->fetchColumn();
    }

    /**
     * Inserta un producto nuevo.
     * Ejemplo de entrada:
     * - ['nombre'=>'Pan','descripcion'=>'Integral','precio'=>1.5,'stock'=>40]
     */
    public function create(array $data, string $workspaceKey = '') {
        $sql = 'INSERT INTO ' . $this->table . ' (nombre, descripcion, precio, stock, workspace_key)
                VALUES (:nombre, :descripcion, :precio, :stock, :workspace_key)';
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([
            ':nombre' => $data['nombre'],
            ':descripcion' => $data['descripcion'] ?? '',
            ':precio' => $data['precio'],
            ':stock' => $data['stock'] ?? 0,
            ':workspace_key' => $workspaceKey !== '' ? $workspaceKey : null,
        ]);
    }

    /**
     * Busca un producto por id.
     * Ejemplo:
     * - getById(3, 'ws_demo') -> fila del producto o false si no existe.
     */
    public function getById($id, string $workspaceKey = '') {
        $sql = 'SELECT * FROM ' . $this->table . ' WHERE id = :id AND (:ws = \'\' OR workspace_key = :ws)';
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':id' => $id, ':ws' => $workspaceKey]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Actualiza los datos de un producto.
     * Nota: el precio y stock se guardan tal como llegan del controlador.
     */
    public function update($id, array $data, string $workspaceKey = '') {
        $sql = 'UPDATE ' . $this->table . '
                SET nombre = :nombre,
                    descripcion = :descripcion,
                    precio = :precio,
                    stock = :stock
                WHERE id = :id
                  AND (:ws = \'\' OR workspace_key = :ws)';
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([
            ':nombre' => $data['nombre'],
            ':descripcion' => $data['descripcion'] ?? '',
            ':precio' => $data['precio'],
            ':stock' => $data['stock'] ?? 0,
            ':id' => $id,
            ':ws' => $workspaceKey,
        ]);
    }

    /**
     * Borra un producto por id.
     * Consejo: normalmente conviene validar antes que no esté en uso.
     */
    public function delete($id, string $workspaceKey = '') {
        $sql = 'DELETE FROM ' . $this->table . ' WHERE id = :id AND (:ws = \'\' OR workspace_key = :ws)';
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([':id' => $id, ':ws' => $workspaceKey]);
    }
}
