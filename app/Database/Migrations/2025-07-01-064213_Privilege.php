<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class Privilege extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'         => [
                'type'           => 'INT',
                'constraint'     => 10,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'role_id'    => [
                'type'       => 'INT',
                'constraint' => 10,
                'unsigned'   => true,
                'null'       => false,
            ],
            'submenu_id' => [
                'type'       => 'INT',
                'constraint' => 10,
                'unsigned'   => true,
                'null'       => false,
            ],
            'can_create'     => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'null'       => false,
                'default'    => 0,
                'comment'    => '0=No, 1=Yes',
            ],
            'can_update'     => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'null'       => false,
                'default'    => 0,
                'comment'    => '0=No, 1=Yes',
            ],
            'can_delete'     => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'null'       => false,
                'default'    => 0,
                'comment'    => '0=No, 1=Yes',
            ],
            'can_approve'    => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'null'       => false,
                'default'    => 0,
                'comment'    => '0=No, 1=Yes',
            ],
        ]);

        $this->forge->addKey('id', true); 
        $this->forge->createTable('privilege');
    }

    public function down()
    {
        $this->forge->dropTable('privilege');
    }
}
