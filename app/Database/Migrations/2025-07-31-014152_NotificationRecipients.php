<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class NotificationRecipients extends Migration
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
            'notification_id' => [
                'type'       => 'INT',
                'constraint' => 10,
                'unsigned'   => true,
                'null'           => false,
            ],
            'user_id' => [
                'type'       => 'INT',
                'constraint' => 10,
                'unsigned'   => true,
                'null'           => false,
            ],
            'status' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 0,
                'null'           => false,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('notification_recipients');
    }

    public function down()
    {
        $this->forge->dropTable('notification_recipients');
    }
}
