<?php
require_once __DIR__ . '/../../helpers/auth_helper.php';
authEnsureSession();
$isAdmin = isAdmin();
$pageTitle = $pageTitle ?? 'ERP miPedido';
$showSidebar = $showSidebar ?? true;
$currentNav = $currentNav ?? '';
$usuarioNombre = $_SESSION['usuario']['nombre'] ?? '';
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
            --content-bg: #f1f5f9;
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
            min-height: 100vh;
            background: linear-gradient(180deg, #1e293b 0%, #0f172a 55%, #020617 100%);
            box-shadow: 4px 0 24px rgba(15, 23, 42, 0.35);
            transition: width 0.25s ease, transform 0.25s ease;
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
        .sidebar-brand i { color: #38bdf8; }
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
        .sidebar-nav .nav-link:hover {
            background: rgba(148, 163, 184, 0.12);
            color: #fff;
        }
        .sidebar-nav .nav-link.active {
            background: rgba(56, 189, 248, 0.18);
            color: #38bdf8;
        }
        .sidebar-nav .nav-link i {
            width: 1.25rem;
            text-align: center;
        }
        .app-main {
            margin-left: var(--sidebar-w);
            min-height: 100vh;
            transition: margin-left 0.25s ease;
        }
        .app-sidebar.collapsed ~ .app-main {
            margin-left: var(--sidebar-collapsed-w);
        }
        .app-main.no-sidebar {
            margin-left: 0 !important;
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
        }
        .top-navbar .user-pill {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            color: #334155;
            font-weight: 500;
        }
        .top-navbar .user-pill i { font-size: 1.35rem; color: #64748b; }
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
            .app-sidebar.mobile-open {
                transform: translateX(0);
            }
            .app-main {
                margin-left: 0 !important;
            }
        }
    </style>
</head>
<body class="<?= $showSidebar ? '' : 'login-body' ?>">

<?php if ($showSidebar): ?>
<aside class="app-sidebar" id="appSidebar" aria-label="Navegación principal">
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
        <?php if ($isAdmin): ?>
        <a class="nav-link <?= $currentNav === 'usuarios' ? 'active' : '' ?>" href="index.php?page=usuarios">
            <i class="fa-solid fa-user-shield"></i><span class="nav-text">Usuarios</span>
        </a>
        <a class="nav-link <?= $currentNav === 'config' ? 'active' : '' ?>" href="index.php?page=config">
            <i class="fa-solid fa-gear"></i><span class="nav-text">Configuración</span>
        </a>
        <?php endif; ?>
        <a class="nav-link text-warning-emphasis" href="index.php?page=logout">
            <i class="fa-solid fa-right-from-bracket"></i><span class="nav-text">Cerrar sesión</span>
        </a>
    </nav>
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
        <div class="user-pill">
            <i class="fa-solid fa-circle-user" aria-hidden="true"></i>
            <span><?= htmlspecialchars($usuarioNombre) ?></span>
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
