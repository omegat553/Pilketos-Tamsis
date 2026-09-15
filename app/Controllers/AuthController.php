<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Security;
use App\Core\Session;
use App\Models\User;
use App\Models\Admin;

class AuthController extends Controller
{
    public function studentLogin(): void
    {
        if (Session::has('user_id') && Session::has('user_username')) {
            $this->redirect('/vote');
        }

        $error = '';

        if ($this->isPost()) {
            $this->validateCsrf();

            $username = strtoupper(Security::sanitizeInput($_POST['username'] ?? ''));

            if (!preg_match('/^[A-Z0-9]{20}$/', $username)) {
                $error = 'Kode peserta harus terdiri dari 20 karakter huruf atau angka.';
            } else {
                $userModel = new User();
                $user = $userModel->findByUsername($username);

                if (!$user) {
                    $error = 'Username tidak ditemukan.';
                } elseif ($user['status_vote'] === 'sudah') {
                    $error = 'Akun ini sudah digunakan untuk memilih dan tidak dapat voting lagi.';
                } else {
                    Session::regenerate();
                    Session::set('user_id', (int) $user['id']);
                    Session::set('user_username', $user['username']);
                    $this->redirect('/vote');
                }
            }
        }

        $flash = Session::getFlash();
        $this->render('student/login', compact('error', 'flash'));
    }

    public function studentLogout(): void
    {
        Session::remove('user_id');
        Session::remove('user_username');
        Session::flash('success', 'Anda telah berhasil keluar.');
        $this->redirect('/login');
    }

    public function adminLogin(): void
    {
        if (Session::has('admin_id') && Session::has('admin_username')) {
            $this->redirect('/admin');
        }

        $error = '';

        if ($this->isPost()) {
            $this->validateCsrf();

            $username = Security::sanitizeInput($_POST['username'] ?? '');
            $password = (string) ($_POST['password'] ?? '');

            if ($username === '' || $password === '') {
                $error = 'Username dan password wajib diisi.';
            } else {
                $adminModel = new Admin();
                $admin = $adminModel->findByUsername($username);

                if (!$admin || !password_verify($password, $admin['password'])) {
                    $error = 'Username atau password salah.';
                } else {
                    Session::regenerate();
                    Session::set('admin_id', (int) $admin['id']);
                    Session::set('admin_username', $admin['username']);
                    $this->redirect('/admin');
                }
            }
        }

        $this->render('admin/login', compact('error'));
    }

    public function adminLogout(): void
    {
        Session::remove('admin_id');
        Session::remove('admin_username');
        $this->redirect('/admin/login');
    }
}
