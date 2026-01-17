<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddObjekPenilaianToNilai extends Migration
{
    public function up()
    {
        $fields = [
            'objek_penilaian' => [
                'type'       => 'VARCHAR',
                'constraint' => '100',
                'null'       => true,
                'after'      => 'id_grup_juri'
            ],
        ];
        $this->forge->addColumn('tbl_munaqosah_nilai_ujian', $fields);
    }

    public function down()
    {
        $this->forge->dropColumn('tbl_munaqosah_nilai_ujian', 'objek_penilaian');
    }
}
