<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Security;
use App\Core\Session;
use App\Models\User;
use App\Models\Candidate;
use App\Models\Vote;
use App\Models\Admin;
use PDOException;
use Throwable;

class AdminController extends Controller
{
    private function requireAdmin(): void
    {
        if (!Session::has('admin_id') || !Session::has('admin_username')) {
            $this->redirect('/admin/login');
        }
    }

    private function requireAdminApi(): void
    {
        if (!Session::has('admin_id') || !Session::has('admin_username')) {
            $this->json(['message' => 'Unauthorized'], 401);
        }
    }

    public function dashboard(): void
    {
        $this->requireAdmin();

        $candidateModel = new Candidate();
        $candidates = $candidateModel->getAll();
        $flash = Session::getFlash();

        $this->render('admin/dashboard', compact('candidates', 'flash'));
    }

    public function changePassword(): void
    {
        $this->requireAdmin();

        $adminId = (int) Session::get('admin_id');

        if ($this->isPost()) {
            $this->validateCsrf();

            $username = Security::sanitizeInput($_POST['username'] ?? '');
            $currentPassword = (string) ($_POST['current_password'] ?? '');
            $newPassword = (string) ($_POST['new_password'] ?? '');
            $confirmPassword = (string) ($_POST['confirm_password'] ?? '');

            if (mb_strlen($username) < 3 || mb_strlen($username) > 60) {
                Session::flash('error', 'Username harus berisi 3 sampai 60 karakter.');
            } elseif ($currentPassword === '' || $newPassword === '' || $confirmPassword === '') {
                Session::flash('error', 'Semua kolom password wajib diisi.');
            } elseif (strlen($newPassword) < 8) {
                Session::flash('error', 'Password baru minimal 8 karakter.');
            } elseif ($newPassword !== $confirmPassword) {
                Session::flash('error', 'Konfirmasi password baru tidak sama.');
            } else {
                $adminModel = new Admin();
                $admin = $adminModel->findById($adminId);

                if (!$admin || !password_verify($currentPassword, $admin['password'])) {
                    Session::flash('error', 'Password saat ini tidak sesuai.');
                } else {
                    try {
                        $adminModel->updatePassword($adminId, password_hash($newPassword, PASSWORD_DEFAULT));
                        
                        $db = \App\Core\Database::getConnection();
                        $updateUsername = $db->prepare('UPDATE admin SET username = ? WHERE id = ?');
                        $updateUsername->execute([$username, $adminId]);
                        
                        Session::set('admin_username', $username);
                        Session::flash('success', 'Username dan password admin berhasil diperbarui.');
                    } catch (PDOException $exception) {
                        Session::flash('error', 'Username tersebut sudah digunakan. Silakan pilih username lain.');
                    }
                }
            }
        }

        $this->redirect('/admin#settings');
    }

    public function uploadLogos(): void
    {
        $this->requireAdmin();

        if ($this->isPost()) {
            $this->validateCsrf();

            $uploadDir = __DIR__ . '/../../assets/uploads/';
            if (!is_dir($uploadDir)) {
                @mkdir($uploadDir, 0777, true);
            }

            if (!is_writable($uploadDir)) {
                Session::flash('error', "Direktori 'assets/uploads/' tidak memiliki izin tulis (Permission Denied). Harap ubah permission folder 'assets/uploads/' menjadi writable (chmod 755 atau 777 di hosting Anda) agar logo dapat diunggah.");
                $this->redirect('/admin#settings');
            }

            $successCount = 0;
            $errors = [];

            $files = ['logo_1', 'logo_2', 'logo_3', 'logo_4', 'favicon'];

            foreach ($files as $field) {
                if (isset($_FILES[$field]) && $_FILES[$field]['error'] !== UPLOAD_ERR_NO_FILE) {
                    $file = $_FILES[$field];

                    if ($file['error'] !== UPLOAD_ERR_OK) {
                        $errors[] = "Gagal mengunggah berkas {$field}.";
                        continue;
                    }

                    if ($file['size'] > 2 * 1024 * 1024) {
                        $errors[] = "Ukuran berkas {$field} maksimal 2MB.";
                        continue;
                    }

                    // Secure MIME detection
                    $finfo = finfo_open(FILEINFO_MIME_TYPE);
                    $mimeType = finfo_file($finfo, $file['tmp_name']);
                    finfo_close($finfo);

                    if ($field === 'favicon') {
                        $allowedMimes = ['image/png', 'image/x-icon', 'image/vnd.microsoft.icon'];
                        if (!in_array($mimeType, $allowedMimes, true)) {
                            $errors[] = "Format favicon harus PNG atau ICO.";
                            continue;
                        }
                        $dest = $uploadDir . 'favicon.png';
                    } else {
                        if ($mimeType !== 'image/png') {
                            $errors[] = "Format {$field} harus PNG transparan.";
                            continue;
                        }
                        $dest = $uploadDir . $field . '.png';
                    }

                    if (move_uploaded_file($file['tmp_name'], $dest)) {
                        $successCount++;
                    } else {
                        $errors[] = "Gagal memindahkan berkas {$field}.";
                    }
                }
            }

            if (count($errors) > 0) {
                Session::flash('error', implode(' ', $errors));
            } elseif ($successCount > 0) {
                Session::flash('success', "Berhasil memperbarui {$successCount} logo/favicon.");
            } else {
                Session::flash('error', 'Tidak ada berkas logo yang dipilih untuk diunggah.');
            }
        }

        $this->redirect('/admin#settings');
    }

