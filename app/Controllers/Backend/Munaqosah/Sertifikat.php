<?php

namespace App\Controllers\Backend\Munaqosah;

use App\Controllers\BaseController;
use App\Models\Backend\SertifikatTemplateModel;
use App\Models\Backend\SertifikatFieldModel;
use App\Helpers\CertificateGenerator;

class Sertifikat extends BaseController
{
    protected $templateModel;
    protected $fieldModel;

    public function __construct()
    {
        $this->templateModel = new SertifikatTemplateModel();
        $this->fieldModel = new SertifikatFieldModel();
    }

    /**
     * Halaman utama pengaturan sertifikat
     */
    public function index()
    {
        if (!$this->isLoggedIn()) return redirect()->to('/login');

        // Get existing templates
        $templateDepan = $this->templateModel->getTemplateByHalaman('depan');
        
        // Fetch ACTIVE back template
        $templateBelakang = $this->templateModel->getTemplateByHalaman('belakang');
        
        // Also fetch specific style templates to pass to view for "Configure" links
        // If active one is Style 1, we might also want to know if Style 2 exists (to show its image if user clicks Option 2)
        // But for allowed complexity, let's just rely on AJAX or simplest approach:
        // The View will need to know the ID of Style 1 and Style 2 templates if they exist.
        
        $templateBelakang1 = $this->templateModel->getTemplateByHalamanAndStyle('belakang', 'option1');
        $templateBelakang2 = $this->templateModel->getTemplateByHalamanAndStyle('belakang', 'option2');
        
        // Current active style
        $activeStyle = $templateBelakang ? ($templateBelakang['design_style'] ?? 'option1') : 'option1';

        $data = [
            'page_title' => 'Pengaturan Sertifikat Munaqosah',
            'template_depan' => $templateDepan,
            'template_belakang' => $templateBelakang, // active one
            
            // Pass specific templates for options
            'template_belakang_1' => $templateBelakang1,
            'template_belakang_2' => $templateBelakang2,
            
            'active_style' => $activeStyle,

            'user' => $this->getCurrentUser(),
            'tahunAjaran' => $this->getTahunAjaran(),
            'availableTahunAjaran' => $this->getAvailableTahunAjaran(),
        ];

        return view('backend/sertifikat/setting', $data);
    }

