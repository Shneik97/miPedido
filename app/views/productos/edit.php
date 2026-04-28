<?php
$pageTitle = 'Editar producto';
$currentNav = 'productos';
ob_start();
?>
<div class="card page-card">
    <div class="card-body">
        <h1 class="h4 mb-4">Editar producto</h1>
        <form action="index.php?page=productos_update" method="POST" class="col-lg-8">
            <?= csrfInput() ?>
            <input type="hidden" name="id" value="<?= (int) $p['id'] ?>">
            <div class="mb-3">
                <label for="nombre" class="form-label">Nombre</label>
                <input type="text" name="nombre" id="nombre" class="form-control" value="<?= htmlspecialchars($p['nombre']) ?>" required>
            </div>
            <div class="mb-3">
                <label for="descripcion" class="form-label">Descripción</label>
                <textarea name="descripcion" id="descripcion" class="form-control" rows="3"><?= htmlspecialchars((string) $p['descripcion']) ?></textarea>
            </div>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="precio" class="form-label">Precio (€)</label>
                    <input type="number" name="precio" id="precio" class="form-control" step="0.01" min="0"
                           value="<?= htmlspecialchars((string) $p['precio']) ?>" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label for="stock" class="form-label">Stock</label>
                    <input type="number" name="stock" id="stock" class="form-control" min="0"
                           value="<?= (int) $p['stock'] ?>" required>
                </div>
            </div>
            <button type="submit" class="btn btn-primary"><i class="fa-solid fa-check me-1"></i>Actualizar</button>
            <a href="index.php?page=productos" class="btn btn-outline-secondary">Cancelar</a>
        </form>
    </div>
</div>
<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/main.php';
