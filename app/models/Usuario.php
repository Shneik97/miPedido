<?php
require_once __DIR__ . '/../../config/database.php';

/**
 * Modelo de usuarios.
 * Nivel estudiante:
 * - Gestiona login, perfil, rol, permisos y datos de configuracion.
 * - No pinta vistas; solo consulta/actualiza base de datos.
 */
class Usuario {
    private $conn;
    private $table = 'usuarios';

    public function __construct() {
        $database = new Database();
        $this->conn = $database->connect();
    }

    public function getAll(string $workspaceKey = ''): array {
        $sql = 'SELECT id, nombre, email, rol, workspace_key, onboarding_version
                FROM ' . $this->table . '
                WHERE (:ws = \'\' OR workspace_key = :ws)
                ORDER BY nombre ASC';
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':ws' => $workspaceKey]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getById(int $id, string $workspaceKey = ''): ?array {
        $sql = 'SELECT id, nombre, email, rol, workspace_key, onboarding_version,
                       email_verificado, plan_actual, plan_elegido_at, plan_cambiado_at,
                       preferencias_ui
                FROM ' . $this->table . '
                WHERE id = :id
                  AND (:ws = \'\' OR workspace_key = :ws)';
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':id' => $id, ':ws' => $workspaceKey]);
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

    public function updatePasswordByEmail(string $email, string $newPasswordPlain): bool
    {
        $hash = password_hash($newPasswordPlain, PASSWORD_DEFAULT);
        $sql = 'UPDATE ' . $this->table . ' SET password = :password WHERE email = :email';
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([
            ':password' => $hash,
            ':email' => $email,
        ]);
        return $stmt->rowCount() > 0;
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

    public function countAdmins(string $workspaceKey = ''): int {
        $sql = 'SELECT COUNT(*) FROM ' . $this->table . " WHERE rol = 'admin' AND (:ws = '' OR workspace_key = :ws)";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':ws' => $workspaceKey]);
        return (int) $stmt->fetchColumn();
    }

    public function create(string $nombre, string $email, string $passwordPlain, string $rol, ?string $workspaceKey = null): int {
        $hash = password_hash($passwordPlain, PASSWORD_DEFAULT);
        $sql = 'INSERT INTO ' . $this->table . ' (nombre, email, password, rol, workspace_key) VALUES (:nombre, :email, :password, :rol, :workspace_key)';
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([
            ':nombre' => $nombre,
            ':email' => $email,
            ':password' => $hash,
            ':rol' => $rol,
            ':workspace_key' => $workspaceKey,
        ]);
        return (int) $this->conn->lastInsertId();
    }

    public function update(int $id, string $nombre, string $email, string $rol, ?string $newPasswordPlain = null, string $workspaceKey = ''): void {
        if ($newPasswordPlain !== null && $newPasswordPlain !== '') {
            $hash = password_hash($newPasswordPlain, PASSWORD_DEFAULT);
            $sql = 'UPDATE ' . $this->table . ' SET nombre = :nombre, email = :email, rol = :rol, password = :password WHERE id = :id AND (:ws = \'\' OR workspace_key = :ws)';
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([
                ':nombre' => $nombre,
                ':email' => $email,
                ':rol' => $rol,
                ':password' => $hash,
                ':id' => $id,
                ':ws' => $workspaceKey,
            ]);
            return;
        }
        $sql = 'UPDATE ' . $this->table . ' SET nombre = :nombre, email = :email, rol = :rol WHERE id = :id AND (:ws = \'\' OR workspace_key = :ws)';
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([
            ':nombre' => $nombre,
            ':email' => $email,
            ':rol' => $rol,
            ':id' => $id,
            ':ws' => $workspaceKey,
        ]);
    }

