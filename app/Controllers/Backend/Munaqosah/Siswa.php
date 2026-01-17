<?php

/**
 * ====================================================================
 * SISWA CONTROLLER
 * ====================================================================
 * Controller untuk manajemen data siswa
 * 
 * @package    Munaqosah
 * @author     SDIT An-Nahl
 */

namespace App\Controllers\Backend\Munaqosah;

use App\Controllers\BaseController;
use App\Models\Munaqosah\SiswaModel;
use App\Models\Munaqosah\TblAlquranModel;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\IOFactory;

class Siswa extends BaseController
{
    protected $siswaModel;
    protected $alquranModel;

    public function __construct()
    {
        $this->siswaModel = new SiswaModel();
        $this->alquranModel = new TblAlquranModel();
    }

    /**
     * Halaman daftar siswa
     *
     * @return string|\CodeIgniter\HTTP\RedirectResponse
     */
    public function index()
    {
        // Cek login
        if (!$this->isLoggedIn()) {
            return redirect()->to('/login');
        }

        $data = [
            'title'      => 'Data Siswa',
            'pageTitle'  => 'Data Siswa',
            'breadcrumb' => [
                ['title' => 'Home', 'url' => '/backend/dashboard'],
                ['title' => 'Data Siswa', 'url' => ''],
            ],
            'user'       => $this->getCurrentUser(),
            'user'       => $this->getCurrentUser(),
            'siswaList'  => $this->processSiswaList($this->siswaModel->orderBy('nama_siswa', 'ASC')->findAll()),
            'daftarSurah'=> $this->alquranModel->getDaftarSurah(), // Fallback / List Unik
            'dataAlquran'=> $this->alquranModel->getAllSurahData(), // Full Data untuk JS Filtering
        ];

        return view('backend/siswa/index', $data);
    }

    /**
     * Halaman form tambah siswa
     *
     * @return string|\CodeIgniter\HTTP\RedirectResponse
     */
    public function detail($id)
    {
        if (!$this->isLoggedIn()) {
            return redirect()->to('/login');
        }

        $siswa = $this->siswaModel->find($id);
        
        if (!$siswa) {
            return redirect()->to('/backend/siswa')->with('error', 'Data siswa tidak ditemukan.');
        }

        // Process Hafalan for display
        // Reuse processSiswaList logic but for single item
        // But processSiswaList expects array of items. 
        // Let's just wrap it in array and take first result.
        $processed = $this->processSiswaList([$siswa])[0];

        $data = [
            'title'      => 'Detail Siswa',
            'pageTitle'  => 'Detail Data Siswa', // Dipersingkat agar tidak kepanjangan
            'breadcrumb' => [
                ['title' => 'Home', 'url' => '/backend/dashboard'],
                ['title' => 'Data Siswa', 'url' => '/backend/siswa'],
                ['title' => 'Detail', 'url' => ''],
            ],
            'user'       => $this->getCurrentUser(),
            'siswa'      => $siswa,
        ];

        return view('backend/siswa/detail', $data);
    }

    public function create()
    {
        if (!$this->isLoggedIn()) {
            return redirect()->to('/login');
        }

        $data = [
            'title'      => 'Tambah Siswa',
            'pageTitle'  => 'Tambah Siswa Baru',
            'breadcrumb' => [
                ['title' => 'Home', 'url' => '/backend/dashboard'],
                ['title' => 'Data Siswa', 'url' => '/backend/siswa'],
                ['title' => 'Tambah', 'url' => ''],
            ],
            'user'       => $this->getCurrentUser(),
            'validation' => \Config\Services::validation(),
        ];

        return view('backend/siswa/create', $data);
    }

