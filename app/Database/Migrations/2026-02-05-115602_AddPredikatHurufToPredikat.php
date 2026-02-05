<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddPredikatHurufToPredikat extends Migration
{
    public function up()
    {
        $fields = [
            'predikat_huruf' => [
                'type'       => 'VARCHAR',
                'constraint' => '5',
                'null'       => true,
                'after'      => 'nama_predikat',
            ],
        ];
        $this->forge->addColumn('tbl_munaqosah_predikat', $fields);
    }

    public function down()
    {
        $this->forge->dropColumn('tbl_munaqosah_predikat', 'predikat_huruf');
    }
}
