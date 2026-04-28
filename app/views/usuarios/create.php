<?php
$pageTitle = 'Nuevo usuario';
$currentNav = 'usuarios';
$err = $_GET['error'] ?? '';
$errMsg = [
    'invalido' => 'Completa nombre y un email válido.',
    'email_duplicado' => 'Ya existe un usuario con ese email.',
    'password_corta' => 'La contraseña debe tener al menos 6 caracteres.',
    'csrf' => 'La sesión del formulario ha caducado. Recarga la página e inténtalo de nuevo.',
];
ob_start();
?>
<div class="card page-card">
    <div class="card-body">
        <h1 class="h4 mb-4">Nuevo usuario</h1>
        <?php if ($err !== '' && isset($errMsg[$err])): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($errMsg[$err]) ?></div>
        <?php endif; ?>
        <form action="index.php?page=usuarios_store" method="post" class="col-lg-8" autocomplete="off">
            <?= csrfInput() ?>
            <div class="mb-3">
                <label for="nombre" class="form-label">Nombre</label>
                <input type="text" name="nombre" id="nombre" class="form-control" required maxlength="100">
            </div>
            <div class="mb-3">
                <label for="email" class="form-label">Email <span class="text-muted">(inicio de sesión)</span></label>
                <input type="email" name="email" id="email" class="form-control" required maxlength="100">
            </div>
            <div class="mb-3">
                <label for="password" class="form-label">Contraseña</label>
                <input type="password" name="password" id="password" class="form-control" required minlength="6" autocomplete="new-password">
            </div>
            <div class="mb-4">
                <label for="rol" class="form-label">Rol</label>
                <select name="rol" id="rol" class="form-select" required>
                    <option value="empleado" selected>Empleado</option>
                    <option value="admin">Administrador</option>
                </select>
            </div>
            <button type="submit" class="btn btn-success"><i class="fa-solid fa-floppy-disk me-1"></i>Guardar</button>
            <a href="index.php?page=usuarios" class="btn btn-outline-secondary">Cancelar</a>
        </form>
    </div>
</div>
<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/main.php';
