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

        $model->create($nombre, $email, $password, $rol);
        header('Location: index.php?page=usuarios&ok=creado');
        exit;
    }

    public function edit(): void {
        $this->requireAdmin();
        $id = (int) ($_GET['id'] ?? 0);
        $u = (new Usuario())->getById($id);
        if (!$u) {
            header('Location: index.php?page=usuarios');
            exit;
        }
        require __DIR__ . '/../views/usuarios/edit.php';
    }

    public function update(): void {
        $this->requireAdmin();
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
