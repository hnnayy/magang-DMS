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
                    'kode' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 10,
                    'null'       => true,
                    'after'      => 'name' 
                ],
        ]);

        $this->forge->addKey('id', true); 
        $this->forge->createTable('document_type');
    }

    public function down()
    {
        if ($this->db->tableExists('document_type')) {
            $this->forge->dropTable('document_type');
        }
    }
}
