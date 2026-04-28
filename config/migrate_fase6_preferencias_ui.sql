-- Preferencias de interfaz por usuario (empleado y admin).
USE mipedido;

ALTER TABLE usuarios
    ADD COLUMN preferencias_ui JSON NULL DEFAULT NULL
    COMMENT 'JSON: sidebar_pos, fondo_trabajo, acento, tema_contenido, sidebar_collapsed';
