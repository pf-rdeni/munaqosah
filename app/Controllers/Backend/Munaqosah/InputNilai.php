<?php

namespace App\Controllers\Backend\Munaqosah;

use App\Controllers\BaseController;
use App\Models\Munaqosah\PesertaModel;
use App\Models\Munaqosah\NilaiUjianModel;
use App\Models\Munaqosah\JuriModel;
use App\Models\Munaqosah\JuriKriteriaModel;
use App\Models\Munaqosah\KriteriaModel;
use App\Models\Munaqosah\TblAlquranModel;

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
            'juri'       => $juri,
            'listDinilai' => $listDinilai
        ];

        return view('backend/nilai/index', $data);
    }

    // Step 2: Load Form Penilaian via AJAX
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
        // Fetch kriteria IDs assigned to this Juri
        // MUST use the PK 'id' (int), not 'id_juri' (string code) because tbl_munaqosah_juri_kriteria uses the INT FK.
        $juriPk = $juri['id']; 
        $kriteriaIds = $this->juriKriteriaModel->getKriteriaIdsByJuri($juriPk); 

        $kriteriaList = [];
        if (!empty($kriteriaIds)) {
            $kriteriaList = $this->kriteriaModel->whereIn('id', $kriteriaIds)
                                                ->orderBy('urutan', 'ASC')
                                                ->findAll();
        } else {
            // Fallback: Get ALL Kriteria based on Juri's Grup Materi
            // Use the aliased ID from getJuriInfo to avoid collision with Code 'TZ01'
            $idGrupMateri = $juri['grup_materi_id_int'] ?? $juri['id_grup_materi'];
            
            // Log for debugging
            log_message('error', 'DEBUG NILAI: Juri ID=' . ($juri['id']??'null') . ' GrupMateri=' . $idGrupMateri);
            
            if ($idGrupMateri) {
                // Get Materi IDs in this group
                $materiModel = new \App\Models\Munaqosah\MateriModel();
                $materiList = $materiModel->where('id_grup_materi', $idGrupMateri)->findAll();
                
                log_message('error', 'DEBUG NILAI: Found Materi: ' . json_encode($materiList));
                
                if (!empty($materiList)) {
                    $materiIds = array_column($materiList, 'id'); 
                    
                    log_message('error', 'DEBUG NILAI: Materi IDs: ' . json_encode($materiIds));

                    // Get Kriteria for these Materi
                    $kriteriaList = $this->kriteriaModel->whereIn('id_materi', $materiIds)
                                                        ->orderBy('urutan', 'ASC')
                                                        ->findAll();
                                                        
                    log_message('error', 'DEBUG NILAI: Found Kriteria Count: ' . count($kriteriaList));
                }
            }
        }    

        // 4. Determine Items (Tabs)
        // If Juri is for Tahfidz (check Grup Materi or Materi Name)
        $isTahfidz = false;
        // Check Grup Materi Name. 
        if (isset($juri['nama_grup_materi']) && (stripos($juri['nama_grup_materi'], 'Tahfidz') !== false)) {
            $isTahfidz = true;
        }

        $items = [];
        // Construct Items based on Peserta's assignments
        // Parse 'surah' JSON
        $surahJson = json_decode($peserta['surah'] ?? '[]', true);
        
        // Items Structure: ['id' => '...', 'label' => '...', 'meta' => '...']
        
        if ($isTahfidz) {
            // Add Tahfidz Wajib
            if (!empty($surahJson['tahfidz_wajib']) && is_array($surahJson['tahfidz_wajib'])) {
                foreach ($surahJson['tahfidz_wajib'] as $sNo) {
                    $sData = $this->alquranModel->where('no_surah', $sNo)->first();
                    if ($sData) {
                        $items[] = [
                            'key' => 'tahfidz_wajib_' . $sNo, // Unique Key for input name
                            'label' => "Wajib: " . $sData['nama_surah'] . " (Ayat 1-End)", // Simplified
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
            // Non-Tahfidz (e.g. Praktek Sholat, Tajwid)
            // Usually just 1 item: The Subject itself.
            $items[] = [
                'key' => 'general',
                'label' => $juri['nama_grup_materi'] ?? 'Penilaian',
                'objek' => $juri['nama_grup_materi'] ?? 'General',
                'objek_id' => 0
            ];
            
            // Special Case: Praktek Sholat might have surah sholat
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
        
        // Check if graded by OTHER Juri (Conflict Check)
        // Ensure one student is handled by only ONE Juri per Materi Group
        $idGrupMateri = $juri['grup_materi_id_int'] ?? $juri['id_grup_materi'];
        
        $otherGrade = $this->nilaiModel->select('tbl_munaqosah_nilai_ujian.*, j.nama_juri')
                                       ->where('no_peserta', $noPeserta)
                                       ->where('tbl_munaqosah_nilai_ujian.id_grup_materi', $idGrupMateri)
                                       ->where('tbl_munaqosah_nilai_ujian.id_juri !=', $juri['id'])
                                       ->join('tbl_munaqosah_juri j', 'j.id = tbl_munaqosah_nilai_ujian.id_juri', 'left')
                                       ->first();

        // Check for existing scores (My Scores)
        $existingScores = [];
        $scoresRaw = $this->nilaiModel->where('no_peserta', $noPeserta)
                                      ->where('id_juri', $juri['id'])
                                      ->findAll();
        
        if (!empty($scoresRaw)) {
            foreach ($scoresRaw as $row) {
                // Key format: [objek_penilaian][id_kriteria] => nilai
                // We need to match the frontend input name format: nilai[item_key][id_kriteria]
                // item_key logic: 
                // if tahfidz: tahfidz_wajib_{objek} or tahfidz_pilihan_{objek}
                // if general: general
                
                // Reverse mapping is tricky because we stored Objek ID/Name but not the prefix.
                // However, we can map broadly or just use [id_kriteria] if unique enough (it isn't per item).
                
                // Let's group by Objek Penilaian
                $objek = $row['objek_penilaian'];
                $kId = $row['id_kriteria'];
                $val = $row['nilai'];
                
                $existingScores[$objek][$kId] = $val;
            }
        }

        // Determine Lock Status
        // Only lock if someone else graded it AND I haven't graded it yet.
        // If I have graded it, I should be able to see/edit my own data despite conflicts.
        $isGraded = !empty($existingScores);
        $lockedByOther = ($otherGrade && !$isGraded) ? true : false;
        
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
        $nilaiData = $this->request->getPost('nilai'); // Array: [item_key][kriteria_id] => score
        $catatan = $this->request->getPost('catatan');

        if (empty($nilaiData)) {
            return $this->response->setJSON(['success' => false, 'message' => 'Tidak ada nilai yang dikirim.']);
        }
        
        $peserta = $this->pesertaModel->where('no_peserta', $noPeserta)->where('tahun_ajaran', $this->getTahunAjaran())->first();
        if(!$peserta) return $this->response->setJSON(['success' => false, 'message' => 'Data peserta invalid.']);

        // Collect all Kriteria IDs to fetch their Materi ID
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
            // itemKey e.g. 'tahfidz_wajib_78' or 'general'
            // Extract objek info
            $objekPenilaian = '';
            
            if (strpos($itemKey, 'tahfidz') !== false) {
                $parts = explode('_', $itemKey);
                $surahNo = end($parts);
                $objekPenilaian = $surahNo;
            } else {
                $objekPenilaian = 'General'; 
            }
            
            foreach ($kriteriaScores as $kriteriaId => $score) {
                // Determine ID Materi
                $realMateriId = $mapKriteriaToMateri[$kriteriaId] ?? 0;

                // Check exist
                $exist = $this->nilaiModel->where([
                    'no_peserta' => $noPeserta,
                    'id_juri' => $juri['id'], 
                    'id_kriteria' => $kriteriaId,
                    'objek_penilaian' => $objekPenilaian
                ])->first();

                $groupId = $juri['grup_materi_id_int'] ?? $juri['id_grup_materi'];
                if(!is_numeric($groupId)) $groupId = 0; 

                $saveData = [
                    'no_peserta' => $noPeserta,
                    'nisn' => $peserta['nisn'],
                    'id_juri' => $juri['id'], 
                    'tahun_ajaran' => $peserta['tahun_ajaran'],
                    'id_grup_materi' => $groupId,
                    'id_grup_juri' => $juri['id_grup_juri'] ?? 0, // Save Grup Juri ID
                    'id_kriteria' => $kriteriaId,
                    'id_materi' => $realMateriId, // Correctly populated
                    'objek_penilaian' => $objekPenilaian,
                    'nilai' => $score,
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

        // Use UserModel to verify
        $userModel = new \App\Models\UserModel();
        $user = $userModel->getUserByUsername($username);

        if (!$user) {
            return $this->response->setJSON(['success' => false, 'message' => 'User tidak ditemukan.']);
        }

        if (!$userModel->validatePassword($password, $user['password_hash'])) {
            return $this->response->setJSON(['success' => false, 'message' => 'Password salah.']);
        }

        // Check Group (Kepala or Admin)
        // Need to refetch user with groups or check manually
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
        return $this->juriModel->select('tbl_munaqosah_juri.*, gm.nama_grup_materi, gm.id as grup_materi_id_int')
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
                // Parse Time
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
}
