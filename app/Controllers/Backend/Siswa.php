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

namespace App\Controllers\Backend;

use App\Controllers\BaseController;
use App\Models\SiswaModel;

class Siswa extends BaseController
{
    protected $siswaModel;

    public function __construct()
    {
        $this->siswaModel = new SiswaModel();
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
            'siswaList'  => $this->siswaModel->orderBy('nama_siswa', 'ASC')->findAll(),
        ];

        return view('backend/siswa/index', $data);
    }

    /**
     * Halaman form tambah siswa
     *
     * @return string|\CodeIgniter\HTTP\RedirectResponse
     */
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
            'nama_siswa'    => $this->request->getPost('nama_siswa'),
            'jenis_kelamin' => $this->request->getPost('jenis_kelamin'),
            'tanggal_lahir' => $this->request->getPost('tanggal_lahir'),
            'tempat_lahir'  => $this->request->getPost('tempat_lahir'),
            'nama_ayah'     => $this->request->getPost('nama_ayah'),
            'nama_ibu'      => $this->request->getPost('nama_ibu'),
            'alamat'        => $this->request->getPost('alamat'),
            'status'        => 'aktif',
        ];

        if ($this->siswaModel->insert($data)) {
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
            'nisn'          => "required|max_length[20]|is_unique[tbl_munaqosah_siswa.nisn,id,{$id}]",
            'nama_siswa'    => 'required|max_length[100]',
            'jenis_kelamin' => 'required|in_list[L,P]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()
                ->withInput()
                ->with('errors', $this->validator->getErrors());
        }

        // Update data
        $data = [
            'nisn'          => $this->request->getPost('nisn'),
            'nama_siswa'    => $this->request->getPost('nama_siswa'),
            'jenis_kelamin' => $this->request->getPost('jenis_kelamin'),
            'tanggal_lahir' => $this->request->getPost('tanggal_lahir'),
            'tempat_lahir'  => $this->request->getPost('tempat_lahir'),
            'nama_ayah'     => $this->request->getPost('nama_ayah'),
            'nama_ibu'      => $this->request->getPost('nama_ibu'),
            'alamat'        => $this->request->getPost('alamat'),
            'status'        => $this->request->getPost('status') ?? 'aktif',
        ];

        if ($this->siswaModel->update($id, $data)) {
            return redirect()->to('/backend/siswa')
                ->with('success', 'Data siswa berhasil diperbarui');
        }

        return redirect()->back()
            ->withInput()
            ->with('error', 'Gagal memperbarui data siswa');
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
}
