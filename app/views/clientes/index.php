<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Listado de Clientes</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
</head>
<body>
<div class="container mt-5">
    <h1>Clientes</h1>
    <a href="index.php?page=clientes_create" class="btn btn-success mb-3">Nuevo Cliente</a>
    <table class="table table-bordered table-striped">
        <thead>
            <tr>
                <th>ID</th>
                <th>Nombre</th>
                <th>Teléfono</th>
                <th>Email</th>
                <th>Dirección</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($clientes)): ?>
                <?php foreach ($clientes as $cliente): ?>
                    <tr>
                        <td><?= $cliente['id'] ?></td>
                        <td><?= htmlspecialchars($cliente['nombre']) ?></td>
                        <td><?= htmlspecialchars($cliente['telefono']) ?></td>
                        <td><?= htmlspecialchars($cliente['email']) ?></td>
                        <td><?= htmlspecialchars($cliente['direccion']) ?></td>
                        <td>
                            <a href="index.php?page=clientes_edit&id=<?= $cliente['id'] ?>" class="btn btn-primary btn-sm">Editar</a>
                            <a href="index.php?page=clientes_delete&id=<?= $cliente['id'] ?>" 
                               onclick="return confirm('¿Estás seguro de que quieres eliminar este cliente?');" 
                               class="btn btn-danger btn-sm">Eliminar</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="6" class="text-center">No hay clientes</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>
</body>
</html>