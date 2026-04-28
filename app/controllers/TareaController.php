<?php
/**
 * CRUD simplificado de tareas: creación solo admin; listado y cambio de estado para asignados y admin.
 */
require_once __DIR__ . '/../helpers/auth_helper.php';
require_once __DIR__ . '/../models/Tarea.php';
require_once __DIR__ . '/../models/Usuario.php';

class TareaController {

    private function requireAuth(): void {
        authEnsureSession();
        if (!isset($_SESSION['usuario'])) {
            header('Location: index.php');
            exit;
        }
    }

    public function index(): void {
        $this->requireAuth();
        $uid = (int) $_SESSION['usuario']['id'];
        /** @var bool $isAdmin visible en la vista para el botón "Nueva tarea" */
        $isAdmin = isAdmin();
        $tareas = (new Tarea())->getAllForUser($uid, $isAdmin);
        require __DIR__ . '/../views/tareas/index.php';
    }

    public function create(): void {
        $this->requireAuth();
        checkRole('admin');
        $usuarios = (new Usuario())->getAll();
        require_once __DIR__ . '/../models/Pedido.php';
        $pedidos = (new Pedido())->getAllResumen();
        require __DIR__ . '/../views/tareas/create.php';
    }

    public function store(): void {
        $this->requireAuth();
        checkRole('admin');
        if (!csrfIsValidRequest()) {
            header('Location: index.php?page=tareas_create&error=csrf');
            exit;
        }
        $titulo = trim((string) ($_POST['titulo'] ?? ''));
        $descripcion = trim((string) ($_POST['descripcion'] ?? ''));
        $asignado = (int) ($_POST['asignado_a'] ?? 0);
        $pedidoId = (int) ($_POST['pedido_id'] ?? 0);
        $fechaLim = trim((string) ($_POST['fecha_limite'] ?? ''));

        if ($titulo === '' || $asignado < 1) {
            header('Location: index.php?page=tareas_create&error=invalido');
            exit;
        }

        $creadoPor = (int) $_SESSION['usuario']['id'];
        $pid = $pedidoId > 0 ? $pedidoId : null;
        $fl = $fechaLim !== '' ? $fechaLim : null;

        (new Tarea())->create($titulo, $descripcion, $asignado, $creadoPor, $pid, $fl);
        header('Location: index.php?page=tareas&ok=creada');
        exit;
    }

    public function cambiarEstado(): void {
        $this->requireAuth();
        if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
            header('Location: index.php?page=tareas');
            exit;
        }
        if (!csrfIsValidRequest()) {
            header('Location: index.php?page=tareas&error=csrf');
            exit;
        }
        $id = (int) ($_POST['id'] ?? 0);
        $estado = (string) ($_POST['estado'] ?? '');
        $actorId = (int) $_SESSION['usuario']['id'];
        $ok = (new Tarea())->updateEstado($id, $estado, $actorId, isAdmin());
        header('Location: index.php?page=tareas&' . ($ok ? 'ok=estado' : 'error=estado'));
        exit;
    }
}
