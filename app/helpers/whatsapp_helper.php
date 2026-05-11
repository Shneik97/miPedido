<?php
/**
 * WHATSAPP HELPER (nivel estudiante)
 * ----------------------------------
 * Este archivo construye texto y enlaces para WhatsApp sin API de pago.
 *
 * ¿Qué hace?
 * - Limpia el teléfono para usarlo en wa.me.
 * - Crea URL de WhatsApp con mensaje.
 * - Prepara mensajes de pedido (versión completa y corta).
 *
 * ¿Qué NO hace?
 * - No envía mensajes automáticamente desde el servidor.
 * - El usuario confirma el envío en su propio WhatsApp.
 */
/**
 * Integración "ligera" con WhatsApp: enlaces wa.me sin API de Meta.
 * El envío real lo confirma el usuario en su app WhatsApp (web o móvil).
 */

/**
 * Normaliza un teléfono típico en España para usarlo en wa.me (solo dígitos, sin +).
 * Acepta números de 9 cifras móvil/fijo (6–9) anteponiendo 34; si ya lleva prefijo internacional, lo conserva.
 * Ejemplo:
 * - "612 34 56 78" -> "34612345678"
 * - "+34 612345678" -> "34612345678"
 */
function normalizarTelefonoEspana(string $raw): ?string {
    $digits = preg_replace('/\D+/', '', $raw);
    if ($digits === null || $digits === '') {
        return null;
    }
    // Si llega un número nacional de 9 dígitos, lo pasamos a formato internacional (34 + número).
    if (strlen($digits) === 9 && ($digits[0] >= '6' && $digits[0] <= '9')) {
        $digits = '34' . $digits;
    }
    if (strlen($digits) < 10 || strlen($digits) > 15) {
        return null;
    }
    return $digits;
}

/**
 * Construye la URL https://wa.me/NUMERO?text=MENSAJE (solo el texto va codificado; el número son dígitos).
 * Ejemplo:
 * - waMeUrl("34612345678", "Hola") -> "https://wa.me/34612345678?text=Hola"
 */
function waMeUrl(string $digitosInternacionales, string $mensaje): string {
    return 'https://wa.me/' . $digitosInternacionales . '?text=' . rawurlencode($mensaje);
}

/**
 * Texto plano con resumen del pedido para pegar en WhatsApp o prellenar wa.me.
 *
 * @param array  $pedido        Fila de getById (incluye cliente_nombre, id, total si se pasa aparte).
 * @param array  $lineas        Líneas tipo getLineasFactura (producto_nombre, cantidad, precio_unitario).
 * @param string $facturaRelUrl URL relativa a la factura (p. ej. index.php?page=pedido_factura&id=1).
 * Ejemplo de salida:
 * "Hola, te envío el resumen del pedido #12 ..."
 */
/**
 * Texto plano con resumen del pedido para pegar en WhatsApp o prellenar wa.me.
 * Versión limpia y profesional.
 */
/**
 * Texto plano con resumen del pedido (Versión ultra-esencial)
 */
function textoResumenPedido(array $pedido, array $lineas, string $facturaRelUrl = ''): string {
    $cliente = (string) ($pedido['cliente_nombre'] ?? '');
    $lines = [];
    $total = 0.0;

    foreach ($lineas as $ln) {
        $nombre = (string) ($ln['producto_nombre'] ?? '');
        $qty = (int) ($ln['cantidad'] ?? 0);
        $pu = (float) ($ln['precio_unitario'] ?? 0);
        $sub = $qty * $pu;
        $total += $sub;
        $lines[] = '- ' . $nombre . ' x' . $qty . ' @ ' . number_format($pu, 2, ',', '.') . ' €';
    }

    $bloqueLineas = $lines !== [] ? implode("\n", $lines) : '(sin líneas)';

    // Construcción del mensaje sin identificadores de ERP ni enlaces
    $msg = "Hola, te envío el resumen del pedido que realizaste.\n\n";
    $msg .= "Cliente: {$cliente}\n";
    $msg .= "Líneas:\n{$bloqueLineas}\n\n";
    $msg .= "Total: " . number_format($total, 2, ',', '.') . " €";

    return $msg; 
}

/**
 * Mensaje breve para el listado (Versión ultra-esencial sin enlace)
 */
function textoWhatsappPedidoCorto(array $filaResumen, string $facturaRelUrl): string {
    $cliente = (string) ($filaResumen['cliente_nombre'] ?? '');
    $total = (float) ($filaResumen['total'] ?? 0);
    $estado = (string) ($filaResumen['estado'] ?? '');

    $msg = "Hola, te informo de tu pedido.\n";
    $msg .= "Cliente: {$cliente}\n";
    $msg .= "Estado: {$estado}\n";
    $msg .= "Total: " . number_format($total, 2, ',', '.') . " €";

    return $msg;
}
