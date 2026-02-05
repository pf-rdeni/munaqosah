<?php

namespace App\Models\Backend;

use CodeIgniter\Model;

class SertifikatTemplateModel extends Model
{
    protected $table = 'tbl_munaqosah_sertifikat_template';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $protectFields = true;
    protected $allowedFields = [
        'halaman',
        'file_template',
        'width',
        'height',
        'orientation',
    ];

    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    protected $validationRules = [
        'halaman' => 'required|in_list[depan,belakang]|is_unique[tbl_munaqosah_sertifikat_template.halaman,id,{id}]',
        'file_template' => 'required|max_length[255]',
        'width' => 'required|integer',
        'height' => 'required|integer',
    ];

    protected $validationMessages = [
        'halaman' => [
            'required' => 'Halaman harus dipilih (depan/belakang)',
            'in_list' => 'Halaman tidak valid',
            'is_unique' => 'Template untuk halaman ini sudah ada',
        ],
    ];

    /**
     * Get template by page type
     */
    public function getTemplateByHalaman($halaman)
    {
        return $this->where('halaman', $halaman)->first();
    }

    /**
     * Delete template and associated file
     */
    public function deleteTemplate($id)
    {
        $template = $this->find($id);
        if ($template && !empty($template['file_template'])) {
            $filePath = FCPATH . 'uploads/' . $template['file_template'];
            if (file_exists($filePath)) {
                unlink($filePath);
            }
        }

        // Also delete fields
        $fieldModel = new SertifikatFieldModel();
        $fieldModel->where('template_id', $id)->delete();

        return $this->delete($id);
    }
}
