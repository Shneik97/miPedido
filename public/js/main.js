(function () {
    'use strict';

    var sidebar = document.getElementById('appSidebar');
    var toggle = document.getElementById('sidebarToggle');
    var mobileToggle = document.getElementById('sidebarMobileToggle');

    if (toggle && sidebar) {
        toggle.addEventListener('click', function () {
            sidebar.classList.toggle('collapsed');
        });
    }

    if (mobileToggle && sidebar) {
        mobileToggle.addEventListener('click', function () {
            sidebar.classList.toggle('mobile-open');
        });
    }

    document.querySelectorAll('a.js-confirm-delete').forEach(function (el) {
        el.addEventListener('click', function (e) {
            var msg = el.getAttribute('data-confirm-message') || '¿Eliminar este registro?';
            if (!window.confirm(msg)) {
                e.preventDefault();
            }
        });
    });

    document.querySelectorAll('.btn-factura').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var url = btn.getAttribute('data-factura-url');
            if (url) {
                window.open(url, '_blank', 'noopener,noreferrer');
            }
        });
    });
})();
