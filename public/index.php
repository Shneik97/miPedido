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

$page = $_GET['page'] ?? 'home';

switch ($page) {
    case 'dashboard':
        (new DashboardController())->index();
        break;

    case 'home':
        require_once '../app/views/home.php';
        break;

    case 'login':
        (new LoginController())->login();
        break;

    case 'register':
        (new LoginController())->register();
        break;

    case 'register_store':
        (new LoginController())->registerStore();
        break;

    case 'auth_google_start':
        (new LoginController())->authGoogleStart();
        break;

    case 'auth_google_callback':
        (new LoginController())->authGoogleCallback();
        break;

    case '403':
        require_once '../app/views/403.php';
        break;

    case 'clientes':
        $controller = new ClienteController();
        $controller->index();
        break;

    case 'clientes_create':
        $controller = new ClienteController();
        $controller->create();
        break;

    case 'clientes_store':
        $controller = new ClienteController();
        $controller->store();
        break;

    case 'clientes_edit':
        $controller = new ClienteController();
        $controller->edit();
        break;

    case 'clientes_update':
        $controller = new ClienteController();
        $controller->update();
        break;

    case 'clientes_delete':
        if (isset($_GET['id'])) {
            $controller = new ClienteController();
            $controller->delete($_GET['id']);
        }
        break;

    case 'productos':
        $controller = new ProductoController();
        $controller->index();
        break;

    case 'productos_create':
        $controller = new ProductoController();
        $controller->create();
        break;

    case 'productos_store':
        $controller = new ProductoController();
        $controller->store();
        break;

    case 'productos_edit':
        $controller = new ProductoController();
        $controller->edit();
        break;

    case 'productos_update':
        $controller = new ProductoController();
        $controller->update();
        break;

    case 'productos_delete':
        if (isset($_GET['id'])) {
            $controller = new ProductoController();
            $controller->delete($_GET['id']);
        }
        break;

    case 'pedidos':
        (new PedidoController())->index();
        break;

    case 'pedidos_create':
        (new PedidoController())->create();
        break;

    case 'pedidos_store':
        (new PedidoController())->store();
        break;

    case 'pedidos_delete':
        if (isset($_GET['id'])) {
            (new PedidoController())->delete($_GET['id']);
        }
        break;

    case 'pedido_factura':
        (new PedidoController())->factura();
        break;

    case 'pedido_factura_guardar':
        (new PedidoController())->facturaGuardar();
        break;

    case 'pedidos_marcar_realizado':
        (new PedidoController())->marcarRealizado();
        break;

    case 'pedidos_historial':
        (new PedidoController())->historial();
        break;

    case 'ventas':
        (new PedidoController())->ventas();
        break;

    case 'facturacion':
        (new PedidoController())->facturacion();
        break;

    case 'tareas':
        (new TareaController())->index();
        break;

    case 'tareas_create':
        (new TareaController())->create();
        break;

    case 'tareas_store':
        (new TareaController())->store();
        break;

    case 'tareas_estado':
        (new TareaController())->cambiarEstado();
        break;

    case 'calendario':
        (new CalendarioController())->index();
        break;

    case 'calendario_personal_store':
        (new CalendarioController())->storePersonal();
        break;

    case 'usuarios':
        (new UsuarioController())->index();
        break;

    case 'usuarios_create':
        (new UsuarioController())->create();
        break;

    case 'usuarios_store':
        (new UsuarioController())->store();
        break;

    case 'usuarios_edit':
        (new UsuarioController())->edit();
        break;

    case 'usuarios_update':
        (new UsuarioController())->update();
        break;

    case 'usuarios_delete':
        if (isset($_GET['id'])) {
            (new UsuarioController())->delete((string) $_GET['id']);
        }
        break;

    case 'config':
        (new ConfigController())->index();
        break;

    case 'mi_entorno':
        (new MiEntornoController())->index();
        break;

    case 'mi_entorno_update':
        (new MiEntornoController())->update();
        break;

    case 'logout':
        authEnsureSession();
        $_SESSION = [];
        session_destroy();
        require_once __DIR__ . '/../app/helpers/preferencias_ui_helper.php';
        mipedido_sidebar_clear_collapse_cookie();
        header('Location: index.php?page=home');
        break;

    default:
        (new LoginController())->login();
        break;
}
