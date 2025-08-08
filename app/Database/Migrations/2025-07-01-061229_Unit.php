<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class Unit extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'constraint' => 10,
                'unsigned' => true,
                'auto_increment' => true,
                'null'       => false,
            ],
            'parent_id' => [
                'type' => 'SMALLINT',
                'constraint' => 4,
                'null'       => false,
            ],
            'name' => [
                'type' => 'VARCHAR',
                'constraint' => 40,
                'null'       => false,
            ],
            'status' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'null'       => false,
                'comment' => '1=Active, 2=Inactive'
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('unit');
    }

    public function down()
    {
        if ($this->db->tableExists('unit')) {
            $this->forge->dropTable('unit');
        }
    }
}
