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