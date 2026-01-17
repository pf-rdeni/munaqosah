<?php

namespace App\Controllers\Backend;

use App\Controllers\BaseController;
use App\Models\UserModel;

class Users extends BaseController
{
    protected $userModel;

    public function __construct()
    {
        $this->userModel = new UserModel();
    }

    /**
     * Helper untuk cek apakah user saat ini adalah admin
     */
    private function isAdmin()
    {
        $user = $this->getCurrentUser();
        // Asumsi group admin punya nama 'admin'
        // Kita cek dari session atau DB
        $groups = session()->get('groups');
        return in_array('admin', $groups);
    }

    public function index()
    {
        if (!$this->isLoggedIn()) {
            return redirect()->to('/login');
        }

        $currentUser = $this->getCurrentUser();
        $isAdmin = $this->isAdmin();

        // Ambil semua user dengan grup mereka
        // Note: UserModel belum punya method getAllUsersWithGroups yang efisien loop, 
        // tapi kita bisa pake findAll dan loop getUserWithGroups untuk sementara atau join manual.
        // Untuk performa lebih baik, kita join manual di sini.
        
        $builder = $this->db->table('users u');
        $builder->select('u.*, g.name as group_name, g.description as group_desc');
        $builder->join('auth_groups_users gu', 'gu.user_id = u.id', 'left');
        $builder->join('auth_groups g', 'g.id = gu.group_id', 'left');
        $builder->where('u.deleted_at', null);
        
        $users = $builder->get()->getResultArray();

        // 1. Filter: Jika Panitia, HIDE Admin dari list
        if (!$isAdmin) {
            $users = array_filter($users, function($u) {
                return strtolower($u['group_name']) !== 'admin';
            });
        }

        // Filter untuk Panitia: Jangan tampilkan Admin? 
        // User request: "fokus hanya admin dan panitia"
        // "Panitia bisa membuat akun untuk juri, kepala, siswa"
        // Sebaiknya Panitia hanya melihat user yang dia bisa manage (Juri, Kepala, Siswa) + Dirinya sendiri proabilly.
        // Tapi untuk simplifikasi list, kita tampilkan semua dulu, tapi batasi aksi (Edit/Delete).

        $data = [
            'title'      => 'Manajemen Users',
            'pageTitle'  => 'Manajemen Pengguna',
            'breadcrumb' => [
                ['title' => 'Home', 'url' => '/backend/dashboard'],
                ['title' => 'Users', 'url' => ''],
            ],
            'user'       => $currentUser,
            'usersList'  => $users,
            'isAdmin'    => $isAdmin
        ];

        return view('backend/users/index', $data);
    }

    public function create()
    {
        if (!$this->isLoggedIn()) {
            return redirect()->to('/login');
        }

        $allGroups = $this->userModel->getGroups();
        $allowedGroups = [];

        if ($this->isAdmin()) {
            $allowedGroups = $allGroups;
        } else {
            // Panitia hanya boleh buat Juri, Kepala, Siswa
            $allowedNames = ['juri', 'kepala_sekolah', 'siswa']; // Sesuaikan dengan nama di DB
            foreach ($allGroups as $g) {
                // Match partial or exact
                // Mari kita lihat nama grup di DB nanti. Asumsi standard slug.
                if (in_array(strtolower($g['name']), $allowedNames)) {
                    $allowedGroups[] = $g;
                }
            }
        }

        $data = [
            'title'      => 'Tambah User',
            'pageTitle'  => 'Tambah User Baru',
            'breadcrumb' => [
                ['title' => 'Home', 'url' => '/backend/dashboard'],
                ['title' => 'Users', 'url' => '/backend/users'],
                ['title' => 'Tambah', 'url' => ''],
            ],
            'user'       => $this->getCurrentUser(),
            'groups'     => $allowedGroups,
            'validation' => \Config\Services::validation(),
        ];

        return view('backend/users/create', $data);
    }

