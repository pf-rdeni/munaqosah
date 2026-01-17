<?php

namespace App\Controllers\Backend\Munaqosah;

use App\Controllers\BaseController;
use App\Models\Munaqosah\PesertaModel;
use App\Models\Munaqosah\SiswaModel;

class Peserta extends BaseController
{
    protected $pesertaModel;
    protected $siswaModel;
    protected $settingUndianModel;
    protected $alquranModel;

    public function __construct()
    {
        $this->pesertaModel = new PesertaModel();
        $this->siswaModel = new SiswaModel();
        $this->settingUndianModel = new \App\Models\Munaqosah\SettingUndianModel();
        $this->alquranModel = new \App\Models\Munaqosah\TblAlquranModel();
    }

    /**
     * Display peserta registration page
     */
    public function index()
    {
        if (!$this->isLoggedIn()) {
            return redirect()->to('/login');
        }

        $tahunAjaran = $this->getTahunAjaran();
        
        // Get statistics
        $totalSiswaAktif = $this->siswaModel->where('status', 'aktif')->countAllResults();
        
        // Count siswa with hafalan
        $siswaWithHafalan = $this->siswaModel->where('status', 'aktif')
                                           ->where('hafalan !=', '')
                                           ->where('hafalan !=', null)
                                           ->countAllResults();

        $pesertaTerdaftar = $this->pesertaModel->where('tahun_ajaran', $tahunAjaran)->countAllResults();
        $belumTerdaftar = $siswaWithHafalan - $pesertaTerdaftar;

        // Get settings
        $settings = $this->settingUndianModel->getAllSettings($tahunAjaran);

        // Get peserta list with siswa data
        $pesertaList = $this->pesertaModel
            ->select('tbl_munaqosah_peserta.*, s.nama_siswa, s.jenis_kelamin, s.foto, s.hafalan, s.no_hp')
            ->join('tbl_munaqosah_siswa s', 's.nisn = tbl_munaqosah_peserta.nisn', 'left')
            ->where('tahun_ajaran', $tahunAjaran)
            ->orderBy('no_peserta', 'ASC')
            ->findAll();

        // Enrich peserta list with surah names if needed
        foreach ($pesertaList as &$p) {
            if ($p['surah']) {
                $surahData = json_decode($p['surah'], true);
                if (json_last_error() === JSON_ERROR_NONE) {
                    // Fetch surah names for display
                    if (!empty($surahData['surah_sholat'])) {
                         $s = $this->alquranModel->where('no_surah', $surahData['surah_sholat'])->first();
                         $p['nama_surah_sholat'] = $s ? $s['nama_surah'] : '-';
                    }
                    
                    // Logic for displaying tahfidz can be handled in view or here
                }
            }
        }

        $data = [
            'title'      => 'Registrasi Peserta',
            'pageTitle'  => 'Undian No Tes & Surah',
            'breadcrumb' => [
                ['title' => 'Home', 'url' => '/backend/dashboard'],
                ['title' => 'Peserta', 'url' => ''],
            ],
            'user'            => $this->getCurrentUser(),
            'tahunAjaran'     => $tahunAjaran,
            'totalSiswaAktif' => $totalSiswaAktif,
            'siswaWithHafalan'=> $siswaWithHafalan,
            'pesertaTerdaftar'=> $pesertaTerdaftar,
            'belumTerdaftar'  => $belumTerdaftar,
            'pesertaList'     => $pesertaList,
            'settings'        => $settings,
            'alquranList'     => $this->alquranModel->getAllSurahData() // For options
        ];

        return view('backend/peserta/index', $data);
    }

    /**
     * Save settings
     */
    public function saveSettings()
    {
        $start = $this->request->getPost('surah_sholat_start');
        $end = $this->request->getPost('surah_sholat_end');
        $tahunAjaran = $this->getTahunAjaran();

        $this->settingUndianModel->setSetting('surah_sholat_start', $start, $tahunAjaran);
        $this->settingUndianModel->setSetting('surah_sholat_end', $end, $tahunAjaran);

        return redirect()->back()->with('success', 'Pengaturan berhasil disimpan');
    }

