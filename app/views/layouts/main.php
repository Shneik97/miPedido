<?php
require_once __DIR__ . '/../../helpers/auth_helper.php';
require_once __DIR__ . '/../../helpers/nav_helper.php';
require_once __DIR__ . '/../../helpers/preferencias_ui_helper.php';

// Variables base del layout compartido.
// Idea estudiante: este archivo es la "plantilla general" (header, sidebar, estilos y scripts globales).
authEnsureSession();
$isAdmin = isAdmin();
$pageTitle = $pageTitle ?? 'ERP miPedido';
$showSidebar = $showSidebar ?? true;
$currentNav = $currentNav ?? '';
$usuarioNombre = $_SESSION['usuario']['nombre'] ?? '';
$usuarioRol = strtolower((string) ($_SESSION['usuario']['rol'] ?? ''));
$usuarioRolLabel = $usuarioRol === 'admin' ? 'Admin' : 'Empleado';

// Datos de navegación (badge de tareas + notificaciones de cabecera).
// Se calculan aquí para que todas las vistas usen el mismo comportamiento.
$navTareasPendientes = 0;
$navCabeceraNotifs = [];
if ($showSidebar && isset($_SESSION['usuario']['id'])) {
    $wsNav = (string) ($_SESSION['usuario']['workspace_key'] ?? '');
    $navTareasPendientes = contarTareasPendientesNav((int) $_SESSION['usuario']['id'], $isAdmin, $wsNav);
    $navCabeceraNotifs = notificacionesCabecera((int) $_SESSION['usuario']['id'], $isAdmin, $wsNav);
}

// Preferencias visuales del usuario (tema, color, posición/collapse de sidebar).
// Si no hay preferencias guardadas, se usan valores por defecto del helper.
$prefsUi = preferenciasUiDefaults();
if ($showSidebar && isset($_SESSION['usuario'])) {
    $prefsUi = preferenciasUiNormalize($_SESSION['usuario']['preferencias_ui'] ?? null);
}
$accentPair = preferenciasUiAcentoMap()[$prefsUi['acento']] ?? preferenciasUiAcentoMap()['cyan'];
$fondoTrabajoHex = preferenciasUiFondoMap()[$prefsUi['fondo_trabajo']] ?? preferenciasUiFondoMap()['slate'];
$asideCollapsed = mipedido_sidebar_collapsed_for_layout($showSidebar, $prefsUi);
$bodyUiClasses = [];
if (!$showSidebar) {
    $bodyUiClasses[] = 'login-body';
} else {
    if (($prefsUi['sidebar_pos'] ?? 'izquierda') === 'derecha') {
        $bodyUiClasses[] = 'sidebar-derecha';
    }
    if (($prefsUi['tema_contenido'] ?? 'claro') === 'oscuro') {
        $bodyUiClasses[] = 'tema-oscuro';
    }
}
$sidebarEsDerecha = $showSidebar && (($prefsUi['sidebar_pos'] ?? 'izquierda') === 'derecha');
$notifDropdownAlignClass = $sidebarEsDerecha ? 'dropdown-menu-start' : 'dropdown-menu-end';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= htmlspecialchars($pageTitle) ?> — ERP miPedido</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
    <link rel="stylesheet" href="css/layout.css">
    <style>
        /* Variables dinamicas del tema (cambian segun preferencias de usuario). */
        :root {
            --sidebar-w: 260px;
            --sidebar-collapsed-w: 72px;
            --content-bg: <?= htmlspecialchars($fondoTrabajoHex, ENT_QUOTES, 'UTF-8') ?>;
            --accent: <?= htmlspecialchars($accentPair[0], ENT_QUOTES, 'UTF-8') ?>;
            --accent-rgb: <?= htmlspecialchars($accentPair[1], ENT_QUOTES, 'UTF-8') ?>;
        }
    </style>
</head>
<body class="<?= htmlspecialchars(implode(' ', $bodyUiClasses), ENT_QUOTES, 'UTF-8') ?>">

