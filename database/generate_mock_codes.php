<?php
declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    exit("Script ini hanya dapat dijalankan melalui command line.\n");
}

require_once __DIR__ . '/../config/database.php';

echo "Menghasilkan 1360 Kode Peserta acak baru...\n";

$chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
$codes = [];

while (count($codes) < 1360) {
    $code = '';
    for ($i = 0; $i < 20; $i++) {
        $code .= $chars[random_int(0, strlen($chars) - 1)];
    }
    $codes[$code] = true;
}

$pdo->beginTransaction();
try {
    // Empty existing users if any to avoid confusion
    $pdo->exec('DELETE FROM users');

    $insert = $pdo->prepare("INSERT INTO users (username, status_vote) VALUES (?, 'belum')");
    foreach (array_keys($codes) as $code) {
        $insert->execute([$code]);
    }
    $pdo->commit();
} catch (Throwable $exception) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    exit('Gagal menyimpan kode: ' . $exception->getMessage() . "\n");
}

// Write the generated codes to a text file for easy reference
$txtFile = __DIR__ . '/1360_kode_acak_20_digit.txt';
file_put_contents($txtFile, implode(PHP_EOL, array_keys($codes)));

echo "Selesai! 1360 kode berhasil disimpan di database dan diekspor ke berkas:\n";
echo "-> database/1360_kode_acak_20_digit.txt\n";
echo "Anda dapat menyalin salah satu kode tersebut untuk digunakan sebagai password login.\n";