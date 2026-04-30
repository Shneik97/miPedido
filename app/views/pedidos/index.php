<?php
/**
 * Listado de pedidos: acciones factura, WhatsApp inteligente, marcar realizado, historial.
 * Nivel estudiante:
 * - Se preparan etiquetas y enlaces en PHP.
 * - El HTML solo pinta tabla y botones.
 */
require_once __DIR__ . '/../../helpers/whatsapp_helper.php';

$pageTitle = 'Pedidos';
$currentNav = 'pedidos';
$okMsg = $_GET['ok'] ?? '';
$errMsg = $_GET['error'] ?? '';
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
    'realizado' => 'Realizado',
];
// Idea estudiante: este bloque traduce valores internos de BD a textos bonitos de la interfaz.
?>
<div class="card page-card">
    <div class="card-body">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
            <h1 class="h4 mb-0">Pedidos</h1>
            <a href="index.php?page=pedidos_create" class="btn btn-success">
                <i class="fa-solid fa-plus me-1"></i>Nuevo pedido
            </a>
        </div>
        <?php if ($okMsg === 'realizado'): ?>
            <div class="alert alert-success py-2 small mb-3" role="alert">Pedido marcado como <strong>realizado</strong>.</div>
        <?php endif; ?>
        <?php if ($okMsg === 'delete'): ?>
            <div class="alert alert-success py-2 small mb-3" role="alert">Pedido eliminado y stock repuesto correctamente.</div>
        <?php endif; ?>
        <?php if ($errMsg === 'realizado'): ?>
            <div class="alert alert-warning py-2 small mb-3" role="alert">No se pudo marcar como realizado (ya estaba cancelado/realizado o hubo un error).</div>
        <?php endif; ?>
        <?php if ($errMsg === 'delete'): ?>
            <div class="alert alert-warning py-2 small mb-3" role="alert">No se pudo eliminar el pedido (si está realizado no se permite borrar).</div>
        <?php endif; ?>
        <?php if ($errMsg === 'csrf'): ?>
            <div class="alert alert-warning py-2 small mb-3" role="alert">Acción no válida o caducada. Recarga la página e inténtalo de nuevo.</div>
        <?php endif; ?>
        <div class="table-responsive">
            <table class="table table-hover table-striped align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>ID</th>
                        <th>Cliente</th>
                        <th>Fecha</th>
                        <th>Estado</th>
                        <th>Realizado el</th>
                        <th>Pago</th>
                        <th class="text-end">Total</th>
                        <th class="text-end" style="min-width: 14rem;">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($pedidos)): ?>
                        <?php foreach ($pedidos as $row): ?>
                            <?php
                            // Variables por fila para no repetir logica en varios botones.
                            $est = (string) $row['estado'];
                            $puedeRealizado = ($est !== 'cancelado' && $est !== 'realizado');
                            $telNorm = normalizarTelefonoEspana((string) ($row['cliente_telefono'] ?? ''));
                            $facturaUrl = 'index.php?page=pedido_factura&id=' . (int) $row['id'];
                            $waText = textoWhatsappPedidoCorto($row, $facturaUrl);
                            $waHref = $telNorm !== null ? waMeUrl($telNorm, $waText) : '';
                            $filaRealizado = ($est === 'realizado');
                            ?>
                            <tr class="<?= $filaRealizado ? 'table-success' : '' ?>">
                                <td><?= (int) $row['id'] ?></td>
                                <td><?= htmlspecialchars((string) $row['cliente_nombre']) ?></td>
                                <td><?= htmlspecialchars((string) $row['fecha']) ?></td>
                                <td><?= htmlspecialchars($estadosMap[$est] ?? $est) ?></td>
                                <td class="small text-muted"><?= !empty($row['fecha_realizado']) ? htmlspecialchars((string) $row['fecha_realizado']) : '—' ?></td>
                                <td><?= htmlspecialchars($metodosMap[(string) $row['metodo_pago']] ?? (string) $row['metodo_pago']) ?></td>
                                <td class="text-end"><?= number_format((float) $row['total'], 2, ',', '.') ?> €</td>
                                <td class="text-end text-nowrap">
                                    <button type="button" class="btn btn-sm btn-outline-secondary btn-factura"
                                            data-factura-url="index.php?page=pedido_factura&id=<?= (int) $row['id'] ?>"
                                            title="Generar factura">
                                        <i class="fa-solid fa-file-invoice"></i>
                                    </button>
                                    <?php if ($waHref !== ''): ?>
                                        <a class="btn btn-sm btn-success js-open-wa" href="<?= htmlspecialchars($waHref) ?>"
                                           data-wa-phone="<?= htmlspecialchars((string) $telNorm, ENT_QUOTES, 'UTF-8') ?>"
                                           data-wa-json="<?= htmlspecialchars(json_encode($waText, JSON_UNESCAPED_UNICODE), ENT_QUOTES, 'UTF-8') ?>"
                                           title="Enviar por WhatsApp (intenta app y si no, WhatsApp Web)">
                                            <i class="fa-brands fa-whatsapp"></i>
                                        </a>
                                        <button type="button" class="btn btn-sm btn-outline-success js-copy-wa-text"
                                                data-wa-json="<?= htmlspecialchars(json_encode($waText, JSON_UNESCAPED_UNICODE), ENT_QUOTES, 'UTF-8') ?>"
                                                title="Copiar texto del mensaje">
                                            <i class="fa-regular fa-copy"></i>
                                        </button>
                                    <?php else: ?>
                                        <span class="btn btn-sm btn-outline-secondary disabled" title="Sin teléfono válido del cliente">
                                            <i class="fa-brands fa-whatsapp"></i>
                                        </span>
                                    <?php endif; ?>
                                    <?php if ($puedeRealizado): ?>
                                        <form method="post" action="index.php?page=pedidos_marcar_realizado" class="d-inline js-confirm-realizado" data-confirm-message="¿Marcar este pedido como REALIZADO? Se guardará la fecha de confirmación.">
                                            <?= csrfInput() ?>
                                            <input type="hidden" name="id" value="<?= (int) $row['id'] ?>">
                                            <button type="submit" class="btn btn-sm btn-outline-primary" title="Marcar como realizado">
                                                <i class="fa-solid fa-circle-check"></i>
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                    <a href="index.php?page=pedidos_historial&id=<?= (int) $row['id'] ?>"
                                       class="btn btn-sm btn-outline-info" title="Historial">
                                        <i class="fa-solid fa-clock-rotate-left"></i>
                                    </a>
                                    <?php if (!empty($isAdmin) && $est !== 'realizado'): ?>
                                    <!-- Regla de negocio: si ya esta realizado, no se permite borrar. -->
                                    <form method="post" action="index.php?page=pedidos_delete&id=<?= (int) $row['id'] ?>" class="d-inline js-confirm-delete" data-confirm-message="¿Eliminar este pedido?">
                                        <?= csrfInput() ?>
                                        <input type="hidden" name="id" value="<?= (int) $row['id'] ?>">
                                        <button type="submit" class="btn btn-sm btn-outline-danger" title="Eliminar">
                                            <i class="fa-solid fa-trash"></i>
                                        </button>
                                    </form>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" class="text-center text-muted py-4">No hay pedidos registrados.</td>
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
