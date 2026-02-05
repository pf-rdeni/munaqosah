<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateSertifikatTables extends Migration
{
    public function up()
    {
        // Table: tbl_munaqosah_sertifikat_template
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'halaman' => [
                'type'       => 'ENUM',
                'constraint' => ['depan', 'belakang'],
                'null'       => false,
            ],
            'file_template' => [
                'type'       => 'VARCHAR',
                'constraint' => '255',
            ],
            'width' => [
                'type'       => 'INT',
                'constraint' => 11,
            ],
            'height' => [
                'type'       => 'INT',
                'constraint' => 11,
            ],
            'orientation' => [
                'type'       => 'ENUM',
                'constraint' => ['landscape', 'portrait'],
                'default'    => 'landscape',
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('halaman'); // One template per page type
        $this->forge->createTable('tbl_munaqosah_sertifikat_template');

        // Table: tbl_munaqosah_sertifikat_fields
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'template_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'field_name' => [
                'type'       => 'VARCHAR',
                'constraint' => '100',
            ],
            'field_label' => [
                'type'       => 'VARCHAR',
                'constraint' => '100',
            ],
            'pos_x' => [
                'type'       => 'INT',
                'constraint' => 11,
                'default'    => 0,
            ],
            'pos_y' => [
                'type'       => 'INT',
                'constraint' => 11,
                'default'    => 0,
            ],
            'font_family' => [
                'type'       => 'VARCHAR',
                'constraint' => '50',
                'default'    => 'Arial',
            ],
            'font_size' => [
                'type'       => 'INT',
                'constraint' => 11,
                'default'    => 12,
            ],
            'font_style' => [
                'type'       => 'VARCHAR',
                'constraint' => '20',
                'default'    => 'N', // N: Normal, B: Bold, I: Italic
            ],
            'text_align' => [
                'type'       => 'VARCHAR',
                'constraint' => '10',
                'default'    => 'L', // L: Left, C: Center, R: Right
            ],
            'text_color' => [
                'type'       => 'VARCHAR',
                'constraint' => '20',
                'default'    => '#000000',
            ],
            'max_width' => [
                'type'       => 'INT',
                'constraint' => 11,
                'default'    => 0,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('template_id', 'tbl_munaqosah_sertifikat_template', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('tbl_munaqosah_sertifikat_fields');
    }

    public function down()
    {
        $this->forge->dropTable('tbl_munaqosah_sertifikat_fields');
        $this->forge->dropTable('tbl_munaqosah_sertifikat_template');
    }
}
