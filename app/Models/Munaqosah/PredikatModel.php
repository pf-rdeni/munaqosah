<?php

namespace App\Models\Munaqosah;

use CodeIgniter\Model;

class PredikatModel extends Model
{
    protected $table = 'tbl_munaqosah_predikat';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    
    protected $allowedFields = [
        'nama_predikat',
        'min_nilai',
        'max_nilai',
        'deskripsi_global',
        'class_css',
        'urutan'
    ];

    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    public function getAll()
    {
        return $this->orderBy('urutan', 'ASC')->findAll();
    }
}
