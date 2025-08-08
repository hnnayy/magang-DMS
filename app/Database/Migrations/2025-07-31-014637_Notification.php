<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class Notification extends Migration
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
            'submenu_id' => [
                'type'       => 'INT',
                'constraint' => 10,
                'unsigned'   => true,
                'null'           => false,
            ],
            'reference_id' => [
                'type'       => 'INT',
                'constraint' => 10,
                'unsigned'   => true,
                'null'           => false,
            ],
            'message' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'           => false,
            ],
            'createdby' => [
                'type'       => 'INT',
                'constraint' => 10,
                'unsigned'   => true,
                'null'       => false,
             ],           
            'createddate' => [
                'type' => 'DATETIME',
                'null'           => false,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('notification');
    }

    public function down()
    {
        if ($this->db->tableExists('notification')) {
            $this->forge->dropTable('notification');
        }
    }
}
