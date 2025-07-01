<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class DocumentType extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 10,
                'unsigned'       => true,
                'auto_increment' => true,
                'null'       => false,
            ],
            'name' => [
                'type'       => 'VARCHAR',
                'constraint' => 40,
                'null'       => false,
                'comment'    => 'alphanumeric + spasi',
            ],
            'description' => [
                'type'       => 'VARCHAR',
                'constraint' => 200,
                'null'       => true,
            ],
            'status' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'null'       => false,
                'comment'    => '1=Active, 2=Inactive',
            ],
        ]);

        $this->forge->addKey('id', true); 
        $this->forge->createTable('document_type');
    }

    public function down()
    {
        $this->forge->dropTable('document_type');
    }
}
