<?php
require_once __DIR__ . '/../helpers/auth_helper.php';
require_once __DIR__ . '/../models/Usuario.php';

/**
 * Gestión de usuarios (solo administrador).
 */
class UsuarioController {
    private function newWorkspaceKey(): string {
        // Clave simple para separar entornos en este proyecto.
        return date('YmdHis') . '-' . (string) random_int(100000, 999999);
    }

    /**
     * Exige sesión activa y rol admin.
     */
    private function requireAdmin(): void {
        authEnsureSession();
        if (!isset($_SESSION['usuario'])) {
            $this->redirect('index.php');
        }
        checkRole('admin');
    }

    /**
     * Atajo para redirecciones y salida inmediata.
     */
    private function redirect(string $url): void {
        header('Location: ' . $url);
        exit;
    }

    /**
     * Normaliza el rol permitido por formulario.
     */
    private function normalizeRol(string $rol): string {
        return in_array($rol, ['admin', 'empleado'], true) ? $rol : 'empleado';
    }

    public function index(): void {
        $this->requireAdmin();
        $workspaceKey = currentWorkspaceKey();
        $usuarios = (new Usuario())->getAll($workspaceKey);
        require __DIR__ . '/../views/usuarios/index.php';
    }

    public function create(): void {
        $this->requireAdmin();
        require __DIR__ . '/../views/usuarios/create.php';
    }

    public function store(): void {
        $this->requireAdmin();
        if (!csrfIsValidRequest()) {
            $this->redirect('index.php?page=usuarios_create&error=csrf');
        }
        $nombre = trim((string) ($_POST['nombre'] ?? ''));
        $email = trim((string) ($_POST['email'] ?? ''));
        $password = (string) ($_POST['password'] ?? '');
        $rol = $this->normalizeRol((string) ($_POST['rol'] ?? 'empleado'));

        if ($nombre === '' || $email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->redirect('index.php?page=usuarios_create&error=invalido');
        }
        if (strlen($password) < 6) {
            $this->redirect('index.php?page=usuarios_create&error=password_corta');
        }

        $model = new Usuario();
        if ($model->emailExists($email)) {
            $this->redirect('index.php?page=usuarios_create&error=email_duplicado');
        }

        $workspaceKey = currentWorkspaceKey();
        $workspaceForNewUser = $rol === 'admin'
            ? $this->newWorkspaceKey()
            : $workspaceKey;
        if ($workspaceForNewUser === '') {
            $workspaceForNewUser = $this->newWorkspaceKey();
        }
        $newId = $model->create($nombre, $email, $password, $rol, $workspaceForNewUser);
        // Alta simple: admin recibe todos los permisos de catálogo por defecto.
        if ($rol === 'admin') {
            $catalogo = $model->getCatalogoPermisos();
            $allClaves = array_map(static function (array $p): string {
                return (string) ($p['clave'] ?? '');
            }, $catalogo);
            $model->setPermisosByUsuarioId($newId, $allClaves);
        }
        $this->redirect('index.php?page=usuarios&ok=creado');
    }

    public function edit(): void {
        $this->requireAdmin();
        $id = (int) ($_GET['id'] ?? 0);
        $workspaceKey = currentWorkspaceKey();
        $model = new Usuario();
        $u = $model->getById($id, $workspaceKey);
        if (!$u) {
            $this->redirect('index.php?page=usuarios');
        }
        // Contexto para renderizar checkboxes de permisos en la vista.
        try {
            $catalogoPermisos = $model->getCatalogoPermisos();
            $permisosUsuario = $model->getPermisosByUsuarioId($id);
        } catch (Throwable $e) {
            $catalogoPermisos = [];
            $permisosUsuario = [];
        }
        require __DIR__ . '/../views/usuarios/edit.php';
    }

