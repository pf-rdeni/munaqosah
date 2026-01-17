<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddIdGrupJuriToJuri extends Migration
{
    public function up()
    {
        $fields = [
            'id_grup_juri' => [
                'type'       => 'INT',
                'constraint' => 11,
                'default'    => 0,
                'comment'    => '1-10 Group ID for Logic'
            ]
        ];
        $this->forge->addColumn('tbl_munaqosah_juri', $fields);
    }

    public function down()
    {
        $this->forge->dropColumn('tbl_munaqosah_juri', 'id_grup_juri');
    }
}
