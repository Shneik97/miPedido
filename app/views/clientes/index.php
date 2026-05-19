<?php
// Listado de clientes con acciones CRUD visibles según rol.
$pageTitle = 'Clientes';
$currentNav = 'clientes';
ob_start();
?>
<div class="card page-card">
    <div class="card-body">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
            <h1 class="h4 mb-0">Clientes</h1>
            <?php if (usuarioTienePermiso('clientes_gestionar')): ?>
            <a href="index.php?page=clientes_create" class="btn btn-success">
                <i class="fa-solid fa-plus me-1"></i>Nuevo cliente
            </a>
            <?php endif; ?>
        </div>
        <div class="table-responsive">
            <table class="table table-hover table-striped align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>ID</th>
                        <th>Nombre</th>
                        <th>Teléfono</th>
                        <th>Email</th>
                        <th>Dirección</th>
                        <th class="text-end" style="width: 8rem;">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($clientes)): ?>
                        <?php foreach ($clientes as $cliente): ?>
                            <tr>
                                <td><?= (int) $cliente['id'] ?></td>
                                <td><?= htmlspecialchars($cliente['nombre']) ?></td>
                                <td><?= htmlspecialchars((string) $cliente['telefono']) ?></td>
                                <td><?= htmlspecialchars((string) $cliente['email']) ?></td>
                                <td><?= htmlspecialchars((string) $cliente['direccion']) ?></td>
                                <td class="text-end text-nowrap">
                                    <?php if (usuarioTienePermiso('clientes_gestionar')): ?>
                                    <a href="index.php?page=clientes_edit&id=<?= (int) $cliente['id'] ?>"
                                       class="btn btn-sm btn-outline-primary"
                                       title="Editar">
                                        <i class="fa-solid fa-pen"></i>
                                    </a>
                                    <?php endif; ?>
                                    <?php if (usuarioTienePermiso('clientes_gestionar')): ?>
                                    <form method="post" action="index.php?page=clientes_delete" class="d-inline js-confirm-delete" data-confirm-message="¿Eliminar este cliente?">
                                        <?= csrfInput() ?>
                                        <input type="hidden" name="id" value="<?= (int) $cliente['id'] ?>">
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
                            <td colspan="6" class="text-center text-muted py-4">No hay clientes registrados.</td>
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
