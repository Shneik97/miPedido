<?php
// Panel de facturacion:
// - Filtro por mes
// - Filtro por estado de factura (todos / pendiente / realizada)
// - Acciones por fila (ver, editar pendiente, historial, cerrar)
$pageTitle = 'Facturación';
$currentNav = 'facturacion';
ob_start();
?>
<div class="card page-card">
    <div class="card-body">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
            <div>
                <h1 class="h4 mb-0">Facturación</h1>
                <p class="text-muted small mb-0">Historial de facturas, estado y acciones de visualización/seguimiento.</p>
            </div>
            <form method="get" action="index.php" class="d-flex flex-wrap gap-2 align-items-center">
                <input type="hidden" name="page" value="facturacion">
                <label for="facturacionYm" class="small text-muted mb-0">Mes</label>
                <div class="d-flex align-items-center gap-1">
                    <input id="facturacionYm" name="ym" type="month" class="form-control form-control-sm" value="<?= htmlspecialchars((string) ($ym ?? date('Y-m'))) ?>">
                    <button type="submit" class="btn btn-sm btn-primary">Ver</button>
                </div>
                <div class="btn-group btn-group-sm" role="group" aria-label="Filtro de facturas">
                    <button type="submit" name="estado_factura" value="todos" class="btn <?= $estadoFactura === 'todos' ? 'btn-primary' : 'btn-outline-primary' ?>">Todas</button>
                    <button type="submit" name="estado_factura" value="pendiente" class="btn <?= $estadoFactura === 'pendiente' ? 'btn-primary' : 'btn-outline-primary' ?>">Pendientes</button>
                    <button type="submit" name="estado_factura" value="realizada" class="btn <?= $estadoFactura === 'realizada' ? 'btn-primary' : 'btn-outline-primary' ?>">Realizadas</button>
                </div>
            </form>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                <tr>
                    <th>Factura</th>
                    <th>Fecha</th>
                    <th>Cliente</th>
                    <th>Estado factura</th>
                    <th>Estado pedido</th>
                    <th>Pago</th>
                    <th class="text-end">Total</th>
                    <th class="text-end">Acciones</th>
                </tr>
                </thead>
                <tbody>
                <?php if (!empty($facturas)): ?>
                    <?php foreach ($facturas as $f): ?>
                        <?php
                        // En esta demo, "factura realizada" equivale a pedido en estado "realizado".
                        $esRealizada = (string) $f['estado'] === 'realizado';
                        $estadoFacturaLabel = $esRealizada ? 'Realizada' : 'Pendiente';
                        ?>
                        <tr class="<?= $esRealizada ? 'table-success' : '' ?>">
                            <td>#F-<?= str_pad((string) ((int) $f['id']), 5, '0', STR_PAD_LEFT) ?></td>
                            <td><?= htmlspecialchars((string) $f['fecha']) ?></td>
                            <td>
                                <?= htmlspecialchars((string) $f['cliente_nombre']) ?>
                                <?php if (!empty($f['cliente_email'])): ?>
                                    <div class="small text-muted"><?= htmlspecialchars((string) $f['cliente_email']) ?></div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="badge <?= $esRealizada ? 'text-bg-success' : 'text-bg-warning text-dark' ?>">
                                    <?= htmlspecialchars($estadoFacturaLabel) ?>
                                </span>
                            </td>
                            <td><?= htmlspecialchars((string) $f['estado']) ?></td>
                            <td><?= htmlspecialchars((string) $f['metodo_pago']) ?></td>
                            <td class="text-end"><?= number_format((float) $f['total'], 2, ',', '.') ?> €</td>
                            <td class="text-end text-nowrap">
                                <a href="index.php?page=pedido_factura&amp;id=<?= (int) $f['id'] ?>" class="btn btn-sm btn-outline-secondary" title="Ver o imprimir factura">
                                    <i class="fa-solid fa-file-invoice"></i>
                                </a>
                                <?php if (!$esRealizada): ?>
                                    <a href="index.php?page=pedido_factura&amp;id=<?= (int) $f['id'] ?>&amp;edit=1" class="btn btn-sm btn-outline-warning" title="Editar factura pendiente" target="_blank" rel="noopener noreferrer">
                                        <i class="fa-solid fa-pen-to-square"></i>
                                    </a>
                                <?php endif; ?>
                                <a href="index.php?page=pedidos_historial&amp;id=<?= (int) $f['id'] ?>" class="btn btn-sm btn-outline-info" title="Ver historial de la factura/pedido">
                                    <i class="fa-solid fa-clock-rotate-left"></i>
                                </a>
                                <?php if (!$esRealizada): ?>
                                    <form method="post" action="index.php?page=pedidos_marcar_realizado" class="d-inline js-confirm-realizado" data-confirm-message="¿Cerrar factura como realizada?">
                                        <?= csrfInput() ?>
                                        <input type="hidden" name="id" value="<?= (int) $f['id'] ?>">
                                        <button type="submit" class="btn btn-sm btn-outline-primary" title="Cerrar factura como realizada">
                                            <i class="fa-solid fa-circle-check"></i>
                                        </button>
                                    </form>
                                <?php else: ?>
                                    <span class="btn btn-sm btn-success disabled" title="Factura ya realizada">
                                        <i class="fa-solid fa-check"></i>
                                    </span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="8" class="text-center text-muted py-4">No hay facturas para este filtro.</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/main.php';
