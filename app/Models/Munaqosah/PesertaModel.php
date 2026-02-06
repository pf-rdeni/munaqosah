<?php

/**
 * ====================================================================
 * MODEL: PESERTA MODEL
 * ====================================================================
 * Model untuk mengelola data peserta ujian munaqosah
 * 
 * @package    Munaqosah
 * @author     SDIT An-Nahl
 */

namespace App\Models\Munaqosah;

use CodeIgniter\Model;

class PesertaModel extends Model
{
    protected $table = 'tbl_munaqosah_peserta';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    
    protected $allowedFields = [
        'nisn',
        'no_peserta',
        'tahun_ajaran',
        'status',
        'surah',
    ];

    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    protected $validationRules = [
        'nisn'         => 'required|max_length[20]',
        'no_peserta'   => 'required|max_length[20]',
        'tahun_ajaran' => 'required|max_length[10]',
    ];

    /**
     * Ambil peserta dengan data siswa
     * 
     * @param int|null $id
     * @return array
     */
    public function getPesertaWithSiswa(?int $id = null): array
    {
        $builder = $this->db->table($this->table . ' p');
        $builder->select('p.*, s.nama_siswa, s.jenis_kelamin, s.tanggal_lahir, s.foto');
        $builder->join('tbl_munaqosah_siswa s', 's.nisn = p.nisn AND s.tahun_ajaran = p.tahun_ajaran', 'left');

        if ($id) {
            $builder->where('p.id', $id);
            return $builder->get()->getRowArray() ?? [];
        }

        $builder->orderBy('p.created_at', 'DESC');
        return $builder->get()->getResultArray();
    }

    /**
     * Ambil peserta berdasarkan tahun ajaran
     * 
     * @param string $tahunAjaran
     * @return array
     */
    public function getPesertaByTahunAjaran(string $tahunAjaran): array
    {
        return $this->where('tahun_ajaran', $tahunAjaran)
                    ->orderBy('no_peserta', 'ASC')
                    ->findAll();
    }

    /**
     * Cek apakah peserta sudah terdaftar
     * 
     * @param string $nisn
     * @param string $tahunAjaran
     * @return bool
     */
    public function isPesertaExists(string $nisn, string $tahunAjaran): bool
    {
        return $this->where('nisn', $nisn)
                    ->where('tahun_ajaran', $tahunAjaran)
                    ->countAllResults() > 0;
    }

    /**
     * Generate nomor peserta baru
     * 
     * @param string $tahunAjaran
     * @return string
     */
    public function generateNoPeserta(string $tahunAjaran): string
    {
        // Format: MQ-TAHUN-URUTAN (contoh: MQ-2526-001)
        $prefix = 'MQ-' . str_replace('/', '', substr($tahunAjaran, 2, 4)) . '-';
        
        $lastPeserta = $this->where('tahun_ajaran', $tahunAjaran)
                            ->orderBy('no_peserta', 'DESC')
                            ->first();

        if ($lastPeserta) {
            $lastNumber = (int)substr($lastPeserta['no_peserta'], -3);
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        return $prefix . str_pad($newNumber, 3, '0', STR_PAD_LEFT);
    }


    /**
     * Ambil detail peserta by No Peserta & Tahun Ajaran
     * 
     * @param string $noPeserta
     * @param string $tahunAjaran
     * @return array|null
     */
    public function getPesertaDetail($noPeserta, $tahunAjaran)
    {
        return $this->select('tbl_munaqosah_peserta.*, s.nama_siswa, s.nisn, s.foto, s.hafalan')
                    ->join('tbl_munaqosah_siswa s', 's.nisn = tbl_munaqosah_peserta.nisn AND s.tahun_ajaran = tbl_munaqosah_peserta.tahun_ajaran', 'left')
                    ->where('tbl_munaqosah_peserta.no_peserta', $noPeserta)
                    ->where('tbl_munaqosah_peserta.tahun_ajaran', $tahunAjaran)
                    ->first();
    }
}
