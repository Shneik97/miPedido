<?php
require_once __DIR__ . '/../helpers/auth_helper.php';
require_once __DIR__ . '/../helpers/preferencias_ui_helper.php';
require_once __DIR__ . '/../models/Usuario.php';

class LoginController
{
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

    public function registerStore(): void
    {
        authEnsureSession();
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST' || !csrfIsValidRequest()) {
            $_SESSION['register_error'] = 'Formulario inválido. Recarga la página e inténtalo de nuevo.';
            header('Location: index.php?page=register');
            exit;
        }

        $nombre = trim((string) ($_POST['nombre'] ?? ''));
        $email = trim((string) ($_POST['email'] ?? ''));
        $password = (string) ($_POST['password'] ?? '');
        $passwordConfirm = (string) ($_POST['password_confirm'] ?? '');

        if ($nombre === '' || $email === '' || $password === '') {
            $_SESSION['register_error'] = 'Todos los campos son obligatorios.';
            header('Location: index.php?page=register');
            exit;
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['register_error'] = 'Introduce un email válido.';
            header('Location: index.php?page=register');
            exit;
        }
        if (strlen($password) < 8) {
            $_SESSION['register_error'] = 'La contraseña debe tener al menos 8 caracteres.';
            header('Location: index.php?page=register');
            exit;
        }
        if (!hash_equals($password, $passwordConfirm)) {
            $_SESSION['register_error'] = 'Las contraseñas no coinciden.';
            header('Location: index.php?page=register');
            exit;
        }

        $userModel = new Usuario();
        if ($userModel->emailExists($email, null)) {
            $_SESSION['register_error'] = 'Ese email ya está registrado.';
            header('Location: index.php?page=register');
            exit;
        }

        $userModel->create($nombre, $email, $password, 'empleado');
        $_SESSION['register_ok'] = 'Cuenta creada. Ya puedes iniciar sesión.';
        header('Location: index.php?page=login');
        exit;
    }

    public function authGoogleStart(): void
    {
        authEnsureSession();
        $cfg = $this->auth0Config();
        if (!$cfg['enabled']) {
            $_SESSION['register_error'] = 'Falta configurar Auth0/Google. Revisa README para las variables AUTH0_*.';
            header('Location: index.php?page=register');
            exit;
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
            $_SESSION['login_error'] = 'Auth0 no está configurado.';
            header('Location: index.php?page=login');
            exit;
        }

        $state = (string) ($_GET['state'] ?? '');
        $code = (string) ($_GET['code'] ?? '');
        $stateSession = (string) ($_SESSION['auth0_state'] ?? '');
        unset($_SESSION['auth0_state']);
        if ($state === '' || $code === '' || $stateSession === '' || !hash_equals($stateSession, $state)) {
            $_SESSION['login_error'] = 'No se pudo validar el inicio con Google (state inválido).';
            header('Location: index.php?page=login');
            exit;
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
            $_SESSION['login_error'] = 'No se recibió token de Auth0.';
            header('Location: index.php?page=login');
            exit;
        }

        $userInfo = $this->httpGetJson('https://' . $cfg['domain'] . '/userinfo', [
            'Authorization: Bearer ' . $accessToken,
        ]);
        $email = trim((string) ($userInfo['email'] ?? ''));
        $nombre = trim((string) ($userInfo['name'] ?? 'Usuario Google'));
        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['login_error'] = 'Google/Auth0 no devolvió un email válido.';
            header('Location: index.php?page=login');
            exit;
        }

        $userModel = new Usuario();
        $existente = $userModel->findByEmail($email);
        if (!$existente) {
            $passwordRandom = bin2hex(random_bytes(16));
            $userModel->create($nombre !== '' ? $nombre : 'Usuario Google', $email, $passwordRandom, 'empleado');
        }

        $usuario = $userModel->getForLoginByEmail($email);
        if (!$usuario) {
            $_SESSION['login_error'] = 'No se pudo completar el acceso con Google.';
            header('Location: index.php?page=login');
            exit;
        }
        $userModel->clearLoginSecurity((int) $usuario['id']);
        session_regenerate_id(true);
        unset($usuario['password']);
        $usuario['preferencias_ui'] = preferenciasUiNormalize($usuario['preferencias_ui'] ?? null);
        $_SESSION['usuario'] = $usuario;
        mipedido_sidebar_emit_collapse_cookie(!empty($usuario['preferencias_ui']['sidebar_collapsed']));
        header('Location: index.php?page=dashboard');
        exit;
    }

    private function processLogin(): void
    {
        if (!csrfIsValidRequest()) {
            $_SESSION['login_error'] = 'Formulario inválido. Recarga la página e inténtalo de nuevo.';
            header('Location: index.php?page=login');
            exit;
        }

        $email = trim((string) ($_POST['email'] ?? ''));
        $password = (string) ($_POST['password'] ?? '');
        $userModel = new Usuario();
        $usuario = $userModel->getForLoginByEmail($email);

        if (!$usuario) {
            $_SESSION['login_error'] = 'Usuario no encontrado.';
            header('Location: index.php?page=login');
            exit;
        }

        if ($userModel->isLoginBlocked($usuario)) {
            $blockedUntil = strtotime((string) $usuario['login_blocked_until']);
            $restante = max(1, (int) ceil(($blockedUntil - time()) / 60));
            $_SESSION['login_error'] = 'Cuenta temporalmente bloqueada. Espera ' . $restante . ' minuto(s).';
            header('Location: index.php?page=login');
            exit;
        }

        if (password_verify($password, (string) $usuario['password'])) {
            $userModel->clearLoginSecurity((int) $usuario['id']);
            session_regenerate_id(true);

            unset($usuario['password']);
            $usuario['preferencias_ui'] = preferenciasUiNormalize($usuario['preferencias_ui'] ?? null);
            $_SESSION['usuario'] = $usuario;
            mipedido_sidebar_emit_collapse_cookie(!empty($usuario['preferencias_ui']['sidebar_collapsed']));
            header('Location: index.php?page=dashboard');
            exit;
        }

        $fail = $userModel->registerFailedLogin((int) $usuario['id']);
        if (!empty($fail['locked']) && $fail['lockType'] === '15m') {
            $_SESSION['login_error'] = 'Has agotado 5 intentos. Tu cuenta queda bloqueada 15 minutos.';
        } elseif (!empty($fail['locked']) && $fail['lockType'] === '24h') {
            $_SESSION['login_error'] = 'Has agotado de nuevo 5 intentos. Ahora debes esperar 24 horas.';
        } else {
            $_SESSION['login_error'] = 'Contraseña incorrecta.';
        }

        header('Location: index.php?page=login');
        exit;
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
}
