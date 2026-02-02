<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class RefactorAntrianTable extends Migration
{
    public function up()
    {
        $this->forge->dropColumn('tbl_munaqosah_antrian', ['id_kategori_materi', 'nomor_antrian']);
    }

    public function down()
    {
        $this->forge->addColumn('tbl_munaqosah_antrian', [
            'id_kategori_materi' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
                'comment'    => 'ID kategori materi yang diujikan',
            ],
            'nomor_antrian' => [
                'type'       => 'INT',
                'constraint' => 5,
                'null'       => true,
                'comment'    => 'Nomor urut antrian',
            ],
        ]);
    }
}
