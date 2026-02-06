<?php

namespace App\Controllers\Backend;

use App\Controllers\BaseController;

class TahunAjaran extends BaseController
{
    /**
     * Switch Academic Year via AJAX
     * 
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    public function switch()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Request harus menggunakan AJAX'
            ]);
        }

        if (!$this->isLoggedIn()) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Unauthorized'
            ]);
        }

        $year = $this->request->getPost('tahun_ajaran');

        // Validate format (YYYY/YYYY)
        if (!preg_match('/^\d{4}\/\d{4}$/', $year)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Format tahun ajaran tidak valid. Gunakan format YYYY/YYYY'
            ]);
        }

        // Validate year range (not too far in past or future)
        list($startYear, $endYear) = explode('/', $year);
        $currentYear = (int)date('Y');
        
        if ($startYear < ($currentYear - 5) || $startYear > ($currentYear + 5)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Tahun ajaran terlalu jauh dari tahun saat ini'
            ]);
        }

        // Set the academic year in session
        session()->set('selected_tahun_ajaran', $year);
        
        // Force write session to avoid race conditions with next page load
        session_write_close();
        
        log_message('info', "TahunAjaran Switch - Requested: {$year}, Session Set successfully.");

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Tahun ajaran berhasil diubah ke ' . $year,
            'tahun_ajaran' => $year
        ]);
    }
}
