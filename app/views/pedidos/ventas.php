<?php
$pageTitle = 'Ventas';
$currentNav = 'ventas';
ob_start();

$ymSeleccionado = sprintf('%04d-%02d', $year, $month);
?>
<div class="row g-4">
    <div class="col-12">
        <div class="card page-card">
            <div class="card-body">
                <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
                    <div>
                        <h1 class="h4 mb-0">Ventas</h1>
                        <p class="text-muted small mb-0">Historial mensual detallado y desglose del mes seleccionado.</p>
                    </div>
                    <form method="get" action="index.php" class="d-flex gap-2 align-items-center">
                        <input type="hidden" name="page" value="ventas">
                        <label for="ventasYm" class="small text-muted mb-0">Mes</label>
                        <input id="ventasYm" name="ym" type="month" class="form-control form-control-sm" value="<?= htmlspecialchars($ymSeleccionado) ?>">
                        <button type="submit" class="btn btn-sm btn-primary">Ver</button>
                    </form>
                </div>
                <div class="row g-3">
                    <div class="col-md-4">
                        <div class="border rounded p-3 h-100 bg-light-subtle">
                            <div class="text-muted small">Total vendido (mes)</div>
                            <div class="h4 mb-0"><?= number_format($totalMes, 2, ',', '.') ?> €</div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="border rounded p-3 h-100 bg-light-subtle">
                            <div class="text-muted small">Pedidos vendidos (mes)</div>
                            <div class="h4 mb-0"><?= (int) $cantidadPedidosMes ?></div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="border rounded p-3 h-100 bg-light-subtle">
                            <div class="text-muted small">Ticket medio (mes)</div>
                            <div class="h4 mb-0"><?= number_format($ticketMedioMes, 2, ',', '.') ?> €</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12">
        <div class="card page-card h-100">
            <div class="card-body">
                <h2 class="h6 text-muted text-uppercase mb-3">Detalle de ventas del mes <?= htmlspecialchars($ymSeleccionado) ?></h2>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                        <tr>
                            <th>Pedido</th>
                            <th>Fecha</th>
                            <th>Cliente</th>
                            <th>Estado</th>
                            <th>Pago</th>
                            <th class="text-end">Total</th>
                            <th class="text-end">Acciones</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php if (!empty($detalleVentas)): ?>
                            <?php foreach ($detalleVentas as $v): ?>
                                <?php $esRealizado = (string) $v['estado'] === 'realizado'; ?>
                                <tr class="<?= $esRealizado ? 'table-success' : '' ?>">
                                    <td>#<?= (int) $v['id'] ?></td>
                                    <td><?= htmlspecialchars((string) $v['fecha']) ?></td>
                                    <td><?= htmlspecialchars((string) $v['cliente_nombre']) ?></td>
                                    <td><?= htmlspecialchars((string) $v['estado']) ?></td>
                                    <td><?= htmlspecialchars((string) $v['metodo_pago']) ?></td>
                                    <td class="text-end"><?= number_format((float) $v['total'], 2, ',', '.') ?> €</td>
                                    <td class="text-end">
                                        <a href="index.php?page=pedido_factura&amp;id=<?= (int) $v['id'] ?>" class="btn btn-sm btn-outline-secondary" title="Ver factura">
                                            <i class="fa-solid fa-file-invoice"></i>
                                        </a>
                                        <a href="index.php?page=pedidos_historial&amp;id=<?= (int) $v['id'] ?>" class="btn btn-sm btn-outline-info" title="Historial pedido">
                                            <i class="fa-solid fa-clock-rotate-left"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="7" class="text-center text-muted py-4">No hay pedidos en este mes.</td></tr>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/main.php';
