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
            header('Location: index.php');
            exit;
        }
    }

    public function index() {
        $this->requireAuth();
        $isAdmin = isAdmin();
        $producto = new Producto();
        $productos = $producto->getAll();
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
            header('Location: index.php?page=productos&error=csrf');
            exit;
        }
        $producto = new Producto();
        $producto->create($this->normalizeProductoPost($_POST));
        header('Location: index.php?page=productos');
        exit;
    }

    public function edit() {
        $this->requireAuth();
        checkRole('admin');
        $id = $_GET['id'] ?? '';
        $producto = new Producto();
        $p = $producto->getById($id);
        if (!$p) {
            header('Location: index.php?page=productos');
            exit;
        }
        require __DIR__ . '/../views/productos/edit.php';
    }

    public function update() {
        $this->requireAuth();
        checkRole('admin');
        if (!csrfIsValidRequest()) {
            header('Location: index.php?page=productos&error=csrf');
            exit;
        }
        $id = $_POST['id'];
        $producto = new Producto();
        $producto->update($id, $this->normalizeProductoPost($_POST));
        header('Location: index.php?page=productos');
        exit;
    }

    public function delete($id) {
        $this->requireAuth();
        checkRole('admin');
        $producto = new Producto();
        $producto->delete($id);
        header('Location: index.php?page=productos');
        exit;
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
