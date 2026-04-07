<?php
require_once __DIR__ . '/../helpers/auth_helper.php';

class ConfigController {

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
