<?php
// Vista principal del panel:
// 1) KPIs rapidos
// 2) Grafico de ventas por meses
// 3) Top productos y estados de pedidos
// Nota estudiante: solo pinta datos; el calculo viene del controlador/modelo.
$pageTitle = 'Dashboard';
$currentNav = 'dashboard';
ob_start();
?>
<style>
    .onboarding-overlay {
        position: fixed;
        inset: 0;
        background: rgba(15, 23, 42, 0.46);
        z-index: 1060;
        display: none;
    }
    .onboarding-overlay.show {
        display: block;
    }
    .onboarding-bubble {
        position: fixed;
        z-index: 1061;
        max-width: 340px;
        width: min(340px, calc(100vw - 24px));
        max-height: calc(100vh - 24px);
        overflow: auto;
        background: #fff;
        border-radius: 12px;
        box-shadow: 0 18px 45px rgba(2, 6, 23, .28);
        border: 1px solid #e2e8f0;
        padding: 14px;
        display: none;
    }
    .onboarding-bubble.show {
        display: block;
    }
    .onboarding-step {
        font-size: .78rem;
        letter-spacing: .03em;
        text-transform: uppercase;
        color: #64748b;
        font-weight: 700;
    }
    .onboarding-highlight {
        position: relative;
        z-index: 1062;
        box-shadow: 0 0 0 3px rgba(59, 130, 246, .55), 0 0 0 7px rgba(59, 130, 246, .18);
        border-radius: 10px;
    }
    .ventas-panel-anim-wrap {
        position: relative;
        overflow: hidden;
    }
    .ventas-panel-content {
        transition: transform .24s ease, opacity .24s ease;
        will-change: transform, opacity;
    }
    .ventas-slide-left-out { transform: translateX(-16px); opacity: .2; }
    .ventas-slide-left-in { transform: translateX(16px); opacity: .2; }
    .ventas-slide-right-out { transform: translateX(16px); opacity: .2; }
    .ventas-slide-right-in { transform: translateX(-16px); opacity: .2; }
    .dash-top-products thead th {
        font-size: 0.78rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: .02em;
        color: #64748b;
        border-bottom: 1px solid #e2e8f0;
        background: transparent;
    }
    .dash-top-products tbody td {
        padding-top: .55rem;
        padding-bottom: .55rem;
        border-color: #edf2f7;
    }
    .dash-top-products tbody tr:last-child td {
        border-bottom: 0;
    }
</style>
<div class="row g-4 mb-2">
    <div class="col-12">
        <div class="card page-card">
            <div class="card-body">
                <h2 class="h5 card-title mb-1">Bienvenido, <?= htmlspecialchars($_SESSION['usuario']['nombre']) ?></h2>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card page-card h-100 border-0">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="rounded-circle bg-primary bg-opacity-10 p-3 text-primary">
                    <i class="fa-solid fa-users fa-2x"></i>
                </div>
                <div>
                    <p class="text-muted small mb-0">Total clientes</p>
                    <p class="fs-3 fw-bold mb-0"><?= (int) $totalClientes ?></p>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card page-card h-100 border-0">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="rounded-circle bg-warning bg-opacity-10 p-3 text-warning">
                    <i class="fa-solid fa-clock fa-2x"></i>
                </div>
                <div>
                    <p class="text-muted small mb-0">Pedidos pendientes</p>
                    <p class="fs-3 fw-bold mb-0"><?= (int) $pedidosPendientes ?></p>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card page-card h-100 border-0">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="rounded-circle bg-success bg-opacity-10 p-3 text-success">
                    <i class="fa-solid fa-box-open fa-2x"></i>
                </div>
                <div>
                    <p class="text-muted small mb-0">Productos en catálogo</p>
                    <p class="fs-3 fw-bold mb-0"><?= (int) $totalProductos ?></p>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card page-card h-100 border-0">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="rounded-circle bg-info bg-opacity-10 p-3 text-info">
                    <i class="fa-solid fa-list-check fa-2x"></i>
                </div>
                <div>
                    <p class="text-muted small mb-0">Tareas abiertas</p>
                    <p class="fs-3 fw-bold mb-0"><?= (int) $tareasPendientesNav ?></p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php if (!empty($showAdminOnboarding)): ?>
