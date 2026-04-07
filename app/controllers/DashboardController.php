<?php
require_once __DIR__ . '/../models/Cliente.php';
require_once __DIR__ . '/../models/Pedido.php';
require_once __DIR__ . '/../models/Producto.php';

class DashboardController {

    public function index(): void {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (!isset($_SESSION['usuario'])) {
            header('Location: index.php');
            exit;
        }

        $totalClientes = (new Cliente())->count();
        $pedidosPendientes = (new Pedido())->countPendientes();
        $totalProductos = (new Producto())->count();

        $pedidoModel = new Pedido();
        $ventasMes = $pedidoModel->getVentasTotalesPorMes(6);
        $ventasLabels = $ventasMes['labels'];
        $ventasData = $ventasMes['data'];
        $estadosChart = $pedidoModel->getDistribucionEstadosGrafico();
        $estadosLabels = $estadosChart['labels'];
        $estadosData = $estadosChart['data'];

        require __DIR__ . '/../views/dashboard.php';
    }
}
