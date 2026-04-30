-- Fase 7: seguridad base (bloqueo login escalado, sesión/cookies y CSRF)
-- Ejecutar UNA vez si ya tenías la BD creada antes de esta fase.

USE mipedido;

-- Columnas de seguridad en modo idempotente (ejecutable varias veces sin romper).
SET @c_failed_count := (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'usuarios'
      AND COLUMN_NAME = 'failed_login_count'
);
SET @s_failed_count := IF(
    @c_failed_count = 0,
    "ALTER TABLE usuarios ADD COLUMN failed_login_count INT NOT NULL DEFAULT 0 COMMENT 'Intentos fallidos acumulados antes del proximo bloqueo'",
    "SELECT 'usuarios.failed_login_count ya existe' AS info"
);
PREPARE st_failed_count FROM @s_failed_count;
EXECUTE st_failed_count;
DEALLOCATE PREPARE st_failed_count;

SET @c_failed_stage := (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'usuarios'
      AND COLUMN_NAME = 'failed_login_stage'
);
SET @s_failed_stage := IF(
    @c_failed_stage = 0,
    "ALTER TABLE usuarios ADD COLUMN failed_login_stage TINYINT NOT NULL DEFAULT 0 COMMENT '0=proximo bloqueo 15 min, 1=proximo bloqueo 24 h'",
    "SELECT 'usuarios.failed_login_stage ya existe' AS info"
);
PREPARE st_failed_stage FROM @s_failed_stage;
EXECUTE st_failed_stage;
DEALLOCATE PREPARE st_failed_stage;

SET @c_blocked_until := (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'usuarios'
      AND COLUMN_NAME = 'login_blocked_until'
);
SET @s_blocked_until := IF(
    @c_blocked_until = 0,
    "ALTER TABLE usuarios ADD COLUMN login_blocked_until DATETIME NULL DEFAULT NULL COMMENT 'Si es futura, el login queda bloqueado'",
    "SELECT 'usuarios.login_blocked_until ya existe' AS info"
);
PREPARE st_blocked_until FROM @s_blocked_until;
EXECUTE st_blocked_until;
DEALLOCATE PREPARE st_blocked_until;

