<?php
// Landing pública del proyecto con resumen funcional y accesos rápidos.
$pageTitle = 'Presentación';
$showSidebar = false;
$currentNav = '';
$publicTopButtons = true;
ob_start();
?>
<style>
    .landing-wrap { max-width: 1180px; margin: 0 auto; }
    .landing-pill {
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
        background: #eff6ff;
        color: #1d4ed8;
        border: 1px solid #bfdbfe;
        border-radius: 999px;
        padding: 0.25rem 0.65rem;
        font-size: 0.75rem;
        font-weight: 600;
    }
    .landing-hero {
        background: linear-gradient(140deg, #0b1f4a 0%, #102a67 45%, #0f3ea8 100%);
        color: #fff;
        border-radius: 1rem;
        padding: 3.2rem 1.6rem;
        margin-top: 1rem;
    }
    .landing-hero h1 { letter-spacing: -0.01em; }
    .landing-hero-sub {
        color: rgba(226, 232, 240, 0.95);
        max-width: 640px;
    }
    .landing-hero-content {
        max-width: 860px;
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
    .landing-card h3 { margin-bottom: 0.55rem; }
    .landing-about {
        border: 1px solid #bfdbfe;
        border-radius: 1rem;
        background: linear-gradient(135deg, #ffffff 0%, #eff6ff 100%);
        box-shadow: 0 12px 28px rgba(37, 99, 235, 0.12);
        padding: 1.5rem 1.35rem;
        text-align: center;
        position: relative;
        overflow: hidden;
    }
    .landing-about::before {
        content: '';
        position: absolute;
        inset: 0;
        background: radial-gradient(circle at 12% 18%, rgba(37, 99, 235, 0.09), transparent 50%);
        pointer-events: none;
    }
    .landing-about h2 {
        font-size: 2rem;
        font-weight: 700;
        color: #0f172a;
        margin-bottom: 0.6rem;
        position: relative;
    }
    .landing-about p {
        margin: 0 auto;
        color: #334155;
        font-size: 1rem;
        line-height: 1.7;
        max-width: 940px;
        position: relative;
    }
    .landing-stat {
        border: 1px solid #dbeafe;
        background: #f8fbff;
        border-radius: 0.85rem;
        padding: 0.8rem 0.9rem;
        height: 100%;
    }
    .landing-stat .n {
        font-size: 1.25rem;
        font-weight: 700;
        color: #0f172a;
    }
    .landing-stat .t {
        color: #475569;
        font-size: 0.82rem;
    }
    .landing-trust {
        border: 1px solid #e2e8f0;
        border-radius: 0.95rem;
        background: #f8fafc;
        padding: 1.2rem;
    }
    .landing-trust-logos {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 0.85rem;
        width: 100%;
    }
    .landing-logo-box {
        border: 1px solid #dbeafe;
        border-radius: 0.7rem;
        background: #fff;
        padding: 0.75rem;
        color: #334155;
    }
    .landing-logo-media {
        width: 100%;
        height: 92px;
        border-radius: 0.45rem;
        background: #fff;
        overflow: hidden;
        border: 0;
        padding: 0.35rem;
        margin-bottom: 0.55rem;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .landing-logo-box img {
        width: 100%;
        height: 100%;
        object-fit: contain;
        object-position: center;
        transform: none;
        image-rendering: auto;
    }
    .landing-logo-name {
        display: block;
        font-size: 0.84rem;
        font-weight: 600;
        color: #0f172a;
        margin-bottom: 0.2rem;
        line-height: 1.25;
    }
    .landing-logo-sector {
        display: block;
        font-size: 0.73rem;
        color: #2563eb;
        font-weight: 600;
        margin-bottom: 0.2rem;
    }
    .landing-logo-use {
        display: block;
        font-size: 0.75rem;
        color: #475569;
        line-height: 1.3;
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
    .pricing-card {
        border: 1px solid #e2e8f0;
        border-radius: 0.9rem;
        background: #fff;
        box-shadow: 0 8px 24px rgba(15, 23, 42, 0.05);
        padding: 1.1rem;
        height: 100%;
    }
    .pricing-card--featured {
        border-color: #93c5fd;
        box-shadow: 0 10px 26px rgba(37, 99, 235, 0.18);
    }
    .pricing-price {
        font-size: 1.8rem;
        font-weight: 700;
        line-height: 1;
    }
    .landing-footer {
        border-top: 1px solid #e2e8f0;
        margin-top: 2.4rem;
        padding: 2rem 0 2.2rem;
        color: #64748b;
        font-size: 0.86rem;
    }
    .landing-cta {
        border: 1px solid #dbeafe;
        background: linear-gradient(135deg, #ffffff 0%, #f8fbff 100%);
        border-radius: 1rem;
        padding: 1.6rem 1.35rem;
        box-shadow: 0 12px 28px rgba(15, 23, 42, 0.08);
        text-align: center;
    }
    .landing-cta h2 {
        font-size: 1.95rem;
        font-weight: 700;
        margin-bottom: 0.55rem;
        color: #0f172a;
    }
    .landing-cta p {
        font-size: 1.03rem;
        color: #475569;
        max-width: 760px;
        margin-left: auto;
        margin-right: auto;
    }
    .landing-cta .btn {
        padding: 0.58rem 1rem;
        font-weight: 600;
    }
    .landing-footer-top {
        display: grid;
        grid-template-columns: 1.2fr repeat(4, 1fr);
        gap: 1rem 1.4rem;
        align-items: start;
    }
    .landing-footer-brand {
        color: #0f172a;
    }
    .landing-footer-brand .brand-name {
        font-size: 1rem;
        font-weight: 700;
        margin-bottom: 0.65rem;
    }
    .landing-footer-brand .contact-line {
        display: flex;
        gap: 0.55rem;
        align-items: flex-start;
        margin-bottom: 0.45rem;
        color: #334155;
        font-size: 0.84rem;
    }
    .landing-footer-title {
        color: #0f172a;
        font-size: 0.92rem;
        font-weight: 700;
        margin-bottom: 0.65rem;
    }
    .landing-footer-col a {
        display: block;
        text-decoration: none;
        color: #475569;
        font-size: 0.84rem;
        margin-bottom: 0.45rem;
    }
    .landing-footer-col a:hover { color: #2563eb; }
    .landing-footer-bottom {
        margin-top: 1.4rem;
        padding-top: 1rem;
        border-top: 1px solid #e2e8f0;
        display: flex;
        justify-content: space-between;
        gap: 0.8rem;
        flex-wrap: wrap;
        color: #64748b;
        font-size: 0.82rem;
    }
    .landing-socials {
        display: inline-flex;
        align-items: center;
        gap: 0.45rem;
    }
    .landing-socials a {
        width: 32px;
        height: 32px;
        border-radius: 999px;
        border: 1px solid #cbd5e1;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        color: #334155;
        text-decoration: none;
        background: #fff;
        transition: all .15s ease;
    }
    .landing-socials a:hover {
        color: #2563eb;
        border-color: #93c5fd;
        transform: translateY(-1px);
    }
    @media (max-width: 991.98px) {
        .landing-trust-logos { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        .landing-cta h2 { font-size: 1.55rem; }
        .landing-cta p { font-size: 0.95rem; }
        .landing-about h2 { font-size: 1.65rem; }
        .landing-about p { font-size: 0.95rem; }
        .landing-footer-top {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }
    @media (max-width: 575.98px) {
        .landing-trust-logos { grid-template-columns: 1fr; }
        .landing-cta { padding: 1.25rem 1rem; }
        .landing-cta h2 { font-size: 1.35rem; }
        .landing-about { padding: 1.2rem 0.95rem; }
        .landing-about h2 { font-size: 1.35rem; }
        .landing-about p { font-size: 0.92rem; }
        .landing-footer-top {
            grid-template-columns: 1fr;
        }
    }
</style>

<div class="landing-wrap">
    <section class="landing-hero reveal">
        <div class="row">
            <div class="col-12">
                <div class="landing-hero-content">
                <span class="landing-pill mb-3"><i class="fa-solid fa-graduation-cap"></i> Demo académica TFG</span>
                <h1 class="display-6 fw-bold mb-3">Gestión comercial clara para PYMEs con miPedido.</h1>
                <p class="mb-4 landing-hero-sub">Centraliza clientes, productos, pedidos, facturación, tareas y calendario en una sola plataforma web. Diseñada para mostrar un flujo realista de operación empresarial en tu defensa.</p>
                <div class="d-flex flex-wrap gap-2">
                    <a href="index.php?page=register" class="btn btn-light text-primary fw-semibold">Probar gratis</a>
                    <a href="#modulos" class="btn btn-outline-light">Saber más</a>
                </div>
            </div>
            </div>
        </div>
    </section>

    <section id="acerca" class="landing-section reveal">
        <div class="landing-about">
            <h2 class="h4 mb-2">Acerca de</h2>
            <p>
                miPedido es una aplicación web de gestión para pequeñas y medianas empresas que buscan organizar su operación diaria en un solo entorno.
                Está orientada a negocios con flujo comercial activo (ventas, pedidos y facturación), y se complementa con control de clientes, productos, tareas internas,
                calendario y permisos por usuario. Su objetivo es mejorar la visibilidad del negocio, reducir tareas manuales y facilitar decisiones rápidas con información centralizada.
            </p>
        </div>
    </section>

    <section class="landing-section">
        <div class="row g-3">
            <div class="col-6 col-md-3 reveal"><div class="landing-stat"><div class="n">+10</div><div class="t">Rutas operativas en producción demo</div></div></div>
            <div class="col-6 col-md-3 reveal"><div class="landing-stat"><div class="n">8</div><div class="t">Fases de evolución documentadas</div></div></div>
            <div class="col-6 col-md-3 reveal"><div class="landing-stat"><div class="n">3</div><div class="t">Perfiles base: admin, empleado, público</div></div></div>
            <div class="col-6 col-md-3 reveal"><div class="landing-stat"><div class="n">24/7</div><div class="t">Acceso web desde navegador</div></div></div>
        </div>
    </section>

    <section id="modulos" class="landing-section">
        <h2 class="h4 mb-3 reveal">Capacidades clave de la plataforma</h2>
        <div class="row g-3">
            <div class="col-md-6 col-lg-4 reveal"><div class="landing-card"><h3 class="h6">Pedidos inteligentes</h3><p class="small text-muted mb-0">Alta de pedidos, control de stock, estados operativos e historial auditable por pedido.</p></div></div>
            <div class="col-md-6 col-lg-4 reveal"><div class="landing-card"><h3 class="h6">Ventas y analítica</h3><p class="small text-muted mb-0">Panel mensual por bloques, ticket medio y ranking de productos más vendidos.</p></div></div>
            <div class="col-md-6 col-lg-4 reveal"><div class="landing-card"><h3 class="h6">Facturación operativa</h3><p class="small text-muted mb-0">Facturas pendientes/realizadas, filtros por mes y flujo de cierre controlado.</p></div></div>
            <div class="col-md-6 col-lg-4 reveal"><div class="landing-card"><h3 class="h6">Usuarios y permisos</h3><p class="small text-muted mb-0">Gestión de usuarios con roles y permisos granulares administrados desde backend.</p></div></div>
            <div class="col-md-6 col-lg-4 reveal"><div class="landing-card"><h3 class="h6">Tareas y calendario</h3><p class="small text-muted mb-0">Asignación de tareas del equipo y agenda personal integrada en calendario mensual.</p></div></div>
        </div>
    </section>

    <section id="precios" class="landing-section">
        <div class="d-flex flex-wrap justify-content-between align-items-end gap-2 mb-3 reveal">
            <div>
                <h2 class="h4 mb-1">Planes orientativos para el TFG</h2>
                <p class="small text-muted mb-0">Precios de referencia para la presentación académica. No es una oferta comercial real.</p>
            </div>
        </div>
        <div class="row g-3">
            <div class="col-md-6 col-lg-4 reveal">
                <div class="pricing-card">
                    <h3 class="h6 mb-2">Básico</h3>
                    <p class="pricing-price mb-1">19€<span class="fs-6 text-muted fw-normal">/mes</span></p>
                    <p class="small text-muted mb-3">Pensado para autónomos o microempresas.</p>
                    <ul class="small mb-0 ps-3">
                        <li>Clientes y productos.</li>
                        <li>Pedidos y facturación básica.</li>
                        <li>1 admin + 1 empleado.</li>
                    </ul>
                </div>
            </div>
            <div class="col-md-6 col-lg-4 reveal">
                <div class="pricing-card pricing-card--featured">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h3 class="h6 mb-0">Profesional</h3>
                        <span class="badge text-bg-primary">Más elegido</span>
                    </div>
                    <p class="pricing-price mb-1">39€<span class="fs-6 text-muted fw-normal">/mes</span></p>
                    <p class="small text-muted mb-3">Para equipos pequeños con operación diaria.</p>
                    <ul class="small mb-0 ps-3">
                        <li>Todo lo del plan Básico.</li>
                        <li>Tareas y calendario.</li>
                        <li>Ventas y facturación con filtros.</li>
                        <li>Hasta 5 usuarios.</li>
                    </ul>
                </div>
            </div>
            <div class="col-md-6 col-lg-4 reveal">
                <div class="pricing-card">
                    <h3 class="h6 mb-2">Avanzado</h3>
                    <p class="pricing-price mb-1">69€<span class="fs-6 text-muted fw-normal">/mes</span></p>
                    <p class="small text-muted mb-3">Para más volumen y control interno.</p>
                    <ul class="small mb-0 ps-3">
                        <li>Todo lo del plan Profesional.</li>
                        <li>Permisos por usuario y gestión avanzada.</li>
                        <li>Personalización de entorno.</li>
                        <li>Soporte prioritario en demo TFG.</li>
                    </ul>
                </div>
            </div>
        </div>
    </section>

    <section id="confianza" class="landing-section reveal">
        <div class="landing-trust">
            <div class="row g-3 align-items-center">
                <div class="col-lg-4">
                    <h2 class="h4 mb-2">Clientes demo por sector</h2>
                    <p class="text-muted small mb-0">Perfiles de negocio realistas para la defensa del TFG. Cada cliente demuestra un uso concreto de miPedido: más control del día a día, mejor seguimiento operativo y decisiones basadas en datos.</p>
                </div>
                <div class="col-lg-8">
                    <div class="landing-trust-logos">
                        <div class="landing-logo-box">
                            <div class="landing-logo-media"><img src="img/clientes_demo/reformas_hermanos_garcia.png" alt="Logo Reformas Hermanos García"></div>
                            <span class="landing-logo-name">Reformas Hermanos García</span>
                            <span class="landing-logo-sector">Construcción / Reformas</span>
                            <span class="landing-logo-use">Usa el módulo de Presupuestos para desglosar materiales, horas de cuadrilla y margen por obra antes de aprobar cada trabajo.</span>
                        </div>
                        <div class="landing-logo-box">
                            <div class="landing-logo-media"><img src="img/clientes_demo/fruteria_huerto_ana.png" alt="Logo Frutería El Huerto de Ana"></div>
                            <span class="landing-logo-name">Frutería El Huerto de Ana</span>
                            <span class="landing-logo-sector">Comercio local</span>
                            <span class="landing-logo-use">Usa Inventario para controlar entradas por proveedor, stock diario y mermas de producto fresco con reposición más precisa.</span>
                        </div>
                        <div class="landing-logo-box">
                            <div class="landing-logo-media"><img src="img/clientes_demo/taller_rayo.png" alt="Logo Taller Mecánico Rayo"></div>
                            <span class="landing-logo-name">Taller Mecánico Rayo</span>
                            <span class="landing-logo-sector">Automoción</span>
                            <span class="landing-logo-use">Usa Órdenes de trabajo para seguir diagnóstico, reparación, recambios y estado de entrega de cada vehículo de cliente.</span>
                        </div>
                        <div class="landing-logo-box">
                            <div class="landing-logo-media"><img src="img/clientes_demo/peluqueria_estilo_color.png" alt="Logo Peluquería Estilo y Color"></div>
                            <span class="landing-logo-name">Peluquería Estilo &amp; Color</span>
                            <span class="landing-logo-sector">Estética</span>
                            <span class="landing-logo-use">Usa Agenda y Facturación rápida para gestionar citas por profesional, servicios realizados y cobro inmediato en mostrador.</span>
                        </div>
                        <div class="landing-logo-box">
                            <div class="landing-logo-media"><img src="img/clientes_demo/panificadora_san_jose.png" alt="Logo Panificadora San José"></div>
                            <span class="landing-logo-name">Panificadora San José</span>
                            <span class="landing-logo-sector">Alimentación</span>
                            <span class="landing-logo-use">Usa Distribución para emitir albaranes por ruta, controlar entregas a tiendas y consolidar facturación semanal por cliente.</span>
                        </div>
                        <div class="landing-logo-box">
                            <div class="landing-logo-media"><img src="img/clientes_demo/fontaneria_gas_jimenez.png" alt="Logo Fontanería y Gas Jiménez"></div>
                            <span class="landing-logo-name">Fontanería y Gas Jiménez</span>
                            <span class="landing-logo-sector">Servicios</span>
                            <span class="landing-logo-use">Usa Gastos para registrar facturas de proveedor, kilometraje de técnicos y rentabilidad por cada servicio realizado.</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="landing-section reveal">
        <div class="landing-cta">
            <h2>Empieza tu demo en menos de 2 minutos</h2>
            <p class="mb-3">Activa una cuenta de prueba y muestra en vivo pedidos, facturación, ventas, tareas y calendario con una experiencia clara y profesional para tu defensa del TFG.</p>
            <div class="d-flex flex-wrap gap-2 justify-content-center">
                <a href="index.php?page=register" class="btn btn-primary btn-lg">Crear cuenta</a>
                <a href="index.php?page=login" class="btn btn-outline-primary btn-lg">Iniciar sesión</a>
            </div>
        </div>
    </section>

    <footer class="landing-footer reveal">
        <div class="landing-footer-top">
            <div class="landing-footer-brand">
                <div class="brand-name"><i class="fa-solid fa-cubes text-primary me-1"></i> miPedido</div>
                <div class="contact-line"><i class="fa-solid fa-location-dot mt-1"></i><span>Castellon, España</span></div>
                <div class="contact-line"><i class="fa-solid fa-phone mt-1"></i><span>+34 600 123 456</span></div>
                <div class="contact-line"><i class="fa-solid fa-envelope mt-1"></i><span>demo@mipedido.local</span></div>
            </div>
            <div class="landing-footer-col">
                <div class="landing-footer-title">Producto</div>
                <a href="#modulos">Módulos principales</a>
                <a href="#precios">Precios orientativos</a>
                <a href="#confianza">Casos demo por sector</a>
                <a href="index.php?page=login">Acceso a la plataforma</a>
            </div>
            <div class="landing-footer-col">
                <div class="landing-footer-title">Empresa</div>
                <a href="#confianza">Clientes de referencia demo</a>
                <a href="index.php?page=register">Solicitar demo</a>
                <a href="index.php?page=home">Quiénes somos</a>
                <a href="index.php?page=home#modulos">Funcionalidades</a>
            </div>
            <div class="landing-footer-col">
                <div class="landing-footer-title">Soporte</div>
                <a href="index.php?page=login">Centro de ayuda</a>
                <a href="index.php?page=register">Contacto comercial</a>
                <a href="index.php?page=home#precios">Preguntas frecuentes</a>
                <a href="index.php?page=home">Estado de la demo</a>
            </div>
            <div class="landing-footer-col">
                <div class="landing-footer-title">Legal</div>
                <a href="index.php?page=home">Privacidad</a>
                <a href="index.php?page=home">Términos de uso</a>
                <a href="index.php?page=home">Cookies</a>
                <a href="index.php?page=home">Aviso legal</a>
            </div>
        </div>
        <div class="landing-footer-bottom">
            <div>© <?= date('Y') ?> miPedido - Proyecto académico TFG.</div>
            <div class="d-flex align-items-center gap-3 flex-wrap">
                <div>Diseñado para presentación profesional de software de gestión para PYMEs.</div>
                <div class="landing-socials" aria-label="Redes sociales">
                    <a href="https://www.instagram.com/" target="_blank" rel="noopener noreferrer" aria-label="Instagram">
                        <i class="fa-brands fa-instagram"></i>
                    </a>
                    <a href="https://www.linkedin.com/" target="_blank" rel="noopener noreferrer" aria-label="LinkedIn">
                        <i class="fa-brands fa-linkedin-in"></i>
                    </a>
                    <a href="https://x.com/" target="_blank" rel="noopener noreferrer" aria-label="X">
                        <i class="fa-brands fa-x-twitter"></i>
                    </a>
                </div>
            </div>
        </div>
    </footer>
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
