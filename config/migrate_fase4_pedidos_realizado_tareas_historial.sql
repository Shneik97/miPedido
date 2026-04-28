-- Fase 4: ejecutar UNA VEZ sobre una base mipedido ya existente (Docker con volumen antiguo, etc.).
-- Instalaciones nuevas desde cero: ya incluido en config/database.sql
USE mipedido;

-- Pedido cerrado confirmado + trazabilidad de fechas
ALTER TABLE pedidos
    MODIFY COLUMN estado ENUM(
        'pendiente', 'en_proceso', 'enviado', 'entregado', 'cancelado', 'realizado'
    ) NOT NULL DEFAULT 'pendiente',
    ADD COLUMN fecha_realizado DATETIME NULL DEFAULT NULL AFTER metodo_pago;

-- Auditoría de acciones sobre pedidos (cambios de estado, alta, etc.)
CREATE TABLE IF NOT EXISTS pedido_historial (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pedido_id INT NOT NULL,
    usuario_id INT NULL,
    accion VARCHAR(80) NOT NULL,
    detalle TEXT NULL,
    fecha TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (pedido_id) REFERENCES pedidos(id) ON DELETE CASCADE,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE SET NULL
);

-- Tareas asignadas por admin a empleados (opcionalmente ligadas a un pedido)
CREATE TABLE IF NOT EXISTS tareas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    titulo VARCHAR(200) NOT NULL,
    descripcion TEXT NULL,
    asignado_a INT NOT NULL,
    creado_por INT NOT NULL,
    estado ENUM('pendiente', 'en_curso', 'hecha') NOT NULL DEFAULT 'pendiente',
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_limite DATETIME NULL,
    pedido_id INT NULL,
    FOREIGN KEY (asignado_a) REFERENCES usuarios(id) ON DELETE CASCADE,
    FOREIGN KEY (creado_por) REFERENCES usuarios(id) ON DELETE CASCADE,
    FOREIGN KEY (pedido_id) REFERENCES pedidos(id) ON DELETE SET NULL
);
