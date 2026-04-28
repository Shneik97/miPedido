<?php
/**
 * Listado de tareas: el admin ve todas; el empleado solo las asignadas a su usuario.
 * Espera $isAdmin definido por TareaController::index().
 */
$pageTitle = 'Tareas';
$currentNav = 'tareas';
$ok = $_GET['ok'] ?? '';
$err = $_GET['error'] ?? '';
ob_start();

$estMap = [
    'pendiente' => 'Pendiente',
    'en_curso' => 'En curso',
    'hecha' => 'Hecha',
];
?>
<div class="card page-card">
    <div class="card-body">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
            <h1 class="h4 mb-0">Tareas</h1>
            <?php if (!empty($isAdmin)): ?>
                <a href="index.php?page=tareas_create" class="btn btn-success btn-sm">
                    <i class="fa-solid fa-plus me-1"></i>Nueva tarea
                </a>
            <?php endif; ?>
        </div>
        <?php if ($ok === 'creada'): ?>
            <div class="alert alert-success py-2 small">Tarea creada correctamente.</div>
        <?php elseif ($ok === 'estado'): ?>
            <div class="alert alert-success py-2 small">Estado actualizado.</div>
        <?php endif; ?>
        <?php if ($err === 'estado' || $err === 'invalido'): ?>
            <div class="alert alert-warning py-2 small">No se pudo completar la operación.</div>
        <?php elseif ($err === 'csrf'): ?>
            <div class="alert alert-danger py-2 small">El formulario caducó. Recarga la página e inténtalo de nuevo.</div>
        <?php endif; ?>
        <div class="table-responsive">
            <table class="table table-hover table-striped align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>ID</th>
                        <th>Título</th>
                        <th>Asignado a</th>
                        <th>Creado por</th>
                        <th>Pedido</th>
                        <th>Límite</th>
                        <th>Estado</th>
                        <th class="text-end">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($tareas)): ?>
                        <?php foreach ($tareas as $t): ?>
                            <?php
                            $estTarea = (string) ($t['estado'] ?? '');
                            // Tras guardar: "En curso" → fila azul claro; "Hecha" → verde (permanente hasta que exista la fila).
                            $claseFilaTarea = '';
                            if ($estTarea === 'hecha') {
                                $claseFilaTarea = 'table-success';
                            } elseif ($estTarea === 'en_curso') {
                                $claseFilaTarea = 'table-info';
                            }
                            ?>
                            <tr id="tarea-<?= (int) $t['id'] ?>" class="<?= htmlspecialchars($claseFilaTarea) ?>">
                                <td><?= (int) $t['id'] ?></td>
                                <td>
                                    <strong><?= htmlspecialchars((string) $t['titulo']) ?></strong>
                                    <?php if (!empty($t['es_personal'])): ?>
                                        <span class="badge text-bg-secondary ms-1" style="font-size:0.65rem;">Propia</span>
                                    <?php endif; ?>
                                    <?php if (!empty($t['descripcion'])): ?>
                                        <div class="small text-muted"><?= nl2br(htmlspecialchars((string) $t['descripcion'])) ?></div>
                                    <?php endif; ?>
                                </td>
                                <td><?= htmlspecialchars((string) ($t['asignado_nombre'] ?? '')) ?></td>
                                <td><?= htmlspecialchars((string) ($t['creador_nombre'] ?? '')) ?></td>
                                <td><?= !empty($t['pedido_id']) ? (int) $t['pedido_id'] : '—' ?></td>
                                <td class="small"><?= !empty($t['fecha_limite']) ? htmlspecialchars((string) $t['fecha_limite']) : '—' ?></td>
                                <td><?= htmlspecialchars($estMap[(string) $t['estado']] ?? (string) $t['estado']) ?></td>
                                <td class="text-end">
                                    <?php if ((string) $t['estado'] !== 'hecha'): ?>
                                        <form action="index.php?page=tareas_estado" method="post" class="d-inline-flex gap-1 flex-wrap justify-content-end">
                                            <?= csrfInput() ?>
                                            <input type="hidden" name="id" value="<?= (int) $t['id'] ?>">
                                            <?php if ((string) $t['estado'] === 'pendiente'): ?>
                                                <button type="submit" name="estado" value="en_curso" class="btn btn-sm btn-outline-primary">En curso</button>
                                            <?php endif; ?>
                                            <button type="submit" name="estado" value="hecha" class="btn btn-sm btn-outline-success">Hecha</button>
                                        </form>
                                    <?php else: ?>
                                        <span class="text-muted small">Cerrada</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" class="text-center text-muted py-4">No hay tareas.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/main.php';
