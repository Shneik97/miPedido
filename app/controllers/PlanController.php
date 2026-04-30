<?php
require_once __DIR__ . '/../helpers/auth_helper.php';
require_once __DIR__ . '/../helpers/plan_helper.php';
require_once __DIR__ . '/../models/Usuario.php';

class PlanController
{
    private function redirect(string $url): void
    {
        header('Location: ' . $url);
        exit;
    }

    public function select(): void
    {
        authEnsureSession();
        if (!isset($_SESSION['usuario'])) {
            $this->redirect('index.php?page=login');
        }
        if (!isAdmin()) {
            $this->redirect('index.php?page=dashboard');
        }
        if (trim((string) ($_SESSION['usuario']['plan_actual'] ?? '')) !== '') {
            $this->redirect('index.php?page=dashboard');
        }
        require __DIR__ . '/../views/plan/select.php';
    }

    public function store(): void
    {
        authEnsureSession();
        if (!isset($_SESSION['usuario'])) {
            $this->redirect('index.php?page=login');
        }
        if (!isAdmin()) {
            $this->redirect('index.php?page=dashboard');
        }
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST' || !csrfIsValidRequest()) {
            $this->redirect('index.php?page=plan_select&error=csrf');
        }

        $validation = planValidateSimulatedPayload($_POST, true);
        if (!$validation['ok']) {
            $this->redirect('index.php?page=plan_select&error=' . rawurlencode($validation['error']));
        }

        $uid = (int) ($_SESSION['usuario']['id'] ?? 0);
        $plan = $validation['plan'];
        (new Usuario())->setPlanInicial($uid, $plan);
        $_SESSION['usuario']['plan_actual'] = $plan;
        $_SESSION['usuario']['plan_elegido_at'] = date('Y-m-d H:i:s');
        $_SESSION['usuario']['plan_cambiado_at'] = date('Y-m-d H:i:s');

        $this->redirect('index.php?page=dashboard&ok=plan_activo');
    }
}
