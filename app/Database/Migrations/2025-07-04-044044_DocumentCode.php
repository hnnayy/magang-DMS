<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class DocumentCode extends Migration
{
    public function up()
    {
        $this->forge->addField([
        'id' => [
            'type'           => 'INT',
            'constraint'     => 10,
            'unsigned'       => true,
            'auto_increment' => true
        ],
        'document_type_id' => [
            'type'       => 'INT',
            'constraint' => 10,
            'unsigned'   => true
        ],
        'kode' => [
            'type'       => 'VARCHAR',
            'constraint' => 40
        ],
        'nama' => [
            'type'       => 'VARCHAR',
            'constraint' => 100
        ],
        'status' => [
            'type'       => 'TINYINT',
            'constraint' => 1,
            'default'    => 1,
            'comment'    => '1=Active, 2=Inactive'
        ],
        'created_at' => [
            'type' => 'DATETIME',
            'null' => true
        ],
        'updated_at' => [
            'type' => 'DATETIME',
            'null' => true
        ],
    ]);


        $this->forge->addKey('id', true);
        $this->forge->createTable('kode_dokumen');
    }

    public function down()
    {
        if ($this->db->tableExists('kode_dokumen')) {
            $this->forge->dropTable('kode_dokumen');
        }
    }
}
