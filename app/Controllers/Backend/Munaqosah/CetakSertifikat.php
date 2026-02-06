<?php

namespace App\Controllers\Backend\Munaqosah;

use App\Controllers\BaseController;
use App\Models\Munaqosah\PesertaModel;
use App\Models\Munaqosah\NilaiUjianModel;
use App\Models\Munaqosah\MateriModel;
use App\Models\Munaqosah\KriteriaModel;
use App\Models\Munaqosah\GrupMateriModel;
use App\Models\Munaqosah\PredikatModel;
use App\Models\Backend\SertifikatTemplateModel;
use App\Models\Backend\SertifikatFieldModel;
use App\Helpers\CertificateGenerator;
use ZipStream\ZipStream;

class CetakSertifikat extends BaseController
{
    protected $pesertaModel;
    protected $nilaiModel;
    protected $materiModel;
    protected $kriteriaModel;
    protected $grupMateriModel;
    protected $templateModel;
    protected $fieldModel;
    protected $predikatModel;

    public function __construct()
    {
        $this->pesertaModel = new PesertaModel();
        $this->nilaiModel = new NilaiUjianModel();
        $this->materiModel = new MateriModel();
        $this->kriteriaModel = new KriteriaModel();
        $this->grupMateriModel = new GrupMateriModel();
        $this->templateModel = new SertifikatTemplateModel();
        $this->fieldModel = new SertifikatFieldModel();
        $this->predikatModel = new PredikatModel();
    }

