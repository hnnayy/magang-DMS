<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class UnitParent extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'constraint' => 10,
                'unsigned' => true,
                'auto_increment' => true,
                'null' => false,
            ],
            'type' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'null' => false,
                'comment' => '1=Directorate, 2=Faculty'
            ],
            'name' => [
                'type' => 'VARCHAR',
                'constraint' => 100, // Menambah panjang menjadi 100 karakter
                'null' => false,
            ],
            'status' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'null' => false,
                'comment' => '1=Active, 2=Inactive'
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
                'default' => null,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
                'default' => null,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('unit_parent');
    }

    public function down()
    {
        $this->forge->dropTable('unit_parent');
    }
}
