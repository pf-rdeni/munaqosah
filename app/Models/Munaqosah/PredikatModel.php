<?php

namespace App\Models\Munaqosah;

use CodeIgniter\Model;

class PredikatModel extends Model
{
    protected $table = 'tbl_munaqosah_predikat';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    
    protected $allowedFields    = ['id_grup_materi', 'nama_predikat', 'predikat_huruf', 'min_nilai', 'max_nilai', 'deskripsi_global', 'class_css', 'urutan'];

    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    public function getAll()
    {
        return $this->orderBy('urutan', 'ASC')->findAll();
    }

    public function getByGrup($idGrup)
    {
        // 1. Try to find specific predikats for this group
        $specific = $this->where('id_grup_materi', $idGrup)
                         ->orderBy('urutan', 'ASC')
                         ->findAll();
        
        if (!empty($specific)) {
            return $specific;
        }

        // 2. Fallback to Global (id_grup_materi IS NULL)
        return $this->whereNull('id_grup_materi')
                    ->orWhere('id_grup_materi', '')
                    ->orderBy('urutan', 'ASC')
                    ->findAll();
    }

    public function checkRangeCollision($idGrup, $min, $max, $excludeId = null)
    {
        $builder = $this->where('id_grup_materi', $idGrup)
                        ->where('min_nilai', $min)
                        ->where('max_nilai', $max);
        
        if ($excludeId) {
            $builder->where('id !=', $excludeId);
        }

        return $builder->first();
    }

    public function checkHurufCollision($idGrup, $huruf, $excludeId = null)
    {
        if (empty($huruf)) return null;

        $builder = $this->where('id_grup_materi', $idGrup)
                        ->where('predikat_huruf', $huruf);
        
        if ($excludeId) {
            $builder->where('id !=', $excludeId);
        }

        return $builder->first();
    }
}
