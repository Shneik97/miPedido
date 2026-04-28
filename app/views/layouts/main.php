<?php
require_once __DIR__ . '/../../helpers/auth_helper.php';
require_once __DIR__ . '/../../helpers/nav_helper.php';
require_once __DIR__ . '/../../helpers/preferencias_ui_helper.php';
authEnsureSession();
$isAdmin = isAdmin();
$pageTitle = $pageTitle ?? 'ERP miPedido';
$showSidebar = $showSidebar ?? true;
$currentNav = $currentNav ?? '';
$usuarioNombre = $_SESSION['usuario']['nombre'] ?? '';
$navTareasPendientes = 0;
$navCabeceraNotifs = [];
if ($showSidebar && isset($_SESSION['usuario']['id'])) {
    $navTareasPendientes = contarTareasPendientesNav((int) $_SESSION['usuario']['id'], $isAdmin);
    $navCabeceraNotifs = notificacionesCabecera((int) $_SESSION['usuario']['id'], $isAdmin);
}
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
    <style>
        :root {
            --sidebar-w: 260px;
            --sidebar-collapsed-w: 72px;
            --content-bg: <?= htmlspecialchars($fondoTrabajoHex, ENT_QUOTES, 'UTF-8') ?>;
            --accent: <?= htmlspecialchars($accentPair[0], ENT_QUOTES, 'UTF-8') ?>;
            --accent-rgb: <?= htmlspecialchars($accentPair[1], ENT_QUOTES, 'UTF-8') ?>;
        }
        body {
            min-height: 100vh;
            background: var(--content-bg);
        }
        .app-sidebar {
            position: fixed;
            top: 0;
            left: 0;
            z-index: 1040;
            width: var(--sidebar-w);
            height: 100vh;
            background: linear-gradient(180deg, #1e293b 0%, #0f172a 55%, #020617 100%);
            box-shadow: 4px 0 24px rgba(15, 23, 42, 0.35);
            transition: width 0.25s ease, transform 0.25s ease;
            display: flex;
            flex-direction: column;
            overflow-y: auto;
            overflow-x: hidden;
            scrollbar-width: thin;
            scrollbar-color: rgba(148, 163, 184, 0.45) transparent;
        }
        .app-sidebar::-webkit-scrollbar { width: 8px; }
        .app-sidebar::-webkit-scrollbar-track { background: transparent; }
        .app-sidebar::-webkit-scrollbar-thumb {
            background: rgba(148, 163, 184, 0.45);
            border-radius: 999px;
        }
        .app-sidebar::-webkit-scrollbar-thumb:hover {
            background: rgba(148, 163, 184, 0.65);
        }
        .app-sidebar.collapsed {
            width: var(--sidebar-collapsed-w);
        }
        .app-sidebar.collapsed .nav-text,
        .app-sidebar.collapsed .sidebar-brand span {
            display: none;
        }
        .app-sidebar.collapsed .sidebar-brand {
            justify-content: center;
            padding-left: 0;
            padding-right: 0;
        }
        /* Iconos centrados al colapsar */
        .app-sidebar.collapsed .sidebar-nav .nav-link {
            justify-content: center;
            padding-left: 0.45rem;
            padding-right: 0.45rem;
        }
        /* Tareas con glovo: expandido = icono · texto · número; colapsado = glovo esquina superior derecha del icono */
        .nav-link--tareas-badge .nav-tareas-icon-wrap {
            display: contents;
        }
        .nav-link--tareas-badge .nav-tareas-icon-wrap > i {
            order: 1;
        }
        .nav-link--tareas-badge > .nav-text {
            order: 2;
        }
        .nav-link--tareas-badge .nav-tareas-icon-wrap > .nav-tareas-glovo {
            order: 3;
            margin-left: 0.35rem;
        }
        .app-sidebar.collapsed .nav-link--tareas-badge .nav-tareas-icon-wrap {
            display: inline-flex !important;
            position: relative;
            width: 1.45rem;
            height: 1.45rem;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }
        .app-sidebar.collapsed .nav-link--tareas-badge .nav-tareas-icon-wrap > i,
        .app-sidebar.collapsed .nav-link--tareas-badge .nav-tareas-icon-wrap > .nav-tareas-glovo {
            order: unset !important;
        }
        .app-sidebar.collapsed .nav-link--tareas-badge .nav-tareas-icon-wrap > .nav-tareas-glovo {
            position: absolute;
            top: -0.08rem;
            right: -0.4rem;
            left: auto;
            margin: 0 !important;
            padding: 0.12rem 0.38rem;
            font-size: 0.6rem;
            font-weight: 700;
            line-height: 1;
            min-width: 1.1rem;
            text-align: center;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.35);
            z-index: 2;
            pointer-events: none;
        }
        .sidebar-brand {
            padding: 1.25rem 1rem;
            color: #f8fafc;
            font-weight: 700;
            font-size: 1.05rem;
            letter-spacing: 0.02em;
            border-bottom: 1px solid rgba(148, 163, 184, 0.15);
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        .sidebar-brand i { color: var(--accent); }
        .sidebar-nav {
            padding-bottom: 0.5rem;
            flex: 0 0 auto;
        }
        .sidebar-nav .nav-link {
            color: #cbd5e1;
            padding: 0.75rem 1.25rem;
            margin: 0.15rem 0.5rem;
            border-radius: 0.5rem;
            display: flex;
            align-items: center;
            gap: 0.65rem;
            transition: background 0.15s, color 0.15s;
        }
        .nav-link--tareas-badge {
            flex-wrap: nowrap;
        }
        .sidebar-nav .nav-link:hover {
            background: rgba(148, 163, 184, 0.12);
            color: #fff;
        }
        .sidebar-nav .nav-link.active {
            background: rgba(var(--accent-rgb), 0.2);
            color: var(--accent);
        }
        .sidebar-nav .nav-link i {
            width: 1.25rem;
            text-align: center;
        }
        .sidebar-footer {
            margin-top: auto;
            padding: 0.2rem 0.5rem 0.55rem;
            border-top: 1px solid rgba(148, 163, 184, 0.14);
        }
        .sidebar-watermark {
            display: block;
            width: 100%;
            text-align: center;
            color: rgba(203, 213, 225, 0.24);
            font-size: 0.68rem;
            font-weight: 500;
            letter-spacing: 0.06em;
            text-transform: uppercase;
            user-select: none;
            pointer-events: none;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            margin-top: 0.55rem;
            padding-bottom: 0.1rem;
        }
        .app-sidebar.collapsed .sidebar-watermark {
            font-size: 0.6rem;
            letter-spacing: 0.04em;
            color: rgba(203, 213, 225, 0.2);
            margin-top: 0.35rem;
        }
        .app-main {
            box-sizing: border-box;
            width: calc(100% - var(--sidebar-w));
            margin-left: var(--sidebar-w);
            min-height: 100vh;
            min-width: 0;
            transition: margin-left 0.25s ease, width 0.25s ease;
        }
        .app-sidebar.collapsed ~ .app-main {
            width: calc(100% - var(--sidebar-collapsed-w));
            margin-left: var(--sidebar-collapsed-w);
        }
        body.sidebar-derecha .app-sidebar {
            left: auto;
            right: 0;
            box-shadow: -4px 0 24px rgba(15, 23, 42, 0.35);
        }
        body.sidebar-derecha .app-main {
            margin-left: 0;
            margin-right: var(--sidebar-w);
            width: calc(100% - var(--sidebar-w));
            transition: margin-right 0.25s ease, margin-left 0.25s ease, width 0.25s ease;
        }
        body.sidebar-derecha .app-sidebar.collapsed ~ .app-main {
            margin-left: 0;
            margin-right: var(--sidebar-collapsed-w);
            width: calc(100% - var(--sidebar-collapsed-w));
        }
        body.tema-oscuro .top-navbar {
            background: #1e293b;
            border-bottom-color: #334155;
        }
        body.tema-oscuro .top-navbar .fw-semibold.text-dark {
            color: #f1f5f9 !important;
        }
        body.tema-oscuro .top-navbar .user-pill {
            color: #e2e8f0 !important;
        }
        body.tema-oscuro .top-navbar .user-pill i {
            color: #94a3b8 !important;
        }
        body.tema-oscuro .top-navbar .btn-outline-secondary {
            --bs-btn-color: #cbd5e1;
            --bs-btn-border-color: #475569;
            --bs-btn-hover-bg: #334155;
            --bs-btn-hover-border-color: #64748b;
        }
        body.tema-oscuro .card.page-card {
            background: #1e293b;
            color: #e2e8f0;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.35);
        }
        body.tema-oscuro .text-muted {
            color: #94a3b8 !important;
        }
        body.tema-oscuro .card.page-card .text-dark {
            color: #f8fafc !important;
        }
        body.tema-oscuro .table {
            --bs-table-bg: transparent;
            color: #e2e8f0;
        }
        .app-main.no-sidebar {
            margin-left: 0 !important;
            margin-right: 0 !important;
            width: 100% !important;
        }
        .top-navbar {
            background: #fff;
            border-bottom: 1px solid #e2e8f0;
            padding: 0.65rem 1.5rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 0.75rem;
            max-width: 100%;
            box-sizing: border-box;
        }
        /* Con barra a la derecha: cabecera alineada al contenido y menú junto al lateral */
        body.sidebar-derecha .top-navbar {
            flex-direction: row-reverse;
        }
        .top-navbar .user-pill {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            color: #334155;
            font-weight: 500;
        }
        .top-navbar .user-pill i { font-size: 1.35rem; color: #64748b; }
        .nav-notif-bell { text-decoration: none !important; }
        .nav-notif-bell .fa-bell { vertical-align: middle; }
        #dropdownNotificacionesMenu.dropdown-menu-notifs {
            background: #1e293b;
            border: 1px solid rgba(148, 163, 184, 0.2);
            min-width: 320px;
            max-width: min(400px, 94vw);
        }
        #dropdownNotificacionesMenu .notifs-header {
            color: #e2e8f0;
            background: #0f172a;
            border-bottom: 1px solid rgba(148, 163, 184, 0.25);
        }
        #dropdownNotificacionesMenu .nav-notif-item {
            border-bottom: 1px solid rgba(148, 163, 184, 0.12);
            color: #f8fafc;
            white-space: normal;
        }
        #dropdownNotificacionesMenu .nav-notif-item:hover,
        #dropdownNotificacionesMenu .nav-notif-item:focus {
            background: #334155;
            color: #fff;
        }
        #dropdownNotificacionesMenu .nav-notif-item.notif-nuevo {
            background: #1e293b;
        }
        #dropdownNotificacionesMenu .nav-notif-item.notif-visto {
            background: #0f172a;
            color: #94a3b8;
        }
        #dropdownNotificacionesMenu .nav-notif-item.notif-visto .notif-titular {
            color: #94a3b8;
        }
        #dropdownNotificacionesMenu .notif-secundario {
            color: #94a3b8;
            font-size: 0.78rem;
            line-height: 1.35;
            margin-top: 0.2rem;
        }
        #dropdownNotificacionesMenu .nav-notif-item.notif-visto .notif-secundario {
            color: #64748b;
        }
        #dropdownNotificacionesMenu .nav-notif-item.notif-visto .nav-notif-dot {
            display: none !important;
        }
        #dropdownNotificacionesMenu .nav-notif-icon {
            width: 1.35rem;
            text-align: center;
            flex-shrink: 0;
            margin-top: 0.1rem;
        }
        #dropdownNotificacionesMenu .nav-notif-dot {
            width: 0.5rem;
            height: 0.5rem;
            border-radius: 50%;
            background: #38bdf8;
            flex-shrink: 0;
            box-shadow: 0 0 0 2px rgba(56, 189, 248, 0.25);
        }
        #dropdownNotificacionesMenu .notifs-empty {
            color: #94a3b8;
            background: #1e293b;
        }
        .content-wrap {
            padding: 1.5rem;
        }
        .card.page-card {
            border: none;
            box-shadow: 0 1px 3px rgba(15, 23, 42, 0.08);
        }
        .login-wrap {
            min-height: calc(100vh - 2rem);
            display: flex;
            align-items: center;
        }
        @media (max-width: 991.98px) {
            .app-sidebar {
                transform: translateX(-100%);
            }
            body.sidebar-derecha .app-sidebar {
                transform: translateX(100%);
            }
            .app-sidebar.mobile-open {
                transform: translateX(0) !important;
            }
            .app-main {
                margin-left: 0 !important;
                margin-right: 0 !important;
                width: 100% !important;
            }
            body.sidebar-derecha .top-navbar {
                flex-direction: row;
            }
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
        <a class="nav-link <?= $currentNav === 'config' ? 'active' : '' ?>" href="index.php?page=config">
            <i class="fa-solid fa-gear"></i><span class="nav-text">Configuración</span>
        </a>
        <?php endif; ?>
    </nav>
    <div class="sidebar-footer">
        <!-- Cerrar sesión queda siempre visible al pie del sidebar -->
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
            <?php endif; ?>
            <span class="fw-semibold text-dark">ERP miPedido</span>
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
            </div>
        </div>
        <?php elseif (!$showSidebar): ?>
        <span class="text-muted small">Acceso al sistema</span>
        <?php endif; ?>
    </header>

    <div class="content-wrap <?= !$showSidebar ? 'login-wrap' : '' ?>">
        <div class="container-fluid px-0 px-lg-2">
            <?= $content ?? '' ?>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="js/main.js"></script>
</body>
</html>