    /**
     * Proses simpan siswa baru
     *
     * @return \CodeIgniter\HTTP\RedirectResponse
     */
    public function store()
    {
        if (!$this->isLoggedIn()) {
            return redirect()->to('/login');
        }

        // Validasi
        $rules = [
            'nisn'          => 'required|max_length[20]|is_unique[tbl_munaqosah_siswa.nisn]',
            'nama_siswa'    => 'required|max_length[100]',
            'jenis_kelamin' => 'required|in_list[L,P]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()
                ->withInput()
                ->with('errors', $this->validator->getErrors());
        }

        // Simpan data
        $data = [
            'nisn'          => $this->request->getPost('nisn'),
            'nama_siswa'    => $this->cleanInput($this->request->getPost('nama_siswa')),
            'jenis_kelamin' => $this->request->getPost('jenis_kelamin'),
            'tanggal_lahir' => $this->request->getPost('tanggal_lahir'),
            'tempat_lahir'  => $this->cleanInput($this->request->getPost('tempat_lahir')),
            'nama_ayah'     => $this->cleanInput($this->request->getPost('nama_ayah')),
            'nama_ibu'      => $this->cleanInput($this->request->getPost('nama_ibu')),
            'alamat'        => $this->cleanInput($this->request->getPost('alamat')),
            'no_hp'         => $this->request->getPost('no_hp'),
            'status'        => 'aktif',
        ];

        // Insert siswa first to get ID
        $insertId = $this->siswaModel->insert($data);
        
        if ($insertId) {
            // Process photo if provided
            $fotoBase64 = $this->request->getPost('foto_base64');
            if (!empty($fotoBase64)) {
                try {
                    // Extract base64 data
                    $parts = explode(',', $fotoBase64);
                    $imageData = base64_decode(end($parts));
                    
                    // Generate filename
                    $filename = 'foto_' . $insertId . '_' . time() . '.jpg';
                    $uploadPath = FCPATH . 'uploads/foto_siswa/';
                    
                    // Create directory if not exists
                    if (!is_dir($uploadPath)) {
                        mkdir($uploadPath, 0755, true);
                    }
                    
                    // Save file
                    file_put_contents($uploadPath . $filename, $imageData);
                    
                    // Update database with foto path
                    $this->siswaModel->update($insertId, [
                        'foto' => 'uploads/foto_siswa/' . $filename
                    ]);
                } catch (\Exception $e) {
                    log_message('error', 'Error saving photo for new student: ' . $e->getMessage());
                }
            }
            
            return redirect()->to('/backend/siswa')
                ->with('success', 'Data siswa berhasil ditambahkan');
        }

        return redirect()->back()
            ->withInput()
            ->with('error', 'Gagal menyimpan data siswa');
    }

    /**
     * Halaman form edit siswa
     *
     * @param int $id
     * @return string|\CodeIgniter\HTTP\RedirectResponse
     */
    public function edit(int $id)
    {
        if (!$this->isLoggedIn()) {
            return redirect()->to('/login');
        }

        $siswa = $this->siswaModel->find($id);
        if (!$siswa) {
            return redirect()->to('/backend/siswa')
                ->with('error', 'Data siswa tidak ditemukan');
        }

        $data = [
            'title'      => 'Edit Siswa',
            'pageTitle'  => 'Edit Data Siswa',
            'breadcrumb' => [
                ['title' => 'Home', 'url' => '/backend/dashboard'],
                ['title' => 'Data Siswa', 'url' => '/backend/siswa'],
                ['title' => 'Edit', 'url' => ''],
            ],
            'user'       => $this->getCurrentUser(),
            'siswa'      => $siswa,
            'validation' => \Config\Services::validation(),
        ];

        return view('backend/siswa/edit', $data);
    }

