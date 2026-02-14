<?php

namespace App\Controllers\Backend\Munaqosah;

use App\Controllers\BaseController;
use App\Models\Munaqosah\JuriModel;
use App\Models\Munaqosah\NilaiUjianModel;
use App\Models\Munaqosah\GrupMateriModel;

class MonitoringGrupJuri extends BaseController
{
    protected $juriModel;
    protected $nilaiModel;
    protected $grupMateriModel;

    public function __construct()
    {
        $this->juriModel = new JuriModel();
        $this->nilaiModel = new NilaiUjianModel();
        $this->grupMateriModel = new GrupMateriModel();
    }

    public function index()
    {
        if (!$this->isLoggedIn()) {
            return redirect()->to('/login');
        }

        if (!in_groups(['admin', 'operator', 'kepala', 'panitia', 'juri'])) {
            return redirect()->to('/backend/dashboard')->with('error', 'Akses ditolak.');
        }

        $tahunAjaran = $this->getTahunAjaran();

        // Cek apakah user adalah Juri - filter otomatis ke grup mereka
        $juriFilter = null;
        $currentUser = $this->getCurrentUser();
        if (in_array('juri', $currentUser['groups'])) {
            $juriData = $this->juriModel->where('user_id', $this->session->get('user_id'))->first();
            if ($juriData) {
                $juriFilter = [
                    'id_grup_juri' => $juriData['id_grup_juri'],
                    'id_grup_materi' => $juriData['id_grup_materi'],
                ];
            }
        }

        // 1. Ambil semua Juri yang sudah punya Grup Juri, lengkap dengan info Grup Materi
        $juriQuery = $this->juriModel
            ->select('tbl_munaqosah_juri.*, gm.nama_grup_materi')
            ->join('tbl_munaqosah_grup_materi gm', 'gm.id = tbl_munaqosah_juri.id_grup_materi', 'left')
            ->where('tbl_munaqosah_juri.id_grup_juri IS NOT NULL')
            ->where('tbl_munaqosah_juri.id_grup_juri >', 0);

        // Filter jika juri - hanya tampilkan grup mereka
        if ($juriFilter) {
            $juriQuery->where('tbl_munaqosah_juri.id_grup_juri', $juriFilter['id_grup_juri']);
        }

        $allJuri = $juriQuery
            ->orderBy('tbl_munaqosah_juri.id_grup_juri', 'ASC')
            ->orderBy('tbl_munaqosah_juri.nama_juri', 'ASC')
            ->findAll();

        // 2. Kelompokkan Juri berdasarkan id_grup_juri
        $juriByGrup = [];
        foreach ($allJuri as $j) {
            $grupId = $j['id_grup_juri'];
            $juriByGrup[$grupId][] = $j;
        }

        // 3. Ambil semua nilai ujian tahun ini
        $rawScores = $this->nilaiModel
            ->distinct()
            ->select('no_peserta, id_juri, id_grup_juri')
            ->where('tahun_ajaran', $tahunAjaran)
            ->findAll();

        // Buat lookup: [id_juri] => [no_peserta1, no_peserta2, ...]
        $juriPesertaMap = [];
        foreach ($rawScores as $row) {
            $jid = $row['id_juri'];
            $np = $row['no_peserta'];
            $juriPesertaMap[$jid][$np] = true;
        }

        // 4. Ambil info peserta (nama) - Gabung peserta + siswa
        $db = \Config\Database::connect();
        $pesertaInfo = $db->table('tbl_munaqosah_peserta p')
            ->select('p.no_peserta, s.nama_siswa')
            ->join('tbl_munaqosah_siswa s', 's.nisn = p.nisn AND s.tahun_ajaran = p.tahun_ajaran', 'left')
            ->where('p.tahun_ajaran', $tahunAjaran)
            ->orderBy('p.no_peserta', 'ASC')
            ->get()->getResultArray();

        $pesertaNameMap = [];
        foreach ($pesertaInfo as $p) {
            $pesertaNameMap[$p['no_peserta']] = $p['nama_siswa'];
        }

        // 5. Bangun Data per Grup
        $grupData = [];
        foreach ($juriByGrup as $grupId => $juris) {
            // Kumpulkan semua no_peserta yang dinilai oleh SALAH SATU juri dalam grup ini
            $allPesertaInGrup = [];
            foreach ($juris as $j) {
                $jid = $j['id'];
                if (isset($juriPesertaMap[$jid])) {
                    foreach (array_keys($juriPesertaMap[$jid]) as $np) {
                        $allPesertaInGrup[$np] = true;
                    }
                }
            }

            // Sort peserta
            $pesertaList = array_keys($allPesertaInGrup);
            sort($pesertaList);

            // Bangun matrix: [no_peserta][juri_id] => true/false
            $matrix = [];
            $countLengkap = 0;
            $countBelum = 0;

            foreach ($pesertaList as $np) {
                $row = [];
                $isComplete = true;
                foreach ($juris as $j) {
                    $jid = $j['id'];
                    $sudahNilai = isset($juriPesertaMap[$jid][$np]);
                    $row[$jid] = $sudahNilai;
                    if (!$sudahNilai) {
                        $isComplete = false;
                    }
                }
                $matrix[$np] = [
                    'scores' => $row,
                    'complete' => $isComplete,
                    'nama' => $pesertaNameMap[$np] ?? '-'
                ];

                if ($isComplete) {
                    $countLengkap++;
                } else {
                    $countBelum++;
                }
            }

            // Ambil nama Grup Materi dari juri pertama (semua juri dalam satu grup biasanya sama)
            $grupMateriName = $juris[0]['nama_grup_materi'] ?? '-';
            $grupMateriId = $juris[0]['id_grup_materi'] ?? 0;

            $grupData[$grupId] = [
                'juris' => $juris,
                'matrix' => $matrix,
                'totalPeserta' => count($pesertaList),
                'countLengkap' => $countLengkap,
                'countBelum' => $countBelum,
                'grupMateriName' => $grupMateriName,
                'grupMateriId' => $grupMateriId,
            ];
        }

        $data = [
            'title' => 'Monitoring Grup Juri',
            'pageTitle' => 'Monitoring Pasangan Grup Juri',
            'breadcrumb' => [
                ['title' => 'Home', 'url' => '/backend/dashboard'],
                ['title' => 'Monitoring Grup Juri', 'url' => ''],
            ],
            'user' => $this->getCurrentUser(),
            'tahunAjaran' => $tahunAjaran,
            'availableTahunAjaran' => $this->getAvailableTahunAjaran(),
            'grupData' => $grupData,
        ];

        return view('backend/monitoring/grup-juri/index', $data);
    }
}
