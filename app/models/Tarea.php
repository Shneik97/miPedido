<?php
/**
 * Tareas internas: el administrador asigna trabajo a usuarios (empleados o admin).
 */
require_once __DIR__ . '/../../config/database.php';

class Tarea {
    private $conn;
    private $table = 'tareas';

    public function __construct() {
        $database = new Database();
        $this->conn = $database->connect();
    }

    /**
     * Listado: admin ve todas; empleado solo las asignadas a él.
     */
    public function getAllForUser(int $usuarioId, bool $isAdmin): array {
        if ($isAdmin) {
            $sql = 'SELECT t.*, ua.nombre AS asignado_nombre, uc.nombre AS creador_nombre
                    FROM ' . $this->table . ' t
                    INNER JOIN usuarios ua ON ua.id = t.asignado_a
                    INNER JOIN usuarios uc ON uc.id = t.creado_por
                    ORDER BY t.estado = \'hecha\' ASC, t.fecha_creacion DESC, t.id DESC';
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
        } else {
            $sql = 'SELECT t.*, ua.nombre AS asignado_nombre, uc.nombre AS creador_nombre
                    FROM ' . $this->table . ' t
                    INNER JOIN usuarios ua ON ua.id = t.asignado_a
                    INNER JOIN usuarios uc ON uc.id = t.creado_por
                    WHERE t.asignado_a = :uid
                    ORDER BY t.estado = \'hecha\' ASC, t.fecha_creacion DESC, t.id DESC';
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([':uid' => $usuarioId]);
        }
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Cuenta tareas no terminadas (para badge de navegación).
     * Admin: todas las pendientes/en_curso del equipo. Empleado: solo las suyas.
     */
    public function countPendientesNav(int $usuarioId, bool $isAdmin): int {
        if ($isAdmin) {
            $sql = "SELECT COUNT(*) FROM {$this->table} WHERE estado IN ('pendiente','en_curso')";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
        } else {
            $sql = "SELECT COUNT(*) FROM {$this->table} WHERE asignado_a = :uid AND estado IN ('pendiente','en_curso')";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([':uid' => $usuarioId]);
        }
        return (int) $stmt->fetchColumn();
    }

    /**
     * Días naturales desde hoy hasta la fecha del límite (solo fecha, sin hora).
     */
    private function diasHastaFechaLimite(string $fechaLimite): int {
        $lim = date('Y-m-d', strtotime($fechaLimite));
        $hoy = date('Y-m-d');

        return (int) floor((strtotime($lim) - strtotime($hoy)) / 86400);
    }

    /**
     * Texto corto: título + cuenta atrás (7…1 días o hoy). Sin guillemets.
     */
    private function textoCuentaRegresivaLimite(string $titulo, int $dias): string {
        $t = trim($titulo);
        if ($dias < 0) {
            return $t . ' — vencida';
        }
        if ($dias === 0) {
            return $t . ' — vence hoy';
        }
        if ($dias === 1) {
            return $t . ' — 1 día restante';
        }

        return $t . ' — ' . $dias . ' días restantes';
    }

    private function notifTiempoRelativoPasado(string $mysqlDt): string {
        $t = strtotime($mysqlDt);
        if ($t === false) {
            return '';
        }
        $sec = max(0, time() - $t);
        if ($sec < 60) {
            return 'hace un momento';
        }
        if ($sec < 3600) {
            $m = (int) floor($sec / 60);

            return 'hace ' . $m . ($m === 1 ? ' minuto' : ' minutos');
        }
        if ($sec < 86400) {
            $h = (int) floor($sec / 3600);

            return 'hace ' . $h . ($h === 1 ? ' hora' : ' horas');
        }
        $d = (int) floor($sec / 86400);
        $hRem = (int) floor(($sec % 86400) / 3600);
        if ($d >= 1 && $hRem >= 1) {
            return 'hace ' . $d . ($d === 1 ? ' día ' : ' días ') . $hRem . ($hRem === 1 ? ' hora' : ' horas');
        }
        if ($d >= 1) {
            return 'hace ' . $d . ($d === 1 ? ' día' : ' días');
        }
        $h = (int) floor($sec / 3600);

        return 'hace ' . $h . ($h === 1 ? ' hora' : ' horas');
    }

    /** Línea secundaria: enlace con calendario / fecha límite. */
    private function notifSubtextoVenceCalendario(string $fechaLimite): string {
        $ts = strtotime($fechaLimite);
        if ($ts === false) {
            return '';
        }

        return 'Calendario / límite: ' . date('d/m/Y', $ts) . ', ' . date('H:i', $ts);
    }

    /**
     * Avisos para la campana: título + días hasta límite (0–7) o solo título si es asignación nueva sin aviso de plazo.
     *
     * @return list<array{tipo:string, texto:string, href:string, tarea_id?:int, secundario?:string, icono?:string}>
     */
    public function getNotificacionesCabecera(int $usuarioId, bool $isAdmin): array {
        $out = [];
        $seenIds = [];

        if ($isAdmin) {
            $sqlV = 'SELECT t.id, t.titulo, t.fecha_limite, ua.nombre AS asignado_nombre
                     FROM ' . $this->table . ' t
                     INNER JOIN usuarios ua ON ua.id = t.asignado_a
                     WHERE t.estado IN (\'pendiente\',\'en_curso\')
                       AND t.fecha_limite IS NOT NULL
                       AND DATE(t.fecha_limite) BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 7 DAY)
                     ORDER BY DATE(t.fecha_limite) ASC, t.id ASC
                     LIMIT 20';
            $stmt = $this->conn->query($sqlV);
            foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
                $id = (int) $row['id'];
                $fl = (string) $row['fecha_limite'];
                $tit = (string) $row['titulo'];
                $dias = $this->diasHastaFechaLimite($fl);
                if ($dias > 7) {
                    continue;
                }
                $seenIds[$id] = true;
                $asig = trim((string) ($row['asignado_nombre'] ?? ''));
                $texto = $this->textoCuentaRegresivaLimite($tit, $dias);
                $sec = $this->notifSubtextoVenceCalendario($fl);
                if ($asig !== '') {
                    $sec .= ($sec !== '' ? ' · ' : '') . 'Asignada a ' . $asig;
                }
                $out[] = [
                    'tipo' => 'vence_limite',
                    'texto' => $texto,
                    'secundario' => $sec,
                    'icono' => 'calendario',
                    'href' => 'index.php?page=tareas#tarea-' . $id,
                    'tarea_id' => $id,
                ];
            }

            return $out;
        }

        $sqlV = 'SELECT id, titulo, fecha_limite FROM ' . $this->table . '
                 WHERE asignado_a = :uid AND estado IN (\'pendiente\',\'en_curso\')
                   AND fecha_limite IS NOT NULL
                   AND DATE(fecha_limite) BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 7 DAY)
                 ORDER BY DATE(fecha_limite) ASC, id ASC
                 LIMIT 20';
        $stmt = $this->conn->prepare($sqlV);
        $stmt->execute([':uid' => $usuarioId]);
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $id = (int) $row['id'];
            $fl = (string) $row['fecha_limite'];
            $tit = (string) $row['titulo'];
            $dias = $this->diasHastaFechaLimite($fl);
            if ($dias > 7) {
                continue;
            }
            $seenIds[$id] = true;
            $out[] = [
                'tipo' => 'vence_limite',
                'texto' => $this->textoCuentaRegresivaLimite($tit, $dias),
                'secundario' => $this->notifSubtextoVenceCalendario($fl),
                'icono' => 'calendario',
                'href' => 'index.php?page=tareas#tarea-' . $id,
                'tarea_id' => $id,
            ];
        }

