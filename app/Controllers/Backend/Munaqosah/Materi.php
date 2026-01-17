<?php

namespace App\Controllers\Backend\Munaqosah;

use App\Controllers\BaseController;
use App\Models\Munaqosah\MateriModel;
use App\Models\Munaqosah\GrupMateriModel;

class Materi extends BaseController
{
    protected $materiModel;
    protected $grupModel;

    public function __construct()
    {
        $this->materiModel = new MateriModel();
        $this->grupModel = new GrupMateriModel();
    }

    public function index()
    {
        if (!$this->isLoggedIn()) {
            return redirect()->to('/login');
        }

        // Admin & Panitia Access
        if (!$this->isAdmin() && !$this->isPanitia()) {
             return redirect()->to('/backend/dashboard')->with('error', 'Akses ditolak.');
        }

        // Join dengan Grup Materi dan Aggregasi Kriteria
        // Format: 1. Nama bobot 20
        $this->materiModel->select('tbl_munaqosah_materi_ujian.*, gm.nama_grup_materi');
        $this->materiModel->select("GROUP_CONCAT(CONCAT(k.urutan, '. ', k.nama_kriteria, ' bobot ', TRIM(TRAILING '.00' FROM k.bobot)) ORDER BY k.urutan ASC SEPARATOR '\n') as list_kriteria", false);
        
        $materiList = $this->materiModel->join('tbl_munaqosah_grup_materi gm', 'gm.id = tbl_munaqosah_materi_ujian.id_grup_materi', 'left')
                                        ->join('tbl_munaqosah_kriteria_materi_ujian k', 'k.id_materi = tbl_munaqosah_materi_ujian.id', 'left')
                                        ->groupBy('tbl_munaqosah_materi_ujian.id')
                                        ->orderBy('gm.nama_grup_materi', 'ASC')
                                        ->orderBy('nama_materi', 'ASC')
                                        ->findAll();

        $data = [
            'title'      => 'Data Materi Ujian',
            'pageTitle'  => 'Materi Ujian',
            'breadcrumb' => [
                ['title' => 'Home', 'url' => '/backend/dashboard'],
                ['title' => 'Materi Ujian', 'url' => ''],
            ],
            'user'       => $this->getCurrentUser(),
            'materiList' => $materiList
        ];

        return view('backend/materi/index', $data);
    }

    public function create()
    {
        if (!$this->isLoggedIn()) {
            return redirect()->to('/login');
        }
        
        if (!$this->isAdmin() && !$this->isPanitia()) {
             return redirect()->to('/backend/dashboard')->with('error', 'Akses ditolak.');
        }

        $grupMateri = $this->grupModel->findAll();

        $data = [
            'title'      => 'Tambah Materi',
            'pageTitle'  => 'Tambah Materi Ujian',
            'breadcrumb' => [
                ['title' => 'Home', 'url' => '/backend/dashboard'],
                ['title' => 'Materi Ujian', 'url' => '/backend/materi'],
                ['title' => 'Tambah', 'url' => ''],
            ],
            'user'       => $this->getCurrentUser(),
            'grupMateri' => $grupMateri,
            'validation' => \Config\Services::validation(),
        ];

        return view('backend/materi/create', $data);
    }

