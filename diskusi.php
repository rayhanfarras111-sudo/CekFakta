<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

$id = (int) ($_GET['id'] ?? 0);
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['vote'])) {
    require_login();
    if (!csrf_verify()) {
        $errors[] = 'Sesi form kedaluwarsa, coba lagi.';
    }
    if (empty($errors)) {
        $voteStmt = db()->prepare(
            'INSERT INTO discussion_votes (topic_id, user_id) VALUES (:tid, :uid) ON CONFLICT (topic_id, user_id) DO NOTHING'
        );
        $voteStmt->execute([':tid' => $id, ':uid' => current_user()['id']]);
        if ($voteStmt->rowCount() > 0) {
            $ownerStmt = db()->prepare('SELECT user_id FROM discussion_topics WHERE id = :id');
            $ownerStmt->execute([':id' => $id]);
            $ownerId = $ownerStmt->fetchColumn();
            if ($ownerId && (int) $ownerId !== (int) current_user()['id']) {
                $notice = db()->prepare('INSERT INTO notifications (user_id, type, message, link) VALUES (:uid, :type, :message, :link)');
                $notice->execute([':uid' => $ownerId, ':type' => 'upvote', ':message' => current_user()['username'] . ' memberi upvote pada topikmu.', ':link' => 'diskusi.php?id=' . $id]);
            }
        }
        header('Location: diskusi.php?id=' . $id);
        exit;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_login();
    if (!csrf_verify()) {
        $errors[] = 'Sesi form kedaluwarsa, coba lagi.';
    }
    $content = trim($_POST['content'] ?? '');
    if ($content === '') {
        $errors[] = 'Komentar tidak boleh kosong.';
    }
    if (empty($errors)) {
        $stmt = db()->prepare("INSERT INTO comments (topic_id, user_id, content) VALUES (:tid, :uid, :content)");
        $stmt->execute([':tid' => $id, ':uid' => current_user()['id'], ':content' => $content]);
        $ownerStmt = db()->prepare('SELECT user_id FROM discussion_topics WHERE id = :id');
        $ownerStmt->execute([':id' => $id]);
        $ownerId = $ownerStmt->fetchColumn();
        if ($ownerId && (int) $ownerId !== (int) current_user()['id']) {
            $notice = db()->prepare('INSERT INTO notifications (user_id, type, message, link) VALUES (:uid, :type, :message, :link)');
            $notice->execute([':uid' => $ownerId, ':type' => 'comment', ':message' => current_user()['username'] . ' mengomentari topikmu.', ':link' => 'diskusi.php?id=' . $id . '#komentar-terbaru']);
        }
        header('Location: diskusi.php?id=' . $id . '#komentar-terbaru');
        exit;
    }
}

$stmt = db()->prepare(
    "SELECT t.*, u.username,
        (SELECT COUNT(*) FROM discussion_votes v WHERE v.topic_id = t.id) AS vote_count,
        (SELECT 1 FROM discussion_votes v WHERE v.topic_id = t.id AND v.user_id = :vote_uid) AS user_voted
     FROM discussion_topics t
     LEFT JOIN users u ON u.id = t.user_id
     WHERE t.id = :id AND t.is_hidden = FALSE"
);
$stmt->execute([':id' => $id, ':vote_uid' => current_user()['id'] ?? null]);
$topic = $stmt->fetch();

if (!$topic) {
    http_response_code(404);
    $pageTitle = 'Tidak Ditemukan';
    require __DIR__ . '/includes/header.php';
    echo '<div class="container"><div class="empty-state"><h2>Topik tidak ditemukan</h2><p><a href="komunitas.php">Kembali ke Komunitas</a></p></div></div>';
    require __DIR__ . '/includes/footer.php';
    exit;
}

$cStmt = db()->prepare(
    "SELECT c.*, u.username FROM comments c LEFT JOIN users u ON u.id = c.user_id WHERE c.topic_id = :id AND c.is_hidden = FALSE ORDER BY c.created_at ASC"
);
$cStmt->execute([':id' => $id]);
$comments = $cStmt->fetchAll();

$pageTitle = $topic['title'];
require __DIR__ . '/includes/header.php';
?>

<section class="container" style="padding-top:32px; max-width:760px;">
    <a href="komunitas.php" style="font-size:0.88rem;">&larr; Kembali ke Komunitas</a>

    <div class="card" style="margin-top:12px;">
        <?php if ($topic['label_badge']): ?><span class="badge <?= label_badge_class($topic['label_badge']) ?>"><?= e($topic['label_badge']) ?></span><?php endif; ?>
        <h1><?= e($topic['title']) ?></h1>
        <p class="topic-meta" style="margin-bottom:14px;">
            <span class="avatar"><?= strtoupper(mb_substr($topic['username'] ?? 'P', 0, 1)) ?></span>
            <?= e($topic['username'] ?? 'Pengguna') ?> &middot; <?= time_ago($topic['created_at']) ?>
        </p>
        <p><?= nl2br(e($topic['body'])) ?></p>
        <?php if ($topic['analysis_id']): ?>
            <a href="hasil.php?id=<?= (int) $topic['analysis_id'] ?>" class="btn btn-ghost btn-sm">Lihat hasil analisis terkait</a>
        <?php endif; ?>
        <?php if (is_logged_in()): ?>
            <form method="POST" action="diskusi.php?id=<?= $id ?>" style="display:inline-block; margin-top:10px;">
                <?= csrf_field() ?>
                <input type="hidden" name="vote" value="1">
                <button type="submit" class="btn btn-ghost btn-sm" <?= $topic['user_voted'] ? 'disabled' : '' ?>>
                    <?= $topic['user_voted'] ? '&#10003; Sudah di-upvote' : '&#9650; Upvote' ?> (<?= (int) $topic['vote_count'] ?>)
                </button>
            </form>
        <?php else: ?>
            <a href="login.php" class="btn btn-ghost btn-sm" style="margin-top:10px;">Login untuk upvote (<?= (int) $topic['vote_count'] ?>)</a>
        <?php endif; ?>
    </div>

    <div class="card" style="margin-top:16px;">
        <h3><?= count($comments) ?> Komentar</h3>

        <?php foreach ($errors as $err): ?><div class="alert alert-error"><?= e($err) ?></div><?php endforeach; ?>

        <?php if (empty($comments)): ?>
            <p style="color:var(--ink-soft);">Belum ada komentar. Jadilah yang pertama menanggapi.</p>
        <?php else: ?>
            <?php foreach ($comments as $c): ?>
                <div class="comment" id="komentar-terbaru">
                    <div class="comment-meta">
                        <span class="avatar"><?= strtoupper(mb_substr($c['username'] ?? 'P', 0, 1)) ?></span>
                        <strong><?= e($c['username'] ?? 'Pengguna') ?></strong> &middot; <?= time_ago($c['created_at']) ?>
                    </div>
                    <p style="margin:0 0 0 40px;"><?= nl2br(e($c['content'])) ?></p>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>

        <?php if (is_logged_in()): ?>
            <form method="POST" action="diskusi.php?id=<?= $id ?>" class="js-once" style="margin-top:18px;">
                <?= csrf_field() ?>
                <div class="form-group">
                    <label for="content">Tambahkan komentar</label>
                    <textarea name="content" id="content" rows="3" required></textarea>
                </div>
                <button type="submit" class="btn btn-primary">Kirim Komentar</button>
            </form>
        <?php else: ?>
            <p style="margin-top:16px;"><a href="login.php">Login</a> untuk ikut berkomentar.</p>
        <?php endif; ?>
    </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
