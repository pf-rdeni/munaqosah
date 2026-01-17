<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddHpAndHafalanToSiswa extends Migration
{
    public function up()
    {
        $fields = [
            'no_hp' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'null'       => true,
                'after'      => 'alamat' 
            ],
            'juz_hafalan' => [
                'type'       => 'INT',
                'constraint' => 2,
                'null'       => true,
                'comment'    => 'Target Juz Hafalan',
                'after'      => 'status'
            ],
            'surah_mulai' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
                'comment'    => 'Mulai Surah',
                'after'      => 'juz_hafalan'
            ],
            'surah_akhir' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
                'comment'    => 'Akhir Surah',
                'after'      => 'surah_mulai'
            ],
        ];

        $this->forge->addColumn('tbl_munaqosah_siswa', $fields);
    }

    public function down()
    {
        $this->forge->dropColumn('tbl_munaqosah_siswa', ['no_hp', 'juz_hafalan', 'surah_mulai', 'surah_akhir']);
    }
}
