<?php

/**
 * Credenciales: en producción usar variables de entorno (ver docs/DESPLIEGUE.md).
 *
 * Desarrollo: por defecto `127.0.0.1` (TCP). Si ves «Access denied» (1045),
 * revisa usuario/clave en este archivo y vuelve a crear la base con
 * `config/database.sql` para un entorno limpio.
 */
class Database {
    private $host;
    private $port;
    private $dbname;
    private $user;
    private $password;
    public $conn;

    /**
     * Lee una variable de entorno y, si no existe o está vacía, usa un valor por defecto.
     * Ejemplo: envOrDefault('DB_HOST', 'localhost')
     */
    private function envOrDefault(string $key, string $default): string {
        $value = getenv($key);
        if ($value === false || $value === '') {
            return $default;
        }
        return $value;
    }

    public function __construct() {
        // Config por entorno (si existe) o por defecto para desarrollo local.
        $this->host = $this->envOrDefault('DB_HOST', 'localhost');
        $this->port = $this->envOrDefault('DB_PORT', '3307');
        $this->dbname = $this->envOrDefault('DB_NAME', 'mipedido');
        $this->user = $this->envOrDefault('DB_USER', 'user_mipedido');
        $this->password = $this->envOrDefault('DB_PASSWORD', '12345');
    }

    public function connect() {
        $this->conn = null;

        try {
            $this->conn = new PDO(
                'mysql:host=' . $this->host . ';port=' . $this->port . ';dbname=' . $this->dbname,
                $this->user,
                $this->password
            );
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            error_log('miPedido DB: ' . $e->getMessage());
        }

        return $this->conn;
    }
}
