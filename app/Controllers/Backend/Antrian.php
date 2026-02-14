<?php

namespace App\Controllers\Backend;

use App\Controllers\BaseController;
use App\Models\Munaqosah\AntrianModel;
use App\Models\Munaqosah\PesertaModel;
use App\Models\Munaqosah\GrupMateriModel;

class Antrian extends BaseController
{
    protected $antrianModel;
    protected $pesertaModel;
    protected $helpFunction;
    protected $munaqosahGrupModel;

    public function __construct()
    {
        $this->antrianModel = new AntrianModel();
        $this->pesertaModel = new PesertaModel();
        $this->munaqosahGrupModel = new GrupMateriModel();
    }

    public function index()
    {
        $currentTahunAjaran = $this->getTahunAjaran();
        // Filter params
        $filterGrup = $this->request->getGet('grup');
        
        // Global Stats
        $stats = $this->antrianModel->getStatusCounts($currentTahunAjaran, $filterGrup);
        
        // Calculate Total & Progress
        $total = $stats[0] + $stats[1] + $stats[2]; // Menunggu + Active + Selesai
        $progress = $total > 0 ? round(($stats[2] / $total) * 100) : 0;

        // Get Room Status (Active Rooms)
        // If filter is set, get rooms for that group. If not, get all rooms? 
        // getRoomStatus logic currently expects a specific GrupMateriId.
        // If no filter, we might need to loop through all groups or update model to handle null.
        // Let's iterate all groups to get comprehensive room status if no filter.
        $roomStatus = [];
        // Map group names for quick lookup
        $groupNames = [];
        $allGroups = $this->munaqosahGrupModel->findAll();
        foreach ($allGroups as $g) {
            $groupNames[$g['id']] = $g['nama_grup_materi'];
        }

        if ($filterGrup) {
            $rs = $this->antrianModel->getRoomStatus($currentTahunAjaran, $filterGrup);
            // Inject Name
             foreach ($rs as &$r) { // Key fix: use reference
                 $r['nama_grup_materi'] = $groupNames[$filterGrup] ?? 'Grup ' . $filterGrup;
             }
            $roomStatus = $rs;
        } else {
             // Fetch all groups and merge
             foreach ($allGroups as $g) {
                 $rs = $this->antrianModel->getRoomStatus($currentTahunAjaran, $g['id']);
                 // Inject Name
                 foreach ($rs as &$r) {
                     $r['nama_grup_materi'] = $g['nama_grup_materi'];
                 }
                 $roomStatus = array_merge($roomStatus, $rs);
             }
        }
        
        $data = [
            'page_title' => 'Input Registrasi Antrian',
            'current_tahun_ajaran' => $currentTahunAjaran,
            'grup_materi' => $this->munaqosahGrupModel->findAll(),
            'filter_grup' => $filterGrup,
            'antrian' => $this->antrianModel->getQueueWithDetails($currentTahunAjaran, $filterGrup),
            'stats' => $stats,
            'total_peserta' => $total,
            'progress' => $progress,
            'room_status' => $roomStatus,
            'user' => $this->getCurrentUser() // Fix: Pass user data for sidebar
        ];

        return view('backend/antrian/index', $data);
    }

    public function monitoring()
    {
        $currentTahunAjaran = $this->getTahunAjaran();
        
        $data = [
            'page_title' => 'Monitoring Status Antrian',
            'current_tahun_ajaran' => $currentTahunAjaran,
            'grup_materi' => $this->munaqosahGrupModel->findAll(),
            'stats' => $this->antrianModel->getStatusCounts($currentTahunAjaran)
        ];

        return view('backend/antrian/monitoring', $data);
    }

