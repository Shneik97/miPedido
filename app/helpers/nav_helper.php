<?php
/**
 * NAV HELPER (nivel estudiante)
 * -----------------------------
 * Este helper sirve para la barra lateral y la cabecera.
 *
 * ¿Para qué existe?
 * - Evita que cada controlador repita las consultas de "contador" y "avisos".
 * - Si algo falla en base de datos, la UI no se rompe: devuelve 0 o [].
 *
 * Nota: aquí solo hay funciones pequeñas de apoyo para navegación.
 */
/**
 * Datos ligeros para la barra lateral (evita duplicar consultas en cada controlador).
 * Nota: ejecuta una consulta COUNT por página con sidebar; asumible en un TFG / PYME pequeña.
 */
require_once __DIR__ . '/../models/Tarea.php';

/**
 * Número de tareas abiertas para el badge: admin ve todas las del equipo; empleado solo las suyas.
 */
function contarTareasPendientesNav(int $usuarioId, bool $isAdmin, string $workspaceKey = ''): int {
    try {
        return (new Tarea())->countPendientesNav($usuarioId, $isAdmin, $workspaceKey);
    } catch (Throwable $e) {
        // Base sin migrar o error temporal: no rompe el layout.
        return 0;
    }
}

/**
 * @return list<array{tipo:string, texto:string, href:string, tarea_id?:int, secundario?:string, icono?:string}>
 */
function notificacionesCabecera(int $usuarioId, bool $isAdmin, string $workspaceKey = ''): array {
    try {
        return (new Tarea())->getNotificacionesCabecera($usuarioId, $isAdmin, $workspaceKey);
    } catch (Throwable $e) {
        return [];
    }
}
