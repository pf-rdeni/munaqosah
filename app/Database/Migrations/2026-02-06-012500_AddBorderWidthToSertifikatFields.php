<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddBorderWidthToSertifikatFields extends Migration
{
    public function up()
    {
        $this->forge->addColumn('tbl_munaqosah_sertifikat_fields', [
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
        $this->forge->dropColumn('tbl_munaqosah_sertifikat_fields', 'border_width');
    }
}