        $sqlN = 'SELECT t.id, t.titulo, t.fecha_creacion
                 FROM ' . $this->table . ' t
                 WHERE t.asignado_a = :uid
                   AND t.creado_por != :uid2
                   AND t.es_personal = 0
                   AND t.estado IN (\'pendiente\',\'en_curso\')
                   AND t.fecha_creacion >= DATE_SUB(NOW(), INTERVAL 7 DAY)
                 ORDER BY t.fecha_creacion DESC
                 LIMIT 8';
        $stmt = $this->conn->prepare($sqlN);
        $stmt->execute([':uid' => $usuarioId, ':uid2' => $usuarioId]);
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $id = (int) $row['id'];
            if (isset($seenIds[$id])) {
                continue;
            }
            $tit = trim((string) $row['titulo']);
            if ($tit === '') {
                continue;
            }
            $fc = (string) ($row['fecha_creacion'] ?? '');
            $sec = $fc !== '' ? ('Tareas · ' . $this->notifTiempoRelativoPasado($fc)) : 'Tareas · asignación reciente';
            $out[] = [
                'tipo' => 'nueva_asignacion',
                'texto' => $tit,
                'secundario' => $sec,
                'icono' => 'tarea',
                'href' => 'index.php?page=tareas#tarea-' . $id,
                'tarea_id' => $id,
            ];
        }

