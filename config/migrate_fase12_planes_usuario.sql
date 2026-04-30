USE mipedido;

-- Fase 12: plan simulado para admins (sin guardar datos de tarjeta completa).
SET @db := DATABASE();

SET @exists_plan_actual := (
    SELECT COUNT(*)
    FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = @db
      AND TABLE_NAME = 'usuarios'
      AND COLUMN_NAME = 'plan_actual'
);
SET @sql_plan_actual := IF(
    @exists_plan_actual = 0,
    "ALTER TABLE usuarios ADD COLUMN plan_actual ENUM('basico','profesional','avanzado') NULL DEFAULT NULL COMMENT 'Plan demo seleccionado por el admin'",
    "SELECT 'plan_actual ya existe' AS info"
);
PREPARE stmt_plan_actual FROM @sql_plan_actual;
EXECUTE stmt_plan_actual;
DEALLOCATE PREPARE stmt_plan_actual;

SET @exists_plan_elegido := (
    SELECT COUNT(*)
    FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = @db
      AND TABLE_NAME = 'usuarios'
      AND COLUMN_NAME = 'plan_elegido_at'
);
SET @sql_plan_elegido := IF(
    @exists_plan_elegido = 0,
    "ALTER TABLE usuarios ADD COLUMN plan_elegido_at DATETIME NULL DEFAULT NULL COMMENT 'Fecha de primera eleccion de plan'",
    "SELECT 'plan_elegido_at ya existe' AS info"
);
PREPARE stmt_plan_elegido FROM @sql_plan_elegido;
EXECUTE stmt_plan_elegido;
DEALLOCATE PREPARE stmt_plan_elegido;

SET @exists_plan_cambiado := (
    SELECT COUNT(*)
    FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = @db
      AND TABLE_NAME = 'usuarios'
      AND COLUMN_NAME = 'plan_cambiado_at'
);
SET @sql_plan_cambiado := IF(
    @exists_plan_cambiado = 0,
    "ALTER TABLE usuarios ADD COLUMN plan_cambiado_at DATETIME NULL DEFAULT NULL COMMENT 'Fecha ultimo cambio de plan'",
    "SELECT 'plan_cambiado_at ya existe' AS info"
);
PREPARE stmt_plan_cambiado FROM @sql_plan_cambiado;
EXECUTE stmt_plan_cambiado;
DEALLOCATE PREPARE stmt_plan_cambiado;
