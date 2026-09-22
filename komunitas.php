<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

$errors = [];

// ---- Handle pembuatan topik baru ----
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['title'])) {
    require_login();
    if (!csrf_verify()) {
        $errors[] = 'Sesi form kedaluwarsa, coba lagi.';
    }
    $title = trim($_POST['title'] ?? '');
    $body  = trim($_POST['body'] ?? '');
    $analysisId = !empty($_POST['analysis_id']) ? (int) $_POST['analysis_id'] : null;

    if ($title === '' || $body === '') {
        $errors[] = 'Judul dan isi diskusi wajib diisi.';
    }

    if (empty($errors)) {
        $labelBadge = null;
        if ($analysisId) {
            $a = db()->prepare("SELECT label FROM analyses WHERE id = :id");
            $a->execute([':id' => $analysisId]);
            $row = $a->fetch();
            $labelBadge = $row['label'] ?? null;
        }

        $stmt = db()->prepare(
            "INSERT INTO discussion_topics (user_id, analysis_id, title, body, label_badge)
             VALUES (:uid, :aid, :title, :body, :badge) RETURNING id"
        );
        $stmt->execute([
            ':uid'   => current_user()['id'],
            ':aid'   => $analysisId,
            ':title' => $title,
            ':body'  => $body,
            ':badge' => $labelBadge,
        ]);
        $newId = $stmt->fetch()['id'];
        flash_set('flash_success', 'Topik diskusi berhasil dibuat.');
        header('Location: diskusi.php?id=' . $newId);
        exit;
    }
}

// ---- Filter & pencarian ----
$q     = trim($_GET['q'] ?? '');
$label = trim($_GET['label'] ?? '');
$page  = max(1, (int) ($_GET['page'] ?? 1));
$perPage = 10;
$prefillAnalysisId = !empty($_GET['dari_analisis']) ? (int) $_GET['dari_analisis'] : null;

$where  = ['t.is_hidden = FALSE'];
$params = [];
if ($q !== '') {
    $where[] = '(t.title ILIKE :q OR t.body ILIKE :q)';
    $params[':q'] = '%' . $q . '%';
}
if ($label !== '') {
    $where[] = 't.label_badge = :label';
    $params[':label'] = $label;
}
$whereSql = implode(' AND ', $where);

$countStmt = db()->prepare("SELECT COUNT(*) FROM discussion_topics t WHERE {$whereSql}");
$countStmt->execute($params);
$totalTopics = (int) $countStmt->fetchColumn();
$totalPages = max(1, (int) ceil($totalTopics / $perPage));
$page = min($page, $totalPages);
$offset = ($page - 1) * $perPage;
$queryParams = $params;
$queryParams[':limit'] = $perPage;
$queryParams[':offset'] = $offset;

$stmt = db()->prepare(
    "SELECT t.*, u.username,
          (SELECT COUNT(*) FROM comments c WHERE c.topic_id = t.id AND c.is_hidden = FALSE) AS comment_count,
          (SELECT COUNT(*) FROM discussion_votes v WHERE v.topic_id = t.id) AS vote_count
     FROM discussion_topics t
     LEFT JOIN users u ON u.id = t.user_id
     WHERE {$whereSql}
     ORDER BY t.created_at DESC
    LIMIT :limit OFFSET :offset"
);
$stmt->execute($queryParams);
$topics = $stmt->fetchAll();

$pageTitle = 'Diskusi Komunitas';
require __DIR__ . '/includes/header.php';
?>

