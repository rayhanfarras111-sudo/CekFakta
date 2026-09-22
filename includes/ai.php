<?php
require_once __DIR__ . '/../config.php';

class AiAnalysisException extends Exception {}

/**
 * Ambil isi teks dari sebuah URL berita (strip tag HTML/script/style).
 */
function fetch_url_content(string $url): ?string
{
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_TIMEOUT        => 15,
        CURLOPT_USERAGENT      => 'Mozilla/5.0 (CekFakta Bot)',
    ]);
    $html = curl_exec($ch);
    $curlError = curl_error($ch);
    curl_close($ch);

    if ($html === false || $html === '') {
        error_log('Gagal ambil URL: ' . $curlError);
        return null;
    }

    $html = preg_replace('/<script\b[^>]*>.*?<\/script>/is', ' ', $html);
    $html = preg_replace('/<style\b[^>]*>.*?<\/style>/is', ' ', $html);
    $text = trim(preg_replace('/\s+/', ' ', strip_tags($html)));

    return $text !== '' ? mb_substr($text, 0, 8000) : null;
}

/**
 * Panggil Gemini untuk menganalisis klaim (teks / url / gambar screenshot).
 * Mengembalikan array: score (0-100), label, explanation, sources[] {title,url}
 *
 * @throws AiAnalysisException
 */
function analyze_with_ai(string $inputText, ?string $imageFullPath = null): array
{
    if (GEMINI_API_KEY === '') {
        throw new AiAnalysisException('GEMINI_API_KEY belum diisi di file .env');
    }

    $tanggal = date('Y-m-d H:i:s');
    $konteksTambahan = '';

    if (filter_var($inputText, FILTER_VALIDATE_URL)) {
        $isi = fetch_url_content($inputText);
        $konteksTambahan = $isi
            ? "\n\nKonten yang berhasil diambil dari URL:\n{$isi}"
            : "\n\n(Catatan: sistem gagal mengambil isi halaman dari URL ini, analisis hanya berdasarkan URL/teks yang diberikan.)";
    }

    $instruksi = <<<PROMPT
Kamu adalah mesin deteksi hoaks untuk platform CekFakta. Tanggal & waktu saat ini: {$tanggal} WIB.
Gunakan pencarian web untuk memverifikasi klaim berikut terhadap sumber tepercaya (media arus utama, situs resmi, fact-checker seperti Mafindo/Cekfakta.com/Kompas.com, dsb).
Jika input berupa gambar/screenshot, baca dulu teks di dalamnya lalu analisis klaimnya.

Balas HANYA dengan JSON valid (tanpa markdown, tanpa penjelasan lain) dengan struktur persis seperti ini:
{
  "score": <angka 0-100, seberapa kredibel/valid klaim ini>,
  "label": "<salah satu persis: Kemungkinan Fakta | Kemungkinan Hoaks | Perlu Konteks | Belum Dapat Diverifikasi>",
  "explanation": "<penjelasan naratif singkat 2-4 kalimat, bahasa Indonesia>",
  "sources": [{"title": "<judul sumber>", "url": "<link sumber>"}]
}

Klaim/input pengguna:
{$inputText}{$konteksTambahan}
PROMPT;

    $parts = [['text' => $instruksi]];

    if ($imageFullPath && is_readable($imageFullPath)) {
        $mime = mime_content_type($imageFullPath) ?: 'image/jpeg';
        $parts[] = [
            'inline_data' => [
                'mime_type' => $mime,
                'data'      => base64_encode(file_get_contents($imageFullPath)),
            ],
        ];
    }

    $payload = [
        'contents' => [['parts' => $parts]],
        'tools'    => [['google_search' => new stdClass()]],
        'generationConfig' => [
            'temperature' => 0.2,
        ],
    ];

    $url = "https://generativelanguage.googleapis.com/v1beta/models/" . GEMINI_MODEL . ":generateContent?key=" . GEMINI_API_KEY;

    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST           => true,
        CURLOPT_HTTPHEADER     => ['Content-Type: application/json'],
        CURLOPT_POSTFIELDS     => json_encode($payload),
        CURLOPT_TIMEOUT        => 45,
    ]);
    $response = curl_exec($ch);

    if (curl_errno($ch)) {
        $err = curl_error($ch);
        curl_close($ch);
        throw new AiAnalysisException('Error jaringan ke Gemini: ' . $err);
    }
    curl_close($ch);

    $result = json_decode($response, true);
    $rawText = $result['candidates'][0]['content']['parts'][0]['text'] ?? null;

    if (!$rawText) {
        error_log('Respons Gemini tidak sesuai harapan: ' . $response);
        throw new AiAnalysisException('AI tidak memberikan respons yang valid. Coba lagi.');
    }

    // Bersihkan kalau AI membungkus JSON dengan ```json ... ```
    $cleaned = trim(preg_replace('/^```(json)?|```$/m', '', trim($rawText)));

    $parsed = json_decode($cleaned, true);
    if (!is_array($parsed) || !isset($parsed['score'], $parsed['label'], $parsed['explanation'])) {
        error_log('Gagal parse JSON dari Gemini: ' . $rawText);
        throw new AiAnalysisException('AI memberikan format jawaban yang tidak terduga. Coba lagi.');
    }

    $validLabels = ['Kemungkinan Fakta', 'Kemungkinan Hoaks', 'Perlu Konteks', 'Belum Dapat Diverifikasi'];
    if (!in_array($parsed['label'], $validLabels, true)) {
        $parsed['label'] = 'Belum Dapat Diverifikasi';
    }

    return [
        'score'       => max(0, min(100, (int) $parsed['score'])),
        'label'       => $parsed['label'],
        'explanation' => (string) $parsed['explanation'],
        'sources'     => is_array($parsed['sources'] ?? null) ? $parsed['sources'] : [],
    ];
}
