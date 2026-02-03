<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class DropIdKategoriMateriFromMateriUjian extends Migration
{
    public function up()
    {
        $this->forge->dropColumn('tbl_munaqosah_materi_ujian', 'id_kategori_materi');
    }

    public function down()
    {
        $this->forge->addColumn('tbl_munaqosah_materi_ujian', [
            'id_kategori_materi' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
                'comment'    => 'ID kategori materi',
            ],
        ]);
        $this->forge->addKey('id_kategori_materi');
    }
}
