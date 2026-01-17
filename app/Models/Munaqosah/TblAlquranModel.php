<?php

namespace App\Models\Munaqosah;

use CodeIgniter\Model;

class TblAlquranModel extends Model
{
    protected $table            = 'tbl_alquran';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $allowedFields    = [
        'juz', 'no_surah', 'nama_surah', 'jumlah_ayat', 
        'ayat_mulai', 'ayat_akhir', 'link'
    ];

    /**
     * Ambil daftar surah unique
     */
    public function getDaftarSurah()
    {
        return $this->select('no_surah, nama_surah')
                    ->groupBy(['no_surah', 'nama_surah'])
                    ->orderBy('no_surah', 'ASC')
                    ->findAll();
    }

    /**
     * Ambil semua data untuk filtering JS
     */
    public function getAllSurahData()
    {
        return $this->select('juz, no_surah, nama_surah')
                    ->orderBy('juz ASC, no_surah ASC')
                    ->findAll();
    }
}
