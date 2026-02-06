<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddBorderFieldsToSertifikatFields extends Migration
{
    public function up()
    {
        $this->forge->addColumn('tbl_munaqosah_sertifikat_fields', [
            'has_border' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'default' => 0,
                'after' => 'max_width'
            ],
            'border_color' => [
                'type' => 'VARCHAR',
                'constraint' => 20,
                'default' => '#000000',
                'after' => 'has_border'
            ],
            'border_width' => [
                'type' => 'TINYINT',
                'constraint' => 2,
                'default' => 1,
                'after' => 'border_color'
            ]
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('tbl_munaqosah_sertifikat_fields', 'has_border');
        $this->forge->dropColumn('tbl_munaqosah_sertifikat_fields', 'border_color');
    }
}
