<?php
// Landing pública del proyecto con resumen funcional y accesos rápidos.
$pageTitle = 'Presentación';
$showSidebar = false;
$currentNav = '';
$publicTopButtons = true;
ob_start();
?>
<link rel="stylesheet" href="css/home.css">

<div class="landing-wrap">
    <section class="landing-hero reveal">
        <div class="row">
            <div class="col-12">
                <div class="landing-hero-content">
                <span class="landing-pill mb-3"><i class="fa-solid fa-graduation-cap"></i> ERP en la nube para PYMEs</span>
                <h1 class="display-6 fw-bold mb-3">Impulsa tu pyme con un ERP todo en uno: ventas, pedidos, facturación y control total.</h1>
                <p class="mb-4 landing-hero-sub">Gestiona clientes, productos, pedidos, facturación, tareas y calendario desde una sola plataforma. Ahorra tiempo, organiza mejor tu negocio y toma decisiones con más control para impulsar tu crecimiento.</p>
                <div class="d-flex flex-wrap gap-2">
                    <a href="index.php?page=register" class="btn btn-light text-primary fw-semibold">Probar</a>
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
                miPedido es un ERP en la nube diseñado para que pymes y negocios en crecimiento gestionen toda su operación desde un único lugar.
                Integra ventas, pedidos, facturación, clientes, productos, tareas y calendario para que trabajes con procesos más ágiles,
                reduzcas errores operativos y tomes decisiones más rentables con información clara y actualizada en tiempo real.
            </p>
        </div>
    </section>

    <section class="landing-section">
        <div class="row g-3">
            <div class="col-6 col-md-3 reveal"><div class="landing-stat"><div class="n">+10</div><div class="t">Módulos de gestión conectados</div></div></div>
            <div class="col-6 col-md-3 reveal"><div class="landing-stat"><div class="n">8</div><div class="t">Áreas clave del negocio digitalizadas</div></div></div>
            <div class="col-6 col-md-3 reveal"><div class="landing-stat"><div class="n">3</div><div class="t">Planes adaptados a cada etapa</div></div></div>
            <div class="col-6 col-md-3 reveal"><div class="landing-stat"><div class="n">24/7</div><div class="t">Acceso seguro desde cualquier lugar</div></div></div>
        </div>
    </section>

    <section id="modulos" class="landing-section">
        <h2 class="h4 mb-3 reveal">Capacidades clave de la plataforma</h2>
        <div class="row g-3">
            <div class="col-md-6 col-lg-4 reveal"><div class="landing-card"><h3 class="h6">Pedidos inteligentes</h3><p class="small text-muted mb-0">Crea y gestiona pedidos en segundos, con trazabilidad completa para mejorar tiempos de entrega y satisfacción del cliente.</p></div></div>
            <div class="col-md-6 col-lg-4 reveal"><div class="landing-card"><h3 class="h6">Ventas y analítica</h3><p class="small text-muted mb-0">Visualiza resultados en tiempo real, detecta oportunidades y aumenta tu rentabilidad con datos accionables.</p></div></div>
            <div class="col-md-6 col-lg-4 reveal"><div class="landing-card"><h3 class="h6">Facturación operativa</h3><p class="small text-muted mb-0">Emite, controla y organiza facturas con un flujo claro que reduce errores y acelera el cierre mensual.</p></div></div>
            <div class="col-md-6 col-lg-4 reveal"><div class="landing-card"><h3 class="h6">Usuarios y permisos</h3><p class="small text-muted mb-0">Protege la información de tu empresa asignando accesos por rol según responsabilidades reales del equipo.</p></div></div>
            <div class="col-md-6 col-lg-4 reveal"><div class="landing-card"><h3 class="h6">Tareas y calendario</h3><p class="small text-muted mb-0">Coordina equipos, prioriza pendientes y asegura el seguimiento diario desde una agenda centralizada.</p></div></div>
        </div>
    </section>

    <section id="precios" class="landing-section">
        <div class="d-flex flex-wrap justify-content-between align-items-end gap-2 mb-3 reveal">
            <div>
                <h2 class="h4 mb-1">Planes de precios</h2>
                <p class="small text-muted mb-0">Elige el plan que mejor se adapta a tu negocio y escala a medida que crece tu operación.</p>
            </div>
        </div>
        <div class="row g-3">
            <div class="col-md-6 col-lg-4 reveal">
                <div class="pricing-card">
                    <h3 class="h6 mb-2">Básico</h3>
                    <p class="pricing-price mb-1">19€<span class="fs-6 text-muted fw-normal">/mes</span></p>
                    <p class="small text-muted mb-3">Ideal para empezar a digitalizar tu gestión sin complicaciones.</p>
                    <ul class="small mb-0 ps-3">
                        <li>Gestión de clientes y catálogo de productos.</li>
                        <li>Pedidos y facturación esenciales en un solo panel.</li>
                        <li>Hasta 2 usuarios para arrancar con control.</li>
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
                    <p class="small text-muted mb-3">La opción más completa para equipos que quieren vender más con orden.</p>
                    <ul class="small mb-0 ps-3">
                        <li>Incluye todo lo del plan Básico.</li>
                        <li>Tareas y calendario para organizar al equipo.</li>
                        <li>Análisis de ventas y facturación avanzada.</li>
                        <li>Hasta 5 usuarios con permisos por rol.</li>
                    </ul>
                </div>
            </div>
            <div class="col-md-6 col-lg-4 reveal">
                <div class="pricing-card">
                    <h3 class="h6 mb-2">Avanzado</h3>
                    <p class="pricing-price mb-1">69€<span class="fs-6 text-muted fw-normal">/mes</span></p>
                    <p class="small text-muted mb-3">Pensado para empresas que necesitan máxima visibilidad y control operativo.</p>
                    <ul class="small mb-0 ps-3">
                        <li>Incluye todo lo del plan Profesional.</li>
                        <li>Control avanzado de usuarios y permisos.</li>
                        <li>Configuración personalizada según tu flujo.</li>
                        <li>Soporte prioritario para tu operación diaria.</li>
                    </ul>
                </div>
            </div>
        </div>
    </section>

    <section id="confianza" class="landing-section reveal">
        <div class="landing-trust">
            <div class="row g-3 align-items-center">
                <div class="col-lg-4">
                    <h2 class="h4 mb-2">Clientes por sector</h2>
                    <p class="text-muted small mb-0">Negocios de distintos sectores ya utilizan miPedido para optimizar su gestión diaria, mejorar su productividad y crecer con procesos más eficientes.</p>
                </div>
                <div class="col-lg-8">
                    <div class="landing-trust-logos">
                        <div class="landing-logo-box">
                            <div class="landing-logo-media"><img src="img/clientes_demo/reformas_hermanos_garcia.png" alt="Logo Reformas Hermanos García"></div>
                            <span class="landing-logo-name">Reformas Hermanos García</span>
                            <span class="landing-logo-sector">Construcción / Reformas</span>
                            <span class="landing-logo-use">Optimiza presupuestos por obra, controla costes en detalle y mejora la rentabilidad antes de aceptar cada proyecto.</span>
                        </div>
                        <div class="landing-logo-box">
                            <div class="landing-logo-media"><img src="img/clientes_demo/fruteria_huerto_ana.png" alt="Logo Frutería El Huerto de Ana"></div>
                            <span class="landing-logo-name">Frutería El Huerto de Ana</span>
                            <span class="landing-logo-sector">Comercio local</span>
                            <span class="landing-logo-use">Gestiona inventario en tiempo real, reduce mermas y asegura reposición inteligente para no perder ventas.</span>
                        </div>
                        <div class="landing-logo-box">
                            <div class="landing-logo-media"><img src="img/clientes_demo/taller_rayo.png" alt="Logo Taller Mecánico Rayo"></div>
                            <span class="landing-logo-name">Taller Mecánico Rayo</span>
                            <span class="landing-logo-sector">Automoción</span>
                            <span class="landing-logo-use">Centraliza órdenes de trabajo, recambios y estados de reparación para entregar más rápido y con mejor servicio.</span>
                        </div>
                        <div class="landing-logo-box">
                            <div class="landing-logo-media"><img src="img/clientes_demo/peluqueria_estilo_color.png" alt="Logo Peluquería Estilo y Color"></div>
                            <span class="landing-logo-name">Peluquería Estilo &amp; Color</span>
                            <span class="landing-logo-sector">Estética</span>
                            <span class="landing-logo-use">Organiza citas por profesional, controla servicios realizados y acelera el cobro para aumentar la rotación diaria.</span>
                        </div>
                        <div class="landing-logo-box">
                            <div class="landing-logo-media"><img src="img/clientes_demo/panificadora_san_jose.png" alt="Logo Panificadora San José"></div>
                            <span class="landing-logo-name">Panificadora San José</span>
                            <span class="landing-logo-sector">Alimentación</span>
                            <span class="landing-logo-use">Planifica rutas, controla entregas y consolida facturación por cliente para ganar eficiencia logística.</span>
                        </div>
                        <div class="landing-logo-box">
                            <div class="landing-logo-media"><img src="img/clientes_demo/fontaneria_gas_jimenez.png" alt="Logo Fontanería y Gas Jiménez"></div>
                            <span class="landing-logo-name">Fontanería y Gas Jiménez</span>
                            <span class="landing-logo-sector">Servicios</span>
                            <span class="landing-logo-use">Registra gastos y desplazamientos con precisión para medir márgenes reales y mejorar cada servicio técnico.</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="landing-section reveal">
        <div class="landing-cta">
            <h2>Empieza en menos de 2 minutos</h2>
            <p class="mb-3">Crea tu cuenta, activa tu entorno y empieza a gestionar ventas, pedidos, facturación y equipo desde un único ERP pensado para hacer crecer tu negocio.</p>
            <div class="d-flex flex-wrap gap-2 justify-content-center">
                <a href="index.php?page=register" class="btn btn-primary btn-lg">Empezar ahora</a>
                <a href="index.php?page=login" class="btn btn-outline-primary btn-lg">Ver plataforma</a>
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
                <a href="#modulos">Funcionalidades clave</a>
                <a href="#precios">Precios</a>
                <a href="#confianza">Casos de éxito por sector</a>
                <a href="index.php?page=login">Acceder a la plataforma</a>
            </div>
            <div class="landing-footer-col">
                <div class="landing-footer-title">Empresa</div>
                <a href="#confianza">Clientes de referencia</a>
                <a href="index.php?page=register">Empezar ahora</a>
                <a href="index.php?page=home">Quiénes somos</a>
                <a href="index.php?page=home#modulos">Funcionalidades</a>
            </div>
            <div class="landing-footer-col">
                <div class="landing-footer-title">Soporte</div>
                <a href="index.php?page=login">Ayuda y soporte</a>
                <a href="index.php?page=register">Asesor comercial</a>
                <a href="index.php?page=home#precios">Preguntas frecuentes</a>
                <a href="index.php?page=home">Estado del servicio</a>
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
            <div>© <?= date('Y') ?> miPedido - ERP para PYMEs.</div>
            <div class="d-flex align-items-center gap-3 flex-wrap">
                <div>Diseñado para simplificar la gestión empresarial y acelerar el crecimiento de tu negocio.</div>
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