    // API to get queue data for monitoring auto-refresh
    public function getQueueData()
    {
        if (!$this->request->isAJAX()) {
             return $this->response->setStatusCode(403);
        }

        $currentTahunAjaran = $this->getTahunAjaran();
        
        try {
            // Always return all groups to support global overview in monitoring
            $grupMateri = $this->munaqosahGrupModel->findAll();
            $queues = [];
            $stats = [];
            $rooms = []; // Stores room status per group

            foreach ($grupMateri as $grup) {
                $queues[$grup['id']] = $this->antrianModel->getQueueWithDetails($currentTahunAjaran, $grup['id']);
                $stats[$grup['id']] = $this->antrianModel->getStatusCounts($currentTahunAjaran, $grup['id']);
                // Get Room Status (Active Juri Groups for this Materi)
                $rooms[$grup['id']] = $this->antrianModel->getRoomStatus($currentTahunAjaran, $grup['id']);
            }
        
            // Total global stats
            $globalStats = $this->antrianModel->getStatusCounts($currentTahunAjaran);

            return $this->response->setJSON([
                'success' => true,
                'data' => [
                    'queues' => $queues,
                    'stats' => $stats,
                    'rooms' => $rooms, // New Room Data injected here
                    'globalStats' => $globalStats,
                    'groupMetadata' => $grupMateri // Send metadata to frontend
                ]
            ]);
        } catch (\Throwable $e) {
            // Return 200 but with success=false to visualize error in console
            return $this->response->setJSON([
                'success' => false,
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    public function register()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setJSON(['success' => false, 'message' => 'Invalid Request']);
        }

        $noPeserta = $this->request->getPost('no_peserta');
        $idGrup = $this->request->getPost('id_grup');
        $tahunAjaran = $this->getTahunAjaran();

        // 1. Validate Peserta
        $peserta = $this->pesertaModel->where('no_peserta', $noPeserta)
                                      ->where('tahun_ajaran', $tahunAjaran)
                                      ->first();

        if (!$peserta) {
            return $this->response->setJSON(['success' => false, 'message' => 'Peserta tidak ditemukan']);
        }

        // 2. Validate: One time per Group Check
        // Peserta cannot register again for the SAME group, regardless of status
        $duplicateGroup = $this->antrianModel->where('no_peserta', $noPeserta)
                                             ->where('tahun_ajaran', $tahunAjaran)
                                             ->where('id_grup_materi', $idGrup)
                                             ->first();
        if ($duplicateGroup) {
             $status = $duplicateGroup['status_antrian'];
             return $this->response->setJSON([
                 'success' => false, 
                 'message' => "<b>Gagal!</b> Peserta sudah terdaftar di grup materi ini (Status: <b>{$status}</b>). Tidak bisa daftar dua kali."
             ]);
        }

        // 2. Validate Status: Can only register if 'selesai' or no record
        // Check if participant has ANY ongoing queue activity
        $activeQueue = $this->antrianModel->select('tbl_munaqosah_antrian.*, m.nama_grup_materi')
                                          ->join('tbl_munaqosah_grup_materi m', 'm.id = tbl_munaqosah_antrian.id_grup_materi', 'left')
                                          ->where('no_peserta', $noPeserta)
                                          ->where('tahun_ajaran', $tahunAjaran)
                                          ->where('status_antrian !=', 'selesai') // If NOT selesai, they are busy
                                          ->first();
                                          
        if ($activeQueue) {
             // Block registration
             $status = $activeQueue['status_antrian']; // menunggu, dipanggil, sedang_ujian
             $grupName = $activeQueue['nama_grup_materi'];
             
             // Friendly status label
             $labels = [
                 'menunggu' => 'Menunggu',
                 'dipanggil' => 'Dipanggil',
                 'sedang_ujian' => 'Sedang Ujian'
             ];
             $label = $labels[$status] ?? $status;
             
             return $this->response->setJSON([
                 'success' => false, 
                 'message' => "<b>Gagal!</b> Peserta ini statusnya sedang <b>{$label}</b> di grup <b>{$grupName}</b>. Harap selesaikan dulu."
             ]);
        }

        // 3. New Queue Insert
        $data = [
            'nisn' => $peserta['nisn'],
            'no_peserta' => $noPeserta,
            'tahun_ajaran' => $tahunAjaran,
            'id_grup_materi' => $idGrup,
            'status_antrian' => 'menunggu',
            'created_at' => date('Y-m-d H:i:s')
        ];

        if ($this->antrianModel->insert($data)) {
            return $this->response->setJSON(['success' => true, 'message' => 'Berhasil ditambahkan ke antrian']);
        } else {
            return $this->response->setJSON(['success' => false, 'message' => 'Gagal menyimpan antrian']);
        }
    }

    public function updateStatus()
    {
         if (!$this->request->isAJAX()) {
            return $this->response->setJSON(['success' => false, 'message' => 'Invalid Request']);
        }

        $id = $this->request->getPost('id');
        $status = $this->request->getPost('status'); // 'menunggu', 'sedang_ujian', 'selesai'
        $idGrupMateri = $this->request->getPost('id_grup_materi'); // Need to pass this from frontend now
        $tahunAjaran = $this->getTahunAjaran();

        // Fetch current queue item first
        $currentItem = $this->antrianModel->find($id);
        if (!$currentItem) {
            return $this->response->setJSON(['success' => false, 'message' => 'Data antrian tidak ditemukan']);
        }
        
        $data = [
            'status_antrian' => $status
        ];
        
        // LOGIC: Start Exam (or Call)
        if ($status == 'sedang_ujian' || $status == 'dipanggil') {
            
            // 1. Validation: Check if participant is busy elsewhere
            $activeElsewhere = $this->antrianModel->checkParticipantActive($currentItem['no_peserta'], $tahunAjaran, $id);
            if ($activeElsewhere) {
                return $this->response->setJSON([
                    'success' => false, 
                    'message' => 'Peserta sedang ujian di grup lain!'
                ]);
            }

            // 2. Auto-Assign Room
            // Check if already assigned a room (re-entry)
            $assignedRoom = $currentItem['id_grup_juri'];
            
            if (!$assignedRoom) {
                $availableRoom = $this->antrianModel->getAvailableRoom($tahunAjaran, $currentItem['id_grup_materi']);
                
                if ($availableRoom === null) {
                    return $this->response->setJSON([
                        'success' => false, 
                        'message' => 'Semua ruangan/juri sedang penuh untuk materi ini!'
                    ]);
                }
                $data['id_grup_juri'] = $availableRoom;
            }

            if ($status == 'sedang_ujian') {
                $data['waktu_mulai'] = date('Y-m-d H:i:s');
            } else {
                $data['waktu_panggil'] = date('Y-m-d H:i:s');
            }

        } elseif ($status == 'selesai') {
            $data['waktu_selesai'] = date('Y-m-d H:i:s');
            $data['id_grup_juri'] = null; // Free up the room
        } elseif ($status == 'menunggu') {
             // Reset
             $data['waktu_mulai'] = null;
             $data['waktu_selesai'] = null;
             $data['waktu_panggil'] = null;
             $data['id_grup_juri'] = null; // Free up room
        }

        $this->antrianModel->update($id, $data);
        return $this->response->setJSON(['success' => true]);
    }

    // API to get available rooms for a group
    public function getRooms() 
    {
         if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(403);
        }
        $grupMateriId = $this->request->getGet('grup_id');
        $currentTahunAjaran = $this->getTahunAjaran();
        
        $rooms = $this->antrianModel->getRoomStatus($currentTahunAjaran, $grupMateriId);
        return $this->response->setJSON(['success' => true, 'data' => $rooms]);
    }
    
    public function delete()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setJSON(['success' => false, 'message' => 'Invalid Request']);
        }
        $id = $this->request->getPost('id');
        $this->antrianModel->delete($id);
         return $this->response->setJSON(['success' => true]);
    }
}
