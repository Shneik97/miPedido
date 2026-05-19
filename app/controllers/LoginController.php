<?php
require_once __DIR__ . '/../helpers/auth_helper.php';
require_once __DIR__ . '/../helpers/preferencias_ui_helper.php';
require_once __DIR__ . '/../models/Usuario.php';

/**
 * Controlador de autenticación:
 * - login local
 * - registro local
 */
class LoginController
{
    /**
     * Redirección corta para evitar repetir header+exit.
     */
    private function redirect(string $url): void
    {
        header('Location: ' . $url);
        exit;
    }

    /**
     * Guarda un mensaje en sesión y redirige.
     */
    private function redirectWithSessionMessage(string $sessionKey, string $message, string $url): void
    {
        $_SESSION[$sessionKey] = $message;
        $this->redirect($url);
    }

    /**
     * Cierra el proceso de login exitoso en un solo punto.
     */
    private function completeLogin(array $usuario): void
    {
        unset($usuario['password']);
        $usuario['preferencias_ui'] = preferenciasUiNormalize($usuario['preferencias_ui'] ?? null);
        $_SESSION['usuario'] = $usuario;
        authRefreshPermisosSesion();
        mipedido_sidebar_emit_collapse_cookie(!empty($usuario['preferencias_ui']['sidebar_collapsed']));

        $this->redirect(authLandingUrlTrasLogin());
    }

