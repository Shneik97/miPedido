<?php
require_once __DIR__ . '/../helpers/auth_helper.php';

/**
 * Controlador de configuración global (solo admin).
 */
class ConfigController {

    /**
     * Muestra el panel de configuración protegido por rol.
     */
    public function index(): void {
        authEnsureSession();
        if (!isset($_SESSION['usuario'])) {
            header('Location: index.php');
            exit;
        }
        checkRole('admin');
        require __DIR__ . '/../views/config/index.php';
    }
}
