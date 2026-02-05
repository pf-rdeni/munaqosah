<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddNisToSiswa extends Migration
{
    public function up()
    {
        $fields = [
            'nis' => [
                'type'       => 'VARCHAR',
                'constraint' => '20',
                'null'       => true,
                'after'      => 'nisn',
            ],
        ];
        $this->forge->addColumn('tbl_munaqosah_siswa', $fields);
    }

    public function down()
    {
        $this->forge->dropColumn('tbl_munaqosah_siswa', 'nis');
    }
}
