<?php

/**
 * AUTH HELPER (nivel estudiante)
 * --------------------------------
 * Este archivo reúne funciones de seguridad que se usan en muchos controladores.
 *
 * ¿Qué problema resuelve?
 * - Evitar repetir en cada controlador la misma lógica de sesión, rol y CSRF.
 * - Tener un punto único para reglas de autenticación.
 *
 * ¿Qué incluye?
 * - Detección de HTTPS.
 * - Inicio seguro de sesión.
 * - Comprobación de rol (admin/empleado).
 * - Token CSRF para formularios POST.
 * - Lectura de workspace del usuario logueado.
 */

/**
 * Detecta si la petición actual viene por HTTPS.
 * Se usa para activar la cookie Secure solo cuando corresponde.
 * Ejemplo:
 * - Si SERVER_PORT=443 -> true
 * - Si estás en local por http://localhost -> false
 */
function authIsHttps(): bool
{
    if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') {
        return true;
    }
    $forwarded = strtolower((string) ($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? ''));
    if ($forwarded === 'https') {
        return true;
    }
    return (string) ($_SERVER['SERVER_PORT'] ?? '') === '443';
}

/**
 * Asegura que la sesión PHP esté iniciada con cookies más seguras.
 * Nota: en local (http) la cookie Secure no se fuerza para no romper el login.
 * Ejemplo:
 * - Primera llamada: configura cookie + abre sesión.
 * - Siguientes llamadas: no hace nada (ya existe sesión).
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

    // strict_mode evita aceptar IDs de sesión "inventados".
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
 * Devuelve la clave de espacio de trabajo del usuario autenticado.
 * Si falta (cuentas antiguas), devuelve cadena vacía.
 */
function currentWorkspaceKey(): string
{
    authEnsureSession();
    return trim((string) ($_SESSION['usuario']['workspace_key'] ?? ''));
}

/**
 * Devuelve el token CSRF actual, creándolo si no existía.
 * Ejemplo:
 * - Primera vez -> "a3f1... (64 chars)"
 * - Luego devuelve siempre el mismo token de la sesión actual.
 */
function csrfToken(): string
{
    authEnsureSession();
    if (empty($_SESSION['csrf_token'])) {
        // Token aleatorio fuerte (32 bytes -> 64 chars hex).
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return (string) $_SESSION['csrf_token'];
}

/**
 * Imprime un input hidden para incluir el token en formularios POST.
 * Ejemplo de salida:
 * <input type="hidden" name="_csrf" value="TOKEN...">
 */
function csrfInput(): string
{
    $token = csrfToken();
    return '<input type="hidden" name="_csrf" value="' . htmlspecialchars($token, ENT_QUOTES, 'UTF-8') . '">';
}

/**
 * Comprueba si el token CSRF enviado en POST es válido.
 * Ejemplo:
 * - POST['_csrf'] == SESSION['csrf_token'] -> true
 * - Distinto o vacío -> false
 */
function csrfIsValidRequest(): bool
{
    authEnsureSession();
    $sent = (string) ($_POST['_csrf'] ?? '');
    $sessionToken = (string) ($_SESSION['csrf_token'] ?? '');
    // hash_equals protege de comparaciones inseguras de strings.
    return $sent !== '' && $sessionToken !== '' && hash_equals($sessionToken, $sent);
}
