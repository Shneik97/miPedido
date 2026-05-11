<?php
// Paso 1 (simulado): el usuario escribe su correo para generar codigo de recuperacion.
$pageTitle = 'Recuperar contraseña';
$showSidebar = false;
$currentNav = '';
$forgotError = $_SESSION['forgot_error'] ?? null;
unset($_SESSION['forgot_error']);
ob_start();
?>
<div class="row justify-content-center w-100">
    <div class="col-12 col-md-6 col-lg-5 col-xl-4">
        <div class="card page-card shadow">
            <div class="card-body p-4">
                <div class="text-center mb-4">
                    <i class="fa-solid fa-key fa-2x text-primary mb-2"></i>
                    <h1 class="h4 mb-1">Recuperar contraseña</h1>
                    <p class="text-muted small mb-0">Recuperación por código</p>
                </div>
                <?php if (!empty($forgotError)): ?>
                    <div class="alert alert-danger py-2 small"><?= htmlspecialchars($forgotError) ?></div>
                <?php endif; ?>
                <form method="post" action="index.php?page=forgot_password">
                    <?= csrfInput() ?>
                    <div class="mb-3">
                        <label for="email" class="form-label">Correo de tu cuenta</label>
                        <input type="email" class="form-control" id="email" name="email" required>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Generar código</button>
                </form>
                <div class="text-center mt-3">
                    <a href="index.php?page=login" class="small text-decoration-none">Volver a iniciar sesión</a>
                </div>
            </div>
        </div>
    </div>
</div>
<?php
$content = ob_get_clean();
require __DIR__ . '/layouts/main.php';
