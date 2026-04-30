<?php
require_once '../app/helpers/auth_helper.php';
require_once '../app/controllers/ClienteController.php';
require_once '../app/controllers/ProductoController.php';
require_once '../app/controllers/DashboardController.php';
require_once '../app/controllers/PedidoController.php';
require_once '../app/controllers/UsuarioController.php';
require_once '../app/controllers/ConfigController.php';
require_once '../app/controllers/TareaController.php';
require_once '../app/controllers/CalendarioController.php';
require_once '../app/controllers/MiEntornoController.php';
require_once '../app/controllers/LoginController.php';
require_once '../app/controllers/PlanController.php';

/**
 * Router simple del proyecto.
 * Nivel estudiante:
 * - Lee `page` desde la URL.
 * - Si es vista directa -> include de vista.
 * - Si es ruta de controlador -> ejecuta metodo.
 * - Si no existe -> fallback a login.
 */

/**
 * Ejecuta una accion de controlador sin parametros.
 */
function dispatchController(string $controllerClass, string $method): void
{
    (new $controllerClass())->$method();
}

/**
 * Ejecuta una accion que requiere "id" en query string.
 * Si no existe, no se ejecuta nada para mantener el comportamiento actual.
 */
function dispatchControllerWithId(string $controllerClass, string $method): void
{
    if (!isset($_GET['id'])) {
        return;
    }
    (new $controllerClass())->$method((string) $_GET['id']);
}

/**
 * Cierra sesion y limpia la preferencia de sidebar colapsado.
 */
function dispatchLogout(): void
{
    authEnsureSession();
    $_SESSION = [];
    session_destroy();
    require_once __DIR__ . '/../app/helpers/preferencias_ui_helper.php';
    mipedido_sidebar_clear_collapse_cookie();
    header('Location: index.php?page=login');
}

// Pagina solicitada por query string. Si no viene, se muestra la home publica.
$page = (string) ($_GET['page'] ?? 'home');

// Rutas que solo renderizan una vista (sin pasar por controlador).
$viewRoutes = [
    'home' => '../app/views/home.php',
    '403' => '../app/views/403.php',
];

// Rutas de controlador (acciones principales de la app).
$controllerRoutes = [
    'dashboard' => [DashboardController::class, 'index'],
    'dashboard_onboarding_complete' => [DashboardController::class, 'onboardingComplete'],
    'login' => [LoginController::class, 'login'],
    'forgot_password' => [LoginController::class, 'forgotPassword'],
    'forgot_password_reset' => [LoginController::class, 'forgotPasswordReset'],
    'register' => [LoginController::class, 'register'],
    'register_store' => [LoginController::class, 'registerStore'],
    'auth_google_start' => [LoginController::class, 'authGoogleStart'],
    'auth_google_callback' => [LoginController::class, 'authGoogleCallback'],
    'plan_select' => [PlanController::class, 'select'],
    'plan_select_store' => [PlanController::class, 'store'],
    'clientes' => [ClienteController::class, 'index'],
    'clientes_create' => [ClienteController::class, 'create'],
    'clientes_store' => [ClienteController::class, 'store'],
    'clientes_edit' => [ClienteController::class, 'edit'],
    'clientes_update' => [ClienteController::class, 'update'],
    'clientes_delete' => [ClienteController::class, 'delete'],
    'productos' => [ProductoController::class, 'index'],
    'productos_create' => [ProductoController::class, 'create'],
    'productos_store' => [ProductoController::class, 'store'],
    'productos_edit' => [ProductoController::class, 'edit'],
    'productos_update' => [ProductoController::class, 'update'],
    'productos_delete' => [ProductoController::class, 'delete'],
    'pedidos' => [PedidoController::class, 'index'],
    'pedidos_create' => [PedidoController::class, 'create'],
    'pedidos_store' => [PedidoController::class, 'store'],
    'pedido_factura' => [PedidoController::class, 'factura'],
    'pedido_factura_guardar' => [PedidoController::class, 'facturaGuardar'],
    'pedidos_marcar_realizado' => [PedidoController::class, 'marcarRealizado'],
    'pedidos_historial' => [PedidoController::class, 'historial'],
    'ventas' => [PedidoController::class, 'ventas'],
    'facturacion' => [PedidoController::class, 'facturacion'],
    'tareas' => [TareaController::class, 'index'],
    'tareas_create' => [TareaController::class, 'create'],
    'tareas_store' => [TareaController::class, 'store'],
    'tareas_estado' => [TareaController::class, 'cambiarEstado'],
    'calendario' => [CalendarioController::class, 'index'],
    'calendario_personal_store' => [CalendarioController::class, 'storePersonal'],
    'usuarios' => [UsuarioController::class, 'index'],
    'usuarios_create' => [UsuarioController::class, 'create'],
    'usuarios_store' => [UsuarioController::class, 'store'],
    'usuarios_edit' => [UsuarioController::class, 'edit'],
    'usuarios_update' => [UsuarioController::class, 'update'],
    'usuarios_delete' => [UsuarioController::class, 'delete'],
    'config' => [ConfigController::class, 'index'],
    'config_email_verify_send' => [ConfigController::class, 'emailVerificacionEnviar'],
    'config_plan_update' => [ConfigController::class, 'planUpdate'],
    'mi_entorno' => [MiEntornoController::class, 'index'],
    'mi_entorno_update' => [MiEntornoController::class, 'update'],
    'pedidos_delete' => [PedidoController::class, 'delete'],
];

if (isset($viewRoutes[$page])) {
    require_once $viewRoutes[$page];
    return;
}

if ($page === 'logout') {
    dispatchLogout();
    return;
}

if (isset($controllerRoutes[$page])) {
    [$controllerClass, $method] = $controllerRoutes[$page];
    dispatchController($controllerClass, $method);
    return;
}

// Fallback histórico: si la ruta no existe, se muestra login.
dispatchController(LoginController::class, 'login');
