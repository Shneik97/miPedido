<?php
// Edición de datos del usuario y sus permisos.
$pageTitle = 'Editar usuario';
$currentNav = 'usuarios';
$err = $_GET['error'] ?? '';
$errMsg = [
    'invalido' => 'Completa nombre y un email válido.',
    'email_duplicado' => 'Ya existe otro usuario con ese email.',
    'password_corta' => 'La nueva contraseña debe tener al menos 6 caracteres.',
    'ultimo_admin' => 'Debe quedar al menos un administrador en el sistema.',
    'csrf' => 'La sesión del formulario ha caducado. Recarga la página e inténtalo de nuevo.',
    'permisos_db' => 'No se pudieron guardar permisos. Revisa si aplicaste la migración de permisos.',
];
ob_start();
?>
<div class="card page-card">
    <div class="card-body">
        <h1 class="h4 mb-4">Editar usuario</h1>
        <?php if ($err !== '' && isset($errMsg[$err])): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($errMsg[$err]) ?></div>
        <?php endif; ?>
        <form action="index.php?page=usuarios_update" method="post" class="col-lg-8" autocomplete="off">
            <?= csrfInput() ?>
            <input type="hidden" name="id" value="<?= (int) $u['id'] ?>">
            <div class="mb-3">
                <label for="nombre" class="form-label">Nombre</label>
                <input type="text" name="nombre" id="nombre" class="form-control" required maxlength="100"
                       value="<?= htmlspecialchars((string) $u['nombre']) ?>">
            </div>
            <div class="mb-3">
                <label for="email" class="form-label">Email <span class="text-muted">(inicio de sesión)</span></label>
                <input type="email" name="email" id="email" class="form-control" required maxlength="100"
                       value="<?= htmlspecialchars((string) $u['email']) ?>">
            </div>
            <div class="mb-3">
                <label for="password_new" class="form-label">Nueva contraseña</label>
                <input type="password" name="password_new" id="password_new" class="form-control" minlength="6" autocomplete="new-password"
                       placeholder="Dejar en blanco para no cambiar">
            </div>
            <div class="mb-4">
                <label for="rol" class="form-label">Rol</label>
                <select name="rol" id="rol" class="form-select" required>
                    <option value="empleado" <?= ($u['rol'] === 'empleado') ? 'selected' : '' ?>>Empleado</option>
                    <option value="admin" <?= ($u['rol'] === 'admin') ? 'selected' : '' ?>>Administrador</option>
                </select>
            </div>
            <div class="mb-4">
                <label class="form-label d-block mb-2">Permisos del usuario</label>
                <p class="text-muted small mb-2">
                    Este bloque lo gestiona el administrador: puedes marcar todos o elegir permisos concretos.
                </p>
                <?php
                $permisosUsuarioMap = [];
                foreach (($permisosUsuario ?? []) as $claveSel) {
                    $permisosUsuarioMap[(string) $claveSel] = true;
                }
                ?>
                <div class="form-check mb-2">
                    <input class="form-check-input" type="checkbox" id="permiso_todos" name="permiso_todos" value="1">
                    <label class="form-check-label fw-semibold" for="permiso_todos">Conceder todos los permisos</label>
                </div>
                <div class="border rounded p-3 bg-light-subtle">
                    <div class="row g-2">
                        <?php if (!empty($catalogoPermisos)): ?>
                            <?php foreach ($catalogoPermisos as $perm): ?>
                                <?php
                                $clave = (string) ($perm['clave'] ?? '');
                                if ($clave === '') { continue; }
                                $isChecked = isset($permisosUsuarioMap[$clave]);
                                ?>
                                <div class="col-md-6">
                                    <div class="form-check">
                                        <input
                                            class="form-check-input js-perm-item"
                                            type="checkbox"
                                            id="perm_<?= htmlspecialchars($clave) ?>"
                                            name="permisos[]"
                                            value="<?= htmlspecialchars($clave) ?>"
                                            <?= $isChecked ? 'checked' : '' ?>
                                        >
                                        <label class="form-check-label" for="perm_<?= htmlspecialchars($clave) ?>">
                                            <span class="fw-medium"><?= htmlspecialchars((string) ($perm['nombre'] ?? $clave)) ?></span>
                                            <?php if (!empty($perm['descripcion'])): ?>
                                                <span class="d-block text-muted small"><?= htmlspecialchars((string) $perm['descripcion']) ?></span>
                                            <?php endif; ?>
                                        </label>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="col-12 text-muted small">No hay permisos de catálogo. Ejecuta la migración de permisos.</div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <button type="submit" class="btn btn-primary"><i class="fa-solid fa-floppy-disk me-1"></i>Actualizar</button>
            <a href="index.php?page=usuarios" class="btn btn-outline-secondary">Cancelar</a>
        </form>
    </div>
</div>
<script>
// Si el admin marca "todos", se marcan todos los checks individuales para simplificar gestión.
document.addEventListener('DOMContentLoaded', function () {
    var chkTodos = document.getElementById('permiso_todos');
    if (!chkTodos) {
        return;
    }
    chkTodos.addEventListener('change', function () {
        document.querySelectorAll('.js-perm-item').forEach(function (el) {
            el.checked = chkTodos.checked;
        });
    });
});
</script>
<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/main.php';
