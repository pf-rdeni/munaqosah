<?php

namespace App\Models\Munaqosah;

use CodeIgniter\Model;

class JuriModel extends Model
{
    protected $table = 'tbl_munaqosah_juri';
    // Based on usage in NilaiUjianModel, link might be id_juri. 
    // Assuming 'id' is distinct or 'id_juri' IS the primary. 
    // Usually 'id' is safe in CI4.
    protected $primaryKey = 'id'; 
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    
    protected $allowedFields = [
        'user_id',
        'nama_juri',
        'id_grup_materi', // Changed from id_materi
        'id_juri', // Legacy ID column if needed
        'id_grup_juri', // Group Juri Assignment (1-10)
        'username' // The legacy table has username column too, we should sync it or ignore
    ];

    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    /**
     * Ambil data juri lengkap dengan Grup Materi dan User info
     */
    public function getJuriWithMateri()
    {
        return $this->select('tbl_munaqosah_juri.*, gm.nama_grup_materi, u.username, u.email')
                    ->join('tbl_munaqosah_grup_materi gm', 'gm.id = tbl_munaqosah_juri.id_grup_materi', 'left')
                    ->join('users u', 'u.id = tbl_munaqosah_juri.user_id', 'left')
                    ->findAll();
    }
}
