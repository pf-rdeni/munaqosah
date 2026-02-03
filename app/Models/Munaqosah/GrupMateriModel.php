<?php

namespace App\Models\Munaqosah;

use CodeIgniter\Model;

class GrupMateriModel extends Model
{
    protected $table = 'tbl_munaqosah_grup_materi';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    
    protected $allowedFields = [
        'id_grup_materi',
        'nama_grup_materi',
        'deskripsi',
        'urutan',
        'status',
        'kondisional_set'
    ];

    /**
     * Ambil dropdown grup materi
     */
    public function getGrupDropdown()
    {
        return $this->orderBy('nama_grup_materi', 'ASC')->findAll();
    }
}
