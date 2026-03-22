<?php
require_once '../app/controllers/ClienteController.php';

$page = $_GET['page'] ?? 'login';

switch ($page) {
    case 'dashboard':
        require_once '../app/views/dashboard.php';
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

    case 'clientes_delete': // <- ruta para eliminar
        if (isset($_GET['id'])) {
            $controller = new ClienteController();
            $controller->delete($_GET['id']);
        }
        break;

    case 'logout':
        session_start();
        session_destroy();
        header("Location: index.php");
        break;

    default:
        require_once '../app/controllers/LoginController.php';
        require_once '../app/views/login.php';
        break;
}