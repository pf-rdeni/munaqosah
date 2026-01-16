<?php

/**
 * ====================================================================
 * MIGRATION: TABEL AUTENTIKASI (myAuth)
 * ====================================================================
 * Tabel untuk menyimpan data user, group, dan permission
 * Sesuai dengan standar Myth:Auth
 * 
 * @package    Munaqosah
 * @author     SDIT An-Nahl
 */

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateAuthTables extends Migration
{
    public function up()
    {
        // =============================================================
        // TABEL USERS
        // =============================================================
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'email' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'username' => [
                'type'       => 'VARCHAR',
                'constraint' => 30,
                'null'       => true,
            ],
            'fullname' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
                'comment'    => 'Nama lengkap user',
            ],
            'user_image' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
                'comment'    => 'Path foto profil user',
            ],
            'password_hash' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => false,
            ],
            'reset_hash' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'reset_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'reset_expires' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'activate_hash' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'status' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'status_message' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'active' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'null'       => false,
                'default'    => 0,
            ],
            'force_pass_reset' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'null'       => false,
                'default'    => 0,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'deleted_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('email');
        $this->forge->addUniqueKey('username');
        $this->forge->createTable('users', true, ['ENGINE' => 'InnoDB']);

        // =============================================================
        // TABEL AUTH_LOGINS
        // =============================================================
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'ip_address' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'email' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'user_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
            ],
            'date' => [
                'type' => 'DATETIME',
                'null' => false,
            ],
            'success' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'null'       => false,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey('email');
        $this->forge->addKey('user_id');
        $this->forge->createTable('auth_logins', true, ['ENGINE' => 'InnoDB']);

        // =============================================================
        // TABEL AUTH_TOKENS
        // =============================================================
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'selector' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => false,
            ],
            'hashedValidator' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => false,
            ],
            'user_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => false,
            ],
            'expires' => [
                'type' => 'DATETIME',
                'null' => false,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey('selector');
        $this->forge->addKey('user_id');
        $this->forge->createTable('auth_tokens', true, ['ENGINE' => 'InnoDB']);

        // =============================================================
        // TABEL AUTH_GROUPS
        // =============================================================
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'name' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => false,
            ],
            'description' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => false,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->createTable('auth_groups', true, ['ENGINE' => 'InnoDB']);

        // =============================================================
        // TABEL AUTH_PERMISSIONS
        // =============================================================
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'name' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => false,
            ],
            'description' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => false,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->createTable('auth_permissions', true, ['ENGINE' => 'InnoDB']);

        // =============================================================
        // TABEL AUTH_GROUPS_USERS
        // =============================================================
        $this->forge->addField([
            'group_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'default'    => 0,
            ],
            'user_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'default'    => 0,
            ],
        ]);

        $this->forge->addKey(['group_id', 'user_id'], true);
        $this->forge->createTable('auth_groups_users', true, ['ENGINE' => 'InnoDB']);

        // =============================================================
        // TABEL AUTH_GROUPS_PERMISSIONS
        // =============================================================
        $this->forge->addField([
            'group_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'default'    => 0,
            ],
            'permission_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'default'    => 0,
            ],
        ]);

        $this->forge->addKey(['group_id', 'permission_id'], true);
        $this->forge->createTable('auth_groups_permissions', true, ['ENGINE' => 'InnoDB']);

        // =============================================================
        // TABEL AUTH_USERS_PERMISSIONS
        // =============================================================
        $this->forge->addField([
            'user_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'default'    => 0,
            ],
            'permission_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'default'    => 0,
            ],
        ]);

        $this->forge->addKey(['user_id', 'permission_id'], true);
        $this->forge->createTable('auth_users_permissions', true, ['ENGINE' => 'InnoDB']);
    }

    public function down()
    {
        // Hapus tabel dalam urutan terbalik untuk menghindari FK constraint
        $this->forge->dropTable('auth_users_permissions', true);
        $this->forge->dropTable('auth_groups_permissions', true);
        $this->forge->dropTable('auth_groups_users', true);
        $this->forge->dropTable('auth_permissions', true);
        $this->forge->dropTable('auth_groups', true);
        $this->forge->dropTable('auth_tokens', true);
        $this->forge->dropTable('auth_logins', true);
        $this->forge->dropTable('users', true);
    }
}
