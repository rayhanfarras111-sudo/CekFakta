<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

require_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !csrf_verify()) {
    header('Location: index.php');
    exit;
}

$analysisId = (int) ($_POST['analysis_id'] ?? 0);
$reason     = trim($_POST['reason'] ?? '');

if ($analysisId && $reason !== '') {
    $stmt = db()->prepare("INSERT INTO reports (analysis_id, user_id, reason) VALUES (:aid, :uid, :reason)");
    $stmt->execute([
        ':aid'    => $analysisId,
        ':uid'    => current_user()['id'],
        ':reason' => $reason,
    ]);
    flash_set('flash_success', 'Terima kasih, laporan kamu sudah diteruskan ke tim moderasi.');
} else {
    flash_set('flash_error', 'Alasan laporan wajib diisi.');
}

header('Location: hasil.php?id=' . $analysisId);
exit;
