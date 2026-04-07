<?php
$pageTitle = 'Editar cliente';
$currentNav = 'clientes';
ob_start();
?>
<div class="card page-card">
    <div class="card-body">
        <h1 class="h4 mb-4">Editar cliente</h1>
        <form action="index.php?page=clientes_update" method="POST" class="col-lg-8">
            <input type="hidden" name="id" value="<?= (int) $c['id'] ?>">
            <div class="mb-3">
                <label for="nombre" class="form-label">Nombre</label>
                <input type="text" name="nombre" id="nombre" class="form-control" value="<?= htmlspecialchars($c['nombre']) ?>" required>
            </div>
            <div class="mb-3">
                <label for="telefono" class="form-label">Teléfono</label>
                <input type="text" name="telefono" id="telefono" class="form-control" value="<?= htmlspecialchars((string) $c['telefono']) ?>" required>
            </div>
            <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <input type="email" name="email" id="email" class="form-control" value="<?= htmlspecialchars((string) $c['email']) ?>" required>
            </div>
            <div class="mb-4">
                <label for="direccion" class="form-label">Dirección</label>
                <input type="text" name="direccion" id="direccion" class="form-control" value="<?= htmlspecialchars((string) $c['direccion']) ?>" required>
            </div>
            <button type="submit" class="btn btn-primary"><i class="fa-solid fa-check me-1"></i>Actualizar</button>
            <a href="index.php?page=clientes" class="btn btn-outline-secondary">Cancelar</a>
        </form>
    </div>
</div>
<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/main.php';
