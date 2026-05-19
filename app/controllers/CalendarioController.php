<?php
/**
 * Calendario mensual: tareas asignadas por el jefe y tareas personales (mismo modelo tareas).
 */
require_once __DIR__ . '/../helpers/auth_helper.php';
require_once __DIR__ . '/../models/Tarea.php';

class CalendarioController {

    private function requireAuth(): void {
        authEnsureSession();
        if (!isset($_SESSION['usuario'])) {
            $this->redirect('index.php');
        }
    }

    /**
     * Atajo para redirecciones (evita código repetido).
     */
    private function redirect(string $url): void {
        header('Location: ' . $url);
        exit;
    }

    public function index(): void {
        $this->requireAuth();
        $ym = (string) ($_GET['ym'] ?? '');
        $year = (int) date('Y');
        $month = (int) date('n');
        if ($ym !== '' && preg_match('/^(\d{4})-(\d{2})$/', $ym, $m)) {
            $year = (int) $m[1];
            $month = (int) $m[2];
        }
        if ($month < 1 || $month > 12) {
            $month = (int) date('n');
        }
        if ($year < 2000 || $year > 2100) {
            $year = (int) date('Y');
        }

        $uid = (int) $_SESSION['usuario']['id'];
        $isAdmin = isAdmin();
        $workspaceKey = currentWorkspaceKey();

        $tareaModel = new Tarea();
        $calendarioDbError = false;
        try {
            $filas = $tareaModel->getForCalendarioMonth($year, $month, $uid, $isAdmin, $workspaceKey);
        } catch (Throwable $e) {
            $filas = [];
            $calendarioDbError = true;
        }

        $porDia = [];
        foreach ($filas as $r) {
            $d = (string) $r['fecha_dia'];
            if (!isset($porDia[$d])) {
                $porDia[$d] = [];
            }
            $porDia[$d][] = $r;
        }

        $primerDiaMes = new DateTime(sprintf('%04d-%02d-01', $year, $month));
        $inicioRejilla = clone $primerDiaMes;
        $diaSemana = (int) $inicioRejilla->format('N');
        if ($diaSemana > 1) {
            $inicioRejilla->modify('-' . ($diaSemana - 1) . ' days');
        }

        $semanas = [];
        $cursor = clone $inicioRejilla;
        for ($s = 0; $s < 6; $s++) {
            $semana = [];
            for ($d = 0; $d < 7; $d++) {
                $semana[] = clone $cursor;
                $cursor->modify('+1 day');
            }
            $semanas[] = $semana;
        }

        $anterior = (clone $primerDiaMes)->modify('-1 month');
        $siguiente = (clone $primerDiaMes)->modify('+1 month');
        $ymAnterior = $anterior->format('Y-m');
        $ymSiguiente = $siguiente->format('Y-m');

        require __DIR__ . '/../views/calendario/index.php';
    }

    public function storePersonal(): void {
        $this->requireAuth();
        if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
            $this->redirect('index.php?page=calendario');
        }
        if (!csrfIsValidRequest()) {
            $this->redirect('index.php?page=calendario&error=csrf');
        }
        $titulo = trim((string) ($_POST['titulo'] ?? ''));
        $descripcion = trim((string) ($_POST['descripcion'] ?? ''));
        $fecha = trim((string) ($_POST['fecha'] ?? ''));

        if ($titulo === '' || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha)) {
            $this->redirect('index.php?page=calendario&error=personal');
        }

        $uid = (int) $_SESSION['usuario']['id'];
        $workspaceKey = currentWorkspaceKey();
        $fechaLimite = $fecha . ' 09:00:00';

        try {
            (new Tarea())->create($titulo, $descripcion !== '' ? $descripcion : null, $uid, $uid, null, $fechaLimite, true, $workspaceKey);
        } catch (Throwable $e) {
            $this->redirect('index.php?page=calendario&error=personal');
        }

        $ym = substr($fecha, 0, 7);
        $this->redirect('index.php?page=calendario&ym=' . rawurlencode($ym) . '&ok=personal');
    }
}
