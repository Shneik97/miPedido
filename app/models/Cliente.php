<?php
require_once __DIR__ . '/../../config/database.php';

/**
 * Modelo de clientes: operaciones básicas CRUD.
 * Nivel estudiante:
 * - Aquí solo hablamos con la tabla `clientes`.
 * - El controlador valida formularios y decide a dónde redirigir.
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
     * Ejemplo:
     * - getAll('ws_demo') -> [ ['id'=>1,'nombre'=>'Ana', ...], ... ]
     */
    public function getAll(string $workspaceKey = '') {
        $sql = "SELECT * FROM " . $this->table . " WHERE (:ws = '' OR workspace_key = :ws) ORDER BY nombre ASC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':ws' => $workspaceKey]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function count(string $workspaceKey = ''): int {
        $sql = "SELECT COUNT(*) FROM " . $this->table . " WHERE (:ws = '' OR workspace_key = :ws)";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':ws' => $workspaceKey]);
        return (int) $stmt->fetchColumn();
    }

    /**
     * Inserta un cliente nuevo.
     * Ejemplo de entrada:
     * - ['nombre'=>'Ana','telefono'=>'612...','email'=>'a@x.com','direccion'=>'Calle 1']
     */
    public function create($data, string $workspaceKey = '') {
        $sql = "INSERT INTO " . $this->table . " (nombre, telefono, email, direccion, workspace_key) 
                VALUES (:nombre, :telefono, :email, :direccion, :workspace_key)";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([
            ':nombre'    => $data['nombre'],
            ':telefono'  => $data['telefono'],
            ':email'     => $data['email'],
            ':direccion' => $data['direccion'],
            ':workspace_key' => $workspaceKey !== '' ? $workspaceKey : null,
        ]);
    }

    /**
     * Devuelve un cliente por su id.
     * Ejemplo:
     * - getById(7, 'ws_demo') -> ['id'=>7, 'nombre'=>'Ana', ...] o false/null si no existe.
     */
    public function getById($id, string $workspaceKey = '') {
        $sql = "SELECT * FROM " . $this->table . " WHERE id = :id AND (:ws = '' OR workspace_key = :ws)";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':id' => $id, ':ws' => $workspaceKey]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Actualiza datos de un cliente existente.
     * Nota estudiante: solo actualiza columnas de perfil del cliente, no toca pedidos.
     */
    public function update($id, $data, string $workspaceKey = '') {
        $sql = "UPDATE " . $this->table . " 
                SET nombre = :nombre,
                    telefono = :telefono,
                    email = :email,
                    direccion = :direccion
                WHERE id = :id
                  AND (:ws = '' OR workspace_key = :ws)";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([
            ':nombre' => $data['nombre'],
            ':telefono' => $data['telefono'],
            ':email' => $data['email'],
            ':direccion' => $data['direccion'],
            ':id' => $id,
            ':ws' => $workspaceKey,
        ]);
    }

    /**
     * Elimina cliente por id.
     * Importante: el controlador es quien confirma permisos y CSRF antes de llegar aquí.
     */
    public function delete($id, string $workspaceKey = '') {
        $sql = "DELETE FROM " . $this->table . " WHERE id = :id AND (:ws = '' OR workspace_key = :ws)";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([':id' => $id, ':ws' => $workspaceKey]);
    }
}
