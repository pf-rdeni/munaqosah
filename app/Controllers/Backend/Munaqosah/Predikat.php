<?php

namespace App\Controllers\Backend\Munaqosah;

use App\Controllers\BaseController;
use App\Models\Munaqosah\PredikatModel;

class Predikat extends BaseController
{
    protected $predikatModel;

    public function __construct()
    {
        $this->predikatModel = new PredikatModel();
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
            'listData'   => $this->predikatModel->getAll()
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
            'user'       => $this->getCurrentUser()
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
            'data'       => $this->predikatModel->find($id)
        ];

        return view('backend/predikat/form', $data);
    }

    public function store()
    {
        if (!$this->isLoggedIn()) return redirect()->to('/login');

        $data = [
            'nama_predikat'    => $this->request->getPost('nama_predikat'),
            'min_nilai'        => $this->request->getPost('min_nilai'),
            'max_nilai'        => $this->request->getPost('max_nilai'),
            'deskripsi_global' => $this->request->getPost('deskripsi_global'),
            'class_css'        => $this->request->getPost('class_css'),
            'urutan'           => $this->request->getPost('urutan'),
        ];

        $this->predikatModel->insert($data);
        return redirect()->to('/backend/predikat')->with('success', 'Data berhasil ditambahkan.');
    }

    public function update($id)
    {
        if (!$this->isLoggedIn()) return redirect()->to('/login');

        $data = [
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
}
