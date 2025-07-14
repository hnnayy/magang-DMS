<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateStandardsTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'            => ['type' => 'INT', 'auto_increment' => true],
            'nama_standar'  => ['type' => 'VARCHAR', 'constraint' => 255],
            'created_at'    => ['type' => 'DATETIME', 'null' => true],
            'updated_at'    => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('standards');
    }

    public function down()
    {
        $this->forge->dropTable('standards');
    }
}