    public function editCandidate(): void
    {
        $this->requireAdmin();

        if ($this->isPost()) {
            $this->validateCsrf();

            $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
            $chairName = Security::sanitizeInput($_POST['chair_name'] ?? '');
            $vision = Security::sanitizeInput($_POST['vision'] ?? '');
            $mission = Security::sanitizeInput($_POST['mission'] ?? '');

            if (!$id || $chairName === '' || $vision === '' || $mission === '') {
                Session::flash('error', 'Semua kolom kandidat wajib diisi.');
                $this->redirect('/admin');
            }

            $photoPath = null;
            if (isset($_FILES['photo']) && $_FILES['photo']['error'] !== UPLOAD_ERR_NO_FILE) {
                $file = $_FILES['photo'];
                if ($file['error'] !== UPLOAD_ERR_OK) {
                    Session::flash('error', 'Gagal mengunggah foto.');
                    $this->redirect('/admin');
                }

                if ($file['size'] > 2 * 1024 * 1024) {
                    Session::flash('error', 'Ukuran foto maksimal 2MB.');
                    $this->redirect('/admin');
                }

                // SECURE MIME-type detection using Fileinfo on the server
                $finfo = finfo_open(FILEINFO_MIME_TYPE);
                $mimeType = finfo_file($finfo, $file['tmp_name']);
                finfo_close($finfo);

                $allowedExtensions = [
                    'image/jpeg' => 'jpg',
                    'image/jpg' => 'jpg',
                    'image/png' => 'png',
                ];

                if (!array_key_exists($mimeType, $allowedExtensions)) {
                    Session::flash('error', 'Format foto harus berupa gambar valid (JPG/PNG).');
                    $this->redirect('/admin');
                }

                $ext = $allowedExtensions[$mimeType];
                $filename = 'candidate_' . $id . '_' . time() . '.' . $ext;
                
                $uploadDir = __DIR__ . '/../../assets/uploads/';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }

                $dest = $uploadDir . $filename;
                if (!move_uploaded_file($file['tmp_name'], $dest)) {
                    Session::flash('error', 'Gagal memindahkan berkas foto.');
                    $this->redirect('/admin');
                }

                $photoPath = 'assets/uploads/' . $filename;
            }

            $candidateModel = new Candidate();
            $success = $candidateModel->updateCandidate($id, $chairName, $vision, $mission, $photoPath);

            if ($success) {
                Session::flash('success', 'Data kandidat berhasil diperbarui.');
            } else {
                Session::flash('error', 'Gagal memperbarui data kandidat.');
            }
        }

