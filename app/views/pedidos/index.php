<?php
$pageTitle = 'Pedidos';
$currentNav = 'pedidos';
ob_start();

$metodosMap = [
    'efectivo' => 'Efectivo',
    'tarjeta' => 'Tarjeta',
    'transferencia' => 'Transferencia',
];
$estadosMap = [
    'pendiente' => 'Pendiente',
    'en_proceso' => 'En proceso',
    'enviado' => 'Enviado',
    'entregado' => 'Entregado',
    'cancelado' => 'Cancelado',
];
?>
<div class="card page-card">
    <div class="card-body">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
            <h1 class="h4 mb-0">Pedidos</h1>
            <a href="index.php?page=pedidos_create" class="btn btn-success">
                <i class="fa-solid fa-plus me-1"></i>Nuevo pedido
            </a>
        </div>
        <div class="table-responsive">
            <table class="table table-hover table-striped align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>ID</th>
                        <th>Cliente</th>
                        <th>Fecha</th>
                        <th>Estado</th>
                        <th>Pago</th>
                        <th class="text-end">Total</th>
                        <th class="text-end" style="width: 11rem;">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($pedidos)): ?>
                        <?php foreach ($pedidos as $row): ?>
                            <tr>
                                <td><?= (int) $row['id'] ?></td>
                                <td><?= htmlspecialchars((string) $row['cliente_nombre']) ?></td>
                                <td><?= htmlspecialchars((string) $row['fecha']) ?></td>
                                <td><?= htmlspecialchars($estadosMap[(string) $row['estado']] ?? (string) $row['estado']) ?></td>
                                <td><?= htmlspecialchars($metodosMap[(string) $row['metodo_pago']] ?? (string) $row['metodo_pago']) ?></td>
                                <td class="text-end"><?= number_format((float) $row['total'], 2, ',', '.') ?> €</td>
                                <td class="text-end text-nowrap">
                                    <button type="button" class="btn btn-sm btn-outline-secondary btn-factura"
                                            data-factura-url="index.php?page=pedido_factura&id=<?= (int) $row['id'] ?>"
                                            title="Generar factura">
                                        <i class="fa-solid fa-file-invoice"></i>
                                    </button>
                                    <?php if (!empty($isAdmin)): ?>
                                    <a href="index.php?page=pedidos_delete&id=<?= (int) $row['id'] ?>"
                                       class="btn btn-sm btn-outline-danger js-confirm-delete"
                                       title="Eliminar"
                                       data-confirm-message="¿Eliminar este pedido?">
                                        <i class="fa-solid fa-trash"></i>
                                    </a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">No hay pedidos registrados.</td>
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
