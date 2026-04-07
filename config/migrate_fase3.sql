-- Ejecutar en MySQL si la base ya existía antes de la Fase 3 (añade columna metodo_pago).
USE mipedido;

ALTER TABLE pedidos
ADD COLUMN metodo_pago ENUM('efectivo', 'tarjeta', 'transferencia') NOT NULL DEFAULT 'efectivo'
AFTER estado;
