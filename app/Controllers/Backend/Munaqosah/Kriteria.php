<?php

namespace App\Controllers\Backend\Munaqosah;

use App\Controllers\BaseController;
use App\Models\Munaqosah\KriteriaModel;
use App\Models\Munaqosah\MateriModel;

class Kriteria extends BaseController
{
    protected $kriteriaModel;
    protected $materiModel;

    public function __construct()
    {
        $this->kriteriaModel = new KriteriaModel();
        $this->materiModel = new MateriModel();
    }

    public function index($materiId)
    {
        if (!$this->isLoggedIn()) {
            return redirect()->to('/login');
        }

        if (!$this->isAdmin() && !$this->isPanitia()) {
             return redirect()->to('/backend/dashboard')->with('error', 'Akses ditolak.');
        }

        $materi = $this->materiModel->find($materiId);
        if (!$materi) {
            return redirect()->to('/backend/materi')->with('error', 'Materi tidak ditemukan.');
        }

        $kriteriaList = $this->kriteriaModel->getByMateri($materiId);
        
        // Hitung Total Bobot
        $totalBobot = 0;
        foreach ($kriteriaList as $k) {
            $totalBobot += $k['bobot'];
        }

        $data = [
            'title'      => 'Kriteria Penilaian',
            'pageTitle'  => 'Kriteria: ' . $materi['nama_materi'],
            'breadcrumb' => [
                ['title' => 'Home', 'url' => '/backend/dashboard'],
                ['title' => 'Materi Ujian', 'url' => '/backend/materi'],
                ['title' => 'Kriteria', 'url' => ''],
            ],
            'user'       => $this->getCurrentUser(),
            'materi'     => $materi,
            'kriteriaList' => $kriteriaList,
            'totalBobot' => $totalBobot
        ];

        return view('backend/kriteria/index', $data);
    }

    public function store($materiId)
    {
        if (!$this->isLoggedIn()) {
            return redirect()->to('/login');
        }

        if (!$this->isAdmin() && !$this->isPanitia()) {
             return redirect()->to('/backend/dashboard')->with('error', 'Akses ditolak.');
        }

        $newBobot = $this->request->getPost('bobot') ?? 0;
        
        // Validasi Total Bobot > 100
        $currentTotal = $this->getTotalBobot($materiId);
        if (($currentTotal + $newBobot) > 100) {
            return redirect()->back()->withInput()->with('error', 'Gagal! Total bobot melebihi 100. Sisa bobot: ' . (100 - $currentTotal));
        }

        // Simpan
        $namaKriteria = $this->request->getPost('nama_kriteria');
        $idKriteria = $this->generateIdKriteria($namaKriteria, $materiId);

        $data = [
            'id_kriteria'   => $idKriteria,
            'id_materi'     => $materiId,
            'nama_kriteria' => $namaKriteria,
            'deskripsi'     => $this->request->getPost('deskripsi'),
            'bobot'         => $newBobot,
            'urutan'        => $this->request->getPost('urutan') ?? 0,
        ];

        if ($this->kriteriaModel->save($data)) {
            return redirect()->back()->with('success', 'Kriteria berhasil ditambahkan.');
        }

        return redirect()->back()->withInput()->with('error', 'Gagal menyimpan kriteria.');
    }

    public function update($kriteriaId)
    {
        if (!$this->isLoggedIn()) {
            return redirect()->to('/login');
        }

        if (!$this->isAdmin() && !$this->isPanitia()) {
             return redirect()->to('/backend/dashboard')->with('error', 'Akses ditolak.');
        }

        $kriteria = $this->kriteriaModel->find($kriteriaId);
        if (!$kriteria) {
            return redirect()->back()->with('error', 'Data tidak ditemukan.');
        }
        
        $newBobot = $this->request->getPost('bobot');
        $materiId = $kriteria['id_materi'];

        // Validasi Total Bobot (Exclude current bobot first)
        $currentTotal = $this->getTotalBobot($materiId) - $kriteria['bobot'];
        if (($currentTotal + $newBobot) > 100) {
             return redirect()->back()->withInput()->with('error', 'Gagal! Total bobot melebihi 100. Max update: ' . (100 - $currentTotal));
        }

        $data = [
            'id'            => $kriteriaId,
            'nama_kriteria' => $this->request->getPost('nama_kriteria'),
            'deskripsi'     => $this->request->getPost('deskripsi'),
            'bobot'         => $newBobot,
            'urutan'        => $this->request->getPost('urutan'),
        ];

        if ($this->kriteriaModel->save($data)) {
            return redirect()->back()->with('success', 'Kriteria berhasil diperbarui.');
        }

        return redirect()->back()->with('error', 'Gagal update kriteria.');
    }
    
    // --- Helper ---
    
    private function getTotalBobot($materiId) 
    {
        $list = $this->kriteriaModel->where('id_materi', $materiId)->findAll();
        $total = 0;
        foreach ($list as $item) {
            $total += $item['bobot'];
        }
        return $total;
    }

    public function delete($kriteriaId)
    {
        if (!$this->isLoggedIn()) {
            return redirect()->to('/login');
        }

        if (!$this->isAdmin() && !$this->isPanitia()) {
             return redirect()->to('/backend/dashboard')->with('error', 'Akses ditolak.');
        }

        $this->kriteriaModel->delete($kriteriaId);
        return redirect()->back()->with('success', 'Kriteria berhasil dihapus.');
    }

    // --- Helper ---

    private function generateIdKriteria($name, $materiId)
    {
        // Format: [ID Materi]_K[No Urut]
        // Contoh: MPW01_K01
        
        // Ambil ID Materi dari DB untuk memastikan (atau pakai parameter jika sudah string ID)
        // Parameter $materiId saat ini adalah primary key (INT), bukan kode string id_materi.
        // Kita perlu lookup dulu kodenya.
        $materi = $this->materiModel->find($materiId);
        $kodeMateri = $materi['id_materi']; 

        $prefix = $kodeMateri . '_K';
        
        $counter = 1;
        $finalId = $prefix . str_pad($counter, 2, '0', STR_PAD_LEFT);
        
        while ($this->kriteriaModel->where('id_kriteria', $finalId)->countAllResults() > 0) {
            $counter++;
            $finalId = $prefix . str_pad($counter, 2, '0', STR_PAD_LEFT);
        }
        return $finalId;
    }

    private function isPanitia() {
        $groups = session()->get('groups');
        return in_array('panitia', $groups);
    }
    
    private function isAdmin() {
        $groups = session()->get('groups');
        return in_array('admin', $groups);
    }
}
