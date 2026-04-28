-- Fase 5: tareas personales en calendario (empleado y admin). Ejecutar una vez si la base ya existía.
USE mipedido;

ALTER TABLE tareas
    ADD COLUMN es_personal TINYINT(1) NOT NULL DEFAULT 0 COMMENT '1 = creada por el usuario para sí mismo; 0 = asignada por otro (p. ej. jefe)'
    AFTER pedido_id;