    public function store()
    {
        if (!$this->isLoggedIn()) {
            return redirect()->to('/login');
        }

        // Ambil Data Input
        $data = [
            'username' => $this->request->getPost('username'),
            'email'    => $this->request->getPost('email'),
            'fullname' => $this->request->getPost('fullname'),
            'password' => $this->request->getPost('password'),
            'group_id' => $this->request->getPost('group_id'),
        ];

        // Logic Default Email
        if (empty($data['email']) && !empty($data['username'])) {
            $data['email'] = $data['username'] . '@an-nahl.sch.id';
        }

        // Validasi
        $validation = \Config\Services::validation();
        $rules = [
            'username' => 'required|regex_match[/^[a-zA-Z0-9_]+$/]|min_length[3]|is_unique[users.username]',
            'email'    => 'required|valid_email|is_unique[users.email]',
            'password' => 'required|min_length[6]',
            'group_id' => 'required|numeric',
            'fullname' => 'required',
        ];

        // Custom Error Message for Username Regex
        $errors = [
            'username' => [
                'regex_match' => 'Username hanya boleh berisi huruf, angka, dan underscore (_).'
            ]
        ];

        if (!$validation->setRules($rules, $errors)->run($data)) {
            return redirect()->back()->withInput()->with('errors', $validation->getErrors());
        }

        // Cek permission group (Security Check)
        $groupId = $data['group_id'];
        if (!$this->isAdmin()) {
            $group = $this->db->table('auth_groups')->where('id', $groupId)->get()->getRowArray();
            $allowedNames = ['juri', 'kepala_sekolah', 'siswa'];
            if (!$group || !in_array(strtolower($group['name']), $allowedNames)) {
                return redirect()->back()->withInput()->with('error', 'Anda tidak memiliki hak akses untuk membuat role ini.');
            }
        }

        // Simpan
        // Password akan di-hash di model createUserWithGroup
        // Kita perlu unset password dari array data jika menggunakan method model yang mengharapkan raw array, 
        // tapi createUserWithGroup saya buat menerima array $userData dan menghandle 'password' key.
        // Cek UserModel: createUserWithGroup($userData, $groupId) -> hashes 'password' key if exists.
        
        // Data yang disimpan ke DB perlu dibersihkan key yang tidak ada di tabel users (group_id)
        $userData = $data;
        unset($userData['group_id']);

        if ($this->userModel->createUserWithGroup($userData, $groupId)) {
            return redirect()->to('/backend/users')->with('success', 'User berhasil ditambahkan.');
        }

        return redirect()->back()->withInput()->with('error', 'Gagal menambahkan user.');
    }

    public function edit($id)
    {
        if (!$this->isLoggedIn()) {
            return redirect()->to('/login');
        }

        $targetUser = $this->userModel->getUserWithGroups($id);
        if (!$targetUser) {
            return redirect()->to('/backend/users')->with('error', 'User tidak ditemukan.');
        }

        // Permission Check
        if (!$this->isAdmin()) {
            // Panitia tidak boleh edit Admin atau Panitia lain (hanya Juri/Kepala/Siswa)
            // Cek group target user
            $targetGroups = array_column($targetUser['groups'], 'name');
            if (in_array('admin', $targetGroups) || in_array('panitia', $targetGroups)) {
                // Kecuali user itu sendiri? (User profile biasanya beda controller, tapi ok lah)
                if ($targetUser['id'] != session()->get('user_id')) {
                    return redirect()->to('/backend/users')->with('error', 'Anda tidak memiliki hak akses untuk mengedit user ini.');
                }
            }
        }

        // Prepare Groups Dropdown
        $allGroups = $this->userModel->getGroups();
        $allowedGroups = [];
        if ($this->isAdmin()) {
            $allowedGroups = $allGroups;
        } else {
            $allowedNames = ['juri', 'kepala_sekolah', 'siswa'];
            foreach ($allGroups as $g) {
                if (in_array(strtolower($g['name']), $allowedNames)) {
                    $allowedGroups[] = $g;
                }
            }
        }

        $data = [
            'title'      => 'Edit User',
            'pageTitle'  => 'Edit Data User',
            'breadcrumb' => [
                ['title' => 'Home', 'url' => '/backend/dashboard'],
                ['title' => 'Users', 'url' => '/backend/users'],
                ['title' => 'Edit', 'url' => ''],
            ],
            'user'       => $this->getCurrentUser(),
            'targetUser' => $targetUser,
            'groups'     => $allowedGroups,
            'validation' => \Config\Services::validation(),
        ];

        return view('backend/users/edit', $data);
    }