    /**
     * Execute lottery
     */
    public function undian()
    {
        if (!$this->isLoggedIn()) {
            return $this->response->setJSON(['success' => false, 'message' => 'Unauthorized']);
        }

        $tahunAjaran = $this->getTahunAjaran();
        
        // 1. Get Settings
        $startSurah = $this->settingUndianModel->getSetting('surah_sholat_start', $tahunAjaran) ?? 78;
        $endSurah = $this->settingUndianModel->getSetting('surah_sholat_end', $tahunAjaran) ?? 114;

        // 2. Get Available Students (Active + Hafalan)
        $allEligible = $this->siswaModel->where('status', 'aktif')
                                     ->where('hafalan !=', '')
                                     ->where('hafalan !=', null)
                                     ->findAll();

        if (empty($allEligible)) {
            return $this->response->setJSON(['success' => false, 'message' => 'Tidak ada siswa aktif yang memiliki hafalan.']);
        }

        // 3. Filter only UNREGISTERED students
        // Get NISN of already registered students
        $existingPeserta = $this->pesertaModel->where('tahun_ajaran', $tahunAjaran)->findAll();
        $registeredNisn = array_column($existingPeserta, 'nisn');
        $usedNumbers = array_map('intval', array_column($existingPeserta, 'no_peserta'));

        $studentsToProcess = [];
        foreach ($allEligible as $s) {
            if (!in_array($s['nisn'], $registeredNisn)) {
                $studentsToProcess[] = $s;
            }
        }

        if (empty($studentsToProcess)) {
            return $this->response->setJSON(['success' => false, 'message' => 'Semua siswa (dengan hafalan) sudah terdaftar.']);
        }

        // 4. Prepare Random Numbers (Compact & Sequential)
        // Find highest existing no_peserta
        $maxNo = 99;
        if (!empty($usedNumbers)) {
            $maxNo = max($usedNumbers);
        }
        
        // Start from next number
        $startNo = $maxNo + 1;
        $countNeeded = count($studentsToProcess);
        
        // Create range of exact needed size
        // e.g. needed 5, start 100 -> [100, 101, 102, 103, 104]
        $pickedNumbers = range($startNo, $startNo + $countNeeded - 1);
        
        // Shuffle the numbers to randomize assignment
        shuffle($pickedNumbers);
        
        // Students are also shuffled, double randomness
        shuffle($studentsToProcess);

        // 5. Pre-fetch All Surahs for fast lookup
        $allSurahs = $this->alquranModel->getAllSurahData();
        
        // Group Surahs by Juz
        $surahsByJuz = [];
        foreach ($allSurahs as $s) {
            $surahsByJuz[$s['juz']][] = $s['no_surah'];
        }

        // Surah Sholat Range List
        $surahSholatList = [];
        foreach ($allSurahs as $s) {
            if ($s['no_surah'] >= $startSurah && $s['no_surah'] <= $endSurah) {
                $surahSholatList[] = $s['no_surah'];
            }
        }

        $this->db->transStart();

        $count = 0;
        foreach ($studentsToProcess as $index => $siswa) {
            $noPeserta = $pickedNumbers[$index];
            
            // A. Surah Sholat
            $pickedSurahSholat = null;
            if (!empty($surahSholatList)) {
                $pickedSurahSholat = $surahSholatList[array_rand($surahSholatList)];
            }

            // B. Surah Tahfidz
            $hafalanJson = json_decode($siswa['hafalan'], true);
            $targetJuz = 30; // Default fallback
            $rangeStart = 0;
            $rangeEnd = 999;

            if (json_last_error() === JSON_ERROR_NONE && is_array($hafalanJson) && !empty($hafalanJson)) {
                $juzList = [];
                // First pass: find min juz
                foreach ($hafalanJson as $h) {
                    if (isset($h['juz'])) {
                        $juzList[] = (int)$h['juz'];
                    }
                }
                
                if (!empty($juzList)) {
                    $targetJuz = min($juzList);
                    
                    // Second pass: get range for target juz
                    foreach($hafalanJson as $h) {
                        if(isset($h['juz']) && (int)$h['juz'] == $targetJuz) {
                            $rangeStart = isset($h['no_surah_mulai']) 
                                ? (int)$h['no_surah_mulai'] 
                                : (isset($h['surah_start']) ? (int)$h['surah_start'] : 0);
                                
                            $rangeEnd = isset($h['no_surah_akhir']) 
                                ? (int)$h['no_surah_akhir'] 
                                : (isset($h['surah_end']) ? (int)$h['surah_end'] : 999);
                            break;
                        }
                    }
                }
            } else {
                 $parts = array_map('trim', explode(',', $siswa['hafalan']));
                 if (!empty($parts)) {
                     $targetJuz = (int)end($parts);
                 }
            }

            // Determine Candidate Surahs from Target Juz with Range Filter
            $candidateSurahs = [];
            if (isset($surahsByJuz[$targetJuz])) {
                foreach ($surahsByJuz[$targetJuz] as $sNo) {
                    if ($sNo >= $rangeStart && $sNo <= $rangeEnd) {
                        $candidateSurahs[] = $sNo;
                    }
                }
            }
            
            // Fallback: If range filtering results in empty (e.g. bad range), use full juz
            if (empty($candidateSurahs) && isset($surahsByJuz[$targetJuz])) {
                $candidateSurahs = $surahsByJuz[$targetJuz];
            }

            $pickedTahfidz = [];
            if (count($candidateSurahs) >= 3) {
                // Get 3 random unique keys
                $randomKeys = array_rand($candidateSurahs, 3);
                foreach ($randomKeys as $k) {
                    $pickedTahfidz[] = $candidateSurahs[$k];
                }
            } else {
                 // Fallback: pick all available if < 3
                 $pickedTahfidz = $candidateSurahs;
            }

            $surahJson = [
                'surah_sholat' => $pickedSurahSholat,
                'tahfidz_wajib' => $pickedTahfidz,
                'tahfidz_pilihan' => null
            ];

            $data = [
                'nisn'         => $siswa['nisn'],
                'no_peserta'   => (string)$noPeserta,
                'tahun_ajaran' => $tahunAjaran,
                'status'       => 'terdaftar',
                'surah'        => json_encode($surahJson)
            ];

            $this->pesertaModel->insert($data);
            $count++;
        }

        $this->db->transComplete();

        if ($this->db->transStatus() === false) {
             return $this->response->setJSON([
                'success' => false,
                'message' => 'Terjadi kesalahan saat menyimpan data.'
            ]);
        }

        return $this->response->setJSON([
            'success' => true,
            'message' => "Undian selesai! {$count} peserta BARU telah ditambahkan."
        ]);
    }

