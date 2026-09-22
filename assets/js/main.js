// Tutup menu mobile saat klik salah satu link
document.querySelectorAll('.nav-links a').forEach(function (link) {
    link.addEventListener('click', function () {
        document.querySelector('.nav-links')?.classList.remove('open');
    });
});

var navToggle = document.querySelector('[data-nav-toggle]');
var navLinks = document.querySelector('.nav-links');
if (navToggle && navLinks) {
    navToggle.addEventListener('click', function () {
        var isOpen = navLinks.classList.toggle('open');
        navToggle.setAttribute('aria-expanded', String(isOpen));
        navToggle.setAttribute('aria-label', isOpen ? 'Tutup menu' : 'Buka menu');
    });
}

var themeToggle = document.querySelector('[data-theme-toggle]');
var savedTheme = localStorage.getItem('cekfakta-theme');
if (savedTheme === 'dark') document.documentElement.classList.add('dark-theme');
if (themeToggle) {
    var syncThemeButton = function () {
        var dark = document.documentElement.classList.contains('dark-theme');
        themeToggle.textContent = dark ? '\u2600' : '\u263D';
        themeToggle.setAttribute('aria-label', dark ? 'Aktifkan mode terang' : 'Aktifkan mode gelap');
    };
    themeToggle.addEventListener('click', function () {
        document.documentElement.classList.toggle('dark-theme');
        localStorage.setItem('cekfakta-theme', document.documentElement.classList.contains('dark-theme') ? 'dark' : 'light');
        syncThemeButton();
    });
    syncThemeButton();
}

document.querySelectorAll('.toast-message').forEach(function (toast) {
    window.setTimeout(function () { toast.classList.add('toast-hide'); }, 4200);
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
        var loading = form.querySelector('.analysis-loading');
        if (loading) loading.hidden = false;
        form.classList.add('is-processing');
        form.setAttribute('aria-busy', 'true');
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

document.querySelectorAll('[data-toggle-password]').forEach(function (button) {
    button.addEventListener('click', function () {
        var input = document.getElementById(button.dataset.togglePassword);
        if (!input) return;

        var isPassword = input.type === 'password';
        input.type = isPassword ? 'text' : 'password';
        button.textContent = isPassword ? 'Sembunyikan' : 'Lihat';
        button.setAttribute('aria-label', isPassword ? 'Sembunyikan password' : 'Tampilkan password');
    });
});

var historyStart = document.getElementById('history-start');
var historyEnd = document.getElementById('history-end');
if (historyStart && historyEnd) {
    var syncHistoryDateRange = function () {
        historyEnd.min = historyStart.value;
        if (historyStart.value && historyEnd.value && historyEnd.value < historyStart.value) {
            historyEnd.value = historyStart.value;
        }
    };

    var revealSelectors = [
        'main > .container > *:not(.topic-list)',
        '.hero > *',
        '.steps > *',
        '.quick-links > *',
        '.article-grid > *',
        '.prebunking-grid > *',
        '.topic-list .topic-item',
        '.stat-grid > *',
        '.notification-list > *'
    ];
    var revealItems = document.querySelectorAll(revealSelectors.join(','));
    var reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    revealItems.forEach(function (item) {
        item.classList.add('scroll-reveal');
    });

    if (reducedMotion || !('IntersectionObserver' in window)) {
        revealItems.forEach(function (item) { item.classList.add('is-visible'); });
    } else {
        var revealObserver = new IntersectionObserver(function (entries, observer) {
            entries.forEach(function (entry) {
                if (!entry.isIntersecting) return;
                entry.target.classList.add('is-visible');
                observer.unobserve(entry.target);
            });
        }, { threshold: 0.12, rootMargin: '0px 0px -8% 0px' });

        revealItems.forEach(function (item) { revealObserver.observe(item); });
    }

    historyStart.addEventListener('change', syncHistoryDateRange);
    syncHistoryDateRange();
}

// Tampilkan nama file + thumbnail preview begitu screenshot dipilih
var screenshotInput = document.getElementById('screenshot');
if (screenshotInput) {
    var preview     = document.getElementById('upload-preview');
    var previewImg  = document.getElementById('upload-preview-img');
    var previewName = document.getElementById('upload-preview-name');
    var previewSize = document.getElementById('upload-preview-size');
    var uploadBox   = document.getElementById('upload-box');
    var removeBtn   = document.getElementById('upload-preview-remove');

    screenshotInput.addEventListener('change', function () {
        var file = screenshotInput.files[0];
        if (!file) return;

        previewName.textContent = file.name;
        previewSize.textContent = (file.size / 1024 / 1024).toFixed(2) + ' MB';

        var reader = new FileReader();
        reader.onload = function (e) { previewImg.src = e.target.result; };
        reader.readAsDataURL(file);

        preview.style.display = 'flex';
        uploadBox.style.display = 'none';
    });

    removeBtn.addEventListener('click', function () {
        screenshotInput.value = '';
        preview.style.display = 'none';
        uploadBox.style.display = 'flex';
    });
}