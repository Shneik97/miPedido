-- Fase 5: tareas personales en calendario (empleado y admin). Ejecutar una vez si la base ya existía.
USE mipedido;

-- Versión idempotente: si la columna ya existe, no falla.
SET @c_es_personal := (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'tareas'
      AND COLUMN_NAME = 'es_personal'
);
SET @s_es_personal := IF(
    @c_es_personal = 0,
    "ALTER TABLE tareas ADD COLUMN es_personal TINYINT(1) NOT NULL DEFAULT 0 COMMENT '1 = creada por el usuario para si mismo; 0 = asignada por otro (p.ej. jefe)' AFTER pedido_id",
    "SELECT 'tareas.es_personal ya existe' AS info"
);
PREPARE st_es_personal FROM @s_es_personal;
EXECUTE st_es_personal;
DEALLOCATE PREPARE st_es_personal;
