<?php
require_once __DIR__ . '/../../config/database.php';

/**
 * Modelo de clientes: operaciones básicas CRUD.
 */
class Cliente {
    private $conn;
    private $table = "clientes";

    public function __construct() {
        $database = new Database();
        $this->conn = $database->connect();
    }

    /**
     * Devuelve todos los clientes para el listado.
     */
    public function getAll() {
        $sql = "SELECT * FROM " . $this->table;
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function count(): int {
        $sql = "SELECT COUNT(*) FROM " . $this->table;
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return (int) $stmt->fetchColumn();
    }

    /**
     * Inserta un cliente nuevo.
     */
    public function create($data) {
        $sql = "INSERT INTO " . $this->table . " (nombre, telefono, email, direccion) 
                VALUES (:nombre, :telefono, :email, :direccion)";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([
            ':nombre'    => $data['nombre'],
            ':telefono'  => $data['telefono'],
            ':email'     => $data['email'],
            ':direccion' => $data['direccion']
        ]);
    }

    /**
     * Devuelve un cliente por su id.
     */
    public function getById($id) {
        $sql = "SELECT * FROM " . $this->table . " WHERE id = :id";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Actualiza datos de un cliente existente.
     */
    public function update($id, $data) {
        $sql = "UPDATE " . $this->table . " 
                SET nombre = :nombre,
                    telefono = :telefono,
                    email = :email,
                    direccion = :direccion
                WHERE id = :id";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([
            ':nombre' => $data['nombre'],
            ':telefono' => $data['telefono'],
            ':email' => $data['email'],
            ':direccion' => $data['direccion'],
            ':id' => $id
        ]);
    }

    /**
     * Elimina cliente por id.
     */
    public function delete($id) {
        $sql = "DELETE FROM " . $this->table . " WHERE id = :id";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([':id' => $id]);
    }
}
