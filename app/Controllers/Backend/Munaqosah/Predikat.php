<?php

namespace App\Controllers\Backend\Munaqosah;

use App\Controllers\BaseController;
use App\Models\Munaqosah\PredikatModel;
use App\Models\Munaqosah\GrupMateriModel;

class Predikat extends BaseController
{
    protected $predikatModel;
    protected $grupModel;

    public function __construct()
    {
        $this->predikatModel = new PredikatModel();
        $this->grupModel     = new GrupMateriModel();
    }

    public function index()
    {
        if (!$this->isLoggedIn()) return redirect()->to('/login');

        $data = [
            'title'      => 'Kriteria Skoring',
            'pageTitle'  => 'Manajemen Kriteria Skoring (Predikat)',
            'breadcrumb' => [
                ['title' => 'Home', 'url' => '/backend/dashboard'],
                ['title' => 'Kriteria Skoring', 'url' => ''],
            ],
            'user'       => $this->getCurrentUser(),
            'listData'   => $this->predikatModel->select('tbl_munaqosah_predikat.*, tbl_munaqosah_grup_materi.nama_grup_materi')
                                                ->join('tbl_munaqosah_grup_materi', 'tbl_munaqosah_grup_materi.id = tbl_munaqosah_predikat.id_grup_materi', 'left')
                                                ->orderBy('tbl_munaqosah_predikat.urutan', 'ASC')
                                                ->findAll()
        ];

        return view('backend/predikat/index', $data);
    }

    public function create()
    {
        if (!$this->isLoggedIn()) return redirect()->to('/login');

        $data = [
            'title'      => 'Tambah Kriteria',
            'pageTitle'  => 'Tambah Kriteria Skoring',
            'breadcrumb' => [
                ['title' => 'Home', 'url' => '/backend/dashboard'],
                ['title' => 'Kriteria Skoring', 'url' => '/backend/predikat'],
                ['title' => 'Tambah', 'url' => ''],
            ],
            'user'       => $this->getCurrentUser(),
            'grupMateri' => $this->grupModel->findAll()
        ];

        return view('backend/predikat/form', $data);
    }

    public function edit($id)
    {
        if (!$this->isLoggedIn()) return redirect()->to('/login');

        $data = [
            'title'      => 'Edit Kriteria',
            'pageTitle'  => 'Edit Kriteria Skoring',
            'breadcrumb' => [
                ['title' => 'Home', 'url' => '/backend/dashboard'],
                ['title' => 'Kriteria Skoring', 'url' => '/backend/predikat'],
                ['title' => 'Edit', 'url' => ''],
            ],
            'user'       => $this->getCurrentUser(),
            'data'       => $this->predikatModel->find($id),
            'grupMateri' => $this->grupModel->findAll()
        ];

        return view('backend/predikat/form', $data);
    }

    public function store()
    {
        if (!$this->isLoggedIn()) return redirect()->to('/login');

        $idGrup = $this->request->getPost('id_grup_materi');
        if ($idGrup === 'global' || empty($idGrup)) $idGrup = null;

        $min = $this->request->getPost('min_nilai');
        $max = $this->request->getPost('max_nilai');

        // Check Collision
        $collision = $this->predikatModel->checkRangeCollision($idGrup, $min, $max);

        // Check if user confirmed overwrite
        $allowOverwrite = $this->request->getPost('allow_overwrite');

        $data = [
            'id_grup_materi'   => $idGrup,
            'nama_predikat'    => $this->request->getPost('nama_predikat'),
            'min_nilai'        => $min,
            'max_nilai'        => $max,
            'deskripsi_global' => $this->request->getPost('deskripsi_global'),
            'class_css'        => $this->request->getPost('class_css'),
            'urutan'           => $this->request->getPost('urutan'),
        ];

        if ($collision) {
            if ($allowOverwrite) {
                // Perform Update instead of Insert
                $this->predikatModel->update($collision['id'], $data);
                return redirect()->to('/backend/predikat')->with('success', 'Data berhasil diperbarui (Overwrite).');
            } else {
                // Show Confirmation
                $viewData = [
                    'title'      => 'Konfirmasi Overwrite',
                    'pageTitle'  => 'Konfirmasi Overwrite Data',
                    'breadcrumb' => [
                        ['title' => 'Home', 'url' => '/backend/dashboard'],
                        ['title' => 'Kriteria Skoring', 'url' => '/backend/predikat'],
                        ['title' => 'Konfirmasi', 'url' => ''],
                    ],
                    'user'       => $this->getCurrentUser(),
                    'data'       => $data + ['id_grup_materi' => $this->request->getPost('id_grup_materi')], // Keep raw string for dropdown
                    'grupMateri' => $this->grupModel->findAll(),
                    'collision'  => $collision
                ];
                return view('backend/predikat/form', $viewData);
            }
        }

        $this->predikatModel->insert($data);
        return redirect()->to('/backend/predikat')->with('success', 'Data berhasil ditambahkan.');
    }

    public function update($id)
    {
        if (!$this->isLoggedIn()) return redirect()->to('/login');

        $idGrup = $this->request->getPost('id_grup_materi');
        if ($idGrup === 'global' || empty($idGrup)) $idGrup = null;

        $data = [
            'id_grup_materi'   => $idGrup,
            'nama_predikat'    => $this->request->getPost('nama_predikat'),
            'min_nilai'        => $this->request->getPost('min_nilai'),
            'max_nilai'        => $this->request->getPost('max_nilai'),
            'deskripsi_global' => $this->request->getPost('deskripsi_global'),
            'class_css'        => $this->request->getPost('class_css'),
            'urutan'           => $this->request->getPost('urutan'),
        ];

        $this->predikatModel->update($id, $data);
        return redirect()->to('/backend/predikat')->with('success', 'Data berhasil diperbarui.');
    }

    public function delete($id)
    {
        if (!$this->isLoggedIn()) return redirect()->to('/login');

        $this->predikatModel->delete($id);
        return redirect()->to('/backend/predikat')->with('success', 'Data berhasil dihapus.');
    }
    public function copy($id)
    {
        if (!$this->isLoggedIn()) return redirect()->to('/login');

        // Fetch source data
        $source = $this->predikatModel->find($id);
        
        if (!$source) {
            return redirect()->to('/backend/predikat')->with('error', 'Data tidak ditemukan.');
        }

        $idGrup = $this->request->getPost('id_grup_materi');
        if($idGrup === 'global' || empty($idGrup)) $idGrup = null;

        $data = [
            'title'      => 'Copy Kriteria',
            'pageTitle'  => 'Copy Kriteria Skoring',
            'breadcrumb' => [
                ['title' => 'Home', 'url' => '/backend/dashboard'],
                ['title' => 'Kriteria Skoring', 'url' => '/backend/predikat'],
                ['title' => 'Copy', 'url' => ''],
            ],
            'user'       => $this->getCurrentUser(),
            'data'       => [
                'nama_predikat'    => $source['nama_predikat'] . ' (Copy)',
                'min_nilai'        => $source['min_nilai'],
                'max_nilai'        => $source['max_nilai'],
                'deskripsi_global' => $source['deskripsi_global'],
                'class_css'        => $source['class_css'],
                'urutan'           => $source['urutan'],
                'id_grup_materi'   => '' // Reset group
            ],
            'grupMateri' => $this->grupModel->findAll()
        ];

        return view('backend/predikat/form', $data);
    }
}
