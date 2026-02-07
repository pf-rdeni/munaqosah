<?php

namespace App\Models\Munaqosah;

use CodeIgniter\Model;

class AntrianModel extends Model
{
    protected $table = 'tbl_munaqosah_antrian';
    protected $primaryKey = 'id';
    protected $useTimestamps = true;
    protected $allowedFields = [
        'nisn',
        'no_peserta',
        'tahun_ajaran',
        'id_grup_materi', // Renamed
        'id_grup_juri', // Renamed
        'status_antrian',
        'waktu_panggil',
        'waktu_mulai',
        'waktu_selesai'
    ];

    /**
     * Get antrian with details (join peserta, siswa)
     */
    public function getQueueWithDetails($tahunAjaran, $groupMateri = null, $typeUjian = null, $status = null)
    {
        $builder = $this->db->table($this->table . ' a');
        $builder->select('a.*, s.nama_siswa as NamaLengkap, s.jenis_kelamin as JenisKelamin, s.nama_siswa as NamaSiswa, s.foto as Foto, m.nama_grup_materi as NamaGrup');
        // Join ke tabel peserta (registrasi_uji di tpqSmart)
        $builder->join('tbl_munaqosah_peserta p', 'p.no_peserta = a.no_peserta AND p.tahun_ajaran = a.tahun_ajaran', 'left');
        $builder->join('tbl_munaqosah_siswa s', 's.nisn = a.nisn AND s.tahun_ajaran = a.tahun_ajaran', 'left');
        $builder->join('tbl_munaqosah_grup_materi m', 'm.id = a.id_grup_materi', 'left'); // Updated column name
        
        $builder->where('a.tahun_ajaran', $tahunAjaran);

        if ($groupMateri) {
            $builder->where('a.id_grup_materi', $groupMateri); // Updated column name
        }

        if ($status) {
             if (is_numeric($status)) {
                 $statusMap = [0 => 'menunggu', 1 => 'sedang_ujian', 2 => 'selesai'];
                 $statusEnum = $statusMap[$status] ?? 'menunggu';
                 $builder->where('a.status_antrian', $statusEnum);
             } else {
                $builder->where('a.status_antrian', $status);
             }
        }
        
        // Filter TypeUjian...
        if ($typeUjian) { }

        // Custom Ordering: Menunggu -> Dipanggil -> Sedang Ujian -> Selesai
        $builder->orderBy("CASE 
            WHEN a.status_antrian = 'menunggu' THEN 1
            WHEN a.status_antrian = 'dipanggil' THEN 2
            WHEN a.status_antrian = 'sedang_ujian' THEN 3
            WHEN a.status_antrian = 'selesai' THEN 4
            ELSE 5 
        END", 'ASC');

        // FIFO: Created At ASC (First In First Out)
        $builder->orderBy('a.created_at', 'ASC');
        return $builder->get()->getResultArray();
    }

    /**
     * Get status counts for dashboard/monitoring
     */
    public function getStatusCounts($tahunAjaran, $groupMateri = null)
    {
        $builder = $this->db->table($this->table);
        $builder->select('status_antrian, COUNT(*) as count');
        $builder->where('tahun_ajaran', $tahunAjaran);
        
        if ($groupMateri) {
            $builder->where('id_grup_materi', $groupMateri); // Updated column
        }

        $builder->groupBy('status_antrian');
        $results = $builder->get()->getResultArray();

        $counts = [
            'menunggu' => 0,
            'sedang_ujian' => 0, // Maps to 'dipanggil' + 'sedang_ujian'
            'selesai' => 0
        ];

        foreach ($results as $row) {
            $status = $row['status_antrian'];
            $count = (int)$row['count'];
            
            if ($status == 'menunggu') {
                $counts['menunggu'] += $count;
            } elseif ($status == 'sedang_ujian' || $status == 'dipanggil') {
                $counts['sedang_ujian'] += $count;
            } elseif ($status == 'selesai') {
                $counts['selesai'] += $count;
            }
        }

        return [
            0 => $counts['menunggu'],
            1 => $counts['sedang_ujian'],
            2 => $counts['selesai']
        ];
    }

    /**
     * Helper to get queue for specific year
     */
    public function getQueueByTahun($tahunAjaran)
    {
        return $this->where('tahun_ajaran', $tahunAjaran)->findAll();
    }
    /**
     * Get Room Status based on Juri Groups
     */
    public function getRoomStatus($tahunAjaran, $grupMateriId)
    {
        // 1. Get Available Rooms (Distinct Grup Juri for this Materi)
        $db = \Config\Database::connect();
        $builder = $db->table('tbl_munaqosah_juri');
        $builder->select('DISTINCT(id_grup_juri) as room_id');
        $builder->where('id_grup_materi', $grupMateriId);
        $builder->orderBy('id_grup_juri', 'ASC');
        $rooms = $builder->get()->getResultArray();

        $roomStatus = [];

        // 2. Check occupancy for each room
        foreach ($rooms as $room) {
            $roomId = $room['room_id'];
            
            // Find participant currently in this room (status = sedang_ujian or dipanggil)
            $occupant = $this->where($this->table . '.tahun_ajaran', $tahunAjaran)
                             ->where($this->table . '.id_grup_materi', $grupMateriId)
                             ->where($this->table . '.id_grup_juri', $roomId)
                             ->groupStart()
                                 ->where($this->table . '.status_antrian', 'sedang_ujian')
                                 ->orWhere($this->table . '.status_antrian', 'dipanggil')
                             ->groupEnd()
                             ->join('tbl_munaqosah_siswa s', 's.nisn = ' . $this->table . '.nisn', 'left')
                             ->select($this->table . '.*, s.nama_siswa, s.foto')
                             ->first();

            $roomStatus[] = [
                'room_id' => $roomId,
                'room_name' => 'Ruang ' . $roomId, // Or 'Grup Juri ' . $roomId
                'is_active' => !empty($occupant),
                'occupant' => $occupant
            ];
        }

        return $roomStatus;
    }
    /**
     * Check if participant is currently active in any other group
     */
    public function checkParticipantActive($noPeserta, $tahunAjaran, $excludeId = null)
    {
        $builder = $this->where('no_peserta', $noPeserta)
                        ->where('tahun_ajaran', $tahunAjaran)
                        ->groupStart()
                            ->where('status_antrian', 'sedang_ujian')
                            ->orWhere('status_antrian', 'dipanggil')
                        ->groupEnd();
        
        if ($excludeId) {
            $builder->where('id !=', $excludeId);
        }

        return $builder->first();
    }

    /**
     * Get first available room for a group
     * Logic: Get all rooms for this group -> Subtract rooms currently in use -> Return first available
     */
    public function getAvailableRoom($tahunAjaran, $grupMateriId)
    {
        // 1. Get All Rooms
        $db = \Config\Database::connect();
        $builder = $db->table('tbl_munaqosah_juri');
        $builder->select('DISTINCT(id_grup_juri) as room_id');
        $builder->where('id_grup_materi', $grupMateriId);
        $builder->orderBy('id_grup_juri', 'ASC');
        $allRoomsResult = $builder->get()->getResultArray();
        $allRooms = array_column($allRoomsResult, 'room_id');

        if (empty($allRooms)) {
            return null; // Configuration error: No rooms defined
        }

        // 2. Get Occupied Rooms (id_grup_juri matches AND status is active)
        $occupiedResult = $this->where('tahun_ajaran', $tahunAjaran)
                               ->where('id_grup_materi', $grupMateriId)
                               ->where('id_grup_juri IS NOT NULL') // Crucial: Only count rows with assigned room
                               ->groupStart()
                                   ->where('status_antrian', 'sedang_ujian')
                                   ->orWhere('status_antrian', 'dipanggil')
                               ->groupEnd()
                               ->select('id_grup_juri')
                               ->findAll();
        
        $occupiedRooms = array_column($occupiedResult, 'id_grup_juri');

        // 3. Find Difference
        $availableRooms = array_diff($allRooms, $occupiedRooms);
        
        if (!empty($availableRooms)) {
            return array_values($availableRooms)[0]; // Return first available
        }

        return null; // Full
    }
}
