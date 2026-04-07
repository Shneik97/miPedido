<?php
require_once __DIR__ . '/../../config/database.php';

class Producto {
    private $conn;
    private $table = 'productos';

    public function __construct() {
        $database = new Database();
        $this->conn = $database->connect();
    }

    public function getAll() {
        $sql = 'SELECT * FROM ' . $this->table . ' ORDER BY nombre ASC';
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function count(): int {
        $sql = 'SELECT COUNT(*) FROM ' . $this->table;
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return (int) $stmt->fetchColumn();
    }

    public function create(array $data) {
        $sql = 'INSERT INTO ' . $this->table . ' (nombre, descripcion, precio, stock)
                VALUES (:nombre, :descripcion, :precio, :stock)';
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([
            ':nombre' => $data['nombre'],
            ':descripcion' => $data['descripcion'] ?? '',
            ':precio' => $data['precio'],
            ':stock' => $data['stock'] ?? 0,
        ]);
    }

    public function getById($id) {
        $sql = 'SELECT * FROM ' . $this->table . ' WHERE id = :id';
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function update($id, array $data) {
        $sql = 'UPDATE ' . $this->table . '
                SET nombre = :nombre,
                    descripcion = :descripcion,
                    precio = :precio,
                    stock = :stock
                WHERE id = :id';
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([
            ':nombre' => $data['nombre'],
            ':descripcion' => $data['descripcion'] ?? '',
            ':precio' => $data['precio'],
            ':stock' => $data['stock'] ?? 0,
            ':id' => $id,
        ]);
    }

    public function delete($id) {
        $sql = 'DELETE FROM ' . $this->table . ' WHERE id = :id';
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([':id' => $id]);
    }
}
