(function () {
    'use strict';

    var sidebar = document.getElementById('appSidebar');
    var toggle = document.getElementById('sidebarToggle');
    var mobileToggle = document.getElementById('sidebarMobileToggle');

    var collapseCookieName = 'mipedido_sidebar_collapsed';
    var collapseCookieMaxAge = 365 * 24 * 60 * 60;

    function setSidebarCollapseCookie(collapsed) {
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

    var AVISOS_LEIDOS_PREFIX = 'mipedido_avisos_vistos_';

    function avisosLeidosKey(uid) {
        return AVISOS_LEIDOS_PREFIX + uid;
    }

    function getAvisosLeidos(uid) {
        try {
            var raw = localStorage.getItem(avisosLeidosKey(uid));
            var arr = raw ? JSON.parse(raw) : [];
            return Array.isArray(arr) ? arr : [];
        } catch (e) {
            return [];
        }
    }

    function saveAvisosLeidos(uid, arr) {
        try {
            localStorage.setItem(avisosLeidosKey(uid), JSON.stringify(arr));
        } catch (ignore) { /* cuota llena o modo privado */ }
    }

    function marcarAvisoLeido(uid, taskId) {
        var id = String(taskId);
        var arr = getAvisosLeidos(uid);
        if (arr.indexOf(id) === -1) {
            arr.push(id);
            saveAvisosLeidos(uid, arr);
        }
    }

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

    document.querySelectorAll('.js-confirm-delete').forEach(function (el) {
        function validarConfirmacion(e) {
            var msg = el.getAttribute('data-confirm-message') || '¿Eliminar este registro?';
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

    document.querySelectorAll('.btn-factura').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var url = btn.getAttribute('data-factura-url');
            if (url) {
                window.open(url, '_blank', 'noopener,noreferrer');
            }
        });
    });

    document.querySelectorAll('.js-confirm-realizado').forEach(function (el) {
        function validarConfirmacion(e) {
            var msg = el.getAttribute('data-confirm-message') || '¿Confirmar esta acción?';
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

    /**
     * Intenta abrir WhatsApp app (escritorio/móvil) y, si no, pasa a WhatsApp Web.
     * No usamos API de pago: solo preparamos el texto para que el usuario confirme envío.
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

        // 3) Solo si NO abrió app, a los ~1200 ms abrimos WhatsApp Web.
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
})();

