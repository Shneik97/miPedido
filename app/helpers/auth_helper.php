<?php

/**
 * Detecta si la petición actual viene por HTTPS.
 * Se usa para activar la cookie Secure solo cuando corresponde.
 */
function authIsHttps(): bool
{
    if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') {
        return true;
    }
    return (string) ($_SERVER['SERVER_PORT'] ?? '') === '443';
}

/**
 * Asegura que la sesión PHP esté iniciada con cookies más seguras.
 * Nota: en local (http) la cookie Secure no se fuerza para no romper el login.
 */
function authEnsureSession(): void
{
    if (session_status() !== PHP_SESSION_NONE) {
        return;
    }

    $secure = authIsHttps();
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'domain' => '',
        'secure' => $secure,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);

    ini_set('session.use_strict_mode', '1');
    session_start();
}

/**
 * Comprueba que el usuario autenticado tenga el rol indicado.
 * Si no coincide, redirige a la vista de acceso denegado (403).
 */
function checkRole(string $role): void
{
    authEnsureSession();
    if (!isset($_SESSION['usuario']['rol']) || $_SESSION['usuario']['rol'] !== $role) {
        header('Location: index.php?page=403');
        exit;
    }
}

/**
 * Indica si el usuario actual es administrador.
 */
function isAdmin(): bool
{
    authEnsureSession();
    return isset($_SESSION['usuario']['rol']) && $_SESSION['usuario']['rol'] === 'admin';
}

/**
 * Devuelve el token CSRF actual, creándolo si no existía.
 */
function csrfToken(): string
{
    authEnsureSession();
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return (string) $_SESSION['csrf_token'];
}

/**
 * Imprime un input hidden para incluir el token en formularios POST.
 */
function csrfInput(): string
{
    $token = csrfToken();
    return '<input type="hidden" name="_csrf" value="' . htmlspecialchars($token, ENT_QUOTES, 'UTF-8') . '">';
}

/**
 * Comprueba si el token CSRF enviado en POST es válido.
 */
function csrfIsValidRequest(): bool
{
    authEnsureSession();
    $sent = (string) ($_POST['_csrf'] ?? '');
    $sessionToken = (string) ($_SESSION['csrf_token'] ?? '');
    return $sent !== '' && $sessionToken !== '' && hash_equals($sessionToken, $sent);
}
