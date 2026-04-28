<?php
require_once __DIR__ . '/../helpers/auth_helper.php';
require_once __DIR__ . '/../helpers/whatsapp_helper.php';
require_once __DIR__ . '/../models/Pedido.php';
require_once __DIR__ . '/../models/Cliente.php';
require_once __DIR__ . '/../models/Producto.php';

/**
 * Gestión de pedidos y facturación básica.
 */
class PedidoController {

    /**
     * Requiere sesión válida.
     */
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

    /**
     * Crea pedido con una línea de detalle y control de stock.
     */
    public function store(): void {
        $this->requireAuth();
        if (!csrfIsValidRequest()) {
            header('Location: index.php?page=pedidos_create&error=csrf');
            exit;
        }
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

        $usuarioId = isset($_SESSION['usuario']['id']) ? (int) $_SESSION['usuario']['id'] : null;

        try {
            (new Pedido())->createWithDetalle($clienteId, $productoId, $cantidad, $metodo, $precio, $usuarioId);
        } catch (Throwable $e) {
            $code = strpos($e->getMessage(), 'Stock') !== false ? 'stock' : 'fail';
            header('Location: index.php?page=pedidos_create&error=' . $code);
            exit;
        }

        header('Location: index.php?page=pedidos');
        exit;
    }

