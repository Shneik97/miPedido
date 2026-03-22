<?php
class Database {
    private $host = "127.0.0.1";
    private $port = "3307";
    private $dbname = "mipedido";
    private $user = "root";
    private $password = "root";
    public $conn;

    public function connect() {
        $this->conn = null;

        try {
            // Fíjate que usamos 127.0.0.1 para evitar líos con localhost en Windows
            $this->conn = new PDO(
                "mysql:host=" . $this->host . ";port=" . $this->port . ";dbname=" . $this->dbname, 
                $this->user, 
                $this->password
            );
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            echo "Error de conexión: " . $e->getMessage();
        }

        return $this->conn;
    }
}