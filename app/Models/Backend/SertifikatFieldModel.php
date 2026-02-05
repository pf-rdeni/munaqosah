<?php

namespace App\Models\Backend;

use CodeIgniter\Model;

class SertifikatFieldModel extends Model
{
    protected $table = 'tbl_munaqosah_sertifikat_fields';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $protectFields = true;
    protected $allowedFields = [
        'template_id',
        'field_name',
        'field_label',
        'pos_x',
        'pos_y',
        'font_family',
        'font_size',
        'font_style',
        'text_align',
        'text_color',
        'max_width'
    ];

    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    protected $validationRules = [
        'template_id' => 'required|integer',
        'field_name' => 'required|max_length[100]',
        'field_label' => 'required|max_length[100]',
        'pos_x' => 'required|integer',
        'pos_y' => 'required|integer',
    ];

    /**
     * Get all fields for a template
     */
    public function getFieldsByTemplate($templateId)
    {
        return $this->where('template_id', $templateId)
                    ->orderBy('id', 'ASC')
                    ->findAll();
    }

    /**
     * Get available field definitions
     */
    /**
     * Get available field definitions
     */
    public function getAvailableFields($halaman = 'depan')
    {
        // Common Real Data Fields
        $commonFields = [
            [
                'name' => 'nama_peserta',
                'label' => 'Nama Peserta',
                'sample' => 'AHMAD FAUZI'
            ],
            [
                'name' => 'nomor_peserta',
                'label' => 'Nomor Peserta',
                'sample' => '001/MUNA/2026'
            ],
            [
                'name' => 'nisn',
                'label' => 'NISN',
                'sample' => '0012345678'
            ],
            [
                'name' => 'nis',
                'label' => 'NIS (Lokal)',
                'sample' => '1001'
            ],
            [
                'name' => 'tempat_lahir',
                'label' => 'Tempat Lahir',
                'sample' => 'Bandung'
            ],
            [
                'name' => 'tanggal_lahir',
                'label' => 'Tanggal Lahir',
                'sample' => '15 Januari 2015'
            ],
             [
                'name' => 'jenis_kelamin',
                'label' => 'Jenis Kelamin',
                'sample' => 'Laki-laki'
            ],
            [
                'name' => 'nama_ayah',
                'label' => 'Nama Ayah',
                'sample' => 'Budi Santoso'
            ],
            [
                'name' => 'alamat',
                'label' => 'Alamat',
                'sample' => 'Jl. Merpati No. 10'
            ],
            [
                'name' => 'nama_sekolah',
                'label' => 'Nama Sekolah/TPQ',
                'sample' => 'SDIT AN-NAHL'
            ],
            [
                'name' => 'predikat',
                'label' => 'Predikat (Sebut/Label)',
                'sample' => 'Sangat Baik'
            ],
            [
                'name' => 'nilai_huruf',
                'label' => 'Nilai Huruf (Grade)',
                'sample' => 'A'
            ],
            [
                'name' => 'nilai_rata_rata',
                'label' => 'Nilai Rata-Rata',
                'sample' => '85.50'
            ],
             [
                'name' => 'tanggal_terbit',
                'label' => 'Tanggal Terbit (Titimangsa)',
                'sample' => 'Bekasi, 04 Februari 2026'
            ],
             [
                'name' => 'nomor_sertifikat',
                'label' => 'Nomor Sertifikat',
                'sample' => 'SERT/2026/001'
            ],
             [
                'name' => 'kepala_sekolah',
                'label' => 'Kepala Sekolah',
                'sample' => 'Sri Maningsih'
            ],
             [
                'name' => 'nip_kepala',
                'label' => 'NIP Kepala Sekolah',
                'sample' => '19800101...'
            ],
        ];

        if ($halaman == 'belakang') {
            // Back Page: Common Fields + Table Block
            $backFields = array_merge($commonFields, [
                [
                    'name' => 'block_table',
                    'label' => 'Block: Tabel Nilai',
                    'sample' => '[TABEL NILAI]'
                ]
            ]);
            return $backFields;
        }

        // Front Page: Common Fields + Images
        $frontFields = array_merge($commonFields, [
             [
                'name' => 'qr_code',
                'label' => 'QR Code Validasi',
                'sample' => '[QR CODE]'
            ],
             [
                'name' => 'foto_peserta',
                'label' => 'Foto Peserta',
                'sample' => '[FOTO]'
            ],
        ]);
        
        return $frontFields;
    }
}
