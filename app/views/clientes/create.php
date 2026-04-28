<?php
$pageTitle = 'Nuevo cliente';
$currentNav = 'clientes';
ob_start();
?>
<div class="card page-card">
    <div class="card-body">
        <h1 class="h4 mb-4">Crear cliente</h1>
        <form action="index.php?page=clientes_store" method="POST" class="col-lg-8">
            <?= csrfInput() ?>
            <div class="mb-3">
                <label for="nombre" class="form-label">Nombre</label>
                <input type="text" name="nombre" id="nombre" class="form-control" required>
            </div>
            <div class="mb-3">
                <label for="telefono" class="form-label">Teléfono</label>
                <input type="text" name="telefono" id="telefono" class="form-control" required>
            </div>
            <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <input type="email" name="email" id="email" class="form-control" required>
            </div>
            <div class="mb-4">
                <label for="direccion" class="form-label">Dirección</label>
                <input type="text" name="direccion" id="direccion" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-success"><i class="fa-solid fa-floppy-disk me-1"></i>Guardar</button>
            <a href="index.php?page=clientes" class="btn btn-outline-secondary">Cancelar</a>
        </form>
    </div>
</div>
<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/main.php';
