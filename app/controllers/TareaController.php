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
            $this->redirect('index.php');
        }
    }

    /**
     * Redirección corta para simplificar el controlador.
     */
    private function redirect(string $url): void {
        header('Location: ' . $url);
        exit;
    }

    public function index(): void {
        $this->requireAuth();
        $uid = (int) $_SESSION['usuario']['id'];
        $workspaceKey = currentWorkspaceKey();
        /** @var bool $isAdmin visible en la vista para el botón "Nueva tarea" */
        $isAdmin = isAdmin();
        $tareas = (new Tarea())->getAllForUser($uid, $isAdmin, $workspaceKey);
        require __DIR__ . '/../views/tareas/index.php';
    }

    public function create(): void {
        $this->requireAuth();
        checkRole('admin');
        $workspaceKey = currentWorkspaceKey();
        $usuarios = (new Usuario())->getAll($workspaceKey);
        require_once __DIR__ . '/../models/Pedido.php';
        $pedidos = (new Pedido())->getAllResumen($workspaceKey);
        require __DIR__ . '/../views/tareas/create.php';
    }

    public function store(): void {
        $this->requireAuth();
        checkRole('admin');
        if (!csrfIsValidRequest()) {
            $this->redirect('index.php?page=tareas_create&error=csrf');
        }
        $titulo = trim((string) ($_POST['titulo'] ?? ''));
        $descripcion = trim((string) ($_POST['descripcion'] ?? ''));
        $asignado = (int) ($_POST['asignado_a'] ?? 0);
        $pedidoId = (int) ($_POST['pedido_id'] ?? 0);
        $fechaLim = trim((string) ($_POST['fecha_limite'] ?? ''));

        if ($titulo === '' || $asignado < 1) {
            $this->redirect('index.php?page=tareas_create&error=invalido');
        }

        $creadoPor = (int) $_SESSION['usuario']['id'];
        $workspaceKey = currentWorkspaceKey();
        $pid = $pedidoId > 0 ? $pedidoId : null;
        $fl = $fechaLim !== '' ? $fechaLim : null;

        (new Tarea())->create($titulo, $descripcion, $asignado, $creadoPor, $pid, $fl, false, $workspaceKey);
        $this->redirect('index.php?page=tareas&ok=creada');
    }

    public function cambiarEstado(): void {
        $this->requireAuth();
        requirePermiso('tareas_gestionar');
        if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
            $this->redirect('index.php?page=tareas');
        }
        if (!csrfIsValidRequest()) {
            $this->redirect('index.php?page=tareas&error=csrf');
        }
        $id = (int) ($_POST['id'] ?? 0);
        $estado = (string) ($_POST['estado'] ?? '');
        $actorId = (int) $_SESSION['usuario']['id'];
        $workspaceKey = currentWorkspaceKey();
        $ok = (new Tarea())->updateEstado($id, $estado, $actorId, isAdmin(), $workspaceKey);
        $this->redirect('index.php?page=tareas&' . ($ok ? 'ok=estado' : 'error=estado'));
    }
}
