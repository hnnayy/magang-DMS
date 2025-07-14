<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class ClauseSeeder extends Seeder
{
    public function run()
    {
        // Ambil ID dari tabel standards
        $standardModel = new \App\Models\StandardModel();
        $standards = $standardModel->findAll();

        $clauses = [];

        foreach ($standards as $standard) {
            if ($standard['nama_standar'] === 'ISO 9001') {
                $clauses[] = [
                    'standar_id'     => $standard['id'],
                    'nomor_klausul'  => '7.1',
                    'nama_klausul'   => 'Resources',
                ];
                $clauses[] = [
                    'standar_id'     => $standard['id'],
                    'nomor_klausul'  => '8.2.2',
                    'nama_klausul'   => 'Determining the requirements',
                ];
            }

            if ($standard['nama_standar'] === 'ISO 27001') {
                $clauses[] = [
                    'standar_id'     => $standard['id'],
                    'nomor_klausul'  => '9.1',
                    'nama_klausul'   => 'Monitoring, measurement, analysis and evaluation',
                ];
            }
        }

        $this->db->table('clauses')->insertBatch($clauses);
    }
}
