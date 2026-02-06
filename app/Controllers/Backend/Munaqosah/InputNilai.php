<?php

namespace App\Controllers\Backend\Munaqosah;

use App\Controllers\BaseController;
use App\Models\Munaqosah\PesertaModel;
use App\Models\Munaqosah\NilaiUjianModel;
use App\Models\Munaqosah\JuriModel;
use App\Models\Munaqosah\JuriKriteriaModel;
use App\Models\Munaqosah\KriteriaModel;
use App\Models\Munaqosah\TblAlquranModel;
use App\Models\Munaqosah\AntrianModel;

class InputNilai extends BaseController
{
    protected $pesertaModel;
    protected $nilaiModel;
    protected $juriModel;
    protected $juriKriteriaModel;
    protected $kriteriaModel;
    protected $alquranModel;

    public function __construct()
    {
        $this->pesertaModel = new PesertaModel();
        $this->nilaiModel = new NilaiUjianModel();
        $this->juriModel = new JuriModel();
        $this->juriKriteriaModel = new JuriKriteriaModel();
        $this->kriteriaModel = new KriteriaModel();
        $this->alquranModel = new TblAlquranModel();
    }

    public function index()
    {
        if (!$this->isLoggedIn()) {
            return redirect()->to('/login');
        }

        if (!$this->inGroups('juri')) {
            return redirect()->to('/backend/dashboard')->with('error', 'Akses ditolak. Menu ini hanya untuk Juri.');
        }

        // Cek apakah user adalah Juri
        $juri = $this->getJuriInfo();
        
        // Ambil daftar peserta yang sudah dinilai
        $listDinilai = [];
        if ($juri) {
            $tahunAjaran = $this->getTahunAjaran();
            $listDinilai = $this->nilaiModel->getPesertaDinilaiByJuri($juri['id'], $tahunAjaran);
        }

        $data = [
            'title'      => 'Input Nilai',
            'pageTitle'  => 'Input Nilai Juri',
            'breadcrumb' => [
                ['title' => 'Home', 'url' => '/backend/dashboard'],
                ['title' => 'Input Nilai', 'url' => ''],
            ],
            'user'       => $this->getCurrentUser(),
            'tahunAjaran'=> $this->getTahunAjaran(),
            'availableTahunAjaran' => $this->getAvailableTahunAjaran(),
            'juri'       => $juri,
            'listDinilai' => $listDinilai
        ];

        return view('backend/nilai/index', $data);
    }

