-- Fase 9: onboarding para admin (una sola vez) + base para entorno limpio de cuentas nuevas.
-- Ejecutar UNA vez si la base de datos ya existía antes de esta fase.
-- Compatible con versiones donde "ADD COLUMN IF NOT EXISTS" no está disponible.

USE mipedido;

SET @exists_workspace_key := (
    SELECT COUNT(*)
    FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'usuarios'
      AND COLUMN_NAME = 'workspace_key'
);
SET @sql_workspace_key := IF(
    @exists_workspace_key = 0,
    "ALTER TABLE usuarios ADD COLUMN workspace_key CHAR(36) NULL DEFAULT NULL COMMENT 'Clave para aislar datos por cuenta en futuras fases'",
    "SELECT 'workspace_key ya existe' AS info"
);
PREPARE stmt_workspace_key FROM @sql_workspace_key;
EXECUTE stmt_workspace_key;
DEALLOCATE PREPARE stmt_workspace_key;

SET @exists_onboarding_version := (
    SELECT COUNT(*)
    FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'usuarios'
      AND COLUMN_NAME = 'onboarding_version'
);
SET @sql_onboarding_version := IF(
    @exists_onboarding_version = 0,
    "ALTER TABLE usuarios ADD COLUMN onboarding_version TINYINT UNSIGNED NOT NULL DEFAULT 0 COMMENT '0 = pendiente; >0 = tutorial completado'",
    "SELECT 'onboarding_version ya existe' AS info"
);
PREPARE stmt_onboarding_version FROM @sql_onboarding_version;
EXECUTE stmt_onboarding_version;
DEALLOCATE PREPARE stmt_onboarding_version;

-- Si necesitas forzar que admins actuales vuelvan a ver el tutorial:
-- UPDATE usuarios SET onboarding_version = 0 WHERE rol = 'admin';
