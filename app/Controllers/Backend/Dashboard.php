<?php

/**
 * ====================================================================
 * DASHBOARD CONTROLLER
 * ====================================================================
 * Controller untuk halaman dashboard backend
 * 
 * @package    Munaqosah
 * @author     SDIT An-Nahl
 */

namespace App\Controllers\Backend;

use App\Controllers\BaseController;
use App\Models\SiswaModel;
use App\Models\PesertaModel;
use App\Models\NilaiUjianModel;

class Dashboard extends BaseController
{
    protected $siswaModel;
    protected $pesertaModel;
    protected $nilaiModel;

    public function __construct()
    {
        $this->siswaModel   = new SiswaModel();
        $this->pesertaModel = new PesertaModel();
        $this->nilaiModel   = new NilaiUjianModel();
    }

    /**
     * Halaman utama dashboard
     *
     * @return string|\CodeIgniter\HTTP\RedirectResponse
     */
    public function index()
    {
        // Cek login
        if (!$this->isLoggedIn()) {
            return redirect()->to('/login');
        }

        // Ambil statistik
        $tahunAjaran = date('Y') . '/' . (date('Y') + 1);
        
        $data = [
            'title'        => 'Dashboard',
            'pageTitle'    => 'Dashboard',
            'breadcrumb'   => [
                ['title' => 'Home', 'url' => '/backend/dashboard'],
                ['title' => 'Dashboard', 'url' => ''],
            ],
            'user'         => $this->getCurrentUser(),
            'statistik'    => [
                'totalSiswa'     => $this->siswaModel->countSiswaByStatus('aktif'),
                'totalPeserta'   => $this->pesertaModel->where('tahun_ajaran', $tahunAjaran)->countAllResults(),
                'pesertaDinilai' => $this->nilaiModel->countPesertaDinilai($tahunAjaran),
                'tahunAjaran'    => $tahunAjaran,
            ],
        ];

        return view('backend/dashboard/index', $data);
    }
}
