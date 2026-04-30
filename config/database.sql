CREATE DATABASE IF NOT EXISTS mipedido;
USE mipedido;

-- =========================
-- BLOQUE 1: USUARIOS
-- =========================
CREATE TABLE usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    rol ENUM('admin', 'empleado') DEFAULT 'empleado',
    workspace_key CHAR(36) NULL DEFAULT NULL COMMENT 'Clave para aislar datos por cuenta en futuras fases',
    onboarding_version TINYINT UNSIGNED NOT NULL DEFAULT 0 COMMENT '0 = pendiente; >0 = tutorial completado',
    email_verificado TINYINT(1) NOT NULL DEFAULT 0 COMMENT '1 cuando el usuario valida su correo',
    plan_actual ENUM('basico', 'profesional', 'avanzado') NULL DEFAULT NULL COMMENT 'Plan demo seleccionado por el admin',
    plan_elegido_at DATETIME NULL DEFAULT NULL COMMENT 'Fecha de primera eleccion de plan',
    plan_cambiado_at DATETIME NULL DEFAULT NULL COMMENT 'Fecha de ultimo cambio de plan (max 1 por mes en demo)',
    preferencias_ui JSON NULL DEFAULT NULL,
    failed_login_count INT NOT NULL DEFAULT 0 COMMENT 'Intentos fallidos acumulados antes del próximo bloqueo',
    failed_login_stage TINYINT NOT NULL DEFAULT 0 COMMENT '0=próximo bloqueo de 15 min, 1=próximo bloqueo de 24 h',
    login_blocked_until DATETIME NULL DEFAULT NULL COMMENT 'Si tiene fecha futura, no puede iniciar sesión'
);

-- Catálogo de permisos finos (independiente del rol admin/empleado).
-- =========================
-- BLOQUE 2: PERMISOS
-- =========================
CREATE TABLE permisos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    clave VARCHAR(80) NOT NULL UNIQUE,
    nombre VARCHAR(120) NOT NULL,
    descripcion VARCHAR(255) NOT NULL DEFAULT '',
    orden_visual INT NOT NULL DEFAULT 0
);

-- Relación N:N entre usuarios y permisos marcados por el admin.
CREATE TABLE usuario_permisos (
    usuario_id INT NOT NULL,
    permiso_id INT NOT NULL,
    PRIMARY KEY (usuario_id, permiso_id),
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    FOREIGN KEY (permiso_id) REFERENCES permisos(id) ON DELETE CASCADE
);

-- =========================
-- BLOQUE 3: CLIENTES Y PRODUCTOS
-- =========================
CREATE TABLE clientes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    telefono VARCHAR(20),
    email VARCHAR(100),
    direccion TEXT,
    workspace_key CHAR(36) NULL DEFAULT NULL COMMENT 'Espacio de trabajo propietario'
);

CREATE TABLE productos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    descripcion TEXT,
    precio DECIMAL(10, 2) NOT NULL,
    stock INT NOT NULL DEFAULT 0,
    workspace_key CHAR(36) NULL DEFAULT NULL COMMENT 'Espacio de trabajo propietario'
);

-- =========================
-- BLOQUE 4: PEDIDOS Y DETALLE
-- =========================
CREATE TABLE pedidos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    cliente_id INT NOT NULL,
    fecha TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    estado ENUM('pendiente', 'en_proceso', 'enviado', 'entregado', 'cancelado', 'realizado') DEFAULT 'pendiente',
    metodo_pago ENUM('efectivo', 'tarjeta', 'transferencia') NOT NULL DEFAULT 'efectivo',
    fecha_realizado DATETIME NULL DEFAULT NULL,
    workspace_key CHAR(36) NULL DEFAULT NULL COMMENT 'Espacio de trabajo propietario',
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

-- =========================
-- BLOQUE 5: HISTORIAL Y TAREAS
-- =========================
CREATE TABLE pedido_historial (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pedido_id INT NOT NULL,
    usuario_id INT NULL,
    accion VARCHAR(80) NOT NULL,
    detalle TEXT NULL,
    fecha TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (pedido_id) REFERENCES pedidos(id) ON DELETE CASCADE,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE SET NULL
);

CREATE TABLE tareas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    titulo VARCHAR(200) NOT NULL,
    descripcion TEXT NULL,
    asignado_a INT NOT NULL,
    creado_por INT NOT NULL,
    estado ENUM('pendiente', 'en_curso', 'hecha') NOT NULL DEFAULT 'pendiente',
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_limite DATETIME NULL,
    pedido_id INT NULL,
    es_personal TINYINT(1) NOT NULL DEFAULT 0 COMMENT '1 = tarea propia del calendario; 0 = asignada por jefe/admin',
    workspace_key CHAR(36) NULL DEFAULT NULL COMMENT 'Espacio de trabajo propietario',
    FOREIGN KEY (asignado_a) REFERENCES usuarios(id) ON DELETE CASCADE,
    FOREIGN KEY (creado_por) REFERENCES usuarios(id) ON DELETE CASCADE,
    FOREIGN KEY (pedido_id) REFERENCES pedidos(id) ON DELETE SET NULL
);

-- Desarrollo: admin@test.com / admin123 (password_hash bcrypt, verificado con password_verify)
INSERT INTO usuarios (nombre, email, password, rol, workspace_key) VALUES (
    'Admin',
    'admin@test.com',
    '$2y$12$riDn8HmrlUD3J7SWkvT5weT604Db.5u/ICUgueHrBA66hRHBGuhGm',
    'admin',
    '00000000-0000-0000-0000-000000000001'
);

INSERT INTO permisos (clave, nombre, descripcion, orden_visual) VALUES
('dashboard_ver', 'Ver dashboard', 'Acceso al panel principal.', 10),
('clientes_gestionar', 'Gestionar clientes', 'Crear/editar/eliminar clientes.', 20),
('productos_gestionar', 'Gestionar productos', 'Crear/editar/eliminar productos.', 30),
('pedidos_gestionar', 'Gestionar pedidos', 'Crear pedidos y cambiar estados.', 40),
('ventas_ver', 'Ver ventas', 'Consultar módulo de ventas.', 50),
('facturacion_ver', 'Ver facturación', 'Consultar módulo de facturación.', 60),
('tareas_gestionar', 'Gestionar tareas', 'Crear o cambiar estado de tareas.', 70),
('calendario_ver', 'Ver calendario', 'Acceso al calendario mensual.', 80),
('usuarios_gestionar', 'Gestionar usuarios', 'Administrar cuentas de usuarios.', 90),
('configuracion_ver', 'Ver configuración', 'Acceso a la pantalla de configuración.', 100);

-- =========================
-- BLOQUE 6: USUARIO MYSQL DE APP
-- =========================
-- Usuario de aplicación (coherente con config/database.php: 127.0.0.1:3307).
-- Este bloque es para entorno limpio (instalación desde cero).
CREATE USER 'user_mipedido'@'localhost' IDENTIFIED BY '12345';
CREATE USER 'user_mipedido'@'127.0.0.1' IDENTIFIED BY '12345';
CREATE USER 'user_mipedido'@'%' IDENTIFIED BY '12345';
GRANT SELECT, INSERT, UPDATE, DELETE ON mipedido.* TO 'user_mipedido'@'localhost';
GRANT SELECT, INSERT, UPDATE, DELETE ON mipedido.* TO 'user_mipedido'@'127.0.0.1';
GRANT SELECT, INSERT, UPDATE, DELETE ON mipedido.* TO 'user_mipedido'@'%';
FLUSH PRIVILEGES;