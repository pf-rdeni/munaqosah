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

namespace App\Controllers\Backend\Munaqosah;

use App\Controllers\BaseController;
use App\Models\Munaqosah\SiswaModel;
use App\Models\Munaqosah\PesertaModel;
use App\Models\Munaqosah\NilaiUjianModel;
use App\Models\Munaqosah\MateriModel;
use App\Models\Munaqosah\GrupMateriModel;
use App\Models\Munaqosah\KriteriaModel;

class Dashboard extends BaseController
{
    protected $siswaModel;
    protected $pesertaModel;
    protected $nilaiModel;
    protected $materiModel;
    protected $grupMateriModel;
    protected $kriteriaModel;

    public function __construct()
    {
        $this->siswaModel      = new SiswaModel();
        $this->pesertaModel    = new PesertaModel();
        $this->nilaiModel      = new NilaiUjianModel();
        $this->materiModel     = new MateriModel();
        $this->grupMateriModel = new GrupMateriModel();
        $this->kriteriaModel   = new KriteriaModel();
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
                'totalSiswa'      => $this->siswaModel->countSiswaByStatus('aktif'),
                'totalPeserta'    => $this->pesertaModel->where('tahun_ajaran', $tahunAjaran)->countAllResults(),
                'pesertaDinilai'  => $this->nilaiModel->countPesertaDinilai($tahunAjaran),
                'totalMateri'     => $this->materiModel->countAllResults(),
                'totalGrupMateri' => $this->grupMateriModel->countAllResults(),
                'totalKriteria'   => $this->kriteriaModel->countAllResults(),
                'tahunAjaran'     => $tahunAjaran,
            ],
        ];

        return view('backend/dashboard/index', $data);
    }
}