    /**
     * Save Tahfidz Pilihan
     */
    public function saveTahfidzPilihan()
    {
        $id = $this->request->getPost('pk'); // specific for x-editable or similar, adapt to frontend
        $value = $this->request->getPost('value');
        
        // If coming from standard form
        if (!$id) {
             $id = $this->request->getPost('id_peserta');
             $value = $this->request->getPost('surah_pilihan');
        }

        $peserta = $this->pesertaModel->find($id);
        if (!$peserta) return $this->response->setJSON(['success' => false]);

        $currentSurah = json_decode($peserta['surah'], true) ?? [];
        $currentSurah['tahfidz_pilihan'] = $value;

        $this->pesertaModel->update($id, ['surah' => json_encode($currentSurah)]);

        return $this->response->setJSON(['success' => true]);
    }

    public function reset()
    {
        if (!$this->isLoggedIn()) return $this->response->setJSON(['success' => false]);
        $tahun = $this->getTahunAjaran();
        $this->pesertaModel->where('tahun_ajaran', $tahun)->delete();
        return $this->response->setJSON(['success' => true, 'message' => 'Data reset!']);
    }

    private function getTahunAjaran(): string
    {
        $bulan = (int)date('m');
        $tahun = (int)date('Y');
        return ($bulan >= 7) ? $tahun . '/' . ($tahun + 1) : ($tahun - 1) . '/' . $tahun;
    }
}
