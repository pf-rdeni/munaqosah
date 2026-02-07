<?php

/**
 * ====================================================================
 * MODEL: NILAI UJIAN MODEL
 * ====================================================================
 * Model untuk mengelola nilai ujian munaqosah
 * 
 * @package    Munaqosah
 * @author     SDIT An-Nahl
 */

namespace App\Models\Munaqosah;

use CodeIgniter\Model;

class NilaiUjianModel extends Model
{
    protected $table = 'tbl_munaqosah_nilai_ujian';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    
    protected $allowedFields = [
        'no_peserta',
        'nisn',
        'id_juri',
        'tahun_ajaran',
        'id_materi',
        'id_kriteria',
        'id_grup_materi',
        'id_grup_juri',
        'objek_penilaian', // Surah No / Nama (Dynamic Item)
        'nilai',
        'catatan',
    ];

    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    /**
     * Ambil nilai dengan relasi lengkap
     * 
     * @param int|null $id
     * @return array
     */
    public function getNilaiWithRelations(?int $id = null): array
    {
        $builder = $this->db->table($this->table . ' n');
        $builder->select('n.*, s.nama_siswa, m.nama_materi, j.nama_juri');
        $builder->join('tbl_munaqosah_siswa s', 's.nisn = n.nisn AND s.tahun_ajaran = n.tahun_ajaran', 'left');
        $builder->join('tbl_munaqosah_materi_ujian m', 'm.id = n.id_materi', 'left');
        $builder->join('tbl_munaqosah_juri j', 'j.id_juri = n.id_juri', 'left');

        if ($id) {
            $builder->where('n.id', $id);
            return $builder->get()->getRowArray() ?? [];
        }

        $builder->orderBy('n.created_at', 'DESC');
        return $builder->get()->getResultArray();
    }

    /**
     * Ambil nilai berdasarkan peserta
     * 
     * @param string $noPeserta
     * @return array
     */
    public function getNilaiByPeserta(string $noPeserta): array
    {
        return $this->where('no_peserta', $noPeserta)
                    ->orderBy('id_materi', 'ASC')
                    ->findAll();
    }

    /**
     * Ambil nilai berdasarkan juri
     * 
     * @param string $idJuri
     * @param string $tahunAjaran
     * @return array
     */
    public function getNilaiByJuri(string $idJuri, string $tahunAjaran): array
    {
        return $this->where('id_juri', $idJuri)
                    ->where('tahun_ajaran', $tahunAjaran)
                    ->orderBy('created_at', 'DESC')
                    ->findAll();
    }

    /**
     * Hitung rata-rata nilai peserta
     * 
     * @param string $noPeserta
     * @return float
     */
    public function getAverageNilai(string $noPeserta): float
    {
        $result = $this->selectAvg('nilai')
                       ->where('no_peserta', $noPeserta)
                       ->first();
        
        return (float)($result['nilai'] ?? 0);
    }

    /**
     * Hitung total peserta yang sudah dinilai
     * 
     * @param string $tahunAjaran
     * @return int
     */
    public function countPesertaDinilai(string $tahunAjaran): int
    {
        return $this->select('no_peserta')
                    ->where('tahun_ajaran', $tahunAjaran)
                    ->distinct()
                    ->countAllResults();
    }

    /**
     * Cek apakah nilai sudah diinput oleh juri tertentu
     * 
     * @param string $noPeserta
     * @param string $idJuri
     * @param int $idMateri
     * @return bool
     */
    public function isNilaiExists(string $noPeserta, string $idJuri, int $idMateri): bool
    {
        return $this->where('no_peserta', $noPeserta)
                    ->where('id_juri', $idJuri)
                    ->where('id_materi', $idMateri)
                    ->countAllResults() > 0;
    }

    /**
     * Ambil daftar peserta yang sudah dinilai oleh Juri tertentu (Distinct)
     */
    public function getPesertaDinilaiByJuri($idJuri, $tahunAjaran)
    {
        return $this->select('tbl_munaqosah_nilai_ujian.no_peserta, s.nama_siswa, s.nisn, MAX(tbl_munaqosah_nilai_ujian.created_at) as tgl_nilai, MAX(tbl_munaqosah_nilai_ujian.id_grup_materi) as id_grup_materi, MAX(a.waktu_mulai) as waktu_mulai, MAX(a.waktu_selesai) as waktu_selesai')
                    ->join('tbl_munaqosah_peserta p', 'p.no_peserta = tbl_munaqosah_nilai_ujian.no_peserta AND p.tahun_ajaran = tbl_munaqosah_nilai_ujian.tahun_ajaran', 'left')
                    ->join('tbl_munaqosah_siswa s', 's.nisn = tbl_munaqosah_nilai_ujian.nisn AND s.tahun_ajaran = tbl_munaqosah_nilai_ujian.tahun_ajaran', 'left')
                    ->join('tbl_munaqosah_antrian a', 'a.no_peserta = tbl_munaqosah_nilai_ujian.no_peserta AND a.tahun_ajaran = tbl_munaqosah_nilai_ujian.tahun_ajaran AND a.id_grup_materi = tbl_munaqosah_nilai_ujian.id_grup_materi', 'left')
                    ->where('tbl_munaqosah_nilai_ujian.id_juri', $idJuri)
                    ->where('tbl_munaqosah_nilai_ujian.tahun_ajaran', $tahunAjaran)
                    ->groupBy('tbl_munaqosah_nilai_ujian.no_peserta, s.nama_siswa, s.nisn')
                    ->orderBy('tgl_nilai', 'DESC')
                    ->findAll();
    }
}
