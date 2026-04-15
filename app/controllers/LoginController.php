<?php
session_start();
require_once __DIR__ . '/../../config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    $database = new Database();
    $pdo = $database->connect();

    if ($pdo === null) {
        $_SESSION['login_error'] = 'No se pudo conectar a la base de datos. Revisa usuario, contraseña y puerto en config/database.php o ejecuta config/fix_mysql_app_user.sql en MySQL.';
        header('Location: index.php');
        exit;
    }

    $stmt = $pdo->prepare('SELECT * FROM usuarios WHERE email = ?');
    $stmt->execute([$email]);

    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($usuario && password_verify($password, $usuario['password'])) {
        unset($usuario['password']);
        $_SESSION['usuario'] = $usuario;
        header('Location: index.php?page=dashboard');
        exit;
    }

    $_SESSION['login_error'] = $usuario
        ? 'Contraseña incorrecta.'
        : 'Usuario no encontrado.';
}