    /**
     * Proses update siswa
     *
     * @param int $id
     * @return \CodeIgniter\HTTP\RedirectResponse
     */
    public function update(int $id)
    {
        if (!$this->isLoggedIn()) {
            return redirect()->to('/login');
        }

        // Validasi
        $rules = [
            'nama_siswa'    => 'required|max_length[100]',
            'jenis_kelamin' => 'required|in_list[L,P]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()
                ->withInput()
                ->with('errors', $this->validator->getErrors());
        }

        // Update data (Exclude NISN)
        $data = [
            // 'nisn' => $this->request->getPost('nisn'), // NISN tidak boleh diubah
            'nama_siswa'    => $this->cleanInput($this->request->getPost('nama_siswa')),
            'jenis_kelamin' => $this->request->getPost('jenis_kelamin'),
            'tanggal_lahir' => $this->request->getPost('tanggal_lahir'),
            'tempat_lahir'  => $this->cleanInput($this->request->getPost('tempat_lahir')),
            'nama_ayah'     => $this->cleanInput($this->request->getPost('nama_ayah')),
            'nama_ibu'      => $this->cleanInput($this->request->getPost('nama_ibu')),
            'alamat'        => $this->cleanInput($this->request->getPost('alamat')),
            'no_hp'         => $this->request->getPost('no_hp'),
            'status'        => $this->request->getPost('status') ?? 'aktif',
        ];

        if ($this->siswaModel->update($id, $data)) {
            return redirect()->to('/backend/siswa')
                ->with('success', 'Data siswa berhasil diperbarui');
        }

        $errors = $this->siswaModel->errors();
        $errorMsg = 'Gagal memperbarui data siswa.';
        
        if (!empty($errors)) {
            $errorMsg .= ' ' . implode(', ', $errors);
        }

        return redirect()->back()
            ->withInput()
            ->with('error', $errorMsg);
    }

    /**
     * Update Data Hafalan (Modal)
     */
    public function updateHafalan()
    {
        if (!$this->isLoggedIn()) {
            return redirect()->to('/login');
        }

        $id = $this->request->getPost('id_siswa');
        
        // Ambil array dari form
        $juz = $this->request->getPost('juz');
        $mulai = $this->request->getPost('mulai');
        $akhir = $this->request->getPost('akhir');
        
        // Ambil Referensi Surah untuk Lookup No Surah
        $refSurah = $this->alquranModel->getDaftarSurah();
        $surahMap = [];
        foreach ($refSurah as $s) {
            // Mapping Nama Surah ke No Surah
            // Gunakan lowercase/trim untuk antisipasi perbedaan minor
            $key = strtolower(trim($s['nama_surah']));
            $surahMap[$key] = $s['no_surah'];
        }

        $dataHafalan = [];
        
        if (is_array($juz)) {
            foreach ($juz as $key => $val) {
                if (!empty($val)) {
                    $namaMulai = $mulai[$key] ?? '';
                    $namaAkhir = $akhir[$key] ?? '';
                    
                    // Lookup No Surah
                    $noMulai = $surahMap[strtolower(trim($namaMulai))] ?? null;
                    $noAkhir = $surahMap[strtolower(trim($namaAkhir))] ?? null;

                    $dataHafalan[] = [
                        'juz'              => $val,
                        'no_surah_mulai'   => $noMulai,
                        'nama_surah_mulai' => $namaMulai,
                        'no_surah_akhir'   => $noAkhir,
                        'nama_surah_akhir' => $namaAkhir,
                    ];
                }
            }
        }
        
        // Simpan sebagai JSON
        $data = [
            'hafalan' => json_encode($dataHafalan)
        ];

        if ($this->siswaModel->update($id, $data)) {
            return redirect()->back()->with('success', 'Data hafalan berhasil diupdate.');
        }

        return redirect()->back()->with('error', 'Gagal update hafalan.');
    }

    /**
     * Proses hapus siswa
     *
     * @param int $id
     * @return \CodeIgniter\HTTP\RedirectResponse
     */
    public function delete(int $id)
    {
        if (!$this->isLoggedIn()) {
            return redirect()->to('/login');
        }

        $siswa = $this->siswaModel->find($id);
        if (!$siswa) {
            return redirect()->to('/backend/siswa')
                ->with('error', 'Data siswa tidak ditemukan');
        }

        if ($this->siswaModel->delete($id)) {
            return redirect()->to('/backend/siswa')
                ->with('success', 'Data siswa berhasil dihapus');
        }

        return redirect()->to('/backend/siswa')
            ->with('error', 'Gagal menghapus data siswa');
    }

