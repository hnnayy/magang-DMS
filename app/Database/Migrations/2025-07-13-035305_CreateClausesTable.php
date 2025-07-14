<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateClausesTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'              => ['type' => 'INT', 'auto_increment' => true],
            'standar_id'      => ['type' => 'INT'],
            'nomor_klausul'   => ['type' => 'VARCHAR', 'constraint' => 50],
            'nama_klausul'    => ['type' => 'VARCHAR', 'constraint' => 255],
            'created_at'      => ['type' => 'DATETIME', 'null' => true],
            'updated_at'      => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('standar_id', 'standards', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('clauses');
    }

    public function down()
    {
        $this->forge->dropTable('clauses');
    }
}
