<?php

namespace App\Controllers\Backend\Munaqosah;

use App\Controllers\BaseController;
use App\Models\Munaqosah\NilaiUjianModel;

class ResetNilai extends BaseController
{
    protected $nilaiModel;

    public function __construct()
    {
        $this->nilaiModel = new NilaiUjianModel();
    }

    public function index()
    {
        if (!$this->isLoggedIn()) {
            return redirect()->to('/login');
        }

        // Only Admin
        if (!in_groups('admin')) {
             return redirect()->to('/backend/dashboard')->with('error', 'Akses ditolak. Hanya Admin.');
        }

        // Get Distinct Tahun Ajaran from Nilai Table
        $years = $this->nilaiModel->select('tahun_ajaran')->distinct()->orderBy('tahun_ajaran', 'DESC')->findAll();
        
        $data = [
            'title'      => 'Reset Nilai',
            'pageTitle'  => 'Reset Data Nilai',
            'breadcrumb' => [
                ['title' => 'Home', 'url' => '/backend/dashboard'],
                ['title' => 'Reset Nilai', 'url' => ''],
            ],
            'user'       => $this->getCurrentUser(),
            'years'      => array_column($years, 'tahun_ajaran')
        ];

        return view('backend/setting/reset_nilai', $data);
    }

    public function preview()
    {
        if (!$this->request->isAJAX()) {
             return $this->response->setJSON(['success' => false, 'message' => 'Invalid request']);
        }
        
        if (!in_groups('admin')) {
             return $this->response->setJSON(['success' => false, 'message' => 'Unauthorized']);
        }

        $tahunAjaran = $this->request->getPost('tahun_ajaran');

        if (!$tahunAjaran) {
             return $this->response->setJSON(['success' => false, 'message' => 'Pilih Tahun Ajaran.']);
        }

        // Count Records
        $total = $this->nilaiModel->where('tahun_ajaran', $tahunAjaran)->countAllResults();
        
        // Detailed Stats by Materi
        $stats = $this->nilaiModel->select('m.nama_materi, COUNT(*) as jumlah')
                                  ->join('tbl_munaqosah_materi_ujian m', 'm.id = tbl_munaqosah_nilai_ujian.id_materi', 'left')
                                  ->where('tbl_munaqosah_nilai_ujian.tahun_ajaran', $tahunAjaran)
                                  ->groupBy('tbl_munaqosah_nilai_ujian.id_materi')
                                  ->findAll();

        return $this->response->setJSON([
            'success' => true,
            'total' => $total,
            'stats' => $stats
        ]);
    }

    public function execute()
    {
        if (!$this->request->isAJAX()) {
             return $this->response->setJSON(['success' => false, 'message' => 'Invalid request']);
        }
        
        if (!in_groups('admin')) {
             return $this->response->setJSON(['success' => false, 'message' => 'Unauthorized']);
        }

        $tahunAjaran = $this->request->getPost('tahun_ajaran');
        
        if (!$tahunAjaran) {
             return $this->response->setJSON(['success' => false, 'message' => 'Tahun Ajaran invalid.']);
        }

        try {
            $this->nilaiModel->where('tahun_ajaran', $tahunAjaran)->delete();
            
            // Log Activity (Optional)
            // log_message('warning', "User " . $this->getCurrentUser()['username'] . " reset nilai for " . $tahunAjaran);

            return $this->response->setJSON([
                'success' => true, 
                'message' => "Data nilai tahun ajaran $tahunAjaran berhasil dihapus permanen."
            ]);
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false, 
                'message' => 'Gagal menghapus: ' . $e->getMessage()
            ]);
        }
    }
}
