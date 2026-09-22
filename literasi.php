<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

$articles = [
    ['cat' => 'Dasar', 'title' => 'Judul Bombastis, Waspada', 'summary' => 'Kenapa judul yang terlalu emosional atau clickbait sering jadi tanda pertama berita tidak akurat.', 'read' => '4 menit',
        'content' => "Judul yang penuh tanda seru, huruf kapital semua, atau kalimat yang sengaja bikin penasaran/marah adalah salah satu ciri paling umum dari konten yang tidak akurat. Media resmi biasanya menulis judul secara faktual dan proporsional dengan isi beritanya.\n\nKalau kamu menemukan judul seperti \"HEBOH!!! Ternyata Selama Ini Kita Dibohongi!\" tanpa nama sumber atau tanggal jelas, itu sinyal untuk berhenti sejenak. Baca dulu isi lengkapnya sebelum bereaksi atau membagikan.\n\nTrik sederhana: coba baca ulang judul itu, dan tanyakan pada diri sendiri, apakah judul ini menjelaskan fakta, atau cuma memancing emosi? Kalau condong ke emosi, cek dulu ke sumber lain sebelum percaya."],
    ['cat' => 'Dasar', 'title' => 'Cek Sumber Sebelum Percaya', 'summary' => 'Cara cepat menelusuri siapa penulis dan penerbit sebuah berita sebelum membagikannya.', 'read' => '5 menit',
        'content' => "Sebelum percaya sebuah berita, cek dulu tiga hal: siapa penulisnya, media apa yang menerbitkan, dan kapan diterbitkan. Media kredibel biasanya mencantumkan nama penulis, punya halaman \"Tentang Kami\" yang jelas, dan alamat redaksi yang bisa ditelusuri.\n\nKalau sebuah \"berita\" cuma berupa gambar/teks tanpa nama situs sama sekali (misalnya cuma di-forward lewat WhatsApp), itu bukan berita, itu klaim yang belum terverifikasi.\n\nCoba juga googling nama medianya. Kalau nggak ada media lain yang mengutip atau membahas hal yang sama, apalagi kalau kejadiannya besar, itu patut dicurigai."],
    ['cat' => 'Dasar', 'title' => 'Beda Fakta, Opini, dan Propaganda', 'summary' => 'Belajar membedakan tiga jenis informasi yang sering tertukar di media.', 'read' => '6 menit',
        'content' => "Fakta adalah sesuatu yang bisa dibuktikan dan diverifikasi, misalnya data resmi atau kejadian yang direkam banyak saksi. Opini adalah pandangan atau penilaian seseorang atas suatu fakta, boleh berbeda-beda tapi tidak bisa disebut benar/salah secara mutlak.\n\nPropaganda lebih berbahaya: ini adalah penyajian informasi (kadang fakta yang dipelintir, kadang murni bohong) dengan tujuan menggiring opini publik ke arah tertentu, biasanya demi kepentingan politik atau ekonomi.\n\nCara membedakannya: fakta biasanya netral dan bisa dicek ulang, opini biasanya ditandai kata seperti \"menurut saya\" atau \"seharusnya\", sementara propaganda sering pakai bahasa yang sangat emosional dan satu sisi saja, tanpa ruang untuk sudut pandang lain."],
    ['cat' => 'Media Sosial', 'title' => 'Mengenali Akun Bot & Buzzer', 'summary' => 'Tanda-tanda akun yang sengaja dibuat untuk menyebarkan narasi tertentu secara masif.', 'read' => '5 menit',
        'content' => "Akun bot/buzzer biasanya punya beberapa ciri: dibuat dalam waktu berdekatan dengan akun-akun sejenis lainnya, foto profil generik atau curian, jumlah following jauh lebih banyak dari follower, dan isi postingannya berulang-ulang membahas satu topik yang sama persis dengan gaya bahasa mirip.\n\nKalau kamu lihat banyak akun berbeda tapi menulis kalimat yang nyaris identik dalam waktu bersamaan, itu ciri khas operasi buzzer terkoordinasi, bukan opini publik yang organik.\n\nJangan langsung percaya sesuatu cuma karena \"rame\" di media sosial. Keramaian bisa direkayasa."],
    ['cat' => 'Media Sosial', 'title' => 'Efek Ruang Gema (Echo Chamber)', 'summary' => 'Kenapa linimasa kita cenderung memperkuat apa yang sudah kita percaya.', 'read' => '4 menit',
        'content' => "Algoritma media sosial dirancang untuk menampilkan konten yang paling mungkin membuat kita terus scroll, biasanya itu adalah konten yang sesuai dengan apa yang sudah kita sukai atau percayai sebelumnya. Efeknya, kita jadi jarang lihat sudut pandang berbeda, dan lama-lama merasa pendapat kita adalah \"pendapat mayoritas\" padahal belum tentu.\n\nIni disebut ruang gema (echo chamber): pendapat kita terus dipantulkan balik ke kita sendiri, membuatnya terasa makin benar padahal cuma berputar di lingkaran yang sama.\n\nCara keluar dari jebakan ini: sesekali aktif cari sumber berita dari media dengan sudut pandang berbeda, dan biasakan bertanya \"apakah saya percaya ini karena buktinya kuat, atau karena saya sering lihat ini?\""],
    ['cat' => 'Media Sosial', 'title' => 'Sebelum Repost, Tarik Napas Dulu', 'summary' => 'Kebiasaan kecil yang bisa mencegah kamu ikut menyebarkan hoaks tanpa sadar.', 'read' => '3 menit',
        'content' => "Kebanyakan orang menyebarkan hoaks bukan karena jahat, tapi karena buru-buru. Begitu baca sesuatu yang bikin kaget/marah/takut, jari langsung reflek pencet tombol Share sebelum benar-benar membaca sampai selesai, apalagi sebelum mengecek kebenarannya.\n\nBiasakan aturan sederhana: tunggu minimal beberapa menit sebelum membagikan berita yang bikin emosi kamu naik. Waktu jeda itu memberi ruang buat berpikir lebih jernih.\n\nKalau ragu, gunakan fitur Cek Berita di CekFakta dulu sebelum forward ke grup keluarga atau teman."],
    ['cat' => 'AI & Deepfake', 'title' => 'Mengenali Foto/Video Hasil AI', 'summary' => 'Detail kecil (tangan, bayangan, tekstur) yang sering jadi celah gambar buatan AI.', 'read' => '6 menit',
        'content' => "Gambar buatan AI sering punya kejanggalan di detail kecil: jari tangan yang jumlahnya salah atau bentuknya aneh, pola pada kain/tekstil yang tidak masuk akal kalau diperhatikan dekat, bayangan yang arahnya tidak konsisten dengan sumber cahaya, atau teks di latar belakang (misalnya di baju atau papan) yang jadi huruf acak tidak bermakna.\n\nUntuk video, perhatikan gerakan bibir yang tidak pas dengan suara, kedipan mata yang terlalu jarang/tidak natural, atau tekstur kulit yang terlalu halus/mulus secara tidak wajar.\n\nSemakin ke sini, teknologi AI makin canggih dan kejanggalan ini makin sulit dilihat mata telanjang, jadi kombinasikan juga dengan cek sumber dan konteks, bukan cuma mengandalkan mata."],
    ['cat' => 'AI & Deepfake', 'title' => 'Suara Deepfake Makin Mirip Asli', 'summary' => 'Kenapa memverifikasi audio kini sama pentingnya dengan memverifikasi teks dan gambar.', 'read' => '5 menit',
        'content' => "Teknologi kloning suara sekarang cuma butuh beberapa detik rekaman asli seseorang untuk bisa meniru suaranya dengan sangat mirip, termasuk nada bicara dan aksen. Ini sudah dipakai untuk penipuan, misalnya suara palsu \"anak\" atau \"atasan\" yang meminta transfer uang mendesak.\n\nTanda yang perlu diwaspadai: permintaan yang sangat mendesak dan melibatkan uang/data sensitif, kualitas audio yang sedikit tidak natural (nada datar, jeda aneh), atau permintaan yang tidak biasa dari orang tersebut.\n\nKalau ragu, selalu verifikasi lewat jalur lain, misalnya telepon balik ke nomor yang sudah kamu simpan sebelumnya, bukan nomor yang menghubungi kamu."],
    ['cat' => 'AI & Deepfake', 'title' => 'Kenapa AI Bisa Salah Juga', 'summary' => 'Batasan alat deteksi berbasis AI (termasuk CekFakta) dan kenapa verifikasi manusia tetap penting.', 'read' => '5 menit',
        'content' => "Alat deteksi hoaks berbasis AI, termasuk yang dipakai di CekFakta, bekerja dengan mencari dan membandingkan informasi dari berbagai sumber di internet. Tapi AI tetap punya keterbatasan: ia bisa salah menafsirkan konteks, sumber yang ditemukannya bisa saja juga keliru, atau untuk topik yang sangat baru, belum ada cukup informasi di internet untuk dibandingkan.\n\nItu kenapa hasil analisis AI sebaiknya dianggap sebagai titik awal, bukan vonis final. Selalu cek juga sumber yang direkomendasikan, dan gunakan penilaian kamu sendiri.\n\nDi CekFakta, kamu juga bisa melaporkan kalau merasa hasil analisisnya kurang tepat, supaya tim bisa meninjau ulang."],
];

