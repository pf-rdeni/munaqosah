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

    protected $juriModel;
    protected $antrianModel;
    protected $predikatModel;
    protected $rubrikModel;

    public function __construct()
    {
        $this->siswaModel      = new SiswaModel();
        $this->pesertaModel    = new PesertaModel();
        $this->nilaiModel      = new NilaiUjianModel();
        $this->materiModel     = new MateriModel();
        $this->grupMateriModel = new GrupMateriModel();
        $this->kriteriaModel   = new KriteriaModel();
        $this->juriModel       = new \App\Models\Munaqosah\JuriModel();
        $this->antrianModel    = new \App\Models\Munaqosah\AntrianModel();
        $this->predikatModel   = new \App\Models\Munaqosah\PredikatModel();
        $this->rubrikModel     = new \App\Models\Munaqosah\RubrikModel();
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
        $tahunAjaran = $this->getTahunAjaran(); // Dynamic from BaseController
        
        // Gender Calculation
        $genderL = $this->pesertaModel->select('tbl_munaqosah_peserta.id')
                        ->join('tbl_munaqosah_siswa', 'tbl_munaqosah_siswa.nisn = tbl_munaqosah_peserta.nisn')
                        ->where('tbl_munaqosah_peserta.tahun_ajaran', $tahunAjaran)
                        ->where('tbl_munaqosah_siswa.jenis_kelamin', 'L')
                        ->countAllResults();

        $genderP = $this->pesertaModel->select('tbl_munaqosah_peserta.id')
                        ->join('tbl_munaqosah_siswa', 'tbl_munaqosah_siswa.nisn = tbl_munaqosah_peserta.nisn')
                        ->where('tbl_munaqosah_peserta.tahun_ajaran', $tahunAjaran)
                        ->where('tbl_munaqosah_siswa.jenis_kelamin', 'P')
                        ->countAllResults();

        $totalPeserta   = $this->pesertaModel->where('tahun_ajaran', $tahunAjaran)->countAllResults();
        
        // Restore variable needed for view (backward compatibility)
        $pesertaDinilai = $this->nilaiModel->countPesertaDinilai($tahunAjaran);

        // Helper status counts
        $statusCounts = $this->antrianModel->getStatusCounts($tahunAjaran);
        $statMenunggu   = $statusCounts[0] ?? 0;
        $statProses     = $statusCounts[1] ?? 0; // Sedang Ujian + Dipanggil
        $statSelesai    = $statusCounts[2] ?? 0;
        
        // Calculate percentages
        $totalRegistered = $statMenunggu + $statProses + $statSelesai;
        // Fallback to totalPeserta if antrian records missing (should match theoretically)
        if ($totalRegistered == 0) $totalRegistered = $totalPeserta; 
        
        $percentSelesai = $totalRegistered > 0 ? ($statSelesai / $totalRegistered) * 100 : 0;
        $percentProses  = $totalRegistered > 0 ? ($statProses / $totalRegistered) * 100 : 0;
        // Residual for grey area (Belum)
        $percentBelum   = 100 - ($percentSelesai + $percentProses); // Can also use ($statMenunggu / total) * 100

        $progressPercent = round($percentSelesai, 1); // Main indicator is "Done"

        // Data Rubrik Dinamis
        $rubrikData  = [];
        $predikats   = [];
        $listDinilai = [];

        if (in_array('juri', $this->getCurrentUser()['groups'])) {
             // Cari ID Juri berdasarkan User ID
             $juriData = $this->juriModel->where('user_id', $this->session->get('user_id'))->first();
             if ($juriData) {
                 $listDinilai = $this->nilaiModel->getPesertaDinilaiByJuri($juriData['id'], $tahunAjaran);
                 
                 // Ambil Rubrik berdasarkan Grup Materi Juri
                 if (!empty($juriData['id_grup_materi'])) {
                     // 1. Ambil Predikat (Spesifik Grup atau Fallback Global)
                    $predikats = $this->predikatModel->getByGrup($juriData['id_grup_materi']);

                     // 2. Ambil Materi dalam Grup ini
                     $materiList = $this->materiModel->where('id_grup_materi', $juriData['id_grup_materi'])
                                                     ->orderBy('id', 'ASC') // Change from id_materi to id just in case
                                                     ->findAll();

                     foreach ($materiList as $m) {
                         // Ambil Kriteria per Materi
                         $kriteria = $this->kriteriaModel->getByMateri($m['id']);
                         if (empty($kriteria)) continue;

                         $kriteriaIds = array_column($kriteria, 'id');
                         $rubriks = $this->rubrikModel->getRubrikByKriteria($kriteriaIds);
                         
                         // Map Rubrik
                         $map = [];
                         foreach ($rubriks as $r) {
                             $map[$r['id_kriteria']][$r['id_predikat']] = $r['deskripsi'];
                         }

                         $rubrikData[] = [
                             'materi'   => $m,
                             'kriteria' => $kriteria,
                             'map'      => $map
                         ];
                     }
                 }

                 // Calculate Duration
                 $count = count($listDinilai);
                 for ($i = 0; $i < $count; $i++) {
                     $current = $listDinilai[$i];
                     $duration = '-';
                     $source = ''; // Debug/Info

                     // 1. Priority: Queue Times (Real Exam Duration)
                     if (!empty($current['waktu_mulai']) && !empty($current['waktu_selesai'])) {
                         $start = strtotime($current['waktu_mulai']);
                         $end   = strtotime($current['waktu_selesai']);
                         if ($end > $start) {
                             $mins = floor(($end - $start) / 60);
                             $secs = ($end - $start) % 60;
                             $duration = $mins . 'm ' . $secs . 's';
                         }
                     } 
                     // 2. Fallback: Gap between Submissions (Session Pace)
                     // Since list is DESC (Latest first), the "Previous" participant is at $i + 1
                     else if (isset($listDinilai[$i + 1])) {
                         $prev = $listDinilai[$i + 1];
                         $currentTime = strtotime($current['tgl_nilai']);
                         $prevTime    = strtotime($prev['tgl_nilai']);
                         $diff = $currentTime - $prevTime;

                         // Validasi: Jika beda waktu masuk akal (misal < 60 menit)
                         if ($diff > 0 && $diff < 3600) { 
                             $mins = floor($diff / 60);
                             $secs = $diff % 60;
                             $duration = '~' . $mins . 'm ' . $secs . 's'; // Tilde indicates estimate
                         }
                     }

                     $listDinilai[$i]['lama_ujian'] = $duration;
                 }
             }
        }

        $data = [
            'title'        => 'Dashboard',
            'pageTitle'    => 'Dashboard',
            'breadcrumb'   => [
                ['title' => 'Home', 'url' => '/backend/dashboard'],
                ['title' => 'Dashboard', 'url' => ''],
            ],
            'user'         => $this->getCurrentUser(),
            'listDinilai'  => $listDinilai,
            'rubrikData'   => $rubrikData, // Pass dynamic rubric
            'predikats'    => $predikats,  // Pass predikats
            'statistik'    => [
                'totalSiswa'      => $this->siswaModel->countSiswaByStatus('aktif'),
                'totalPeserta'    => $totalPeserta,
                'pesertaDinilai'  => $pesertaDinilai, // Keep original for backwards compat or specific widget
                'statSelesai'     => $statSelesai,
                'statProses'      => $statProses,
                'statMenunggu'    => $statMenunggu,
                'progressPercent' => round($progressPercent, 1),
                'percentProses'   => round($percentProses, 1),
                'genderL'         => $genderL,
                'genderP'         => $genderP,
                'totalMateri'     => $this->materiModel->countAllResults(),
                'totalGrupMateri' => $this->grupMateriModel->countAllResults(),
                'totalKriteria'   => $this->kriteriaModel->countAllResults(),
                'tahunAjaran'     => $tahunAjaran,
            ],
        ];

        return view('backend/dashboard/index', $data);
    }
}
