<?php

/**
 * Permisos finos (tablas permisos / usuario_permisos).
 * Admin: acceso total. Empleado: solo claves asignadas por el administrador.
 */

require_once __DIR__ . '/../models/Usuario.php';

/**
 * Sincroniza permisos en sesión (empleados: lectura fresca desde BD cada petición).
 */
function authRefreshPermisosSesion(): void
{
    authEnsureSession();
    if (!isset($_SESSION['usuario']['id'])) {
        $_SESSION['permisos'] = [];
        return;
    }
    if (isAdmin()) {
        $_SESSION['permisos'] = ['*'];
        return;
    }
    $uid = (int) $_SESSION['usuario']['id'];
    try {
        $_SESSION['permisos'] = (new Usuario())->getPermisosByUsuarioId($uid);
    } catch (Throwable $e) {
        $_SESSION['permisos'] = [];
    }
}

function usuarioTienePermiso(string $clave): bool
{
    authEnsureSession();
    if ($clave === '') {
        return false;
    }
    if (isAdmin()) {
        return true;
    }
    if (!isset($_SESSION['permisos'])) {
        authRefreshPermisosSesion();
    }
    $lista = $_SESSION['permisos'] ?? [];
    if (!is_array($lista)) {
        return false;
    }
    return in_array($clave, $lista, true);
}

/**
 * @param list<string> $claves
 */
function usuarioTieneAlgunoPermiso(array $claves): bool
{
    foreach ($claves as $clave) {
        if (usuarioTienePermiso((string) $clave)) {
            return true;
        }
    }
    return false;
}

function requirePermiso(string $clave): void
{
    if (!usuarioTienePermiso($clave)) {
        header('Location: index.php?page=403');
        exit;
    }
}

/**
 * @param list<string> $claves
 */
function requireAlgunoPermiso(array $claves): void
{
    if (!usuarioTieneAlgunoPermiso($claves)) {
        header('Location: index.php?page=403');
        exit;
    }
}

/**
 * @return string|list<string>|null
 */
function authPermisoRequeridoParaPagina(string $page)
{
    static $map = [
        'dashboard' => 'dashboard_ver',
        'dashboard_onboarding_complete' => 'dashboard_ver',
        'clientes' => 'clientes_gestionar',
        'clientes_create' => 'clientes_gestionar',
        'clientes_store' => 'clientes_gestionar',
        'clientes_edit' => 'clientes_gestionar',
        'clientes_update' => 'clientes_gestionar',
        'clientes_delete' => 'clientes_gestionar',
        'productos' => 'productos_gestionar',
        'productos_create' => 'productos_gestionar',
        'productos_store' => 'productos_gestionar',
        'productos_edit' => 'productos_gestionar',
        'productos_update' => 'productos_gestionar',
        'productos_delete' => 'productos_gestionar',
        'pedidos' => 'pedidos_gestionar',
        'pedidos_create' => 'pedidos_gestionar',
        'pedidos_store' => 'pedidos_gestionar',
        'pedidos_delete' => 'pedidos_gestionar',
        'pedidos_marcar_realizado' => 'pedidos_gestionar',
        'pedido_factura' => ['pedidos_gestionar', 'facturacion_ver'],
        'pedido_factura_guardar' => ['pedidos_gestionar', 'facturacion_ver'],
        'pedidos_historial' => ['pedidos_gestionar', 'ventas_ver', 'facturacion_ver'],
        'ventas' => 'ventas_ver',
        'facturacion' => 'facturacion_ver',
        'tareas' => 'tareas_gestionar',
        'tareas_create' => 'tareas_gestionar',
        'tareas_store' => 'tareas_gestionar',
        'tareas_estado' => 'tareas_gestionar',
        'calendario' => 'calendario_ver',
        'calendario_personal_store' => 'calendario_ver',
        'usuarios' => 'usuarios_gestionar',
        'usuarios_create' => 'usuarios_gestionar',
        'usuarios_store' => 'usuarios_gestionar',
        'usuarios_edit' => 'usuarios_gestionar',
        'usuarios_update' => 'usuarios_gestionar',
        'usuarios_delete' => 'usuarios_gestionar',
        'config' => 'configuracion_ver',
        'config_email_verify_send' => 'configuracion_ver',
        'config_plan_update' => 'configuracion_ver',
        'mi_entorno' => null,
        'mi_entorno_update' => null,
        'plan_select' => null,
        'plan_select_store' => null,
    ];

    return $map[$page] ?? null;
}

function authEnforcePermisoPagina(string $page): void
{
    authEnsureSession();
    if (!isset($_SESSION['usuario'])) {
        return;
    }
    authRefreshPermisosSesion();
    $req = authPermisoRequeridoParaPagina($page);
    if ($req === null) {
        return;
    }
    if (is_string($req)) {
        requirePermiso($req);
        return;
    }
    if (is_array($req)) {
        requireAlgunoPermiso($req);
    }
}

function authLandingUrlTrasLogin(): string
{
    if (isAdmin()) {
        if (trim((string) ($_SESSION['usuario']['plan_actual'] ?? '')) === '') {
            return 'index.php?page=plan_select';
        }
        return 'index.php?page=dashboard';
    }

    $orden = [
        ['dashboard', 'dashboard_ver'],
        ['ventas', 'ventas_ver'],
        ['facturacion', 'facturacion_ver'],
        ['pedidos', 'pedidos_gestionar'],
        ['tareas', 'tareas_gestionar'],
        ['calendario', 'calendario_ver'],
        ['clientes', 'clientes_gestionar'],
        ['productos', 'productos_gestionar'],
        ['config', 'configuracion_ver'],
    ];
    foreach ($orden as [$pagina, $permiso]) {
        if (usuarioTienePermiso($permiso)) {
            return 'index.php?page=' . $pagina;
        }
    }

    return 'index.php?page=mi_entorno';
}
