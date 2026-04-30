-- Preferencias de interfaz por usuario (empleado y admin).
USE mipedido;

-- Version idempotente: evita error si se ejecuta mas de una vez.
SET @c_prefs := (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'usuarios'
      AND COLUMN_NAME = 'preferencias_ui'
);
SET @s_prefs := IF(
    @c_prefs = 0,
    "ALTER TABLE usuarios ADD COLUMN preferencias_ui JSON NULL DEFAULT NULL COMMENT 'JSON: sidebar_pos, fondo_trabajo, acento, tema_contenido, sidebar_collapsed'",
    "SELECT 'usuarios.preferencias_ui ya existe' AS info"
);
PREPARE st_prefs FROM @s_prefs;
EXECUTE st_prefs;
DEALLOCATE PREPARE st_prefs;
