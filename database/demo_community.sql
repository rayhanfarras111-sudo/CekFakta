-- Data demo komunitas CekFakta.
-- Jalankan setelah database/schema.sql. Aman dijalankan ulang.

INSERT INTO users (username, email, password, role)
VALUES
    ('Sari', 'demo.sari@cekfakta.test', '$2b$10$TSlOQSa8u5JAHpilp5/a1.KeFnF5GTMRbKurVPqCyS8ywLYCePx5K', 'user'),
    ('Bima', 'demo.bima@cekfakta.test', '$2b$10$TSlOQSa8u5JAHpilp5/a1.KeFnF5GTMRbKurVPqCyS8ywLYCePx5K', 'user'),
    ('Nadia', 'demo.nadia@cekfakta.test', '$2b$10$TSlOQSa8u5JAHpilp5/a1.KeFnF5GTMRbKurVPqCyS8ywLYCePx5K', 'user'),
    ('Dimas', 'demo.dimas@cekfakta.test', '$2b$10$TSlOQSa8u5JAHpilp5/a1.KeFnF5GTMRbKurVPqCyS8ywLYCePx5K', 'user')
ON CONFLICT (email) DO NOTHING;

INSERT INTO discussion_topics (user_id, title, body, label_badge)
SELECT u.id, demo.title, demo.body, demo.label_badge
FROM users u
CROSS JOIN (
    VALUES
        ('Waspada Pesan Berantai tentang Kesehatan', 'Beredar pesan yang menyebut minum air hangat dapat menyembuhkan semua penyakit. Bagaimana cara memeriksa klaim seperti ini?', 'Perlu Konteks'),
        ('Benarkah Ada Bantuan Tunai dari Link Ini?', 'Saya menerima tautan yang meminta data pribadi untuk mencairkan bantuan. Apakah ciri-cirinya termasuk penipuan?', 'Kemungkinan Hoaks'),
        ('Tips Memeriksa Foto Lama yang Disebarkan Ulang', 'Apa langkah paling mudah untuk mengetahui apakah sebuah foto berasal dari kejadian terbaru atau foto lama?', NULL),
        ('Diskusi: Sumber Resmi untuk Informasi Bencana', 'Mari berbagi sumber resmi yang bisa dipakai untuk memeriksa kabar bencana dan cuaca ekstrem.', 'Kemungkinan Fakta'),
        ('Klaim Promo Gratis Mengatasnamakan Brand Terkenal', 'Ada unggahan yang meminta pengguna membagikan postingan untuk mendapatkan hadiah. Bagaimana cara mengecek keaslian promonya?', 'Kemungkinan Hoaks'),
        ('Cara Mengenali Judul Berita yang Menyesatkan', 'Judul yang sangat provokatif belum tentu menggambarkan isi berita. Apa saja tanda yang perlu diperhatikan?', 'Perlu Konteks'),
        ('Apakah Video Ini Rekaman Kejadian Terbaru?', 'Saya menemukan video lama yang dibagikan seolah-olah terjadi minggu ini. Mari cek cara menemukan tanggal aslinya.', 'Kemungkinan Hoaks'),
        ('Membandingkan Berita dari Dua Media', 'Apakah membandingkan beberapa media sudah cukup untuk memastikan sebuah informasi benar?', NULL),
        ('Kabar Gempa dan Informasi Resmi', 'Di mana tempat paling tepat untuk mengecek informasi gempa agar tidak ikut menyebarkan kabar yang belum pasti?', 'Kemungkinan Fakta'),
        ('Ciri-Ciri Akun Media Sosial Palsu', 'Akun baru dengan banyak hadiah dan tautan singkat sering muncul di beranda. Apa indikator akun tersebut tidak resmi?', 'Perlu Konteks'),
        ('Pesan Berantai tentang Lowongan Kerja', 'Sebuah pesan menawarkan pekerjaan dengan biaya pendaftaran. Apakah ini pola penipuan yang umum?', 'Kemungkinan Hoaks'),
        ('Mengenali Sumber Berita yang Kredibel', 'Apa yang harus diperiksa dari sebuah situs berita sebelum kita mempercayai informasinya?', 'Kemungkinan Fakta'),
        ('Klaim Makanan yang Bisa Menurunkan Berat Badan', 'Banyak unggahan menyebut satu bahan makanan dapat menurunkan berat badan dengan cepat. Bagaimana memeriksa klaim kesehatan seperti ini?', 'Perlu Konteks'),
        ('Foto Tokoh yang Dipakai untuk Konteks Berbeda', 'Bagaimana cara mengetahui apakah foto seorang tokoh dipakai di luar konteks aslinya?', NULL),
        ('Waspada Tautan Pendek dari Nomor Tidak Dikenal', 'Saya menerima tautan pendek melalui pesan singkat. Apa langkah aman sebelum membukanya?', 'Kemungkinan Hoaks'),
        ('Memeriksa Informasi Harga dan Subsidi', 'Informasi tentang harga atau subsidi sering berubah. Sumber mana yang sebaiknya dijadikan rujukan utama?', 'Kemungkinan Fakta'),
        ('Apakah Semua Centang Biru Berarti Resmi?', 'Centang verifikasi di media sosial tidak selalu berarti semua unggahan akun tersebut benar. Bagaimana cara tetap memeriksa informasinya?', 'Perlu Konteks'),
        ('Diskusi Etika Membagikan Berita yang Belum Terverifikasi', 'Menurut teman-teman, apa yang sebaiknya dilakukan ketika mendapat berita mengejutkan tetapi belum menemukan sumber resminya?', NULL),
        ('Klaim Beasiswa dengan Formulir Berbayar', 'Ada formulir beasiswa yang meminta biaya administrasi dan data lengkap. Apa saja yang perlu diverifikasi?', 'Kemungkinan Hoaks'),
        ('Sumber Resmi Informasi Lalu Lintas', 'Mari kumpulkan sumber resmi untuk mengecek penutupan jalan, kecelakaan, atau perubahan rute.', 'Kemungkinan Fakta')
) AS demo(title, body, label_badge)
WHERE u.email = 'admin@cekfakta.test'
  AND NOT EXISTS (
      SELECT 1 FROM discussion_topics t WHERE t.title = demo.title
  );

