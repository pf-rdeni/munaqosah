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

        // ------------------------------------------------------------------
        // REVISI: Hitung Statistik Berdasarkan PROGRESS PENILAIAN (Match Monitoring)
        // ------------------------------------------------------------------
        
        // 1. Ambil Struktur Materi & Kriteria (Sama seperti MonitoringNilai)
        $allMateri = $this->materiModel->select('tbl_munaqosah_materi_ujian.*, gm.kondisional_set')
                                       ->join('tbl_munaqosah_grup_materi gm', 'gm.id = tbl_munaqosah_materi_ujian.id_grup_materi', 'left')
                                       ->orderBy('tbl_munaqosah_materi_ujian.id', 'ASC')
                                       ->findAll();

        $structure = [];
        foreach ($allMateri as $m) {
            $mId = $m['id'];
            $kriteria = $this->kriteriaModel->where('id_materi', $mId)->findAll();
            $structure[$mId] = [
                'info' => $m,
                'kriteria' => $kriteria
            ];
        }

        // 2. Ambil Semua Skor
        $rawScores = $this->nilaiModel->where('tahun_ajaran', $tahunAjaran)->findAll();
        
        // Organize Scores: $dataScores[NoPeserta][MateriID][KriteriaID] = Count/Array
        // Gunakan struktur yang efisien untuk checking
        $dataScores = [];
        foreach ($rawScores as $row) {
             // Mark as exists
             $dataScores[$row['no_peserta']][$row['id_materi']][$row['id_kriteria']] = true;
        }

        // 3. Ambil Semua Peserta Aktif
        $allPeserta = $this->pesertaModel->where('tahun_ajaran', $tahunAjaran)->findAll();

        // 4. Hitung Status per Peserta
        $countBelum   = 0;
        $countProgres = 0;
        $countSelesai = 0;

        foreach ($allPeserta as $p) {
            $np = $p['no_peserta'];
            $hasAnyScore = false;
            $isComplete  = true;

            foreach ($structure as $mid => $mData) {
                // Ignore Empty Materi (if any)
                if (empty($mData['kriteria'])) continue;

                if (isset($dataScores[$np][$mid])) {
                    $mScores = $dataScores[$np][$mid];
                    foreach ($mData['kriteria'] as $k) {
                        $kid = $k['id'];
                        if (!isset($mScores[$kid])) {
                            $isComplete = false;
                        } else {
                            $hasAnyScore = true;
                        }
                    }
                } else {
                    $isComplete = false;
                }
            }
            
            // Handle case where no kriteria exist at all (edge case)
            if (empty($structure)) $isComplete = false;

            if ($isComplete) {
                $countSelesai++;
            } elseif ($hasAnyScore) {
                $countProgres++;
            } else {
                $countBelum++;
            }
        }

        // Update Statistik Variables
        $statMenunggu = $countBelum;
        $statProses   = $countProgres;
        $statSelesai  = $countSelesai;

        // Calculate percentages
        $totalRegistered = count($allPeserta);
        if ($totalRegistered == 0) $totalRegistered = 1; 
        
        $percentSelesai = ($statSelesai / $totalRegistered) * 100;
        $percentProses  = ($statProses / $totalRegistered) * 100;
        $percentBelum   = 100 - ($percentSelesai + $percentProses);

        $progressPercent = round($percentSelesai, 1);

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
                      // ------------------------------------------------------------------
                      // HITUNG STATISTIK PERBANDINGAN (My Count vs Others)
                      // ------------------------------------------------------------------
                      $myJuriId = $juriData['id'];
                      $myGrupMateriId = $juriData['id_grup_materi'];
    
                      // 1. Jumlah yang SAYA nilai (Distinct Peserta)
                      $myGradedCount = $this->nilaiModel->where('id_juri', $myJuriId)
                                                        ->where('tahun_ajaran', $tahunAjaran)
                                                        ->select('no_peserta')
                                                        ->distinct()
                                                        ->countAllResults();
    
                      // 2. Jumlah yang JURI LAIN nilai (dalam Grup Materi yang sama)
                      $otherJuris = $this->juriModel->where('id_grup_materi', $myGrupMateriId)
                                                    ->where('id !=', $myJuriId)
                                                    ->findAll();
                      
                      $othersGradedCount = 0;
                      if (!empty($otherJuris)) {
                          $otherJuriIds = array_column($otherJuris, 'id');
                          $othersGradedCount = $this->nilaiModel->whereIn('id_juri', $otherJuriIds)
                                                                ->where('tahun_ajaran', $tahunAjaran)
                                                                ->select('no_peserta')
                                                                ->distinct()
                                                                ->countAllResults();
                      }
    
                      $juriComparison = [
                          'my_count' => $myGradedCount,
                          'others_count' => $othersGradedCount,
                          'my_name' => $juriData['nama_juri'],
                          'others_label' => (count($otherJuris) > 0) ? 'Juri Lain' : '-'
                      ];
                      // ------------------------------------------------------------------

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
            'juriComparison' => $juriComparison ?? null, // Data Statistik Juri
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