    /**
     * API untuk DataTables
     *
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    public function getData()
    {
        if (!$this->isLoggedIn()) {
            return $this->response->setJSON(['error' => 'Unauthorized']);
        }

        $request = [
            'draw'   => $this->request->getGet('draw'),
            'start'  => $this->request->getGet('start'),
            'length' => $this->request->getGet('length'),
            'search' => $this->request->getGet('search'),
            'order'  => $this->request->getGet('order'),
        ];

        $result = $this->siswaModel->getDataForDatatables($request);
        $result['draw'] = $request['draw'];

        return $this->response->setJSON($result);
    }

    /**
     * Download template Excel
     */
    public function downloadTemplate()
    {
        if (!$this->isLoggedIn()) {
            return redirect()->to('/login');
        }

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Set Header
        $sheet->setCellValue('A1', 'NISN');
        $sheet->setCellValue('B1', 'NAMA SISWA');
        $sheet->setCellValue('C1', 'JENIS KELAMIN (L/P)');
        $sheet->setCellValue('D1', 'TANGGAL LAHIR (YYYY-MM-DD)');
        $sheet->setCellValue('E1', 'TEMPAT LAHIR');
        $sheet->setCellValue('F1', 'NAMA AYAH');
        $sheet->setCellValue('G1', 'NAMA IBU');
        $sheet->setCellValue('H1', 'ALAMAT');
        $sheet->setCellValue('I1', 'NO HP (Opsional)');

        // Contoh Data
        $sheet->setCellValue('A2', '1234567890');
        $sheet->setCellValue('B2', 'AHMAD FAUZAN');
        $sheet->setCellValue('C2', 'L');
        $sheet->setCellValue('D2', '2015-05-20');
        $sheet->setCellValue('E2', 'BEKASI');
        $sheet->setCellValue('F2', 'ABDUL');
        $sheet->setCellValue('G2', 'SITI');
        $sheet->setCellValue('H2', 'JL. JAMBU NO. 10');

        // Styling Header
        $headerStyle = [
            'font' => ['bold' => true],
            'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['argb' => 'FFCCCCCC'],
            ],
        ];
        $sheet->getStyle('A1:H1')->applyFromArray($headerStyle);

        // Auto Size Columns
        foreach (range('A', 'H') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $writer = new Xlsx($spreadsheet);
        $filename = 'Template_Import_Siswa.xlsx';

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $writer->save('php://output');
        exit;
    }

