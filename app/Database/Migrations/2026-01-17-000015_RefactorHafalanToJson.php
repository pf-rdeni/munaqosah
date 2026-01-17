<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class RefactorHafalanToJson extends Migration
{
    public function up()
    {
        // Remove old columns
        $this->forge->dropColumn('tbl_munaqosah_siswa', ['juz_hafalan', 'surah_mulai', 'surah_akhir']);

        // Add new JSON column
        $fields = [
            'hafalan' => [
                'type'       => 'TEXT',
                'null'       => true,
                'comment'    => 'Data hafalan format JSON: [{juz:1, mulai:A, akhir:B}, ...]',
                'after'      => 'status'
            ],
        ];
        $this->forge->addColumn('tbl_munaqosah_siswa', $fields);
    }

    public function down()
    {
        // Revert changes (Warning: Data loss of JSON content)
        $this->forge->dropColumn('tbl_munaqosah_siswa', 'hafalan');

        $fields = [
            'juz_hafalan' => [
                'type'       => 'INT',
                'constraint' => 2,
                'null'       => true,
                'after'      => 'status'
            ],
            'surah_mulai' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
                'after'      => 'juz_hafalan'
            ],
            'surah_akhir' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
                'after'      => 'surah_mulai'
            ],
        ];
        $this->forge->addColumn('tbl_munaqosah_siswa', $fields);
    }
}
