<?php

/**
 * ====================================================================
 * MODEL: USER MODEL
 * ====================================================================
 * Model untuk mengelola data user (autentikasi)
 * 
 * @package    Munaqosah
 * @author     SDIT An-Nahl
 */

namespace App\Models;

use CodeIgniter\Model;

class UserModel extends Model
{
    protected $table = 'users';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $useSoftDeletes = true;
    
    protected $allowedFields = [
        'email',
        'username',
        'fullname',
        'user_image',
        'password_hash',
        'reset_hash',
        'reset_at',
        'reset_expires',
        'activate_hash',
        'status',
        'status_message',
        'active',
        'force_pass_reset',
    ];

    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    protected $deletedField = 'deleted_at';

    /**
     * Ambil user berdasarkan username
     * 
     * @param string $username
     * @return array|null
     */
    public function getUserByUsername(string $username): ?array
    {
        return $this->where('username', $username)->first();
    }

    /**
     * Ambil user dengan grup
     * 
     * @param int $userId
     * @return array
     */
    public function getUserWithGroups(int $userId): array
    {
        $user = $this->find($userId);
        if (!$user) {
            return [];
        }

        $groups = $this->db->table('auth_groups_users gu')
                          ->select('g.id, g.name, g.description')
                          ->join('auth_groups g', 'g.id = gu.group_id')
                          ->where('gu.user_id', $userId)
                          ->get()
                          ->getResultArray();

        $user['groups'] = $groups;
        return $user;
    }

    /**
     * Ambil data user berdasarkan ID
     * 
     * @param int $userId
     * @return array|null
     */
    public function getUser(int $userId): ?array
    {
        return $this->find($userId);
    }

    /**
     * Validasi password user
     * 
     * @param string $password
     * @param string $hash
     * @return bool
     */
    public function validatePassword(string $password, string $hash): bool
    {
        return password_verify($password, $hash);
    }

    /**
     * Update password user
     * 
     * @param int $userId
     * @param string $newPassword
     * @return bool
     */
    public function updatePassword(int $userId, string $newPassword): bool
    {
        return $this->update($userId, [
            'password_hash' => password_hash($newPassword, PASSWORD_DEFAULT),
        ]);
    }
    /**
     * Ambil semua grup (role)
     * 
     * @return array
     */
    public function getGroups(): array
    {
        return $this->db->table('auth_groups')->get()->getResultArray();
    }

    /**
     * Buat user baru dengan grup
     * 
     * @param array $userData
     * @param int $groupId
     * @return bool|int ID user jika berhasil, false jika gagal
     */
    public function createUserWithGroup(array $userData, int $groupId)
    {
        $this->db->transStart();

        // Hash password
        if (isset($userData['password'])) {
            $userData['password_hash'] = password_hash($userData['password'], PASSWORD_DEFAULT);
            unset($userData['password']);
        }

        $userData['active'] = 1; // Default active
        $this->insert($userData);
        $userId = $this->insertID();

        // Assign Group
        $this->db->table('auth_groups_users')->insert([
            'group_id' => $groupId,
            'user_id'  => $userId
        ]);

        $this->db->transComplete();

        if ($this->db->transStatus() === false) {
            return false;
        }

        return $userId;
    }

    /**
     * Update user dan grup
     * 
     * @param int $userId
     * @param array $userData
     * @param int|null $groupId
     * @return bool
     */
    public function updateUserWithGroup(int $userId, array $userData, ?int $groupId = null): bool
    {
        $this->db->transStart();

        // Handle Password update if provided
        if (!empty($userData['password'])) {
            $userData['password_hash'] = password_hash($userData['password'], PASSWORD_DEFAULT);
        }
        unset($userData['password']); // Remove plain password

        // Update User Data
        $this->update($userId, $userData);

        // Update Group if provided
        if ($groupId) {
            // Hapus grup lama (Assuming single role per user for simple management)
            $this->db->table('auth_groups_users')->where('user_id', $userId)->delete();
            
            // Insert grup baru
            $this->db->table('auth_groups_users')->insert([
                'group_id' => $groupId,
                'user_id'  => $userId
            ]);
        }

        $this->db->transComplete();

        return $this->db->transStatus();
    }
}
