<?php
$pageTitle = 'Nuevo pedido';
$currentNav = 'pedidos';
ob_start();
?>
<div class="card page-card">
    <div class="card-body">
        <h1 class="h4 mb-4">Registrar pedido</h1>

        <?php if (!empty($error)): ?>
            <div class="alert alert-danger py-2 small">
                <?php if ($error === 'stock'): ?>
                    Stock insuficiente para la cantidad solicitada.
                <?php elseif ($error === 'invalid'): ?>
                    Datos no válidos. Selecciona cliente y producto.
                <?php else: ?>
                    No se pudo crear el pedido.
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <form action="index.php?page=pedidos_store" method="POST" class="col-lg-8">
            <div class="mb-3">
                <label for="cliente_id" class="form-label">Cliente</label>
                <select name="cliente_id" id="cliente_id" class="form-select" required>
                    <option value="">— Seleccionar cliente —</option>
                    <?php foreach ($clientes as $c): ?>
                        <option value="<?= (int) $c['id'] ?>"><?= htmlspecialchars($c['nombre']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="mb-3">
                <label for="producto_id" class="form-label">Producto</label>
                <select name="producto_id" id="producto_id" class="form-select" required>
                    <option value="">— Seleccionar producto —</option>
                    <?php foreach ($productos as $p): ?>
                        <option value="<?= (int) $p['id'] ?>">
                            <?= htmlspecialchars($p['nombre']) ?> — <?= number_format((float) $p['precio'], 2, ',', '.') ?> € (stock: <?= (int) $p['stock'] ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="mb-3">
                <label for="cantidad" class="form-label">Cantidad</label>
                <input type="number" name="cantidad" id="cantidad" class="form-control" min="1" value="1" required>
            </div>
            <div class="mb-4">
                <label for="metodo_pago" class="form-label">Método de pago</label>
                <select name="metodo_pago" id="metodo_pago" class="form-select" required>
                    <option value="efectivo">Efectivo</option>
                    <option value="tarjeta">Tarjeta</option>
                    <option value="transferencia">Transferencia</option>
                </select>
            </div>
            <button type="submit" class="btn btn-success"><i class="fa-solid fa-check me-1"></i>Confirmar pedido</button>
            <a href="index.php?page=pedidos" class="btn btn-outline-secondary">Cancelar</a>
        </form>
    </div>
</div>
<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/main.php';
