<?php
require_once __DIR__ . '/../../config/database.php';

class Usuario {
    private $conn;
    private $table = 'usuarios';

    public function __construct() {
        $database = new Database();
        $this->conn = $database->connect();
    }

    public function getAll(): array {
        $sql = 'SELECT id, nombre, email, rol FROM ' . $this->table . ' ORDER BY nombre ASC';
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getById(int $id): ?array {
        $sql = 'SELECT id, nombre, email, rol, preferencias_ui FROM ' . $this->table . ' WHERE id = :id';
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public function findByEmail(string $email): ?array {
        $sql = 'SELECT * FROM ' . $this->table . ' WHERE email = :email LIMIT 1';
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':email' => $email]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public function emailExists(string $email, ?int $exceptId = null): bool {
        $sql = 'SELECT COUNT(*) FROM ' . $this->table . ' WHERE email = :email';
        $params = [':email' => $email];
        if ($exceptId !== null) {
            $sql .= ' AND id != :id';
            $params[':id'] = $exceptId;
        }
        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);
        return (int) $stmt->fetchColumn() > 0;
    }

    public function countAdmins(): int {
        $sql = 'SELECT COUNT(*) FROM ' . $this->table . " WHERE rol = 'admin'";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return (int) $stmt->fetchColumn();
    }

    public function create(string $nombre, string $email, string $passwordPlain, string $rol): int {
        $hash = password_hash($passwordPlain, PASSWORD_DEFAULT);
        $sql = 'INSERT INTO ' . $this->table . ' (nombre, email, password, rol) VALUES (:nombre, :email, :password, :rol)';
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([
            ':nombre' => $nombre,
            ':email' => $email,
            ':password' => $hash,
            ':rol' => $rol,
        ]);
        return (int) $this->conn->lastInsertId();
    }

    public function update(int $id, string $nombre, string $email, string $rol, ?string $newPasswordPlain = null): void {
        if ($newPasswordPlain !== null && $newPasswordPlain !== '') {
            $hash = password_hash($newPasswordPlain, PASSWORD_DEFAULT);
            $sql = 'UPDATE ' . $this->table . ' SET nombre = :nombre, email = :email, rol = :rol, password = :password WHERE id = :id';
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([
                ':nombre' => $nombre,
                ':email' => $email,
                ':rol' => $rol,
                ':password' => $hash,
                ':id' => $id,
            ]);
            return;
        }
        $sql = 'UPDATE ' . $this->table . ' SET nombre = :nombre, email = :email, rol = :rol WHERE id = :id';
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([
            ':nombre' => $nombre,
            ':email' => $email,
            ':rol' => $rol,
            ':id' => $id,
        ]);
    }

    /**
     * Catálogo de permisos disponibles para marcar en la pantalla de edición de usuario.
     * Nota: de momento se guardan/gestionan aquí; aplicar estos permisos en cada módulo
     * se puede hacer en una fase posterior.
     *
     * @return list<array{id:int, clave:string, nombre:string, descripcion:string, orden:int}>
     */
    public function getCatalogoPermisos(): array
    {
        $sql = 'SELECT id, clave, nombre, descripcion, orden_visual
                FROM permisos
                ORDER BY orden_visual ASC, id ASC';
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Devuelve las claves de permisos actualmente asignadas al usuario.
     *
     * @return list<string>
     */
    public function getPermisosByUsuarioId(int $usuarioId): array
    {
        $sql = 'SELECT p.clave
                FROM usuario_permisos up
                INNER JOIN permisos p ON p.id = up.permiso_id
                WHERE up.usuario_id = :uid
                ORDER BY p.orden_visual ASC, p.id ASC';
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':uid' => $usuarioId]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $out = [];
        foreach ($rows as $r) {
            $out[] = (string) ($r['clave'] ?? '');
        }
        return array_values(array_filter($out, static function (string $v): bool {
            return $v !== '';
        }));
    }

    /**
     * Reemplaza permisos del usuario por una nueva lista de claves.
     * Se hace con transacción para evitar estados intermedios.
     *
     * @param list<string> $claves
     */
    public function setPermisosByUsuarioId(int $usuarioId, array $claves): void
    {
        // Normaliza entradas para evitar basura.
        $claves = array_values(array_unique(array_filter(array_map('strval', $claves), static function (string $v): bool {
            return $v !== '';
        })));

        $this->conn->beginTransaction();
        try {
            $del = $this->conn->prepare('DELETE FROM usuario_permisos WHERE usuario_id = :uid');
            $del->execute([':uid' => $usuarioId]);

            if (!empty($claves)) {
                // Solo inserta claves que existan en catálogo.
                $in = implode(',', array_fill(0, count($claves), '?'));
                $sqlPerm = 'SELECT id FROM permisos WHERE clave IN (' . $in . ')';
                $stmtPerm = $this->conn->prepare($sqlPerm);
                $stmtPerm->execute($claves);
                $permIds = $stmtPerm->fetchAll(PDO::FETCH_COLUMN);

                if (!empty($permIds)) {
                    $ins = $this->conn->prepare('INSERT INTO usuario_permisos (usuario_id, permiso_id) VALUES (:uid, :pid)');
                    foreach ($permIds as $pid) {
                        $ins->execute([
                            ':uid' => $usuarioId,
                            ':pid' => (int) $pid,
                        ]);
                    }
                }
            }

            $this->conn->commit();
        } catch (Throwable $e) {
            $this->conn->rollBack();
            throw $e;
        }
    }

    public function delete(int $id): bool {
        $sql = 'DELETE FROM ' . $this->table . ' WHERE id = :id';
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([':id' => $id]);
    }

    public function updatePreferenciasUi(int $id, array $preferencias): void {
        require_once __DIR__ . '/../helpers/preferencias_ui_helper.php';
        $sanitized = preferenciasUiSanitize(array_merge(preferenciasUiDefaults(), $preferencias));
        $json = json_encode($sanitized, JSON_UNESCAPED_UNICODE);
        $sql = 'UPDATE ' . $this->table . ' SET preferencias_ui = :prefs WHERE id = :id';
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':prefs' => $json, ':id' => $id]);
    }

    /**
     * Datos mínimos para autenticación y control anti-fuerza-bruta.
     */
    public function getForLoginByEmail(string $email): ?array {
        $sql = 'SELECT id, nombre, email, password, rol, preferencias_ui,
                       failed_login_count, failed_login_stage, login_blocked_until
                FROM ' . $this->table . '
                WHERE email = :email
                LIMIT 1';
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':email' => $email]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    /**
     * Limpia bloqueos/contador cuando el login es correcto.
     */
    public function clearLoginSecurity(int $id): void {
        $sql = 'UPDATE ' . $this->table . '
                SET failed_login_count = 0,
                    failed_login_stage = 0,
                    login_blocked_until = NULL
                WHERE id = :id';
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':id' => $id]);
    }

    /**
     * Suma intento fallido y, al llegar al límite, aplica bloqueo escalado:
     * - stage 0 => bloqueo 15 min, después pasa a stage 1
     * - stage 1 => bloqueo 24 h, después vuelve a stage 0
     *
     * @return array{locked:bool, lockType:string, blockedUntil:?string}
     */
    public function registerFailedLogin(int $id): array {
        $sqlSel = 'SELECT failed_login_count, failed_login_stage
                   FROM ' . $this->table . ' WHERE id = :id LIMIT 1';
        $sel = $this->conn->prepare($sqlSel);
        $sel->execute([':id' => $id]);
        $row = $sel->fetch(PDO::FETCH_ASSOC);
        if (!$row) {
            return ['locked' => false, 'lockType' => '', 'blockedUntil' => null];
        }

        $count = (int) ($row['failed_login_count'] ?? 0);
        $stage = (int) ($row['failed_login_stage'] ?? 0);
        $count++;

        if ($count < 5) {
            $sqlUpd = 'UPDATE ' . $this->table . ' SET failed_login_count = :c WHERE id = :id';
            $upd = $this->conn->prepare($sqlUpd);
            $upd->execute([':c' => $count, ':id' => $id]);
            return ['locked' => false, 'lockType' => '', 'blockedUntil' => null];
        }

        if ($stage === 0) {
            $blockedUntil = date('Y-m-d H:i:s', time() + (15 * 60));
            $nextStage = 1;
            $lockType = '15m';
        } else {
            $blockedUntil = date('Y-m-d H:i:s', time() + (24 * 60 * 60));
            $nextStage = 0;
            $lockType = '24h';
        }

        $sqlLock = 'UPDATE ' . $this->table . '
                    SET failed_login_count = 0,
                        failed_login_stage = :stage,
                        login_blocked_until = :blocked
                    WHERE id = :id';
        $lock = $this->conn->prepare($sqlLock);
        $lock->execute([
            ':stage' => $nextStage,
            ':blocked' => $blockedUntil,
            ':id' => $id,
        ]);

        return ['locked' => true, 'lockType' => $lockType, 'blockedUntil' => $blockedUntil];
    }

    /**
     * Comprueba si el usuario sigue bloqueado en este momento.
     */
    public function isLoginBlocked(array $userRow): bool {
        $blockedUntil = trim((string) ($userRow['login_blocked_until'] ?? ''));
        if ($blockedUntil === '') {
            return false;
        }
        $ts = strtotime($blockedUntil);
        if ($ts === false) {
            return false;
        }
        return $ts > time();
    }
}
