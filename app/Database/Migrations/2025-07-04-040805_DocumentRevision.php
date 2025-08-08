<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class DocumentRevision extends Migration
{
     public function up()
    {
        $this->forge->addField([
            'id'           => [
                'type'           => 'INT',
                'constraint'     => 10,
                'unsigned'       => true,
                'auto_increment' => true,
                'null'           => false,
            ],
            'document_id'   => [
                'type'       => 'INT',
                'constraint' => 10,
                'unsigned'   => true,
                'null'       => false,
            ],
            'revision'      => [
                'type'       => 'CHAR',
                'constraint' => 3,
                'null'       => false,
            ],
            'filename'      => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => false,
            ],
            'filepath'      => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => false,
            ],
            'filesize'      => [
                'type'       => 'INT',
                'constraint' => 10,
                'unsigned'   => true,
                'null'       => false,
            ],
            'remark'        => [
                'type'       => 'VARCHAR',
                'constraint' => 200,
                'null'       => true,
            ],
            'createddate'   => [
                'type' => 'DATETIME',
                'null' => false,
            ],
            'createdby'     => [
                'type'       => 'INT',
                'constraint' => 10,
                'unsigned'   => true,
                'null'       => false,
            ],
        ]);

        $this->forge->addKey('id', true); 
        $this->forge->createTable('document_revision');
    }

    public function down()
    {
        if ($this->db->tableExists('document_revision')) {
            $this->forge->dropTable('document_revision');
        }
    }
}