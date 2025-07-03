<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;

class KelolaDokumen extends BaseController
{
    public function add(): string
    {
        return view('KelolaDokumen/dokumen-create');
    }
    public function pengajuan()
    {
        $data['documents'] = [
            [
                'id' => 1, 
                'fakultas' => 'FSAL', 
                'bagian' => 'Yan CeLOE', 
                'nama' => 'Prosedur perubahan data', 
                'revisi' => '00', 
                'jenis' => 'internal', 
                'kode_nama' => 'IK - Perubahan Data', 
                'file' => 'file1.pdf', 
                'keterangan' => 'Keterangan 1'
            ],
            [
                'id' => 2, 
                'fakultas' => 'FSAL', 
                'bagian' => 'Yan CeLOE', 
                'nama' => 'Revisi SOP', 
                'revisi' => '01', 
                'jenis' => 'eksternal', 
                'kode_nama' => 'IK - Revisi SOP', 
                'file' => 'file2.pdf', 
                'keterangan' => 'Keterangan 2'
            ],
            [
                'id' => 3, 
                'fakultas' => 'FTI', 
                'bagian' => 'IT Support', 
                'nama' => 'Manual Penggunaan Sistem', 
                'revisi' => '02', 
                'jenis' => 'internal', 
                'kode_nama' => 'MAN - IT Support', 
                'file' => 'manual_sistem.pdf', 
                'keterangan' => 'Manual untuk pengguna baru'
            ]
        ];

        return view('KelolaDokumen/daftar-pengajuan', $data);
    }

    public function approveForm($id)
    {
        $data['document_id'] = $id;
        return view('KelolaDokumen/modal-approve', $data);
    }

    public function approve()
    {
        $data = [
            'document_id' => $this->request->getPost('document_id'),
            'remark'      => $this->request->getPost('remark'),
            'status'      => 1,
            'approvedate' => date('Y-m-d H:i:s'),
            'approveby'   => 1 // Anggap user login ID = 1
        ];

        // Simulasi insert (ganti dengan model jika real db)
        log_message('info', 'APPROVAL: ' . json_encode($data));

        return redirect()->to('/dokumen/pengajuan')->with('success', 'Dokumen berhasil di-approve.');
    }

    public function edit()
    {
        $data = [
            'fakultas'   => $this->request->getPost('fakultas'),
            'bagian'     => $this->request->getPost('bagian'),
            'nama'       => $this->request->getPost('nama'),
            'revisi'     => $this->request->getPost('revisi'),
            'jenis'      => $this->request->getPost('jenis'),
            'kode_nama'  => $this->request->getPost('kode_nama'),
            'keterangan' => $this->request->getPost('keterangan'),
            'updated_at' => date('Y-m-d H:i:s')
        ];

        // Handle file upload if exists
        $file = $this->request->getFile('file');
        if ($file && $file->isValid() && !$file->hasMoved()) {
            $data['file'] = $file->getRandomName();
            $file->move(WRITEPATH . 'uploads', $data['file']);
        }

        // Simulasi update (ganti dengan model jika real db)
        log_message('info', 'EDIT: ' . json_encode($data));

        return redirect()->to('/dokumen/pengajuan')->with('success', 'Dokumen berhasil diupdate.');
    }

    public function delete($id)
    {
        // Simulasi delete (ganti dengan model jika real db)
        log_message('info', 'DELETE: Document ID ' . $id);

        return redirect()->to('/dokumen/pengajuan')->with('success', 'Dokumen berhasil dihapus.');
    }
}