    /**
     * Proses Import Excel
     */
    /**
     * Proses Import Excel
     */
    /**
     * Proses Preview Import Excel
     */
    public function import()
    {
        if (!$this->isLoggedIn()) {
            return redirect()->to('/login');
        }

        $file = $this->request->getFile('file_excel');
        $importType = $this->request->getPost('import_type') ?? 'template';

        if ($file && $file->isValid() && !$file->hasMoved()) {
            $ext = $file->getClientExtension();

            if (!in_array($ext, ['xls', 'xlsx'])) {
                return redirect()->back()->with('error', 'Format file tidak valid. Gunakan .xls atau .xlsx');
            }

            // Pindahkan file ke folder temporary
            $newName = $file->getRandomName();
            $file->move(WRITEPATH . 'uploads/temp_import', $newName);
            
            // Proses Baca File (Hanya Preview)
            try {
                if ($ext === 'xls') {
                    $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xls();
                } else {
                    $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
                }

                $spreadsheet = $reader->load(WRITEPATH . 'uploads/temp_import/' . $newName);
                $sheet = $spreadsheet->getActiveSheet()->toArray(null, true, true, true);

                $previewData = [];

                foreach ($sheet as $key => $row) {
                    $nisn = null;
                    $data = [];
                    $isValid = true;
                    $errorMsg = null;

                    // LOGIC IMPORT TEMPLATE
                    if ($importType === 'template') {
                        if ($key == 1) continue; // Skip header
                        if (empty($row['A']) && empty($row['B'])) continue; // Skip empty row

                        $nisn = $row['A'];
                        $data = [
                            'nisn'          => $nisn,
                            'nama_siswa'    => $this->cleanInput($row['B']),
                            'jenis_kelamin' => strtoupper($row['C']),
                            'tanggal_lahir' => $row['D'],
                            'tempat_lahir'  => $this->cleanInput($row['E']),
                            'nama_ayah'     => $this->cleanInput($row['F']),
                            'nama_ibu'      => $this->cleanInput($row['G']),
                            'no_hp'         => $row['I'] ?? null,
                        ];
                    } 
                    // LOGIC IMPORT DAPODIK
                    else {
                        if ($key < 7) continue; // Skip header
                        if (empty($row['B'])) continue; // Skip empty row

                        $nisn = $row['E']; 
                        $data = [
                            'nisn'          => $nisn,
                            'nama_siswa'    => $this->cleanInput($row['B']),
                            'jenis_kelamin' => strtoupper($row['D']),
                            'tanggal_lahir' => $row['G'],
                            'tempat_lahir'  => $this->cleanInput($row['F']),
                            'nama_ayah'     => $this->cleanInput($row['Y']),
                            'nama_ibu'      => $this->cleanInput($row['AE']),
                            'no_hp'         => $row['T'] ?? null, // Kolom T Dapodik
                        ];
                    }

                    // Validasi Dasar (Duplikat NISN)
                    if (empty($nisn)) {
                        $isValid = false;
                        $errorMsg = 'NISN Kosong';
                    } elseif ($this->siswaModel->where('nisn', $nisn)->first()) {
                        $isValid = false;
                        $errorMsg = 'NISN Duplikat';
                    }

                    $previewData[] = [
                        'row_index'     => $key,
                        'nisn'          => $nisn,
                        'nama_siswa'    => $data['nama_siswa'],
                        'jenis_kelamin' => $data['jenis_kelamin'],
                        'tanggal_lahir' => $data['tanggal_lahir'],
                        'tempat_lahir'  => $data['tempat_lahir'],
                        'nama_ayah'     => $data['nama_ayah'],
                        'nama_ibu'      => $data['nama_ibu'],
                        'no_hp'         => $data['no_hp'],
                        'valid'         => $isValid,
                        'error'         => $errorMsg,
                    ];
                }

                $data = [
                    'title'       => 'Preview Import',
                    'pageTitle'   => 'Konfirmasi Data Import',
                    'breadcrumb'  => [
                        ['title' => 'Home', 'url' => '/backend/dashboard'],
                        ['title' => 'Data Siswa', 'url' => '/backend/siswa'],
                        ['title' => 'Preview Import', 'url' => ''],
                    ],
                    'user'        => $this->getCurrentUser(),
                    'importType'  => $importType,
                    'tempFileName'=> $newName, // Nama file untuk diproses di tahap selanjutnya
                    'sheetData'   => $previewData,
                ];

                return view('backend/siswa/import_preview', $data);

            } catch (\Exception $e) {
                return redirect()->back()->with('error', 'Terjadi kesalahan saat membaca file: ' . $e->getMessage());
            }
        }

        return redirect()->back()->with('error', 'Gagal mengupload file.');
    }

