<?php
require_once __DIR__ . '/../helpers/auth_helper.php';
require_once __DIR__ . '/../models/Cliente.php';

/**
 * CRUD de clientes.
 * - Admin: alta, edición y borrado.
 * - Empleado: solo listado.
 */
class ClienteController {

    /**
     * Impide el acceso al módulo de clientes sin sesión iniciada.
     */
    private function requireAuth(): void {
        authEnsureSession();
        if (!isset($_SESSION['usuario'])) {
            $this->redirect('index.php');
        }
    }

    /**
     * Atajo para redirecciones (evita repetir header+exit).
     */
    private function redirect(string $url): void {
        header('Location: ' . $url);
        exit;
    }

    public function index() {
        $this->requireAuth();
        $isAdmin = isAdmin();
        $workspaceKey = currentWorkspaceKey();
        $cliente = new Cliente();
        $clientes = $cliente->getAll($workspaceKey);
        require __DIR__ . '/../views/clientes/index.php';
    }

    /**
     * Muestra formulario de alta.
     */
    public function create() {
        $this->requireAuth();
        requirePermiso('clientes_gestionar');
        require __DIR__ . '/../views/clientes/create.php';
    }

    public function store() {
        $this->requireAuth();
        requirePermiso('clientes_gestionar');
        // CSRF: solo aceptamos el formulario generado por nuestra app.
        if (!csrfIsValidRequest()) {
            $this->redirect('index.php?page=clientes&error=csrf');
        }
        $workspaceKey = currentWorkspaceKey();
        $cliente = new Cliente();
        $cliente->create($_POST, $workspaceKey);
        $this->redirect('index.php?page=clientes');
    }

    public function edit() {
        $this->requireAuth();
        requirePermiso('clientes_gestionar');
        $id = $_GET['id'] ?? '';
        $workspaceKey = currentWorkspaceKey();
        $cliente = new Cliente();
        $c = $cliente->getById($id, $workspaceKey);
        if (!$c) {
            $this->redirect('index.php?page=clientes');
        }
        require __DIR__ . '/../views/clientes/edit.php';
    }

    public function update() {
        $this->requireAuth();
        requirePermiso('clientes_gestionar');
        if (!csrfIsValidRequest()) {
            $this->redirect('index.php?page=clientes&error=csrf');
        }
        $id = $_POST['id'];
        $workspaceKey = currentWorkspaceKey();
        $cliente = new Cliente();
        $cliente->update($id, $_POST, $workspaceKey);
        $this->redirect('index.php?page=clientes');
    }

    /**
     * Elimina un cliente por id.
     */
    public function delete(): void {
        $this->requireAuth();
        requirePermiso('clientes_gestionar');
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST' || !csrfIsValidRequest()) {
            $this->redirect('index.php?page=clientes&error=csrf');
        }
        $id = (int) ($_POST['id'] ?? 0);
        if ($id < 1) {
            $this->redirect('index.php?page=clientes');
        }
        $workspaceKey = currentWorkspaceKey();
        $cliente = new Cliente();
        $cliente->delete($id, $workspaceKey);
        $this->redirect('index.php?page=clientes');
    }
}
