-- Fase 7: seguridad base (bloqueo login escalado, sesión/cookies y CSRF)
-- Ejecutar UNA vez si ya tenías la BD creada antes de esta fase.

USE mipedido;

ALTER TABLE usuarios
    ADD COLUMN failed_login_count INT NOT NULL DEFAULT 0 COMMENT 'Intentos fallidos acumulados antes del próximo bloqueo',
    ADD COLUMN failed_login_stage TINYINT NOT NULL DEFAULT 0 COMMENT '0=próximo bloqueo 15 min, 1=próximo bloqueo 24 h',
    ADD COLUMN login_blocked_until DATETIME NULL DEFAULT NULL COMMENT 'Si es futura, el login queda bloqueado';

