<?php
require_once __DIR__ . '/../helpers/auth_helper.php';
require_once __DIR__ . '/../models/Usuario.php';

class UsuarioController {

    private function requireAdmin(): void {
        authEnsureSession();
        if (!isset($_SESSION['usuario'])) {
            header('Location: index.php');
            exit;
        }
        checkRole('admin');
    }

    private function normalizeRol(string $rol): string {
        return in_array($rol, ['admin', 'empleado'], true) ? $rol : 'empleado';
    }

    public function index(): void {
        $this->requireAdmin();
        $usuarios = (new Usuario())->getAll();
        require __DIR__ . '/../views/usuarios/index.php';
    }

    public function create(): void {
        $this->requireAdmin();
        require __DIR__ . '/../views/usuarios/create.php';
    }

    public function store(): void {
        $this->requireAdmin();
        if (!csrfIsValidRequest()) {
            header('Location: index.php?page=usuarios_create&error=csrf');
            exit;
        }
        $nombre = trim((string) ($_POST['nombre'] ?? ''));
        $email = trim((string) ($_POST['email'] ?? ''));
        $password = (string) ($_POST['password'] ?? '');
        $rol = $this->normalizeRol((string) ($_POST['rol'] ?? 'empleado'));

        if ($nombre === '' || $email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            header('Location: index.php?page=usuarios_create&error=invalido');
            exit;
        }
        if (strlen($password) < 6) {
            header('Location: index.php?page=usuarios_create&error=password_corta');
            exit;
        }

        $model = new Usuario();
        if ($model->emailExists($email)) {
            header('Location: index.php?page=usuarios_create&error=email_duplicado');
            exit;
        }

        $newId = $model->create($nombre, $email, $password, $rol);
        // Alta simple: admin recibe todos los permisos de catálogo por defecto.
        if ($rol === 'admin') {
            $catalogo = $model->getCatalogoPermisos();
            $allClaves = array_map(static function (array $p): string {
                return (string) ($p['clave'] ?? '');
            }, $catalogo);
            $model->setPermisosByUsuarioId($newId, $allClaves);
        }
        header('Location: index.php?page=usuarios&ok=creado');
        exit;
    }

    public function edit(): void {
        $this->requireAdmin();
        $id = (int) ($_GET['id'] ?? 0);
        $model = new Usuario();
        $u = $model->getById($id);
        if (!$u) {
            header('Location: index.php?page=usuarios');
            exit;
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
            header('Location: index.php?page=usuarios&error=csrf');
            exit;
        }
        $id = (int) ($_POST['id'] ?? 0);
        $model = new Usuario();
        $actual = $model->getById($id);
        if (!$actual) {
            header('Location: index.php?page=usuarios');
            exit;
        }

        $nombre = trim((string) ($_POST['nombre'] ?? ''));
        $email = trim((string) ($_POST['email'] ?? ''));
        $rol = $this->normalizeRol((string) ($_POST['rol'] ?? 'empleado'));
        $passwordNew = trim((string) ($_POST['password_new'] ?? ''));

        if ($nombre === '' || $email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            header('Location: index.php?page=usuarios_edit&id=' . $id . '&error=invalido');
            exit;
        }
        if ($model->emailExists($email, $id)) {
            header('Location: index.php?page=usuarios_edit&id=' . $id . '&error=email_duplicado');
            exit;
        }

        $sessionId = (int) ($_SESSION['usuario']['id'] ?? 0);
        if ($actual['rol'] === 'admin' && $rol === 'empleado' && $model->countAdmins() <= 1) {
            header('Location: index.php?page=usuarios_edit&id=' . $id . '&error=ultimo_admin');
            exit;
        }

        if ($passwordNew !== '' && strlen($passwordNew) < 6) {
            header('Location: index.php?page=usuarios_edit&id=' . $id . '&error=password_corta');
            exit;
        }

        $pwd = $passwordNew !== '' ? $passwordNew : null;
        $model->update($id, $nombre, $email, $rol, $pwd);

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
            header('Location: index.php?page=usuarios_edit&id=' . $id . '&error=permisos_db');
            exit;
        }

        if ($sessionId === $id) {
            $_SESSION['usuario']['nombre'] = $nombre;
            $_SESSION['usuario']['email'] = $email;
            $_SESSION['usuario']['rol'] = $rol;
        }

        header('Location: index.php?page=usuarios&ok=actualizado');
        exit;
    }

    public function delete(string $id): void {
        $this->requireAdmin();
        $uid = (int) $id;
        $sessionId = (int) ($_SESSION['usuario']['id'] ?? 0);
        if ($uid === $sessionId) {
            header('Location: index.php?page=usuarios&error=no_propio');
            exit;
        }

        $model = new Usuario();
        $row = $model->getById($uid);
        if (!$row) {
            header('Location: index.php?page=usuarios');
            exit;
        }
        if ($row['rol'] === 'admin' && $model->countAdmins() <= 1) {
            header('Location: index.php?page=usuarios&error=ultimo_admin');
            exit;
        }

        $model->delete($uid);
        header('Location: index.php?page=usuarios&ok=eliminado');
        exit;
    }
}
