<?php
$pageTitle = 'Dashboard';
$currentNav = 'dashboard';
ob_start();
?>
<div class="row g-4 mb-2">
    <div class="col-md-4">
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
    <div class="col-md-4">
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
    <div class="col-md-4">
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
</div>

<div class="row g-4 mb-2">
    <div class="col-lg-7">
        <div class="card page-card h-100">
            <div class="card-body">
                <h2 class="h6 text-muted text-uppercase mb-3">Ventas por mes (últimos 6 meses)</h2>
                <div style="max-height: 320px;">
                    <canvas id="chartVentas" aria-label="Gráfico de ventas por mes"></canvas>
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

<div class="card page-card">
    <div class="card-body">
        <h2 class="h5 card-title mb-3">Bienvenido, <?= htmlspecialchars($_SESSION['usuario']['nombre']) ?></h2>
        <p class="text-muted mb-0">Resumen del ERP: KPIs y gráficos con datos de la base de datos (Chart.js).</p>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    var ventasLabels = <?= json_encode($ventasLabels, JSON_UNESCAPED_UNICODE) ?>;
    var ventasData = <?= json_encode($ventasData) ?>;
    var estadosLabels = <?= json_encode($estadosLabels, JSON_UNESCAPED_UNICODE) ?>;
    var estadosData = <?= json_encode($estadosData) ?>;

    var coloresEstados = [
        'rgba(234, 179, 8, 0.85)',
        'rgba(59, 130, 246, 0.85)',
        'rgba(34, 197, 94, 0.85)',
        'rgba(139, 92, 246, 0.85)',
        'rgba(239, 68, 68, 0.85)'
    ];

    if (typeof Chart !== 'undefined') {
        new Chart(document.getElementById('chartVentas'), {
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
});
</script>
<?php
$content = ob_get_clean();
require __DIR__ . '/layouts/main.php';