<?php if ($showSidebar): ?>
<aside class="app-sidebar<?= $asideCollapsed ? ' collapsed' : '' ?>" id="appSidebar" aria-label="Navegación principal">
    <div class="sidebar-brand">
        <i class="fa-solid fa-cubes"></i>
        <span>miPedido</span>
    </div>
    <nav class="sidebar-nav flex-column py-3">
        <a class="nav-link <?= $currentNav === 'dashboard' ? 'active' : '' ?>" href="index.php?page=dashboard">
            <i class="fa-solid fa-chart-line"></i><span class="nav-text">Dashboard</span>
        </a>
        <a class="nav-link <?= $currentNav === 'clientes' ? 'active' : '' ?>" href="index.php?page=clientes">
            <i class="fa-solid fa-users"></i><span class="nav-text">Clientes</span>
        </a>
        <a class="nav-link <?= $currentNav === 'productos' ? 'active' : '' ?>" href="index.php?page=productos">
            <i class="fa-solid fa-box-open"></i><span class="nav-text">Productos</span>
        </a>
        <a class="nav-link <?= $currentNav === 'pedidos' ? 'active' : '' ?>" href="index.php?page=pedidos">
            <i class="fa-solid fa-receipt"></i><span class="nav-text">Pedidos</span>
        </a>
        <a class="nav-link <?= $currentNav === 'ventas' ? 'active' : '' ?>" href="index.php?page=ventas">
            <i class="fa-solid fa-chart-column"></i><span class="nav-text">Ventas</span>
        </a>
        <a class="nav-link <?= $currentNav === 'facturacion' ? 'active' : '' ?>" href="index.php?page=facturacion">
            <i class="fa-solid fa-file-invoice-dollar"></i><span class="nav-text">Facturación</span>
        </a>
        <a class="nav-link <?= $currentNav === 'tareas' ? 'active' : '' ?><?= !empty($navTareasPendientes) ? ' nav-link--tareas-badge' : '' ?>" href="index.php?page=tareas">
            <?php if (!empty($navTareasPendientes)): ?>
            <span class="nav-tareas-icon-wrap"><i class="fa-solid fa-list-check"></i><span class="badge rounded-pill text-bg-warning nav-tareas-glovo"><?= (int) $navTareasPendientes ?></span></span>
            <?php else: ?>
            <i class="fa-solid fa-list-check"></i>
            <?php endif; ?>
            <span class="nav-text">Tareas</span>
        </a>
        <a class="nav-link <?= $currentNav === 'calendario' ? 'active' : '' ?>" href="index.php?page=calendario">
            <i class="fa-solid fa-calendar-days"></i><span class="nav-text">Calendario</span>
        </a>
        <a class="nav-link <?= $currentNav === 'mi_entorno' ? 'active' : '' ?>" href="index.php?page=mi_entorno">
            <i class="fa-solid fa-gear"></i><span class="nav-text">Mi entorno</span>
        </a>
        <?php if ($isAdmin): ?>
        <a class="nav-link <?= $currentNav === 'usuarios' ? 'active' : '' ?>" href="index.php?page=usuarios">
            <i class="fa-solid fa-user-shield"></i><span class="nav-text">Usuarios</span>
        </a>
        <?php endif; ?>
        <a class="nav-link <?= $currentNav === 'config' ? 'active' : '' ?>" href="index.php?page=config">
            <i class="fa-solid fa-gear"></i><span class="nav-text">Configuración</span>
        </a>
    </nav>
    <div class="sidebar-footer">
        <!-- Cerrar sesion queda siempre visible al pie del sidebar -->
        <a class="nav-link text-warning-emphasis mt-1" href="index.php?page=logout">
            <i class="fa-solid fa-right-from-bracket"></i><span class="nav-text">Cerrar sesión</span>
        </a>
        <div class="sidebar-watermark">miPedido</div>
    </div>
</aside>
<?php endif; ?>

