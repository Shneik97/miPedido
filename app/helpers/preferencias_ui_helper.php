<?php

/**
 * Preferencias de interfaz guardadas en usuarios.preferencias_ui (JSON).
 */
function preferenciasUiDefaults(): array
{
    return [
        'sidebar_pos' => 'izquierda',
        'fondo_trabajo' => 'slate',
        'acento' => 'cyan',
        'tema_contenido' => 'claro',
        'sidebar_collapsed' => false,
    ];
}

/**
 * @param mixed $raw JSON string desde BD, array ya decodificado, o null
 */
function preferenciasUiNormalize($raw): array
{
    $defaults = preferenciasUiDefaults();
    if (is_array($raw)) {
        $data = $raw;
    } elseif ($raw === null || $raw === '') {
        return $defaults;
    } else {
        $data = json_decode((string) $raw, true);
        if (!is_array($data)) {
            return $defaults;
        }
    }
    $merged = array_merge($defaults, array_intersect_key($data, $defaults));
    $merged['sidebar_collapsed'] = !empty($merged['sidebar_collapsed']);
    return preferenciasUiSanitize($merged);
}

function preferenciasUiSanitize(array $p): array
{
    $allowed = [
        'sidebar_pos' => ['izquierda', 'derecha'],
        'fondo_trabajo' => ['slate', 'blanco', 'humo'],
        'acento' => ['cyan', 'azul', 'violeta', 'verde', 'naranja'],
        'tema_contenido' => ['claro', 'oscuro'],
    ];
    foreach ($allowed as $key => $vals) {
        if (!isset($p[$key]) || !in_array($p[$key], $vals, true)) {
            $p[$key] = preferenciasUiDefaults()[$key];
        }
    }
    $p['sidebar_collapsed'] = !empty($p['sidebar_collapsed']);
    return $p;
}

/** @return array<string, array{0:string,1:string}> hex, rgb sin espacios */
function preferenciasUiAcentoMap(): array
{
    return [
        'cyan' => ['#38bdf8', '56,189,248'],
        'azul' => ['#3b82f6', '59,130,246'],
        'violeta' => ['#a78bfa', '167,139,250'],
        'verde' => ['#34d399', '52,211,153'],
        'naranja' => ['#fb923c', '251,146,60'],
    ];
}

/** @return array<string, string> */
function preferenciasUiFondoMap(): array
{
    return [
        'slate' => '#f1f5f9',
        'blanco' => '#f8fafc',
        'humo' => '#e8eef5',
    ];
}

/** Nombre de la cookie que recuerda el menú colapsado entre páginas (solo escritorio). */
function mipedido_sidebar_collapse_cookie_name(): string
{
    return 'mipedido_sidebar_collapsed';
}

function mipedido_sidebar_emit_collapse_cookie(bool $collapsed): void
{
    if (headers_sent()) {
        return;
    }
    $name = mipedido_sidebar_collapse_cookie_name();
    setcookie($name, $collapsed ? '1' : '0', [
        'expires' => time() + 365 * 24 * 60 * 60,
        'path' => '/',
        'secure' => false,
        'httponly' => false,
        'samesite' => 'Lax',
    ]);
}

function mipedido_sidebar_clear_collapse_cookie(): void
{
    if (headers_sent()) {
        return;
    }
    $name = mipedido_sidebar_collapse_cookie_name();
    setcookie($name, '', [
        'expires' => time() - 3600,
        'path' => '/',
        'samesite' => 'Lax',
    ]);
}

/**
 * Si hay cookie (usuario pulsó el botón de colapsar), manda sobre la preferencia guardada en BD.
 */
function mipedido_sidebar_collapsed_for_layout(bool $showSidebar, array $prefsUi): bool
{
    if (!$showSidebar) {
        return false;
    }
    $c = $_COOKIE[mipedido_sidebar_collapse_cookie_name()] ?? null;
    if ($c === '1') {
        return true;
    }
    if ($c === '0') {
        return false;
    }

    return !empty($prefsUi['sidebar_collapsed']);
}
