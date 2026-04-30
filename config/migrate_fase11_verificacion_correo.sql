-- Fase 11 (simulación junior): estado simple de "correo verificado".
-- No hay envío real de email ni códigos OTP.
-- Compatible con servidores que no soportan "ADD COLUMN IF NOT EXISTS".

USE mipedido;

SET @c1 := (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'usuarios' AND COLUMN_NAME = 'email_verificado'
);
SET @s1 := IF(
    @c1 = 0,
    "ALTER TABLE usuarios ADD COLUMN email_verificado TINYINT(1) NOT NULL DEFAULT 0 COMMENT '1 cuando el usuario valida su correo'",
    "SELECT 'usuarios.email_verificado ya existe' AS info"
);
PREPARE st1 FROM @s1; EXECUTE st1; DEALLOCATE PREPARE st1;
