<?php

namespace App\Controllers\Backend\Munaqosah;

use App\Controllers\BaseController;
use App\Models\Munaqosah\JuriModel;
use App\Models\Munaqosah\GrupMateriModel;
use App\Models\UserModel;

class Juri extends BaseController
{
    protected $juriModel;
    protected $grupMateriModel;
    protected $userModel;

    public function __construct()
    {
        $this->juriModel       = new JuriModel();
        $this->grupMateriModel = new GrupMateriModel();
        $this->userModel       = new UserModel();
    }

    public function index()
    {
        if (!$this->isLoggedIn()) {
            return redirect()->to('/login');
        }

        $juriKriteriaModel = new \App\Models\Munaqosah\JuriKriteriaModel();
        $kriteriaModel = new \App\Models\Munaqosah\KriteriaModel();
        
        $juriList = $this->juriModel->getJuriWithMateri();
        
        // Add kriteria info for each juri
        foreach ($juriList as &$juri) {
            $hasCustom = $juriKriteriaModel->hasCustomKriteria($juri['id']);
            $juri['kriteria_custom'] = $hasCustom;
            
            // Get total kriteria for this materi grup
            $materiModel = new \App\Models\Munaqosah\MateriModel();
            $materiList = $materiModel->where('id_grup_materi', $juri['id_grup_materi'])->findAll();
            $totalKriteria = 0;
            foreach ($materiList as $m) {
                $totalKriteria += $kriteriaModel->where('id_materi', $m['id'])->countAllResults();
            }
            $juri['kriteria_total'] = $totalKriteria;
            
            if ($hasCustom) {
                $juri['kriteria_count'] = $juriKriteriaModel->getAssignedCount($juri['id']);
                
                // Get actual kriteria names
                $assignedIds = $juriKriteriaModel->getKriteriaIdsByJuri($juri['id']);
                $kriteriaNames = [];
                foreach ($assignedIds as $kId) {
                    $k = $kriteriaModel->find($kId);
                    if ($k) {
                        $kriteriaNames[] = $k['nama_kriteria'];
                    }
                }
                $juri['kriteria_names'] = $kriteriaNames;
            } else {
                $juri['kriteria_count'] = 0;
                $juri['kriteria_names'] = [];
            }
        }

        $data = [
            'title'      => 'Manajemen Juri',
            'pageTitle'  => 'Data Juri',
            'breadcrumb' => [
                ['title' => 'Home', 'url' => '/backend/dashboard'],
                ['title' => 'Juri', 'url' => ''],
            ],
            'user'       => $this->getCurrentUser(),
            'juriList'   => $juriList
        ];

        return view('backend/juri/index', $data);
    }

    public function create()
    {
        if (!$this->isLoggedIn()) {
            return redirect()->to('/login');
        }

        $data = [
            'title'      => 'Tambah Juri',
            'pageTitle'  => 'Tambah Juri Baru',
            'breadcrumb' => [
                ['title' => 'Home', 'url' => '/backend/dashboard'],
                ['title' => 'Juri', 'url' => '/backend/juri'],
                ['title' => 'Tambah', 'url' => ''],
            ],
            'user'       => $this->getCurrentUser(),
            'materiList' => $this->grupMateriModel->getGrupDropdown(), // Now fetching Grup
            'validation' => \Config\Services::validation()
        ];

        return view('backend/juri/create', $data);
    }

