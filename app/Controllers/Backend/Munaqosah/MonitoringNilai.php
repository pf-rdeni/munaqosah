<?php

namespace App\Controllers\Backend\Munaqosah;

use App\Controllers\BaseController;
use App\Models\Munaqosah\PesertaModel;
use App\Models\Munaqosah\NilaiUjianModel;
use App\Models\Munaqosah\MateriModel;
use App\Models\Munaqosah\KriteriaModel;
use App\Models\Munaqosah\GrupMateriModel;
use App\Models\Munaqosah\JuriModel;

class MonitoringNilai extends BaseController
{
    protected $pesertaModel;
    protected $nilaiModel;
    protected $materiModel;
    protected $kriteriaModel;
    protected $grupMateriModel;
    protected $juriModel;

    public function __construct()
    {
        $this->pesertaModel = new PesertaModel();
        $this->nilaiModel = new NilaiUjianModel();
        $this->materiModel = new MateriModel();
        $this->kriteriaModel = new KriteriaModel();
        $this->grupMateriModel = new GrupMateriModel();
        $this->juriModel = new JuriModel(); 
    }

    public function index()
    {
        if (!$this->isLoggedIn()) {
            return redirect()->to('/login');
        }

        if (!in_groups(['admin', 'operator', 'kepala', 'panitia'])) {
            return redirect()->to('/backend/dashboard')->with('error', 'Akses ditolak.');
        }

        $tahunAjaran = $this->getTahunAjaran();

        // 1. PERSIAPAN DATA: MATERI & KRITERIA
        // Ambil Semua Materi beserta Logika Grupnya
        $allMateri = $this->materiModel->select('tbl_munaqosah_materi_ujian.*, gm.kondisional_set, gm.nama_grup_materi')
                                       ->join('tbl_munaqosah_grup_materi gm', 'gm.id = tbl_munaqosah_materi_ujian.id_grup_materi', 'left')
                                       ->orderBy('tbl_munaqosah_materi_ujian.id', 'ASC')
                                       ->findAll();

        $structure = []; // Struktur Hirarkis: [MateriID] => ['info' => ..., 'kriteria' => [...]]
        
        foreach ($allMateri as $m) {
            $mId = $m['id'];
            $kriteria = $this->kriteriaModel->where('id_materi', $mId)->orderBy('urutan', 'ASC')->findAll();
            
            $structure[$mId] = [
                'info' => $m,
                'kriteria' => $kriteria
            ];
        }

        // 2. PERSIAPAN DATA: PESERTA
        $pesertaList = $this->pesertaModel
            ->select('tbl_munaqosah_peserta.*, s.nama_siswa, s.jenis_kelamin')
            ->join('tbl_munaqosah_siswa s', 's.nisn = tbl_munaqosah_peserta.nisn', 'inner')
            ->where('tahun_ajaran', $tahunAjaran)
            ->orderBy('no_peserta', 'ASC')
            ->findAll();

        // 3. PERSIAPAN DATA: NILAI (Hirarkis)
        // Ambil Nilai Mentah dengan Info Juri
        $rawScores = $this->nilaiModel->select('tbl_munaqosah_nilai_ujian.*, j.nama_juri, j.id as real_juri_id')
                                      ->join('tbl_munaqosah_juri j', 'j.id = tbl_munaqosah_nilai_ujian.id_juri', 'left')
                                      ->where('tahun_ajaran', $tahunAjaran)
                                      ->findAll();

        // Atur Nilai: $dataScores[NoPeserta][MateriID][KriteriaID][JuriID] = Nilai
        $dataScores = [];
        $juriMap = []; // [MateriID][KriteriaID] => [JuriID1, JuriID2] (Untuk mendeteksi berapa banyak juri yang menilai kriteria ini)
        
        foreach ($rawScores as $row) {
            $np = $row['no_peserta'];
            $mid = $row['id_materi'];
            $kid = $row['id_kriteria'];
            $jid = $row['id_juri'];
            
            $dataScores[$np][$mid][$kid][$jid] = $row['nilai'];
        }

        // 4. PERHITUNGAN & KONFIGURASI KOLOM
        // Tentukan Struktur Kolom yang benar per Materi.
        // Aturan: Jika ADA Peserta yang memiliki > 1 Juri untuk Materi tertentu, tampilkan Multi-Kolom.
        
        $finalData = [];
        
        foreach ($pesertaList as $p) {
            $np = $p['no_peserta'];
            $grandTotal = 0;
            
            $isComplete = true; // Asumsikan lengkap pada awalnya
            
            foreach ($structure as $mid => $mData) {
                // Logika Perhitungan per Materi
                $mInfo = $mData['info'];
                $isPengurangan = ($mInfo['kondisional_set'] == 'nilai_pengurangan');
                
                $materiSubtotal = 0;
                $kriteriaResults = [];
                
                if (isset($dataScores[$np][$mid])) {
                    $mScores = $dataScores[$np][$mid]; // [kid][jid] => nilai
                    
                    foreach ($mData['kriteria'] as $k) {
                        $kid = $k['id'];
                        $vals = $mScores[$kid] ?? []; // Daftar Nilai Juri
                                                
                        if (empty($vals)) {
                            $isComplete = false; // Nilai hilang
                            $kriteriaResults[$kid] = ['avg' => 0, 'bb' => 0, 'raw' => []];
                            continue;
                        }

                        // Hitung Rata-Rata
                        $count = count($vals);
                        $sum = array_sum($vals);
                        $avg = $count > 0 ? $sum / $count : 0;
                        
                        // Terapkan Bobot
                        // Aturan: Jika Pengurangan, TANPA Bobot. Nilai hanya Nilai itu sendiri (Rata-Rata).
                        $bobot = $k['bobot'];
                        $finalVal = 0;
                        
                        if ($isPengurangan) {
                            $finalVal = $avg; 
                        } else {
                            $finalVal = $avg * ($bobot / 100);
                        }
                        
                        $materiSubtotal += $finalVal;
                        
                        $kriteriaResults[$kid] = [
                            'avg' => $avg,
                            'bb' => $finalVal,
                            'raw' => array_values($vals) 
                        ];
                    }
                } else {
                    // Tidak ada nilai untuk Materi ini sama sekali
                    $isComplete = false;
                    // Isi struktur kosong untuk tampilan
                    foreach ($mData['kriteria'] as $k) {
                        $kriteriaResults[$k['id']] = ['avg' => 0, 'bb' => 0, 'raw' => []];
                    }
                }

                if ($isPengurangan && count($mData['kriteria']) > 0) {
                     // Rata-Rata Total (Khusus Pengurangan)
                     $materiSubtotal = $materiSubtotal / count($mData['kriteria']);
                }
                
                $finalData[$np][$mid] = [
                    'subtotal' => $materiSubtotal,
                    'details' => $kriteriaResults
                ];
                
                $grandTotal += $materiSubtotal;
            }
            
            $finalData[$np]['grand_total'] = $grandTotal;
            $finalData[$np]['rata_rata'] = count($structure) > 0 ? $grandTotal / count($structure) : 0;
            
            // Logika Status
            if (!$isComplete) {
                $finalData[$np]['status'] = 'PROGRES';
            } else {
                $finalData[$np]['status'] = ($finalData[$np]['rata_rata'] >= 65) ? 'LULUS' : 'TDK LULUS';
            }
        }

        // Tentukan Jumlah Juri Maksimal per Materi (untuk render Header)
        // Kita iterasi semua peserta untuk mencari jumlah juri maksimal
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

        $data = [
            'title'      => 'Monitoring Nilai',
            'pageTitle'  => 'Monitoring Nilai Munaqosah',
            'breadcrumb' => [
                ['title' => 'Home', 'url' => '/backend/dashboard'],
                ['title' => 'Monitoring Nilai', 'url' => ''],
            ],
            'user'       => $this->getCurrentUser(),
            'structure'  => $structure, // Materi & Kriteria
            'pesertaList'=> $pesertaList,
            'finalData'  => $finalData,
            'materiColumns' => $materiColumns // Kolom Maksimal per Materi
        ];

        return view('backend/monitoring/nilai/index', $data);
    }
}
