<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';
$pageTitle = 'Beranda';
require __DIR__ . '/includes/header.php';
?>

<section class="hero">
    <div class="container hero-inner">
        <div>
            <div class="hero-kicker"><span class="hero-pulse"></span> DETEKSI HOAKS DIBANTU AI</div>
            <h1>Berhenti sejenak. <em>Cek dulu</em> sebelum percaya.</h1>
            <p class="hero-sub">Tempel teks atau tautan berita, lalu biarkan CekFakta membandingkan klaim dengan sumber tepercaya dan menjelaskan apa yang perlu kamu perhatikan.</p>

            <form class="check-form js-once" action="cek-berita.php" method="POST">
                <?= csrf_field() ?>
                <div class="hero-form-heading"><label for="pesan_user">Apa yang ingin kamu periksa?</label><span>TEKS / URL</span></div>
                <textarea name="pesan_user" id="pesan_user" placeholder="Contoh: https://contoh-berita.com/artikel atau tulis ringkasan beritanya di sini..." required></textarea>
                <div class="check-form-row">
                    <button type="submit" class="btn btn-primary">Analisis Sekarang <span aria-hidden="true">&rarr;</span></button>
                    <span class="hero-form-note">atau <a href="cek-berita.php">unggah screenshot berita</a></span>
                </div>
            </form>
        </div>

        <div class="quick-links">
            <a href="komunitas.php" class="quick-link-card quick-link-community" style="text-decoration:none;">
                <span class="quick-link-number">01</span><span class="quick-link-icon">&rarr;</span>
                <h3>Diskusi Komunitas</h3>
                <p>Bahas klaim yang bikin penasaran bersama pengguna lain.</p>
                <strong>Masuk ke ruang diskusi</strong>
            </a>
            <a href="literasi.php" class="quick-link-card quick-link-literacy" style="text-decoration:none;">
                <span class="quick-link-number">02</span><span class="quick-link-icon">&nearr;</span>
                <h3>Literasi Media</h3>
                <p>Kenali pola hoaks dan latih kebiasaan cek fakta.</p>
                <strong>Mulai belajar</strong>
            </a>
        </div>
    </div>
</section>

<section class="container">
    <div class="home-section-heading"><div><p class="hero-eyebrow">Sederhana dan transparan</p><h2>Cara kerjanya</h2></div><span>04 langkah untuk lebih kritis</span></div>
    <div class="steps">
        <div class="step">
            <span class="step-num">1</span>
            <div><h3>Kirim berita</h3><p>Tempel teks, tautan, atau unggah screenshot berita yang meragukan.</p></div>
        </div>
        <div class="step">
            <span class="step-num">2</span>
            <div><h3>AI menganalisis</h3><p>Sistem mencari dan membandingkan dengan sumber tepercaya di internet.</p></div>
        </div>
        <div class="step">
            <span class="step-num">3</span>
            <div><h3>Diskusikan hasilnya</h3><p>Bagikan hasil ke komunitas untuk dapat sudut pandang tambahan.</p></div>
        </div>
        <div class="step">
            <span class="step-num">4</span>
            <div><h3>Belajar literasi</h3><p>Pelajari pola hoaks supaya makin sulit kamu tertipu ke depannya.</p></div>
        </div>
    </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
