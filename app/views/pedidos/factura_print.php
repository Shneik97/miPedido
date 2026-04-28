<?php
$total = 0.0;
foreach ($lineas as $ln) {
    $total += (float) $ln['cantidad'] * (float) $ln['precio_unitario'];
}
$m = (string) ($cab['metodo_pago'] ?? '');
$metodoMap = ['efectivo' => 'Efectivo', 'tarjeta' => 'Tarjeta', 'transferencia' => 'Transferencia'];
$metodo = $metodoMap[$m] ?? $m;
$esRealizada = ((string) ($cab['estado'] ?? '') === 'realizado');
?><!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Factura #<?= (int) $cab['id'] ?> — miPedido</title>
    <style>
        * { box-sizing: border-box; }
        body {
            font-family: system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            color: #0f172a;
            margin: 0;
            padding: 2rem;
            background: #fff;
        }
        .invoice { max-width: 720px; margin: 0 auto; }
        .header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            border-bottom: 2px solid #1e293b;
            padding-bottom: 1rem;
            margin-bottom: 1.5rem;
        }
        .brand { font-size: 1.35rem; font-weight: 700; color: #1e293b; }
        .brand small { display: block; font-weight: 400; color: #64748b; font-size: 0.85rem; }
        .meta { text-align: right; font-size: 0.9rem; color: #475569; }
        .block { margin-bottom: 1.25rem; }
        .block h2 { font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.06em; color: #94a3b8; margin: 0 0 0.35rem; }
        table { width: 100%; border-collapse: collapse; font-size: 0.95rem; }
        th, td { padding: 0.6rem 0.5rem; text-align: left; border-bottom: 1px solid #e2e8f0; }
        th { background: #f8fafc; font-weight: 600; font-size: 0.8rem; text-transform: uppercase; color: #64748b; }
        td.num { text-align: right; }
        .total-row td { font-weight: 700; font-size: 1.1rem; border-bottom: none; padding-top: 1rem; }
        .actions { margin-top: 1.5rem; display: flex; align-items: center; gap: 0.5rem; flex-wrap: wrap; }
        .btn-print, .btn-edit, .btn-save {
            display: inline-block;
            padding: 0.5rem 1rem;
            color: #fff;
            border: none;
            border-radius: 0.375rem;
            cursor: pointer;
            font-size: 0.9rem;
            text-decoration: none;
        }
        .btn-print { background: #2563eb; }
        .btn-print:hover { background: #1d4ed8; }
        .btn-edit { background: #0f766e; }
        .btn-edit:hover { background: #115e59; }
        .btn-save { background: #16a34a; }
        .btn-save:hover { background: #15803d; }
        .btn-cancel {
            display: inline-block;
            padding: 0.5rem 1rem;
            border: 1px solid #cbd5e1;
            color: #0f172a;
            border-radius: 0.375rem;
            font-size: 0.9rem;
            text-decoration: none;
        }
        .alert {
            margin-bottom: 1rem;
            border-radius: 0.4rem;
            padding: 0.6rem 0.8rem;
            font-size: 0.9rem;
        }
        .alert-ok { background: #dcfce7; color: #166534; border: 1px solid #86efac; }
        .alert-error { background: #fef3c7; color: #92400e; border: 1px solid #fcd34d; }
        .lock-note { color: #64748b; font-size: 0.85rem; margin: 0; }
        input[type="number"], select {
            width: 100%;
            padding: 0.4rem;
            border: 1px solid #cbd5e1;
            border-radius: 0.35rem;
            font: inherit;
        }
        @media print {
            body { padding: 0; }
            .actions, .btn-print, .btn-edit, .btn-save, .btn-cancel, .alert { display: none; }
        }
    </style>
</head>
<body>
<div class="invoice">
    <?php if (($ok ?? '') === 'editado'): ?>
        <div class="alert alert-ok">Factura actualizada correctamente.</div>
    <?php endif; ?>
    <?php if (($error ?? '') === 'bloqueada'): ?>
        <div class="alert alert-error">No se puede editar porque la factura ya está realizada.</div>
    <?php elseif (($error ?? '') === 'csrf'): ?>
        <div class="alert alert-error">El formulario caducó. Recarga e inténtalo de nuevo.</div>
    <?php endif; ?>

    <div class="header">
        <div class="brand">
            ERP miPedido
            <small>Factura simplificada</small>
        </div>
        <div class="meta">
            <strong>Pedido #<?= (int) $cab['id'] ?></strong><br>
            Fecha: <?= htmlspecialchars((string) $cab['fecha']) ?><br>
            Estado: <?= htmlspecialchars((string) $cab['estado']) ?>
        </div>
    </div>

    <div class="block">
        <h2>Cliente</h2>
        <p style="margin:0;">
            <strong><?= htmlspecialchars((string) $cab['cliente_nombre']) ?></strong><br>
            <?php if (!empty($cab['cliente_email'])): ?><?= htmlspecialchars((string) $cab['cliente_email']) ?><br><?php endif; ?>
            <?php if (!empty($cab['cliente_telefono'])): ?><?= htmlspecialchars((string) $cab['cliente_telefono']) ?><br><?php endif; ?>
            <?php if (!empty($cab['cliente_direccion'])): ?><?= htmlspecialchars((string) $cab['cliente_direccion']) ?><?php endif; ?>
        </p>
    </div>

    <?php if (!empty($editMode) && !$esRealizada): ?>
        <form method="post" action="index.php?page=pedido_factura_guardar">
            <?= csrfInput() ?>
            <input type="hidden" name="id" value="<?= (int) $cab['id'] ?>">

            <div class="block">
                <h2>Pago</h2>
                <select name="metodo_pago">
                    <option value="efectivo" <?= $m === 'efectivo' ? 'selected' : '' ?>>Efectivo</option>
                    <option value="tarjeta" <?= $m === 'tarjeta' ? 'selected' : '' ?>>Tarjeta</option>
                    <option value="transferencia" <?= $m === 'transferencia' ? 'selected' : '' ?>>Transferencia</option>
                </select>
            </div>

            <div class="block">
                <h2>Detalle</h2>
                <table>
                    <thead>
                    <tr>
                        <th>Producto</th>
                        <th class="num">Cantidad</th>
                        <th class="num">P. unitario</th>
                        <th class="num">Importe actual</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($lineas as $ln): ?>
                        <?php $sub = (float) $ln['cantidad'] * (float) $ln['precio_unitario']; ?>
                        <tr>
                            <td><?= htmlspecialchars((string) $ln['producto_nombre']) ?></td>
                            <td class="num">
                                <input type="number" min="1" name="linea_cantidad[<?= (int) $ln['id'] ?>]" value="<?= (int) $ln['cantidad'] ?>">
                            </td>
                            <td class="num">
                                <input type="number" min="0" step="0.01" name="linea_precio[<?= (int) $ln['id'] ?>]" value="<?= htmlspecialchars((string) $ln['precio_unitario']) ?>">
                            </td>
                            <td class="num"><?= number_format($sub, 2, ',', '.') ?> €</td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                    <tr class="total-row">
                        <td colspan="3">Total actual</td>
                        <td class="num"><?= number_format($total, 2, ',', '.') ?> €</td>
                    </tr>
                    </tfoot>
                </table>
            </div>

            <div class="actions">
                <button type="submit" class="btn-save">Guardar cambios</button>
                <a href="index.php?page=pedido_factura&id=<?= (int) $cab['id'] ?>" class="btn-cancel">Cancelar</a>
                <button type="button" class="btn-print" onclick="window.print()">Imprimir / PDF</button>
            </div>
        </form>
    <?php else: ?>
        <div class="block">
            <h2>Pago</h2>
            <p style="margin:0;"><?= htmlspecialchars($metodo) ?></p>
        </div>

        <div class="block">
            <h2>Detalle</h2>
            <table>
                <thead>
                <tr>
                    <th>Producto</th>
                    <th class="num">Cantidad</th>
                    <th class="num">P. unitario</th>
                    <th class="num">Importe</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($lineas as $ln): ?>
                    <?php $sub = (float) $ln['cantidad'] * (float) $ln['precio_unitario']; ?>
                    <tr>
                        <td><?= htmlspecialchars((string) $ln['producto_nombre']) ?></td>
                        <td class="num"><?= (int) $ln['cantidad'] ?></td>
                        <td class="num"><?= number_format((float) $ln['precio_unitario'], 2, ',', '.') ?> €</td>
                        <td class="num"><?= number_format($sub, 2, ',', '.') ?> €</td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
                <tfoot>
                <tr class="total-row">
                    <td colspan="3">Total</td>
                    <td class="num"><?= number_format($total, 2, ',', '.') ?> €</td>
                </tr>
                </tfoot>
            </table>
        </div>

        <div class="actions">
            <?php if (!$esRealizada): ?>
                <a class="btn-edit" href="index.php?page=pedido_factura&id=<?= (int) $cab['id'] ?>&edit=1">Editar</a>
            <?php endif; ?>
            <button type="button" class="btn-print" onclick="window.print()">Imprimir / PDF</button>
            <?php if ($esRealizada): ?>
                <p class="lock-note">Factura bloqueada: este pedido ya está realizado.</p>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>
</body>
</html>
