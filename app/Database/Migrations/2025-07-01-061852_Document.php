<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class Document extends Migration
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
            'type' => [
                'type'       => 'INT',
                'constraint' => 10,
                'unsigned'   => true,
                'null'       => false,
            ],
            'kode_dokumen_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'null'       => true,
            ],
            'number' => [
                'type'       => 'VARCHAR',
                'constraint' => 40,
                'null'       => false,
            ],
            'date_published' => [
                'type' => 'DATE',
                'null' => false,
            ],
            'revision' => [
                'type'       => 'CHAR',
                'constraint' => 3,
                'null'       => false,
            ],
            'title' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => false,
            ],
            'description' => [
                'type'       => 'VARCHAR',
                'constraint' => 200,
                'null'       => true,
            ],
            'unit_id' => [
                'type'       => 'INT',
                'constraint' => 10,
                'unsigned'   => true,
                'null'       => false,
            ],
            'unit_parent_id' => [ 
                'type'       => 'INT',
                'constraint' => 10,
                'unsigned'   => true,
                'null'       => false,
            ],
            'status' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'null'       => false,
                'comment'    => '0=Not Approved, 1=Approved, 2=Disapproved',
            ],
            'createddate' => [
                'type'    => 'DATETIME',
                'null'    => true,
                'default' => null,
            ],
            'createdby' => [
                'type'       => 'INT',
                'constraint' => 10,
                'unsigned'   => true,
                'null'       => false,
            ],
            'filepath' => [
            'type'       => 'VARCHAR',
            'constraint' => 255,
            'null'       => true,
            ],
            'filename' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],

        ]);

        $this->forge->addKey('id', true); 
        $this->forge->createTable('document');
    }

    public function down()
    {
        $this->forge->dropTable('document');
    }
}
