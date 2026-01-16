<?php

/**
 * ====================================================================
 * MIGRATION: TABEL ANTRIAN
 * ====================================================================
 * Tabel untuk menyimpan data antrian ujian munaqosah
 * 
 * @package    Munaqosah
 * @author     SDIT An-Nahl
 */

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateAntrianTable extends Migration
{
    public function up()
    {
        // Definisi struktur tabel antrian
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
                'comment'    => 'NISN siswa',
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
                'comment'    => 'Tahun ajaran',
            ],
            'id_kategori_materi' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
                'comment'    => 'ID kategori materi yang diujikan',
            ],
            'id_group_materi' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
                'comment'    => 'ID group materi',
            ],
            'id_group_penguji' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
                'comment'    => 'ID group penguji/juri',
            ],
            'nomor_antrian' => [
                'type'       => 'INT',
                'constraint' => 5,
                'null'       => true,
                'comment'    => 'Nomor urut antrian',
            ],
            'status_antrian' => [
                'type'       => 'ENUM',
                'constraint' => ['menunggu', 'dipanggil', 'sedang_ujian', 'selesai'],
                'default'    => 'menunggu',
                'comment'    => 'Status antrian saat ini',
            ],
            'waktu_panggil' => [
                'type'    => 'DATETIME',
                'null'    => true,
                'comment' => 'Waktu peserta dipanggil',
            ],
            'waktu_mulai' => [
                'type'    => 'DATETIME',
                'null'    => true,
                'comment' => 'Waktu mulai ujian',
            ],
            'waktu_selesai' => [
                'type'    => 'DATETIME',
                'null'    => true,
                'comment' => 'Waktu selesai ujian',
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

        // Set primary key dan index
        $this->forge->addKey('id', true);
        $this->forge->addKey('nisn');
        $this->forge->addKey('no_peserta');
        $this->forge->addKey('status_antrian');
        
        // Buat tabel
        $this->forge->createTable('tbl_munaqosah_antrian', true, ['ENGINE' => 'InnoDB']);
    }

    public function down()
    {
        $this->forge->dropTable('tbl_munaqosah_antrian', true);
    }
}
