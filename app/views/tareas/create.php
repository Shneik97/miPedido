<?php
/**
 * Formulario solo administrador: alta de tarea y asignación a un usuario.
 */
$pageTitle = 'Nueva tarea';
$currentNav = 'tareas';
$err = $_GET['error'] ?? '';
ob_start();
?>
<div class="card page-card">
    <div class="card-body">
        <h1 class="h4 mb-4">Crear tarea</h1>
        <?php if ($err === 'invalido'): ?>
            <div class="alert alert-danger py-2 small">Revisa título y usuario asignado.</div>
        <?php elseif ($err === 'csrf'): ?>
            <div class="alert alert-danger py-2 small">El formulario caducó. Recarga la página e inténtalo de nuevo.</div>
        <?php endif; ?>
        <form action="index.php?page=tareas_store" method="POST" class="col-lg-8">
            <?= csrfInput() ?>
            <div class="mb-3">
                <label for="titulo" class="form-label">Título</label>
                <input type="text" name="titulo" id="titulo" class="form-control" required maxlength="200">
            </div>
            <div class="mb-3">
                <label for="descripcion" class="form-label">Descripción</label>
                <textarea name="descripcion" id="descripcion" class="form-control" rows="3"></textarea>
            </div>
            <div class="mb-3">
                <label for="asignado_a" class="form-label">Asignar a</label>
                <select name="asignado_a" id="asignado_a" class="form-select" required>
                    <option value="">— Selecciona usuario —</option>
                    <?php foreach ($usuarios as $u): ?>
                        <option value="<?= (int) $u['id'] ?>">
                            <?= htmlspecialchars((string) $u['nombre']) ?> (<?= htmlspecialchars((string) $u['email']) ?> — <?= htmlspecialchars((string) $u['rol']) ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="mb-3">
                <label for="pedido_id" class="form-label">Pedido relacionado (opcional)</label>
                <select name="pedido_id" id="pedido_id" class="form-select">
                    <option value="0">— Ninguno —</option>
                    <?php foreach ($pedidos as $p): ?>
                        <option value="<?= (int) $p['id'] ?>">
                            #<?= (int) $p['id'] ?> — <?= htmlspecialchars((string) $p['cliente_nombre']) ?> (<?= htmlspecialchars((string) $p['estado']) ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="mb-4">
                <label for="fecha_limite" class="form-label">Fecha límite (opcional)</label>
                <input type="datetime-local" name="fecha_limite" id="fecha_limite" class="form-control">
            </div>
            <button type="submit" class="btn btn-success"><i class="fa-solid fa-floppy-disk me-1"></i>Guardar</button>
            <a href="index.php?page=tareas" class="btn btn-outline-secondary">Cancelar</a>
        </form>
    </div>
</div>
<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/main.php';
