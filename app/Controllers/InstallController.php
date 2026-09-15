<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;
use App\Core\Security;
use App\Core\Session;
use PDO;
use Exception;
use Throwable;

class InstallController extends Controller
{
    private function isVercel(): bool
    {
        return getenv('VERCEL') === '1';
    }

    private function checkInstalled(): void
    {
        $lockFile = __DIR__ . '/../../config/installed.lock';
        if (file_exists($lockFile)) {
            $this->redirect('/login');
        }

        if ($this->isVercel()) {
            try {
                $table = Database::getConnection()->query("SHOW TABLES LIKE 'admin'");
                if ($table->fetchColumn() !== false) {
                    $this->redirect('/login');
                }
            } catch (Throwable) {
                // The installer remains available until the external database is configured.
            }
        }
    }

    public function index(): void
    {
        $this->checkInstalled();

        $error = '';
        $success = '';

        $configDir = __DIR__ . '/../../config';
        if (!$this->isVercel() && !is_writable($configDir)) {
            $error = "Direktori 'config/' tidak memiliki izin tulis (Permission Denied). Harap ubah permission folder 'config/' menjadi writable (misalnya chmod 755 atau 777 di cPanel/hosting Anda) agar konfigurasi dapat disimpan.";
        }

        if ($this->isVercel() && (!getenv('DB_HOST') || !getenv('DB_NAME') || !getenv('DB_USER'))) {
            $error = 'Database Vercel belum dikonfigurasi. Tambahkan DB_HOST, DB_PORT, DB_NAME, DB_USER, dan DB_PASS di Environment Variables Vercel, lalu lakukan redeploy.';
        }

        if ($this->isPost() && empty($error)) {
            $host = Security::sanitizeInput($_POST['host'] ?? '127.0.0.1');
            $port = Security::sanitizeInput($_POST['port'] ?? '3306');
            $dbName = Security::sanitizeInput($_POST['db_name'] ?? 'evoting_osis_gedeg');
            $username = Security::sanitizeInput($_POST['username'] ?? 'root');
            $password = (string) ($_POST['password'] ?? '');
            
            $adminUser = Security::sanitizeInput($_POST['admin_user'] ?? 'admin');
            $adminPass = (string) ($_POST['admin_pass'] ?? 'password');

            if ($host === '' || $port === '' || $dbName === '' || $username === '' || $adminUser === '' || $adminPass === '') {
                $error = 'Semua kolom formulir wajib diisi.';
            } elseif (strlen($adminPass) < 8) {
                $error = 'Password administrator minimal 8 karakter.';
            } else {
                try {
                    $pdo = null;
                    $dsnWithDb = "mysql:host={$host};port={$port};dbname={$dbName};charset=utf8mb4";
                    
                    // Step 1 & 2: Detect if database exists by trying to connect to it first
                    try {
                        $pdo = new PDO($dsnWithDb, $username, $password, [
                            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
                        ]);
                    } catch (Throwable $e) {
                        $isUnknownDb = false;
                        if ($e instanceof \PDOException) {
                            if (isset($e->errorInfo[1]) && $e->errorInfo[1] === 1049) {
                                $isUnknownDb = true;
                            } elseif (str_contains(strtolower($e->getMessage()), 'unknown database') || str_contains($e->getMessage(), '1049')) {
                                $isUnknownDb = true;
                            }
                        }

                        if ($isUnknownDb) {
                            // Reconnect without database first to create it
                            $dsnWithoutDb = "mysql:host={$host};port={$port};charset=utf8mb4";
                            $pdoWithoutDb = new PDO($dsnWithoutDb, $username, $password, [
                                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
                            ]);
                            $pdoWithoutDb->exec("CREATE DATABASE IF NOT EXISTS `{$dbName}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
                            
                            // Reconnect with target database
                            $pdo = new PDO($dsnWithDb, $username, $password, [
                                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
                            ]);
                        } else {
                            throw $e;
                        }
                    }

                    // Step 4: Load and execute DDL from database.sql
                    $sqlFile = __DIR__ . '/../../database.sql';
                    if (!file_exists($sqlFile)) {
                        throw new Exception("Berkas 'database.sql' skema tidak ditemukan di root direktori.");
                    }

                    $sqlContent = file_get_contents($sqlFile);
                    
                    // Strip CREATE DATABASE and USE statements dynamically to avoid switching back to evoting_osis_gedeg
                    $sqlContent = preg_replace('/CREATE DATABASE[^;]+;/i', '', $sqlContent);
                    $sqlContent = preg_replace('/USE [^;]+;/i', '', $sqlContent);
                    
                    $pdo->exec($sqlContent);

                    // Step 5: Setup customized admin account
                    $hashedPassword = password_hash($adminPass, PASSWORD_DEFAULT);
                    $pdo->exec("DELETE FROM admin");
                    $stmt = $pdo->prepare("INSERT INTO admin (username, password) VALUES (?, ?)");
                    $stmt->execute([$adminUser, $hashedPassword]);

                    if (!$this->isVercel()) {
                        // Traditional hosting stores its connection configuration on disk.
                        $configContent = "<?php\ndeclare(strict_types=1);\n\n\$dbHost = '" . addslashes($host) . "';\n\$dbName = '" . addslashes($dbName) . "';\n\$dbUser = '" . addslashes($username) . "';\n\$dbPass = '" . addslashes($password) . "';\n\$dbCharset = 'utf8mb4';\n";
                        $writtenConfig = file_put_contents(__DIR__ . '/../../config/database.php', $configContent);
                        if ($writtenConfig === false) {
                            throw new Exception("Gagal menulis file konfigurasi database di 'config/database.php'. Pastikan folder 'config/' memiliki izin tulis (write permission).");
                        }

                        $lockFile = __DIR__ . '/../../config/installed.lock';
                        $lockData = json_encode([
                            'installed_at' => date('Y-m-d H:i:s'),
                            'ip' => $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1',
                        ], JSON_PRETTY_PRINT);
                        if (file_put_contents($lockFile, $lockData) === false) {
                            throw new Exception("Gagal menulis file lock di 'config/installed.lock'. Pastikan folder 'config/' memiliki izin tulis (write permission).");
                        }
                    }

                    Session::flash('success', 'Instalasi sistem e-voting berhasil. Silakan masuk sebagai siswa atau administrator.');
                    $this->redirect('/login');
                } catch (Throwable $e) {
                    $error = 'Koneksi database atau instalasi gagal: ' . $e->getMessage();
                }
            }
        }

        $this->render('install', compact('error'));
    }
}