        $this->redirect('/admin');
    }

    public function listCodes(): void
    {
        $this->requireAdminApi();

        $page = (int) ($_GET['page'] ?? 1);
        $limit = (int) ($_GET['limit'] ?? 10);
        $search = trim((string) ($_GET['search'] ?? ''));
        $status = trim((string) ($_GET['status'] ?? ''));

        $userModel = new User();
        $data = $userModel->getPaginated($page, $limit, $search, $status);
        $totalRecords = $userModel->countFiltered($search, $status);
        $totalPages = (int) ceil($totalRecords / $limit);

        $this->json([
            'data' => $data,
            'current_page' => $page,
            'total_pages' => max(1, $totalPages),
            'total_records' => $totalRecords,
        ]);
    }

    public function generateCodes(): void
    {
        $this->requireAdmin();

        if ($this->isPost()) {
            $this->validateCsrf();

            $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
            $codes = [];

            while (count($codes) < 1360) {
                $code = '';
                for ($i = 0; $i < 20; $i++) {
                    $code .= $chars[random_int(0, strlen($chars) - 1)];
                }
                $codes[$code] = true;
            }

            $userModel = new User();
            $success = $userModel->clearAndInsert(array_keys($codes));

            if ($success) {
                // Write generated codes to text file
                $txtFile = __DIR__ . '/../../database/1360_kode_acak_20_digit.txt';
                file_put_contents($txtFile, implode(PHP_EOL, array_keys($codes)));

                Session::flash('success', 'Berhasil menghasilkan 1360 kode baru dan menyimpannya di database.');
            } else {
                Session::flash('error', 'Gagal menyimpan kode baru ke database.');
            }
        }

        $this->redirect('/admin');
    }

    public function importCodes(): void
    {
        $this->requireAdmin();

        if ($this->isPost()) {
            $this->validateCsrf();

            if (!isset($_FILES['excel']) || $_FILES['excel']['error'] !== UPLOAD_ERR_OK) {
                Session::flash('error', 'Pilih berkas Excel yang valid.');
                $this->redirect('/admin');
            }

            if (!class_exists('ZipArchive')) {
                Session::flash('error', 'Ekstensi PHP ZipArchive belum aktif di server ini.');
                $this->redirect('/admin');
            }

            $file = $_FILES['excel']['tmp_name'];
            $zip = new \ZipArchive();
            if ($zip->open($file) !== true) {
                Session::flash('error', 'Berkas Excel tidak dapat dibuka / rusak.');
                $this->redirect('/admin');
            }

            $sheetXml = $zip->getFromName('xl/worksheets/sheet1.xml');
            $zip->close();

            if ($sheetXml === false) {
                Session::flash('error', 'Worksheet pertama tidak ditemukan pada berkas Excel.');
                $this->redirect('/admin');
            }

            libxml_use_internal_errors(true);
            $xml = simplexml_load_string($sheetXml);
            if ($xml === false) {
                Session::flash('error', 'Isi berkas Excel tidak valid.');
                $this->redirect('/admin');
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
                Session::flash('error', 'Ditemukan ' . count($codes) . ' kode unik; berkas Excel harus berisi tepat 1360 kode valid.');
                $this->redirect('/admin');
            }

            $userModel = new User();
            $success = $userModel->clearAndInsert(array_keys($codes));

            if ($success) {
                $txtFile = __DIR__ . '/../../database/1360_kode_acak_20_digit.txt';
                file_put_contents($txtFile, implode(PHP_EOL, array_keys($codes)));

                Session::flash('success', 'Sukses mengimpor 1360 kode baru dari berkas Excel.');
            } else {
                Session::flash('error', 'Gagal menyimpan kode yang diimpor ke database.');
            }
        }

        $this->redirect('/admin');
    }

    public function exportCodes(): void
    {
        $this->requireAdmin();

        $userModel = new User();
        $codes = $userModel->getAllCodes();

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=daftar_kode_peserta_' . date('Ymd_His') . '.csv');

        $output = fopen('php://output', 'w');
        
        // Add UTF-8 BOM for Excel compatibility
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
        
        fputcsv($output, ['Kode Peserta', 'Status Voting']);

        foreach ($codes as $row) {
            $status = $row['status_vote'] === 'sudah' ? 'Sudah Memilih' : 'Belum Memilih';
            fputcsv($output, [$row['username'], $status]);
        }

        fclose($output);
        exit;
    }

    public function apiStats(): void
    {
        $this->requireAdminApi();

        $userModel = new User();
        $voteModel = new Vote();
        $candidateModel = new Candidate();

        $totalVoters = $userModel->countTotal();
        $voted = $userModel->countVoted();
        $notVoted = max(0, $totalVoters - $voted);
        $totalVotes = $voteModel->countTotal();
        $participation = $totalVoters > 0 ? round(($voted / $totalVoters) * 100, 2) : 0;

        $dbCandidates = $candidateModel->getAll();
        $candidates = [];

        foreach ($dbCandidates as $candidate) {
            $candidateVotes = (int) $candidate['total_votes'];
            $candidates[] = [
                'id' => (int) $candidate['id'],
                'number' => (int) $candidate['number'],
                'chair_name' => $candidate['chair_name'],
                'total_votes' => $candidateVotes,
                'percentage' => $totalVotes > 0 ? round(($candidateVotes / $totalVotes) * 100, 2) : 0,
            ];
        }

        $this->json([
            'total_voters' => $totalVoters,
            'voted' => $voted,
            'not_voted' => $notVoted,
            'participation' => $participation,
            'candidates' => $candidates,
            'updated_at' => date('H:i:s'),
        ]);
    }
}
