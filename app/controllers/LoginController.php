<?php
require_once __DIR__ . '/../helpers/auth_helper.php';
require_once __DIR__ . '/../helpers/preferencias_ui_helper.php';
require_once __DIR__ . '/../models/Usuario.php';

/**
 * Controlador de autenticación:
 * - login local
 * - registro local
 * - login federado con Google/Auth0
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
     * Cierra el proceso de login exitoso (local o Google) en un solo punto.
     */
    private function completeLogin(array $usuario): void
    {
        unset($usuario['password']);
        $usuario['preferencias_ui'] = preferenciasUiNormalize($usuario['preferencias_ui'] ?? null);
        $_SESSION['usuario'] = $usuario;
        mipedido_sidebar_emit_collapse_cookie(!empty($usuario['preferencias_ui']['sidebar_collapsed']));

        if (((string) ($usuario['rol'] ?? '')) === 'admin' && trim((string) ($usuario['plan_actual'] ?? '')) === '') {
            $this->redirect('index.php?page=plan_select');
        }
        $this->redirect('index.php?page=dashboard');
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

    /**
     * Inicia el flujo OAuth contra Auth0/Google.
     */
    public function authGoogleStart(): void
    {
        authEnsureSession();
        $cfg = $this->auth0Config();
        if (!$cfg['enabled']) {
            $this->redirectWithSessionMessage('register_error', 'Falta configurar Auth0/Google. Revisa README para las variables AUTH0_*.', 'index.php?page=register');
        }

        $state = bin2hex(random_bytes(16));
        $_SESSION['auth0_state'] = $state;

        $params = [
            'response_type' => 'code',
            'client_id' => $cfg['client_id'],
            'redirect_uri' => $cfg['redirect_uri'],
            'scope' => 'openid profile email',
            'state' => $state,
            'connection' => $cfg['connection'],
            'prompt' => 'login',
        ];
        header('Location: https://' . $cfg['domain'] . '/authorize?' . http_build_query($params));
        exit;
    }

    public function authGoogleCallback(): void
    {
        authEnsureSession();
        $cfg = $this->auth0Config();
        if (!$cfg['enabled']) {
            $this->redirectWithSessionMessage('login_error', 'Auth0 no está configurado.', 'index.php?page=login');
        }

        $state = (string) ($_GET['state'] ?? '');
        $code = (string) ($_GET['code'] ?? '');
        $stateSession = (string) ($_SESSION['auth0_state'] ?? '');
        unset($_SESSION['auth0_state']);
        if ($state === '' || $code === '' || $stateSession === '' || !hash_equals($stateSession, $state)) {
            $this->redirectWithSessionMessage('login_error', 'No se pudo validar el inicio con Google (state inválido).', 'index.php?page=login');
        }

        $tokenData = $this->httpPostForm('https://' . $cfg['domain'] . '/oauth/token', [
            'grant_type' => 'authorization_code',
            'client_id' => $cfg['client_id'],
            'client_secret' => $cfg['client_secret'],
            'code' => $code,
            'redirect_uri' => $cfg['redirect_uri'],
        ]);
        $accessToken = (string) ($tokenData['access_token'] ?? '');
        if ($accessToken === '') {
            $this->redirectWithSessionMessage('login_error', 'No se recibió token de Auth0.', 'index.php?page=login');
        }

        $userInfo = $this->httpGetJson('https://' . $cfg['domain'] . '/userinfo', [
            'Authorization: Bearer ' . $accessToken,
        ]);
        $email = trim((string) ($userInfo['email'] ?? ''));
        $nombre = trim((string) ($userInfo['name'] ?? 'Usuario Google'));
        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->redirectWithSessionMessage('login_error', 'Google/Auth0 no devolvió un email válido.', 'index.php?page=login');
        }

        $userModel = new Usuario();
        $existente = $userModel->findByEmail($email);
        if (!$existente) {
            $passwordRandom = bin2hex(random_bytes(16));
            $workspaceKey = $this->newWorkspaceKey();
            $userModel->create($nombre !== '' ? $nombre : 'Usuario Google', $email, $passwordRandom, 'admin', $workspaceKey);
        }

        $usuario = $userModel->getForLoginByEmail($email);
        if (!$usuario) {
            $this->redirectWithSessionMessage('login_error', 'No se pudo completar el acceso con Google.', 'index.php?page=login');
        }
        $userModel->clearLoginSecurity((int) $usuario['id']);
        session_regenerate_id(true);
        $this->completeLogin($usuario);
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

    private function auth0Config(): array
    {
        $domain = trim((string) getenv('AUTH0_DOMAIN'));
        $clientId = trim((string) getenv('AUTH0_CLIENT_ID'));
        $clientSecret = trim((string) getenv('AUTH0_CLIENT_SECRET'));
        $connection = trim((string) getenv('AUTH0_CONNECTION'));
        if ($connection === '') {
            $connection = 'google-oauth2';
        }
        $redirectUri = trim((string) getenv('AUTH0_REDIRECT_URI'));
        if ($redirectUri === '') {
            $scheme = authIsHttps() ? 'https' : 'http';
            $host = (string) ($_SERVER['HTTP_HOST'] ?? 'localhost:8000');
            $redirectUri = $scheme . '://' . $host . '/index.php?page=auth_google_callback';
        }

        return [
            'enabled' => ($domain !== '' && $clientId !== '' && $clientSecret !== ''),
            'domain' => $domain,
            'client_id' => $clientId,
            'client_secret' => $clientSecret,
            'redirect_uri' => $redirectUri,
            'connection' => $connection,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function httpPostForm(string $url, array $payload): array
    {
        $opts = [
            'http' => [
                'method' => 'POST',
                'header' => "Content-Type: application/x-www-form-urlencoded\r\nAccept: application/json\r\n",
                'content' => http_build_query($payload),
                'ignore_errors' => true,
                'timeout' => 15,
            ],
        ];
        $raw = @file_get_contents($url, false, stream_context_create($opts));
        if ($raw === false) {
            return [];
        }
        $data = json_decode($raw, true);
        return is_array($data) ? $data : [];
    }

    /**
     * @return array<string, mixed>
     */
    private function httpGetJson(string $url, array $headers = []): array
    {
        $headerText = "Accept: application/json\r\n";
        if (!empty($headers)) {
            $headerText .= implode("\r\n", $headers) . "\r\n";
        }
        $opts = [
            'http' => [
                'method' => 'GET',
                'header' => $headerText,
                'ignore_errors' => true,
                'timeout' => 15,
            ],
        ];
        $raw = @file_get_contents($url, false, stream_context_create($opts));
        if ($raw === false) {
            return [];
        }
        $data = json_decode($raw, true);
        return is_array($data) ? $data : [];
    }

    private function newWorkspaceKey(): string
    {
        // Generador simple de clave (suficiente para demo académica).
        return date('YmdHis') . '-' . (string) random_int(100000, 999999);
    }
}
