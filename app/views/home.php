<?php
$pageTitle = 'Presentación';
$showSidebar = false;
$currentNav = '';
$publicTopButtons = true;
ob_start();
?>
<style>
    .landing-wrap { max-width: 1120px; margin: 0 auto; }
    .landing-topbar {
        position: sticky;
        top: 0;
        z-index: 5;
        background: rgba(248, 250, 252, 0.92);
        backdrop-filter: blur(6px);
        border: 1px solid #e2e8f0;
        border-radius: 0.75rem;
        padding: 0.65rem 0.8rem;
    }
    .landing-hero {
        background: linear-gradient(135deg, #0f172a, #1e3a8a);
        color: #fff;
        border-radius: 1rem;
        padding: 3rem 1.5rem;
        margin-top: 1rem;
    }
    .landing-section { padding: 3rem 0 1rem; }
    .landing-card {
        border: 1px solid #e2e8f0;
        border-radius: 0.9rem;
        background: #fff;
        box-shadow: 0 8px 24px rgba(15, 23, 42, 0.05);
        padding: 1rem;
        height: 100%;
    }
    .reveal {
        opacity: 0;
        transform: translateY(26px);
        transition: opacity 0.55s ease, transform 0.55s ease;
    }
    .reveal.is-visible {
        opacity: 1;
        transform: translateY(0);
    }
</style>

<div class="landing-wrap">
    <div class="landing-topbar d-flex flex-wrap justify-content-between align-items-center gap-2">
        <div class="fw-semibold text-dark"><i class="fa-solid fa-cubes text-primary me-1"></i> miPedido</div>
        <div class="d-flex gap-2">
            <a href="index.php?page=login" class="btn btn-outline-primary btn-sm">Iniciar sesión</a>
            <a href="index.php?page=register" class="btn btn-primary btn-sm">Crear cuenta</a>
        </div>
    </div>

    <section class="landing-hero reveal">
        <div class="row g-4 align-items-center">
            <div class="col-lg-7">
                <h1 class="display-6 fw-bold mb-3">ERP miPedido para ventas, pedidos y facturación.</h1>
                <p class="mb-4 text-light-emphasis">Controla clientes, productos, pedidos, tareas, calendario y facturas en un solo lugar, con panel visual y seguridad base.</p>
                <div class="d-flex flex-wrap gap-2">
                    <a href="index.php?page=register" class="btn btn-light text-primary fw-semibold">Crear cuenta</a>
                    <a href="index.php?page=login" class="btn btn-outline-light">Entrar</a>
                </div>
            </div>
            <div class="col-lg-5">
                <div class="bg-white text-dark rounded-3 p-3 shadow-sm">
                    <div class="small text-muted mb-2">Lo más destacado</div>
                    <ul class="mb-0">
                        <li>Dashboard con KPIs y ventas por mes.</li>
                        <li>Facturación con estados pendiente/realizada.</li>
                        <li>Tareas, calendario y alertas de plazos.</li>
                        <li>Permisos finos gestionados por admin.</li>
                    </ul>
                </div>
            </div>
        </div>
    </section>

    <section class="landing-section">
        <h2 class="h4 mb-3 reveal">Qué incluye la plataforma</h2>
        <div class="row g-3">
            <div class="col-md-6 col-lg-4 reveal"><div class="landing-card"><h3 class="h6">Pedidos inteligentes</h3><p class="small text-muted mb-0">Creación rápida, control de estado, historial y envío por WhatsApp.</p></div></div>
            <div class="col-md-6 col-lg-4 reveal"><div class="landing-card"><h3 class="h6">Ventas y métricas</h3><p class="small text-muted mb-0">Seguimiento mensual, ticket medio y top productos más vendidos.</p></div></div>
            <div class="col-md-6 col-lg-4 reveal"><div class="landing-card"><h3 class="h6">Facturación práctica</h3><p class="small text-muted mb-0">Historial filtrable por mes/estado y edición controlada de pendientes.</p></div></div>
            <div class="col-md-6 col-lg-4 reveal"><div class="landing-card"><h3 class="h6">Usuarios y permisos</h3><p class="small text-muted mb-0">Administra roles y permisos por módulo para cada empleado.</p></div></div>
            <div class="col-md-6 col-lg-4 reveal"><div class="landing-card"><h3 class="h6">Calendario y tareas</h3><p class="small text-muted mb-0">Organiza asignaciones y vencimientos con alertas visibles.</p></div></div>
            <div class="col-md-6 col-lg-4 reveal"><div class="landing-card"><h3 class="h6">Seguridad base</h3><p class="small text-muted mb-0">CSRF, sesiones seguras y bloqueo escalado por intentos fallidos.</p></div></div>
        </div>
    </section>

    <section class="landing-section reveal">
        <div class="landing-card">
            <h2 class="h4">Empieza ahora</h2>
            <p class="text-muted mb-3">Puedes registrarte con email/contraseña o con Google usando Auth0 como intermediario seguro.</p>
            <div class="d-flex flex-wrap gap-2">
                <a href="index.php?page=register" class="btn btn-primary">Crear cuenta</a>
                <a href="index.php?page=login" class="btn btn-outline-primary">Iniciar sesión</a>
            </div>
        </div>
    </section>
</div>

<script>
    (function () {
        var elems = document.querySelectorAll('.reveal');
        if (!('IntersectionObserver' in window)) {
            elems.forEach(function (el) { el.classList.add('is-visible'); });
            return;
        }
        var obs = new IntersectionObserver(function (entries) {
            entries.forEach(function (entry) {
                if (entry.isIntersecting) {
                    entry.target.classList.add('is-visible');
                    obs.unobserve(entry.target);
                }
            });
        }, { threshold: 0.15 });
        elems.forEach(function (el) { obs.observe(el); });
    })();
</script>
<?php
$content = ob_get_clean();
require __DIR__ . '/layouts/main.php';
