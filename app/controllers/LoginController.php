<?php
session_start();
require_once '../config/database.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $email = $_POST['email'];
    $password = $_POST['password'];

    // ✅ SOLUCIÓN: Instanciamos la clase y obtenemos la conexión
    $database = new Database();
    $pdo = $database->connect();

    // Ahora $pdo ya existe y puedes usar el prepare
    $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE email = ?");
    $stmt->execute([$email]);

    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($usuario) {
        // ⚠️ de momento simple (luego metemos hash)
        if ($password == $usuario['password']) {

            $_SESSION['usuario'] = $usuario;

            $_SESSION['usuario'] = $usuario;

// Redirigir según rol
if ($usuario['rol'] == 'admin') {
    header("Location: index.php?page=dashboard");
} else {
    header("Location: index.php?page=dashboard");
}
exit;
        } else {
            echo "Contraseña incorrecta";
        }

    } else {
        echo "Usuario no encontrado";
    }
}