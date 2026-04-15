-- Ejecutar una vez en Workbench si el login falla con admin@test.com / admin123.
-- El hash anterior del script de ejemplo no coincidía con password_verify('admin123', ...).
USE mipedido;

UPDATE usuarios
SET password = '$2y$12$riDn8HmrlUD3J7SWkvT5weT604Db.5u/ICUgueHrBA66hRHBGuhGm'
WHERE email = 'admin@test.com';
