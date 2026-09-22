CREATE TABLE IF NOT EXISTS prebunking_articles (
    id          BIGINT GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    title       VARCHAR(220) NOT NULL,
    summary     TEXT NOT NULL,
    body        TEXT NOT NULL,
    image_url   TEXT,
    source_url  TEXT,
    category    VARCHAR(60) NOT NULL DEFAULT 'Cek Fakta',
    verdict     VARCHAR(40) NOT NULL DEFAULT 'Perlu Konteks',
    published_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP,
    created_by  BIGINT REFERENCES users(id) ON DELETE SET NULL,
    created_at  TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP
);
CREATE INDEX IF NOT EXISTS idx_articles_published ON prebunking_articles(published_at DESC);
CREATE INDEX IF NOT EXISTS idx_articles_category ON prebunking_articles(category, verdict);

INSERT INTO prebunking_articles (title, summary, body, image_url, source_url, category, verdict, published_at, created_by)
SELECT demo.title, demo.summary, demo.body, demo.image_url, demo.source_url, demo.category, demo.verdict, demo.published_at, u.id
FROM users u
CROSS JOIN (VALUES
    ('Klaim Indonesia Akan Turun Salju pada 2026, Benarkah?', 'Klaim viral perlu diperiksa dengan konteks cuaca dan sumber resmi.', 'Jangan langsung percaya pada gambar atau video yang tampak meyakinkan. Periksa tanggal, lokasi, sumber awal, dan bandingkan dengan informasi dari lembaga resmi. Artikel ini adalah contoh materi prebunking untuk membantu pembaca mengenali pola klaim yang sering dibagikan ulang.', 'https://images.unsplash.com/photo-1504711434969-e33886168f5c?auto=format&fit=crop&w=900&q=80', 'https://turnbackhoax.id/', 'Artikel', 'Perlu Konteks', CURRENT_TIMESTAMP - INTERVAL '2 days'),
    ('Klaim Viral di Media Sosial: Cek Konteks Sebelum Membagikan', 'Popularitas sebuah unggahan tidak otomatis membuktikan kebenarannya.', 'Periksa apakah unggahan mencantumkan sumber, tanggal, dan konteks yang lengkap. Cari laporan dari pemeriksa fakta dan hindari membagikan konten hanya karena banyak orang sudah membagikannya.', 'https://images.unsplash.com/photo-1550751827-4bd374c3f58b?auto=format&fit=crop&w=900&q=80', 'https://cekfakta.com/', 'Cek Fakta', 'Perlu Konteks', CURRENT_TIMESTAMP - INTERVAL '5 days'),
    ('Sebelum Membagikan Berita, Periksa Foto dan Sumber Aslinya', 'Foto lama dapat digunakan kembali untuk membangun narasi yang keliru.', 'Gunakan pencarian gambar terbalik, perhatikan detail lokasi, dan cari unggahan paling awal. Foto sebaiknya tidak menjadi satu-satunya dasar untuk menyimpulkan sebuah peristiwa.', 'https://images.unsplash.com/photo-1586339949916-3e9457bef6d3?auto=format&fit=crop&w=900&q=80', 'https://literasi.kompas.com/', 'Panduan', 'Perlu Konteks', CURRENT_TIMESTAMP - INTERVAL '9 days')
) AS demo(title, summary, body, image_url, source_url, category, verdict, published_at)
WHERE u.email = 'admin@cekfakta.test'
AND NOT EXISTS (SELECT 1 FROM prebunking_articles a WHERE a.title = demo.title);
