<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddUserIdToJuri extends Migration
{
    public function up()
    {
        $this->forge->addColumn('tbl_munaqosah_juri', [
            'user_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
                'after'      => 'id',
                'comment'    => 'ID user auth',
            ],
        ]);

        // Optional: Add Foreign Key constraint if users table uses InnoDB
        // $this->forge->addForeignKey('user_id', 'users', 'id', 'SET NULL', 'CASCADE');
    }

    public function down()
    {
        $this->forge->dropColumn('tbl_munaqosah_juri', 'user_id');
    }
}
