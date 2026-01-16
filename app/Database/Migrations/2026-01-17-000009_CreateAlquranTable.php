<?php

/**
 * ====================================================================
 * MIGRATION: TABEL ALQURAN
 * ====================================================================
 * Tabel untuk menyimpan referensi ayat Al-Qur'an untuk ujian
 * 
 * @package    Munaqosah
 * @author     SDIT An-Nahl
 */

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateAlquranTable extends Migration
{
    public function up()
    {
        // Definisi struktur tabel alquran
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'id_materi' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
                'comment'    => 'ID materi terkait',
            ],
            'id_kategori_materi' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
                'comment'    => 'ID kategori materi',
            ],
            'id_surah' => [
                'type'       => 'INT',
                'constraint' => 5,
                'null'       => false,
                'comment'    => 'ID surah (1-114)',
            ],
            'id_ayat' => [
                'type'       => 'INT',
                'constraint' => 5,
                'null'       => false,
                'comment'    => 'ID/nomor ayat',
            ],
            'nama_surah' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => false,
                'comment'    => 'Nama surah dalam bahasa Arab latin',
            ],
            'nama_surah_arab' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
                'comment'    => 'Nama surah dalam huruf Arab',
            ],
            'teks_ayat' => [
                'type'    => 'TEXT',
                'null'    => true,
                'comment' => 'Teks ayat dalam huruf Arab',
            ],
            'link_ayat' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
                'comment'    => 'Link ke audio/referensi ayat',
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
        $this->forge->addKey('id_surah');
        $this->forge->addKey('id_materi');
        
        // Buat tabel
        $this->forge->createTable('tbl_munaqosah_alquran', true, ['ENGINE' => 'InnoDB']);
    }

    public function down()
    {
        $this->forge->dropTable('tbl_munaqosah_alquran', true);
    }
}
