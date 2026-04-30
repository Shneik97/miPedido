<?php
require_once __DIR__ . '/../helpers/auth_helper.php';
require_once __DIR__ . '/../models/Producto.php';

/**
 * CRUD de productos con validación básica de datos.
 */
class ProductoController {

    /**
     * Requiere usuario autenticado para entrar al módulo.
     */
    private function requireAuth(): void {
        authEnsureSession();
        if (!isset($_SESSION['usuario'])) {
            $this->redirect('index.php');
        }
    }

    /**
     * Atajo para redirecciones.
     */
    private function redirect(string $url): void {
        header('Location: ' . $url);
        exit;
    }

    public function index() {
        $this->requireAuth();
        $isAdmin = isAdmin();
        $workspaceKey = currentWorkspaceKey();
        $producto = new Producto();
        $productos = $producto->getAll($workspaceKey);
        require __DIR__ . '/../views/productos/index.php';
    }

    public function create() {
        $this->requireAuth();
        checkRole('admin');
        require __DIR__ . '/../views/productos/create.php';
    }

    public function store() {
        $this->requireAuth();
        checkRole('admin');
        if (!csrfIsValidRequest()) {
            $this->redirect('index.php?page=productos&error=csrf');
        }
        $workspaceKey = currentWorkspaceKey();
        $producto = new Producto();
        $producto->create($this->normalizeProductoPost($_POST), $workspaceKey);
        $this->redirect('index.php?page=productos');
    }

    public function edit() {
        $this->requireAuth();
        checkRole('admin');
        $id = $_GET['id'] ?? '';
        $workspaceKey = currentWorkspaceKey();
        $producto = new Producto();
        $p = $producto->getById($id, $workspaceKey);
        if (!$p) {
            $this->redirect('index.php?page=productos');
        }
        require __DIR__ . '/../views/productos/edit.php';
    }

    public function update() {
        $this->requireAuth();
        checkRole('admin');
        if (!csrfIsValidRequest()) {
            $this->redirect('index.php?page=productos&error=csrf');
        }
        $id = $_POST['id'];
        $workspaceKey = currentWorkspaceKey();
        $producto = new Producto();
        $producto->update($id, $this->normalizeProductoPost($_POST), $workspaceKey);
        $this->redirect('index.php?page=productos');
    }

    public function delete(): void {
        $this->requireAuth();
        checkRole('admin');
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST' || !csrfIsValidRequest()) {
            $this->redirect('index.php?page=productos&error=csrf');
        }
        $id = (int) ($_POST['id'] ?? 0);
        if ($id < 1) {
            $this->redirect('index.php?page=productos');
        }
        $workspaceKey = currentWorkspaceKey();
        $producto = new Producto();
        $producto->delete($id, $workspaceKey);
        $this->redirect('index.php?page=productos');
    }

    /**
     * Convierte y limpia los campos del formulario de producto.
     */
    private function normalizeProductoPost(array $post): array {
        return [
            'nombre' => trim($post['nombre'] ?? ''),
            'descripcion' => trim($post['descripcion'] ?? ''),
            'precio' => (float) str_replace(',', '.', (string) ($post['precio'] ?? 0)),
            'stock' => (int) ($post['stock'] ?? 0),
        ];
    }
}
