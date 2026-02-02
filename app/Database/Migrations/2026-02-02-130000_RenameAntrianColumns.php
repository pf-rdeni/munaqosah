<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class RenameAntrianColumns extends Migration
{
    public function up()
    {
        $fields = [
            'id_group_penguji' => [
                'name' => 'id_grup_juri',
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true,
                'comment' => 'ID group penguji/juri (Refactor)'
            ],
            'id_group_materi' => [
                'name' => 'id_grup_materi',
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true,
                'comment' => 'ID group materi (Refactor)'
            ],
        ];
        $this->forge->modifyColumn('tbl_munaqosah_antrian', $fields);
    }

    public function down()
    {
        $fields = [
            'id_grup_juri' => [
                'name' => 'id_group_penguji',
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true,
                'comment' => 'ID group penguji/juri'
            ],
            'id_grup_materi' => [
                'name' => 'id_group_materi',
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true,
                'comment' => 'ID group materi'
            ],
        ];
        $this->forge->modifyColumn('tbl_munaqosah_antrian', $fields);
    }
}
