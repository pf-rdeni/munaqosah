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
                        ->join('tbl_munaqosah_siswa', 'tbl_munaqosah_siswa.nisn = tbl_munaqosah_peserta.nisn AND tbl_munaqosah_siswa.tahun_ajaran = tbl_munaqosah_peserta.tahun_ajaran')
                        ->where('tbl_munaqosah_peserta.tahun_ajaran', $tahunAjaran)
                        ->where('tbl_munaqosah_siswa.jenis_kelamin', 'L')
                        ->countAllResults();

        $genderP = $this->pesertaModel->select('tbl_munaqosah_peserta.id')
                        ->join('tbl_munaqosah_siswa', 'tbl_munaqosah_siswa.nisn = tbl_munaqosah_peserta.nisn AND tbl_munaqosah_siswa.tahun_ajaran = tbl_munaqosah_peserta.tahun_ajaran')
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

        // 2. Ambil Semua Skor (termasuk info juri, sama seperti MonitoringNilai)
        $rawScores = $this->nilaiModel->where('tahun_ajaran', $tahunAjaran)->findAll();
        
        // Organize Scores: $dataScores[NoPeserta][MateriID][KriteriaID][JuriID] = true
        // Track per-juri scores to check multi-juri completeness
        $dataScores = [];
        foreach ($rawScores as $row) {
             $dataScores[$row['no_peserta']][$row['id_materi']][$row['id_kriteria']][$row['id_juri']] = true;
        }

        // 2b. Hitung jumlah juri maksimal per materi (sama seperti MonitoringNilai)
        $materiColumns = [];
        foreach ($structure as $mid => $mData) {
            $maxJuri = 1;
            foreach ($dataScores as $np => $mats) {
                if (isset($mats[$mid])) {
                    foreach ($mats[$mid] as $kid => $jvals) {
                        $c = count($jvals);
                        if ($c > $maxJuri) $maxJuri = $c;
                    }
                }
            }
            $materiColumns[$mid] = $maxJuri;
        }

        // 3. Ambil Semua Peserta Aktif
        $allPeserta = $this->pesertaModel->where('tahun_ajaran', $tahunAjaran)->findAll();

        // 4. Hitung Status per Peserta (logika sama persis dengan MonitoringNilai)
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

                $expectedJuri = $materiColumns[$mid] ?? 1;

                if (isset($dataScores[$np][$mid])) {
                    $mScores = $dataScores[$np][$mid];
                    foreach ($mData['kriteria'] as $k) {
                        $kid = $k['id'];
                        $vals = $mScores[$kid] ?? [];
                        if (empty($vals)) {
                            $isComplete = false;
                        } else {
                            $hasAnyScore = true;
                            // Cek apakah semua juri yang diharapkan sudah menilai
                            if (count($vals) < $expectedJuri) {
                                $isComplete = false;
                            }
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

                 // --- Monitoring Grup Juri untuk Dashboard ---
                 if (!empty($juriData['id_grup_juri'])) {
                     $grupJuriId = $juriData['id_grup_juri'];

                     // Ambil juri dalam grup yang sama
                     $grupJuris = $this->juriModel
                         ->select('tbl_munaqosah_juri.*, gm.nama_grup_materi')
                         ->join('tbl_munaqosah_grup_materi gm', 'gm.id = tbl_munaqosah_juri.id_grup_materi', 'left')
                         ->where('tbl_munaqosah_juri.id_grup_juri', $grupJuriId)
                         ->orderBy('tbl_munaqosah_juri.nama_juri', 'ASC')
                         ->findAll();

                     // Ambil skor unik per juri per peserta
                     $grupScores = $this->nilaiModel
                         ->distinct()
                         ->select('no_peserta, id_juri, id_grup_juri')
                         ->where('tahun_ajaran', $tahunAjaran)
                         ->where('id_grup_juri', $grupJuriId)
                         ->findAll();

                     $juriPesertaMap = [];
                     foreach ($grupScores as $gs) {
                         $juriPesertaMap[$gs['id_juri']][$gs['no_peserta']] = true;
                     }

                     // Kumpulkan semua peserta
                     $allPesertaInGrup = [];
                     foreach ($grupJuris as $gj) {
                         if (isset($juriPesertaMap[$gj['id']])) {
                             foreach (array_keys($juriPesertaMap[$gj['id']]) as $np) {
                                 $allPesertaInGrup[$np] = true;
                             }
                         }
                     }

                     // Peserta name map
                     $db = \Config\Database::connect();
                     $pesertaInfo = $db->table('tbl_munaqosah_peserta p')
                         ->select('p.no_peserta, s.nama_siswa')
                         ->join('tbl_munaqosah_siswa s', 's.nisn = p.nisn AND s.tahun_ajaran = p.tahun_ajaran', 'left')
                         ->where('p.tahun_ajaran', $tahunAjaran)
                         ->get()->getResultArray();
                     $pesertaNameMap = [];
                     foreach ($pesertaInfo as $pi) {
                         $pesertaNameMap[$pi['no_peserta']] = $pi['nama_siswa'];
                     }

                     $pesertaList = array_keys($allPesertaInGrup);
                     sort($pesertaList);

                     $matrix = [];
                     $gjCountLengkap = 0;
                     $gjCountBelum = 0;
                     foreach ($pesertaList as $np) {
                         $row = [];
                         $isComplete = true;
                         foreach ($grupJuris as $gj) {
                             $sudahNilai = isset($juriPesertaMap[$gj['id']][$np]);
                             $row[$gj['id']] = $sudahNilai;
                             if (!$sudahNilai) $isComplete = false;
                         }
                         $matrix[$np] = [
                             'scores' => $row,
                             'complete' => $isComplete,
                             'nama' => $pesertaNameMap[$np] ?? '-'
                         ];
                         if ($isComplete) $gjCountLengkap++;
                         else $gjCountBelum++;
                     }

                     $grupJuriMonitoring = [
                         'grupId' => $grupJuriId,
                         'juris' => $grupJuris,
                         'matrix' => $matrix,
                         'totalPeserta' => count($pesertaList),
                         'countLengkap' => $gjCountLengkap,
                         'countBelum' => $gjCountBelum,
                         'grupMateriName' => $grupJuris[0]['nama_grup_materi'] ?? '-',
                     ];
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
            'tahunAjaran'  => $tahunAjaran,
            'availableTahunAjaran' => $this->getAvailableTahunAjaran(),
            'listDinilai'  => $listDinilai,
            'rubrikData'   => $rubrikData, // Pass dynamic rubric
            'predikats'    => $predikats,  // Pass predikats
            'juriComparison' => $juriComparison ?? null, // Data Statistik Juri
            'grupJuriMonitoring' => $grupJuriMonitoring ?? null, // Monitoring Grup Juri
            'statistik'    => [
                'totalSiswa'      => $this->siswaModel->countSiswaByStatus('aktif', $tahunAjaran),
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
