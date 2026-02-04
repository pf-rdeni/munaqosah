<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddIdGrupMateriToPredikat extends Migration
{
    public function up()
    {
        $this->forge->addColumn('tbl_munaqosah_predikat', [
            'id_grup_materi' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'null'           => true,
                'after'          => 'id',
                'comment'        => 'NULL = Global, INT = Spesifik Grup Materi'
            ],
        ]);

        // Add Foreign Key (Optional but good for integrity)
        // $this->forge->addForeignKey('id_grup_materi', 'tbl_munaqosah_grup_materi', 'id', 'CASCADE', 'SET NULL');
        // Note: CI4 addForeignKey usually done in createTable, for modifyColumn getting key constraint right can be tricky with raw SQL sometimes, 
        // but let's try standard forge if possible or raw SQL if safer. 
        // For simplicity and speed in this context, we will add index but maybe skip strict FK constraint to avoid error if table has data issues, 
        // but wait, table is fresh-ish. Let's add simple index.
        
        $this->db->query('ALTER TABLE tbl_munaqosah_predikat ADD KEY idx_grup_materi (id_grup_materi)');
    }

    public function down()
    {
        $this->forge->dropColumn('tbl_munaqosah_predikat', 'id_grup_materi');
    }
}
