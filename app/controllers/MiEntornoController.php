<?php

require_once __DIR__ . '/../helpers/auth_helper.php';
require_once __DIR__ . '/../helpers/preferencias_ui_helper.php';
require_once __DIR__ . '/../models/Usuario.php';

/**
 * Gestiona la pantalla "Mi entorno" para preferencias visuales del usuario.
 */
class MiEntornoController {

    private function redirect(string $url): void {
        header('Location: ' . $url);
        exit;
    }

    /**
     * Muestra el formulario con las preferencias actuales.
     */
    public function index(): void {
        authEnsureSession();
        if (!isset($_SESSION['usuario'])) {
            $this->redirect('index.php');
        }

        $prefs = preferenciasUiNormalize($_SESSION['usuario']['preferencias_ui'] ?? null);
        $ok = isset($_GET['ok']) && $_GET['ok'] === '1';
        $errDb = isset($_GET['err']) && $_GET['err'] === 'db';

        require __DIR__ . '/../views/mi_entorno/index.php';
    }

    /**
     * Guarda preferencias UI en sesión y base de datos.
     */
    public function update(): void {
        authEnsureSession();
        if (!isset($_SESSION['usuario'])) {
            $this->redirect('index.php');
        }
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('index.php?page=mi_entorno');
        }
        if (!csrfIsValidRequest()) {
            $this->redirect('index.php?page=mi_entorno&err=csrf');
        }

        $uid = (int) $_SESSION['usuario']['id'];
        $prefs = [
            'sidebar_pos' => $_POST['sidebar_pos'] ?? 'izquierda',
            'fondo_trabajo' => $_POST['fondo_trabajo'] ?? 'slate',
            'acento' => $_POST['acento'] ?? 'cyan',
            'tema_contenido' => $_POST['tema_contenido'] ?? 'claro',
            'sidebar_collapsed' => !empty($_POST['sidebar_collapsed']),
        ];

        $merged = preferenciasUiSanitize(array_merge(preferenciasUiDefaults(), $prefs));
        try {
            (new Usuario())->updatePreferenciasUi($uid, $merged);
            $_SESSION['usuario']['preferencias_ui'] = $merged;
            mipedido_sidebar_emit_collapse_cookie(!empty($merged['sidebar_collapsed']));
        } catch (Throwable $e) {
            $this->redirect('index.php?page=mi_entorno&err=db');
        }

        $this->redirect('index.php?page=mi_entorno&ok=1');
    }
}
