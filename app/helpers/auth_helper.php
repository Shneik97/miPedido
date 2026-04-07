<?php

/**
 * Asegura que la sesión PHP esté iniciada.
 */
function authEnsureSession(): void
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
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