<!-- Tutorial inicial para admin (solo una vez). -->
<div id="onboardingOverlay" class="onboarding-overlay" aria-hidden="true"></div>
<div id="onboardingBubble" class="onboarding-bubble" role="dialog" aria-modal="true" aria-label="Guía rápida">
    <div id="onboardingStep" class="onboarding-step mb-1">Paso 1 de 3</div>
    <h3 id="onboardingTitle" class="h6 mb-2">Bienvenido</h3>
    <p id="onboardingText" class="mb-3 text-muted">Este panel resume lo más importante para empezar.</p>
    <div class="d-flex justify-content-between align-items-center gap-2">
        <button id="onboardingPrev" type="button" class="btn btn-sm btn-outline-secondary" disabled>Anterior</button>
        <div class="ms-auto d-flex gap-2">
            <button id="onboardingNext" type="button" class="btn btn-sm btn-primary">Siguiente</button>
            <form method="post" action="index.php?page=dashboard_onboarding_complete">
                <?= csrfInput() ?>
                <button id="onboardingFinish" type="submit" class="btn btn-sm btn-success d-none">Finalizar</button>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>

<div class="row g-4 mb-2">
    <div class="col-lg-7">
        <div class="card page-card mb-3">
            <div class="card-body">
                <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
                    <h2 class="h6 text-muted text-uppercase mb-0">Ventas por mes (bloques de 6 meses)</h2>
                    <div class="btn-group btn-group-sm" role="group" aria-label="Navegación de ventas por periodo">
                        <button id="btnVentasAtras" type="button" class="btn btn-outline-secondary" <?= !empty($puedeIrAtras) ? '' : 'disabled' ?> title="Ver 6 meses anteriores">
                            <i class="fa-solid fa-chevron-left me-1"></i>6 meses atrás
                        </button>
                        <button id="btnVentasAdelante" type="button" class="btn btn-outline-secondary" <?= !empty($puedeIrAdelante) ? '' : 'disabled' ?> title="Volver 6 meses hacia el presente">
                            6 meses adelante<i class="fa-solid fa-chevron-right ms-1"></i>
                        </button>
                        <a class="btn btn-outline-primary" href="index.php?page=dashboard" title="Volver al periodo actual (recarga completa)">
                            Actualidad
                        </a>
                    </div>
                </div>
                <div id="ventasPanelWrap" class="ventas-panel-anim-wrap">
                    <div id="ventasPanelContent" class="ventas-panel-content">
                        <div style="max-height: 320px;">
                            <canvas id="chartVentas" aria-label="Gráfico de ventas por mes"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div id="topProductosCard" class="card page-card">
            <div class="card-body">
                <h2 class="h6 text-muted text-uppercase mb-2">Productos más vendidos</h2>
                <div id="topProductosBody">
                    <?php if (!empty($productosMasVendidos)): ?>
                        <div class="table-responsive">
                            <table class="table table-sm align-middle mb-0 dash-top-products">
                                <thead>
                                    <tr>
                                        <th>Producto</th>
                                        <th class="text-end">Unidades</th>
                                        <th class="text-end">Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($productosMasVendidos as $p): ?>
                                        <tr>
                                            <td><?= htmlspecialchars((string) $p['producto_nombre']) ?></td>
                                            <td class="text-end"><?= (int) $p['unidades_vendidas'] ?></td>
                                            <td class="text-end"><?= number_format((float) $p['total_facturado'], 2, ',', '.') ?> €</td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <p class="text-muted mb-0">No hay ventas registradas en este periodo.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-5">
        <div class="card page-card h-100">
            <div class="card-body">
                <h2 class="h6 text-muted text-uppercase mb-3">Estados de pedidos</h2>
                <div style="max-height: 320px;">
                    <canvas id="chartEstados" aria-label="Gráfico de estados de pedidos"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Datos que consume public/js/main.js para pintar graficos y eventos de dashboard.
window.MIPEDIDO_DASHBOARD_DATA = {
    ventasLabels: <?= json_encode($ventasLabels, JSON_UNESCAPED_UNICODE) ?>,
    ventasData: <?= json_encode($ventasData) ?>,
    ventasOffset: <?= (int) $ventasOffset ?>,
    estadosLabels: <?= json_encode($estadosLabels, JSON_UNESCAPED_UNICODE) ?>,
    estadosData: <?= json_encode($estadosData) ?>,
    showAdminOnboarding: <?= !empty($showAdminOnboarding) ? 'true' : 'false' ?>
};
</script>
<?php
$content = ob_get_clean();
require __DIR__ . '/layouts/main.php';
