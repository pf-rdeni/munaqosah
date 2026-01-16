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
}
