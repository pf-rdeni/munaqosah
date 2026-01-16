<?php

/**
 * ====================================================================
 * AUTH CONTROLLER
 * ====================================================================
 * Controller untuk autentikasi (login, logout)
 * 
 * @package    Munaqosah
 * @author     SDIT An-Nahl
 */

namespace App\Controllers;

use App\Models\UserModel;

class AuthController extends BaseController
{
    protected $userModel;

    public function __construct()
    {
        $this->userModel = new UserModel();
    }

    /**
     * Tampilkan halaman login
     *
     * @return string
     */
    public function login()
    {
        // Jika sudah login, redirect ke dashboard
        if ($this->isLoggedIn()) {
            return redirect()->to('/backend/dashboard');
        }

        $data = [
            'title' => 'Login - Munaqosah SDIT An-Nahl',
        ];

        return view('backend/auth/login', $data);
    }

    /**
     * Proses login
     *
     * @return \CodeIgniter\HTTP\RedirectResponse
     */
    public function attemptLogin()
    {
        // Validasi input
        $rules = [
            'username' => 'required|min_length[3]',
            'password' => 'required|min_length[4]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Username dan password harus diisi');
        }

        // Ambil input
        $username = $this->request->getPost('username');
        $password = $this->request->getPost('password');

        // Cari user
        $user = $this->userModel->getUserByUsername($username);

        if (!$user) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Username tidak ditemukan');
        }

        // Cek apakah user aktif
        if ($user['active'] != 1) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Akun Anda belum aktif');
        }

        // Validasi password
        if (!$this->userModel->validatePassword($password, $user['password_hash'])) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Password salah');
        }

        // Ambil grup user
        $userWithGroups = $this->userModel->getUserWithGroups($user['id']);
        $groups = array_column($userWithGroups['groups'] ?? [], 'name');

        // Set session
        $this->session->set([
            'user_id'   => $user['id'],
            'username'  => $user['username'],
            'fullname'  => $user['fullname'] ?? $user['username'],
            'email'     => $user['email'],
            'groups'    => $groups,
            'logged_in' => true,
        ]);

        // Log login
        $this->logLogin($user['id'], $user['email'], true);

        return redirect()->to('/backend/dashboard')
            ->with('success', 'Selamat datang, ' . ($user['fullname'] ?? $user['username']) . '!');
    }

    /**
     * Proses logout
     *
     * @return \CodeIgniter\HTTP\RedirectResponse
     */
    public function logout()
    {
        // Hapus session
        $this->session->destroy();

        return redirect()->to('/login')
            ->with('success', 'Anda telah berhasil logout');
    }

    /**
     * Log aktivitas login
     *
     * @param int $userId
     * @param string $email
     * @param bool $success
     */
    private function logLogin(int $userId, string $email, bool $success): void
    {
        $this->db->table('auth_logins')->insert([
            'ip_address' => $this->request->getIPAddress(),
            'email'      => $email,
            'user_id'    => $userId,
            'date'       => date('Y-m-d H:i:s'),
            'success'    => $success ? 1 : 0,
        ]);
    }
}
