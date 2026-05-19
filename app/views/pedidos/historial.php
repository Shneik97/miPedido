<?php
/**
 * Vista: línea de tiempo de acciones sobre un pedido (tabla pedido_historial).
 */
$pageTitle = 'Historial del pedido #' . (int) ($cab['id'] ?? 0);
$currentNav = 'pedidos';
ob_start();
?>
<div class="card page-card">
    <div class="card-body">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
            <div>
                <h1 class="h4 mb-1">Historial del pedido #<?= (int) $cab['id'] ?></h1>
                <p class="text-muted small mb-0">Cliente: <?= htmlspecialchars((string) $cab['cliente_nombre']) ?></p>
            </div>
            <a href="index.php?page=pedidos" class="btn btn-outline-secondary btn-sm">
                <i class="fa-solid fa-arrow-left me-1"></i>Volver a pedidos
            </a>
        </div>
        <div class="table-responsive">
            <table class="table table-hover table-striped align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Fecha</th>
                        <th>Usuario</th>
                        <th>Acción</th>
                        <th>Detalle</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($eventos)): ?>
                        <?php foreach ($eventos as $ev): ?>
                            <tr>
                                <td class="text-nowrap"><?= htmlspecialchars((string) $ev['fecha']) ?></td>
                                <td><?= htmlspecialchars((string) ($ev['usuario_nombre'] ?? '—')) ?></td>
                                <td><code class="small"><?= htmlspecialchars((string) $ev['accion']) ?></code></td>
                                <td class="small text-muted"><?= htmlspecialchars((string) ($ev['detalle'] ?? '')) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="4" class="text-center text-muted py-4">No hay eventos registrados para este pedido.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/main.php';
