<?php

/**
 * ====================================================================
 * SEEDER: AUTH SEEDER
 * ====================================================================
 * Seeder untuk membuat data awal user, group, dan permission
 * 
 * @package    Munaqosah
 * @author     SDIT An-Nahl
 */

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class AuthSeeder extends Seeder
{
    public function run()
    {
        // =============================================================
        // INSERT GROUPS
        // =============================================================
        $groups = [
            [
                'id'          => 1,
                'name'        => 'admin',
                'description' => 'Administrator sistem dengan akses penuh',
            ],
            [
                'id'          => 2,
                'name'        => 'juri',
                'description' => 'Penguji/Juri ujian munaqosah',
            ],
            [
                'id'          => 3,
                'name'        => 'panitia',
                'description' => 'Panitia pelaksana ujian munaqosah',
            ],
            [
                'id'          => 4,
                'name'        => 'kepala',
                'description' => 'Kepala Sekolah SDIT An-Nahl',
            ],
            [
                'id'          => 5,
                'name'        => 'siswa',
                'description' => 'Siswa peserta ujian munaqosah',
            ],
        ];

        // Hapus data lama dan insert baru
        $this->db->table('auth_groups')->truncate();
        $this->db->table('auth_groups')->insertBatch($groups);

        // =============================================================
        // INSERT USER ADMIN
        // =============================================================
        $users = [
            [
                'id'            => 1,
                'email'         => 'admin@sditan-nahl.sch.id',
                'username'      => 'admin',
                'fullname'      => 'Administrator',
                // Password: admin (hash menggunakan PASSWORD_DEFAULT)
                'password_hash' => password_hash('admin', PASSWORD_DEFAULT),
                'active'        => 1,
                'created_at'    => date('Y-m-d H:i:s'),
                'updated_at'    => date('Y-m-d H:i:s'),
            ],
        ];

        // Hapus data lama dan insert baru
        $this->db->table('users')->truncate();
        $this->db->table('users')->insertBatch($users);

        // =============================================================
        // ASSIGN USER KE GROUP
        // =============================================================
        $groupsUsers = [
            [
                'group_id' => 1, // admin
                'user_id'  => 1,
            ],
        ];

        // Hapus data lama dan insert baru
        $this->db->table('auth_groups_users')->truncate();
        $this->db->table('auth_groups_users')->insertBatch($groupsUsers);

        // =============================================================
        // INSERT PERMISSIONS
        // =============================================================
        $permissions = [
            ['id' => 1, 'name' => 'manage-users', 'description' => 'Kelola data user'],
            ['id' => 2, 'name' => 'manage-siswa', 'description' => 'Kelola data siswa'],
            ['id' => 3, 'name' => 'manage-peserta', 'description' => 'Kelola data peserta ujian'],
            ['id' => 4, 'name' => 'manage-nilai', 'description' => 'Kelola nilai ujian'],
            ['id' => 5, 'name' => 'manage-materi', 'description' => 'Kelola materi ujian'],
            ['id' => 6, 'name' => 'manage-juri', 'description' => 'Kelola data juri'],
            ['id' => 7, 'name' => 'input-nilai', 'description' => 'Input nilai sebagai juri'],
            ['id' => 8, 'name' => 'view-laporan', 'description' => 'Lihat laporan ujian'],
        ];

        // Hapus data lama dan insert baru
        $this->db->table('auth_permissions')->truncate();
        $this->db->table('auth_permissions')->insertBatch($permissions);

        // =============================================================
        // ASSIGN PERMISSIONS KE GROUPS
        // =============================================================
        $groupsPermissions = [
            // Admin - semua permission
            ['group_id' => 1, 'permission_id' => 1],
            ['group_id' => 1, 'permission_id' => 2],
            ['group_id' => 1, 'permission_id' => 3],
            ['group_id' => 1, 'permission_id' => 4],
            ['group_id' => 1, 'permission_id' => 5],
            ['group_id' => 1, 'permission_id' => 6],
            ['group_id' => 1, 'permission_id' => 7],
            ['group_id' => 1, 'permission_id' => 8],
            // Juri - input nilai
            ['group_id' => 2, 'permission_id' => 7],
            ['group_id' => 2, 'permission_id' => 8],
            // Panitia - kelola peserta dan antrian
            ['group_id' => 3, 'permission_id' => 3],
            ['group_id' => 3, 'permission_id' => 8],
            // Kepala - view laporan
            ['group_id' => 4, 'permission_id' => 8],
        ];

        // Hapus data lama dan insert baru
        $this->db->table('auth_groups_permissions')->truncate();
        $this->db->table('auth_groups_permissions')->insertBatch($groupsPermissions);

        echo "AuthSeeder: Data user, group, dan permission berhasil dibuat.\n";
        echo "User default: username=admin, password=admin\n";
    }
}