    // Langkah 2: Muat Form Penilaian via AJAX
    public function loadForm()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setJSON(['success' => false, 'message' => 'Invalid request']);
        }

        $noPeserta = $this->request->getPost('no_peserta');
        $tahunAjaran = $this->getTahunAjaran();

        // 1. Validasi Peserta
        $peserta = $this->pesertaModel->getPesertaDetail($noPeserta, $tahunAjaran);
        if (!$peserta) {
            return $this->response->setJSON(['success' => false, 'message' => 'Peserta tidak ditemukan atau tidak aktif pada tahun ajaran ini.']);
        }

        // 2. Info Juri
        $juri = $this->getJuriInfo();
        if (!$juri) {
            return $this->response->setJSON(['success' => false, 'message' => 'Anda tidak terdaftar sebagai Juri.']);
        }

        // 3. Ambil Kriteria Penilaian Juri
        // Ambil ID Kriteria yang ditugaskan ke Juri ini
        $juriPk = $juri['id']; 
        $kriteriaIds = $this->juriKriteriaModel->getKriteriaIdsByJuri($juriPk); 

        $kriteriaList = [];
        if (!empty($kriteriaIds)) {
            $kriteriaList = $this->kriteriaModel->select('tbl_munaqosah_kriteria_materi_ujian.*, m.nilai_maksimal, m.nama_materi, m.id as materi_pk')
                                                ->join('tbl_munaqosah_materi_ujian m', 'm.id = tbl_munaqosah_kriteria_materi_ujian.id_materi', 'left')
                                                ->whereIn('tbl_munaqosah_kriteria_materi_ujian.id', $kriteriaIds)
                                                ->orderBy('tbl_munaqosah_kriteria_materi_ujian.urutan', 'ASC')
                                                ->findAll();
        } else {
            // Fallback: Ambil SEMUA Kriteria berdasarkan Grup Materi Juri
            $idGrupMateri = $juri['grup_materi_id_int'] ?? $juri['id_grup_materi'];
            
            if ($idGrupMateri) {
                // Ambil ID Materi dalam grup ini
                $materiModel = new \App\Models\Munaqosah\MateriModel();
                $materiList = $materiModel->where('id_grup_materi', $idGrupMateri)->findAll();
                
                if (!empty($materiList)) {
                    $materiIds = array_column($materiList, 'id'); 

                    // Ambil Kriteria untuk Materi-materi ini
                    $kriteriaList = $this->kriteriaModel->select('tbl_munaqosah_kriteria_materi_ujian.*, m.nilai_maksimal, m.nama_materi, m.id as materi_pk')
                                                        ->join('tbl_munaqosah_materi_ujian m', 'm.id = tbl_munaqosah_kriteria_materi_ujian.id_materi', 'left')
                                                        ->whereIn('tbl_munaqosah_kriteria_materi_ujian.id_materi', $materiIds)
                                                        ->orderBy('tbl_munaqosah_kriteria_materi_ujian.urutan', 'ASC')
                                                        ->findAll();
                }
            }
        }    

        // 4. Tentukan Items (Tabs)
        // Jika Juri adalah untuk Tahfidz (cek Grup Materi atau Nama Materi)
        $isTahfidz = false;
        // Cek Nama Grup Materi. 
        if (isset($juri['nama_grup_materi']) && (stripos($juri['nama_grup_materi'], 'Tahfidz') !== false)) {
            $isTahfidz = true;
        }

        $items = [];
        // Buat Items berdasarkan tugas Peserta
        // Parse JSON 'surah'
        $surahJson = json_decode($peserta['surah'] ?? '[]', true);
        
        // Items Structure: ['id' => '...', 'label' => '...', 'meta' => '...']
        
        if ($isTahfidz) {
            // Add Tahfidz Wajib
            if (!empty($surahJson['tahfidz_wajib']) && is_array($surahJson['tahfidz_wajib'])) {
                foreach ($surahJson['tahfidz_wajib'] as $sNo) {
                    $sData = $this->alquranModel->where('no_surah', $sNo)->first();
                    if ($sData) {
                        $items[] = [
                            'key' => 'tahfidz_wajib_' . $sNo, // Kunci unik untuk nama input
                            'label' => "Wajib: " . $sData['nama_surah'], // Simplified
                            'objek' => $sData['nama_surah'],
                            'objek_id' => $sNo
                        ];
                    }
                }
            }
            // Add Tahfidz Pilihan
            if (!empty($surahJson['tahfidz_pilihan'])) {
                $sNo = $surahJson['tahfidz_pilihan'];
                $sData = $this->alquranModel->where('no_surah', $sNo)->first();
                if ($sData) {
                    $items[] = [
                        'key' => 'tahfidz_pilihan_' . $sNo,
                        'label' => "Pilihan: " . $sData['nama_surah'],
                        'objek' => $sData['nama_surah'],
                        'objek_id' => $sNo
                    ];
                }
            }
        } else {
            // Non-Tahfidz (misal: Praktek Sholat, Tajwid)
            // Biasanya hanya 1 item: Subjek itu sendiri.
            $items[] = [
                'key' => 'general',
                'label' => $juri['nama_grup_materi'] ?? 'Penilaian',
                'objek' => $juri['nama_grup_materi'] ?? 'General',
                'objek_id' => 0
            ];
            
            // Kasus Khusus: Praktek Sholat mungkin memiliki surah sholat
             if (isset($juri['nama_grup_materi']) && stripos($juri['nama_grup_materi'], 'Sholat') !== false) {
                 if (!empty($surahJson['surah_sholat'])) {
                     $sNo = $surahJson['surah_sholat'];
                     $sData = $this->alquranModel->where('no_surah', $sNo)->first();
                     if ($sData) {
                        // Update label
                        $items[0]['label'] .= " - " . $sData['nama_surah'];
                        $items[0]['objek'] = $sData['nama_surah'];
                        $items[0]['objek_id'] = $sNo;
                     }
                 }
             }
        }
        
        // Render Partial View
        
        // Cek jika dinilai oleh Juri LAIN (Cek Konflik)
        // Pastikan satu siswa ditangani oleh HANYA SATU Juri per Grup Materi
        $idGrupMateri = $juri['grup_materi_id_int'] ?? $juri['id_grup_materi'];
        
        $otherGrade = $this->nilaiModel->select('tbl_munaqosah_nilai_ujian.*, j.nama_juri, j.id_grup_juri as other_grup_juri')
                                       ->where('no_peserta', $noPeserta)
                                       ->where('tbl_munaqosah_nilai_ujian.id_grup_materi', $idGrupMateri)
                                       ->where('tbl_munaqosah_nilai_ujian.id_juri !=', $juri['id'])
                                       ->join('tbl_munaqosah_juri j', 'j.id = tbl_munaqosah_nilai_ujian.id_juri', 'left')
                                       ->first();

        // Cek nilai yang sudah ada (Nilai Saya)
        $existingScores = [];
        // Join with Materi to get Max Value for reverse calculation
        $scoresRaw = $this->nilaiModel->select('tbl_munaqosah_nilai_ujian.*, m.nilai_maksimal')
                                      ->join('tbl_munaqosah_materi_ujian m', 'm.id = tbl_munaqosah_nilai_ujian.id_materi', 'left')
                                      ->where('no_peserta', $noPeserta)
                                      ->where('id_juri', $juri['id'])
                                      ->findAll();
        
        $juriCondition = $juri['kondisional_set'];

        if (!empty($scoresRaw)) {
            foreach ($scoresRaw as $row) {
                // Format Key: [objek_penilaian][id_kriteria] => nilai
                
                $objek = $row['objek_penilaian'];
                $kId = $row['id_kriteria'];
                $val = $row['nilai'];
                
                // Logic Reverse for Display
                if ($juriCondition === 'nilai_pengurangan') {
                    $max = $row['nilai_maksimal'] ?? 100;
                    // DB has Final Score (e.g. 85). Display should be Penalty (15).
                    // Penalty = Max - Final
                    $val = $max - $val;
                    if ($val < 0) $val = 0; // Should not happen ideally
                }

                $existingScores[$objek][$kId] = $val;
            }
        }

        // Tentukan Status Kunci
        // Hanya kunci jika orang lain sudah menilai DAN saya belum menilai.
        // Jika saya sudah menilai, saya harusnya bisa melihat/mengedit data saya sendiri meskipun ada konflik.
        // MODIFIKASI: Ijinkan jika Juri tersebut SATU GRUP (Grup Juri > 0)
        
        $isGraded = !empty($existingScores);
        $lockedByOther = false;
        
        if ($otherGrade && !$isGraded) {
             $myGrupJuri = $juri['id_grup_juri'] ?? 0;
             $otherGrupJuri = $otherGrade['other_grup_juri'] ?? 0;
             
             // Jika Grup Valid (1-20) DAN Sama -> Allow
             if ($myGrupJuri > 0 && $myGrupJuri == $otherGrupJuri) {
                 $lockedByOther = false;
             } else {
                 $lockedByOther = true;
             }
        }
        
        $data = [
            'peserta' => $peserta,
            'juri' => $juri,
            'kriteriaList' => $kriteriaList,
            'items' => $items,
            'existingScores' => $existingScores,
            'isGraded' => $isGraded,
            'lockedByOther' => $lockedByOther,
            'otherJuriName' => $otherGrade['nama_juri'] ?? 'Juri Lain'
        ];

        return $this->response->setJSON([
            'success' => true,
            'html' => view('backend/nilai/form_penilaian', $data)
        ]);
    }

    public function save()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setJSON(['success' => false, 'message' => 'Invalid request']);
        }

        $juri = $this->getJuriInfo();
        if (!$juri) return $this->response->setJSON(['success' => false, 'message' => 'Unauthorized']);

        $noPeserta = $this->request->getPost('no_peserta');
        $nilaiData = $this->request->getPost('nilai'); // Array: [item_key][kriteria_id] => nilai
        $catatan = $this->request->getPost('catatan');

        if (empty($nilaiData)) {
            return $this->response->setJSON(['success' => false, 'message' => 'Tidak ada nilai yang dikirim.']);
        }
        
        $peserta = $this->pesertaModel->where('no_peserta', $noPeserta)->where('tahun_ajaran', $this->getTahunAjaran())->first();
        if(!$peserta) return $this->response->setJSON(['success' => false, 'message' => 'Data peserta invalid.']);

        // Kumpulkan semua ID Kriteria untuk mengambil ID Materi mereka
        $allKriteriaIds = [];
        foreach ($nilaiData as $scores) {
            foreach ($scores as $kId => $val) {
                $allKriteriaIds[] = $kId;
            }
        }
        $allKriteriaIds = array_unique($allKriteriaIds);
        
        $mapKriteriaToMateri = [];
        if (!empty($allKriteriaIds)) {
            $kriteriaInfos = $this->kriteriaModel->whereIn('id', $allKriteriaIds)->findAll();
            foreach ($kriteriaInfos as $k) {
                $mapKriteriaToMateri[$k['id']] = $k['id_materi'];
            }
        }

        $count = 0;
        foreach ($nilaiData as $itemKey => $kriteriaScores) {
            // itemKey misal 'tahfidz_wajib_78' atau 'general'
            // Ekstrak info objek
            $objekPenilaian = '';
            
            if (strpos($itemKey, 'tahfidz') !== false) {
                $parts = explode('_', $itemKey);
                $surahNo = end($parts);
                $objekPenilaian = $surahNo;
            } else {
                $objekPenilaian = 'General'; 
            }
            
            foreach ($kriteriaScores as $kriteriaId => $score) {
                // Tentukan ID Materi
                $realMateriId = $mapKriteriaToMateri[$kriteriaId] ?? 0;

                // Check Group/Condition Logic
                 $groupId = $juri['grup_materi_id_int'] ?? $juri['id_grup_materi'];
                 if(!is_numeric($groupId)) $groupId = 0;
                 
                 // Fetch Condition (Cache for performance)
                 static $groupConditionCache = [];
                 if (!isset($groupConditionCache[$groupId])) {
                      $gm = (new \App\Models\Munaqosah\GrupMateriModel())->find($groupId);
                      $groupConditionCache[$groupId] = $gm ? $gm['kondisional_set'] : 'nilai_default';
                 }
                 $condition = $groupConditionCache[$groupId];

                 $finalScore = $score;
                 if ($condition === 'nilai_pengurangan') {
                     // Fetch Max Score for this specific materi/kriteria
                     // We need to look up $kriteriaInfos again or map it effectively.
                     // Optimization: Build a map [kriteriaId => maxVal] beforehand.
                     // Since we didn't do it above, let's just fetch efficiently or rely on what we have.
                     // Ideally we should have fetched it with $mapKriteriaToMateri.
                     
                     // Let's optimize the lookup above first (in the block before loop) or query here.
                     // For safety, let's query individually or use a prepared map if possible.
                     // The $kriteriaInfos above fetched `id_materi`, let's also fetch `nilai_maksimal`.
                 }
                 
                 // Simplified Logic: Fetch Max Value if needed
                 if ($condition === 'nilai_pengurangan') {
                     // Get Max Value from Materi or Kriteria? Usually Materi has max value.
                     // We have $realMateriId.
                     $materiInfo = (new \App\Models\Munaqosah\MateriModel())->find($realMateriId);
                     $maxVal = $materiInfo ? $materiInfo['nilai_maksimal'] : 100;
                     
                     $finalScore = $maxVal - $score;
                     if ($finalScore < 0) $finalScore = 0;
                 }


                // Cek eksistensi
                $exist = $this->nilaiModel->where([
                    'no_peserta' => $noPeserta,
                    'id_juri' => $juri['id'], 
                    'id_kriteria' => $kriteriaId,
                    'objek_penilaian' => $objekPenilaian
                ])->first();

                $saveData = [
                    'no_peserta' => $noPeserta,
                    'nisn' => $peserta['nisn'],
                    'id_juri' => $juri['id'], 
                    'tahun_ajaran' => $peserta['tahun_ajaran'],
                    'id_grup_materi' => $groupId,
                    'id_grup_juri' => $juri['id_grup_juri'] ?? 0, 
                    'id_kriteria' => $kriteriaId,
                    'id_materi' => $realMateriId, 
                    'objek_penilaian' => $objekPenilaian,
                    'nilai' => $finalScore, // Saved the FINAL score
                    'catatan' => $catatan[$itemKey] ?? ''
                ];

                if ($exist) {
                     $this->nilaiModel->update($exist['id'], $saveData);
                } else {
                     $this->nilaiModel->insert($saveData);
                }
                $count++;
            }
        }

        return $this->response->setJSON(['success' => true, 'message' => 'Nilai berhasil disimpan.', 'count' => $count]);
    }

    public function authorizeEdit()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setJSON(['success' => false, 'message' => 'Invalid request']);
        }

        $username = $this->request->getPost('username');
        $password = $this->request->getPost('password');

        if (!$username || !$password) {
            return $this->response->setJSON(['success' => false, 'message' => 'Username dan Password wajib diisi.']);
        }

        // Gunakan UserModel untuk verifikasi
        $userModel = new \App\Models\UserModel();
        $user = $userModel->getUserByUsername($username);

        if (!$user) {
            return $this->response->setJSON(['success' => false, 'message' => 'User tidak ditemukan.']);
        }

        if (!$userModel->validatePassword($password, $user['password_hash'])) {
            return $this->response->setJSON(['success' => false, 'message' => 'Password salah.']);
        }

        // Cek Grup (Kepala atau Admin)
        // Perlu mengambil ulang user dengan grup atau cek manual
        $userWithGroups = $userModel->getUserWithGroups($user['id']);
        $groups = array_column($userWithGroups['groups'], 'name');

        if (in_array('kepala', $groups) || in_array('admin', $groups)) {
            return $this->response->setJSON(['success' => true, 'message' => 'Otorisasi Berhasil. Silakan edit nilai.']);
        }

        return $this->response->setJSON(['success' => false, 'message' => 'Akses Ditolak. Hanya Kepala/Admin yang bisa membuka kunci.']);
    }

    private function getJuriInfo()
    {
        $userId = $this->getCurrentUser()['id'];
        return $this->juriModel->select('tbl_munaqosah_juri.*, gm.nama_grup_materi, gm.id as grup_materi_id_int, gm.kondisional_set')
                               ->where('user_id', $userId)
                               ->join('tbl_munaqosah_grup_materi gm', 'gm.id = tbl_munaqosah_juri.id_grup_materi', 'left')
                               ->first();
    }

    public function refreshHistory()
    {
        if (!$this->request->isAJAX()) return $this->response->setJSON(['success' => false]);
        
        $juri = $this->getJuriInfo();
        $listDinilai = [];
        if ($juri) {
            $tahunAjaran = $this->getTahunAjaran();
            $listDinilai = $this->nilaiModel->getPesertaDinilaiByJuri($juri['id'], $tahunAjaran);
        }

        $html = '';
        if (empty($listDinilai)) {
            $html = '<tr><td colspan="5" class="text-center text-muted py-3">Belum ada data penilaian.</td></tr>';
        } else {
            $no = 1;
            foreach ($listDinilai as $d) {
                // Parse Waktu
                $time = \CodeIgniter\I18n\Time::parse($d['tgl_nilai'])->humanize();
                $html .= '<tr>
                    <td class="text-center">'. $no++ .'</td>
                    <td>'. esc($d['nama_siswa']) .'</td>
                    <td><strong>'. esc($d['no_peserta']) .'</strong></td>
                    <td>'. $time .'</td>
                    <td class="text-center">
                        <button type="button" class="btn btn-xs btn-primary btn-pilih-history" data-nopeserta="'. $d['no_peserta'] .'">
                            <i class="fas fa-eye mr-1"></i> Pilih
                        </button>
                    </td>
                </tr>';
            }
        }

        return $this->response->setJSON([
            'success' => true,
            'html' => $html,
            'count' => count($listDinilai)
        ]);
    }
    public function getNextPesertaFromAntrian()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setJSON(['success' => false, 'message' => 'Request harus menggunakan AJAX']);
        }

        $juri = $this->getJuriInfo();
        if (!$juri) {
            return $this->response->setJSON(['success' => false, 'message' => 'Data juri tidak ditemukan']);
        }

        $tahunAjaran = $this->getTahunAjaran();
        $idGrupMateri = $juri['grup_materi_id_int'] ?? $juri['id_grup_materi'];
        
        // Filter berdasarkan ID Ruangan (id_grup_juri) untuk memastikan hanya peserta yang ditugaskan ke ruangan Juri INI yang ditampilkan
        $roomId = $juri['id_grup_juri'];

        if (!$roomId) {
             // Fallback jika Juri belum memiliki ruangan yang ditugaskan (seharusnya tidak terjadi di logika baru)
             // Tapi mari kita kembalikan kosong untuk aman, atau izinkan fallback?
             // Paling aman adalah tidak mengembalikan apa pun jika tidak ada ruangan.
             return $this->response->setJSON(['success' => true, 'hasPeserta' => false]);
        }
        
        $antrianModel = new AntrianModel();
        
        // Cari peserta yang statusnya 'sedang_ujian' (atau 'dipanggil') DI ROOM INI
        $potentialCandidates = $antrianModel->where('tahun_ajaran', $tahunAjaran)
                                            ->where('id_grup_materi', $idGrupMateri)
                                            ->where('id_grup_juri', $roomId) // Strict Room Check
                                            ->whereIn('status_antrian', ['sedang_ujian', 'dipanggil'])
                                            ->orderBy('updated_at', 'DESC') 
                                            ->findAll();
                                            
        if (empty($potentialCandidates)) {
             return $this->response->setJSON([
                'success' => true,
                'hasPeserta' => false,
                'NoPeserta' => null
            ]);
        }
        
        // Filter peserta yang sudah dinilai
        $validCandidate = null;
        foreach ($potentialCandidates as $candidate) {
            // Check if already graded by THIS Juri
            // Note: getPesertaDinilaiByJuri returns list of finished grading
            // But we want to know if specific grading exists?
            // Let's use NilaiUjianModel check
             $exist = $this->nilaiModel->where([
                'no_peserta' => $candidate['no_peserta'],
                'id_juri' => $juri['id'],
                'tahun_ajaran' => $tahunAjaran
            ])->first();
            
            if (!$exist) {
                // Ketemu satu!
                $validCandidate = $candidate;
                break;
            }
        }
        
        if ($validCandidate) {
            return $this->response->setJSON([
                'success' => true,
                'hasPeserta' => true,
                'NoPeserta' => $validCandidate['no_peserta']
            ]);
        }
        
        return $this->response->setJSON([
            'success' => true,
            'hasPeserta' => false
        ]);
    }
}
