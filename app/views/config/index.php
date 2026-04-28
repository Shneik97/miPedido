<?php
// Página de configuración general reservada para administradores.
$pageTitle = 'Configuración';
$currentNav = 'config';
ob_start();
?>
<div class="card page-card">
    <div class="card-body col-lg-8">
        <h1 class="h4 mb-3">Configuración</h1>
        <p class="text-muted">Zona reservada a administradores. Aquí podrás enlazar en futuras versiones parámetros globales, integraciones o copias de seguridad.</p>
        <ul class="small text-muted">
            <li>Variables de entorno para la base de datos (ver <code>docs/DESPLIEGUE.md</code>).</li>
            <li>PHP <?= htmlspecialchars(PHP_VERSION) ?> — recomendado usar HTTPS en producción.</li>
        </ul>
    </div>
</div>
<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/main.php';
