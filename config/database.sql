CREATE DATABASE IF NOT EXISTS mipedido;
USE mipedido;

CREATE TABLE usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    rol ENUM('admin', 'empleado') DEFAULT 'empleado'
);

CREATE TABLE clientes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    telefono VARCHAR(20),
    email VARCHAR(100),
    direccion TEXT
);

CREATE TABLE productos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    descripcion TEXT,
    precio DECIMAL(10, 2) NOT NULL,
    stock INT NOT NULL DEFAULT 0
);

CREATE TABLE pedidos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    cliente_id INT NOT NULL,
    fecha TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    estado ENUM('pendiente', 'en_proceso', 'enviado', 'entregado', 'cancelado') DEFAULT 'pendiente',
    metodo_pago ENUM('efectivo', 'tarjeta', 'transferencia') NOT NULL DEFAULT 'efectivo',
    FOREIGN KEY (cliente_id) REFERENCES clientes(id) ON DELETE CASCADE
);

CREATE TABLE detalle_pedidos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pedido_id INT NOT NULL,
    producto_id INT NOT NULL,
    cantidad INT NOT NULL,
    precio_unitario DECIMAL(10, 2) NOT NULL,
    FOREIGN KEY (pedido_id) REFERENCES pedidos(id) ON DELETE CASCADE,
    FOREIGN KEY (producto_id) REFERENCES productos(id) ON DELETE CASCADE
);

-- Desarrollo: admin@test.com / admin123 (password_hash bcrypt, verificado con password_verify)
INSERT INTO usuarios (nombre, email, password, rol) VALUES (
    'Admin',
    'admin@test.com',
    '$2y$12$riDn8HmrlUD3J7SWkvT5weT604Db.5u/ICUgueHrBA66hRHBGuhGm',
    'admin'
);

-- Usuario de aplicación (coherente con config/database.php: 127.0.0.1:3307).
-- En Docker solo se ejecuta con volumen nuevo; entornos ya creados: fix_mysql_app_user.sql
CREATE USER 'user_mipedido'@'localhost' IDENTIFIED BY '12345';
CREATE USER 'user_mipedido'@'127.0.0.1' IDENTIFIED BY '12345';
CREATE USER 'user_mipedido'@'%' IDENTIFIED BY '12345';
GRANT SELECT, INSERT, UPDATE, DELETE ON mipedido.* TO 'user_mipedido'@'localhost';
GRANT SELECT, INSERT, UPDATE, DELETE ON mipedido.* TO 'user_mipedido'@'127.0.0.1';
GRANT SELECT, INSERT, UPDATE, DELETE ON mipedido.* TO 'user_mipedido'@'%';
FLUSH PRIVILEGES;