<?php
$pageTitle = 'Mi entorno';
$currentNav = 'mi_entorno';
$err = $_GET['err'] ?? '';
ob_start();
?>
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card page-card">
            <div class="card-body">
                <h1 class="h4 mb-2">Mi entorno</h1>
                <p class="text-muted small mb-4">Personaliza el aspecto del ERP en este dispositivo. Los cambios se guardan en tu usuario.</p>
                <?php if (!empty($ok)): ?>
                    <div class="alert alert-success py-2 small">Preferencias guardadas.</div>
                <?php endif; ?>
                <?php if (!empty($errDb)): ?>
                    <div class="alert alert-danger py-2 small">No se pudo guardar. Si acabas de actualizar el proyecto, ejecuta en MySQL el script <code>config/migrate_fase6_preferencias_ui.sql</code>.</div>
                <?php elseif ($err === 'csrf'): ?>
                    <div class="alert alert-danger py-2 small">El formulario caducó. Recarga la página e inténtalo de nuevo.</div>
                <?php endif; ?>
                <form method="post" action="index.php?page=mi_entorno_update" class="row g-3">
                    <?= csrfInput() ?>
                    <div class="col-md-6">
                        <label class="form-label" for="sidebar_pos">Barra lateral</label>
                        <select class="form-select" name="sidebar_pos" id="sidebar_pos">
                            <option value="izquierda" <?= ($prefs['sidebar_pos'] ?? '') === 'izquierda' ? 'selected' : '' ?>>Izquierda</option>
                            <option value="derecha" <?= ($prefs['sidebar_pos'] ?? '') === 'derecha' ? 'selected' : '' ?>>Derecha</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="fondo_trabajo">Fondo del área de trabajo</label>
                        <select class="form-select" name="fondo_trabajo" id="fondo_trabajo">
                            <option value="slate" <?= ($prefs['fondo_trabajo'] ?? '') === 'slate' ? 'selected' : '' ?>>Gris pizarra (por defecto)</option>
                            <option value="blanco" <?= ($prefs['fondo_trabajo'] ?? '') === 'blanco' ? 'selected' : '' ?>>Blanco roto</option>
                            <option value="humo" <?= ($prefs['fondo_trabajo'] ?? '') === 'humo' ? 'selected' : '' ?>>Gris humo</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="acento">Color de acento (menú activo, icono)</label>
                        <select class="form-select" name="acento" id="acento">
                            <option value="cyan" <?= ($prefs['acento'] ?? '') === 'cyan' ? 'selected' : '' ?>>Cian</option>
                            <option value="azul" <?= ($prefs['acento'] ?? '') === 'azul' ? 'selected' : '' ?>>Azul</option>
                            <option value="violeta" <?= ($prefs['acento'] ?? '') === 'violeta' ? 'selected' : '' ?>>Violeta</option>
                            <option value="verde" <?= ($prefs['acento'] ?? '') === 'verde' ? 'selected' : '' ?>>Verde</option>
                            <option value="naranja" <?= ($prefs['acento'] ?? '') === 'naranja' ? 'selected' : '' ?>>Naranja</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="tema_contenido">Tema del contenido</label>
                        <select class="form-select" name="tema_contenido" id="tema_contenido">
                            <option value="claro" <?= ($prefs['tema_contenido'] ?? '') === 'claro' ? 'selected' : '' ?>>Claro</option>
                            <option value="oscuro" <?= ($prefs['tema_contenido'] ?? '') === 'oscuro' ? 'selected' : '' ?>>Oscuro (cabecera y tarjetas)</option>
                        </select>
                    </div>
                    <div class="col-12">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="sidebar_collapsed" value="1" id="sidebar_collapsed" <?= !empty($prefs['sidebar_collapsed']) ? 'checked' : '' ?>>
                            <label class="form-check-label" for="sidebar_collapsed">Iniciar con el menú lateral colapsado (solo pantallas grandes)</label>
                        </div>
                    </div>
                    <div class="col-12">
                        <button type="submit" class="btn btn-primary">Guardar preferencias</button>
                        <a href="index.php?page=dashboard" class="btn btn-outline-secondary ms-1">Volver al panel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/main.php';
