<?php
// Configuracion de cuenta:
// - Admin: ve perfil + bloque de plan simulado
// - Empleado: vista basica de perfil
// Nivel estudiante: esta vista solo muestra informacion y formularios simples.
$pageTitle = 'Configuración';
$currentNav = 'config';
$rol = (string) ($usuarioConfig['rol'] ?? 'empleado');
$esAdmin = !empty($esAdminConfig);
$tituloRol = $esAdmin ? 'Admin' : 'Empleado';
$emailVerificado = (int) ($usuarioConfig['email_verificado'] ?? 0) === 1;
$okConfig = (string) ($_GET['ok'] ?? '');
$errorConfig = (string) ($_GET['error'] ?? '');
$planActual = strtolower(trim((string) ($usuarioConfig['plan_actual'] ?? '')));
$planLabelMap = ['basico' => 'Basico', 'profesional' => 'Profesional', 'avanzado' => 'Avanzado'];
$planActualLabel = $planLabelMap[$planActual] ?? 'Sin seleccionar';
ob_start();
?>
<style>
    .perfil-hero {
        background: linear-gradient(135deg, rgba(37, 99, 235, .08), rgba(14, 165, 233, .06));
        border: 1px solid #dbeafe;
    }
    .perfil-chip {
        display: inline-flex;
        align-items: center;
        gap: .4rem;
        border-radius: 999px;
        padding: .35rem .75rem;
        font-size: .84rem;
        font-weight: 600;
        background: #eff6ff;
        color: #1d4ed8;
    }
    .perfil-item {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        padding: .85rem .95rem;
    }
    .perfil-label {
        font-size: .78rem;
        text-transform: uppercase;
        letter-spacing: .02em;
        color: #64748b;
        margin-bottom: .2rem;
    }
    .perfil-value {
        font-size: 1.02rem;
        font-weight: 600;
        color: #0f172a;
    }
    .perfil-verify-ok {
        color: #166534;
        background: #dcfce7;
        border: 1px solid #86efac;
        border-radius: 8px;
        font-size: .85rem;
        font-weight: 700;
        padding: .35rem .6rem;
        display: inline-flex;
        align-items: center;
        gap: .35rem;
    }
    .plan-card {
        border: 1px solid #dbeafe;
        border-radius: 14px;
        background: linear-gradient(135deg, #ffffff 0%, #eff6ff 100%);
        box-shadow: 0 10px 24px rgba(37, 99, 235, 0.1);
    }
    .plan-badge {
        display: inline-flex;
        align-items: center;
        gap: .35rem;
        padding: .28rem .7rem;
        border-radius: 999px;
        background: #dbeafe;
        color: #1d4ed8;
        font-size: .82rem;
        font-weight: 700;
    }
    .plan-note {
        border: 1px dashed #bfdbfe;
        background: rgba(255, 255, 255, .7);
        border-radius: 10px;
        padding: .55rem .7rem;
        font-size: .84rem;
        color: #475569;
    }
</style>
<div class="row g-3">
    <div class="col-12">
        <div class="card page-card perfil-hero">
            <div class="card-body">
                <p class="text-muted text-uppercase small mb-1">Perfil de cuenta</p>
                <div class="d-flex flex-wrap align-items-center gap-2 mb-2">
                    <h1 class="display-6 mb-0"><?= htmlspecialchars($tituloRol) ?></h1>
                    <span class="perfil-chip"><i class="fa-solid fa-shield-halved"></i><?= htmlspecialchars($tituloRol) ?></span>
                </div>
                <p class="text-muted mb-0">
                    <?= $esAdmin
                        ? 'Tienes acceso completo a la gestión del entorno de tu cuenta.'
                        : 'Vista limitada: tu cuenta fue creada por un administrador y solo muestra datos básicos.' ?>
                </p>
            </div>
        </div>
    </div>

    <div class="col-12 col-lg-8">
        <div class="card page-card h-100">
            <div class="card-body">
                <h2 class="h5 mb-3">Datos de la cuenta</h2>
                <?php if ($okConfig === 'verificada' || $okConfig === 'ya_verificada'): ?>
                    <div class="alert alert-success py-2 small">Cuenta Verificada correctamente.</div>
                <?php elseif ($okConfig === 'plan_actualizado'): ?>
                        <div class="alert alert-success py-2 small">Plan actualizado correctamente (simulacion).</div>
                <?php elseif ($errorConfig !== ''): ?>
                    <div class="alert alert-danger py-2 small">
                        <?php
                        $mapErrors = [
                            'csrf' => 'Solicitud inválida. Recarga la página e inténtalo de nuevo.',
                            'cuenta' => 'No se encontró la cuenta actual.',
                            'permiso' => 'Solo un admin puede cambiar su plan.',
                            'plan_datos' => 'Revisa nombre o DNI demo.',
                            'plan_email' => 'Revisa el email de facturacion.',
                            'plan_tarjeta' => 'Revisa ultimos 4 digitos o caducidad.',
                            'plan_sim' => 'Debes aceptar la simulacion academica.',
                            'plan_cooldown' => 'Solo puedes cambiar de plan una vez cada 30 dias.',
                        ];
                        echo htmlspecialchars($mapErrors[$errorConfig] ?? 'No se pudo completar la verificación.');
                        ?>
                    </div>
                <?php endif; ?>
                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="perfil-item">
                            <div class="perfil-label">Nombre completo</div>
                            <div class="perfil-value"><?= htmlspecialchars((string) ($usuarioConfig['nombre'] ?? '')) ?></div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="perfil-item">
                            <div class="perfil-label">Correo electrónico</div>
                            <div class="perfil-value"><?= htmlspecialchars((string) ($usuarioConfig['email'] ?? '')) ?></div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="perfil-item">
                            <div class="perfil-label">Rol</div>
                            <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
                                <div class="perfil-value"><?= htmlspecialchars(ucfirst($rol)) ?></div>
                                <?php if ($emailVerificado): ?>
                                    <span class="perfil-verify-ok"><i class="fa-solid fa-circle-check"></i>Cuenta Verificada</span>
                                <?php else: ?>
                                    <form method="post" action="index.php?page=config_email_verify_send" class="d-inline">
                                        <?= csrfInput() ?>
                                        <button type="submit" class="btn btn-sm btn-outline-primary">
                                            <i class="fa-solid fa-circle-check me-1"></i>Verificar correo
                                        </button>
                                    </form>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php if ($esAdmin): ?>
    <div class="col-12 col-lg-4">
        <div class="card page-card h-100 plan-card">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between gap-2 mb-3">
                    <h2 class="h6 text-uppercase text-muted mb-0">Plan actual</h2>
                    <span class="plan-badge"><i class="fa-solid fa-crown"></i><?= htmlspecialchars($planActualLabel) ?></span>
                </div>
                <p class="text-muted small mb-2">Tu suscripcion está activa para esta cuenta admin.</p>
                <div class="plan-note mb-3">
                    Plan mensual abonado.
                </div>
                <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
                    <?php if (!empty($puedeCambiarPlan)): ?>
                        <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#planChangeModal">
                            <i class="fa-solid fa-arrow-up-right-dots me-1"></i>Cambiar plan
                        </button>
                        <span class="small text-success"><i class="fa-solid fa-circle-check me-1"></i>Disponible ahora</span>
                    <?php else: ?>
                        <span class="small text-muted">Cambio disponible del plan cada 30 dias</span>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>
<?php if ($esAdmin && !empty($puedeCambiarPlan)): ?>
<!-- Modal de cambio de plan (demo academica, sin pago real). -->
<div class="modal fade" id="planChangeModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form method="post" action="index.php?page=config_plan_update">
                <?= csrfInput() ?>
                <div class="modal-header">
                    <h5 class="modal-title">Cambiar plan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    <p class="small text-muted mb-3">Formulario: Rellene correctamente sus datos.</p>
                    <div class="mb-2">
                        <label class="form-label">Nuevo plan</label>
                        <select name="plan" class="form-select" required>
                            <option value="basico">Basico (19EUR/mes)</option>
                            <option value="profesional">Profesional (39EUR/mes)</option>
                            <option value="avanzado">Avanzado (69EUR/mes)</option>
                        </select>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Nombre y apellidos</label>
                        <input type="text" name="titular" class="form-control" required minlength="4" maxlength="80">
                    </div>
                    <div class="mb-2">
                        <label class="form-label">DNI/NIE</label>
                        <input type="text" name="dni" class="form-control" required maxlength="12" placeholder="12345678A">
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Email facturacion</label>
                        <input type="email" name="email_facturacion" class="form-control" required>
                    </div>
                    <div class="row g-2">
                        <div class="col-6">
                            <label class="form-label">Ultimos 4 digitos</label>
                            <input type="text" name="card_last4" class="form-control" required pattern="\d{4}" maxlength="4" placeholder="1234">
                        </div>
                        <div class="col-6">
                            <label class="form-label">Caducidad</label>
                            <input type="text" name="caducidad" class="form-control" required pattern="(0[1-9]|1[0-2])\/\d{2}" placeholder="MM/AA">
                        </div>
                    </div>
                    <div class="form-check mt-3">
                        <input class="form-check-input" type="checkbox" value="1" name="accept_sim" id="acceptPlanSim" required>
                        <label class="form-check-label small" for="acceptPlanSim">
                            Acepto los terminos y condiciones.
                        </label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Guardar cambio de plan</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>
<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/main.php';