        return $out;
    }

    public function getById(int $id): ?array {
        $sql = 'SELECT * FROM ' . $this->table . ' WHERE id = :id';
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    /**
     * Alta de tarea. $esPersonal: tarea creada por el usuario para sí (calendario / notas propias).
     */
    public function create(string $titulo, ?string $descripcion, int $asignadoA, int $creadoPor, ?int $pedidoId, ?string $fechaLimite, bool $esPersonal = false): int {
        $sql = 'INSERT INTO ' . $this->table . ' (titulo, descripcion, asignado_a, creado_por, pedido_id, fecha_limite, es_personal)
                VALUES (:titulo, :desc, :asig, :crea, :ped, :flim, :esp)';
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([
            ':titulo' => $titulo,
            ':desc' => $descripcion !== null && $descripcion !== '' ? $descripcion : null,
            ':asig' => $asignadoA,
            ':crea' => $creadoPor,
            ':ped' => $pedidoId !== null && $pedidoId > 0 ? $pedidoId : null,
            ':flim' => $fechaLimite !== null && $fechaLimite !== '' ? $fechaLimite : null,
            ':esp' => $esPersonal ? 1 : 0,
        ]);
        return (int) $this->conn->lastInsertId();
    }

    /**
     * Tareas visibles en un mes concreto para el calendario.
     * Empleado: las que tiene asignadas (jefe o él mismo). Admin: todas las del equipo.
     * Día mostrado: fecha límite si existe; si no, día de creación.
     */
    public function getForCalendarioMonth(int $year, int $month, int $actorId, bool $isAdmin): array {
        $start = sprintf('%04d-%02d-01', $year, $month);
        $lastDay = (int) date('t', strtotime($start));
        $end = sprintf('%04d-%02d-%02d', $year, $month, $lastDay);

        $sql = 'SELECT t.id, t.titulo, t.estado, t.es_personal, t.fecha_limite, t.fecha_creacion,
                       DATE(COALESCE(t.fecha_limite, t.fecha_creacion)) AS fecha_dia,
                       ua.nombre AS asignado_nombre, uc.nombre AS creador_nombre
                FROM ' . $this->table . ' t
                INNER JOIN usuarios ua ON ua.id = t.asignado_a
                INNER JOIN usuarios uc ON uc.id = t.creado_por
                WHERE DATE(COALESCE(t.fecha_limite, t.fecha_creacion)) BETWEEN :d1 AND :d2 ';
        if (!$isAdmin) {
            $sql .= ' AND t.asignado_a = :uid ';
        }
        $sql .= ' ORDER BY fecha_dia ASC, t.id ASC';
        $stmt = $this->conn->prepare($sql);
        $params = [':d1' => $start, ':d2' => $end];
        if (!$isAdmin) {
            $params[':uid'] = $actorId;
        }
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Cambia estado: el asignado o un admin pueden actualizar.
     */
    public function updateEstado(int $id, string $nuevoEstado, int $actorId, bool $actorIsAdmin): bool {
        $allowed = ['pendiente', 'en_curso', 'hecha'];
        if (!in_array($nuevoEstado, $allowed, true)) {
            return false;
        }
        $t = $this->getById($id);
        if (!$t) {
            return false;
        }
        if (!$actorIsAdmin && (int) $t['asignado_a'] !== $actorId) {
            return false;
        }
        $sql = 'UPDATE ' . $this->table . ' SET estado = :e WHERE id = :id';
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([':e' => $nuevoEstado, ':id' => $id]);
    }
}
