<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/ai.php';

$errors = [];
$isLoggedIn = is_logged_in();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!$isLoggedIn) {
        $errors[] = 'Fitur cek berita terkunci. Silakan login terlebih dahulu.';
    } elseif (!csrf_verify()) {
        $errors[] = 'Sesi form sudah kedaluwarsa, silakan coba lagi.';
    }

    $userId  = current_user()['id'] ?? null;
    $pesan   = trim($_POST['pesan_user'] ?? '');
    $hasFile = isset($_FILES['screenshot']) && $_FILES['screenshot']['error'] === UPLOAD_ERR_OK;

    if ($pesan === '' && !$hasFile) {
        $errors[] = 'Masukkan teks/tautan berita, atau unggah screenshot terlebih dahulu.';
    }

    if (empty($errors) && is_rate_limited($userId)) {
        $errors[] = 'Kamu sudah mencapai batas ' . MAX_ANALISIS_PER_HARI . ' analisis hari ini. Coba lagi besok ya, ini supaya biaya AI tetap terkendali untuk semua orang.';
    }

    $imageFullPath = null;
    $imageStoredPath = null;
    if (empty($errors) && $hasFile) {
        $ext = strtolower(pathinfo($_FILES['screenshot']['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, ['jpg', 'jpeg', 'png', 'webp'], true)) {
            $errors[] = 'Format screenshot harus JPG, PNG, atau WEBP.';
        } elseif ($_FILES['screenshot']['size'] > 5 * 1024 * 1024) {
            $errors[] = 'Ukuran screenshot maksimal 5MB.';
        } else {
            if (!is_dir(UPLOAD_DIR)) mkdir(UPLOAD_DIR, 0755, true);
            $filename = uniqid('ss_', true) . '.' . $ext;
            $imageFullPath = UPLOAD_DIR . $filename;
            if (move_uploaded_file($_FILES['screenshot']['tmp_name'], $imageFullPath)) {
                $imageStoredPath = UPLOAD_URL . $filename;
            } else {
                $errors[] = 'Gagal menyimpan screenshot, coba lagi.';
            }
        }
    }

    if (empty($errors)) {
        $inputType = $imageStoredPath ? 'image' : (filter_var($pesan, FILTER_VALIDATE_URL) ? 'url' : 'text');
        $normalized = normalize_text($pesan !== '' ? $pesan : 'screenshot-' . ($imageStoredPath ?? ''));

        // Cek cache kemiripan klaim dulu (hemat panggilan AI), hanya untuk input teks/url
        $cached = ($inputType !== 'image') ? find_similar_analysis($normalized) : null;

        if ($cached) {
            $analysisId = $cached['id'];
            flash_set('flash_success', 'Klaim mirip sudah pernah dianalisis sebelumnya, menampilkan hasil yang tersimpan.');
        } else {
            try {
                $result = analyze_with_ai($pesan !== '' ? $pesan : 'Analisis gambar screenshot berikut.', $imageFullPath);

                $stmt = db()->prepare(
                    "INSERT INTO analyses (user_id, input_type, input_content, normalized_text, image_path, credibility_score, label, explanation, sources)
                     VALUES (:uid, :itype, :icontent, :ntext, :ipath, :score, :label, :explanation, :sources)
                     RETURNING id"
                );
                $stmt->execute([
                    ':uid'         => $userId,
                    ':itype'       => $inputType,
                    ':icontent'    => $pesan !== '' ? $pesan : '[Screenshot]',
                    ':ntext'       => $normalized,
                    ':ipath'       => $imageStoredPath,
                    ':score'       => $result['score'],
                    ':label'       => $result['label'],
                    ':explanation' => $result['explanation'],
                    ':sources'     => json_encode($result['sources']),
                ]);
                $analysisId = $stmt->fetch()['id'];

                if (!$userId) {
                    guest_hit_rate_limit();
                }
            } catch (AiAnalysisException $e) {
                $errors[] = $e->getMessage();
            }
        }

        if (empty($errors) && isset($analysisId)) {
            header('Location: hasil.php?id=' . $analysisId);
            exit;
        }
    }
}

$pageTitle = 'Cek Berita';
$articleQuery = trim($_GET['artikel'] ?? '');
$articleCategory = trim($_GET['kategori'] ?? '');
$articleWhere = ['1 = 1'];
$articleParams = [];
if ($articleQuery !== '') {
    $articleWhere[] = '(title ILIKE :article_q OR summary ILIKE :article_q)';
    $articleParams[':article_q'] = '%' . $articleQuery . '%';
}
if ($articleCategory !== '') {
    $articleWhere[] = 'category = :article_category';
    $articleParams[':article_category'] = $articleCategory;
}
$articleStmt = db()->prepare('SELECT id, title, category, image_url, published_at FROM prebunking_articles WHERE ' . implode(' AND ', $articleWhere) . ' ORDER BY published_at DESC LIMIT 6');
$articleStmt->execute($articleParams);
$prebunkingArticles = $articleStmt->fetchAll();
$factCheckLinks = [
    [
        'title' => 'TurnBackHoax',
        'type' => 'Pemeriksa hoaks Indonesia',
        'description' => 'Cari klarifikasi dan debunking terhadap klaim viral yang beredar di media sosial.',
        'url' => 'https://turnbackhoax.id/',
    ],
    [
        'title' => 'Cek Fakta',
        'type' => 'Kolaborasi media Indonesia',
        'description' => 'Baca hasil pemeriksaan fakta dari jaringan media dan organisasi pemeriksa fakta.',
        'url' => 'https://cekfakta.com/',
    ],
    [
        'title' => 'Kompas Cek Fakta',
        'type' => 'Referensi berita terverifikasi',
        'description' => 'Bandingkan klaim dengan artikel pemeriksaan fakta dan sumber berita yang jelas.',
        'url' => 'https://www.kompas.com/cekfakta',
    ],
    [
        'title' => 'AFP Fact Check',
        'type' => 'Pemeriksa fakta internasional',
        'description' => 'Rujukan pemeriksaan klaim global, termasuk foto, video, dan informasi viral.',
        'url' => 'https://factcheck.afp.com/',
    ],
];
require __DIR__ . '/includes/header.php';
?>

<section class="container" style="padding-top:32px;">
    <div class="check-page-heading">
        <div>
            <p class="hero-eyebrow">Pusat pemeriksaan</p>
            <h1>Cek Berita</h1>
            <p class="hero-sub">Uji sebuah klaim sebelum ikut menyebarkannya. CekFakta membandingkan informasi dengan sumber yang tersedia dan menampilkan alasan di balik skornya.</p>
        </div>
        <div class="check-status"><span></span> Sistem siap digunakan</div>
    </div>

    <?php if (!$isLoggedIn): ?>
        <div class="alert alert-error">
            Fitur cek berita sedang terkunci. <a href="login.php">Login</a> untuk membuka input teks, tautan, dan screenshot.
        </div>
    <?php endif; ?>

    <?php foreach ($errors as $err): ?>
        <div class="alert alert-error"><?= e($err) ?></div>
    <?php endforeach; ?>

    <div class="check-workspace">
        <form class="check-form js-once analysis-form" action="cek-berita.php" method="POST" enctype="multipart/form-data" style="margin-top:8px;">
            <?= csrf_field() ?>
            <div class="form-title-row"><div><span class="form-step">01</span><h2>Masukkan klaim</h2></div><span class="input-badge">AI assisted</span></div>
            <label for="pesan_user">Teks atau tautan berita</label>
            <textarea name="pesan_user" id="pesan_user" placeholder="<?= $isLoggedIn ? 'Tulis atau tempel di sini...' : 'Login terlebih dahulu untuk menggunakan fitur ini.' ?>" <?= $isLoggedIn ? '' : 'disabled' ?>><?= e($_POST['pesan_user'] ?? '') ?></textarea>

            <div class="upload-divider"><span>atau gunakan bukti visual</span></div>
            <div class="upload-box" id="upload-box">
                <span class="upload-icon">+</span>
                <div><label for="screenshot">Unggah screenshot berita</label><small>JPG, PNG, WEBP · maksimal 5MB</small></div>
                <input type="file" name="screenshot" id="screenshot" accept=".jpg,.jpeg,.png,.webp" <?= $isLoggedIn ? '' : 'disabled' ?>>
            </div>
            <div class="upload-preview" id="upload-preview" style="display:none;">
                <img id="upload-preview-img" alt="Preview screenshot">
                <div class="upload-preview-info">
                    <strong id="upload-preview-name"></strong>
                    <small id="upload-preview-size"></small>
                </div>
                <button type="button" id="upload-preview-remove" class="upload-preview-remove" aria-label="Hapus screenshot">&times;</button>
            </div>

            <div class="check-form-row">
                <button type="submit" class="btn btn-primary" <?= $isLoggedIn ? '' : 'disabled' ?>>Analisis Sekarang <span aria-hidden="true">&rarr;</span></button>
                <span class="form-note">Hasil bukan keputusan final.</span>
            </div>
            <div class="analysis-loading" aria-live="polite" hidden>
                <span class="loading-orbit" aria-hidden="true"></span>
                <div><strong>Sedang memeriksa klaim...</strong><small>Membandingkan informasi dengan sumber yang tersedia.</small></div>
            </div>
        </form>

        <aside class="check-guide">
            <p class="section-kicker">BACA HASIL DENGAN BIJAK</p>
            <h2>Bagaimana skor bekerja?</h2>
            <p>Skor membantu menunjukkan seberapa kuat dukungan sumber yang ditemukan. Tetap periksa tautan rujukan sebelum mengambil keputusan.</p>
            <div class="guide-item"><span>01</span><div><strong>Bandingkan sumber</strong><small>Jangan bergantung pada satu unggahan.</small></div></div>
            <div class="guide-item"><span>02</span><div><strong>Periksa tanggal</strong><small>Informasi lama sering dibagikan ulang.</small></div></div>
            <div class="guide-item"><span>03</span><div><strong>Baca konteks</strong><small>Judul belum tentu mewakili isi berita.</small></div></div>
            <a href="literasi.php" class="guide-link">Pelajari literasi media &rarr;</a>
        </aside>
    </div>

    <section class="prebunking-section">
        <div class="section-heading">
            <div>
                <p class="section-kicker">PREBUNKING CEKFAKTA</p>
                <h2>Belajar mengenali hoaks sebelum percaya</h2>
            </div>
            <a class="section-more" href="literasi.php">Lihat semua &rarr;</a>
        </div>
        <p class="reference-intro">Kumpulan bacaan singkat untuk mengenali pola informasi palsu dan membandingkan klaim dengan sumber yang sudah diperiksa.</p>
        <form method="GET" action="cek-berita.php" class="article-filter">
            <input type="search" name="artikel" placeholder="Cari artikel..." value="<?= e($articleQuery) ?>">
            <select name="kategori">
                <option value="">Semua kategori</option>
                <?php foreach (['Artikel', 'Cek Fakta', 'Panduan'] as $category): ?><option value="<?= e($category) ?>" <?= $articleCategory === $category ? 'selected' : '' ?>><?= e($category) ?></option><?php endforeach; ?>
            </select>
            <button class="btn btn-ghost btn-sm" type="submit">Cari</button>
        </form>
        <div class="prebunking-grid">
            <?php foreach ($prebunkingArticles as $article): ?>
                <a class="prebunking-card" href="artikel.php?id=<?= (int) $article['id'] ?>">
                    <img src="<?= e($article['image_url']) ?>" alt="Ilustrasi <?= e($article['title']) ?>">
                    <div class="prebunking-card-body">
                        <span class="article-cat"><?= e($article['category']) ?></span>
                        <h3><?= e($article['title']) ?></h3>
                        <time datetime="<?= e(date('Y-m-d', strtotime($article['published_at']))) ?>"><?= e(date('d M Y', strtotime($article['published_at']))) ?></time>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
    </section>

</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
