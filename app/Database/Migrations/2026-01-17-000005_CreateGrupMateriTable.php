<?php

/**
 * ====================================================================
 * MIGRATION: TABEL GRUP MATERI
 * ====================================================================
 * Tabel untuk menyimpan data grup materi ujian
 * 
 * @package    Munaqosah
 * @author     SDIT An-Nahl
 */

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateGrupMateriTable extends Migration
{
    public function up()
    {
        // Definisi struktur tabel grup_materi
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'id_grup_materi' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'null'       => false,
                'comment'    => 'ID unik grup materi',
            ],
            'nama_grup_materi' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => false,
                'comment'    => 'Nama grup materi (contoh: Tilawah, Hafalan)',
            ],
            'deskripsi' => [
                'type'    => 'TEXT',
                'null'    => true,
                'comment' => 'Deskripsi grup materi',
            ],
            'urutan' => [
                'type'       => 'INT',
                'constraint' => 3,
                'default'    => 0,
                'comment'    => 'Urutan tampil',
            ],
            'status' => [
                'type'       => 'ENUM',
                'constraint' => ['aktif', 'nonaktif'],
                'default'    => 'aktif',
                'comment'    => 'Status grup materi',
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
        $this->forge->addUniqueKey('id_grup_materi');
        
        // Buat tabel
        $this->forge->createTable('tbl_munaqosah_grup_materi', true, ['ENGINE' => 'InnoDB']);
    }

    public function down()
    {
        $this->forge->dropTable('tbl_munaqosah_grup_materi', true);
    }
}
