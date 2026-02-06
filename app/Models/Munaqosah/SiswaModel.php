<?php

/**
 * ====================================================================
 * MODEL: SISWA MODEL
 * ====================================================================
 * Model untuk mengelola data siswa SDIT An-Nahl
 * 
 * @package    Munaqosah
 * @author     SDIT An-Nahl
 */

namespace App\Models\Munaqosah;

use CodeIgniter\Model;

class SiswaModel extends Model
{
    // Nama tabel di database
    protected $table = 'tbl_munaqosah_siswa';
    
    // Primary key
    protected $primaryKey = 'id';
    
    // Tipe return data
    protected $returnType = 'array';
    
    // Gunakan soft delete
    protected $useSoftDeletes = false;
    
    // Kolom yang boleh diisi
    protected $allowedFields = [
        'nisn',
        'nisn',
        'nis',
        'nama_siswa',
        'jenis_kelamin',
        'tanggal_lahir',
        'tempat_lahir',
        'nama_ayah',
        'nama_ibu',
        'alamat',
        'no_hp',
        'hafalan',
        'foto',
        'tahun_ajaran',
        'status',
    ];

    // Gunakan timestamps
    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    // Aturan validasi
    protected $validationRules = [
        'nisn'       => 'required|max_length[20]',
        'nis'        => 'permit_empty|max_length[20]',
        'nama_siswa' => 'required|max_length[100]',
        'jenis_kelamin' => 'required|in_list[L,P]',
    ];

    protected $validationMessages = [
        'nisn' => [
            'required'  => 'NISN harus diisi',
            'max_length' => 'NISN maksimal 20 karakter',
            'is_unique' => 'NISN sudah terdaftar',
        ],
        'nama_siswa' => [
            'required' => 'Nama siswa harus diisi',
        ],
        'jenis_kelamin' => [
            'required' => 'Jenis kelamin harus dipilih',
            'in_list'  => 'Jenis kelamin tidak valid',
        ],
    ];

    // ================================================================
    // METHOD CUSTOM
    // ================================================================

    /**
     * Ambil siswa berdasarkan NISN
     * 
     * @param string $nisn
     * @return array|null
     */
    public function getSiswaByNisn(string $nisn): ?array
    {
        return $this->where('nisn', $nisn)->first();
    }

    /**
     * Ambil semua siswa aktif
     * 
     * @return array
     */
    public function getSiswaAktif(string $tahunAjaran = null): array
    {
        $builder = $this->where('status', 'aktif');
        if ($tahunAjaran) {
            $builder->where('tahun_ajaran', $tahunAjaran);
        }
        return $builder->orderBy('nama_siswa', 'ASC')
                       ->findAll();
    }

    /**
     * Cari siswa berdasarkan nama atau NISN
     * 
     * @param string $keyword
     * @return array
     */
    public function searchSiswa(string $keyword, string $tahunAjaran = null): array
    {
        $builder = $this->groupStart()
                        ->like('nama_siswa', $keyword)
                        ->orLike('nisn', $keyword)
                        ->orLike('nis', $keyword)
                        ->groupEnd();
        
        if ($tahunAjaran) {
            $builder->where('tahun_ajaran', $tahunAjaran);
        }

        return $builder->orderBy('nama_siswa', 'ASC')
                       ->findAll();
    }

    /**
     * Hitung total siswa berdasarkan status
     * 
     * @param string|null $status
     * @return int
     */
    public function countSiswaByStatus(?string $status = null, string $tahunAjaran = null): int
    {
        $builder = $this;
        if ($status) {
            $builder = $builder->where('status', $status);
        }
        if ($tahunAjaran) {
            $builder = $builder->where('tahun_ajaran', $tahunAjaran);
        }
        return $builder->countAllResults();
    }

    /**
     * Ambil data untuk DataTables (server-side)
     * 
     * @param array $request Parameter dari DataTables
     * @return array
     */
    public function getDataForDatatables(array $request, string $tahunAjaran = null): array
    {
        $builder = $this->builder();

        if ($tahunAjaran) {
            $builder->where('tahun_ajaran', $tahunAjaran);
        }

        // Search
        if (!empty($request['search']['value'])) {
            $search = $request['search']['value'];
            $builder->groupStart()
                    ->like('nisn', $search)
                    ->orLike('nis', $search)
                    ->orLike('nama_siswa', $search)
                    ->orLike('alamat', $search)
                    ->groupEnd();
        }

        // Total filtered
        $totalFiltered = $builder->countAllResults(false);

        // Order
        if (!empty($request['order'])) {
            $columns = ['id', 'nisn', 'nama_siswa', 'jenis_kelamin', 'tanggal_lahir', 'status'];
            $orderColumn = $columns[$request['order'][0]['column']] ?? 'id';
            $orderDir = $request['order'][0]['dir'] ?? 'asc';
            $builder->orderBy($orderColumn, $orderDir);
        }

        // Pagination
        $start = $request['start'] ?? 0;
        $length = $request['length'] ?? 10;
        $builder->limit($length, $start);

        return [
            'data'            => $builder->get()->getResultArray(),
            'recordsTotal'    => $this->countAll(),
            'recordsFiltered' => $totalFiltered,
        ];
    }
}
