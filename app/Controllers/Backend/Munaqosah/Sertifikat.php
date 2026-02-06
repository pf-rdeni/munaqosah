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
        $templateBelakang = $this->templateModel->getTemplateByHalaman('belakang');

        $data = [
            'page_title' => 'Pengaturan Sertifikat Munaqosah',
            'template_depan' => $templateDepan,
            'template_belakang' => $templateBelakang,
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

            // Check if template exists for this page
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
                ];

                // 3. Update DB
                if ($this->templateModel->update($existingTemplate['id'], $updateData)) {
                    return $this->response->setJSON([
                        'success' => true,
                        'message' => 'Template berhasil diperbarui (Field konfigurasi tetap tersimpan)',
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
     * Halaman konfigurasi field
     */
    public function configure($halaman)
    {
        if (!$this->isLoggedIn()) return redirect()->to('/login');

        $template = $this->templateModel->getTemplateByHalaman($halaman);
        if (!$template) {
            return redirect()->to(base_url('backend/sertifikat'))->with('error', 'Template belum diupload');
        }

        $fields = $this->fieldModel->getFieldsByTemplate($template['id']);
        $availableFields = $this->fieldModel->getAvailableFields($halaman);

        $data = [
            'page_title' => 'Konfigurasi Sertifikat - ' . ucfirst($halaman),
            'template' => $template,
            'fields' => $fields,
            'available_fields' => $availableFields,
            'halaman' => $halaman,
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
