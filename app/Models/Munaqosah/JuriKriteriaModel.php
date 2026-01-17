<?php

namespace App\Models\Munaqosah;

use CodeIgniter\Model;

class JuriKriteriaModel extends Model
{
    protected $table = 'tbl_munaqosah_juri_kriteria';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    
    protected $allowedFields = [
        'id_juri',
        'id_kriteria'
    ];

    protected $useTimestamps = false;
    protected $createdField = 'created_at';

    /**
     * Get all kriteria assigned to a specific juri
     * @param int $juriId
     * @return array
     */
    public function getByJuri($juriId)
    {
        return $this->where('id_juri', $juriId)->findAll();
    }

    /**
     * Check if juri has custom kriteria settings
     * @param int $juriId
     * @return bool
     */
    public function hasCustomKriteria($juriId)
    {
        return $this->where('id_juri', $juriId)->countAllResults() > 0;
    }

    /**
     * Get count of assigned kriteria for a juri
     * @param int $juriId
     * @return int
     */
    public function getAssignedCount($juriId)
    {
        return $this->where('id_juri', $juriId)->countAllResults();
    }

    /**
     * Get list of juri IDs that have a specific kriteria assigned
     * @param int $kriteriaId
     * @return array
     */
    public function getJuriByKriteria($kriteriaId)
    {
        return $this->where('id_kriteria', $kriteriaId)->findAll();
    }

    /**
     * Save kriteria assignments for a juri
     * Delete existing and insert new ones
     * @param int $juriId
     * @param array $kriteriaIds
     * @return bool
     */
    public function saveKriteriaForJuri($juriId, $kriteriaIds = [])
    {
        // Delete existing assignments
        $this->where('id_juri', $juriId)->delete();
        
        // If empty array (use default/all), don't insert anything
        if (empty($kriteriaIds)) {
            return true;
        }
        
        // Insert new assignments
        $data = [];
        foreach ($kriteriaIds as $kriteriaId) {
            $data[] = [
                'id_juri' => $juriId,
                'id_kriteria' => $kriteriaId
            ];
        }
        
        return $this->insertBatch($data);
    }

    /**
     * Get kriteria IDs assigned to a juri
     * @param int $juriId
     * @return array
     */
    public function getKriteriaIdsByJuri($juriId)
    {
        $results = $this->where('id_juri', $juriId)->findAll();
        return array_column($results, 'id_kriteria');
    }
}
