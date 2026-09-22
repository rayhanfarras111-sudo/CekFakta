<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

require_admin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !csrf_verify()) {
    header('Location: dashboard.php');
    exit;
}

$action = $_POST['action'] ?? '';

switch ($action) {
    case 'hide_topic':
        $stmt = db()->prepare('UPDATE discussion_topics SET is_hidden = TRUE WHERE id = :id');
        $stmt->execute([':id' => (int) $_POST['topic_id']]);
        flash_set('flash_success', 'Topik disembunyikan.');
        break;

    case 'unhide_topic':
        $stmt = db()->prepare('UPDATE discussion_topics SET is_hidden = FALSE WHERE id = :id');
        $stmt->execute([':id' => (int) $_POST['topic_id']]);
        flash_set('flash_success', 'Topik ditampilkan kembali.');
        break;

    case 'hide_comment':
        $stmt = db()->prepare('UPDATE comments SET is_hidden = TRUE WHERE id = :id');
        $stmt->execute([':id' => (int) $_POST['comment_id']]);
        flash_set('flash_success', 'Komentar disembunyikan.');
        break;

    case 'resolve_report':
        $stmt = db()->prepare("UPDATE reports SET status = 'reviewed' WHERE id = :id");
        $stmt->execute([':id' => (int) $_POST['report_id']]);
        flash_set('flash_success', 'Laporan ditandai selesai.');
        break;

    default:
        flash_set('flash_error', 'Aksi tidak dikenali.');
}

header('Location: dashboard.php');
exit;
