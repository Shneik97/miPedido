-- Ejecutar en MySQL Workbench como usuario con privilegios (p. ej. root).
-- Corrige «Access denied (1045)» para user_mipedido: recrea la cuenta con la misma
-- contraseña que config/database.php (12345) en localhost, TCP local y comodín.

DROP USER IF EXISTS 'user_mipedido'@'localhost';
DROP USER IF EXISTS 'user_mipedido'@'127.0.0.1';
DROP USER IF EXISTS 'user_mipedido'@'%';

CREATE USER 'user_mipedido'@'localhost' IDENTIFIED BY '12345';
CREATE USER 'user_mipedido'@'127.0.0.1' IDENTIFIED BY '12345';
CREATE USER 'user_mipedido'@'%' IDENTIFIED BY '12345';

GRANT SELECT, INSERT, UPDATE, DELETE ON mipedido.* TO 'user_mipedido'@'localhost';
GRANT SELECT, INSERT, UPDATE, DELETE ON mipedido.* TO 'user_mipedido'@'127.0.0.1';
GRANT SELECT, INSERT, UPDATE, DELETE ON mipedido.* TO 'user_mipedido'@'%';

FLUSH PRIVILEGES;
