# CekFakta — Website Deteksi Berita Hoaks Berbasis AI

Dibangun mengikuti `Dokumen_Requirement_CekFakta.docx`. Backend PHP native + PostgreSQL (Supabase), AI pakai Google Gemini dengan web search grounding.

## ⚠️ PENTING — perbaiki dulu sebelum lanjut

File `koneksi.php` di ZIP awal kamu **berisi password database Supabase dalam bentuk teks polos**, langsung tertulis di kode. Kalau file itu sempat ter-push ke GitHub (bahkan repo private sekalipun), anggap password itu bocor.

Yang sudah saya benahi di project ini:
- Semua kredensial (DB & API key Gemini) dipindah ke file `.env` (lihat `.env.example`), yang **tidak** ikut ter-commit (sudah ada di `.gitignore`).
- **Yang WAJIB kamu lakukan sekarang:** buka Supabase dashboard → Database → Reset/rotate password database kamu, karena password lama sudah pernah tertulis di kode dan mungkin sudah terekspos.

## 1. Setup Supabase

1. Buat project di [supabase.com](https://supabase.com) (gratis).
2. Buka **SQL Editor** → jalankan seluruh isi `database/schema.sql`. Ini akan membuat semua tabel (`users`, `analyses`, `discussion_topics`, `comments`, `reports`) + extension `pg_trgm` untuk pencarian klaim mirip.
3. Buka **Project Settings → Database → Connection string**, pilih mode **Session pooler** (port 6543), catat host, user, dan password-nya.

## 2. Setup lokal

1. Salin `.env.example` menjadi `.env`:
   ```
   cp .env.example .env
   ```
2. Isi `.env` dengan kredensial Supabase kamu (langkah 1) dan API key Gemini dari [aistudio.google.com/apikey](https://aistudio.google.com/apikey).
3. Jalankan dengan XAMPP/PHP built-in server (butuh ekstensi PHP `pdo_pgsql` & `curl` aktif):
   ```
   php -S localhost:8000
   ```
4. Buka `http://localhost:8000`.
5. Akun admin default (ganti passwordnya setelah login pertama!):
   - Email: `admin@cekfakta.test`
   - Password: `admin123`

## 3. Struktur project

```
config.php          -> load .env & konstanta aplikasi
includes/
  db.php             -> koneksi PDO ke Supabase
  auth.php           -> session, login/logout, require_login(), require_admin()
  functions.php      -> flash message, CSRF, normalisasi teks, cache kemiripan, rate limit
  ai.php             -> integrasi Gemini (web search grounding + input gambar)
  header.php/footer.php -> layout bersama (navbar terpusat, tanpa sidebar)
index.php            -> Home/Landing
cek-berita.php       -> form input + proses analisis AI
hasil.php            -> Hasil Analisis
komunitas.php        -> Diskusi Komunitas (list, search, filter, mulai diskusi)
diskusi.php          -> Detail Diskusi + komentar
literasi.php         -> Literasi Media
riwayat.php          -> Riwayat Pribadi
login.php / register.php / logout.php
report.php           -> kirim laporan/feedback atas hasil analisis
admin/dashboard.php  -> statistik + moderasi
admin/moderasi.php   -> aksi sembunyikan topik/komentar, tandai laporan selesai
database/schema.sql  -> DDL lengkap untuk Supabase
assets/css/style.css -> desain (warna & font sesuai dokumen requirement)
```

## 4. Checklist fitur vs dokumen requirement

**Sudah diimplementasikan:**
- [x] Home/Landing: navbar terpusat, hero + form cek berita, 4 langkah cara kerja, akses langsung ke Komunitas & Literasi
- [x] Deteksi hoaks AI dari teks, tautan (fetch isi halaman), dan **screenshot** (dikirim langsung ke Gemini sebagai gambar — tidak perlu OCR terpisah)
- [x] Web search grounding lewat Gemini `google_search` tool
- [x] Hasil Analisis: skor 0–100, label, penjelasan, sumber rujukan, disclaimer, tombol diskusikan & simpan otomatis ke riwayat
- [x] Cache kemiripan klaim (Postgres `pg_trgm`) sebelum memanggil AI ulang
- [x] Rate limiting per user/hari (dan per sesi untuk tamu)
- [x] Diskusi Komunitas: daftar topik, badge label, search + filter, mulai diskusi mandiri atau dari hasil analisis, komentar
- [x] Literasi Media: checklist ciri hoaks + artikel dengan filter kategori
- [x] Riwayat Pribadi + filter label & tanggal
- [x] Login/Register dengan password ter-hash (bcrypt) & proteksi CSRF di semua form
- [x] Sistem report/feedback ke hasil analisis
- [x] Dashboard Admin: statistik, topik trending, moderasi (sembunyikan topik/komentar), tinjau laporan
- [x] Desain responsive mobile-first dengan warna & font sesuai dokumen

**Belum diimplementasikan (nice-to-have, bisa menyusul kalau waktu masih ada):**
- [ ] Trending klaim mingguan otomatis (saat ini "trending" di admin dashboard berdasarkan jumlah komentar)
- [ ] Badge/gamifikasi kontributor aktif (kolom `points` di tabel `users` sudah disiapkan, tinggal logikanya)
- [ ] Newsletter ringkasan hoaks mingguan
- [ ] SEO lanjutan (sitemap.xml)

## 5. Catatan keamanan yang sudah diterapkan

- Semua query pakai prepared statement (PDO) → aman dari SQL injection.
- Password di-hash dengan `password_hash()` (bcrypt), tidak pernah disimpan/ditampilkan polos.
- CSRF token di semua form POST.
- Output di-escape dengan `htmlspecialchars()` → aman dari XSS.
- Upload screenshot dibatasi tipe file & ukuran (maks 5MB).
- API key Gemini hanya dipanggil dari backend (PHP), tidak pernah dikirim ke browser.
