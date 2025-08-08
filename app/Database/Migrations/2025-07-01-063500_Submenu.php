<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class Submenu extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 10,
                'unsigned'       => true,
                'auto_increment' => true,
                'null'           => false,
            ],
            'parent' => [
                'type'       => 'INT',
                'constraint' => 10,
                'unsigned'   => true,
                'null'       => false,
            ],
            'name' => [
                'type'       => 'VARCHAR',
                'constraint' => 40,
                'null'       => false,
            ],
            'status' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'null'       => false,
                'comment'    => '1=Active, 2=Inactive',
            ],
        ]);

        $this->forge->addKey('id', true); 
        $this->forge->createTable('submenu');
    }

    public function down()
    {
        if ($this->db->tableExists('submenu')) {
            $this->forge->dropTable('submenu');
        }
    }
}
