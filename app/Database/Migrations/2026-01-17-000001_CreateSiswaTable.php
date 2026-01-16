<?php

/**
 * ====================================================================
 * MIGRATION: TABEL SISWA
 * ====================================================================
 * Tabel untuk menyimpan data siswa SDIT An-Nahl
 * 
 * @package    Munaqosah
 * @author     SDIT An-Nahl
 */

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateSiswaTable extends Migration
{
    public function up()
    {
        // Definisi struktur tabel siswa
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'nisn' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'null'       => false,
                'comment'    => 'Nomor Induk Siswa Nasional (Primary Key)',
            ],
            'nama_siswa' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => false,
                'comment'    => 'Nama lengkap siswa',
            ],
            'jenis_kelamin' => [
                'type'       => 'ENUM',
                'constraint' => ['L', 'P'],
                'null'       => false,
                'comment'    => 'L = Laki-laki, P = Perempuan',
            ],
            'tanggal_lahir' => [
                'type'    => 'DATE',
                'null'    => true,
                'comment' => 'Tanggal lahir siswa',
            ],
            'tempat_lahir' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
                'comment'    => 'Tempat lahir siswa',
            ],
            'nama_ayah' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
                'comment'    => 'Nama ayah kandung',
            ],
            'nama_ibu' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
                'comment'    => 'Nama ibu kandung',
            ],
            'alamat' => [
                'type'    => 'TEXT',
                'null'    => true,
                'comment' => 'Alamat lengkap siswa',
            ],
            'foto' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
                'comment'    => 'Path file foto siswa',
            ],
            'status' => [
                'type'       => 'ENUM',
                'constraint' => ['aktif', 'nonaktif', 'lulus', 'pindah'],
                'default'    => 'aktif',
                'comment'    => 'Status siswa saat ini',
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        // Set primary key dan unique key
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('nisn');
        
        // Buat tabel dengan engine InnoDB
        $this->forge->createTable('tbl_munaqosah_siswa', true, ['ENGINE' => 'InnoDB']);
    }

    public function down()
    {
        // Hapus tabel jika rollback
        $this->forge->dropTable('tbl_munaqosah_siswa', true);
    }
}
