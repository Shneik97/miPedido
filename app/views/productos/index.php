<?php
// Listado de productos con stock/precio y acciones según rol.
$pageTitle = 'Productos';
$currentNav = 'productos';
ob_start();
?>
<div class="card page-card">
    <div class="card-body">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
            <h1 class="h4 mb-0">Productos</h1>
            <?php if (!empty($isAdmin)): ?>
            <a href="index.php?page=productos_create" class="btn btn-success">
                <i class="fa-solid fa-plus me-1"></i>Nuevo producto
            </a>
            <?php endif; ?>
        </div>
        <div class="table-responsive">
            <table class="table table-hover table-striped align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>ID</th>
                        <th>Nombre</th>
                        <th>Descripción</th>
                        <th class="text-end">Precio</th>
                        <th class="text-end">Stock</th>
                        <th class="text-end" style="width: 8rem;">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($productos)): ?>
                        <?php foreach ($productos as $row): ?>
                            <tr>
                                <td><?= (int) $row['id'] ?></td>
                                <td><?= htmlspecialchars($row['nombre']) ?></td>
                                <td class="text-muted small"><?= htmlspecialchars((string) $row['descripcion']) ?></td>
                                <td class="text-end"><?= number_format((float) $row['precio'], 2, ',', '.') ?> €</td>
                                <td class="text-end"><?= (int) $row['stock'] ?></td>
                                <td class="text-end text-nowrap">
                                    <?php if (!empty($isAdmin)): ?>
                                    <a href="index.php?page=productos_edit&id=<?= (int) $row['id'] ?>"
                                       class="btn btn-sm btn-outline-primary"
                                       title="Editar">
                                        <i class="fa-solid fa-pen"></i>
                                    </a>
                                    <?php endif; ?>
                                    <?php if (!empty($isAdmin)): ?>
                                    <form method="post" action="index.php?page=productos_delete" class="d-inline js-confirm-delete" data-confirm-message="¿Eliminar este producto?">
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
                            <td colspan="6" class="text-center text-muted py-4">No hay productos registrados.</td>
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