    /**
     * Proses Simpan Import
     */
    public function saveImport()
    {
        if (!$this->isLoggedIn()) {
            return redirect()->to('/login');
        }

        $filename = $this->request->getPost('filename');
        $importType = $this->request->getPost('import_type');
        $selectedRows = $this->request->getPost('selected_rows');
        $filePath = WRITEPATH . 'uploads/temp_import/' . $filename;

        if (!$filename || !file_exists($filePath)) {
            return redirect()->to('/backend/siswa')->with('error', 'File import kadaluarsa atau tidak ditemukan. Silakan upload ulang.');
        }

        if (empty($selectedRows)) {
            // Jika tidak ada yang dipilih, hapus file dan kembali
            unlink($filePath);
            return redirect()->to('/backend/siswa')->with('warning', 'Tidak ada data siswa yang dipilih untuk disimpan.');
        }

        try {
            $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReaderForFile($filePath);
            $spreadsheet = $reader->load($filePath);
            $sheet = $spreadsheet->getActiveSheet()->toArray(null, true, true, true);

            $successCount = 0;
            $failCount = 0;

            foreach ($sheet as $key => $row) {
                // Hanya proses baris yang dipilih (checkbox value = row index)
                if (!in_array($key, $selectedRows)) {
                    continue;
                }

                $data = [];
                // Mapping Ulang (Copy logic dari Import Preview)
                if ($importType === 'template') {
                    $data = [
                        'nisn'          => $row['A'],
                        'nama_siswa'    => $this->cleanInput($row['B']),
                        'jenis_kelamin' => strtoupper($row['C']),
                        'tanggal_lahir' => $row['D'],
                        'tempat_lahir'  => $this->cleanInput($row['E']),
                        'nama_ayah'     => $this->cleanInput($row['F']),
                        'nama_ibu'      => $this->cleanInput($row['G']),
                        'alamat'        => $this->cleanInput($row['H']),
                        'no_hp'         => $row['I'] ?? null,
                        'status'        => 'aktif',
                    ];
                } else {
                    $data = [
                        'nisn'          => $row['E'],
                        'nama_siswa'    => $this->cleanInput($row['B']),
                        'jenis_kelamin' => strtoupper($row['D']),
                        'tanggal_lahir' => $row['G'],
                        'tempat_lahir'  => $this->cleanInput($row['F']),
                        'nama_ayah'     => $this->cleanInput($row['Y']),
                        'nama_ibu'      => $this->cleanInput($row['AE']),
                        'alamat'        => $this->cleanInput($row['J']),
                        'no_hp'         => $row['T'] ?? null,
                        'status'        => 'aktif',
                    ];
                }
                
                // Final Check (Optional, in case DB changed between Preview and Save)
                if ($this->siswaModel->insert($data)) {
                    $successCount++;
                } else {
                    $failCount++;
                }
            }

            // Hapus file temp
            unlink($filePath);

            return redirect()->to('/backend/siswa')->with('success', "Import selesai. Berhasil disimpan: $successCount siswa.");

        } catch (\Exception $e) {
            return redirect()->to('/backend/siswa')->with('error', 'Terjadi kesalahan saat menyimpan: ' . $e->getMessage());
        }
    }
    /**
     * Helper untuk normalisasi teks (Title Case)
     */
    private function cleanInput($text)
    {
        if (empty($text)) {
            return null;
        }
        return ucwords(strtolower(trim($text)));
    }

    /**
     * Helper untuk memproses data siswa sebelum dikirim ke view
     */
    private function processSiswaList(array $siswaList): array
    {
        foreach ($siswaList as &$siswa) {
            $hafalan = json_decode($siswa['hafalan'] ?? '[]', true);
            $processedHafalan = [];

            if (!empty($hafalan) && is_array($hafalan)) {
                foreach ($hafalan as $h) {
                    $namaMulai = $h['nama_surah_mulai'] ?? $h['mulai'] ?? '';
                    $noMulai   = $h['no_surah_mulai'] ?? '';
                    $namaAkhir = $h['nama_surah_akhir'] ?? $h['akhir'] ?? '';
                    $noAkhir   = $h['no_surah_akhir'] ?? '';

                    $displayMulai = $namaMulai . ($noMulai ? "({$noMulai})" : '');
                    $displayAkhir = $namaAkhir . ($noAkhir ? "({$noAkhir})" : '');

                    $processedHafalan[] = [
                        'juz' => $h['juz'],
                        'display_mulai' => $displayMulai,
                        'display_akhir' => $displayAkhir
                    ];
                }
            }
            $siswa['processed_hafalan'] = $processedHafalan;
        }
        return $siswaList;
    }

