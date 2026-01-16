<?php

/**
 * ====================================================================
 * MIGRATION: TABEL KRITERIA MATERI UJIAN
 * ====================================================================
 * Tabel untuk menyimpan kriteria penilaian materi ujian
 * 
 * @package    Munaqosah
 * @author     SDIT An-Nahl
 */

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateKriteriaMateriUjianTable extends Migration
{
    public function up()
    {
        // Definisi struktur tabel kriteria_materi_ujian
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'id_kriteria' => [
                'type'       => 'CHAR',
                'constraint' => 10,
                'null'       => false,
                'comment'    => 'ID unik kriteria',
            ],
            'nama_kriteria' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => false,
                'comment'    => 'Nama kriteria penilaian (contoh: Tajwid, Makhorijul Huruf)',
            ],
            'id_materi' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => false,
                'comment'    => 'ID materi yang terkait',
            ],
            'deskripsi' => [
                'type'    => 'TEXT',
                'null'    => true,
                'comment' => 'Deskripsi kriteria',
            ],
            'bobot' => [
                'type'       => 'DECIMAL',
                'constraint' => '5,2',
                'default'    => 1.00,
                'comment'    => 'Bobot kriteria dalam penilaian',
            ],
            'urutan' => [
                'type'       => 'INT',
                'constraint' => 3,
                'default'    => 0,
                'comment'    => 'Urutan tampil',
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
        $this->forge->addUniqueKey('id_kriteria');
        $this->forge->addKey('id_materi');
        
        // Buat tabel
        $this->forge->createTable('tbl_munaqosah_kriteria_materi_ujian', true, ['ENGINE' => 'InnoDB']);
    }

    public function down()
    {
        $this->forge->dropTable('tbl_munaqosah_kriteria_materi_ujian', true);
    }
}
