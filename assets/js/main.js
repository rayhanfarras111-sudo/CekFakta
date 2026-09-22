// Tutup menu mobile saat klik salah satu link
document.querySelectorAll('.nav-links a').forEach(function (link) {
    link.addEventListener('click', function () {
        document.querySelector('.nav-links')?.classList.remove('open');
    });
});

// Nonaktifkan tombol submit setelah diklik supaya user tidak submit ganda
// (proteksi ringan dari spam/double request), khusus form yang diberi class "js-once"
document.querySelectorAll('form.js-once').forEach(function (form) {
    form.addEventListener('submit', function () {
        var btn = form.querySelector('button[type=submit]');
        if (btn) {
            btn.disabled = true;
            btn.dataset.originalText = btn.textContent;
            btn.textContent = 'Memproses...';
        }
    });
});

document.querySelectorAll('[data-copy-url]').forEach(function (button) {
    button.addEventListener('click', function () {
        var url = new URL(button.dataset.copyUrl, window.location.href).href;
        navigator.clipboard.writeText(url).then(function () {
            var original = button.textContent;
            button.textContent = 'Link tersalin';
            window.setTimeout(function () { button.textContent = original; }, 1800);
        });
    });
});
