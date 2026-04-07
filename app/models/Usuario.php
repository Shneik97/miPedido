<?php
require_once __DIR__ . '/../../config/database.php';

class Usuario {
    private $conn;
    private $table = 'usuarios';

    public function __construct() {
        $database = new Database();
        $this->conn = $database->connect();
    }

    public function getAll(): array {
        $sql = 'SELECT id, nombre, email, rol FROM ' . $this->table . ' ORDER BY nombre ASC';
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getById(int $id): ?array {
        $sql = 'SELECT id, nombre, email, rol FROM ' . $this->table . ' WHERE id = :id';
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public function findByEmail(string $email): ?array {
        $sql = 'SELECT * FROM ' . $this->table . ' WHERE email = :email LIMIT 1';
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':email' => $email]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public function emailExists(string $email, ?int $exceptId = null): bool {
        $sql = 'SELECT COUNT(*) FROM ' . $this->table . ' WHERE email = :email';
        $params = [':email' => $email];
        if ($exceptId !== null) {
            $sql .= ' AND id != :id';
            $params[':id'] = $exceptId;
        }
        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);
        return (int) $stmt->fetchColumn() > 0;
    }

    public function countAdmins(): int {
        $sql = 'SELECT COUNT(*) FROM ' . $this->table . " WHERE rol = 'admin'";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return (int) $stmt->fetchColumn();
    }

    public function create(string $nombre, string $email, string $passwordPlain, string $rol): int {
        $hash = password_hash($passwordPlain, PASSWORD_DEFAULT);
        $sql = 'INSERT INTO ' . $this->table . ' (nombre, email, password, rol) VALUES (:nombre, :email, :password, :rol)';
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([
            ':nombre' => $nombre,
            ':email' => $email,
            ':password' => $hash,
            ':rol' => $rol,
        ]);
        return (int) $this->conn->lastInsertId();
    }

    public function update(int $id, string $nombre, string $email, string $rol, ?string $newPasswordPlain = null): void {
        if ($newPasswordPlain !== null && $newPasswordPlain !== '') {
            $hash = password_hash($newPasswordPlain, PASSWORD_DEFAULT);
            $sql = 'UPDATE ' . $this->table . ' SET nombre = :nombre, email = :email, rol = :rol, password = :password WHERE id = :id';
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([
                ':nombre' => $nombre,
                ':email' => $email,
                ':rol' => $rol,
                ':password' => $hash,
                ':id' => $id,
            ]);
            return;
        }
        $sql = 'UPDATE ' . $this->table . ' SET nombre = :nombre, email = :email, rol = :rol WHERE id = :id';
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([
            ':nombre' => $nombre,
            ':email' => $email,
            ':rol' => $rol,
            ':id' => $id,
        ]);
    }

    public function delete(int $id): bool {
        $sql = 'DELETE FROM ' . $this->table . ' WHERE id = :id';
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([':id' => $id]);
    }
}