    public function update($id)
    {
        if (!$this->isLoggedIn()) {
            return redirect()->to('/login');
        }

        // Permission Check (Same as edit)
        if (!$this->isAdmin()) {
            $targetUser = $this->userModel->getUserWithGroups($id);
            $targetGroups = array_column($targetUser['groups'], 'name');
            if (in_array('admin', $targetGroups) || in_array('panitia', $targetGroups)) {
                 if ($targetUser['id'] != session()->get('user_id')) {
                    return redirect()->to('/backend/users')->with('error', 'Anda tidak berhak.');
                 }
            }
        }

        // Ambil Data Input
        $data = [
            'username' => $this->request->getPost('username'),
            'email'    => $this->request->getPost('email'),
            'fullname' => $this->request->getPost('fullname'),
            'group_id' => $this->request->getPost('group_id'),
        ];

        $inputPassword = $this->request->getPost('password');
        if (!empty($inputPassword)) {
            $data['password'] = $inputPassword;
        }

        // Logic Default Email
        if (empty($data['email']) && !empty($data['username'])) {
            $data['email'] = $data['username'] . '@an-nahl.sch.id';
        }

        // Validation
        $validation = \Config\Services::validation();
        $rules = [
            'username' => "required|regex_match[/^[a-zA-Z0-9_]+$/]|min_length[3]|is_unique[users.username,id,{$id}]",
            'email'    => "required|valid_email|is_unique[users.email,id,{$id}]",
            'fullname' => 'required',
            'group_id' => 'required|numeric',
        ];

        if (!empty($inputPassword)) {
            $rules['password'] = 'min_length[6]';
        }

        // Custom Error Message
        $errors = [
            'username' => [
                'regex_match' => 'Username hanya boleh berisi huruf, angka, dan underscore (_).'
            ]
        ];

        if (!$validation->setRules($rules, $errors)->run($data)) {
            return redirect()->back()->withInput()->with('errors', $validation->getErrors());
        }

        $groupId = $data['group_id'];
         // Secure Group ID again for Panitia
        if (!$this->isAdmin()) {
            $group = $this->db->table('auth_groups')->where('id', $groupId)->get()->getRowArray();
            $allowedNames = ['juri', 'kepala_sekolah', 'siswa'];
            if (!$group || !in_array(strtolower($group['name']), $allowedNames)) {
                 return redirect()->back()->withInput()->with('error', 'Role tidak valid untuk akses Anda.');
            }
        }

        // Clean data for model
        $userData = $data;
        unset($userData['group_id']);

        if ($this->userModel->updateUserWithGroup($id, $userData, $groupId)) {
            return redirect()->to('/backend/users')->with('success', 'User berhasil diperbarui.');
        }

        return redirect()->back()->withInput()->with('error', 'Gagal update user.');
    }

    public function delete($id)
    {
        if (!$this->isLoggedIn()) {
            return redirect()->to('/login');
        }

        $currentUser = $this->getCurrentUser();
        if ($currentUser['id'] == $id) {
            return redirect()->to('/backend/users')->with('error', 'Tidak dapat menghapus akun sendiri.');
        }

        if (!$this->isAdmin()) {
             // Cek target user
             $targetUser = $this->userModel->getUserWithGroups($id);
             $targetGroups = array_column($targetUser['groups'], 'name');
             if (in_array('admin', $targetGroups) || in_array('panitia', $targetGroups)) {
                 return redirect()->to('/backend/users')->with('error', 'Anda tidak berhak menghapus user ini.');
             }
        }

        $this->userModel->delete($id);
        $this->db->table('auth_groups_users')->where('user_id', $id)->delete(); // Cleanup relation
        
        return redirect()->to('/backend/users')->with('success', 'User berhasil dihapus.');
    }
}