<section class="container" style="padding-top:32px;">
    <div style="display:flex; justify-content:space-between; align-items:center; gap:12px; flex-wrap:wrap;">
        <h1 style="margin:0;">Diskusi Komunitas</h1>
        <button class="btn btn-primary" onclick="document.getElementById('form-topik-baru').style.display='block'; this.style.display='none';">Mulai Diskusi</button>
    </div>
    <p class="hero-sub">Bahas klaim yang kamu temui, baik yang berasal dari hasil cek AI maupun topik mandiri.</p>

    <?php foreach ($errors as $err): ?><div class="alert alert-error"><?= e($err) ?></div><?php endforeach; ?>

    <div id="form-topik-baru" class="card" style="<?= $prefillAnalysisId ? '' : 'display:none;' ?> margin-bottom:20px;">
        <?php if (is_logged_in()): ?>
        <h3>Topik diskusi baru</h3>
        <form method="POST" action="komunitas.php" class="js-once">
            <?= csrf_field() ?>
            <?php if ($prefillAnalysisId): ?>
                <input type="hidden" name="analysis_id" value="<?= $prefillAnalysisId ?>">
                <p style="font-size:0.85rem; color:var(--ink-soft);">Topik ini akan ditautkan ke <a href="hasil.php?id=<?= $prefillAnalysisId ?>" target="_blank">hasil analisis #<?= $prefillAnalysisId ?></a>.</p>
            <?php endif; ?>
            <div class="form-group">
                <label for="title">Judul</label>
                <input type="text" name="title" id="title" required maxlength="200">
            </div>
            <div class="form-group">
                <label for="body">Isi diskusi</label>
                <textarea name="body" id="body" rows="4" required></textarea>
            </div>
            <button type="submit" class="btn btn-primary">Posting Topik</button>
        </form>
        <?php else: ?>
            <p>Kamu perlu <a href="login.php">login</a> dulu untuk memulai diskusi.</p>
        <?php endif; ?>
    </div>

    <form method="GET" action="komunitas.php" class="filter-bar">
        <input type="search" name="q" placeholder="Cari topik..." value="<?= e($q) ?>">
        <select name="label" onchange="this.form.submit()">
            <option value="">Semua label</option>
            <?php foreach (['Kemungkinan Fakta', 'Kemungkinan Hoaks', 'Perlu Konteks', 'Belum Dapat Diverifikasi'] as $l): ?>
                <option value="<?= e($l) ?>" <?= $label === $l ? 'selected' : '' ?>><?= e($l) ?></option>
            <?php endforeach; ?>
        </select>
        <button type="submit" class="btn btn-ghost">Cari</button>
    </form>

    <?php if (empty($topics)): ?>
        <div class="empty-state">
            <h3>Belum ada diskusi yang cocok</h3>
            <p>Jadilah yang pertama memulai diskusi tentang topik ini.</p>
        </div>
    <?php else: ?>
        <ul class="topic-list">
            <?php foreach ($topics as $t): ?>
                <li>
                    <a href="diskusi.php?id=<?= (int) $t['id'] ?>" class="topic-item">
                        <?php if ($t['label_badge']): ?><span class="badge <?= label_badge_class($t['label_badge']) ?>"><?= e($t['label_badge']) ?></span><?php endif; ?>
                        <h3><?= e($t['title']) ?></h3>
                        <p style="color:var(--ink-soft); margin:0;"><?= e(mb_substr($t['body'], 0, 140)) ?><?= mb_strlen($t['body']) > 140 ? '…' : '' ?></p>
                        <div class="topic-meta">
                            <span><?= e($t['username'] ?? 'Pengguna') ?></span>
                            <span>&middot;</span>
                            <span><?= time_ago($t['created_at']) ?></span>
                            <span>&middot;</span>
                            <span><?= (int) $t['comment_count'] ?> komentar</span>
                            <span>&middot;</span>
                            <span><?= (int) $t['vote_count'] ?> upvote</span>
                        </div>
                    </a>
                </li>
            <?php endforeach; ?>
        </ul>
            <?php if ($totalPages > 1): ?>
                <nav class="pagination" aria-label="Halaman komunitas">
                    <?php for ($p = 1; $p <= $totalPages; $p++): ?>
                        <a class="<?= $p === $page ? 'active' : '' ?>" href="?q=<?= urlencode($q) ?>&label=<?= urlencode($label) ?>&page=<?= $p ?>"><?= $p ?></a>
                    <?php endfor; ?>
                </nav>
            <?php endif; ?>
    <?php endif; ?>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
