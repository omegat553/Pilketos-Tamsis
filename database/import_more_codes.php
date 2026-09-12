<?php
declare(strict_types=1);

/* Pemakaian: php database/import_codes.php "C:\\path\\ke\\1360_kode_acak_20_digit.xlsx" */

if (PHP_SAPI !== 'cli') {
    exit("Script ini hanya dapat dijalankan melalui command line.\n");
}

$file = $argv[1] ?? '';
$dryRun = in_array('--dry-run', $argv, true);
if ($file === '' || !is_file($file)) {
    exit("File Excel tidak ditemukan.\n");
}

if (!class_exists('ZipArchive')) {
    exit("Ekstensi PHP ZipArchive belum aktif. Aktifkan extension=zip di php.ini.\n");
}

$zip = new ZipArchive();
if ($zip->open($file) !== true) {
    exit("Workbook tidak dapat dibuka.\n");
}

$sheetXml = $zip->getFromName('xl/worksheets/sheet1.xml');
$zip->close();
if ($sheetXml === false) {
    exit("Worksheet pertama tidak ditemukan pada workbook.\n");
}

libxml_use_internal_errors(true);
$xml = simplexml_load_string($sheetXml);
if ($xml === false) {
    exit("Isi worksheet tidak valid.\n");
}

$ns = $xml->getNamespaces(true);
$xml->registerXPathNamespace('x', $ns[''] ?? 'http://schemas.openxmlformats.org/spreadsheetml/2006/main');
$cells = $xml->xpath('//x:sheetData/x:row/x:c[starts-with(@r, "B")]') ?: [];
$codes = [];

foreach ($cells as $cell) {
    $cell->registerXPathNamespace('x', $ns[''] ?? 'http://schemas.openxmlformats.org/spreadsheetml/2006/main');
    $value = trim((string) implode('', $cell->xpath('.//x:t') ?: []));
    $code = strtoupper($value);
    if (preg_match('/^[A-Z0-9]{20}$/', $code)) {
        $codes[$code] = true;
    }
}

if (count($codes) !== 1360) {
    exit('Ditemukan ' . count($codes) . " kode unik dan valid; seharusnya 1360. Impor dibatalkan.\n");
}

if ($dryRun) {
    echo "Validasi berhasil: 1360 kode unik siap diimpor.\n";
    exit;
}

require_once __DIR__ . '/../config/database.php';
$pdo->beginTransaction();
try {
    $insert = $pdo->prepare("INSERT IGNORE INTO users (username, status_vote) VALUES (?, 'belum')");
    $added = 0;
    foreach (array_keys($codes) as $code) {
        $insert->execute([$code]);
        $added += $insert->rowCount();
    }
    $pdo->commit();
} catch (Throwable $exception) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    exit('Impor gagal: ' . $exception->getMessage() . "\n");
}

echo "Impor selesai. {$added} kode baru ditambahkan; " . (count($codes) - $added) . " kode sudah ada.\n";