<?php
/**
 * Calendario mensual: rejilla L–D; tareas del jefe (asignadas) y personales (es_personal).
 * Variables desde CalendarioController: $year, $month, $semanas, $porDia, $ymAnterior, $ymSiguiente, $calendarioDbError, $isAdmin (layout).
 * Nivel estudiante: se dibuja por semanas; cada celda representa un dia.
 */
$pageTitle = 'Calendario';
$currentNav = 'calendario';
$ok = $_GET['ok'] ?? '';
$err = $_GET['error'] ?? '';

$nombresMes = [
    1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril', 5 => 'Mayo', 6 => 'Junio',
    7 => 'Julio', 8 => 'Agosto', 9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre',
];
$tituloMes = ($nombresMes[$month] ?? '') . ' ' . $year;
$diasCabecera = ['Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb', 'Dom'];
$hoyYmd = date('Y-m-d');
ob_start();
?>
<style>
    .cal-grid { display: grid; grid-template-columns: repeat(7, 1fr); gap: 2px; }
    .cal-cell {
        min-height: 7.5rem;
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 0.35rem;
        padding: 0.35rem 0.45rem;
        font-size: 0.8rem;
    }
    .cal-cell.otro-mes { background: #f8fafc; color: #94a3b8; }
    .cal-cell.hoy { outline: 2px solid #38bdf8; outline-offset: -1px; }
    .cal-dia-num { font-weight: 600; color: #0f172a; margin-bottom: 0.25rem; }
    .cal-cell.otro-mes .cal-dia-num { color: #94a3b8; }
    .cal-tarea-line { line-height: 1.25; margin-bottom: 0.25rem; }
    /* Misma idea que en la tabla de tareas: hecha = verde, en curso = azul claro */
    .cal-tarea-line.cal-tarea-estado-hecha {
        background: rgba(16, 185, 129, 0.18);
        border-left: 3px solid #10b981;
        border-radius: 0.3rem;
        padding: 0.2rem 0.35rem;
    }
    .cal-tarea-line.cal-tarea-estado-curso {
        background: rgba(59, 130, 246, 0.12);
        border-left: 3px solid #3b82f6;
        border-radius: 0.3rem;
        padding: 0.2rem 0.35rem;
    }
    .cal-tarea-line.cal-tarea-estado-hecha a { color: #047857 !important; }
    .cal-cab { font-weight: 600; font-size: 0.75rem; text-transform: uppercase; color: #64748b; padding: 0.35rem 0.25rem; text-align: center; }
</style>

<div class="row g-4">
    <div class="col-lg-8">
        <div class="card page-card">
            <div class="card-body">
                <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
                    <h1 class="h4 mb-0"><i class="fa-solid fa-calendar-days me-2 text-primary"></i><?= htmlspecialchars($tituloMes) ?></h1>
                    <div class="btn-group">
                        <a class="btn btn-outline-secondary btn-sm" href="index.php?page=calendario&amp;ym=<?= htmlspecialchars($ymAnterior) ?>" title="Mes anterior">
                            <i class="fa-solid fa-chevron-left"></i>
                        </a>
                        <a class="btn btn-outline-secondary btn-sm" href="index.php?page=calendario" title="Mes actual">Hoy</a>
                        <a class="btn btn-outline-secondary btn-sm" href="index.php?page=calendario&amp;ym=<?= htmlspecialchars($ymSiguiente) ?>" title="Mes siguiente">
                            <i class="fa-solid fa-chevron-right"></i>
                        </a>
                    </div>
                </div>
                <?php if (!empty($calendarioDbError)): ?>
                    <div class="alert alert-warning small mb-3">No se pudieron cargar las tareas. ¿Has ejecutado <code>config/migrate_fase5_calendario_es_personal.sql</code> en MySQL?</div>
                <?php endif; ?>
                <?php if ($ok === 'personal'): ?>
                    <div class="alert alert-success py-2 small mb-3">Tarea personal guardada en el calendario.</div>
                <?php endif; ?>
                <?php if ($err === 'personal'): ?>
                    <div class="alert alert-danger py-2 small mb-3">Revisa título y fecha.</div>
                <?php elseif ($err === 'csrf'): ?>
                    <div class="alert alert-danger py-2 small mb-3">El formulario caducó. Recarga la página e inténtalo de nuevo.</div>
                <?php endif; ?>
                <p class="text-muted small mb-3">
                    <span class="badge text-bg-primary me-1">Asignada</span> la puso otra persona (p. ej. tu jefe).
                    <span class="badge text-bg-secondary me-1">Propia</span> la creaste tú desde este calendario.
                    El día de cada tarea es la <strong>fecha límite</strong> si existe; si no, el día en que se <strong>creó</strong>.
                    <span class="d-block mt-1">Estado: <span class="badge text-bg-success">Hecha</span> verde · <span class="badge text-bg-info text-dark">En curso</span> azul.</span>
                </p>
                <div class="cal-grid mb-1">
                    <?php foreach ($diasCabecera as $cab): ?>
                        <div class="cal-cab"><?= htmlspecialchars($cab) ?></div>
                    <?php endforeach; ?>
                </div>
                <?php foreach ($semanas as $semana): ?>
                    <div class="cal-grid mb-2">
                        <?php foreach ($semana as $dt): ?>
                            <?php
                            // Cada celda sabe si pertenece al mes actual y si es el dia de hoy.
                            $ymd = $dt->format('Y-m-d');
                            $esMesActual = ((int) $dt->format('n') === $month && (int) $dt->format('Y') === $year);
                            $claseCelda = 'cal-cell' . ($esMesActual ? '' : ' otro-mes') . ($ymd === $hoyYmd ? ' hoy' : '');
                            $lista = $porDia[$ymd] ?? [];
                            ?>
                            <div class="<?= htmlspecialchars($claseCelda) ?>">
                                <div class="cal-dia-num"><?= (int) $dt->format('j') ?></div>
                                <?php foreach ($lista as $tar): ?>
                                    <?php
                                    // Badge "Propia/Asignada" + color segun estado de tarea.
                                    $esPropia = !empty($tar['es_personal']);
                                    $badgeClass = $esPropia ? 'text-bg-secondary' : 'text-bg-primary';
                                    $est = (string) ($tar['estado'] ?? '');
                                    $claseEstado = '';
                                    if ($est === 'hecha') {
                                        $claseEstado = ' cal-tarea-estado-hecha';
                                    } elseif ($est === 'en_curso') {
                                        $claseEstado = ' cal-tarea-estado-curso';
                                    }
                                    ?>
                                    <div class="cal-tarea-line<?= $claseEstado ?>">
                                        <span class="badge <?= $badgeClass ?> me-1" style="font-size:0.65rem;"><?= $esPropia ? 'Propia' : 'Asignada' ?></span>
                                        <?php if ($est === 'hecha'): ?>
                                            <span class="badge text-bg-success me-1" style="font-size:0.65rem;">Hecha</span>
                                        <?php elseif ($est === 'en_curso'): ?>
                                            <span class="badge text-bg-info text-dark me-1" style="font-size:0.65rem;">En curso</span>
                                        <?php endif; ?>
                                        <a href="index.php?page=tareas" class="link-dark text-decoration-none" title="Ir al listado de tareas">
                                            <?= htmlspecialchars((string) $tar['titulo']) ?>
                                        </a>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card page-card">
            <div class="card-body">
                <h2 class="h5 mb-3">Nueva tarea personal</h2>
                <p class="text-muted small">Aparecerá en el día que elijas (como recordatorio tuyo). También la verás en <strong>Tareas</strong>.</p>
                <form action="index.php?page=calendario_personal_store" method="post">
                    <?= csrfInput() ?>
                    <div class="mb-2">
                        <label class="form-label small" for="cal_titulo">Título</label>
                        <input type="text" class="form-control form-control-sm" name="titulo" id="cal_titulo" required maxlength="200" placeholder="Ej.: Llamar al proveedor">
                    </div>
                    <div class="mb-2">
                        <label class="form-label small" for="cal_fecha">Día en el calendario</label>
                        <input type="date" class="form-control form-control-sm" name="fecha" id="cal_fecha" required value="<?= htmlspecialchars(sprintf('%04d-%02d-01', $year, $month)) ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label small" for="cal_desc">Notas (opcional)</label>
                        <textarea class="form-control form-control-sm" name="descripcion" id="cal_desc" rows="2"></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary btn-sm w-100">
                        <i class="fa-solid fa-plus me-1"></i>Añadir al calendario
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/main.php';
