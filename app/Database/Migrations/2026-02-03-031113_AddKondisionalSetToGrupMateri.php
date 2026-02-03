<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddKondisionalSetToGrupMateri extends Migration
{
    public function up()
    {
        $fields = [
            'kondisional_set' => [
                'type'       => 'ENUM',
                'constraint' => ['nilai_default', 'nilai_pengurangan', 'nilai_penjumlahan'],
                'default'    => 'nilai_default',
                'comment'    => 'Jenis perhitungan nilai untuk grup ini',
            ],
        ];
        $this->forge->addColumn('tbl_munaqosah_grup_materi', $fields);
    }

    public function down()
    {
        $this->forge->dropColumn('tbl_munaqosah_grup_materi', 'kondisional_set');
    }
}
