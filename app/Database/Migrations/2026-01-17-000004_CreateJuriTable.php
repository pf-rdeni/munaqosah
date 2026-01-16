<?php

/**
 * ====================================================================
 * MIGRATION: TABEL JURI
 * ====================================================================
 * Tabel untuk menyimpan data juri/penguji munaqosah
 * 
 * @package    Munaqosah
 * @author     SDIT An-Nahl
 */

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateJuriTable extends Migration
{
    public function up()
    {
        // Definisi struktur tabel juri
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'id_juri' => [
                'type'       => 'CHAR',
                'constraint' => 10,
                'null'       => false,
                'comment'    => 'ID unik juri',
            ],
            'nama_juri' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => false,
                'comment'    => 'Nama lengkap juri',
            ],
            'username' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => false,
                'comment'    => 'Username untuk login',
            ],
            'id_grup_materi' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
                'comment'    => 'ID grup materi yang diuji',
            ],
            'id_grup_juri' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
                'comment'    => 'ID grup juri (ruangan)',
            ],
            'no_ruangan' => [
                'type'       => 'VARCHAR',
                'constraint' => 10,
                'null'       => true,
                'comment'    => 'Nomor ruangan ujian',
            ],
            'status' => [
                'type'       => 'ENUM',
                'constraint' => ['aktif', 'nonaktif'],
                'default'    => 'aktif',
                'comment'    => 'Status juri',
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
        $this->forge->addUniqueKey('id_juri');
        $this->forge->addUniqueKey('username');
        
        // Buat tabel
        $this->forge->createTable('tbl_munaqosah_juri', true, ['ENGINE' => 'InnoDB']);
    }

    public function down()
    {
        $this->forge->dropTable('tbl_munaqosah_juri', true);
    }
}
