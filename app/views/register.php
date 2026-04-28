<?php
$pageTitle = 'Crear cuenta';
$showSidebar = false;
$currentNav = '';
$registerError = $_SESSION['register_error'] ?? null;
unset($_SESSION['register_error']);
ob_start();
?>
<div class="row justify-content-center w-100">
    <div class="col-12 col-md-7 col-lg-6 col-xl-5">
        <div class="card page-card shadow">
            <div class="card-body p-4">
                <?php if (!empty($registerError)): ?>
                    <div class="alert alert-danger py-2 small" role="alert"><?= htmlspecialchars($registerError) ?></div>
                <?php endif; ?>
                <div class="text-center mb-4">
                    <i class="fa-solid fa-user-plus fa-3x text-primary mb-2"></i>
                    <h1 class="h4 mb-0">Crear cuenta</h1>
                    <p class="text-muted small mb-0">Empieza con tu acceso a miPedido</p>
                </div>

                <form method="post" action="index.php?page=register_store">
                    <?= csrfInput() ?>
                    <div class="mb-3">
                        <label for="nombre" class="form-label">Nombre completo</label>
                        <input type="text" class="form-control" id="nombre" name="nombre" required maxlength="100">
                    </div>
                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="email" name="email" required maxlength="100" autocomplete="email">
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">Contraseña</label>
                        <input type="password" class="form-control" id="password" name="password" required minlength="8" autocomplete="new-password">
                        <div class="form-text">Mínimo 8 caracteres.</div>
                    </div>
                    <div class="mb-4">
                        <label for="password_confirm" class="form-label">Repetir contraseña</label>
                        <input type="password" class="form-control" id="password_confirm" name="password_confirm" required minlength="8" autocomplete="new-password">
                    </div>

                    <button type="submit" class="btn btn-primary w-100 py-2 mb-3">
                        <i class="fa-solid fa-check me-2"></i>Crear cuenta
                    </button>
                </form>

                <a href="index.php?page=auth_google_start" class="btn btn-outline-dark w-100 py-2 mb-3">
                    <i class="fa-brands fa-google me-2"></i>Registrarme con Google (Auth0)
                </a>

                <div class="text-center">
                    <a href="index.php?page=login" class="small text-decoration-none">Ya tengo cuenta</a>
                    <span class="mx-1 text-muted">·</span>
                    <a href="index.php?page=home" class="small text-decoration-none">Volver a presentación</a>
                </div>
            </div>
        </div>
    </div>
</div>
<?php
$content = ob_get_clean();
require __DIR__ . '/layouts/main.php';
