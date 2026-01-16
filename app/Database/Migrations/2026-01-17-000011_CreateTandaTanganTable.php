<?php

/**
 * ====================================================================
 * MIGRATION: TABEL TANDA TANGAN
 * ====================================================================
 * Tabel untuk menyimpan tanda tangan digital dengan QR Code
 * 
 * @package    Munaqosah
 * @author     SDIT An-Nahl
 */

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateTandaTanganTable extends Migration
{
    public function up()
    {
        // Definisi struktur tabel tanda_tangan
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'token' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => false,
                'comment'    => 'Token unik untuk tanda tangan',
            ],
            'signatur_data' => [
                'type'    => 'JSON',
                'null'    => true,
                'comment' => 'Data tanda tangan dalam format JSON',
            ],
            'qr_code' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
                'comment'    => 'Path file QR Code',
            ],
            'tipe' => [
                'type'       => 'ENUM',
                'constraint' => ['siswa', 'juri', 'kepala', 'panitia'],
                'default'    => 'siswa',
                'comment'    => 'Tipe pemilik tanda tangan',
            ],
            'id_pemilik' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
                'comment'    => 'ID pemilik tanda tangan',
            ],
            'status' => [
                'type'       => 'ENUM',
                'constraint' => ['aktif', 'nonaktif', 'kadaluarsa'],
                'default'    => 'aktif',
                'comment'    => 'Status tanda tangan',
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

        // Set primary key dan index
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('token');
        
        // Buat tabel
        $this->forge->createTable('tbl_munaqosah_tanda_tangan', true, ['ENGINE' => 'InnoDB']);
    }

    public function down()
    {
        $this->forge->dropTable('tbl_munaqosah_tanda_tangan', true);
    }
}