    /**
     * Muestra login o procesa POST de acceso.
     */
    public function login(): void
    {
        authEnsureSession();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->processLogin();
            return;
        }
        require __DIR__ . '/../views/login.php';
    }

    public function register(): void
    {
        authEnsureSession();
        require __DIR__ . '/../views/register.php';
    }

    public function forgotPassword(): void
    {
        authEnsureSession();
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
            $this->processForgotPasswordRequest();
            return;
        }
        require __DIR__ . '/../views/forgot_password.php';
    }

    public function forgotPasswordReset(): void
    {
        authEnsureSession();
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
            $this->processForgotPasswordReset();
            return;
        }
        require __DIR__ . '/../views/forgot_password_reset.php';
    }

    /**
     * Alta de usuario local (en este proyecto de demo: rol admin por defecto).
     */
    public function registerStore(): void
    {
        authEnsureSession();
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST' || !csrfIsValidRequest()) {
            $this->redirectWithSessionMessage('register_error', 'Formulario inválido. Recarga la página e inténtalo de nuevo.', 'index.php?page=register');
        }

        $nombre = trim((string) ($_POST['nombre'] ?? ''));
        $email = trim((string) ($_POST['email'] ?? ''));
        $password = (string) ($_POST['password'] ?? '');
        $passwordConfirm = (string) ($_POST['password_confirm'] ?? '');

        if ($nombre === '' || $email === '' || $password === '') {
            $this->redirectWithSessionMessage('register_error', 'Todos los campos son obligatorios.', 'index.php?page=register');
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->redirectWithSessionMessage('register_error', 'Introduce un email válido.', 'index.php?page=register');
        }
        if (strlen($password) < 8) {
            $this->redirectWithSessionMessage('register_error', 'La contraseña debe tener al menos 8 caracteres.', 'index.php?page=register');
        }
        if ($password !== $passwordConfirm) {
            $this->redirectWithSessionMessage('register_error', 'Las contraseñas no coinciden.', 'index.php?page=register');
        }

        $userModel = new Usuario();
        if ($userModel->emailExists($email, null)) {
            $this->redirectWithSessionMessage('register_error', 'Ese email ya está registrado.', 'index.php?page=register');
        }

        $workspaceKey = $this->newWorkspaceKey();
        $userModel->create($nombre, $email, $password, 'admin', $workspaceKey);
        $this->redirectWithSessionMessage('register_ok', 'Cuenta creada. Ya puedes iniciar sesión.', 'index.php?page=login');
    }

    private function processLogin(): void
    {
        if (!csrfIsValidRequest()) {
            $this->redirectWithSessionMessage('login_error', 'Formulario inválido. Recarga la página e inténtalo de nuevo.', 'index.php?page=login');
        }

        $email = trim((string) ($_POST['email'] ?? ''));
        $password = (string) ($_POST['password'] ?? '');
        $userModel = new Usuario();
        $usuario = $userModel->getForLoginByEmail($email);

        if (!$usuario) {
            $this->redirectWithSessionMessage('login_error', 'Usuario no encontrado.', 'index.php?page=login');
        }

        if ($userModel->isLoginBlocked($usuario)) {
            $blockedUntil = strtotime((string) $usuario['login_blocked_until']);
            $restante = max(1, (int) ceil(($blockedUntil - time()) / 60));
            $this->redirectWithSessionMessage('login_error', 'Cuenta temporalmente bloqueada. Espera ' . $restante . ' minuto(s).', 'index.php?page=login');
        }

        if (password_verify($password, (string) $usuario['password'])) {
            $userModel->clearLoginSecurity((int) $usuario['id']);
            session_regenerate_id(true);
            $this->completeLogin($usuario);
        }

        $fail = $userModel->registerFailedLogin((int) $usuario['id']);
        if (!empty($fail['locked']) && $fail['lockType'] === '15m') {
            $_SESSION['login_error'] = 'Has agotado 5 intentos. Tu cuenta queda bloqueada 15 minutos.';
        } elseif (!empty($fail['locked']) && $fail['lockType'] === '24h') {
            $_SESSION['login_error'] = 'Has agotado de nuevo 5 intentos. Ahora debes esperar 24 horas.';
        } else {
            $_SESSION['login_error'] = 'Contraseña incorrecta.';
        }

        $this->redirect('index.php?page=login');
    }

    private function processForgotPasswordRequest(): void
    {
        if (!csrfIsValidRequest()) {
            $this->redirectWithSessionMessage('forgot_error', 'Formulario inválido. Recarga e inténtalo de nuevo.', 'index.php?page=forgot_password');
        }
        $email = trim((string) ($_POST['email'] ?? ''));
        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->redirectWithSessionMessage('forgot_error', 'Introduce un correo válido.', 'index.php?page=forgot_password');
        }
        $userModel = new Usuario();
        $u = $userModel->findByEmail($email);
        if (!$u) {
            $this->redirectWithSessionMessage('forgot_error', 'No existe una cuenta con ese correo.', 'index.php?page=forgot_password');
        }

        $code = (string) random_int(100000, 999999);
        $_SESSION['pwd_reset_demo'] = [
            'email' => $email,
            'code' => $code,
            'expires_at' => time() + (10 * 60),
        ];
        // Simulación junior: se muestra el código en pantalla, no se envía email real.
        $this->redirectWithSessionMessage('forgot_ok', 'Código demo generado: ' . $code . ' (caduca en 10 minutos).', 'index.php?page=forgot_password_reset');
    }

    private function processForgotPasswordReset(): void
    {
        if (!csrfIsValidRequest()) {
            $this->redirectWithSessionMessage('forgot_error', 'Formulario inválido. Recarga e inténtalo de nuevo.', 'index.php?page=forgot_password_reset');
        }
        $code = trim((string) ($_POST['code'] ?? ''));
        $password = (string) ($_POST['password'] ?? '');
        $passwordConfirm = (string) ($_POST['password_confirm'] ?? '');
        $sessionData = $_SESSION['pwd_reset_demo'] ?? null;
        if (!is_array($sessionData) || empty($sessionData['email']) || empty($sessionData['code'])) {
            $this->redirectWithSessionMessage('forgot_error', 'Primero solicita un código de recuperación.', 'index.php?page=forgot_password');
        }
        if ((int) ($sessionData['expires_at'] ?? 0) < time()) {
            unset($_SESSION['pwd_reset_demo']);
            $this->redirectWithSessionMessage('forgot_error', 'El código ha caducado. Solicita uno nuevo.', 'index.php?page=forgot_password');
        }
        if (!preg_match('/^\d{6}$/', $code) || $code !== (string) $sessionData['code']) {
            $this->redirectWithSessionMessage('forgot_error', 'Código incorrecto.', 'index.php?page=forgot_password_reset');
        }
        if (strlen($password) < 8) {
            $this->redirectWithSessionMessage('forgot_error', 'La nueva contraseña debe tener al menos 8 caracteres.', 'index.php?page=forgot_password_reset');
        }
        if ($password !== $passwordConfirm) {
            $this->redirectWithSessionMessage('forgot_error', 'Las contraseñas no coinciden.', 'index.php?page=forgot_password_reset');
        }
        $email = (string) $sessionData['email'];
        $userModel = new Usuario();
        $ok = $userModel->updatePasswordByEmail($email, $password);
        if (!$ok) {
            $this->redirectWithSessionMessage('forgot_error', 'No se pudo actualizar la contraseña.', 'index.php?page=forgot_password_reset');
        }
        $u = $userModel->findByEmail($email);
        if ($u && isset($u['id'])) {
            $userModel->clearLoginSecurity((int) $u['id']);
        }
        unset($_SESSION['pwd_reset_demo']);
        $_SESSION['login_error'] = null;
        $this->redirectWithSessionMessage('register_ok', 'Contraseña actualizada. Ya puedes iniciar sesión.', 'index.php?page=login');
    }

    private function newWorkspaceKey(): string
    {
        // Generador simple de clave (suficiente para demo académica).
        return date('YmdHis') . '-' . (string) random_int(100000, 999999);
    }
}
