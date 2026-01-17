<?php

namespace App\Models\Munaqosah;

use CodeIgniter\Model;

class MateriModel extends Model
{
    protected $table = 'tbl_munaqosah_materi_ujian';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    
    protected $allowedFields = [
        'id_materi',
        'nama_materi',
        'id_grup_materi',
        'deskripsi',
        'nilai_maksimal',
        'status'
    ];

    /**
     * Ambil data materi untuk dropdown
     */
    public function getMateriDropdown()
    {
        return $this->orderBy('nama_materi', 'ASC')->findAll();
    }
}
