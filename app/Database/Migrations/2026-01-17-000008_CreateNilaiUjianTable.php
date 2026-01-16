<?php

/**
 * ====================================================================
 * MIGRATION: TABEL NILAI UJIAN
 * ====================================================================
 * Tabel untuk menyimpan nilai ujian peserta munaqosah
 * 
 * @package    Munaqosah
 * @author     SDIT An-Nahl
 */

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateNilaiUjianTable extends Migration
{
    public function up()
    {
        // Definisi struktur tabel nilai_ujian
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'no_peserta' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'null'       => false,
                'comment'    => 'Nomor peserta ujian',
            ],
            'nisn' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'null'       => false,
                'comment'    => 'NISN siswa',
            ],
            'id_juri' => [
                'type'       => 'CHAR',
                'constraint' => 10,
                'null'       => false,
                'comment'    => 'ID juri penilai',
            ],
            'tahun_ajaran' => [
                'type'       => 'VARCHAR',
                'constraint' => 10,
                'null'       => false,
                'comment'    => 'Tahun ajaran',
            ],
            'id_materi' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => false,
                'comment'    => 'ID materi yang dinilai',
            ],
            'id_kriteria' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
                'comment'    => 'ID kriteria yang dinilai',
            ],
            'id_grup_materi' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
                'comment'    => 'ID grup materi',
            ],
            'id_grup_juri' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
                'comment'    => 'ID grup juri (ruangan)',
            ],
            'nilai' => [
                'type'       => 'DECIMAL',
                'constraint' => '8,2',
                'null'       => true,
                'comment'    => 'Nilai yang diberikan',
            ],
            'catatan' => [
                'type'    => 'TEXT',
                'null'    => true,
                'comment' => 'Catatan juri',
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
        $this->forge->addKey('no_peserta');
        $this->forge->addKey('nisn');
        $this->forge->addKey('id_juri');
        $this->forge->addKey('tahun_ajaran');
        $this->forge->addKey('id_materi');
        
        // Buat tabel
        $this->forge->createTable('tbl_munaqosah_nilai_ujian', true, ['ENGINE' => 'InnoDB']);
    }

    public function down()
    {
        $this->forge->dropTable('tbl_munaqosah_nilai_ujian', true);
    }
}
