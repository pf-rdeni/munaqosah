<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddSurahColumnAndSettingTable extends Migration
{
    public function up()
    {
        // 1. Add surah JSON column to peserta table
        $this->forge->addColumn('tbl_munaqosah_peserta', [
            'surah' => [
                'type' => 'JSON',
                'null' => true,
                'after' => 'status'
            ]
        ]);

        // 2. Create setting undian table
        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'key' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
                'null' => false,
            ],
            'value' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'tahun_ajaran' => [
                'type' => 'VARCHAR',
                'constraint' => 10,
                'null' => false,
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
        $this->forge->addUniqueKey(['key', 'tahun_ajaran'], 'unique_key_tahun');
        $this->forge->createTable('tbl_munaqosah_setting_undian', true);
    }

    public function down()
    {
        // Remove surah column
        $this->forge->dropColumn('tbl_munaqosah_peserta', 'surah');
        
        // Drop setting table
        $this->forge->dropTable('tbl_munaqosah_setting_undian', true);
    }
}
