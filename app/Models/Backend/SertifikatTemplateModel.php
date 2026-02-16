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
        'design_style',
        'is_active'
    ];

    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    protected $validationRules = [
        'halaman' => 'required|in_list[depan,belakang]',
        'file_template' => 'required|max_length[255]',
        'width' => 'required|integer',
        'height' => 'required|integer',
    ];

    protected $validationMessages = [
        'halaman' => [
            'required' => 'Halaman harus dipilih (depan/belakang)',
            'in_list' => 'Halaman tidak valid',
        ],
    ];

    /**
     * Get template by page type
     */
    /**
     * Get active template by page type
     */
    public function getTemplateByHalaman($halaman)
    {
        // For 'depan', there is only one style (1), but let's be safe
        // For 'belakang', return the one with is_active = 1
        return $this->where('halaman', $halaman)
                    ->orderBy('is_active', 'DESC') // Prefer active
                    ->first();
    }

    /**
     * Get template by page type and style
     */
    public function getTemplateByHalamanAndStyle($halaman, $style)
    {
        return $this->where('halaman', $halaman)
                    ->where('design_style', $style)
                    ->first();
    }

    /**
     * Delete template and associated file
     */
    public function deleteTemplate($id)
    {
        if ($template && !empty($template['file_template'])) {
            $filePath = FCPATH . 'writable' . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'sertifikat' . DIRECTORY_SEPARATOR . $template['file_template'];
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