    public function update(): void {
        $this->requireAdmin();
        if (!csrfIsValidRequest()) {
            $this->redirect('index.php?page=usuarios&error=csrf');
        }
        $id = (int) ($_POST['id'] ?? 0);
        $workspaceKey = currentWorkspaceKey();
        $model = new Usuario();
        $actual = $model->getById($id, $workspaceKey);
        if (!$actual) {
            $this->redirect('index.php?page=usuarios');
        }

        $nombre = trim((string) ($_POST['nombre'] ?? ''));
        $email = trim((string) ($_POST['email'] ?? ''));
        $rol = $this->normalizeRol((string) ($_POST['rol'] ?? 'empleado'));
        $passwordNew = trim((string) ($_POST['password_new'] ?? ''));

        if ($nombre === '' || $email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->redirect('index.php?page=usuarios_edit&id=' . $id . '&error=invalido');
        }
        if ($model->emailExists($email, $id)) {
            $this->redirect('index.php?page=usuarios_edit&id=' . $id . '&error=email_duplicado');
        }

        $sessionId = (int) ($_SESSION['usuario']['id'] ?? 0);
        if ($actual['rol'] === 'admin' && $rol === 'empleado' && $model->countAdmins($workspaceKey) <= 1) {
            $this->redirect('index.php?page=usuarios_edit&id=' . $id . '&error=ultimo_admin');
        }

        if ($passwordNew !== '' && strlen($passwordNew) < 6) {
            $this->redirect('index.php?page=usuarios_edit&id=' . $id . '&error=password_corta');
        }

        $pwd = $passwordNew !== '' ? $passwordNew : null;
        $model->update($id, $nombre, $email, $rol, $pwd, $workspaceKey);
        if (($actual['rol'] ?? '') !== 'admin' && $rol === 'admin') {
            // Al promover a admin, pasa a workspace limpio/independiente.
            $model->setWorkspaceKey($id, $this->newWorkspaceKey(), $workspaceKey);
        }

        // Gestión de permisos por admin:
        // - si marca "todos", se aplican todas las claves del catálogo.
        // - si no, se aplican solo las seleccionadas.
        try {
            $catalogoPermisos = $model->getCatalogoPermisos();
            $todasClaves = array_values(array_filter(array_map(static function (array $p): string {
                return (string) ($p['clave'] ?? '');
            }, $catalogoPermisos)));
            $permisosPost = $_POST['permisos'] ?? [];
            if (!is_array($permisosPost)) {
                $permisosPost = [];
            }
            // Importante: guardamos exactamente lo que llega en checkboxes "permisos[]".
            // El check "permiso_todos" solo sirve como ayuda visual en frontend para marcar rápido.
            // Si usamos "permiso_todos" como fuente aquí, acabaríamos pisando selección personalizada.
            $clavesFinales = array_map('strval', $permisosPost);
            $model->setPermisosByUsuarioId($id, $clavesFinales);
        } catch (Throwable $e) {
            $this->redirect('index.php?page=usuarios_edit&id=' . $id . '&error=permisos_db');
        }

        if ($sessionId === $id) {
            $_SESSION['usuario']['nombre'] = $nombre;
            $_SESSION['usuario']['email'] = $email;
            $_SESSION['usuario']['rol'] = $rol;
        }

        $this->redirect('index.php?page=usuarios&ok=actualizado');
    }

    public function delete(): void {
        $this->requireAdmin();
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST' || !csrfIsValidRequest()) {
            $this->redirect('index.php?page=usuarios&error=csrf');
        }
        $uid = (int) ($_POST['id'] ?? 0);
        if ($uid < 1) {
            $this->redirect('index.php?page=usuarios');
        }
        $sessionId = (int) ($_SESSION['usuario']['id'] ?? 0);
        if ($uid === $sessionId) {
            $this->redirect('index.php?page=usuarios&error=no_propio');
        }

        $model = new Usuario();
        $workspaceKey = currentWorkspaceKey();
        $row = $model->getById($uid, $workspaceKey);
        if (!$row) {
            $this->redirect('index.php?page=usuarios');
        }
        if ($row['rol'] === 'admin' && $model->countAdmins($workspaceKey) <= 1) {
            $this->redirect('index.php?page=usuarios&error=ultimo_admin');
        }

        $model->delete($uid, $workspaceKey);
        $this->redirect('index.php?page=usuarios&ok=eliminado');
    }
}
