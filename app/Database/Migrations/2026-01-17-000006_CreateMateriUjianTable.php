<?php

/**
 * ====================================================================
 * MIGRATION: TABEL MATERI UJIAN
 * ====================================================================
 * Tabel untuk menyimpan data materi ujian munaqosah
 * 
 * @package    Munaqosah
 * @author     SDIT An-Nahl
 */

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateMateriUjianTable extends Migration
{
    public function up()
    {
        // Definisi struktur tabel materi_ujian
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'id_materi' => [
                'type'       => 'CHAR',
                'constraint' => 10,
                'null'       => false,
                'comment'    => 'ID unik materi',
            ],
            'nama_materi' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => false,
                'comment'    => 'Nama materi ujian',
            ],
            'id_kategori_materi' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
                'comment'    => 'ID kategori materi',
            ],
            'id_grup_materi' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
                'comment'    => 'ID grup materi',
            ],
            'deskripsi' => [
                'type'    => 'TEXT',
                'null'    => true,
                'comment' => 'Deskripsi materi',
            ],
            'nilai_maksimal' => [
                'type'       => 'INT',
                'constraint' => 5,
                'default'    => 100,
                'comment'    => 'Nilai maksimal materi',
            ],
            'status' => [
                'type'       => 'ENUM',
                'constraint' => ['aktif', 'nonaktif'],
                'default'    => 'aktif',
                'comment'    => 'Status materi',
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
        $this->forge->addUniqueKey('id_materi');
        $this->forge->addKey('id_kategori_materi');
        $this->forge->addKey('id_grup_materi');
        
        // Buat tabel
        $this->forge->createTable('tbl_munaqosah_materi_ujian', true, ['ENGINE' => 'InnoDB']);
    }

    public function down()
    {
        $this->forge->dropTable('tbl_munaqosah_materi_ujian', true);
    }
}