    public function index()
    {
        if (!$this->isLoggedIn()) return redirect()->to('/login');
        if (!in_groups(['admin', 'operator', 'kepala', 'panitia'])) return redirect()->to('/backend/dashboard');

        $tahunAjaran = $this->getTahunAjaran();

        // 1. Get Materi Structure
        $allMateri = $this->materiModel->select('tbl_munaqosah_materi_ujian.*, gm.kondisional_set')
                                       ->join('tbl_munaqosah_grup_materi gm', 'gm.id = tbl_munaqosah_materi_ujian.id_grup_materi', 'left')
                                       ->orderBy('tbl_munaqosah_materi_ujian.id', 'ASC')
                                       ->findAll();

        $structure = [];
        foreach ($allMateri as $m) {
            $mId = $m['id'];
            $kriteria = $this->kriteriaModel->where('id_materi', $mId)->findAll();
            $structure[$mId] = ['info' => $m, 'kriteria' => $kriteria];
        }

        // 2. Get Peserta
        $pesertaList = $this->pesertaModel
            ->select('tbl_munaqosah_peserta.*, s.nama_siswa, s.nisn as real_nisn')
            ->join('tbl_munaqosah_siswa s', 's.nisn = tbl_munaqosah_peserta.nisn AND s.tahun_ajaran = tbl_munaqosah_peserta.tahun_ajaran', 'inner')
            ->where('tbl_munaqosah_peserta.tahun_ajaran', $tahunAjaran)
            ->orderBy('no_peserta', 'ASC')
            ->findAll();

        // 3. Get Scores
        $rawScores = $this->nilaiModel->where('tahun_ajaran', $tahunAjaran)->findAll();
        
        $dataScores = [];
        foreach ($rawScores as $row) {
            $dataScores[$row['no_peserta']][$row['id_materi']][$row['id_kriteria']][$row['id_juri']] = $row['nilai'];
        }

        // Global Predikats
        $globalPredikats = $this->predikatModel->getByGrup(null);

        // 4. Calculate Final Scores
        $finalData = [];
        foreach ($pesertaList as $p) {
            $np = $p['no_peserta'];
            $grandTotal = 0;
            $isComplete = true;

            foreach ($structure as $mid => $mData) {
                // ... (existing inner loop logic for scores) ...
                $mInfo = $mData['info'];
                $isPengurangan = ($mInfo['kondisional_set'] == 'nilai_pengurangan');
                $materiSubtotal = 0;

                if (isset($dataScores[$np][$mid])) {
                    $mScores = $dataScores[$np][$mid];
                    
                    foreach ($mData['kriteria'] as $k) {
                        $kid = $k['id'];
                        $vals = $mScores[$kid] ?? [];

                        if (empty($vals)) {
                           $isComplete = false; 
                           continue;
                        }

                        $avg = array_sum($vals) / count($vals);
                        
                        // Weighted
                        if ($isPengurangan) {
                            $materiSubtotal += $avg;
                        } else {
                            $materiSubtotal += $avg * ($k['bobot'] / 100);
                        }
                    }
                } else {
                    $isComplete = false;
                }

                if ($isPengurangan && count($mData['kriteria']) > 0) {
                     $materiSubtotal = $materiSubtotal / count($mData['kriteria']);
                }

                $finalData[$np][$mid] = $materiSubtotal;
                $grandTotal += $materiSubtotal;
            }

            $finalData[$np]['grand_total'] = $grandTotal;
            $avgScore = count($structure) > 0 ? $grandTotal / count($structure) : 0;
            $finalData[$np]['rata_rata'] = $avgScore;
            
            // Calculate Nilai Huruf
        // Calculate Nilai Huruf & Predikat Label
        $nilaiHuruf = '-';
        $predikatLabel = '-';
        foreach ($globalPredikats as $pred) {
            if ($avgScore >= $pred['min_nilai'] && $avgScore <= $pred['max_nilai']) {
                $nilaiHuruf = $pred['predikat_huruf'] ?? '-';
                $predikatLabel = $pred['nama_predikat'];
                break;
            }
        }
        $finalData[$np]['nilai_huruf'] = $nilaiHuruf;
        $finalData[$np]['predikat_label'] = $predikatLabel;
            
            // Status & Ranking helper
            $finalData[$np]['status'] = (!$isComplete) ? 'BELUM LENGKAP' : (($finalData[$np]['rata_rata'] >= 65) ? 'LULUS' : 'TDK LULUS');
            $finalData[$np]['is_complete'] = $isComplete;
        }

        // 5. Ranking Logic
        // Filters only Complete & Passed for Ranking? Or all? Usually all completed.
        // Let's rank everyone based on Grand Total DESC
        
        // Convert to array for sorting
        $sortable = [];
        foreach ($pesertaList as $p) {
            $np = $p['no_peserta'];
            $sortable[] = [
                'no_peserta' => $np,
                'grand_total' => $finalData[$np]['grand_total'],
                'is_complete' => $finalData[$np]['is_complete']
            ];
        }

        // Sort DESC
        usort($sortable, function($a, $b) {
            return $b['grand_total'] <=> $a['grand_total'];
        });

        // Assign Rank
        $rankMap = [];
        $currentRank = 1;
        foreach ($sortable as $s) {
            // Rank only valid for completed? 
            // For now, rank everything
            $rankMap[$s['no_peserta']] = $currentRank++;
        }

        $data = [
            'title' => 'Cetak Sertifikat',
            'pageTitle' => 'Cetak Sertifikat Munaqosah',
            'user' => $this->getCurrentUser(),
            'tahunAjaran' => $tahunAjaran,
            'availableTahunAjaran' => $this->getAvailableTahunAjaran(),
            'materiList' => $allMateri,
            'pesertaList' => $pesertaList,
            'finalData' => $finalData,
            'rankMap' => $rankMap
        ];

        return view('backend/sertifikat/cetak', $data);
    }

