<?php

namespace App\Controllers;

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

class PublicSertifikat extends BaseController
{
    // Secret key for HMAC signing (from .env encryption.key)
    private const HMAC_SECRET = 'munaqosah-sertifikat-2026';

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

    /**
     * Download sertifikat via signed public link (tanpa login)
     * URL: /sertifikat/{token}
     * Token format: base64url(pesertaId + '-' + 16charHMAC)
     */
    public function download($token)
    {
        // 1. Decode and verify token
        $pesertaId = $this->verifyToken($token);

        if (!$pesertaId) {
            return view('public/sertifikat_error', [
                'message' => 'Link sertifikat tidak valid atau telah kadaluarsa.'
            ]);
        }

        // 2. Get peserta from database
        $peserta = $this->pesertaModel
            ->select('tbl_munaqosah_peserta.*, s.*, s.nisn as real_nisn, tbl_munaqosah_peserta.id as id_peserta')
            ->join('tbl_munaqosah_siswa s', 's.nisn = tbl_munaqosah_peserta.nisn AND s.tahun_ajaran = tbl_munaqosah_peserta.tahun_ajaran', 'inner')
            ->where('tbl_munaqosah_peserta.id', $pesertaId)
            ->first();

        if (!$peserta) {
            return view('public/sertifikat_error', [
                'message' => 'Data peserta tidak ditemukan.'
            ]);
        }

        // 3. Verify the token signature matches this peserta's data
        $nisn = $peserta['real_nisn'] ?? $peserta['nisn'] ?? '';
        $expectedToken = self::generateToken($pesertaId, $nisn, $peserta['tahun_ajaran']);

        if (!$expectedToken || !hash_equals($expectedToken, $token)) {
            return view('public/sertifikat_error', [
                'message' => 'Link sertifikat tidak valid.'
            ]);
        }

        // 4. Generate PDF
        return $this->generateCertificatePdf($peserta);
    }

