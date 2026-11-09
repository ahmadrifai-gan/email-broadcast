<?php
// public/check_pdf.php
// Akses via browser: http://localhost:8080/check_pdf.php?f=1793946621_8fc804307aed893664c8.pdf
// Ganti nama file sesuai isi kolom attachment_pdf_url di tabel template_email.

header('Content-Type: text/plain; charset=utf-8');

$filename = $_GET['f'] ?? '';
if ($filename === '') {
    echo "Usage: ?f=namafile.pdf\n";
    exit;
}

$paths = [
    __DIR__ . '/uploads/' . $filename,
    __DIR__ . '/' . $filename,
];
if (preg_match('#/uploads/(.+)$#', $filename, $m)) {
    $paths[] = __DIR__ . '/uploads/' . $m[1];
}

$found = null;
foreach ($paths as $p) {
    if (is_file($p)) { $found = $p; break; }
}

if (!$found) {
    echo "FILE TIDAK DITEMUKAN.\nDicoba:\n  " . implode("\n  ", $paths) . "\n";
    exit;
}

$size = filesize($found);
$head = file_get_contents($found, false, null, 0, 5);
$tail = $size > 20 ? file_get_contents($found, false, null, $size - 20, 20) : '';

echo "File   : $found\n";
echo "Size   : $size bytes (" . round($size/1024/1024, 2) . " MB)\n";
echo "MD5    : " . md5_file($found) . "\n";
echo "Head   : " . bin2hex($head) . "  (" . preg_replace('/[^\x20-\x7e]/', '.', $head) . ")\n";
echo "Tail   : " . bin2hex($tail) . "  (" . preg_replace('/[^\x20-\x7e]/', '.', $tail) . ")\n";

if (function_exists('finfo_open')) {
    $fi = finfo_open(FILEINFO_MIME_TYPE);
    $mime = finfo_file($fi, $found);
    finfo_close($fi);
    echo "MIME   : $mime\n";
}

// Verifikasi
echo "\n=== VERIFIKASI ===\n";
echo substr($head, 0, 4) === '%PDF' ? "✅ Magic bytes OK (%PDF)\n" : "❌ Magic bytes SALAH — bukan PDF!\n";
echo preg_match('/%%EOF\s*$/', $tail) ? "✅ EOF marker OK (%%EOF)\n" : "❌ EOF marker HILANG — file truncated!\n";

// Round-trip test
$content = file_get_contents($found);
$b64     = base64_encode($content);
$decoded = base64_decode($b64, true);
$rt_ok   = ($decoded !== false && md5($content) === md5($decoded));
echo $rt_ok ? "✅ Base64 round-trip OK\n" : "❌ Base64 round-trip GAGAL\n";

// Cek apakah HTML 404 di-rename
if (stripos($content, '<html') !== false || stripos($content, '<!doctype') !== false) {
    echo "\n🚨 FILE INI ADALAH HTML, BUKAN PDF! (kemungkinan 404 page yang di-rename .pdf)\n";
    echo "Preview 300 byte pertama:\n" . htmlspecialchars(substr($content, 0, 300)) . "\n";
}