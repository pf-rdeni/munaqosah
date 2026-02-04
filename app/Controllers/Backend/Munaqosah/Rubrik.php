<?php

namespace App\Controllers\Backend\Munaqosah;

use App\Controllers\BaseController;
use App\Models\Munaqosah\MateriModel;
use App\Models\Munaqosah\GrupMateriModel;
use App\Models\Munaqosah\KriteriaModel;
use App\Models\Munaqosah\PredikatModel;
use App\Models\Munaqosah\RubrikModel;

class Rubrik extends BaseController
{
    protected $materiModel;
    protected $grupMateriModel;
    protected $kriteriaModel;
    protected $predikatModel;
    protected $rubrikModel;

    public function __construct()
    {
        $this->materiModel     = new MateriModel();
        $this->grupMateriModel = new GrupMateriModel();
        $this->kriteriaModel   = new KriteriaModel();
        $this->predikatModel   = new PredikatModel();
        $this->rubrikModel     = new RubrikModel();
    }

    public function index()
    {
        if (!$this->isLoggedIn()) return redirect()->to('/login');

        // Fetch grouped materi for selection
        $grupMateri = $this->grupMateriModel->orderBy('urutan', 'ASC')->findAll();
        $listData = [];

        foreach ($grupMateri as $grup) {
            $materi = $this->materiModel->where('id_grup_materi', $grup['id'])->findAll();
            $grup['materi_list'] = $materi;
            $listData[] = $grup;
        }

        $data = [
            'title'      => 'Manajemen Rubrik',
            'pageTitle'  => 'Manajemen Rubrik Penilaian',
            'breadcrumb' => [
                ['title' => 'Home', 'url' => '/backend/dashboard'],
                ['title' => 'Manajemen Rubrik', 'url' => ''],
            ],
            'user'       => $this->getCurrentUser(),
            'listData'   => $listData
        ];

        return view('backend/rubrik/index', $data);
    }

    public function manage($idMateri)
    {
        if (!$this->isLoggedIn()) return redirect()->to('/login');

        $materi = $this->materiModel->find($idMateri);
        if (!$materi) {
            return redirect()->to('/backend/rubrik')->with('error', 'Materi tidak ditemukan');
        }

        $kriteria = $this->kriteriaModel->getByMateri($idMateri);
        $predikats = $this->predikatModel->getAll(); // Sangat Baik, Baik, etc.
        
        // Fetch existing rubric data
        $kriteriaIds = array_column($kriteria, 'id');
        $existingRubrik = $this->rubrikModel->getRubrikByKriteria($kriteriaIds);

        // Map existing to [kriteria_id][predikat_id] => deskripsi
        $rubrikMap = [];
        foreach ($existingRubrik as $r) {
            $rubrikMap[$r['id_kriteria']][$r['id_predikat']] = $r['deskripsi'];
        }

        $data = [
            'title'      => 'Edit Rubrik - ' . $materi['nama_materi'],
            'pageTitle'  => 'Edit Rubrik Penilaian: ' . $materi['nama_materi'],
            'breadcrumb' => [
                ['title' => 'Home', 'url' => '/backend/dashboard'],
                ['title' => 'Manajemen Rubrik', 'url' => '/backend/rubrik'],
                ['title' => 'Edit', 'url' => ''],
            ],
            'user'       => $this->getCurrentUser(),
            'materi'     => $materi,
            'kriteria'   => $kriteria,
            'predikats'  => $predikats,
            'rubrikMap'  => $rubrikMap
        ];

        return view('backend/rubrik/form', $data);
    }

    public function save()
    {
        if (!$this->isLoggedIn()) return redirect()->to('/login');

        $pk = $this->request->getPost('rubrik'); // Array [id_kriteria][id_predikat] => description
        $idMateri = $this->request->getPost('id_materi');

        if (!empty($pk)) {
            foreach ($pk as $idKriteria => $predikatData) {
                foreach ($predikatData as $idPredikat => $deskripsi) {
                    // Check if exists
                    $existing = $this->rubrikModel->where('id_kriteria', $idKriteria)
                                                  ->where('id_predikat', $idPredikat)
                                                  ->first();

                    if (!empty($deskripsi)) {
                        if ($existing) {
                            $this->rubrikModel->update($existing['id'], ['deskripsi' => $deskripsi]);
                        } else {
                            $this->rubrikModel->insert([
                                'id_kriteria' => $idKriteria,
                                'id_predikat' => $idPredikat,
                                'deskripsi'   => $deskripsi
                            ]);
                        }
                    } else {
                        // If empty description, maybe delete? Or keep empty.
                        // For now keep empty update to clear it if user deleted text.
                        if ($existing) {
                            $this->rubrikModel->update($existing['id'], ['deskripsi' => null]);
                        }
                    }
                }
            }
        }

        return redirect()->to('/backend/rubrik/manage/' . $idMateri)->with('success', 'Data rubrik berhasil disimpan.');
    }
}