    /**
     * Elimina pedido pendiente (solo admin, POST + CSRF).
     */
    public function delete(string $id): void {
        $this->requireAuth();
        checkRole('admin');
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST' || !csrfIsValidRequest()) {
            header('Location: index.php?page=pedidos&error=csrf');
            exit;
        }
        $idPost = (int) ($_POST['id'] ?? 0);
        if ($idPost > 0) {
            $id = (string) $idPost;
        }
        $pedido = new Pedido();
        $ok = false;
        try {
            $ok = $pedido->delete((int) $id);
        } catch (Throwable $e) {
            $ok = false;
        }
        $q = $ok ? 'ok=delete' : 'error=delete';
        header('Location: index.php?page=pedidos&' . $q);
        exit;
    }

    public function factura(): void {
        $this->requireAuth();
        $id = (int) ($_GET['id'] ?? 0);
        $editMode = (string) ($_GET['edit'] ?? '') === '1';
        $ok = (string) ($_GET['ok'] ?? '');
        $error = (string) ($_GET['error'] ?? '');
        $pedido = new Pedido();
        $cab = $pedido->getById($id);
        if (!$cab) {
            header('Location: index.php?page=pedidos');
            exit;
        }
        $lineas = $pedido->getLineasFactura($id);
        require __DIR__ . '/../views/pedidos/factura_print.php';
    }

    /**
     * Guarda edición de factura solamente cuando el pedido sigue pendiente.
     */
    public function facturaGuardar(): void {
        $this->requireAuth();
        if (!csrfIsValidRequest()) {
            $id = (int) ($_POST['id'] ?? 0);
            header('Location: index.php?page=pedido_factura&id=' . $id . '&edit=1&error=csrf');
            exit;
        }

        $id = (int) ($_POST['id'] ?? 0);
        if ($id < 1) {
            header('Location: index.php?page=facturacion');
            exit;
        }

        $metodo = (string) ($_POST['metodo_pago'] ?? 'efectivo');
        $cantidades = $_POST['linea_cantidad'] ?? [];
        $precios = $_POST['linea_precio'] ?? [];

        $lineasInput = [];
        foreach ($cantidades as $lineaId => $cantidad) {
            $lineasInput[(int) $lineaId] = [
                'cantidad' => (int) $cantidad,
                'precio_unitario' => isset($precios[$lineaId]) ? (float) $precios[$lineaId] : 0,
            ];
        }

        $usuarioId = isset($_SESSION['usuario']['id']) ? (int) $_SESSION['usuario']['id'] : null;
        $ok = false;
        try {
            $ok = (new Pedido())->updateFacturaEditable($id, $metodo, $lineasInput, $usuarioId);
        } catch (Throwable $e) {
            $ok = false;
        }

        if ($ok) {
            header('Location: index.php?page=pedido_factura&id=' . $id . '&ok=editado');
            exit;
        }

        header('Location: index.php?page=pedido_factura&id=' . $id . '&edit=1&error=bloqueada');
        exit;
    }

    /**
     * Cierra el pedido como "realizado" y fija fecha_realizado (admin y empleado).
     */
    public function marcarRealizado(): void {
        $this->requireAuth();
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST' || !csrfIsValidRequest()) {
            header('Location: index.php?page=pedidos&error=csrf');
            exit;
        }
        $id = (int) ($_POST['id'] ?? 0);
        if ($id < 1) {
            header('Location: index.php?page=pedidos');
            exit;
        }
        $uid = (int) ($_SESSION['usuario']['id'] ?? 0);
        try {
            $ok = (new Pedido())->marcarRealizado($id, $uid);
        } catch (Throwable $e) {
            $ok = false;
        }
        $q = $ok ? 'ok=realizado' : 'error=realizado';
        header('Location: index.php?page=pedidos&' . $q);
        exit;
    }

    /**
     * Vista de línea de tiempo auditable del pedido.
     */
    public function historial(): void {
        $this->requireAuth();
        $id = (int) ($_GET['id'] ?? 0);
        $model = new Pedido();
        $cab = $model->getById($id);
        if (!$cab) {
            header('Location: index.php?page=pedidos');
            exit;
        }
        $eventos = $model->getHistorialByPedidoId($id);
        require __DIR__ . '/../views/pedidos/historial.php';
    }

    /**
     * Vista específica de ventas: histórico mensual + detalle del mes seleccionado.
     */
    public function ventas(): void
    {
        $this->requireAuth();
        $pedido = new Pedido();
        $resumenMensual = $pedido->getResumenMensualVentasHistorico(36);

        $ymActual = date('Y-m');
        $ymMin = date('Y-m', strtotime('-35 months'));
        $ym = (string) ($_GET['ym'] ?? $ymActual);
        if (!preg_match('/^(\d{4})-(\d{2})$/', $ym, $m)) {
            $ym = $ymActual;
        }
        if ($ym < $ymMin) {
            $ym = $ymMin;
        }
        if ($ym > $ymActual) {
            $ym = $ymActual;
        }
        preg_match('/^(\d{4})-(\d{2})$/', $ym, $m);
        $year = (int) $m[1];
        $month = (int) $m[2];

        $dtSel = DateTime::createFromFormat('Y-m-d', sprintf('%04d-%02d-01', $year, $month));
        $dtPrev = (clone $dtSel)->modify('-1 month');
        $dtNext = (clone $dtSel)->modify('+1 month');
        $ymPrev = $dtPrev->format('Y-m');
        $ymNext = $dtNext->format('Y-m');
        if ($ymPrev < $ymMin) {
            $ymPrev = $ymMin;
        }
        if ($ymNext > $ymActual) {
            $ymNext = $ymActual;
        }
        $puedePrev = $ym > $ymMin;
        $puedeNext = $ym < $ymActual;

        $detalleVentas = $pedido->getVentasDetallePorMes($year, $month);
        $totalMes = 0.0;
        foreach ($detalleVentas as $row) {
            $totalMes += (float) ($row['total'] ?? 0);
        }
        $cantidadPedidosMes = count($detalleVentas);
        $ticketMedioMes = $cantidadPedidosMes > 0 ? $totalMes / $cantidadPedidosMes : 0.0;

        require __DIR__ . '/../views/pedidos/ventas.php';
    }

    /**
     * Historial de facturas con estado y acciones de visualización/seguimiento.
     */
    public function facturacion(): void
    {
        $this->requireAuth();
        $estadoFactura = (string) ($_GET['estado_factura'] ?? 'todos');
        if (!in_array($estadoFactura, ['todos', 'pendiente', 'realizada'], true)) {
            $estadoFactura = 'todos';
        }
        $ymActual = date('Y-m');
        $ymMin = date('Y-m', strtotime('-35 months'));
        $ym = (string) ($_GET['ym'] ?? $ymActual);
        if (!preg_match('/^(\d{4})-(\d{2})$/', $ym)) {
            $ym = $ymActual;
        }
        if ($ym < $ymMin) {
            $ym = $ymMin;
        }
        if ($ym > $ymActual) {
            $ym = $ymActual;
        }

        $pedido = new Pedido();
        $facturas = $pedido->getFacturasHistorial($estadoFactura, $ym);
        require __DIR__ . '/../views/pedidos/facturacion.php';
    }
}