    /**
     * Update Foto Profil
     */
    public function updateFoto()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Invalid request'
            ]);
        }
        
        $id = $this->request->getPost('id_siswa');
        if (!$id) {
             return $this->response->setJSON([
                'success' => false,
                'message' => 'ID Siswa tidak ditemukan'
            ]);
        }

        // Ambil data siswa lama untuk hapus foto lama (optional)
        $siswa = $this->siswaModel->find($id);
        if (!$siswa) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Data siswa tidak ditemukan'
            ]);
        }

        // Path penyimpanan (sesuai request user)
        // Gunakan FCPATH agar menunjuk ke folder public yang benar
        $uploadPath = FCPATH . 'writable/uploads/profil/siswa/';
        $dbPathPrefix = 'writable/uploads/profil/siswa/'; 
        
        // Buat folder jika belum ada
        if (!is_dir($uploadPath)) {
            mkdir($uploadPath, 0755, true);
        }

        $photoCropped = $this->request->getPost('photo_cropped'); // Base64 string from Cropper

        try {
            if (!empty($photoCropped)) {
                // 1. Handle Base64 Upload
                if (preg_match('/^data:image\/(\w+);base64,/', $photoCropped, $type)) {
                    $data = substr($photoCropped, strpos($photoCropped, ',') + 1);
                    $data = base64_decode($data);

                    if ($data === false) {
                        throw new \Exception('Gagal decode base64 image');
                    }
                    
                    $extension = strtolower($type[1] ?? 'jpg');
                    if ($extension === 'jpeg') $extension = 'jpg';

                    // Nama file baru
                    $newFileName = 'Profil_' . $id . '_' . time() . '.' . $extension;
                    $filePath = $uploadPath . $newFileName;

                    // Simpan file
                    if (file_put_contents($filePath, $data)) {
                        // Hapus foto lama jika ada
                        if (!empty($siswa['foto'])) {
                             $oldFilePath = FCPATH . $siswa['foto'];
                             if (file_exists($oldFilePath) && strpos($siswa['foto'], 'default') === false) {
                                 @unlink($oldFilePath);
                             }
                        }

                        // Simpan relative path ke database
                        $dbPath = $dbPathPrefix . $newFileName;
                        
                        $this->siswaModel->update($id, ['foto' => $dbPath]);

                        return $this->response->setJSON([
                            'success' => true,
                            'message' => 'Foto profil berhasil diperbarui',
                            'foto_url' => base_url($dbPath) . '?t=' . time()
                        ]);
                    } else {
                         log_message('error', 'Gagal menulis file foto profil ke: ' . $filePath);
                         throw new \Exception('Gagal menyimpan file ke disk. Cek permission folder.');
                    }
                } else {
                    throw new \Exception('Format base64 tidak valid');
                }

            } else {
                // 2. Handle Normal File Upload (Fallback)
                $file = $this->request->getFile('foto');
                if ($file && $file->isValid() && !$file->hasMoved()) {
                    $newFileName = 'Profil_' . $id . '_' . time() . '.' . $file->getExtension();
                    
                    $file->move($uploadPath, $newFileName);
                    
                    if (!empty($siswa['foto'])) {
                        $oldFilePath = FCPATH . $siswa['foto'];
                        if (file_exists($oldFilePath) && strpos($siswa['foto'], 'default') === false) {
                             @unlink($oldFilePath);
                        }
                    }

                    $dbPath = $dbPathPrefix . $newFileName;
                    $this->siswaModel->update($id, ['foto' => $dbPath]);

                    return $this->response->setJSON([
                        'success' => true,
                        'message' => 'Foto profil berhasil diperbarui',
                        'foto_url' => base_url($dbPath) . '?t=' . time()
                    ]);
                } else {
                    return $this->response->setJSON([
                        'success' => false,
                        'message' => 'Tidak ada gambar yang dikirim'
                    ]);
                }
            }
        } catch (\Exception $e) {
            log_message('error', 'Update Foto Error: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ]);
        }
    }
}
