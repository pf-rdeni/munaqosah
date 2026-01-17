<?php

namespace App\Models\Munaqosah;

use CodeIgniter\Model;

class KriteriaModel extends Model
{
    protected $table = 'tbl_munaqosah_kriteria_materi_ujian';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    
    protected $allowedFields = [
        'id_kriteria', // Unik CHAR(10)
        'nama_kriteria',
        'id_materi', // Link ke tbl_munaqosah_materi_ujian
        'deskripsi',
        'bobot', // Default 1.00
        'urutan'
    ];

    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    /**
     * Ambil kriteria berdasarkan ID Materi
     */
    public function getByMateri($materiId)
    {
        return $this->where('id_materi', $materiId)
                    ->orderBy('urutan', 'ASC')
                    ->findAll();
    }
}
