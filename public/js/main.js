(function () {
    'use strict';

    // =========================================================
    // 1) CONSENTIMIENTO DE COOKIES (solo funcionales de demo)
    // =========================================================
    var consentStorageKey = 'mipedido_cookie_consent';
    var consentCookieName = 'mipedido_cookie_consent';
    var consentCookieMaxAge = 365 * 24 * 60 * 60;

    // Crea/actualiza una cookie simple para todo el sitio.
    function setCookie(name, value, maxAge) {
        document.cookie = name + '=' + value + '; path=/; max-age=' + maxAge + '; SameSite=Lax';
    }

    // Lee el valor de una cookie por nombre (si no existe, devuelve '').
    function getCookie(name) {
        var parts = document.cookie ? document.cookie.split('; ') : [];
        for (var i = 0; i < parts.length; i++) {
            var kv = parts[i].split('=');
            if (kv[0] === name) {
                return kv.slice(1).join('=');
            }
        }
        return '';
    }

    // Devuelve: 'accepted', 'rejected' o '' (sin decidir).
    function getCookieConsentValue() {
        var fromStorage = '';
        try {
            fromStorage = localStorage.getItem(consentStorageKey) || '';
        } catch (ignore) { /* localStorage no disponible */ }
        if (fromStorage === 'accepted' || fromStorage === 'rejected') {
            return fromStorage;
        }
        var fromCookie = getCookie(consentCookieName);
        return (fromCookie === 'accepted' || fromCookie === 'rejected') ? fromCookie : '';
    }

    // Guarda la decision del banner en localStorage y cookie.
    function setCookieConsentValue(value) {
        try {
            localStorage.setItem(consentStorageKey, value);
        } catch (ignore) { /* localStorage no disponible */ }
        setCookie(consentCookieName, value, consentCookieMaxAge);
    }

    // True solo cuando el usuario acepto cookies funcionales.
    function hasFunctionalConsent() {
        return getCookieConsentValue() === 'accepted';
    }

    // Muestra el banner solo si el usuario aún no eligió opción.
    function initCookieBanner() {
        var banner = document.getElementById('cookieBanner');
        var acceptBtn = document.getElementById('cookieAcceptBtn');
        var rejectBtn = document.getElementById('cookieRejectBtn');
        if (!banner || !acceptBtn || !rejectBtn) {
            return;
        }
        if (getCookieConsentValue() !== '') {
            banner.hidden = true;
            return;
        }
        banner.hidden = false;
        acceptBtn.addEventListener('click', function () {
            setCookieConsentValue('accepted');
            banner.hidden = true;
        });
        rejectBtn.addEventListener('click', function () {
            setCookieConsentValue('rejected');
            banner.hidden = true;
        });
    }

    initCookieBanner();

    // =========================================================
    // 2) SIDEBAR (desktop/movil) + estado colapsado
    // =========================================================
    // Sidebar desktop/móvil y cookie de estado (colapsado/no colapsado).
    var sidebar = document.getElementById('appSidebar');
    var toggle = document.getElementById('sidebarToggle');
    var mobileToggle = document.getElementById('sidebarMobileToggle');

    var collapseCookieName = 'mipedido_sidebar_collapsed';
    var collapseCookieMaxAge = 365 * 24 * 60 * 60;

    // Guarda si el menu lateral queda colapsado (1) o expandido (0).
    function setSidebarCollapseCookie(collapsed) {
        if (!hasFunctionalConsent()) {
            return;
        }
        document.cookie = collapseCookieName + '=' + (collapsed ? '1' : '0') +
            '; path=/; max-age=' + collapseCookieMaxAge + '; SameSite=Lax';
    }

    if (toggle && sidebar) {
        toggle.addEventListener('click', function () {
            if (window.matchMedia('(min-width: 992px)').matches) {
                sidebar.classList.toggle('collapsed');
                setSidebarCollapseCookie(sidebar.classList.contains('collapsed'));
            } else {
                sidebar.classList.toggle('collapsed');
            }
        });
    }

    if (mobileToggle && sidebar) {
        mobileToggle.addEventListener('click', function () {
            sidebar.classList.toggle('mobile-open');
        });
    }

    // =========================================================
    // 3) NOTIFICACIONES DE CABECERA
    // =========================================================
    // Guardamos "leido/no leido" por usuario en localStorage.
    var AVISOS_LEIDOS_PREFIX = 'mipedido_avisos_vistos_';

    // Construye la clave de localStorage para un usuario concreto.
    function avisosLeidosKey(uid) {
        return AVISOS_LEIDOS_PREFIX + uid;
    }

    // Devuelve array de ids de avisos leidos para ese usuario.
    function getAvisosLeidos(uid) {
        if (!hasFunctionalConsent()) {
            return [];
        }
        try {
            var raw = localStorage.getItem(avisosLeidosKey(uid));
            var arr = raw ? JSON.parse(raw) : [];
            return Array.isArray(arr) ? arr : [];
        } catch (e) {
            return [];
        }
    }

    // Persiste el array de avisos leidos.
    function saveAvisosLeidos(uid, arr) {
        if (!hasFunctionalConsent()) {
            return;
        }
        try {
            localStorage.setItem(avisosLeidosKey(uid), JSON.stringify(arr));
        } catch (ignore) { /* cuota llena o modo privado */ }
    }

    // Añade un aviso como leido si todavia no estaba en la lista.
    function marcarAvisoLeido(uid, taskId) {
        var id = String(taskId);
        var arr = getAvisosLeidos(uid);
        if (arr.indexOf(id) === -1) {
            arr.push(id);
            saveAvisosLeidos(uid, arr);
        }
    }

    // Pinta visualmente qué avisos están leídos.
    function aplicarEstadoAvisos(uid) {
        var leidos = getAvisosLeidos(uid);
        document.querySelectorAll('.nav-notif-item[data-notif-id]').forEach(function (el) {
            var id = el.getAttribute('data-notif-id');
            if (!id) {
                return;
            }
            if (leidos.indexOf(id) !== -1) {
                el.classList.remove('notif-nuevo');
                el.classList.add('notif-visto');
            }
        });
        actualizarBadgeNotifCampana();
    }

    // Calcula cuántos avisos nuevos quedan y actualiza el badge visual.
    function actualizarBadgeNotifCampana() {
        var badge = document.getElementById('navNotifBadge');
        if (!badge) {
            return;
        }
        var n = document.querySelectorAll('.nav-notif-item.notif-nuevo').length;
        if (n === 0) {
            badge.style.display = 'none';
        } else {
            badge.style.display = '';
            badge.textContent = n > 9 ? '9+' : String(n);
        }
    }

    // Inicializa clicks y estado del dropdown de notificaciones.
    function initNotificacionesCabecera() {
        var btn = document.getElementById('dropdownNotificaciones');
        var menu = document.getElementById('dropdownNotificacionesMenu');
        if (!btn || !menu) {
            return;
        }
        var uid = btn.getAttribute('data-user-id');
        if (!uid || uid === '0') {
            return;
        }

        aplicarEstadoAvisos(uid);

        menu.addEventListener('click', function (e) {
            var a = e.target.closest('a.nav-notif-item');
            if (!a) {
                return;
            }
            var nid = a.getAttribute('data-notif-id');
            if (!nid || nid === '0') {
                return;
            }
            e.preventDefault();
            marcarAvisoLeido(uid, nid);
            a.classList.remove('notif-nuevo');
            a.classList.add('notif-visto');
            actualizarBadgeNotifCampana();
            window.location.href = a.getAttribute('href');
        });

        btn.addEventListener('show.bs.dropdown', function () {
            aplicarEstadoAvisos(uid);
        });
    }

    initNotificacionesCabecera();

    /**
     * Enlaza una confirmacion a enlaces o formularios.
     * Idea estudiante: una sola funcion reutilizable para todos los "¿Seguro?".
     */
    function bindConfirmAction(selector, defaultMessage) {
        document.querySelectorAll(selector).forEach(function (el) {
            function validarConfirmacion(e) {
                var msg = el.getAttribute('data-confirm-message') || defaultMessage;
                if (!window.confirm(msg)) {
                    e.preventDefault();
                }
            }
            if (el.tagName === 'FORM') {
                el.addEventListener('submit', validarConfirmacion);
            } else {
                el.addEventListener('click', validarConfirmacion);
            }
        });
    }

    // =========================================================
    // 4) ACCIONES SENSIBLES (confirmar borrar / confirmar realizado)
    // =========================================================
    bindConfirmAction('.js-confirm-delete', '¿Eliminar este registro?');

    // Boton de factura: abre la vista imprimible en una pestaña nueva.
    document.querySelectorAll('.btn-factura').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var url = btn.getAttribute('data-factura-url');
            if (url) {
                window.open(url, '_blank', 'noopener,noreferrer');
            }
        });
    });

    bindConfirmAction('.js-confirm-realizado', '¿Confirmar esta acción?');

    // =========================================================
    // 5) WHATSAPP (intento app nativa + fallback web)
    // =========================================================
    /**
     * Intenta abrir WhatsApp app (escritorio/movil) y, si no, pasa a WhatsApp Web.
     * No usamos API de pago: solo preparamos el texto para que el usuario confirme envio.
     */
    function abrirWhatsAppInteligente(btn) {
        var rawText = btn.getAttribute('data-wa-json') || '';
        var text = rawText;
        try {
            text = JSON.parse(rawText);
        } catch (ignore) { /* compatibilidad con texto plano */ }

        var phone = String(btn.getAttribute('data-wa-phone') || '').replace(/\D+/g, '');
        if (!phone || !text) {
            var hrefFallback = btn.getAttribute('href');
            if (hrefFallback) {
                window.open(hrefFallback, '_blank', 'noopener,noreferrer');
            }
            return;
        }

        var encoded = encodeURIComponent(text);
        var appUrl = 'whatsapp://send?phone=' + phone + '&text=' + encoded;
        var webUrl = 'https://wa.me/' + phone + '?text=' + encoded;
        var icon = btn.querySelector('i');
        var originalClass = icon ? icon.className : '';

        btn.classList.remove('btn-success');
        btn.classList.add('btn-warning');
        btn.title = 'Intentando abrir app de WhatsApp...';
        if (icon) {
            icon.className = 'fa-solid fa-paper-plane';
        }

        // 1) Intento abrir app nativa.
        window.location.href = appUrl;

        // 2) Detectamos si la página perdió foco/visibilidad.
        // Si pasa, asumimos que la app sí se abrió y NO forzamos el fallback web.
        var appAbierta = false;
        function marcarAppAbierta() {
            appAbierta = true;
        }
        window.addEventListener('blur', marcarAppAbierta, { once: true });
        document.addEventListener('visibilitychange', function onVisChange() {
            if (document.visibilityState === 'hidden') {
                appAbierta = true;
            }
        }, { once: true });

        // 3) Solo si NO abrio app, abrimos WhatsApp Web.
        window.setTimeout(function () {
            if (!appAbierta) {
                window.open(webUrl, '_blank', 'noopener,noreferrer');
            }
            btn.classList.remove('btn-warning');
            btn.classList.add('btn-success');
            btn.title = 'Enviar por WhatsApp (intenta app y si no, WhatsApp Web)';
            if (icon) {
                icon.className = originalClass;
            }
        }, 900);
    }

    document.querySelectorAll('a.js-open-wa').forEach(function (btn) {
        btn.addEventListener('click', function (e) {
            e.preventDefault();
            abrirWhatsAppInteligente(btn);
        });
    });

    // =========================================================
    // 6) COPIAR TEXTO WHATSAPP (feedback visual)
    // =========================================================
    /** Tras copiar al portapapeles: check verde breve, sin alert del navegador. */
    function animarCopiadoWhatsApp(btn) {
        var icon = btn.querySelector('i');
        var iconClassOriginal = icon ? icon.className : '';
        var clasesBtnOriginal = btn.className;
        btn.disabled = true;
        if (icon) {
            icon.className = 'fa-solid fa-check';
        }
        btn.classList.remove('btn-outline-success');
        btn.classList.add('btn-success');
        btn.style.transform = 'scale(1.08)';
        btn.style.transition = 'transform 0.2s ease';
        window.setTimeout(function () {
            btn.style.transform = '';
            btn.classList.remove('btn-success');
            btn.classList.add('btn-outline-success');
            if (icon) {
                icon.className = iconClassOriginal;
            }
            btn.disabled = false;
            btn.className = clasesBtnOriginal;
        }, 1600);
    }

    // Copia el texto del WhatsApp y muestra feedback.
    document.querySelectorAll('.js-copy-wa-text').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var raw = btn.getAttribute('data-wa-json');
            var text = raw;
            try {
                text = JSON.parse(raw);
            } catch (ignore) { /* texto plano antiguo */ }
            if (!text) {
                return;
            }
            if (navigator.clipboard && navigator.clipboard.writeText) {
                navigator.clipboard.writeText(text).then(function () {
                    animarCopiadoWhatsApp(btn);
                }).catch(function () {
                    window.prompt('Copia este texto manualmente:', text);
                });
            } else {
                window.prompt('Copia este texto manualmente:', text);
            }
        });
    });

    // =========================================================
    // 7) DASHBOARD (graficos, navegacion de periodos y onboarding)
    // =========================================================
    // Arranca la logica del dashboard solo si la vista cargo datos globales.
    function initDashboardPage() {
        var cfg = window.MIPEDIDO_DASHBOARD_DATA || null;
        if (!cfg) {
            return;
        }
        var ventasLabels = cfg.ventasLabels || [];
        var ventasData = cfg.ventasData || [];
        var ventasOffset = Number(cfg.ventasOffset || 0);
        var estadosLabels = cfg.estadosLabels || [];
        var estadosData = cfg.estadosData || [];

        var coloresEstados = [
            'rgba(234, 179, 8, 0.85)',
            'rgba(59, 130, 246, 0.85)',
            'rgba(34, 197, 94, 0.85)',
            'rgba(139, 92, 246, 0.85)',
            'rgba(239, 68, 68, 0.85)',
            'rgba(16, 185, 129, 0.85)'
        ];

        var ventasChart = null;
        if (typeof Chart !== 'undefined') {
            var chartVentasEl = document.getElementById('chartVentas');
            if (chartVentasEl) {
                ventasChart = new Chart(chartVentasEl, {
                    type: 'bar',
                    data: {
                        labels: ventasLabels,
                        datasets: [{
                            label: 'Ventas (€)',
                            data: ventasData,
                            backgroundColor: 'rgba(37, 99, 235, 0.65)',
                            borderColor: 'rgb(37, 99, 235)',
                            borderWidth: 1
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: { legend: { display: false } },
                        scales: { y: { beginAtZero: true } }
                    }
                });
            }

            var chartEstadosEl = document.getElementById('chartEstados');
            if (chartEstadosEl && estadosData.length > 0) {
                new Chart(chartEstadosEl, {
                    type: 'pie',
                    data: {
                        labels: estadosLabels,
                        datasets: [{
                            data: estadosData,
                            backgroundColor: coloresEstados.slice(0, estadosData.length),
                            borderWidth: 1
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: { legend: { position: 'bottom' } }
                    }
                });
            }
        }

        // Formato de numero a euros estilo ES: 1234.5 -> "1.234,50 €"
        function euroEs(v) {
            return Number(v || 0).toLocaleString('es-ES', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + ' €';
        }

        // Escape minimo para pintar texto en HTML sin inyectar etiquetas.
        function escHtml(v) {
            return String(v || '')
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;');
        }

        // Dibuja la tabla "Productos mas vendidos" con HTML simple.
        function renderTopProductos(rows) {
            var body = document.getElementById('topProductosBody');
            if (!body) return;
            if (!rows || rows.length === 0) {
                body.innerHTML = '<p class="text-muted mb-0">No hay ventas registradas en este periodo.</p>';
                return;
            }
            var html = '<div class="table-responsive"><table class="table table-sm align-middle mb-0 dash-top-products"><thead><tr><th>Producto</th><th class="text-end">Unidades</th><th class="text-end">Total</th></tr></thead><tbody>';
            rows.forEach(function (r) {
                html += '<tr><td>' + escHtml(r.producto_nombre) + '</td><td class="text-end">' + Number(r.unidades_vendidas || 0) + '</td><td class="text-end">' + euroEs(r.total_facturado) + '</td></tr>';
            });
            html += '</tbody></table></div>';
            body.innerHTML = html;
        }

        // Habilita/deshabilita botones de navegacion por periodos.
        function setNavButtons(canBack, canForward) {
            var bBack = document.getElementById('btnVentasAtras');
            var bForward = document.getElementById('btnVentasAdelante');
            if (bBack) bBack.disabled = !canBack;
            if (bForward) bForward.disabled = !canForward;
        }

        // Animacion breve antes de cambiar los datos del panel.
        function animarSalida(panel, direccion) {
            panel.classList.remove('ventas-slide-left-in', 'ventas-slide-right-in');
            panel.classList.add(direccion === 'left' ? 'ventas-slide-left-out' : 'ventas-slide-right-out');
        }

        // Animacion breve al mostrar los nuevos datos.
        function animarEntrada(panel, direccion) {
            panel.classList.remove('ventas-slide-left-out', 'ventas-slide-right-out');
            panel.classList.add(direccion === 'left' ? 'ventas-slide-left-in' : 'ventas-slide-right-in');
            window.requestAnimationFrame(function () {
                panel.classList.remove('ventas-slide-left-in', 'ventas-slide-right-in');
            });
        }

        /**
         * Carga por AJAX un nuevo bloque de 6 meses.
         * - offset: cuanto retrocedemos desde el periodo actual
         * - direccion: 'left' o 'right' (solo para animacion)
         */
        function cargarVentas(offset, direccion) {
            var panel = document.getElementById('ventasPanelContent');
            if (!panel || !ventasChart) {
                window.location.href = 'index.php?page=dashboard&ventas_offset=' + offset;
                return;
            }
            animarSalida(panel, direccion);
            window.setTimeout(function () {
                fetch('index.php?page=dashboard&ajax=ventas_panel&ventas_offset=' + encodeURIComponent(offset), {
                    credentials: 'same-origin'
                }).then(function (r) {
                    return r.json();
                }).then(function (data) {
                    ventasOffset = Number(data.offset || 0);
                    ventasChart.data.labels = data.ventas_labels || [];
                    ventasChart.data.datasets[0].data = data.ventas_data || [];
                    ventasChart.update();
                    renderTopProductos(data.productos_mas_vendidos || []);
                    setNavButtons(!!data.puede_ir_atras, !!data.puede_ir_adelante);
                    animarEntrada(panel, direccion);
                }).catch(function () {
                    window.location.href = 'index.php?page=dashboard&ventas_offset=' + offset;
                });
            }, 140);
        }

        var btnBack = document.getElementById('btnVentasAtras');
        var btnForward = document.getElementById('btnVentasAdelante');
        if (btnBack) {
            btnBack.addEventListener('click', function () {
                if (!btnBack.disabled) cargarVentas(ventasOffset + 6, 'left');
            });
        }
        if (btnForward) {
            btnForward.addEventListener('click', function () {
                if (!btnForward.disabled) cargarVentas(Math.max(0, ventasOffset - 6), 'right');
            });
        }

        // Tutorial corto solo para admins nuevos.
        if (cfg.showAdminOnboarding) {
            var steps = [
                {
                    selector: '.row.g-4.mb-2 .col-6.col-md-3:first-child .card',
                    title: 'Indicadores principales',
                    text: 'Aquí ves un resumen inmediato de clientes, pedidos pendientes, catálogo y tareas abiertas.'
                },
                {
                    selector: '#ventasPanelWrap',
                    title: 'Ventas del periodo',
                    text: 'Este bloque muestra ventas por mes (en bloques de 6 meses) para ver tendencias rápidamente.'
                },
                {
                    selector: '#topProductosCard',
                    title: 'Productos más vendidos',
                    text: 'Este ranking te ayuda a decidir qué productos rotan mejor en el mismo periodo de ventas.'
                }
            ];

            var overlay = document.getElementById('onboardingOverlay');
            var bubble = document.getElementById('onboardingBubble');
            var stepEl = document.getElementById('onboardingStep');
            var titleEl = document.getElementById('onboardingTitle');
            var textEl = document.getElementById('onboardingText');
            var prevBtn = document.getElementById('onboardingPrev');
            var nextBtn = document.getElementById('onboardingNext');
            var finishBtn = document.getElementById('onboardingFinish');
            if (!overlay || !bubble || !stepEl || !titleEl || !textEl || !prevBtn || !nextBtn || !finishBtn) {
                return;
            }
            var idx = 0;
            var currentTarget = null;
            overlay.classList.add('show');
            bubble.classList.add('show');

            // Coloca el globo del tutorial evitando que salga de la pantalla.
            function placeBubble(target) {
                var r = target.getBoundingClientRect();
                var margin = 12;
                var viewportWidth = document.documentElement.clientWidth;
                var viewportHeight = document.documentElement.clientHeight;
                var bubbleWidth = bubble.offsetWidth || 320;
                var bubbleHeight = bubble.offsetHeight || 180;

                var left = r.left;
                if (left + bubbleWidth > viewportWidth - margin) left = viewportWidth - bubbleWidth - margin;
                if (left < margin) left = margin;

                var top = r.bottom + margin;
                var fitsBelow = top + bubbleHeight <= viewportHeight - margin;
                if (!fitsBelow) top = r.top - bubbleHeight - margin;
                if (top < margin) top = margin;
                if (top + bubbleHeight > viewportHeight - margin) top = Math.max(margin, viewportHeight - bubbleHeight - margin);

                bubble.style.top = Math.round(top) + 'px';
                bubble.style.left = Math.round(left) + 'px';
            }

            // Pinta un paso del onboarding (titulo, texto y posicion).
            function renderStep() {
                if (currentTarget) currentTarget.classList.remove('onboarding-highlight');
                var step = steps[idx];
                var target = document.querySelector(step.selector);
                if (!target) return;
                currentTarget = target;
                target.classList.add('onboarding-highlight');
                stepEl.textContent = 'Paso ' + (idx + 1) + ' de ' + steps.length;
                titleEl.textContent = step.title;
                textEl.textContent = step.text;
                prevBtn.disabled = idx === 0;
                nextBtn.classList.toggle('d-none', idx === steps.length - 1);
                finishBtn.classList.toggle('d-none', idx !== steps.length - 1);
                placeBubble(target);
            }

            prevBtn.addEventListener('click', function () { if (idx > 0) { idx--; renderStep(); } });
            nextBtn.addEventListener('click', function () { if (idx < steps.length - 1) { idx++; renderStep(); } });
            window.addEventListener('resize', function () { if (currentTarget) placeBubble(currentTarget); });
            window.addEventListener('scroll', function () { if (currentTarget) placeBubble(currentTarget); }, { passive: true });
            renderStep();
        }
    }

    initDashboardPage();
})();

