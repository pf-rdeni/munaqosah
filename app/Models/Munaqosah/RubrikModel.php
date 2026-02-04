<?php

namespace App\Models\Munaqosah;

use CodeIgniter\Model;

class RubrikModel extends Model
{
    protected $table = 'tbl_munaqosah_rubrik';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    
    protected $allowedFields = [
        'id_kriteria',
        'id_predikat',
        'deskripsi'
    ];

    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    /**
     * Get detailed rubric descriptions for a set of criteria IDs
     */
    public function getRubrikByKriteria(array $kriteriaIds)
    {
        if (empty($kriteriaIds)) return [];

        return $this->whereIn('id_kriteria', $kriteriaIds)
                    ->findAll();
    }
}
