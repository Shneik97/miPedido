<?php
$total = 0.0;
foreach ($lineas as $ln) {
    $total += (float) $ln['cantidad'] * (float) $ln['precio_unitario'];
}
$m = (string) ($cab['metodo_pago'] ?? '');
$metodoMap = ['efectivo' => 'Efectivo', 'tarjeta' => 'Tarjeta', 'transferencia' => 'Transferencia'];
$metodo = $metodoMap[$m] ?? $m;
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
        .invoice {
            max-width: 720px;
            margin: 0 auto;
        }
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
        .actions { margin-top: 1.5rem; }
        .btn-print {
            display: inline-block;
            padding: 0.5rem 1rem;
            background: #2563eb;
            color: #fff;
            border: none;
            border-radius: 0.375rem;
            cursor: pointer;
            font-size: 0.9rem;
        }
        .btn-print:hover { background: #1d4ed8; }
        @media print {
            body { padding: 0; }
            .actions { display: none; }
            .btn-print { display: none; }
        }
    </style>
</head>
<body>
<div class="invoice">
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
                    <?php
                    $sub = (float) $ln['cantidad'] * (float) $ln['precio_unitario'];
                    ?>
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
        <button type="button" class="btn-print" onclick="window.print()">Imprimir / PDF</button>
    </div>
</div>
</body>
</html>
