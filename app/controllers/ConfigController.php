<?php
require_once __DIR__ . '/../helpers/auth_helper.php';
require_once __DIR__ . '/../helpers/plan_helper.php';
require_once __DIR__ . '/../models/Usuario.php';

/**
 * Configuración de cuenta:
 * - Admin: vista completa.
 * - Empleado: vista limitada/minimal.
 */
class ConfigController {
    private function requireAuth(): void
    {
        authEnsureSession();
        if (!isset($_SESSION['usuario'])) {
            $this->redirect('index.php');
        }
    }

    /**
     * Helper de redirección para no repetir header+exit.
     */
    private function redirect(string $url): void
    {
        header('Location: ' . $url);
        exit;
    }

    /**
     * Muestra el panel de configuración de la cuenta actual.
     */
    public function index(): void {
        $this->requireAuth();

        $uid = (int) ($_SESSION['usuario']['id'] ?? 0);
        $ws = currentWorkspaceKey();
        $usuarioConfig = (new Usuario())->getById($uid, $ws);
        if (!$usuarioConfig) {
            $usuarioConfig = [
                'id' => $uid,
                'nombre' => (string) ($_SESSION['usuario']['nombre'] ?? ''),
                'email' => (string) ($_SESSION['usuario']['email'] ?? ''),
                'rol' => (string) ($_SESSION['usuario']['rol'] ?? 'empleado'),
                'workspace_key' => $ws,
                'onboarding_version' => (int) ($_SESSION['usuario']['onboarding_version'] ?? 0),
            ];
        }
        $esAdminConfig = ((string) ($usuarioConfig['rol'] ?? 'empleado')) === 'admin';
        $puedeCambiarPlan = false;
        if ($esAdminConfig) {
            $puedeCambiarPlan = (new Usuario())->canChangePlanNow($uid);
        }
        require __DIR__ . '/../views/config/index.php';
    }

    public function emailVerificacionEnviar(): void
    {
        $this->requireAuth();
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST' || !csrfIsValidRequest()) {
            $this->redirect('index.php?page=config&error=csrf');
        }
        $uid = (int) ($_SESSION['usuario']['id'] ?? 0);
        $ws = currentWorkspaceKey();
        $model = new Usuario();
        $u = $model->getById($uid, $ws);
        if (!$u) {
            $this->redirect('index.php?page=config&error=cuenta');
        }
        if ((int) ($u['email_verificado'] ?? 0) === 1) {
            $this->redirect('index.php?page=config&ok=ya_verificada');
        }
        // Simulación junior: el botón marca la cuenta como verificada sin OTP real.
        $model->markEmailVerified($uid);
        $this->redirect('index.php?page=config&ok=verificada');
    }

    public function planUpdate(): void
    {
        $this->requireAuth();
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST' || !csrfIsValidRequest()) {
            $this->redirect('index.php?page=config&error=csrf');
        }
        $uid = (int) ($_SESSION['usuario']['id'] ?? 0);
        $ws = currentWorkspaceKey();
        $model = new Usuario();
        $u = $model->getById($uid, $ws);
        if (!$u || (string) ($u['rol'] ?? '') !== 'admin') {
            $this->redirect('index.php?page=config&error=permiso');
        }
        $validation = planValidateSimulatedPayload($_POST, true);
        if (!$validation['ok']) {
            $mapError = [
                'datos' => 'plan_datos',
                'email' => 'plan_email',
                'tarjeta' => 'plan_tarjeta',
                'terminos' => 'plan_sim',
                'plan' => 'plan_datos',
            ];
            $targetError = $mapError[$validation['error']] ?? 'plan_datos';
            $this->redirect('index.php?page=config&error=' . rawurlencode($targetError));
        }
        $plan = $validation['plan'];

        if ($plan === '') {
            $this->redirect('index.php?page=config&error=plan_datos');
        }
        if (!$model->updatePlan($uid, $plan)) {
            $this->redirect('index.php?page=config&error=plan_cooldown');
        }
        $_SESSION['usuario']['plan_actual'] = $plan;
        $_SESSION['usuario']['plan_cambiado_at'] = date('Y-m-d H:i:s');
        $this->redirect('index.php?page=config&ok=plan_actualizado');
    }
}
