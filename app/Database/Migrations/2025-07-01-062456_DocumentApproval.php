<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class DocumentApproval extends Migration
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
            'document_id' => [
                'type'       => 'INT',
                'constraint' => 10,
                'unsigned'   => true,
                'null'       => false,
            ],
            'remark' => [
                'type'       => 'VARCHAR',
                'constraint' => 200,
                'null'       => false,
            ],
            'status' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'null'       => false,
                'comment'    => '1=Approved, 2=Disapproved',
            ],
            'approvedate' => [
                'type'    => 'DATETIME',
                'null'    => false,
                'default' => null,
            ],
            'approveby' => [
                'type'       => 'INT',
                'constraint' => 10,
                'unsigned'   => true,
                'null'       => false,
            ],
        ]);

        $this->forge->addKey('id', true); 
        $this->forge->createTable('document_approval');
    }

    public function down()
    {
        $this->forge->dropTable('document_approval');
    }
}
