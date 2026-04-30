-- Fase 10: aislamiento por workspace para que cada admin tenga su entorno.
-- Empleados ven los datos del workspace de su admin.
-- Compatible con servidores que no soportan "ADD COLUMN IF NOT EXISTS".

USE mipedido;

SET @legacy_ws := UUID();

-- usuarios.workspace_key
SET @exists_u_ws := (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'usuarios' AND COLUMN_NAME = 'workspace_key'
);
SET @sql_u_ws := IF(
    @exists_u_ws = 0,
    "ALTER TABLE usuarios ADD COLUMN workspace_key CHAR(36) NULL DEFAULT NULL COMMENT 'Espacio de trabajo del usuario'",
    "SELECT 'usuarios.workspace_key ya existe' AS info"
);
PREPARE stmt_u_ws FROM @sql_u_ws;
EXECUTE stmt_u_ws;
DEALLOCATE PREPARE stmt_u_ws;

-- clientes.workspace_key
SET @exists_c_ws := (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'clientes' AND COLUMN_NAME = 'workspace_key'
);
SET @sql_c_ws := IF(
    @exists_c_ws = 0,
    "ALTER TABLE clientes ADD COLUMN workspace_key CHAR(36) NULL DEFAULT NULL COMMENT 'Espacio de trabajo propietario'",
    "SELECT 'clientes.workspace_key ya existe' AS info"
);
PREPARE stmt_c_ws FROM @sql_c_ws;
EXECUTE stmt_c_ws;
DEALLOCATE PREPARE stmt_c_ws;

-- productos.workspace_key
SET @exists_pr_ws := (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'productos' AND COLUMN_NAME = 'workspace_key'
);
SET @sql_pr_ws := IF(
    @exists_pr_ws = 0,
    "ALTER TABLE productos ADD COLUMN workspace_key CHAR(36) NULL DEFAULT NULL COMMENT 'Espacio de trabajo propietario'",
    "SELECT 'productos.workspace_key ya existe' AS info"
);
PREPARE stmt_pr_ws FROM @sql_pr_ws;
EXECUTE stmt_pr_ws;
DEALLOCATE PREPARE stmt_pr_ws;

-- pedidos.workspace_key
SET @exists_pe_ws := (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'pedidos' AND COLUMN_NAME = 'workspace_key'
);
SET @sql_pe_ws := IF(
    @exists_pe_ws = 0,
    "ALTER TABLE pedidos ADD COLUMN workspace_key CHAR(36) NULL DEFAULT NULL COMMENT 'Espacio de trabajo propietario'",
    "SELECT 'pedidos.workspace_key ya existe' AS info"
);
PREPARE stmt_pe_ws FROM @sql_pe_ws;
EXECUTE stmt_pe_ws;
DEALLOCATE PREPARE stmt_pe_ws;

-- tareas.workspace_key
SET @exists_ta_ws := (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'tareas' AND COLUMN_NAME = 'workspace_key'
);
SET @sql_ta_ws := IF(
    @exists_ta_ws = 0,
    "ALTER TABLE tareas ADD COLUMN workspace_key CHAR(36) NULL DEFAULT NULL COMMENT 'Espacio de trabajo propietario'",
    "SELECT 'tareas.workspace_key ya existe' AS info"
);
PREPARE stmt_ta_ws FROM @sql_ta_ws;
EXECUTE stmt_ta_ws;
DEALLOCATE PREPARE stmt_ta_ws;

-- Backfill de usuarios sin workspace: todos al workspace legado común.
UPDATE usuarios
SET workspace_key = @legacy_ws
WHERE workspace_key IS NULL OR workspace_key = '';

-- Backfill datos existentes al workspace legado para no perder visibilidad actual.
UPDATE clientes
SET workspace_key = @legacy_ws
WHERE workspace_key IS NULL OR workspace_key = '';

UPDATE productos
SET workspace_key = @legacy_ws
WHERE workspace_key IS NULL OR workspace_key = '';

UPDATE pedidos
SET workspace_key = @legacy_ws
WHERE workspace_key IS NULL OR workspace_key = '';

UPDATE tareas
SET workspace_key = @legacy_ws
WHERE workspace_key IS NULL OR workspace_key = '';
