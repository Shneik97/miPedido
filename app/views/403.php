<?php
require_once __DIR__ . '/../helpers/auth_helper.php';
authEnsureSession();
$pageTitle = 'Acceso denegado';
$currentNav = '';
$showSidebar = isset($_SESSION['usuario']);
ob_start();
?>
<div class="row justify-content-center">
    <div class="col-md-6 col-lg-5">
        <div class="card page-card border-danger border-opacity-25">
            <div class="card-body text-center py-5">
                <i class="fa-solid fa-lock fa-3x text-danger mb-3"></i>
                <h1 class="h4 mb-2">Acceso denegado</h1>
                <p class="text-muted mb-4">No tienes permisos para ver esta sección. Si crees que es un error, contacta con un administrador.</p>
                <a href="index.php?page=dashboard" class="btn btn-primary"><i class="fa-solid fa-house me-1"></i>Volver al inicio</a>
            </div>
        </div>
    </div>
</div>
<?php
$content = ob_get_clean();
require __DIR__ . '/layouts/main.php';
