<?php

/**
 * ====================================================================
 * MIGRATION: TABEL PESERTA
 * ====================================================================
 * Tabel untuk menyimpan data peserta ujian munaqosah
 * 
 * @package    Munaqosah
 * @author     SDIT An-Nahl
 */

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreatePesertaTable extends Migration
{
    public function up()
    {
        // Definisi struktur tabel peserta
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
                'comment'    => 'NISN siswa (FK ke tabel siswa)',
            ],
            'no_peserta' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'null'       => false,
                'comment'    => 'Nomor peserta ujian',
            ],
            'tahun_ajaran' => [
                'type'       => 'VARCHAR',
                'constraint' => 10,
                'null'       => false,
                'comment'    => 'Tahun ajaran (contoh: 2025/2026)',
            ],
            'status' => [
                'type'       => 'ENUM',
                'constraint' => ['terdaftar', 'ujian', 'selesai'],
                'default'    => 'terdaftar',
                'comment'    => 'Status peserta ujian',
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

        // Set primary key
        $this->forge->addKey('id', true);
        
        // Index untuk pencarian
        $this->forge->addKey('nisn');
        $this->forge->addKey('no_peserta');
        $this->forge->addKey('tahun_ajaran');
        
        // Buat tabel
        $this->forge->createTable('tbl_munaqosah_peserta', true, ['ENGINE' => 'InnoDB']);
    }

    public function down()
    {
        $this->forge->dropTable('tbl_munaqosah_peserta', true);
    }
}