<div class="app-main <?= $showSidebar ? '' : 'no-sidebar' ?>">
    <header class="top-navbar sticky-top">
        <div class="d-flex align-items-center gap-2">
            <?php if ($showSidebar): ?>
            <button type="button" class="btn btn-outline-secondary btn-sm d-none d-lg-inline-flex" id="sidebarToggle" title="Colapsar menú" aria-label="Colapsar menú lateral">
                <i class="fa-solid fa-bars"></i>
            </button>
            <button type="button" class="btn btn-outline-secondary btn-sm d-lg-none" id="sidebarMobileToggle" title="Menú" aria-label="Abrir menú">
                <i class="fa-solid fa-bars"></i>
            </button>
            <span class="fw-semibold text-dark">ERP miPedido</span>
            <?php elseif (!empty($publicTopButtons)): ?>
            <div class="fw-semibold text-dark d-flex align-items-center gap-2">
                <i class="fa-solid fa-cubes text-primary"></i>
                <span>miPedido</span>
            </div>
            <nav class="d-none d-md-flex align-items-center gap-3 ms-2">
                <a href="#modulos" class="small text-decoration-none text-secondary">Módulos</a>
                <a href="#precios" class="small text-decoration-none text-secondary">Precios</a>
                <a href="#acerca" class="small text-decoration-none text-secondary">Acerca de</a>
                <a href="#confianza" class="small text-decoration-none text-secondary">Confianza</a>
            </nav>
            <?php else: ?>
            <a href="index.php?page=home" class="fw-semibold text-dark text-decoration-none">ERP miPedido</a>
            <?php endif; ?>
        </div>
        <?php if ($showSidebar && $usuarioNombre !== ''): ?>
        <?php
        $navNotifCount = count($navCabeceraNotifs);
        $navNotifBadge = $navNotifCount > 9 ? '9+' : (string) $navNotifCount;
        $navNotifUserId = (int) ($_SESSION['usuario']['id'] ?? 0);
        ?>
        <div class="d-flex align-items-center gap-2 flex-shrink-0">
            <div class="dropdown">
                <button type="button" class="btn btn-link text-secondary position-relative px-2 py-1 nav-notif-bell" id="dropdownNotificaciones" data-bs-toggle="dropdown" data-bs-auto-close="outside" data-user-id="<?= $navNotifUserId ?>" aria-expanded="false" aria-label="Notificaciones">
                    <i class="fa-solid fa-bell fa-lg" aria-hidden="true"></i>
                    <?php if ($navNotifCount > 0): ?>
                    <span id="navNotifBadge" class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size: 0.6rem; padding: 0.28em 0.42em;"><?= htmlspecialchars($navNotifBadge) ?></span>
                    <?php endif; ?>
                </button>
                <ul id="dropdownNotificacionesMenu" class="dropdown-menu <?= htmlspecialchars($notifDropdownAlignClass, ENT_QUOTES, 'UTF-8') ?> dropdown-menu-notifs shadow py-0 mt-1" aria-labelledby="dropdownNotificaciones" style="max-height: 420px; overflow-y: auto;">
                    <li class="px-3 py-2 notifs-header small fw-semibold">Avisos</li>
                    <?php if ($navNotifCount === 0): ?>
                    <li class="px-3 py-4 notifs-empty small">Sin avisos. Las tareas con límite en el calendario y las nuevas asignaciones aparecerán aquí.</li>
                    <?php else: ?>
                        <?php foreach ($navCabeceraNotifs as $notif): ?>
                            <?php
                            $nid = (int) ($notif['tarea_id'] ?? 0);
                            $ico = ($notif['icono'] ?? '') === 'tarea' ? 'fa-list-check' : 'fa-calendar-days';
                            $icoTone = ($notif['icono'] ?? '') === 'tarea' ? 'text-warning' : 'text-info';
                            $sec = trim((string) ($notif['secundario'] ?? ''));
                            ?>
                    <li>
                        <a class="dropdown-item small py-3 px-3 text-decoration-none nav-notif-item notif-nuevo d-flex gap-2 align-items-start" href="<?= htmlspecialchars($notif['href'] ?? 'index.php?page=tareas') ?>" data-notif-id="<?= $nid ?>">
                            <span class="nav-notif-icon <?= htmlspecialchars($icoTone, ENT_QUOTES, 'UTF-8') ?>" aria-hidden="true"><i class="fa-solid <?= htmlspecialchars($ico, ENT_QUOTES, 'UTF-8') ?>"></i></span>
                            <span class="flex-grow-1 min-w-0">
                                <span class="notif-titular d-block fw-medium"><?= htmlspecialchars((string) ($notif['texto'] ?? '')) ?></span>
                                <?php if ($sec !== ''): ?>
                                <span class="notif-secundario d-block"><?= htmlspecialchars($sec) ?></span>
                                <?php endif; ?>
                            </span>
                            <span class="nav-notif-dot mt-1" aria-hidden="true"></span>
                        </a>
                    </li>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </ul>
            </div>
            <div class="user-pill">
                <i class="fa-solid fa-circle-user" aria-hidden="true"></i>
                <span><?= htmlspecialchars($usuarioNombre) ?></span>
                <span class="user-role-tag role-<?= $usuarioRol === 'admin' ? 'admin' : 'empleado' ?>"><?= htmlspecialchars($usuarioRolLabel) ?></span>
            </div>
        </div>
        <?php elseif (!$showSidebar && !empty($publicTopButtons)): ?>
        <div class="d-flex gap-2">
            <a href="index.php?page=login" class="btn btn-outline-primary btn-sm">Iniciar sesión</a>
            <a href="index.php?page=register" class="btn btn-primary btn-sm">Crear cuenta</a>
        </div>
        <?php endif; ?>
    </header>

    <div class="content-wrap <?= !$showSidebar ? 'login-wrap' : '' ?>">
        <div class="container-fluid px-0 px-lg-2">
            <?= $content ?? '' ?>
        </div>
    </div>
</div>

<section id="cookieBanner" class="cookie-banner" hidden aria-label="Aviso de cookies">
    <div class="cookie-banner__title">Uso de cookies en miPedido</div>
    <div class="cookie-banner__text">
        Este proyecto usa cookies necesarias de sesión y, si lo aceptas, una cookie funcional para recordar el estado del menú lateral.
        También usa almacenamiento local para marcar avisos leídos. En este TFG es solo una demostración orientativa.
    </div>
    <div class="cookie-banner__actions">
        <button type="button" id="cookieAcceptBtn" class="btn btn-primary btn-sm">Aceptar cookies funcionales</button>
        <button type="button" id="cookieRejectBtn" class="btn btn-outline-secondary btn-sm">Solo necesarias</button>
    </div>
</section>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="js/main.js"></script>
</body>
</html>
