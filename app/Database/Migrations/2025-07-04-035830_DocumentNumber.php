<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class DocumentNumber extends Migration
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
            'unit_id' => [
                'type'       => 'INT',
                'constraint' => 10,
                'unsigned'   => true,
                'null'       => false,
            ],
            'type' => [
                'type'       => 'INT',
                'constraint' => 10,
                'unsigned'   => true,
            ],
            'number' => [
                'type'       => 'VARCHAR',
                'constraint' => 40,
                'null'       => false,
            ],
            'createddate' => [
                'type'    => 'TIMESTAMP',
                'null'    => false,
            ],
            'createdby' => [
                'type'       => 'INT',
                'constraint' => 10,
                'unsigned'   => true,
                'null'       => false,
            ],
        ]);

        $this->forge->addKey('id', true);

        $this->forge->createTable('document_number');
    }

    public function down()
    {
        $this->forge->dropTable('document_number');
    }
}