INSERT INTO comments (topic_id, user_id, content)
SELECT t.id, u.id, demo.content
FROM discussion_topics t
JOIN users u ON u.email = 'admin@cekfakta.test'
CROSS JOIN (
    VALUES
        ('Waspada Pesan Berantai tentang Kesehatan', 'Sebaiknya cek klaim tersebut ke situs kementerian kesehatan atau organisasi medis yang tepercaya.'),
        ('Benarkah Ada Bantuan Tunai dari Link Ini?', 'Jangan memasukkan OTP atau kata sandi. Periksa alamat situs dan cari pengumuman di kanal resmi.'),
        ('Tips Memeriksa Foto Lama yang Disebarkan Ulang', 'Pencarian gambar terbalik dan pemeriksaan tanggal unggahan bisa menjadi langkah awal.'),
        ('Diskusi: Sumber Resmi untuk Informasi Bencana', 'Untuk informasi cuaca, saya biasanya membandingkan kabar dengan kanal resmi BMKG dan BPBD.'),
        ('Klaim Promo Gratis Mengatasnamakan Brand Terkenal', 'Cek akun dan situs resmi brand sebelum mengikuti promo atau mengisi data pribadi.'),
        ('Cara Mengenali Judul Berita yang Menyesatkan', 'Saya biasanya membaca isi lengkap dan mencari sumber utama sebelum membagikannya.'),
        ('Apakah Video Ini Rekaman Kejadian Terbaru?', 'Pencarian kata kunci dari cuplikan video bisa membantu menemukan unggahan awal.'),
        ('Membandingkan Berita dari Dua Media', 'Perbandingan akan lebih kuat jika kedua media mencantumkan sumber primer yang jelas.'),
        ('Kabar Gempa dan Informasi Resmi', 'Gunakan kanal resmi BMKG dan hindari membagikan angka korban yang belum dikonfirmasi.'),
        ('Ciri-Ciri Akun Media Sosial Palsu', 'Periksa usia akun, alamat tautan, dan apakah akun tersebut tercantum di situs resmi.'),
        ('Pesan Berantai tentang Lowongan Kerja', 'Perusahaan yang sah umumnya tidak meminta pembayaran untuk proses rekrutmen dasar.'),
        ('Mengenali Sumber Berita yang Kredibel', 'Tanggal, penulis, sumber kutipan, dan halaman redaksi bisa menjadi pemeriksaan awal.'),
        ('Klaim Makanan yang Bisa Menurunkan Berat Badan', 'Klaim kesehatan sebaiknya dibandingkan dengan penjelasan tenaga kesehatan atau lembaga resmi.'),
        ('Foto Tokoh yang Dipakai untuk Konteks Berbeda', 'Cari versi resolusi lebih tinggi dan perhatikan keterangan tanggal serta lokasi foto.'),
        ('Waspada Tautan Pendek dari Nomor Tidak Dikenal', 'Jangan memasukkan data login setelah membuka tautan yang sumbernya tidak jelas.'),
        ('Memeriksa Informasi Harga dan Subsidi', 'Informasi terbaru biasanya tersedia di situs kementerian atau lembaga terkait.'),
        ('Apakah Semua Centang Biru Berarti Resmi?', 'Verifikasi akun tidak menggantikan pemeriksaan isi dan sumber setiap unggahan.'),
        ('Diskusi Etika Membagikan Berita yang Belum Terverifikasi', 'Lebih baik menahan diri dan mencari konfirmasi daripada ikut menyebarkan kepanikan.'),
        ('Klaim Beasiswa dengan Formulir Berbayar', 'Cari pengumuman di situs kampus atau penyelenggara resmi dan jangan kirim OTP.'),
        ('Sumber Resmi Informasi Lalu Lintas', 'Akun resmi pemerintah daerah dan kepolisian biasanya memberi pembaruan yang paling relevan.')
) AS demo(title, content)
WHERE t.title = demo.title
  AND NOT EXISTS (
      SELECT 1 FROM comments c WHERE c.topic_id = t.id AND c.content = demo.content
  );

