<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
require_admin();
$errors = [];
$editId = (int) ($_GET['edit'] ?? 0);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_verify()) $errors[] = 'Sesi form kedaluwarsa.';
    $action = $_POST['action'] ?? 'save';
    if ($action === 'delete' && empty($errors)) {
        $delete = db()->prepare('DELETE FROM prebunking_articles WHERE id = :id');
        $delete->execute([':id' => (int) $_POST['id']]);
        flash_set('flash_success', 'Artikel dihapus.');
        header('Location: artikel.php'); exit;
    }
    $title = trim($_POST['title'] ?? ''); $summary = trim($_POST['summary'] ?? ''); $body = trim($_POST['body'] ?? '');
    $imageUrl = trim($_POST['image_url'] ?? ''); $sourceUrl = trim($_POST['source_url'] ?? '');
    $category = trim($_POST['category'] ?? 'Artikel'); $verdict = trim($_POST['verdict'] ?? 'Perlu Konteks');
    if ($title === '' || $summary === '' || $body === '') $errors[] = 'Judul, ringkasan, dan isi wajib diisi.';
    if ($imageUrl !== '' && !filter_var($imageUrl, FILTER_VALIDATE_URL)) $errors[] = 'URL gambar tidak valid.';
    if ($sourceUrl !== '' && !filter_var($sourceUrl, FILTER_VALIDATE_URL)) $errors[] = 'URL sumber tidak valid.';
    if (empty($errors)) {
        if (!empty($_POST['id'])) {
            $stmt = db()->prepare('UPDATE prebunking_articles SET title=:title, summary=:summary, body=:body, image_url=:image, source_url=:source, category=:category, verdict=:verdict WHERE id=:id');
            $stmt->execute([':title'=>$title, ':summary'=>$summary, ':body'=>$body, ':image'=>$imageUrl ?: null, ':source'=>$sourceUrl ?: null, ':category'=>$category, ':verdict'=>$verdict, ':id'=>(int) $_POST['id']]);
        } else {
            $stmt = db()->prepare('INSERT INTO prebunking_articles (title, summary, body, image_url, source_url, category, verdict, created_by) VALUES (:title,:summary,:body,:image,:source,:category,:verdict,:uid)');
            $stmt->execute([':title'=>$title, ':summary'=>$summary, ':body'=>$body, ':image'=>$imageUrl ?: null, ':source'=>$sourceUrl ?: null, ':category'=>$category, ':verdict'=>$verdict, ':uid'=>current_user()['id']]);
        }
        flash_set('flash_success', 'Artikel berhasil disimpan.'); header('Location: artikel.php'); exit;
    }
}
$article = null;
if ($editId) { $stmt=db()->prepare('SELECT * FROM prebunking_articles WHERE id=:id'); $stmt->execute([':id'=>$editId]); $article=$stmt->fetch(); }
$list = db()->query('SELECT id,title,category,verdict,published_at FROM prebunking_articles ORDER BY published_at DESC')->fetchAll();
$pageTitle = 'Kelola Artikel'; require __DIR__ . '/../includes/header.php';
?>
<section class="container" style="padding-top:32px;">
    <div class="admin-hero"><div><p class="admin-kicker">CONTENT CONTROL</p><h1>Kelola Artikel</h1><p>Atur konten prebunking yang tampil di halaman Cek Berita.</p></div></div>
    <?php foreach ($errors as $error): ?><div class="alert alert-error"><?= e($error) ?></div><?php endforeach; ?>
    <div class="card">
        <h2><?= $article ? 'Edit artikel' : 'Tambah artikel' ?></h2>
        <form method="POST" action="artikel.php<?= $article ? '?edit=' . (int) $article['id'] : '' ?>" class="js-once">
            <?= csrf_field() ?><?php if ($article): ?><input type="hidden" name="id" value="<?= (int) $article['id'] ?>"><?php endif; ?>
            <div class="form-group"><label>Judul</label><input type="text" name="title" maxlength="220" required value="<?= e($article['title'] ?? '') ?>"></div>
            <div class="form-group"><label>Ringkasan</label><textarea name="summary" rows="2" required><?= e($article['summary'] ?? '') ?></textarea></div>
            <div class="form-group"><label>Isi artikel</label><textarea name="body" rows="6" required><?= e($article['body'] ?? '') ?></textarea></div>
            <div class="form-group"><label>URL gambar</label><input type="url" name="image_url" value="<?= e($article['image_url'] ?? '') ?>" placeholder="https://..."></div>
            <div class="form-group"><label>URL sumber</label><input type="url" name="source_url" value="<?= e($article['source_url'] ?? '') ?>" placeholder="https://..."></div>
            <div class="filter-bar"><select name="category"><option>Artikel</option><option>Cek Fakta</option><option>Panduan</option></select><select name="verdict"><option>Perlu Konteks</option><option>Terbukti Hoaks</option><option>Informasi Benar</option><option>Menyesatkan</option></select></div>
            <button class="btn btn-admin" type="submit">Simpan artikel</button>
        </form>
    </div>
    <div class="card"><h2>Artikel terbit</h2><table><thead><tr><th>Judul</th><th>Kategori</th><th>Status</th><th>Aksi</th></tr></thead><tbody><?php foreach ($list as $item): ?><tr><td><?= e($item['title']) ?></td><td><?= e($item['category']) ?></td><td><?= e($item['verdict']) ?></td><td><a class="btn btn-sm btn-ghost" href="artikel.php?edit=<?= (int) $item['id'] ?>">Edit</a> <form method="POST" style="display:inline"><input type="hidden" name="id" value="<?= (int) $item['id'] ?>"><input type="hidden" name="action" value="delete"><?= csrf_field() ?><button class="btn btn-sm btn-danger" type="submit">Hapus</button></form></td></tr><?php endforeach; ?></tbody></table></div>
</section>
<?php require __DIR__ . '/../includes/footer.php'; ?>
