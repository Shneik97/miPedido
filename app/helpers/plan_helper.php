<?php

/**
 * PLAN HELPER (nivel estudiante)
 * ------------------------------
 * Centraliza validaciones del flujo de plan simulado.
 *
 * ¿Por qué está en helper?
 * - Porque se usa en más de un controlador (PlanController y ConfigController).
 * - Así evitamos duplicar reglas y errores.
 *
 * Seguridad de demo:
 * - Solo valida datos mock (no se guarda tarjeta completa).
 */

/**
 * Convierte el plan recibido a formato válido interno.
 * Ejemplo:
 * - " Profesional " -> "profesional"
 * - "premium" -> "" (no permitido)
 */
function planNormalize(string $plan): string
{
    $plan = strtolower(trim($plan));
    $allowed = ['basico', 'profesional', 'avanzado'];
    return in_array($plan, $allowed, true) ? $plan : '';
}

/**
 * Valida el formulario completo del plan simulado.
 *
 * Devuelve:
 * - ok=true si todo está correcto.
 * - ok=false + error para saber qué mensaje mostrar.
 *
 * @return array{ok:bool,error:string,plan:string}
 * Ejemplo:
 * - Si faltan términos -> ['ok' => false, 'error' => 'terminos', 'plan' => 'basico']
 * - Si todo correcto -> ['ok' => true, 'error' => '', 'plan' => 'profesional']
 */
function planValidateSimulatedPayload(array $payload, bool $requirePlan = true): array
{
    $plan = planNormalize((string) ($payload['plan'] ?? ''));
    $titular = trim((string) ($payload['titular'] ?? ''));
    $dni = strtoupper(trim((string) ($payload['dni'] ?? '')));
    $emailFacturacion = trim((string) ($payload['email_facturacion'] ?? ''));
    $cardLast4 = trim((string) ($payload['card_last4'] ?? ''));
    $caducidad = trim((string) ($payload['caducidad'] ?? ''));
    $accepted = (string) ($payload['accept_sim'] ?? '') === '1';

    if ($requirePlan && $plan === '') {
        return ['ok' => false, 'error' => 'plan', 'plan' => ''];
    }
    if ($titular === '' || strlen($titular) < 4 || !preg_match('/^[A-Z0-9]{6,12}$/', $dni)) {
        return ['ok' => false, 'error' => 'datos', 'plan' => $plan];
    }
    if (!filter_var($emailFacturacion, FILTER_VALIDATE_EMAIL)) {
        return ['ok' => false, 'error' => 'email', 'plan' => $plan];
    }
    if (!preg_match('/^\d{4}$/', $cardLast4) || !preg_match('/^(0[1-9]|1[0-2])\/\d{2}$/', $caducidad)) {
        return ['ok' => false, 'error' => 'tarjeta', 'plan' => $plan];
    }
    if (!$accepted) {
        return ['ok' => false, 'error' => 'terminos', 'plan' => $plan];
    }

    return ['ok' => true, 'error' => '', 'plan' => $plan];
}
