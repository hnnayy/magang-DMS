<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateClausesTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'              => ['type' => 'INT', 'auto_increment' => true],
            'standar_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'nama_klausul'    => ['type' => 'VARCHAR', 'constraint' => 255],
            'description' => [
                'type' => 'TEXT',
                'null' => true,
                'comment' => 'Deskripsi standar'
            ],
            'status' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 1,
                'null'       => false,
                'comment'    => '0 = nonaktif, 1 = aktif',
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('standar_id', 'standards', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('clauses');
    }

    public function down()
    {
        if ($this->db->tableExists('clauses')) {
            $this->forge->dropTable('clauses');
        }
    }
    
}
