<?php
$pageTitle = 'Usuarios';
$currentNav = 'usuarios';
$ok = $_GET['ok'] ?? '';
$err = $_GET['error'] ?? '';
$okMsg = [
    'creado' => 'Usuario creado correctamente.',
    'actualizado' => 'Usuario actualizado.',
    'eliminado' => 'Usuario eliminado.',
];
$errMsg = [
    'no_propio' => 'No puedes eliminar tu propia cuenta.',
    'ultimo_admin' => 'No se puede eliminar o degradar al único administrador del sistema.',
];
$rolEtiqueta = static function (string $rol): string {
    return $rol === 'admin' ? 'Administrador' : 'Empleado';
};
$miUsuarioId = (int) ($_SESSION['usuario']['id'] ?? 0);
ob_start();
?>
<div class="card page-card">
    <div class="card-body">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
            <h1 class="h4 mb-0">Gestión de personal</h1>
            <a href="index.php?page=usuarios_create" class="btn btn-success">
                <i class="fa-solid fa-user-plus me-1"></i>Nuevo usuario
            </a>
        </div>
        <?php if ($ok !== '' && isset($okMsg[$ok])): ?>
            <div class="alert alert-success"><?= htmlspecialchars($okMsg[$ok]) ?></div>
        <?php endif; ?>
        <?php if ($err !== '' && isset($errMsg[$err])): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($errMsg[$err]) ?></div>
        <?php endif; ?>
        <p class="text-muted small mb-3">Alta, edición y baja de cuentas. Solo los administradores acceden a este módulo.</p>
        <div class="table-responsive">
            <table class="table table-hover table-striped align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>ID</th>
                        <th>Nombre</th>
                        <th>Email</th>
                        <th>Rol</th>
                        <th class="text-end" style="width: 8rem;">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($usuarios)): ?>
                        <tr><td colspan="5" class="text-center text-muted py-4">No hay usuarios.</td></tr>
                    <?php else: ?>
                        <?php foreach ($usuarios as $u): ?>
                            <tr>
                                <td><?= (int) $u['id'] ?></td>
                                <td><?= htmlspecialchars($u['nombre']) ?></td>
                                <td><?= htmlspecialchars($u['email']) ?></td>
                                <td>
                                    <span class="badge bg-<?= $u['rol'] === 'admin' ? 'primary' : 'secondary' ?>">
                                        <?= htmlspecialchars($rolEtiqueta((string) $u['rol'])) ?>
                                    </span>
                                </td>
                                <td class="text-end text-nowrap">
                                    <a href="index.php?page=usuarios_edit&id=<?= (int) $u['id'] ?>"
                                       class="btn btn-sm btn-outline-primary" title="Editar">
                                        <i class="fa-solid fa-pen"></i>
                                    </a>
                                    <?php if ((int) $u['id'] !== $miUsuarioId): ?>
                                    <a href="index.php?page=usuarios_delete&id=<?= (int) $u['id'] ?>"
                                       class="btn btn-sm btn-outline-danger js-confirm-delete"
                                       title="Eliminar"
                                       data-confirm-message="¿Eliminar este usuario?">
                                        <i class="fa-solid fa-trash"></i>
                                    </a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/main.php';
