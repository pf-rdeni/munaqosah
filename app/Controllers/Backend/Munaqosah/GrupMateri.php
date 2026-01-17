<?php

namespace App\Controllers\Backend\Munaqosah;

use App\Controllers\BaseController;
use App\Models\Munaqosah\GrupMateriModel;

class GrupMateri extends BaseController
{
    protected $grupMateriModel;

    public function __construct()
    {
        $this->grupMateriModel = new GrupMateriModel();
    }

    public function index()
    {
        if (!$this->isLoggedIn()) {
            return redirect()->to('/login');
        }

        $data = [
            'title'      => 'Grup Materi',
            'pageTitle'  => 'Data Grup Materi',
            'breadcrumb' => [
                ['title' => 'Home', 'url' => '/backend/dashboard'],
                ['title' => 'Grup Materi', 'url' => ''],
            ],
            'user'       => $this->getCurrentUser(),
            'grupList'   => $this->grupMateriModel->orderBy('urutan', 'ASC')->findAll()
        ];

        return view('backend/grup_materi/index', $data);
    }

    public function create()
    {
        if (!$this->isLoggedIn()) {
            return redirect()->to('/login');
        }

        $data = [
            'title'      => 'Tambah Grup Materi',
            'pageTitle'  => 'Tambah Grup Materi',
            'breadcrumb' => [
                ['title' => 'Home', 'url' => '/backend/dashboard'],
                ['title' => 'Grup Materi', 'url' => '/backend/grup-materi'],
                ['title' => 'Tambah', 'url' => ''],
            ],
            'user'       => $this->getCurrentUser(),
            'validation' => \Config\Services::validation()
        ];

        return view('backend/grup_materi/create', $data);
    }

    public function store()
    {
        if (!$this->isLoggedIn()) {
            return redirect()->to('/login');
        }

        $rules = [
            'nama_grup_materi' => 'required',
            // 'nama_grup_materi' => 'required|is_unique[tbl_munaqosah_grup_materi.nama_grup_materi]', // Allow duplicates
            // 'id_grup_materi' validation handled manually for auto-increment
            'urutan'           => 'numeric'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $inputID = strtoupper($this->request->getPost('id_grup_materi')); // e.g. PS01
        
        // Cek apakah ID yang diinput sudah ada?
        $isExist = $this->grupMateriModel->where('id_grup_materi', $inputID)->countAllResults() > 0;

        if (!$isExist) {
            // Jika belum ada, gunakan ID tersebut (ini support custom ID seperti PD01)
            $finalID = $inputID;
        } else {
            // Jika sudah ada (collision), lakukan Auto-Increment berdasarkan Prefix
            $prefix = substr($inputID, 0, 2);
            if (!ctype_alpha($prefix)) {
                 $prefix = 'GR'; 
            }

            // Cari counter tertinggi untuk prefix ini
            $existing = $this->grupMateriModel->like('id_grup_materi', $prefix, 'after')->findAll();
            
            $maxCounter = 0;
            foreach ($existing as $row) {
                $suffix = substr($row['id_grup_materi'], 2);
                if (is_numeric($suffix)) {
                    $val = intval($suffix);
                    if ($val > $maxCounter) {
                        $maxCounter = $val;
                    }
                }
            }

            $nextCounter = $maxCounter + 1;
            $finalID = $prefix . str_pad($nextCounter, 2, '0', STR_PAD_LEFT);
        }

        $data = [
            'id_grup_materi'   => $finalID,
            'nama_grup_materi' => $this->request->getPost('nama_grup_materi'),
            'deskripsi'        => $this->request->getPost('deskripsi'),
            'urutan'           => $this->request->getPost('urutan') ?? 0,
            'status'           => 'aktif'
        ];

        $this->grupMateriModel->insert($data);

        return redirect()->to('/backend/grup-materi')->with('success', "Grup materi berhasil ditambahkan (ID: {$finalID}).");
    }

    public function edit($id)
    {
        if (!$this->isLoggedIn()) {
            return redirect()->to('/login');
        }

        $grup = $this->grupMateriModel->find($id);
        if (!$grup) {
            return redirect()->back()->with('error', 'Data tidak ditemukan.');
        }

        $data = [
            'title'      => 'Edit Grup Materi',
            'pageTitle'  => 'Edit Grup Materi',
            'breadcrumb' => [
                ['title' => 'Home', 'url' => '/backend/dashboard'],
                ['title' => 'Grup Materi', 'url' => '/backend/grup-materi'],
                ['title' => 'Edit', 'url' => ''],
            ],
            'user'       => $this->getCurrentUser(),
            'grup'       => $grup,
            'validation' => \Config\Services::validation()
        ];

        return view('backend/grup_materi/edit', $data);
    }

    public function update($id)
    {
        if (!$this->isLoggedIn()) {
            return redirect()->to('/login');
        }

        $rules = [
            'nama_grup_materi' => "required",
            // 'nama_grup_materi' => "required|is_unique[tbl_munaqosah_grup_materi.nama_grup_materi,id,{$id}]",
            'id_grup_materi'   => "required|is_unique[tbl_munaqosah_grup_materi.id_grup_materi,id,{$id}]|alpha_dash",
            'urutan'           => 'numeric'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $data = [
            'id_grup_materi'   => strtoupper($this->request->getPost('id_grup_materi')),
            'nama_grup_materi' => $this->request->getPost('nama_grup_materi'),
            'deskripsi'        => $this->request->getPost('deskripsi'),
            'urutan'           => $this->request->getPost('urutan'),
            'status'           => $this->request->getPost('status')
        ];

        $this->grupMateriModel->update($id, $data);

        return redirect()->to('/backend/grup-materi')->with('success', 'Grup materi berhasil diperbarui.');
    }

    public function delete($id)
    {
        if (!$this->isLoggedIn()) {
            return redirect()->to('/login');
        }
        
        // TODO: Check if used in Juri or MateriUjian table before delete
        
        $this->grupMateriModel->delete($id);
        return redirect()->to('/backend/grup-materi')->with('success', 'Grup materi berhasil dihapus.');
    }
}