    /**
     * Verify token and extract peserta ID
     * Returns peserta_id on success, null on failure
     */
    private function verifyToken($token)
    {
        try {
            $decoded = self::base64UrlDecode($token);
            if ($decoded === false || strpos($decoded, '-') === false) {
                return null;
            }

            // Split: pesertaId-signature
            $lastDash = strrpos($decoded, '-');
            if ($lastDash === false) return null;

            $pesertaId = substr($decoded, 0, $lastDash);
            
            if (!is_numeric($pesertaId) || $pesertaId <= 0) {
                return null;
            }

            return (int) $pesertaId;
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Generate certificate PDF for a peserta
     * Reused logic from CetakSertifikat::print()
     */
    private function generateCertificatePdf($peserta)
    {
        // 1. Get Templates
        $templateDepan = $this->templateModel->getTemplateByHalaman('depan');
        $templateBelakang = $this->templateModel->getTemplateByHalaman('belakang');

        if (!$templateDepan || !$templateBelakang) {
            return view('public/sertifikat_error', [
                'message' => 'Template sertifikat belum tersedia. Silakan hubungi panitia.'
            ]);
        }

        // 2. Get Scores
        $rawScores = $this->nilaiModel
            ->where('no_peserta', $peserta['no_peserta'])
            ->where('tahun_ajaran', $peserta['tahun_ajaran'])
            ->findAll();

        // 3. Calculate Final Scores
        $allMateri = $this->materiModel->select('tbl_munaqosah_materi_ujian.*, gm.kondisional_set')
            ->join('tbl_munaqosah_grup_materi gm', 'gm.id = tbl_munaqosah_materi_ujian.id_grup_materi', 'left')
            ->orderBy('tbl_munaqosah_materi_ujian.id', 'ASC')
            ->findAll();

        $scores = [];
        $detailedScores = [];
        $totalScore = 0;

        $globalPredikats = $this->predikatModel->getByGrup(null);

        foreach ($allMateri as $m) {
            $mId = $m['id'];
            $kriteria = $this->kriteriaModel->where('id_materi', $mId)->findAll();
            $materiSubtotal = 0;
            $hasScore = false;

            $isPengurangan = ($m['kondisional_set'] == 'nilai_pengurangan');

            $mScores = array_filter($rawScores, function ($row) use ($mId) {
                return $row['id_materi'] == $mId;
            });

            $criteriaList = [];

            if (!empty($mScores)) {
                $hasScore = true;

                $kriteriaScores = [];
                foreach ($mScores as $row) {
                    $kriteriaScores[$row['id_kriteria']][$row['id_juri']] = $row['nilai'];
                }

                foreach ($kriteria as $k) {
                    $kid = $k['id'];
                    $vals = $kriteriaScores[$kid] ?? [];

                    if (empty($vals)) continue;

                    $avg = array_sum($vals) / count($vals);
                    $weightedScore = 0;

                    if ($isPengurangan) {
                        $materiSubtotal += $avg;
                        $weightedScore = $avg;
                    } else {
                        $weightedScore = $avg * ($k['bobot'] / 100);
                        $materiSubtotal += $weightedScore;
                    }

                    $criteriaList[] = [
                        'nama' => $k['nama_kriteria'],
                        'bobot' => $isPengurangan ? '-' : $k['bobot'],
                        'nilai' => $avg,
                        'score' => $weightedScore
                    ];
                }

                if ($isPengurangan && count($kriteria) > 0) {
                    $materiSubtotal = $materiSubtotal / count($kriteria);
                }
            }

            if ($hasScore || !$isPengurangan) {
                $gradeLetter = '-';
                foreach ($globalPredikats as $pred) {
                    $score = (float) $materiSubtotal;
                    if ($score >= (float) $pred['min_nilai'] && $score <= (float) $pred['max_nilai']) {
                        $gradeLetter = $pred['predikat_huruf'] ?? null;
                        if (empty($gradeLetter) && !empty($pred['nama_predikat'])) {
                            $gradeLetter = strtoupper(substr($pred['nama_predikat'], 0, 1));
                        }
                        $gradeLetter = $gradeLetter ?: '-';
                        break;
                    }
                }

                $scores[$m['nama_materi']] = ['nilai' => $materiSubtotal, 'huruf' => $gradeLetter];
                $detailedScores[$m['id']] = [
                    'id' => $m['id'],
                    'nama_materi' => $m['nama_materi'],
                    'id_grup_materi' => $m['id_grup_materi'],
                    'kriteria' => $criteriaList,
                    'total' => $materiSubtotal
                ];
                $totalScore += $materiSubtotal;
            }
        }

        $divider = count($allMateri) > 0 ? count($allMateri) : 1;
        $avgScore = $totalScore / $divider;

        $scoreData = [
            'nama_siswa' => $peserta['nama_siswa'],
            'no_peserta' => $peserta['no_peserta'],
            'tahun_ajaran' => $peserta['tahun_ajaran'],
            'scores' => $scores,
            'detailed_scores' => $detailedScores,
            'total' => $totalScore,
            'avg' => $avgScore,
            'nilai_huruf' => $this->getPredikatByScore($avgScore, $globalPredikats, 'predikat_huruf'),
            'predikat' => $this->getPredikatByScore($avgScore, $globalPredikats, 'nama_predikat')
        ];

        $frontFields = $this->fieldModel->getFieldsByTemplate($templateDepan['id']);
        $backFields = $this->fieldModel->getFieldsByTemplate($templateBelakang['id']);

        $generatorData = [
            'nama_peserta' => $peserta['nama_siswa'],
            'nomor_peserta' => $peserta['no_peserta'],
            'nisn' => $peserta['real_nisn'],
            'nis' => $peserta['nis'] ?? '-',
            'tahun_ajaran' => $peserta['tahun_ajaran'],
            'tempat_lahir' => $peserta['tempat_lahir'],
            'tanggal_lahir' => $peserta['tanggal_lahir'],
            'jenis_kelamin' => $peserta['jenis_kelamin'],
            'nama_ayah' => $peserta['nama_ayah'],
            'alamat' => $peserta['alamat'],
            'nama_sekolah' => 'SDIT AN-NAHL',
            'predikat' => $this->getPredikatByScore($avgScore, $globalPredikats, 'nama_predikat'),
            'nilai_huruf' => $this->getPredikatByScore($avgScore, $globalPredikats, 'predikat_huruf'),
            'nilai_rata_rata' => number_format($avgScore, 1),
            'tanggal_terbit' => date('d F Y'),
            'nomor_sertifikat' => 'SERT/' . date('Y') . '/' . str_pad($peserta['id_peserta'], 3, '0', STR_PAD_LEFT),
            'kepala_sekolah' => 'Nama Kepala Sekolah',
            'nip_kepala' => '-',
            'qr_code' => '',
            'foto_peserta' => FCPATH . ($peserta['foto'] ?? 'assets/img/default.png'),
        ];

        $generator = new CertificateGenerator($templateDepan, $frontFields);
        $generator->setData($generatorData);

        $filename = 'Sertifikat_' . $peserta['nama_siswa'] . '.pdf';
        $combinedData = array_merge($scoreData, $generatorData);

        $generator->generateWithBackPage($templateBelakang, $backFields, $combinedData)
                  ->stream($filename, ['Attachment' => 0]);
    }

    /**
     * Get predikat by score (reused from CetakSertifikat)
     */
    private function getPredikatByScore($score, $predikats, $field = 'nama_predikat')
    {
        $score = ceil($score);
        foreach ($predikats as $p) {
            if ($score >= $p['min_nilai'] && $score <= $p['max_nilai']) {
                return $p[$field] ?? '-';
            }
        }
        return '-';
    }

    // ================================================================
    // STATIC TOKEN HELPERS (called from CetakSertifikat)
    // ================================================================

    /**
     * Get the HMAC secret key
     */
    private static function getSecret()
    {
        return env('encryption.key', self::HMAC_SECRET);
    }

    /**
     * Generate a short signed token for a peserta
     * Token = base64url(pesertaId + '-' + 16charHMAC)
     * Result: ~25 chars, well under 50
     */
    public static function generateToken($pesertaId, $nisn, $tahunAjaran)
    {
        try {
            $data = $pesertaId . ':' . $nisn . ':' . $tahunAjaran;
            $sig = substr(hash_hmac('sha256', $data, self::getSecret()), 0, 16);
            $payload = $pesertaId . '-' . $sig;
            return self::base64UrlEncode($payload);
        } catch (\Exception $e) {
            log_message('error', 'Certificate token generation failed: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Generate the full public URL for a certificate
     */
    public static function generateLink($pesertaId, $nisn, $tahunAjaran)
    {
        $token = self::generateToken($pesertaId, $nisn, $tahunAjaran);
        if (!$token) return null;
        return base_url('sertifikat/' . $token);
    }

    /**
     * URL-safe base64 encode
     */
    private static function base64UrlEncode($data)
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    /**
     * URL-safe base64 decode
     */
    private static function base64UrlDecode($data)
    {
        $remainder = strlen($data) % 4;
        if ($remainder) {
            $data .= str_repeat('=', 4 - $remainder);
        }
        return base64_decode(strtr($data, '-_', '+/'));
    }
}
