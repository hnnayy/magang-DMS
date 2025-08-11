<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateStandardsTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'nama_standar' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
            ],
            'description' => [
                'type' => 'TEXT',
                'null' => true,
                'comment' => 'Deskripsi standar'
            ],
            'status' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 1,
                'null'       => false,
                'comment'    => '0 = nonaktif, 1 = aktif',
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('standards');
    }

    public function down()
    {
        $this->forge->dropTable('standards');
    }
}
