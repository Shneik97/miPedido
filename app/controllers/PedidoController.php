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
            $this->redirect('index.php');
        }
    }

    /**
     * Helper simple para no repetir header+exit en cada método.
     */
    private function redirect(string $url): void {
        header('Location: ' . $url);
        exit;
    }

    public function index(): void {
        $this->requireAuth();
        $isAdmin = isAdmin();
        $workspaceKey = currentWorkspaceKey();
        $pedido = new Pedido();
        $pedidos = $pedido->getAllResumen($workspaceKey);
        require __DIR__ . '/../views/pedidos/index.php';
    }

    public function create(): void {
        $this->requireAuth();
        requirePermiso('pedidos_gestionar');
        $workspaceKey = currentWorkspaceKey();
        $clientes = (new Cliente())->getAll($workspaceKey);
        $productos = (new Producto())->getAll($workspaceKey);
        $error = $_GET['error'] ?? '';
        require __DIR__ . '/../views/pedidos/create.php';
    }

    /**
     * Crea pedido con una línea de detalle y control de stock.
     */
    public function store(): void {
        $this->requireAuth();
        requirePermiso('pedidos_gestionar');
        if (!csrfIsValidRequest()) {
            $this->redirect('index.php?page=pedidos_create&error=csrf');
        }
        $clienteId = (int) ($_POST['cliente_id'] ?? 0);
        $productoId = (int) ($_POST['producto_id'] ?? 0);
        $cantidad = (int) ($_POST['cantidad'] ?? 0);
        $metodo = $_POST['metodo_pago'] ?? 'efectivo';
        $workspaceKey = currentWorkspaceKey();

        $allowed = ['efectivo', 'tarjeta', 'transferencia'];
        if (!in_array($metodo, $allowed, true)) {
            $metodo = 'efectivo';
        }

        $producto = new Producto();
        $p = $producto->getById($productoId, $workspaceKey);
        $cliente = (new Cliente())->getById($clienteId, $workspaceKey);
        if (!$p || !$cliente || $clienteId < 1) {
            $this->redirect('index.php?page=pedidos_create&error=invalid');
        }

        $precio = (float) $p['precio'];

        $usuarioId = isset($_SESSION['usuario']['id']) ? (int) $_SESSION['usuario']['id'] : null;

        try {
            (new Pedido())->createWithDetalle($clienteId, $productoId, $cantidad, $metodo, $precio, $usuarioId, $workspaceKey);
        } catch (Throwable $e) {
            $code = strpos($e->getMessage(), 'Stock') !== false ? 'stock' : 'fail';
            $this->redirect('index.php?page=pedidos_create&error=' . $code);
        }

        $this->redirect('index.php?page=pedidos');
    }

    /**
     * Elimina pedido pendiente (solo admin, POST + CSRF).
     */
    public function delete(): void {
        $this->requireAuth();
        checkRole('admin');
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST' || !csrfIsValidRequest()) {
            $this->redirect('index.php?page=pedidos&error=csrf');
        }
        $idPost = (int) ($_POST['id'] ?? 0);
        if ($idPost < 1) {
            $this->redirect('index.php?page=pedidos');
        }
        $pedido = new Pedido();
        $ok = false;
        try {
            $ok = $pedido->delete($idPost, currentWorkspaceKey());
        } catch (Throwable $e) {
            $ok = false;
        }
        $q = $ok ? 'ok=delete' : 'error=delete';
        $this->redirect('index.php?page=pedidos&' . $q);
    }

    public function factura(): void {
        $this->requireAuth();
        requireAlgunoPermiso(['pedidos_gestionar', 'facturacion_ver']);
        $id = (int) ($_GET['id'] ?? 0);
        $workspaceKey = currentWorkspaceKey();
        $editMode = (string) ($_GET['edit'] ?? '') === '1';
        $ok = (string) ($_GET['ok'] ?? '');
        $error = (string) ($_GET['error'] ?? '');
        $pedido = new Pedido();
        $cab = $pedido->getById($id, $workspaceKey);
        if (!$cab) {
            $this->redirect('index.php?page=pedidos');
        }
        $lineas = $pedido->getLineasFactura($id, $workspaceKey);
        require __DIR__ . '/../views/pedidos/factura_print.php';
    }

    /**
     * Guarda edición de factura solamente cuando el pedido sigue pendiente.
     */
    public function facturaGuardar(): void {
        $this->requireAuth();
        requireAlgunoPermiso(['pedidos_gestionar', 'facturacion_ver']);
        if (!csrfIsValidRequest()) {
            $id = (int) ($_POST['id'] ?? 0);
            $this->redirect('index.php?page=pedido_factura&id=' . $id . '&edit=1&error=csrf');
        }

        $id = (int) ($_POST['id'] ?? 0);
        if ($id < 1) {
            $this->redirect('index.php?page=facturacion');
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
        $workspaceKey = currentWorkspaceKey();
        $ok = false;
        try {
            $ok = (new Pedido())->updateFacturaEditable($id, $metodo, $lineasInput, $usuarioId, $workspaceKey);
        } catch (Throwable $e) {
            $ok = false;
        }

        if ($ok) {
            $this->redirect('index.php?page=pedido_factura&id=' . $id . '&ok=editado');
        }

        $this->redirect('index.php?page=pedido_factura&id=' . $id . '&edit=1&error=bloqueada');
    }

    /**
     * Cierra el pedido como "realizado" y fija fecha_realizado (admin y empleado).
     */
    public function marcarRealizado(): void {
        $this->requireAuth();
        requirePermiso('pedidos_gestionar');
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST' || !csrfIsValidRequest()) {
            $this->redirect('index.php?page=pedidos&error=csrf');
        }
        $id = (int) ($_POST['id'] ?? 0);
        if ($id < 1) {
            $this->redirect('index.php?page=pedidos');
        }
        $uid = (int) ($_SESSION['usuario']['id'] ?? 0);
        $workspaceKey = currentWorkspaceKey();
        try {
            $ok = (new Pedido())->marcarRealizado($id, $uid, $workspaceKey);
        } catch (Throwable $e) {
            $ok = false;
        }
        $q = $ok ? 'ok=realizado' : 'error=realizado';
        $this->redirect('index.php?page=pedidos&' . $q);
    }

    /**
     * Vista de línea de tiempo auditable del pedido.
     */
    public function historial(): void {
        $this->requireAuth();
        $id = (int) ($_GET['id'] ?? 0);
        $workspaceKey = currentWorkspaceKey();
        $model = new Pedido();
        $cab = $model->getById($id, $workspaceKey);
        if (!$cab) {
            $this->redirect('index.php?page=pedidos');
        }
        $eventos = $model->getHistorialByPedidoId($id, $workspaceKey);
        require __DIR__ . '/../views/pedidos/historial.php';
    }

    /**
     * Vista específica de ventas: histórico mensual + detalle del mes seleccionado.
     */
    public function ventas(): void
    {
        $this->requireAuth();
        $workspaceKey = currentWorkspaceKey();
        $pedido = new Pedido();
        $resumenMensual = $pedido->getResumenMensualVentasHistorico(36, $workspaceKey);

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

        $detalleVentas = $pedido->getVentasDetallePorMes($year, $month, $workspaceKey);
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
        $workspaceKey = currentWorkspaceKey();
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
        $facturas = $pedido->getFacturasHistorial($estadoFactura, $ym, $workspaceKey);
        require __DIR__ . '/../views/pedidos/facturacion.php';
    }
}