    public function print($pesertaId)
    {
        if (!$this->isLoggedIn()) return redirect()->to('/login');

        // 1. Get Peserta Data
        $peserta = $this->pesertaModel
            ->select('tbl_munaqosah_peserta.*, s.*, s.nisn as real_nisn, tbl_munaqosah_peserta.id as id_peserta') // Select all student data
            ->join('tbl_munaqosah_siswa s', 's.nisn = tbl_munaqosah_peserta.nisn AND s.tahun_ajaran = tbl_munaqosah_peserta.tahun_ajaran', 'inner')
            ->where('tbl_munaqosah_peserta.id', $pesertaId)
            ->first();

        if (!$peserta) {
            return redirect()->back()->with('error', 'Peserta tidak ditemukan');
        }

        // 2. Get Templates
        $templateDepan = $this->templateModel->getTemplateByHalaman('depan');
        $templateBelakang = $this->templateModel->getTemplateByHalaman('belakang');

        if (!$templateDepan || !$templateBelakang) {
            return redirect()->back()->with('error', 'Template Sertifikat belum lengkap (Depan & Belakang wajib ada)');
        }

        // 3. Get Scores
        $rawScores = $this->nilaiModel
            ->where('no_peserta', $peserta['no_peserta'])
            ->where('tahun_ajaran', $peserta['tahun_ajaran'])
            ->findAll();

        // 4. Calculate Final Scores (Simplified Logic for Print)
        // We reuse the logic but only for this specific peserta
        // Better to extract this logic to a private method or helper, but for now inline is safe.
        
        $allMateri = $this->materiModel->select('tbl_munaqosah_materi_ujian.*, gm.kondisional_set')
            ->join('tbl_munaqosah_grup_materi gm', 'gm.id = tbl_munaqosah_materi_ujian.id_grup_materi', 'left')
            ->orderBy('tbl_munaqosah_materi_ujian.id', 'ASC')
            ->findAll();

        $scores = [];
        $totalScore = 0;
        $countMateri = 0;

        // Fetch Global Predikats
        $globalPredikats = $this->predikatModel->getByGrup(null);

        foreach ($allMateri as $m) {
            $mId = $m['id'];
            $kriteria = $this->kriteriaModel->where('id_materi', $mId)->findAll();
            $materiSubtotal = 0;
            $hasScore = false;
            
            $mInfo = $m;
            $isPengurangan = ($m['kondisional_set'] == 'nilai_pengurangan');

            // Find scores for this materi
            // Filter rawScores in memory
            $mScores = array_filter($rawScores, function($row) use ($mId) {
                return $row['id_materi'] == $mId;
            });

            if (!empty($mScores)) {
                 $hasScore = true;
                 
                 // Group by Kriteria
                 $kriteriaScores = [];
                 foreach ($mScores as $row) {
                     $kriteriaScores[$row['id_kriteria']][$row['id_juri']] = $row['nilai'];
                 }

                 foreach ($kriteria as $k) {
                     $kid = $k['id'];
                     $vals = $kriteriaScores[$kid] ?? []; // Daftar Nilai Juri
                     
                     if (empty($vals)) {
                        // If value is empty, we act like it's 0 contribution?
                        // In Monitoring: continue (subtotal unchanged).
                        continue;
                     }

                     // Hitung Rata-Rata (Matches Monitoring)
                     $avg = array_sum($vals) / count($vals);
                     
                     // Terapkan Bobot (Matches Monitoring)
                     if ($isPengurangan) {
                        $materiSubtotal += $avg;
                     } else {
                        $materiSubtotal += $avg * ($k['bobot'] / 100);
                     }
                 }
                 
                 if ($isPengurangan && count($kriteria) > 0) {
                     $materiSubtotal = $materiSubtotal / count($kriteria);
                 }
            }

            // Ensure distinct handling for empty scores vs zero scores
            if ($hasScore || !$isPengurangan) { 
                 // Calculate Grade Logic
                 $gradeLetter = '-';
                 $usePredikats = $globalPredikats;
                 
                 // TODO: If we want per-materi group predicates, we need to fetch them.
                 // For now, request implies we use database predicates. Assuming Global for all subjects unless specified?
                 // User said "ambil dari databse tabe predikat". Usually subjects follow global unless specific.
                 // Let's rely on Global for now as per previous context "ambil yang gelobal".
                 // "penentuan rang A, B dan lainya melihat dari tabel predikat rang tertinggi adalah A dan mengikuti rang ke bawahnya, ambil yang gelobal" -> confirmed global.
                 
                 foreach ($globalPredikats as $pred) {
                    // Cast to float to ensure numeric comparison
                    $score = (float)$materiSubtotal;
                    $minNilai = (float)$pred['min_nilai'];
                    $maxNilai = (float)$pred['max_nilai'];
                    
                    if ($score >= $minNilai && $score <= $maxNilai) {
                        // Use predikat_huruf if available, otherwise extract first letter from nama_predikat
                        $gradeLetter = $pred['predikat_huruf'] ?? null;
                        if (empty($gradeLetter) && !empty($pred['nama_predikat'])) {
                            // Fallback: Extract first letter from nama_predikat
                            $gradeLetter = strtoupper(substr($pred['nama_predikat'], 0, 1));
                        }
                        $gradeLetter = $gradeLetter ?: '-';
                        break;
                    }
                 }

                 $scores[$m['nama_materi']] = [
                    'nilai' => $materiSubtotal,
                    'huruf' => $gradeLetter
                 ];
                 $totalScore += $materiSubtotal;
                 $countMateri++; 
            }
        }
        
        // Use structure count for average division
        $divider = count($allMateri) > 0 ? count($allMateri) : 1;
        $avgScore = $totalScore / $divider;

        $scoreData = [
            'nama_siswa' => $peserta['nama_siswa'],
            'no_peserta' => $peserta['no_peserta'],
            'tahun_ajaran' => $peserta['tahun_ajaran'],
            'scores' => $scores,
            'total' => $totalScore,
            'avg' => $avgScore,
            'nilai_huruf' => $this->getPredikatByScore($avgScore, $globalPredikats, 'predikat_huruf')
        ];

        // 5. Prepare Front Page Data Mapping
        // Map fields to data
        $frontFields = $this->fieldModel->getFieldsByTemplate($templateDepan['id']);
        $backFields = $this->fieldModel->getFieldsByTemplate($templateBelakang['id']);
        
        // Dynamic Data Binding
        $generatorData = [
             'nama_peserta' => $peserta['nama_siswa'],
             'nomor_peserta' => $peserta['no_peserta'],
             'nisn' => $peserta['real_nisn'], // real_nisn alias from join
             'nis' => $peserta['nis'] ?? '-', // Fetch from s.*
             'tahun_ajaran' => $peserta['tahun_ajaran'],
             'tempat_lahir' => $peserta['tempat_lahir'],
             'tanggal_lahir' => $peserta['tanggal_lahir'], // As is, or format it?
             'jenis_kelamin' => $peserta['jenis_kelamin'],
             'nama_ayah' => $peserta['nama_ayah'],
             'alamat' => $peserta['alamat'],
             'nama_sekolah' => 'SDIT AN-NAHL', // Hardcoded or from settings?
             'predikat' => $this->getPredikatByScore($avgScore, $globalPredikats, 'nama_predikat'),
             'nilai_huruf' => $this->getPredikatByScore($avgScore, $globalPredikats, 'predikat_huruf'),
             'nilai_rata_rata' => number_format($avgScore, 1),
             'tanggal_terbit' => date('d F Y'), // Param?
             'nomor_sertifikat' => 'SERT/'.date('Y').'/'.str_pad($peserta['id_peserta'], 3, '0', STR_PAD_LEFT), // Dummy logic
             'kepala_sekolah' => 'Nama Kepala Sekolah', // Should be from Settings
             'nip_kepala' => '-',
             // Images
             'qr_code' => '', // TODO: Generate QR
             'foto_peserta' => FCPATH . ($peserta['foto'] ?? 'assets/img/default.png'), // Handle if path stored in DB
        ];

        // 6. Generate
        $generator = new CertificateGenerator($templateDepan, $frontFields);
        $generator->setData($generatorData); // Data for Front Page Fields
        
        $filename = 'Sertifikat_' . $peserta['nama_siswa'] . '.pdf';
        
        $combinedData = array_merge($scoreData, $generatorData);
        
        $generator->generateWithBackPage($templateBelakang, $backFields, $combinedData)
                  ->stream($filename, ['Attachment' => 0]); // 0 = Inline Preview
    }

