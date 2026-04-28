<?php
$pageTitle = 'Dashboard';
$currentNav = 'dashboard';
ob_start();
?>
<style>
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
                <p class="text-muted small mb-3">Ranking del mismo periodo que el gráfico de ventas.</p>
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
document.addEventListener('DOMContentLoaded', function () {
    var ventasLabels = <?= json_encode($ventasLabels, JSON_UNESCAPED_UNICODE) ?>;
    var ventasData = <?= json_encode($ventasData) ?>;
    var ventasOffset = <?= (int) $ventasOffset ?>;
    var estadosLabels = <?= json_encode($estadosLabels, JSON_UNESCAPED_UNICODE) ?>;
    var estadosData = <?= json_encode($estadosData) ?>;

    var coloresEstados = [
        'rgba(234, 179, 8, 0.85)',
        'rgba(59, 130, 246, 0.85)',
        'rgba(34, 197, 94, 0.85)',
        'rgba(139, 92, 246, 0.85)',
        'rgba(239, 68, 68, 0.85)',
        'rgba(16, 185, 129, 0.85)'
    ];

    var ventasChart = null;
    if (typeof Chart !== 'undefined') {
        ventasChart = new Chart(document.getElementById('chartVentas'), {
            type: 'bar',
            data: {
                labels: ventasLabels,
                datasets: [{
                    label: 'Ventas (€)',
                    data: ventasData,
                    backgroundColor: 'rgba(37, 99, 235, 0.65)',
                    borderColor: 'rgb(37, 99, 235)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: { y: { beginAtZero: true } }
            }
        });

        if (estadosData.length > 0) {
            new Chart(document.getElementById('chartEstados'), {
                type: 'pie',
                data: {
                    labels: estadosLabels,
                    datasets: [{
                        data: estadosData,
                        backgroundColor: coloresEstados.slice(0, estadosData.length),
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { position: 'bottom' } }
                }
            });
        }
    }

    function euroEs(v) {
        return Number(v || 0).toLocaleString('es-ES', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + ' €';
    }

    function escHtml(v) {
        return String(v || '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function renderTopProductos(rows) {
        var body = document.getElementById('topProductosBody');
        if (!body) {
            return;
        }
        if (!rows || rows.length === 0) {
            body.innerHTML = '<p class="text-muted mb-0">No hay ventas registradas en este periodo.</p>';
            return;
        }
        var html = '<div class="table-responsive"><table class="table table-sm align-middle mb-0 dash-top-products"><thead><tr><th>Producto</th><th class="text-end">Unidades</th><th class="text-end">Total</th></tr></thead><tbody>';
        rows.forEach(function (r) {
            html += '<tr><td>' + escHtml(r.producto_nombre) + '</td><td class="text-end">' + Number(r.unidades_vendidas || 0) + '</td><td class="text-end">' + euroEs(r.total_facturado) + '</td></tr>';
        });
        html += '</tbody></table></div>';
        body.innerHTML = html;
    }

    function setNavButtons(canBack, canForward) {
        var bBack = document.getElementById('btnVentasAtras');
        var bForward = document.getElementById('btnVentasAdelante');
        if (bBack) {
            bBack.disabled = !canBack;
        }
        if (bForward) {
            bForward.disabled = !canForward;
        }
    }

    function animarSalida(panel, direccion) {
        panel.classList.remove('ventas-slide-left-in', 'ventas-slide-right-in');
        panel.classList.add(direccion === 'left' ? 'ventas-slide-left-out' : 'ventas-slide-right-out');
    }

    function animarEntrada(panel, direccion) {
        panel.classList.remove('ventas-slide-left-out', 'ventas-slide-right-out');
        panel.classList.add(direccion === 'left' ? 'ventas-slide-left-in' : 'ventas-slide-right-in');
        window.requestAnimationFrame(function () {
            panel.classList.remove('ventas-slide-left-in', 'ventas-slide-right-in');
        });
    }

    function cargarVentas(offset, direccion) {
        var panel = document.getElementById('ventasPanelContent');
        if (!panel || !ventasChart) {
            window.location.href = 'index.php?page=dashboard&ventas_offset=' + offset;
            return;
        }
        animarSalida(panel, direccion);
        window.setTimeout(function () {
            fetch('index.php?page=dashboard&ajax=ventas_panel&ventas_offset=' + encodeURIComponent(offset), {
                credentials: 'same-origin'
            }).then(function (r) {
                return r.json();
            }).then(function (data) {
                ventasOffset = Number(data.offset || 0);
                ventasChart.data.labels = data.ventas_labels || [];
                ventasChart.data.datasets[0].data = data.ventas_data || [];
                ventasChart.update();
                renderTopProductos(data.productos_mas_vendidos || []);
                setNavButtons(!!data.puede_ir_atras, !!data.puede_ir_adelante);
                animarEntrada(panel, direccion);
            }).catch(function () {
                window.location.href = 'index.php?page=dashboard&ventas_offset=' + offset;
            });
        }, 140);
    }

    var btnBack = document.getElementById('btnVentasAtras');
    var btnForward = document.getElementById('btnVentasAdelante');
    if (btnBack) {
        btnBack.addEventListener('click', function () {
            if (btnBack.disabled) {
                return;
            }
            cargarVentas(ventasOffset + 6, 'left');
        });
    }
    if (btnForward) {
        btnForward.addEventListener('click', function () {
            if (btnForward.disabled) {
                return;
            }
            cargarVentas(Math.max(0, ventasOffset - 6), 'right');
        });
    }
});
</script>
<?php
$content = ob_get_clean();
require __DIR__ . '/layouts/main.php';
