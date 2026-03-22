<?php
session_start();

if (!isset($_SESSION['usuario'])) {
    header("Location: index.php");
    exit;
}
?>

<h2>Bienvenido <?php echo $_SESSION['usuario']['nombre']; ?> 👋</h2>

<p>Este es tu dashboard</p>

<a href="index.php?page=logout">Cerrar sesión</a>