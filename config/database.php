<?php

/**
 * Credenciales: en producción usar variables de entorno (ver docs/DESPLIEGUE.md).
 *
 * Desarrollo: por defecto `127.0.0.1` (TCP). En MySQL, `usuario@localhost` y
 * `usuario@127.0.0.1` son cuentas distintas; usa config/fix_mysql_app_user.sql
 * si ves «Access denied» (1045).
 */
class Database {
    private $host;
    private $port;
    private $dbname;
    private $user;
    private $password;
    public $conn;

    public function __construct() {
        $this->host = getenv('DB_HOST') !== false && getenv('DB_HOST') !== '' ? getenv('DB_HOST') : 'localhost';
        $this->port = getenv('DB_PORT') !== false && getenv('DB_PORT') !== '' ? getenv('DB_PORT') : '3307';
        $this->dbname = getenv('DB_NAME') !== false && getenv('DB_NAME') !== '' ? getenv('DB_NAME') : 'mipedido';
        $this->user = getenv('DB_USER') !== false && getenv('DB_USER') !== '' ? getenv('DB_USER') : 'user_mipedido';
        $this->password = getenv('DB_PASSWORD') !== false && getenv('DB_PASSWORD') !== '' ? getenv('DB_PASSWORD') : '12345';
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