    /**
     * Catálogo de permisos disponibles para marcar en la pantalla de edición de usuario.
     * Se aplican en menú, router y vistas vía permisos_helper.php.
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

    public function delete(int $id, string $workspaceKey = ''): bool {
        $sql = 'DELETE FROM ' . $this->table . ' WHERE id = :id AND (:ws = \'\' OR workspace_key = :ws)';
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([':id' => $id, ':ws' => $workspaceKey]);
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
     * Ejemplo:
     * - getForLoginByEmail('ana@demo.com') -> ['id'=>..,'password'=>..,'failed_login_count'=>..]
     */
    public function getForLoginByEmail(string $email): ?array {
        $sql = 'SELECT id, nombre, email, password, rol, workspace_key, onboarding_version,
                       plan_actual, plan_elegido_at, plan_cambiado_at, preferencias_ui,
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
     * Idea: cada login bueno "resetea" intentos fallidos.
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
     * Ejemplo:
     * - Quinto intento malo -> ['locked'=>true,'lockType'=>'15m',...]
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
     * Ejemplo:
     * - login_blocked_until en el futuro -> true
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

    /**
     * Marca el tutorial de onboarding como completado para un usuario.
     * Ejemplo:
     * - markOnboardingCompleted(12, 1)
     */
    public function markOnboardingCompleted(int $id, int $version = 1): void
    {
        if ($version < 1) {
            $version = 1;
        }
        $sql = 'UPDATE ' . $this->table . ' SET onboarding_version = :version WHERE id = :id';
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([
            ':version' => $version,
            ':id' => $id,
        ]);
    }

    public function setWorkspaceKey(int $id, string $workspaceKey, string $scopeWorkspaceKey = ''): void
    {
        $sql = 'UPDATE ' . $this->table . '
                SET workspace_key = :new_ws
                WHERE id = :id
                  AND (:scope_ws = \'\' OR workspace_key = :scope_ws)';
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([
            ':new_ws' => $workspaceKey,
            ':id' => $id,
            ':scope_ws' => $scopeWorkspaceKey,
        ]);
    }

    public function markEmailVerified(int $id): void
    {
        $sql = 'UPDATE ' . $this->table . ' SET email_verificado = 1 WHERE id = :id';
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':id' => $id]);
    }

    /**
     * Asigna plan inicial solo una vez (si aun no tenia plan).
     * Ejemplo:
     * - setPlanInicial(12, 'profesional')
     */
    public function setPlanInicial(int $id, string $plan): void
    {
        $allowed = ['basico', 'profesional', 'avanzado'];
        if (!in_array($plan, $allowed, true)) {
            $plan = 'basico';
        }
        $sql = 'UPDATE ' . $this->table . '
                SET plan_actual = :plan,
                    plan_elegido_at = NOW(),
                    plan_cambiado_at = NOW()
                WHERE id = :id
                  AND (plan_actual IS NULL OR plan_actual = \'\')';
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([
            ':plan' => $plan,
            ':id' => $id,
        ]);
    }

    /**
     * Regla de demo: solo permite cambiar plan cada 30 dias.
     * Ejemplo:
     * - canChangePlanNow(12) -> true/false
     */
    public function canChangePlanNow(int $id): bool
    {
        $sql = 'SELECT plan_cambiado_at FROM ' . $this->table . ' WHERE id = :id LIMIT 1';
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$row) {
            return false;
        }
        $last = trim((string) ($row['plan_cambiado_at'] ?? ''));
        if ($last === '') {
            return true;
        }
        $lastTs = strtotime($last);
        if ($lastTs === false) {
            return true;
        }
        return (time() - $lastTs) >= (30 * 24 * 60 * 60);
    }

    /**
     * Cambia plan si es valido y si cumple ventana de 30 dias.
     * Ejemplo:
     * - updatePlan(12, 'avanzado') -> true/false
     */
    public function updatePlan(int $id, string $plan): bool
    {
        $allowed = ['basico', 'profesional', 'avanzado'];
        if (!in_array($plan, $allowed, true)) {
            return false;
        }
        if (!$this->canChangePlanNow($id)) {
            return false;
        }
        $sql = 'UPDATE ' . $this->table . '
                SET plan_actual = :plan,
                    plan_cambiado_at = NOW()
                WHERE id = :id';
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([
            ':plan' => $plan,
            ':id' => $id,
        ]);
        return $stmt->rowCount() > 0;
    }
}
