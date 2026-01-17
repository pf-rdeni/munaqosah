<?php

namespace App\Models\Munaqosah;

use CodeIgniter\Model;

class AlquranModel extends Model
{
    protected $table            = 'tbl_munaqosah_alquran';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'id_materi', 'id_kategori_materi', 'id_surah', 'id_ayat',
        'nama_surah', 'nama_surah_arab', 'teks_ayat', 'link_ayat'
    ];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    /**
     * Ambil daftar surah unique
     */
    public function getDaftarSurah()
    {
        return $this->select('id_surah, nama_surah')
                    ->distinct()
                    ->orderBy('id_surah', 'ASC')
                    ->findAll();
    }
}
