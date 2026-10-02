document.addEventListener('DOMContentLoaded', function () {
    // Sidebar toggle for mobile
    var toggle = document.getElementById('menuToggle');
    var sidebar = document.getElementById('sidebar');
    if (toggle && sidebar) {
        toggle.addEventListener('click', function () {
            sidebar.classList.toggle('open');
        });
        document.addEventListener('click', function (e) {
            if (sidebar.classList.contains('open') && !sidebar.contains(e.target) && e.target !== toggle && !toggle.contains(e.target)) {
                sidebar.classList.remove('open');
            }
        });
    }

    // Dismiss alerts
    document.querySelectorAll('[data-dismiss]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var alert = btn.closest('.alert');
            if (alert) alert.remove();
        });
    });

    // Auto-hide success/info alerts after 5s
    window.setTimeout(function () {
        document.querySelectorAll('.alert-success, .alert-info').forEach(function (el) {
            el.style.transition = 'opacity .5s';
            el.style.opacity = '0';
            setTimeout(function () { el.remove(); }, 600);
        });
    }, 5000);

    // Global confirm for data-confirm buttons/forms
    document.querySelectorAll('form[data-confirm]').forEach(function (form) {
        form.addEventListener('submit', function (e) {
            if (!window.confirm(form.getAttribute('data-confirm') || 'Are you sure?')) {
                e.preventDefault();
            }
        });
    });

    // Print buttons trigger window.print
    document.querySelectorAll('[data-print]').forEach(function (btn) {
        btn.addEventListener('click', function () { window.print(); });
    });
});

// Simple QR scanner using getUserMedia frame analysis is intentionally an
// input-and-lookup flow: type or paste the scanned asset code into /scan.
function navToScan(value) {
    var base = window.APP && window.APP.baseUrl || '';
    window.location.href = base + '/qr/' + encodeURIComponent(value.trim());
}