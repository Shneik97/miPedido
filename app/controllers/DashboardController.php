<?php
require_once __DIR__ . '/../helpers/auth_helper.php';
require_once __DIR__ . '/../models/Cliente.php';
require_once __DIR__ . '/../models/Pedido.php';
require_once __DIR__ . '/../models/Producto.php';
require_once __DIR__ . '/../models/Tarea.php';

/**
 * Dashboard principal con KPIs y paneles de resumen.
 */
class DashboardController {

    /**
     * Carga datos del panel y renderiza la vista.
     */
    public function index(): void {
        authEnsureSession();
        if (!isset($_SESSION['usuario'])) {
            header('Location: index.php');
            exit;
        }

        // Métricas rápidas para tarjetas superiores.
        $totalClientes = (new Cliente())->count();
        $pedidosPendientes = (new Pedido())->countPendientes();
        $totalProductos = (new Producto())->count();

        $uidDash = (int) $_SESSION['usuario']['id'];
        try {
            $tareasPendientesNav = (new Tarea())->countPendientesNav($uidDash, isAdmin());
        } catch (Throwable $e) {
            $tareasPendientesNav = 0;
        }

        // Datos de ventas y estado para gráficos del dashboard.
        $pedidoModel = new Pedido();
        // Navegación de ventas por bloques de 6 meses (máximo 3 años atrás).
        $bloqueMeses = 6;
        $maxAtrasMeses = 36;
        $maxOffset = $maxAtrasMeses - $bloqueMeses; // 30 -> bloque más antiguo visible
        $offsetRaw = (int) ($_GET['ventas_offset'] ?? 0);
        if ($offsetRaw < 0) {
            $offsetRaw = 0;
        }
        if ($offsetRaw > $maxOffset) {
            $offsetRaw = $maxOffset;
        }
        // Fuerza pasos de 6 en 6 para mantener la UX simple.
        $ventasOffset = (int) (floor($offsetRaw / $bloqueMeses) * $bloqueMeses);

        $ventasMes = $pedidoModel->getVentasTotalesPorMes($bloqueMeses, $ventasOffset);
        $ventasLabels = $ventasMes['labels'];
        $ventasData = $ventasMes['data'];
        $productosMasVendidos = $pedidoModel->getProductosMasVendidos(5, $bloqueMeses, $ventasOffset);
        $ventasOffsetPrev = min($maxOffset, $ventasOffset + $bloqueMeses);
        $ventasOffsetNext = max(0, $ventasOffset - $bloqueMeses);
        $puedeIrAtras = $ventasOffset < $maxOffset;
        $puedeIrAdelante = $ventasOffset > 0;
        $estadosChart = $pedidoModel->getDistribucionEstadosGrafico();
        $estadosLabels = $estadosChart['labels'];
        $estadosData = $estadosChart['data'];

        // Petición parcial AJAX: devuelve solo datos del panel de ventas.
        if (isset($_GET['ajax']) && $_GET['ajax'] === 'ventas_panel') {
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode([
                'offset' => $ventasOffset,
                'offset_prev' => $ventasOffsetPrev,
                'offset_next' => $ventasOffsetNext,
                'puede_ir_atras' => $puedeIrAtras,
                'puede_ir_adelante' => $puedeIrAdelante,
                'ventas_labels' => $ventasLabels,
                'ventas_data' => $ventasData,
                'productos_mas_vendidos' => $productosMasVendidos,
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }

        require __DIR__ . '/../views/dashboard.php';
    }
}
