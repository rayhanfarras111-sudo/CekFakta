-- =========================================================
-- Skema Database CekFakta - jalankan di Supabase SQL Editor
-- (Project Supabase kamu > SQL Editor > New query > paste > Run)
-- =========================================================

-- Dipakai untuk pencarian kemiripan teks (cache klaim mirip)
CREATE EXTENSION IF NOT EXISTS pg_trgm;

-- ---------- USERS ----------
CREATE TABLE IF NOT EXISTS users (
    id            BIGINT GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    username      VARCHAR(100) NOT NULL,
    email         VARCHAR(150) NOT NULL UNIQUE,
    password      VARCHAR(255) NOT NULL,
    role          VARCHAR(20) NOT NULL DEFAULT 'user', -- user | checker | admin
    points        INT NOT NULL DEFAULT 0,               -- untuk badge/gamifikasi kontributor
    created_at    TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP
);

-- ---------- ANALYSES (hasil cek AI + database publik verifikasi) ----------
CREATE TABLE IF NOT EXISTS analyses (
    id                 BIGINT GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    user_id            BIGINT REFERENCES users(id) ON DELETE SET NULL,
    input_type         VARCHAR(10) NOT NULL DEFAULT 'text', -- text | url | image
    input_content      TEXT NOT NULL,      -- teks asli / URL asli
    normalized_text    TEXT NOT NULL,      -- teks yang sudah dinormalisasi, dipakai utk similarity
    image_path         TEXT,
    credibility_score  INT NOT NULL DEFAULT 0,   -- 0-100
    label              VARCHAR(50) NOT NULL,     -- Kemungkinan Fakta | Kemungkinan Hoaks | Perlu Konteks | Belum Dapat Diverifikasi
    explanation        TEXT NOT NULL,
    sources            JSONB NOT NULL DEFAULT '[]', -- [{"title": "...", "url": "..."}]
    created_at         TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX IF NOT EXISTS idx_analyses_user ON analyses(user_id);
CREATE INDEX IF NOT EXISTS idx_analyses_created ON analyses(created_at DESC);
-- index trigram untuk cari klaim yang mirip (cache, hemat panggilan AI)
CREATE INDEX IF NOT EXISTS idx_analyses_text_trgm ON analyses USING gin (normalized_text gin_trgm_ops);

-- ---------- REPORTS (report/feedback ke hasil analisis) ----------
CREATE TABLE IF NOT EXISTS reports (
    id           BIGINT GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    analysis_id  BIGINT NOT NULL REFERENCES analyses(id) ON DELETE CASCADE,
    user_id      BIGINT REFERENCES users(id) ON DELETE SET NULL,
    reason       TEXT NOT NULL,
    status       VARCHAR(20) NOT NULL DEFAULT 'pending', -- pending | reviewed | dismissed
    created_at   TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP
);

-- ---------- DISCUSSION TOPICS ----------
CREATE TABLE IF NOT EXISTS discussion_topics (
    id           BIGINT GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    user_id      BIGINT REFERENCES users(id) ON DELETE SET NULL,
    analysis_id  BIGINT REFERENCES analyses(id) ON DELETE SET NULL, -- null = topik mandiri
    title        VARCHAR(200) NOT NULL,
    body         TEXT NOT NULL,
    label_badge  VARCHAR(50),           -- diisi otomatis dari analyses.label jika berasal dari hasil analisis
    is_hidden    BOOLEAN NOT NULL DEFAULT FALSE, -- untuk moderasi admin
    created_at   TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX IF NOT EXISTS idx_topics_created ON discussion_topics(created_at DESC);

-- ---------- COMMENTS ----------
CREATE TABLE IF NOT EXISTS comments (
    id           BIGINT GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    topic_id     BIGINT NOT NULL REFERENCES discussion_topics(id) ON DELETE CASCADE,
    user_id      BIGINT REFERENCES users(id) ON DELETE SET NULL,
    content      TEXT NOT NULL,
    is_hidden    BOOLEAN NOT NULL DEFAULT FALSE,
    created_at   TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX IF NOT EXISTS idx_comments_topic ON comments(topic_id);

-- ---------- DISCUSSION UPVOTES ----------
-- Satu user hanya bisa memberi satu upvote pada satu topik.
CREATE TABLE IF NOT EXISTS discussion_votes (
    topic_id    BIGINT NOT NULL REFERENCES discussion_topics(id) ON DELETE CASCADE,
    user_id     BIGINT NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    created_at  TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (topic_id, user_id)
);

CREATE INDEX IF NOT EXISTS idx_votes_topic ON discussion_votes(topic_id);

-- ---------- BOOKMARKS ----------
-- User dapat menyimpan hasil analisis untuk dibaca kembali.
CREATE TABLE IF NOT EXISTS bookmarks (
    user_id     BIGINT NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    analysis_id BIGINT NOT NULL REFERENCES analyses(id) ON DELETE CASCADE,
    created_at  TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (user_id, analysis_id)
);

CREATE INDEX IF NOT EXISTS idx_bookmarks_user ON bookmarks(user_id, created_at DESC);

-- ---------- NOTIFICATIONS ----------
CREATE TABLE IF NOT EXISTS notifications (
    id          BIGINT GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    user_id     BIGINT NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    type        VARCHAR(30) NOT NULL,
    message     TEXT NOT NULL,
    link        TEXT,
    is_read     BOOLEAN NOT NULL DEFAULT FALSE,
    created_at  TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX IF NOT EXISTS idx_notifications_user ON notifications(user_id, is_read, created_at DESC);

-- ---------- SEED: akun admin default untuk testing ----------
-- Password hash di bawah ini valid untuk password: admin123
-- Segera login lalu ganti password ini untuk penggunaan sungguhan.
-- Untuk generate hash baru sendiri: php -r "echo password_hash('password_baru', PASSWORD_DEFAULT);"
INSERT INTO users (username, email, password, role)
SELECT 'Admin', 'admin@cekfakta.test', '$2b$10$TSlOQSa8u5JAHpilp5/a1.KeFnF5GTMRbKurVPqCyS8ywLYCePx5K', 'admin'
WHERE NOT EXISTS (SELECT 1 FROM users WHERE email = 'admin@cekfakta.test');
