<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateJuriKriteriaTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'id_juri' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'id_kriteria' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey(['id_juri', 'id_kriteria'], 'unique_juri_kriteria');
        
        $this->forge->createTable('tbl_munaqosah_juri_kriteria', true);
    }

    public function down()
    {
        $this->forge->dropTable('tbl_munaqosah_juri_kriteria', true);
    }
}