    public function store()
    {
        if (!$this->isLoggedIn()) {
            return redirect()->to('/login');
        }

        // 1. Validasi Input Dasar
        $rules = [
            'nama_juri' => 'required',
            'id_grup_materi' => 'required|numeric'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $namaJuri = $this->request->getPost('nama_juri');
        $idGrupMateri = $this->request->getPost('id_grup_materi');

        // 2. Ambil Data Materi untuk Generate Username
        $grupMateri = $this->grupMateriModel->find($idGrupMateri);
        if (!$grupMateri) {
            return redirect()->back()->withInput()->with('error', 'Grup Materi tidak ditemukan.');
        }

        // 3. Logic Auto-Generate Username
        // Format: juri_[nama_grup]_[counter]
        // Bersihkan nama (Praktek Sholat -> praktek_sholat)
        $cleanMateri = url_title($grupMateri['nama_grup_materi'], '_', true);
        $baseUsername = 'juri_' . $cleanMateri;
        
        $finalUsername = '';
        $counter = 1;
        
        do {
            $candidate = $baseUsername . '_' . $counter;
            $exists = $this->userModel->where('username', $candidate)->countAllResults();
            
            if ($exists == 0) {
                $finalUsername = $candidate;
                break;
            }
            $counter++;
        } while (true);

        // 4. Buat User Baru
        $groupJuri = $this->db->table('auth_groups')->where('name', 'juri')->get()->getRowArray();
        
        if (!$groupJuri) {
            return redirect()->back()->withInput()->with('error', 'Group user "juri" belum tersedia.');
        }

        $userData = [
            'username' => $finalUsername,
            'fullname' => $namaJuri,
            'email'    => $finalUsername . '@an-nahl.sch.id',
            'password' => 'JuriMunaqosah123',
            'active'   => 1
        ];

        $this->db->transStart();

        $userId = $this->userModel->createUserWithGroup($userData, $groupJuri['id']);
        
        if (!$userId) {
             $this->db->transRollback();
             return redirect()->back()->withInput()->with('error', 'Gagal membuat akun user.');
        }

        // Simpan Data Juri
        // Table needs: nama_juri, username, id_grup_materi, id_juri (unique code?), user_id
        
        // Generate Smart ID Juri from Username
        // Example: juri_praktek_sholat_1 -> JPS01
        $parts = explode('_', $finalUsername);
        $initials = '';
        $number = '';
        
        foreach ($parts as $index => $part) {
            if ($index === count($parts) - 1 && is_numeric($part)) {
                // Last part is number
                $number = str_pad($part, 2, '0', STR_PAD_LEFT);
            } else {
                // Take first letter, uppercase
                $initials .= strtoupper(substr($part, 0, 1));
            }
        }
        
        $kodeJuri = $initials . $number;

        $juriData = [
            'user_id'        => $userId,
            'nama_juri'      => $namaJuri,
            'username'       => $finalUsername, // Legacy column sync
            'id_grup_materi' => $idGrupMateri,
            'id_juri'        => $kodeJuri 
        ];

        $this->juriModel->insert($juriData);

        $this->db->transComplete();

        if ($this->db->transStatus() === false) {
            // Check db error if debug
            return redirect()->back()->withInput()->with('error', 'Terjadi kesalahan saat menyimpan data juri.');
        }

        return redirect()->to('/backend/juri')->with('success', 
            "Juri berhasil ditambahkan.\nUsername: <b>{$finalUsername}</b>\nPassword: <b>JuriMunaqosah123</b>");
    }
    public function edit($id)
    {
        if (!$this->isLoggedIn()) {
            return redirect()->to('/login');
        }

        $juri = $this->juriModel->find($id);
        if (!$juri) {
            return redirect()->back()->with('error', 'Data juri tidak ditemukan.');
        }

        $data = [
            'title'      => 'Edit Juri',
            'pageTitle'  => 'Edit Data Juri',
            'breadcrumb' => [
                ['title' => 'Home', 'url' => '/backend/dashboard'],
                ['title' => 'Juri', 'url' => '/backend/juri'],
                ['title' => 'Edit', 'url' => ''],
            ],
            'user'       => $this->getCurrentUser(),
            'juri'       => $juri,
            'materiList' => $this->grupMateriModel->getGrupDropdown(),
            'validation' => \Config\Services::validation()
        ];

        return view('backend/juri/edit', $data);
    }

    public function update($id)
    {
        if (!$this->isLoggedIn()) {
            return redirect()->to('/login');
        }

        $rules = [
            'nama_juri'      => 'required',
            'id_grup_materi' => 'required|numeric'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $juri = $this->juriModel->find($id);
        if (!$juri) {
            return redirect()->back()->with('error', 'Data tidak ditemukan.');
        }

        $namaJuri = $this->request->getPost('nama_juri');
        $idGrupMateri = $this->request->getPost('id_grup_materi');

        // Logic Note: We update the Name and Group. 
        // We DO NOT auto-update the Username/ID Juri because it would break login info for the person holding the card.
        // Unless requested otherwise.
        
        $this->db->transStart();

        // Update Juri Data
        $this->juriModel->update($id, [
            'nama_juri'      => $namaJuri,
            'id_grup_materi' => $idGrupMateri
        ]);

        // Update User Fullname
        if (!empty($juri['user_id'])) {
            $this->userModel->update($juri['user_id'], ['fullname' => $namaJuri]);
        }

        $this->db->transComplete();

        if ($this->db->transStatus() === false) {
             return redirect()->back()->withInput()->with('error', 'Gagal mengupdate data juri.');
        }

        return redirect()->to('/backend/juri')->with('success', 'Data juri berhasil diperbarui.');
    }

    public function delete($id)
    {
        if (!$this->isLoggedIn()) {
            return redirect()->to('/login');
        }

        $juri = $this->juriModel->find($id);
        if (!$juri) {
            return redirect()->back()->with('error', 'Data tidak ditemukan.');
        }

        $this->db->transStart();

        // Delete Juri Kriteria assignments
        $juriKriteriaModel = new \App\Models\Munaqosah\JuriKriteriaModel();
        $juriKriteriaModel->where('id_juri', $id)->delete();

        // Delete Juri Record
        $this->juriModel->delete($id);

        // Delete User Account
        if (!empty($juri['user_id'])) {
            $this->userModel->delete($juri['user_id'], true); // Force Delete (Purge)
        }

        $this->db->transComplete();

        return redirect()->to('/backend/juri')->with('success', 'Data juri dan akun user berhasil dihapus.');
    }

    public function resetPassword($id)
    {
        if (!$this->isLoggedIn()) {
            return redirect()->to('/login');
        }

        $juri = $this->juriModel->find($id);
        if (!$juri || empty($juri['user_id'])) {
            return redirect()->back()->with('error', 'Data user tidak ditemukan.');
        }

        // Reset Password Logic
        $defaultPass = 'JuriMunaqosah123';
        
        $this->userModel->update($juri['user_id'], [
            'password_hash' => password_hash($defaultPass, PASSWORD_DEFAULT)
        ]);

        return redirect()->back()->with('success', "Password untuk <b>{$juri['nama_juri']}</b> berhasil direset menjadi: <b>$defaultPass</b>");
    }

    /**
     * AJAX: Get kriteria list with assignment status for a juri
     */
    public function getJuriKriteria($juriId)
    {
        if (!$this->isLoggedIn()) {
            return $this->response->setJSON(['success' => false, 'message' => 'Unauthorized']);
        }

        $juri = $this->juriModel->find($juriId);
        if (!$juri) {
            return $this->response->setJSON(['success' => false, 'message' => 'Juri tidak ditemukan']);
        }

        $juriKriteriaModel = new \App\Models\Munaqosah\JuriKriteriaModel();
        $kriteriaModel = new \App\Models\Munaqosah\KriteriaModel();
        $materiModel = new \App\Models\Munaqosah\MateriModel();
        
        // Get all materi in this grup
        $materiList = $materiModel->where('id_grup_materi', $juri['id_grup_materi'])->findAll();
        
        // Get all kriteria for those materi
        $allKriteria = [];
        foreach ($materiList as $m) {
            $kriteria = $kriteriaModel->where('id_materi', $m['id'])->orderBy('urutan', 'ASC')->findAll();
            foreach ($kriteria as $k) {
                $k['materi_nama'] = $m['nama_materi'];
                $allKriteria[] = $k;
            }
        }
        
        // Get assigned kriteria for this juri
        $assignedIds = $juriKriteriaModel->getKriteriaIdsByJuri($juriId);
        $hasCustom = !empty($assignedIds);
        
        // Get all juri in this grup to show who uses which kriteria
        $allJuriInGrup = $this->juriModel->where('id_grup_materi', $juri['id_grup_materi'])->findAll();
        
        // Build kriteria list with assignment info
        $kriteriaList = [];
        foreach ($allKriteria as $k) {
            $isAssigned = in_array($k['id'], $assignedIds);
            
            // Check if other juri use this kriteria
            $usedByOthers = false;
            $usedByName = '';
            foreach ($allJuriInGrup as $otherJuri) {
                if ($otherJuri['id'] != $juriId) {
                    $otherAssigned = $juriKriteriaModel->getKriteriaIdsByJuri($otherJuri['id']);
                    if (in_array($k['id'], $otherAssigned)) {
                        $usedByOthers = true;
                        $usedByName = $otherJuri['nama_juri'];
                        break;
                    }
                }
            }
            
            $kriteriaList[] = [
                'id' => $k['id'],
                'nama_kriteria' => $k['nama_kriteria'],
                'bobot' => $k['bobot'],
                'materi_nama' => $k['materi_nama'],
                'assigned' => $isAssigned,
                'used_by_others' => $usedByOthers,
                'used_by' => $usedByName
            ];
        }
        
        return $this->response->setJSON([
            'success' => true,
            'data' => [
                'has_custom_setting' => $hasCustom,
                'kriteria' => $kriteriaList,
                'total_count' => count($kriteriaList)
            ]
        ]);
    }

    /**
     * AJAX: Save kriteria assignments for a juri
     */
    public function saveJuriKriteria()
    {
        if (!$this->isLoggedIn()) {
            return $this->response->setJSON(['success' => false, 'message' => 'Unauthorized']);
        }

        $juriId = $this->request->getPost('juri_id');
        $useDefault = $this->request->getPost('use_default');
        $kriteriaIds = $this->request->getPost('kriteria_ids') ?? [];

        $juri = $this->juriModel->find($juriId);
        if (!$juri) {
            return $this->response->setJSON(['success' => false, 'message' => 'Juri tidak ditemukan']);
        }

        $juriKriteriaModel = new \App\Models\Munaqosah\JuriKriteriaModel();

        if ($useDefault) {
            // Clear all custom assignments (use all kriteria)
            $juriKriteriaModel->saveKriteriaForJuri($juriId, []);
            return $this->response->setJSON([
                'success' => true,
                'message' => 'Juri akan menilai semua kriteria'
            ]);
        }

        if (empty($kriteriaIds)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Pilih minimal satu kriteria atau centang "Semua Kriteria"'
            ]);
        }

        // Save custom kriteria assignments
        $juriKriteriaModel->saveKriteriaForJuri($juriId, $kriteriaIds);

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Kriteria juri berhasil disimpan (' . count($kriteriaIds) . ' kriteria)'
        ]);
    }

    /**
     * AJAX: Generate next available username based on grup materi
     */
    public function generateUsername($idGrupMateri)
    {
        if (!$this->isLoggedIn()) {
            return $this->response->setJSON(['success' => false, 'message' => 'Unauthorized']);
        }

        $grupMateri = $this->grupMateriModel->find($idGrupMateri);
        if (!$grupMateri) {
            return $this->response->setJSON(['success' => false, 'message' => 'Grup Materi tidak ditemukan']);
        }

        // Generate username using same logic as store()
        $cleanMateri = url_title($grupMateri['nama_grup_materi'], '_', true);
        $baseUsername = 'juri_' . $cleanMateri;
        
        $finalUsername = '';
        $counter = 1;
        
        do {
            $candidate = $baseUsername . '_' . $counter;
            $exists = $this->userModel->where('username', $candidate)->countAllResults();
            
            if ($exists == 0) {
                $finalUsername = $candidate;
                break;
            }
            $counter++;
        } while ($counter < 100); // Safety limit

        return $this->response->setJSON([
            'success' => true,
            'username' => $finalUsername,
            'nama_juri' => 'Juri ' . $grupMateri['nama_grup_materi']
        ]);
    }

    /**
     * AJAX: Update Juri Group ID (1-10)
     */
    public function updateGrupJuri()
    {
        if (!$this->isLoggedIn()) {
            return $this->response->setJSON(['success' => false, 'message' => 'Unauthorized']);
        }

        $juriId = $this->request->getPost('juri_id');
        $idGrupJuri = $this->request->getPost('id_grup_juri');

        if (!$juriId) {
            return $this->response->setJSON(['success' => false, 'message' => 'Data Juri invalid']);
        }

        if (!is_numeric($idGrupJuri) || $idGrupJuri < 1 || $idGrupJuri > 10) {
            return $this->response->setJSON(['success' => false, 'message' => 'Grup Juri harus antara 1-10']);
        }

        $this->juriModel->update($juriId, ['id_grup_juri' => $idGrupJuri]);

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Grup Juri berhasil diupdate.'
        ]);
    }
}