$filter = trim($_GET['kategori'] ?? '');
$filtered = $filter === '' ? $articles : array_filter($articles, fn($a) => $a['cat'] === $filter);

$pageTitle = 'Literasi Media';
require __DIR__ . '/includes/header.php';
?>

<section class="container" style="padding-top:32px;">
    <div class="literacy-hero">
        <div>
            <p class="section-kicker">RUANG BELAJAR CEKFAKTA</p>
            <h1>Literasi Media</h1>
            <p class="hero-sub">Kenali ciri-ciri hoaks dan latih kebiasaan berpikir kritis sebelum mempercayai atau membagikan informasi.</p>
        </div>
        <div class="literacy-mark"><span>CF</span><small>Think<br>twice.</small></div>
    </div>

    <div class="literacy-check-card">
        <div class="literacy-card-heading"><div><p class="hero-eyebrow">Checklist cepat</p><h2>Ciri-ciri berita hoaks</h2></div><span class="check-count">06 tanda</span></div>
        <ul class="checklist">
            <li>Judul terlalu provokatif/emosional dibanding isinya</li>
            <li>Tidak menyebutkan sumber, penulis, atau tanggal yang jelas</li>
            <li>Meminta kamu membagikan sebelum "keburu dihapus"</li>
            <li>Foto/video terlihat janggal atau diambil dari konteks berbeda</li>
            <li>Isinya menimbulkan kemarahan atau ketakutan berlebih</li>
            <li>Tidak ditemukan di media resmi manapun saat dicari</li>
        </ul>
    </div>

    <div class="literacy-articles-heading"><div><p class="hero-eyebrow">Bacaan pilihan</p><h2>Artikel Panduan</h2></div><span class="dashboard-list-meta"><?= count($filtered) ?> artikel tersedia</span></div>
    <div class="literacy-filter">
        <a href="literasi.php" class="btn btn-sm <?= $filter === '' ? 'btn-primary' : 'btn-ghost' ?>">Semua</a>
        <a href="literasi.php?kategori=Dasar" class="btn btn-sm <?= $filter === 'Dasar' ? 'btn-primary' : 'btn-ghost' ?>">Dasar</a>
        <a href="literasi.php?kategori=Media+Sosial" class="btn btn-sm <?= $filter === 'Media Sosial' ? 'btn-primary' : 'btn-ghost' ?>">Media Sosial</a>
        <a href="literasi.php?kategori=AI+%26+Deepfake" class="btn btn-sm <?= $filter === 'AI & Deepfake' ? 'btn-primary' : 'btn-ghost' ?>">AI & Deepfake</a>
    </div>

    <div class="article-grid literacy-grid">
        <?php foreach ($filtered as $a): ?>
            <details class="article-card">
                <summary>
                    <span class="article-cat"><?= e($a['cat']) ?></span>
                    <h3><?= e($a['title']) ?></h3>
                    <p><?= e($a['summary']) ?></p>
                    <p class="meta">&#9200; <?= e($a['read']) ?> baca &middot; <span class="expand-hint">klik untuk baca lengkap</span></p>
                </summary>
                <div class="article-full">
                    <?= nl2br(e($a['content'])) ?>
                </div>
            </details>
        <?php endforeach; ?>
    </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>