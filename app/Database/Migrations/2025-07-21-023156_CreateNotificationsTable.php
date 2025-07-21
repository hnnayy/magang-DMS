<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateNotificationsTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'            => [
                'type'           => 'INT',
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'receiver_id'   => [
                'type'       => 'INT',
                'constraint' => 11,
                'null'       => false,
            ],
            'user_id'       => [
                'type'       => 'INT',
                'unsigned'   => true,
                'null'       => false,
            ],
            'document_id'   => [
                'type'       => 'INT',
                'unsigned'   => true,
                'null'       => true,
            ],
            'title'         => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => false,
            ],
            'message'       => [
                'type' => 'TEXT',
                'null' => false,
            ],
            'link'          => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'is_read'       => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 0,
            ],
            'created_at'    => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at'    => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addKey('id', true); // Primary key
        $this->forge->createTable('notifications');
    }

    public function down()
    {
        $this->forge->dropTable('notifications');
    }
}
