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
            header('Location: index.php');
            exit;
        }
    }

    public function index() {
        $this->requireAuth();
        $isAdmin = isAdmin();
        $cliente = new Cliente();
        $clientes = $cliente->getAll();
        require __DIR__ . '/../views/clientes/index.php';
    }

    /**
     * Muestra formulario de alta.
     */
    public function create() {
        $this->requireAuth();
        checkRole('admin');
        require __DIR__ . '/../views/clientes/create.php';
    }

    public function store() {
        $this->requireAuth();
        checkRole('admin');
        // CSRF: solo aceptamos el formulario generado por nuestra app.
        if (!csrfIsValidRequest()) {
            header('Location: index.php?page=clientes&error=csrf');
            exit;
        }
        $cliente = new Cliente();
        $cliente->create($_POST);
        header('Location: index.php?page=clientes');
        exit;
    }

    public function edit() {
        $this->requireAuth();
        checkRole('admin');
        $id = $_GET['id'] ?? '';
        $cliente = new Cliente();
        $c = $cliente->getById($id);
        if (!$c) {
            header('Location: index.php?page=clientes');
            exit;
        }
        require __DIR__ . '/../views/clientes/edit.php';
    }

    public function update() {
        $this->requireAuth();
        checkRole('admin');
        if (!csrfIsValidRequest()) {
            header('Location: index.php?page=clientes&error=csrf');
            exit;
        }
        $id = $_POST['id'];
        $cliente = new Cliente();
        $cliente->update($id, $_POST);
        header('Location: index.php?page=clientes');
        exit;
    }

    /**
     * Elimina un cliente por id.
     */
    public function delete($id) {
        $this->requireAuth();
        checkRole('admin');
        $cliente = new Cliente();
        $cliente->delete($id);
        header('Location: index.php?page=clientes');
        exit;
    }
}
