<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class RefactorBorderSettingsToJson extends Migration
{
    public function up()
    {
        // 1. Add new JSON column
        $this->forge->addColumn('tbl_munaqosah_sertifikat_fields', [
            'border_settings' => [
                'type' => 'JSON',
                'null' => true,
                'after' => 'max_width'
            ]
        ]);

        // 2. Migrate existing data to JSON
        $db = \Config\Database::connect();
        $fields = $db->table('tbl_munaqosah_sertifikat_fields')->get()->getResultArray();
        
        foreach ($fields as $field) {
            $borderSettings = [
                'enabled' => (bool)($field['has_border'] ?? 0),
                'color' => $field['border_color'] ?? '#000000',
                'width' => (int)($field['border_width'] ?? 1)
            ];
            
            $db->table('tbl_munaqosah_sertifikat_fields')
                ->where('id', $field['id'])
                ->update(['border_settings' => json_encode($borderSettings)]);
        }

        // 3. Drop old columns
        $this->forge->dropColumn('tbl_munaqosah_sertifikat_fields', 'has_border');
        $this->forge->dropColumn('tbl_munaqosah_sertifikat_fields', 'border_color');
        $this->forge->dropColumn('tbl_munaqosah_sertifikat_fields', 'border_width');
    }

    public function down()
    {
        // Restore old columns
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

        // Migrate data back from JSON
        $db = \Config\Database::connect();
        $fields = $db->table('tbl_munaqosah_sertifikat_fields')->get()->getResultArray();
        
        foreach ($fields as $field) {
            if (!empty($field['border_settings'])) {
                $settings = json_decode($field['border_settings'], true);
                $db->table('tbl_munaqosah_sertifikat_fields')
                    ->where('id', $field['id'])
                    ->update([
                        'has_border' => $settings['enabled'] ? 1 : 0,
                        'border_color' => $settings['color'] ?? '#000000',
                        'border_width' => $settings['width'] ?? 1
                    ]);
            }
        }

        // Drop JSON column
        $this->forge->dropColumn('tbl_munaqosah_sertifikat_fields', 'border_settings');
    }
}
