<?php

namespace App\Controllers\Backend;

use App\Controllers\BaseController;
use App\Models\UserModel;

class Profil extends BaseController
{
    protected $userModel;

    public function __construct()
    {
        $this->userModel = new UserModel();
    }

    /**
     * Halaman Profil User
     */
    public function index()
    {
        if (!$this->isLoggedIn()) {
            return redirect()->to('/login');
        }

        $userId = user_id(); // Use MythAuth helper directly
        $user = $this->userModel->find($userId);

        if (!$user) {
            return redirect()->to('/logout')->with('error', 'User tidak ditemukan.');
        }

        $data = [
            'title'      => 'Profil Saya',
            'pageTitle'  => 'Profil Pengguna',
            'breadcrumb' => [
                ['title' => 'Home', 'url' => '/backend/dashboard'],
                ['title' => 'Profil', 'url' => ''],
            ],
            'user'       => $this->getCurrentUser(), // method in BaseController
            'userData'   => $user,
            'validation' => \Config\Services::validation(),
        ];

        return view('backend/profil/index', $data);
    }

    /**
     * Proses Update Profil (Nama & Password)
     */
    public function update()
    {
        if (!$this->isLoggedIn()) {
            return redirect()->to('/login');
        }

        $userId = user_id(); // Use MythAuth helper directly
        
        // Rules Validasi
        $rules = [
            'fullname' => 'required|min_length[3]|max_length[100]',
        ];

        // Jika password diisi, validasi password
        $password = $this->request->getPost('password');
        if (!empty($password)) {
            $rules['password'] = 'required|min_length[6]';
            $rules['pass_confirm'] = 'required|matches[password]';
        }

        if (!$this->validate($rules)) {
            return redirect()->back()
                ->withInput()
                ->with('errors', $this->validator->getErrors());
        }

        $data = [
            'fullname' => $this->cleanInput($this->request->getPost('fullname')),
        ];

        // Handle Password
        if (!empty($password)) {
            $data['password'] = $password;
        }

        // Gunakan method di UserModel untuk update (termasuk hash password jika ada)
        // updateUserWithGroup handles password hashing if 'password' key exists
        if ($this->userModel->updateUserWithGroup($userId, $data)) {
            return redirect()->to('/backend/profil')->with('success', 'Profil berhasil diperbarui.');
        }

        return redirect()->back()->with('error', 'Gagal memperbarui profil.');
    }

    /**
     * Update Foto Profil (AJAX)
     */
    public function updateFoto()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Invalid request'
            ]);
        }
        
        $userId = user_id(); // Use MythAuth helper directly
        if (!$userId) {
             return $this->response->setJSON([
                'success' => false,
                'message' => 'Sesi habis, silakan login ulang.'
            ]);
        }

        // Ambil data user lama
        $user = $this->userModel->find($userId);

        // Path penyimpanan: public/writable/uploads/profil/user/
        // Sesuai request user: "lokasi image di public/wittable/upload/profil/user"
        // Kita koreksi "wittable" jadi "writable"
        // Dan pastikan ada di FCPATH (public folder)
        $uploadPath = FCPATH . 'writable/uploads/profil/user/';
        $dbPathPrefix = 'writable/uploads/profil/user/'; 
        
        // Buat folder jika belum ada
        if (!is_dir($uploadPath)) {
            mkdir($uploadPath, 0755, true);
        }

        $photoCropped = $this->request->getPost('photo_cropped'); // Base64 string

        try {
            if (!empty($photoCropped)) {
                // 1. Handle Base64 Upload
                if (preg_match('/^data:image\/(\w+);base64,/', $photoCropped, $type)) {
                    $data = substr($photoCropped, strpos($photoCropped, ',') + 1);
                    $data = base64_decode($data);

                    if ($data === false) {
                        throw new \Exception('Gagal decode base64 image');
                    }
                    
                    $extension = strtolower($type[1] ?? 'jpg');
                    if ($extension === 'jpeg') $extension = 'jpg';

                    // Nama file baru
                    // Format: Profil_USERID_TIMESTAMP.jpg
                    $newFileName = 'Profil_' . $userId . '_' . time() . '.' . $extension;
                    $filePath = $uploadPath . $newFileName;

                    // Simpan file
                    if (file_put_contents($filePath, $data)) {
                        // Hapus foto lama jika ada
                        if (!empty($user['user_image'])) {
                             $oldFilePath = FCPATH . $user['user_image'];
                             // Hapus hanya jika file ada dan bukan default (misal)
                             if (file_exists($oldFilePath) && strpos($user['user_image'], 'default') === false) {
                                 @unlink($oldFilePath);
                             }
                        }

                        // Simpan relative path ke database
                        $dbPath = $dbPathPrefix . $newFileName;
                        
                        $this->userModel->update($userId, ['user_image' => $dbPath]);

                        return $this->response->setJSON([
                            'success' => true,
                            'message' => 'Foto profil berhasil diperbarui',
                            'foto_url' => base_url($dbPath) . '?t=' . time()
                        ]);
                    } else {
                        throw new \Exception('Gagal menulis file ke server');
                    }
                } else {
                    throw new \Exception('Format Data Gambar tidak valid');
                }
            } else {
                throw new \Exception('Tidak ada data gambar yang dikirim');
            }

        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    /**
     * Helper untuk normalisasi teks
     */
    private function cleanInput($text)
    {
        if (empty($text)) {
            return null;
        }
        return ucwords(strtolower(trim($text)));
    }
}
