<?php
// Pantalla de acceso:
// - Login local (email + contraseña)
// - Acceso con Google (Auth0)
// - Enlaces a registro y recuperacion de contraseña
$pageTitle = 'Iniciar sesión';
$showSidebar = false;
$currentNav = '';
$loginError = $_SESSION['login_error'] ?? null;
$registerOk = $_SESSION['register_ok'] ?? null;
unset($_SESSION['login_error']);
unset($_SESSION['register_ok']);
ob_start();
?>
<div class="row justify-content-center w-100">
    <div class="col-12 col-md-6 col-lg-5 col-xl-4">
        <div class="card page-card shadow">
            <div class="card-body p-4">
                <?php if (!empty($loginError)): ?>
                    <div class="alert alert-danger py-2 small" role="alert"><?= htmlspecialchars($loginError) ?></div>
                <?php endif; ?>
                <?php if (!empty($registerOk)): ?>
                    <div class="alert alert-success py-2 small" role="alert"><?= htmlspecialchars($registerOk) ?></div>
                <?php endif; ?>
                <div class="text-center mb-4">
                    <i class="fa-solid fa-cubes fa-3x text-primary mb-2"></i>
                    <h1 class="h4 mb-0">Iniciar sesión</h1>
                    <p class="text-muted small mb-0">ERP miPedido</p>
                </div>
                <form method="POST" action="index.php?page=login">
                    <?= csrfInput() ?>
                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light"><i class="fa-solid fa-envelope text-muted"></i></span>
                            <input type="email" class="form-control" name="email" id="email" required autocomplete="username">
                        </div>
                    </div>
                    <div class="mb-4">
                        <label for="password" class="form-label">Contraseña</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light"><i class="fa-solid fa-lock text-muted"></i></span>
                            <input type="password" class="form-control" name="password" id="password" required autocomplete="current-password">
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary w-100 py-2">
                        <i class="fa-solid fa-right-to-bracket me-2"></i>Entrar
                    </button>
                    <a href="index.php?page=auth_google_start" class="btn btn-outline-dark w-100 py-2 mt-2">
                        <i class="fa-brands fa-google me-2"></i>Ingresar con Google
                    </a>
                </form>
                <div class="text-center mt-3">
                    <a href="index.php?page=forgot_password" class="small text-decoration-none d-block mb-1">Olvidé mi contraseña</a>
                    <a href="index.php?page=register" class="small text-decoration-none">Crear cuenta nueva</a>
                </div>
            </div>
        </div>
    </div>
</div>
<?php
$content = ob_get_clean();
require __DIR__ . '/layouts/main.php';