    public function printBatch()
    {
        if (!$this->isLoggedIn()) return redirect()->to('/login');

        // Increase limits for batch processing
        ini_set('memory_limit', '1024M');
        set_time_limit(600);

        $tahunAjaran = $this->getTahunAjaran();

        // 1. Get Templates
        $templateDepan = $this->templateModel->getTemplateByHalaman('depan');
        $templateBelakang = $this->templateModel->getTemplateByHalaman('belakang');

        if (!$templateDepan || !$templateBelakang) {
            return redirect()->back()->with('error', 'Template Sertifikat belum lengkap');
        }

        $frontFields = $this->fieldModel->getFieldsByTemplate($templateDepan['id']);
        $backFields = $this->fieldModel->getFieldsByTemplate($templateBelakang['id']);

        // 2. Get Data (Materi, Peserta, Scores)
        
        // Materi
        $allMateri = $this->materiModel->select('tbl_munaqosah_materi_ujian.*, gm.kondisional_set')
                                       ->join('tbl_munaqosah_grup_materi gm', 'gm.id = tbl_munaqosah_materi_ujian.id_grup_materi', 'left')
                                       ->orderBy('tbl_munaqosah_materi_ujian.id', 'ASC')
                                       ->findAll();
        // Kriteria Cache
        $kriteriaCache = [];
        foreach ($allMateri as $m) {
            $kriteriaCache[$m['id']] = $this->kriteriaModel->where('id_materi', $m['id'])->findAll();
        }

        // Peserta
        $pesertaList = $this->pesertaModel
            ->select('tbl_munaqosah_peserta.*, s.*, s.nisn as real_nisn, tbl_munaqosah_peserta.id as id_peserta')
            ->join('tbl_munaqosah_siswa s', 's.nisn = tbl_munaqosah_peserta.nisn AND s.tahun_ajaran = tbl_munaqosah_peserta.tahun_ajaran', 'inner')
            ->where('tbl_munaqosah_peserta.tahun_ajaran', $tahunAjaran)
            ->orderBy('no_peserta', 'ASC')
            ->findAll();

        // Scores
        $rawScores = $this->nilaiModel->where('tahun_ajaran', $tahunAjaran)->findAll();
        $dataScores = [];
        foreach ($rawScores as $row) {
            $dataScores[$row['no_peserta']][$row['id_materi']][$row['id_kriteria']][$row['id_juri']] = $row['nilai'];
        }

        // 3. Init Zip (ZipStream v3)
        $zipName = 'Sertifikat_Munaqosah_'.date('YmdHis').'.zip';
        
        $zip = new ZipStream(
            outputName: $zipName,
            sendHttpHeaders: true
        );

        $countProcessed = 0;
        
        // Fetch Global Predikats
        $globalPredikats = $this->predikatModel->getByGrup(null);

        foreach ($pesertaList as $p) {
            $np = $p['no_peserta'];
            
            // Calculate Scores for this Peserta
            $scores = [];
            $totalScore = 0;
            $isComplete = true;
            
            foreach ($allMateri as $m) {
                $mId = $m['id'];
                $isPengurangan = ($m['kondisional_set'] == 'nilai_pengurangan');
                $materiSubtotal = 0;
                $hasScore = false;
                
                if (isset($dataScores[$np][$mId])) {
                     $mScores = $dataScores[$np][$mId];
                     $kriteria = $kriteriaCache[$mId];
                     
                     foreach ($kriteria as $k) {
                         $kid = $k['id'];
                         $vals = $mScores[$kid] ?? [];
                         
                         if (empty($vals)) {
                             $isComplete = false; 
                             continue;
                         }

                         $avg = array_sum($vals) / count($vals);
                         
                         if ($isPengurangan) {
                             $materiSubtotal += $avg;
                         } else {
                             $materiSubtotal += $avg * ($k['bobot'] / 100);
                         }
                         $hasScore = true;
                     }
                     
                     if ($isPengurangan && count($kriteria) > 0) {
                         $materiSubtotal = $materiSubtotal / count($kriteria);
                     }
                } else {
                    $isComplete = false;
                }

                if ($hasScore || !$isPengurangan) {
                    // Calculate Grade Logic (Global)
                    $gradeLetter = '-';
                    foreach ($globalPredikats as $pred) {
                        // Cast to float to ensure numeric comparison
                        $score = (float)$materiSubtotal;
                        $minNilai = (float)$pred['min_nilai'];
                        $maxNilai = (float)$pred['max_nilai'];
                        
                        if ($score >= $minNilai && $score <= $maxNilai) {
                            // Use predikat_huruf if available, otherwise extract first letter from nama_predikat
                            $gradeLetter = $pred['predikat_huruf'] ?? null;
                            if (empty($gradeLetter) && !empty($pred['nama_predikat'])) {
                                // Fallback: Extract first letter from nama_predikat
                                $gradeLetter = strtoupper(substr($pred['nama_predikat'], 0, 1));
                            }
                            $gradeLetter = $gradeLetter ?: '-';
                            break;
                        }
                    }

                    $scores[$m['nama_materi']] = [
                        'nilai' => $materiSubtotal,
                        'huruf' => $gradeLetter
                    ];
                    $totalScore += $materiSubtotal;
                }
            }
            
            // Avg
            $avgScore = count($allMateri) > 0 ? $totalScore / count($allMateri) : 0;
            $status = (!$isComplete) ? 'BELUM' : (($avgScore >= 65) ? 'LULUS' : 'TDK');

            // Only Process LULUS
            if ($isComplete && $status == 'LULUS') {
                
                $scoreData = [
                    'nama_siswa' => $p['nama_siswa'],
                    'no_peserta' => $p['no_peserta'],
                    'tahun_ajaran' => $p['tahun_ajaran'],
                    'scores' => $scores, // Note: scores keys might need to match template requirements
                    'total' => $totalScore,
                    'avg' => $avgScore
                ];
                
                // Generator Data
                $generatorData = [
                     'nama_peserta' => $p['nama_siswa'],
                     'nomor_peserta' => $p['no_peserta'],
                     'nisn' => $p['real_nisn'],
                     'nis' => $p['nis'] ?? '-',
                     'tahun_ajaran' => $p['tahun_ajaran'],
                     'tempat_lahir' => $p['tempat_lahir'],
                     'tanggal_lahir' => $p['tanggal_lahir'],
                     'jenis_kelamin' => $p['jenis_kelamin'],
                     'nama_ayah' => $p['nama_ayah'],
                     'alamat' => $p['alamat'],
                     'nama_sekolah' => 'SDIT AN-NAHL',
                     'predikat' => $this->getPredikatByScore($avgScore, $globalPredikats, 'nama_predikat'),
                     'nilai_huruf' => $this->getPredikatByScore($avgScore, $globalPredikats, 'predikat_huruf'),
                     'nilai_rata_rata' => number_format($avgScore, 1),
                     'tanggal_terbit' => date('d F Y'),
                     'nomor_sertifikat' => 'SERT/'.date('Y').'/'.str_pad($p['id_peserta'], 3, '0', STR_PAD_LEFT),
                     'kepala_sekolah' => 'Nama Kepala Sekolah', // TODO: Setting
                     'nip_kepala' => '-',
                     'qr_code' => '',
                     'foto_peserta' => FCPATH . ($p['foto'] ?? 'assets/img/default.png'),
                ];
                
                $generator = new CertificateGenerator($templateDepan, $frontFields);
                $generator->setData($generatorData);
                
                $combinedData = array_merge($scoreData, $generatorData);
                
                // Get PDF String
                // Pass S for String return
                $pdfContent = $generator->generateWithBackPage($templateBelakang, $backFields, $combinedData)
                                        ->output(null, 'S');
                
                // Add to Zip
                $fileName = 'Sertifikat_' . preg_replace('/[^A-Za-z0-9_\-]/', '_', $p['nama_siswa']) . '.pdf';
                $zip->addFile($fileName, $pdfContent);
                
                $countProcessed++;
            }
        }
        
        $zip->finish();
    }

    private function getPredikatByScore($score, $predikats, $field = 'nama_predikat')
    {
        foreach ($predikats as $p) {
            if ($score >= $p['min_nilai'] && $score <= $p['max_nilai']) {
                return $p[$field] ?? '-';
            }
        }
        return '-';
    }
}
