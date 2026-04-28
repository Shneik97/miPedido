-- Fase 8: permisos finos por usuario (gestionados por admin)
-- Ejecutar UNA vez si la base de datos ya existía antes de esta fase.

USE mipedido;

CREATE TABLE IF NOT EXISTS permisos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    clave VARCHAR(80) NOT NULL UNIQUE,
    nombre VARCHAR(120) NOT NULL,
    descripcion VARCHAR(255) NOT NULL DEFAULT '',
    orden_visual INT NOT NULL DEFAULT 0
);

CREATE TABLE IF NOT EXISTS usuario_permisos (
    usuario_id INT NOT NULL,
    permiso_id INT NOT NULL,
    PRIMARY KEY (usuario_id, permiso_id),
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    FOREIGN KEY (permiso_id) REFERENCES permisos(id) ON DELETE CASCADE
);

INSERT IGNORE INTO permisos (clave, nombre, descripcion, orden_visual) VALUES
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

-- Conveniencia: a los admins actuales se les asignan todos los permisos.
INSERT IGNORE INTO usuario_permisos (usuario_id, permiso_id)
SELECT u.id, p.id
FROM usuarios u
CROSS JOIN permisos p
WHERE u.rol = 'admin';

