<?php
require_once __DIR__ . '/../helpers/auth_helper.php';
require_once __DIR__ . '/../models/Pedido.php';
require_once __DIR__ . '/../models/Cliente.php';
require_once __DIR__ . '/../models/Producto.php';

class PedidoController {

    private function requireAuth(): void {
        authEnsureSession();
        if (!isset($_SESSION['usuario'])) {
            header('Location: index.php');
            exit;
        }
    }

    public function index(): void {
        $this->requireAuth();
        $isAdmin = isAdmin();
        $pedido = new Pedido();
        $pedidos = $pedido->getAllResumen();
        require __DIR__ . '/../views/pedidos/index.php';
    }

    public function create(): void {
        $this->requireAuth();
        $clientes = (new Cliente())->getAll();
        $productos = (new Producto())->getAll();
        $error = $_GET['error'] ?? '';
        require __DIR__ . '/../views/pedidos/create.php';
    }

    public function store(): void {
        $this->requireAuth();
        $clienteId = (int) ($_POST['cliente_id'] ?? 0);
        $productoId = (int) ($_POST['producto_id'] ?? 0);
        $cantidad = (int) ($_POST['cantidad'] ?? 0);
        $metodo = $_POST['metodo_pago'] ?? 'efectivo';

        $allowed = ['efectivo', 'tarjeta', 'transferencia'];
        if (!in_array($metodo, $allowed, true)) {
            $metodo = 'efectivo';
        }

        $producto = new Producto();
        $p = $producto->getById($productoId);
        if (!$p || $clienteId < 1) {
            header('Location: index.php?page=pedidos_create&error=invalid');
            exit;
        }

        $precio = (float) $p['precio'];

        try {
            (new Pedido())->createWithDetalle($clienteId, $productoId, $cantidad, $metodo, $precio);
        } catch (Throwable $e) {
            $code = strpos($e->getMessage(), 'Stock') !== false ? 'stock' : 'fail';
            header('Location: index.php?page=pedidos_create&error=' . $code);
            exit;
        }

        header('Location: index.php?page=pedidos');
        exit;
    }

    public function delete(string $id): void {
        $this->requireAuth();
        checkRole('admin');
        $pedido = new Pedido();
        $pedido->delete((int) $id);
        header('Location: index.php?page=pedidos');
        exit;
    }

    public function factura(): void {
        $this->requireAuth();
        $id = (int) ($_GET['id'] ?? 0);
        $pedido = new Pedido();
        $cab = $pedido->getById($id);
        if (!$cab) {
            header('Location: index.php?page=pedidos');
            exit;
        }
        $lineas = $pedido->getLineasFactura($id);
        require __DIR__ . '/../views/pedidos/factura_print.php';
    }
}