-- Variasikan penulis dan waktu agar data demo terasa lebih alami.
UPDATE discussion_topics AS t
SET user_id = (
        SELECT id FROM users
        WHERE email = CASE (t.id % 4)
            WHEN 0 THEN 'demo.sari@cekfakta.test'
            WHEN 1 THEN 'demo.bima@cekfakta.test'
            WHEN 2 THEN 'demo.nadia@cekfakta.test'
            ELSE 'demo.dimas@cekfakta.test'
        END
    ),
    created_at = CURRENT_TIMESTAMP - ((t.id % 20 + 1) || ' days')::INTERVAL
WHERE t.id IN (SELECT topic_id FROM comments);

UPDATE comments AS c
SET user_id = (
        SELECT id FROM users
        WHERE email = CASE (c.topic_id % 4)
            WHEN 0 THEN 'demo.nadia@cekfakta.test'
            WHEN 1 THEN 'demo.dimas@cekfakta.test'
            WHEN 2 THEN 'demo.sari@cekfakta.test'
            ELSE 'demo.bima@cekfakta.test'
        END
    ),
    created_at = t.created_at + INTERVAL '4 hours'
FROM discussion_topics AS t
WHERE t.id = c.topic_id;

-- Perpanjang deskripsi agar setiap topik terasa seperti diskusi sungguhan.
UPDATE discussion_topics
SET body = body || E'\n\nCatatan: ' || CASE label_badge
        WHEN 'Kemungkinan Fakta' THEN 'Bandingkan informasi ini dengan sumber resmi dan perhatikan tanggal pembaruannya sebelum membagikan.'
        WHEN 'Kemungkinan Hoaks' THEN 'Jangan langsung membuka tautan atau menyebarkan pesan sebelum memeriksa sumber primernya.'
        WHEN 'Perlu Konteks' THEN 'Periksa konteks, waktu, lokasi, dan sumber asli agar kesimpulannya tidak terburu-buru.'
        ELSE 'Mari kumpulkan sumber yang jelas dan saling membandingkan informasi sebelum mengambil kesimpulan.'
END
WHERE body NOT LIKE '%Catatan:%';

-- Tambahkan dua komentar lanjutan pada setiap topik demo.
INSERT INTO comments (topic_id, user_id, content, created_at)
SELECT t.id, u.id,
             format('Menurut saya, topik "%s" perlu dicek dari sumber primer agar informasi yang dibahas tidak hanya berasal dari potongan unggahan.', t.title),
             t.created_at + INTERVAL '6 hours'
FROM discussion_topics t
JOIN users u ON u.email = 'demo.sari@cekfakta.test'
WHERE t.id IN (SELECT topic_id FROM comments)
    AND NOT EXISTS (
            SELECT 1 FROM comments c
            WHERE c.topic_id = t.id
                AND c.content LIKE 'Menurut saya, topik%'
    );

INSERT INTO comments (topic_id, user_id, content, created_at)
SELECT t.id, u.id,
             format('Saya menemukan pembahasan tentang "%s" juga di beberapa kanal lain. Sebaiknya cek tanggal publikasi, identitas penulis, dan tautan rujukannya.', t.title),
             t.created_at + INTERVAL '12 hours'
FROM discussion_topics t
JOIN users u ON u.email = 'demo.bima@cekfakta.test'
WHERE t.id IN (SELECT topic_id FROM comments)
    AND NOT EXISTS (
            SELECT 1 FROM comments c
            WHERE c.topic_id = t.id
                AND c.content LIKE 'Saya menemukan pembahasan%'
    );
