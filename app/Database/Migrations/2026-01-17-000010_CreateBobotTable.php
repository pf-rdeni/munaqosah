<?php

/**
 * ====================================================================
 * MIGRATION: TABEL BOBOT
 * ====================================================================
 * Tabel untuk menyimpan bobot penilaian per kriteria
 * 
 * @package    Munaqosah
 * @author     SDIT An-Nahl
 */

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateBobotTable extends Migration
{
    public function up()
    {
        // Definisi struktur tabel bobot
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'id_bobot' => [
                'type'       => 'CHAR',
                'constraint' => 10,
                'null'       => false,
                'comment'    => 'ID unik bobot',
            ],
            'id_kriteria' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => false,
                'comment'    => 'ID kriteria terkait',
            ],
            'bobot_poin' => [
                'type'       => 'DECIMAL',
                'constraint' => '5,2',
                'null'       => false,
                'default'    => 1.00,
                'comment'    => 'Nilai bobot poin',
            ],
            'tahun_ajaran' => [
                'type'       => 'VARCHAR',
                'constraint' => 10,
                'null'       => true,
                'comment'    => 'Tahun ajaran berlaku',
            ],
            'keterangan' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
                'comment'    => 'Keterangan bobot',
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
        $this->forge->addUniqueKey('id_bobot');
        $this->forge->addKey('id_kriteria');
        
        // Buat tabel
        $this->forge->createTable('tbl_munaqosah_bobot', true, ['ENGINE' => 'InnoDB']);
    }

    public function down()
    {
        $this->forge->dropTable('tbl_munaqosah_bobot', true);
    }
}