    /**
     * Upload template (AJAX)
     */
    public function uploadTemplate()
    {
        if (!$this->isLoggedIn()) {
            return $this->response->setJSON(['success' => false, 'message' => 'Session expired']);
        }

        $halaman = $this->request->getPost('halaman'); // depan or belakang
        $orientation = $this->request->getPost('orientation') ?? 'landscape';
        // Note: For 'belakang', we should upload to the ACTIVE style, OR receive style from form.
        // As per workflow, user selects Style -> Active is updated -> Upload targets Active.
        
        if (!in_array($halaman, ['depan', 'belakang'])) {
            return $this->response->setJSON(['success' => false, 'message' => 'Halaman tidak valid']);
        }

        $file = $this->request->getFile('template_file');
        if (!$file || !$file->isValid()) {
            return $this->response->setJSON(['success' => false, 'message' => 'File template harus diupload']);
        }

        // Validate file type
        if (!in_array($file->getExtension(), ['jpg', 'jpeg', 'png'])) {
            return $this->response->setJSON(['success' => false, 'message' => 'File harus berformat JPG atau PNG']);
        }

        // Create upload directory if not exists
        $uploadPath = FCPATH . 'uploads/sertifikat/';
        if (!is_dir($uploadPath)) {
            mkdir($uploadPath, 0755, true);
        }

        // Generate unique filename
        $newName = 'template_' . $halaman . '_' . time() . '.' . $file->getExtension();
        
        try {
            // Move file
            $file->move($uploadPath, $newName);

            // Get image dimensions
            $imagePath = $uploadPath . $newName;
            list($width, $height) = getimagesize($imagePath);

            // Determine Target Record
            $existingTemplate = $this->templateModel->getTemplateByHalaman($halaman);
            
            if ($existingTemplate) {
                // UPDATE existing record
                
                // 1. Delete old physical file
                if (!empty($existingTemplate['file_template'])) {
                    $oldFilePath = FCPATH . 'uploads/' . $existingTemplate['file_template'];
                    if (file_exists($oldFilePath)) {
                        unlink($oldFilePath);
                    }
                }

                // 2. Prepare update data
                $updateData = [
                    'file_template' => 'sertifikat/' . $newName,
                    'width' => $width,
                    'height' => $height,
                    'orientation' => $orientation,
                    'is_active' => 1 // Ensure it stays active
                ];

                // 3. Update DB
                if ($this->templateModel->update($existingTemplate['id'], $updateData)) {
                    return $this->response->setJSON([
                        'success' => true,
                        'message' => 'Template berhasil diperbarui',
                        'template_id' => $existingTemplate['id']
                    ]);
                }
            } else {
                // INSERT new record
                $data = [
                    'halaman' => $halaman,
                    'file_template' => 'sertifikat/' . $newName,
                    'width' => $width,
                    'height' => $height,
                    'orientation' => $orientation,
                    'design_style' => ($halaman == 'depan') ? 1 : 'option1', // Default
                    'is_active' => 1
                ];

                if ($this->templateModel->insert($data)) {
                    return $this->response->setJSON([
                        'success' => true,
                        'message' => 'Template berhasil diupload',
                        'template_id' => $this->templateModel->getInsertID()
                    ]);
                }
            }

            return $this->response->setJSON(['success' => false, 'message' => 'Gagal menyimpan template']);

        } catch (\Exception $e) {
            return $this->response->setJSON(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    /**
     * Simpan Style Desain (Option 1 vs Option 2)
     */
    public function saveDesignStyle()
    {
        if (!$this->isLoggedIn()) {
            return $this->response->setJSON(['success' => false, 'message' => 'Session expired']);
        }

        $style = $this->request->getPost('design_style');
        
        if (!in_array($style, ['option1', 'option2'])) {
             return $this->response->setJSON(['success' => false, 'message' => 'Style tidak valid']);
        }

        $this->templateModel->db->transStart();

        try {
            // 1. Deactivate ALL 'belakang' templates
            $this->templateModel->where('halaman', 'belakang')->set(['is_active' => 0])->update();
            
            // 2. Activate the selected style
            // Check if it exists
            $existing = $this->templateModel->getTemplateByHalamanAndStyle('belakang', $style);
            
            if ($existing) {
                $this->templateModel->update($existing['id'], ['is_active' => 1]);
                $message = 'Desain berhasil diubah ke Opsi ' . $style;
            } else {
                // If it doesn't exist, we should probably create a placeholder record?
                // Or just creating it with no file.
                // INSERT placeholder
                $inserted = $this->templateModel->skipValidation(true)->insert([
                    'halaman' => 'belakang',
                    'design_style' => $style,
                    'is_active' => 1,
                    'file_template' => '', // Empty initially
                    'width' => 0,
                    'height' => 0,
                    'orientation' => 'landscape' // Default
                ]);

                if (!$inserted) {
                    throw new \Exception('Gagal membuat template placeholder: ' . implode(', ', $this->templateModel->errors()));
                }

                $message = 'Desain Opsi ' . $style . ' diaktifkan. Silakan upload template.';
            }

            $this->templateModel->db->transComplete();
            
            return $this->response->setJSON(['success' => true, 'message' => $message]);

        } catch (\Exception $e) {
            return $this->response->setJSON(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    /**
     * Halaman konfigurasi field
     * @param string|int $identifier Halaman (depan/belakang) OR Template ID
     */
    public function configure($identifier)
    {
        if (!$this->isLoggedIn()) return redirect()->to('/login');

        // Check if identifier is numeric (ID) or string (halaman)
        if (is_numeric($identifier)) {
            $template = $this->templateModel->find($identifier);
        } else {
            $template = $this->templateModel->getTemplateByHalaman($identifier);
        }

        if (!$template) {
            return redirect()->to(base_url('backend/sertifikat'))->with('error', 'Template belum diupload atau tidak ditemukan');
        }

        $halaman = $template['halaman'];
        $fields = $this->fieldModel->getFieldsByTemplate($template['id']);
        
        $designStyle = $template['design_style'] ?? 1;
        $availableFields = $this->fieldModel->getAvailableFields($halaman, $designStyle);

        // Identify logic for title
        $titleSuffix = ucfirst($halaman);
        if ($halaman == 'belakang') {
             $style = $template['design_style'] ?? 1;
             $titleSuffix .= " (Opsi $style)";
        }

        $data = [
            'page_title' => 'Konfigurasi Sertifikat - ' . $titleSuffix,
            'template' => $template,
            'fields' => $fields,
            'available_fields' => $availableFields,
            'halaman' => $halaman, // Still needed for JS helpers?
            'user' => $this->getCurrentUser(),
            'tahunAjaran' => $this->getTahunAjaran(),
            'availableTahunAjaran' => $this->getAvailableTahunAjaran(),
        ];

        return view('backend/sertifikat/configure', $data);
    }

    /**
     * Simpan konfigurasi field (AJAX)
     */
    public function saveFieldConfig()
    {
        if (!$this->isLoggedIn()) {
            return $this->response->setJSON(['success' => false, 'message' => 'Session expired']);
        }

        // Read JSON body
        $json = $this->request->getJSON(true); // true = return as array
        $templateId = $json['template_id'] ?? null;
        $fieldsData = $json['fields'] ?? null;

        if (!$templateId) {
            return $this->response->setJSON(['success' => false, 'message' => 'ID Template tidak valid']);
        }

        $this->templateModel->db->transStart();

        try {
            // 1. Delete existing fields
            $this->fieldModel->where('template_id', $templateId)->delete();

            // 2. Insert new fields
            if (!empty($fieldsData) && is_array($fieldsData)) {
                $insertData = [];
                foreach ($fieldsData as $field) {
                    // Debug: Log border data
                    log_message('debug', 'Field: ' . ($field['name'] ?? 'unknown') . 
                        ' | has_border type: ' . gettype($field['has_border'] ?? null) . 
                        ' | has_border value: ' . var_export($field['has_border'] ?? null, true));
                    
                $insertData[] = [
                        'template_id' => $templateId,
                        'field_name' => $field['name'],
                        'field_label' => $field['label'],
                        'pos_x' => (int) $field['x'],
                        'pos_y' => (int) $field['y'],
                        'font_family' => $field['font_family'] ?? 'Arial',
                        'font_size' => (int) ($field['font_size'] ?? 12),
                        'font_style' => $field['font_style'] ?? 'N',
                        'text_align' => $field['text_align'] ?? 'L',
                        'text_color' => $field['text_color'] ?? '#000000',
                        'max_width' => (int) ($field['max_width'] ?? 0),
                        'border_settings' => json_encode([
                            'enabled' => isset($field['has_border']) ? (bool)$field['has_border'] : false,
                            'color' => $field['border_color'] ?? '#000000',
                            'width' => (int) ($field['border_width'] ?? 1)
                        ])
                    ];
                }

                if (!empty($insertData)) {
                    $this->fieldModel->insertBatch($insertData);
                }
            }

            $this->templateModel->db->transComplete();

            if ($this->templateModel->db->transStatus() === false) {
                return $this->response->setJSON(['success' => false, 'message' => 'Gagal menyimpan konfigurasi']);
            }

            return $this->response->setJSON(['success' => true, 'message' => 'Konfigurasi berhasil disimpan']);

        } catch (\Exception $e) {
            return $this->response->setJSON(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    /**
     * Preview Sertifikat (Dummy Data)
     */
    public function preview($halaman)
    {
        $template = $this->templateModel->getTemplateByHalaman($halaman);
        if (!$template) {
            return "Template tidak ditemukan";
        }

        $fields = $this->fieldModel->getFieldsByTemplate($template['id']);
        
        // Dummy Data Generator
        $dummyData = [
            'nama_peserta' => 'AHMAD FAUZI BIN ABDULLAH',
            'nomor_peserta' => '001/MUNA/2026',
            'nisn' => '0012345678',
            'nis' => '1001',
            'tempat_lahir' => 'Bandung',
            'tanggal_lahir' => '15 Januari 2015',
            'jenis_kelamin' => 'Laki-laki',
            'nama_ayah' => 'Abdullah bin Ahmad',
            'alamat' => 'Jl. Merpati No. 10, Bandung',
            'nama_sekolah' => 'SDIT AN-NAHL',
            'predikat' => 'MUMTAZ',
            'nilai_huruf' => 'A',
            'nilai_rata_rata' => '95.50',
            'tanggal_terbit' => 'Bekasi, 04 Februari 2026',
            'nomor_sertifikat' => 'SERT/2026/001',
            'kepala_sekolah' => 'Sri Maningsih, S.Pd',
            'nip_kepala' => '198001012005011001',
            // Simple QR placeholder - in real app use Helper to generate QR image path
            'qr_code' => '', // Empty for now or path to dummy QR
            'foto_peserta' => '', // Empty
        ];

        // Generate PDF
        try {
            $generator = new CertificateGenerator($template, $fields);
            $generator->setData($dummyData)->generate();
            
            // Stream inline
            $generator->stream('Preview_Sertifikat_' . ucfirst($halaman) . '.pdf', ['Attachment' => false]);
            
        } catch (\Exception $e) {
            return "Error: " . $e->getMessage();
        }
    }

    /**
     * Delete Template
     */
    public function delete($halaman)
    {
        if (!$this->isLoggedIn()) return redirect()->to('/login');

        $template = $this->templateModel->getTemplateByHalaman($halaman);
        if ($template) {
            $this->templateModel->deleteTemplate($template['id']);
            return redirect()->back()->with('success', 'Template berhasil dihapus');
        }
        return redirect()->back()->with('error', 'Template tidak ditemukan');
    }
}
