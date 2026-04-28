<?php
/**
 * Datos ligeros para la barra lateral (evita duplicar consultas en cada controlador).
 * Nota: ejecuta una consulta COUNT por página con sidebar; asumible en un TFG / PYME pequeña.
 */
require_once __DIR__ . '/../models/Tarea.php';

/**
 * Número de tareas abiertas para el badge: admin ve todas las del equipo; empleado solo las suyas.
 */
function contarTareasPendientesNav(int $usuarioId, bool $isAdmin): int {
    try {
        return (new Tarea())->countPendientesNav($usuarioId, $isAdmin);
    } catch (Throwable $e) {
        // Base sin migrar o error temporal: no rompe el layout.
        return 0;
    }
}

/**
 * @return list<array{tipo:string, texto:string, href:string, tarea_id?:int, secundario?:string, icono?:string}>
 */
function notificacionesCabecera(int $usuarioId, bool $isAdmin): array {
    try {
        return (new Tarea())->getNotificacionesCabecera($usuarioId, $isAdmin);
    } catch (Throwable $e) {
        return [];
    }
}
