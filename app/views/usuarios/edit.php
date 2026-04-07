<?php
$pageTitle = 'Editar usuario';
$currentNav = 'usuarios';
$err = $_GET['error'] ?? '';
$errMsg = [
    'invalido' => 'Completa nombre y un email válido.',
    'email_duplicado' => 'Ya existe otro usuario con ese email.',
    'password_corta' => 'La nueva contraseña debe tener al menos 6 caracteres.',
    'ultimo_admin' => 'Debe quedar al menos un administrador en el sistema.',
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
            <button type="submit" class="btn btn-primary"><i class="fa-solid fa-floppy-disk me-1"></i>Actualizar</button>
            <a href="index.php?page=usuarios" class="btn btn-outline-secondary">Cancelar</a>
        </form>
    </div>
</div>
<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/main.php';
