<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateRubrikTables extends Migration
{
    public function up()
    {
        // 1. Tabel Predikat (Global Score Ranges)
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'nama_predikat' => [
                'type'       => 'VARCHAR',
                'constraint' => '50', // Sangat Baik, Baik, dll
            ],
            'min_nilai' => [
                'type'       => 'INT',
                'constraint' => 3,
            ],
            'max_nilai' => [
                'type'       => 'INT',
                'constraint' => 3,
            ],
            'deskripsi_global' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'class_css' => [
                'type'       => 'VARCHAR',
                'constraint' => '50', // success, info, warning, danger
                'default'    => 'secondary',
            ],
            'urutan' => [
                'type'       => 'INT',
                'constraint' => 11,
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
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('tbl_munaqosah_predikat');

        // 2. Tabel Rubrik (Detail Description per Criteria & Predikat)
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'id_kriteria' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'id_predikat' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'deskripsi' => [
                'type' => 'TEXT',
                'null' => true,
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
        $this->forge->addKey('id', true);
        // Foreign Keys (optional, but good practice if engine supports it)
        // $this->forge->addForeignKey('id_kriteria', 'tbl_munaqosah_kriteria_materi_ujian', 'id', 'CASCADE', 'CASCADE');
        // $this->forge->addForeignKey('id_predikat', 'tbl_munaqosah_predikat', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('tbl_munaqosah_rubrik');

        // Seed Default Predikat Data
        $data = [
            [
                'nama_predikat' => 'Sangat Baik',
                'min_nilai'     => 86,
                'max_nilai'     => 100,
                'deskripsi_global' => 'Gerakan dan bacaan sangat tepat, tertib dan berurutan, sikap khusyuk.',
                'class_css'     => 'success',
                'urutan'        => 1
            ],
            [
                'nama_predikat' => 'Baik',
                'min_nilai'     => 76,
                'max_nilai'     => 85,
                'deskripsi_global' => 'Gerakan dan bacaan cukup tepat, terdapat kesalahan kecil.',
                'class_css'     => 'info',
                'urutan'        => 2
            ],
            [
                'nama_predikat' => 'Cukup',
                'min_nilai'     => 66,
                'max_nilai'     => 75,
                'deskripsi_global' => 'Masih ada beberapa kesalahan bacaan atau gerakan.',
                'class_css'     => 'warning',
                'urutan'        => 3
            ],
             [
                'nama_predikat' => 'Perlu Bimbingan',
                'min_nilai'     => 0,
                'max_nilai'     => 65,
                'deskripsi_global' => 'Banyak kesalahan bacaan dan gerakan.',
                'class_css'     => 'danger',
                'urutan'        => 4
            ],
        ];
        $this->db->table('tbl_munaqosah_predikat')->insertBatch($data);
    }

    public function down()
    {
        $this->forge->dropTable('tbl_munaqosah_rubrik');
        $this->forge->dropTable('tbl_munaqosah_predikat');
    }
}
