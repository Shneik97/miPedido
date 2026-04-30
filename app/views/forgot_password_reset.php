<?php
// Paso 2 (simulado): validar codigo y guardar nueva contraseña.
$pageTitle = 'Restablecer contraseña';
$showSidebar = false;
$currentNav = '';
$forgotError = $_SESSION['forgot_error'] ?? null;
$forgotOk = $_SESSION['forgot_ok'] ?? null;
unset($_SESSION['forgot_error'], $_SESSION['forgot_ok']);
ob_start();
?>
<style>
    .demo-code-box {
        background: linear-gradient(135deg, #e0f2fe, #f0f9ff);
        border: 1px solid #7dd3fc;
        border-radius: 10px;
        color: #0c4a6e;
        padding: .7rem .8rem;
    }
    .demo-code-box strong {
        font-size: 1.05rem;
        letter-spacing: .04em;
    }
</style>
<div class="row justify-content-center w-100">
    <div class="col-12 col-md-6 col-lg-5 col-xl-4">
        <div class="card page-card shadow">
            <div class="card-body p-4">
                <div class="text-center mb-4">
                    <i class="fa-solid fa-unlock-keyhole fa-2x text-primary mb-2"></i>
                    <h1 class="h4 mb-1">Nueva contraseña</h1>
                    <p class="text-muted small mb-0">Introduce el código y tu nueva clave</p>
                </div>
                <?php if (!empty($forgotOk)): ?>
                    <div class="demo-code-box small">
                        <i class="fa-solid fa-circle-info me-1"></i>
                        <?= htmlspecialchars($forgotOk) ?>
                    </div>
                <?php endif; ?>
                <?php if (!empty($forgotError)): ?>
                    <div class="alert alert-danger py-2 small"><?= htmlspecialchars($forgotError) ?></div>
                <?php endif; ?>
                <form method="post" action="index.php?page=forgot_password_reset">
                    <?= csrfInput() ?>
                    <div class="mb-3">
                        <label for="code" class="form-label">Código de recuperación</label>
                        <input type="text" class="form-control" id="code" name="code" value="" autocomplete="one-time-code" maxlength="6" pattern="\d{6}" required>
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">Nueva contraseña</label>
                        <input type="password" class="form-control" id="password" name="password" minlength="8" required>
                    </div>
                    <div class="mb-3">
                        <label for="password_confirm" class="form-label">Repetir nueva contraseña</label>
                        <input type="password" class="form-control" id="password_confirm" name="password_confirm" minlength="8" required>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Guardar nueva contraseña</button>
                </form>
                <div class="text-center mt-3">
                    <a href="index.php?page=forgot_password" class="small text-decoration-none">Solicitar nuevo código</a>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
document.addEventListener('DOMContentLoaded', function () {
    // Extra de seguridad visual: limpiamos el campo codigo al entrar.
    var codeInput = document.getElementById('code');
    if (codeInput) {
        codeInput.value = '';
        window.setTimeout(function () {
            codeInput.value = '';
        }, 80);
    }
});
</script>
<?php
$content = ob_get_clean();
require __DIR__ . '/layouts/main.php';
