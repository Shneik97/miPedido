<?php
// Seleccion inicial de plan para cuentas admin nuevas.
// Demo academica: se validan datos mock y no se procesa ningun pago real.
$pageTitle = 'Seleccionar plan';
$showSidebar = false;
$currentNav = '';
$errorPlan = (string) ($_GET['error'] ?? '');
ob_start();
?>
<div class="container py-4" style="max-width: 1100px;">
    <div class="card page-card mb-3">
        <div class="card-body">
            <h1 class="h3 mb-2">Elige tu plan inicial</h1>
            <p class="text-muted mb-2">Se guarda el plan elegido.</p>
            <div class="alert alert-info py-2 small mb-0">
                Por seguridad, esta solo pide datos como (por ejemplo, ultimos 4 digitos). No se guardará tú tarjeta.
            </div>
        </div>
    </div>

    <?php if ($errorPlan !== ''): ?>
        <div class="alert alert-danger py-2 small">
            <?php
            $mapErrors = [
                'csrf' => 'Solicitud invalida. Recarga la pagina.',
                'plan' => 'Debes elegir un plan valido.',
                'datos' => 'Revisa nombre o DNI demo.',
                'email' => 'Introduce un email de facturacion valido.',
                'tarjeta' => 'Revisa ultimos 4 digitos o caducidad (MM/AA).',
                'terminos' => 'Debes aceptar la simulacion academica.',
            ];
            echo htmlspecialchars($mapErrors[$errorPlan] ?? 'No se pudo guardar tu plan.');
            ?>
        </div>
    <?php endif; ?>

    <div class="row g-3">
        <div class="col-md-4">
            <div class="card h-100 border-primary-subtle">
                <div class="card-body d-flex flex-column">
                    <h2 class="h5">Basico</h2>
                    <p class="display-6 mb-2">19EUR <span class="fs-6 text-muted">/mes</span></p>
                    <ul class="small text-muted mb-3">
                        <li>Clientes y productos</li>
                        <li>Pedidos y facturacion basica</li>
                        <li>1 admin + 1 empleado</li>
                    </ul>
                    <button class="btn btn-outline-primary mt-auto js-open-plan-modal" data-plan="basico">Elegir Basico</button>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card h-100 border-primary">
                <div class="card-body d-flex flex-column">
                    <div class="d-flex justify-content-between align-items-center">
                        <h2 class="h5 mb-0">Profesional</h2>
                        <span class="badge text-bg-primary">Mas elegido</span>
                    </div>
                    <p class="display-6 mb-2 mt-2">39EUR <span class="fs-6 text-muted">/mes</span></p>
                    <ul class="small text-muted mb-3">
                        <li>Todo lo del plan Basico</li>
                        <li>Tareas y calendario</li>
                        <li>Hasta 5 usuarios</li>
                    </ul>
                    <button class="btn btn-primary mt-auto js-open-plan-modal" data-plan="profesional">Elegir Profesional</button>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card h-100 border-primary-subtle">
                <div class="card-body d-flex flex-column">
                    <h2 class="h5">Avanzado</h2>
                    <p class="display-6 mb-2">69EUR <span class="fs-6 text-muted">/mes</span></p>
                    <ul class="small text-muted mb-3">
                        <li>Todo lo del plan Profesional</li>
                        <li>Permisos avanzados</li>
                        <li>Soporte prioritario demo</li>
                    </ul>
                    <button class="btn btn-outline-primary mt-auto js-open-plan-modal" data-plan="avanzado">Elegir Avanzado</button>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="planModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form method="post" action="index.php?page=plan_select_store">
                <?= csrfInput() ?>
                <input type="hidden" name="plan" id="planInput" value="">
                <div class="modal-header">
                    <h5 class="modal-title">Confirmar plan <span id="planNameText"></span></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    <p class="small text-muted mb-3">Rellene los campos correctamente</p>
                    <div class="mb-2">
                        <label class="form-label">Nombre y apellidos</label>
                        <input type="text" name="titular" class="form-control" required minlength="4" maxlength="80">
                    </div>
                    <div class="mb-2">
                        <label class="form-label">DNI simple</label>
                        <input type="text" name="dni" class="form-control" required placeholder="12345678A" maxlength="12">
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
                        <input class="form-check-input" type="checkbox" value="1" name="accept_sim" id="acceptSim" required>
                        <label class="form-check-label small" for="acceptSim">
                            Acepto los terminos y condiciones.
                        </label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Guardar plan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    window.addEventListener('load', function () {
        // Script simple: al pulsar "Elegir", guardamos plan en hidden y abrimos modal.
        var modalEl = document.getElementById('planModal');
        if (!modalEl || typeof bootstrap === 'undefined') return;
        var planInput = document.getElementById('planInput');
        var planNameText = document.getElementById('planNameText');
        var modal = new bootstrap.Modal(modalEl);
        var map = { basico: 'Basico', profesional: 'Profesional', avanzado: 'Avanzado' };
        document.querySelectorAll('.js-open-plan-modal').forEach(function (btn) {
            btn.addEventListener('click', function () {
                var plan = (btn.getAttribute('data-plan') || '').toLowerCase();
                if (!map[plan]) return;
                planInput.value = plan;
                planNameText.textContent = map[plan];
                modal.show();
            });
        });
    });
</script>
<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/main.php';