    public function store()
    {
        if (!$this->isLoggedIn()) {
            return redirect()->to('/login');
        }

        if (!$this->isAdmin() && !$this->isPanitia()) {
             return redirect()->to('/backend/dashboard')->with('error', 'Akses ditolak.');
        }

        $rules = [
            'nama_materi'    => 'required|min_length[3]',
            'id_grup_materi' => 'required|numeric',
            'nilai_maksimal' => 'required|numeric',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        // Auto Generate ID Materi (3 Huruf dari Nama)
        $namaMateri = $this->request->getPost('nama_materi');
        $idMateri = $this->generateIdMateri($namaMateri);

        $data = [
            'id_materi'      => $idMateri,
            'nama_materi'    => $namaMateri,
            'id_grup_materi' => $this->request->getPost('id_grup_materi'),
            'deskripsi'      => $this->request->getPost('deskripsi'),
            'nilai_maksimal' => $this->request->getPost('nilai_maksimal') ?? 0,
            'status'         => 'aktif'
        ];

        if ($this->materiModel->save($data)) {
            return redirect()->to('/backend/materi')->with('success', 'Materi berhasil ditambahkan.');
        }

        return redirect()->back()->withInput()->with('error', 'Gagal menyimpan materi.');
    }

    public function edit($id)
    {
        if (!$this->isLoggedIn()) {
            return redirect()->to('/login');
        }

        if (!$this->isAdmin() && !$this->isPanitia()) {
             return redirect()->to('/backend/dashboard')->with('error', 'Akses ditolak.');
        }

        $materi = $this->materiModel->find($id);
        if (!$materi) {
            return redirect()->back()->with('error', 'Data tidak ditemukan.');
        }

        $grupMateri = $this->grupModel->findAll();

        $data = [
            'title'      => 'Edit Materi',
            'pageTitle'  => 'Edit Materi Ujian',
            'breadcrumb' => [
                ['title' => 'Home', 'url' => '/backend/dashboard'],
                ['title' => 'Materi Ujian', 'url' => '/backend/materi'],
                ['title' => 'Edit', 'url' => ''],
            ],
            'user'       => $this->getCurrentUser(),
            'materi'     => $materi,
            'grupMateri' => $grupMateri,
            'validation' => \Config\Services::validation(),
        ];

        return view('backend/materi/edit', $data);
    }

    public function update($id)
    {
        if (!$this->isLoggedIn()) {
            return redirect()->to('/login');
        }

        if (!$this->isAdmin() && !$this->isPanitia()) {
             return redirect()->to('/backend/dashboard')->with('error', 'Akses ditolak.');
        }

        $rules = [
            'nama_materi'    => 'required|min_length[3]',
            'id_grup_materi' => 'required|numeric',
            'nilai_maksimal' => 'required|numeric',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $data = [
            'id'             => $id,
            'nama_materi'    => $this->request->getPost('nama_materi'),
            'id_grup_materi' => $this->request->getPost('id_grup_materi'),
            'deskripsi'      => $this->request->getPost('deskripsi'),
            'nilai_maksimal' => $this->request->getPost('nilai_maksimal'),
        ];
        
        // Note: ID Materi biasanya tidak diubah otomatis untuk menjaga integritas, 
        // tapi jika user ingin mengubah nama dan ID ikut berubah itu policy lain.
        // Di sini kita keep ID lama untuk konsistensi.

        if ($this->materiModel->save($data)) {
            return redirect()->to('/backend/materi')->with('success', 'Materi berhasil diperbarui.');
        }

        return redirect()->back()->withInput()->with('error', 'Gagal update materi.');
    }

    public function delete($id)
    {
        if (!$this->isLoggedIn()) {
            return redirect()->to('/login');
        }

        if (!$this->isAdmin() && !$this->isPanitia()) {
             return redirect()->to('/backend/dashboard')->with('error', 'Akses ditolak.');
        }

        if ($this->materiModel->delete($id)) {
            return redirect()->to('/backend/materi')->with('success', 'Materi berhasil dihapus.');
        }

        return redirect()->back()->with('error', 'Gagal menghapus materi.');
    }

    // --- Helper Functions ---

    private function generateIdMateri($name)
    {
        // Format: M + [Inisial 2 Huruf] + [Nomor Urut 2 Digit]
        // Contoh: Praktek Wudhu -> MPW01
        
        $words = explode(' ', trim($name));
        $initials = '';
        
        if (count($words) >= 2) {
            $initials = substr($words[0], 0, 1) . substr($words[1], 0, 1);
        } else {
            $initials = substr($words[0], 0, 2);
        }
        
        $prefix = 'M' . strtoupper($initials);
        
        // Cek Auto Increment
        $counter = 1;
        $finalId = $prefix . str_pad($counter, 2, '0', STR_PAD_LEFT);
        
        while ($this->materiModel->where('id_materi', $finalId)->countAllResults() > 0) {
            $counter++;
            $finalId = $prefix . str_pad($counter, 2, '0', STR_PAD_LEFT);
        }

        return $finalId;
    }

    private function isPanitia()
    {
        $user = $this->getCurrentUser();
        $groups = session()->get('groups');
        return in_array('panitia', $groups);
    }
    
    private function isAdmin() 
    {
        $groups = session()->get('groups');
        return in_array('admin', $groups);
    }
}
