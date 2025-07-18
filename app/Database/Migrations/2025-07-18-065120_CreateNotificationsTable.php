<?php
// File: app/Database/Migrations/2025-07-18_create_notifications_table.php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateNotificationsTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'            => ['type' => 'INT', 'constraint' => 10, 'unsigned' => true, 'auto_increment' => true],
            'user_id'       => ['type' => 'INT', 'constraint' => 10, 'unsigned' => true],
            'document_id'   => ['type' => 'INT', 'constraint' => 10, 'unsigned' => true, 'null' => true],
            'message'       => ['type' => 'TEXT'],
            'is_read'       => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
            'created_at'    => ['type' => 'DATETIME', 'null' => true],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('user_id', 'user', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('document_id', 'document', 'id', 'SET NULL', 'CASCADE');

        $this->forge->createTable('notifications');
    }

    public function down()
    {
        $this->forge->dropTable('notifications');
    }
}
