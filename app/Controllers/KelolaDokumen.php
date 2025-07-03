<?php

namespace App\Controllers;

use App\Controllers\BaseController;

class KelolaDokumen extends BaseController
{
    // Dummy data kategori dokumen
    private $kategoriDokumen = [
        [
            'id' => 1,
            'nama' => 'Internal',
            'kode' => 'internal',
            'use_predefined_codes' => true,
            'status' => 1
        ],
        [
            'id' => 2,
            'nama' => 'Eksternal',
            'kode' => 'eksternal',
            'use_predefined_codes' => true,
            'status' => 1
        ],
        [
            'id' => 3,
            'nama' => 'Formulir',
            'kode' => 'formulir',
            'use_predefined_codes' => false,
            'status' => 1
        ],
        [
            'id' => 4,
            'nama' => 'Lainnya',
            'kode' => 'lainnya',
            'use_predefined_codes' => false,
            'status' => 1
        ]
    ];

    // Dummy data kode dokumen
    private $kodeDokumen = [
        'internal' => [
            ['kode' => 'IK-001', 'nama' => 'Instruksi Kerja - Perubahan Data'],
            ['kode' => 'IK-002', 'nama' => 'Instruksi Kerja - Backup Data'],
            ['kode' => 'SOP-001', 'nama' => 'Standard Operating Procedure - Admin'],
            ['kode' => 'MAN-001', 'nama' => 'Manual - Penggunaan Sistem'],
        ],
        'eksternal' => [
            ['kode' => 'EXT-001', 'nama' => 'Dokumen Eksternal - Kerjasama'],
            ['kode' => 'EXT-002', 'nama' => 'Dokumen Eksternal - Vendor'],
            ['kode' => 'REG-001', 'nama' => 'Regulasi - Pemerintah'],
            ['kode' => 'REG-002', 'nama' => 'Regulasi - Industri'],
        ]
    ];

    // View tambah dokumen
    public function add(): string
    {
        $data['kategori_dokumen'] = $this->kategoriDokumen;
        $data['kode_dokumen'] = $this->kodeDokumen;
        return view('KelolaDokumen/dokumen-create', $data);
    }

    // Untuk ambil data kode berdasarkan jenis via AJAX
    public function getKodeDokumen()
    {
        $jenis = $this->request->getPost('jenis');
        $kategori = array_filter($this->kategoriDokumen, fn($item) => $item['kode'] === $jenis);
        $kategori = reset($kategori);

        if (!$kategori || !$kategori['use_predefined_codes']) {
            return $this->response->setJSON([]);
        }

        $kodes = $this->kodeDokumen[$jenis] ?? [];
        return $this->response->setJSON($kodes);
    }

    // Dummy daftar dokumen pengajuan
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
                'kode_nama' => 'IK-001 - Perubahan Data',
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
                'kode_nama' => 'EXT-001 - Revisi SOP',
                'file' => 'file2.pdf',
                'keterangan' => 'Keterangan 2'
            ],
            [
                'id' => 3,
                'fakultas' => 'FTI',
                'bagian' => 'IT Support',
                'nama' => 'Manual Penggunaan Sistem',
                'revisi' => '02',
                'jenis' => 'formulir',
                'kode_nama' => 'FORM-MANUAL-001',
                'file' => 'manual_sistem.pdf',
                'keterangan' => 'Manual untuk pengguna baru'
            ]
        ];
        return view('KelolaDokumen/daftar-pengajuan', $data);
    }

    // View konfigurasi kategori
    public function configJenisDokumen()
{
    // Dummy kategori dan kode dokumen (bisa diganti ke DB kalau sudah siap)
    $data['kategori_dokumen'] = $this->kategoriDokumen;
    $data['kode_dokumen'] = $this->kodeDokumen;
    return view('KelolaDokumen/config-jenis-dokumen', $data);
}

    // Tambah kategori dokumen
    public function addKategori()
    {
        $data = [
            'nama' => $this->request->getPost('nama'),
            'kode' => strtolower(str_replace(' ', '_', $this->request->getPost('nama'))),
            'use_predefined_codes' => $this->request->getPost('use_predefined_codes') ? true : false,
            'status' => 1
        ];

        log_message('info', 'ADD KATEGORI: ' . json_encode($data));
        return redirect()->to('/dokumen/config-kategori')->with('success', 'Kategori berhasil ditambahkan.');
    }

    // Edit kategori dokumen
    public function editKategori($id)
    {
        $data = [
            'nama' => $this->request->getPost('nama'),
            'kode' => strtolower(str_replace(' ', '_', $this->request->getPost('nama'))),
            'use_predefined_codes' => $this->request->getPost('use_predefined_codes') ? true : false,
            'updated_at' => date('Y-m-d H:i:s')
        ];

        log_message('info', 'EDIT KATEGORI ID ' . $id . ': ' . json_encode($data));
        return redirect()->to('/dokumen/config-kategori')->with('success', 'Kategori berhasil diupdate.');
    }

    // Hapus kategori dokumen
    public function deleteKategori($id)
    {
        log_message('info', 'DELETE KATEGORI: ID ' . $id);
        return redirect()->to('/dokumen/config-kategori')->with('success', 'Kategori berhasil dihapus.');
    }

    // View modal approval
    public function approveForm($id)
    {
        $data['document_id'] = $id;
        return view('KelolaDokumen/modal-approve', $data);
    }

    // Simulasi aksi approval dokumen
    public function approve()
    {
        $data = [
            'document_id' => $this->request->getPost('document_id'),
            'remark' => $this->request->getPost('remark'),
            'status' => 1,
            'approvedate' => date('Y-m-d H:i:s'),
            'approveby' => 1 // user login dummy
        ];

        log_message('info', 'APPROVAL: ' . json_encode($data));
        return redirect()->to('/dokumen/pengajuan')->with('success', 'Dokumen berhasil di-approve.');
    }

    // Simulasi edit dokumen
    public function edit()
    {
        $data = [
            'fakultas' => $this->request->getPost('fakultas'),
            'bagian' => $this->request->getPost('bagian'),
            'nama' => $this->request->getPost('nama'),
            'revisi' => $this->request->getPost('revisi'),
            'jenis' => $this->request->getPost('jenis'),
            'kode_nama' => $this->request->getPost('kode_nama'),
            'keterangan' => $this->request->getPost('keterangan'),
            'updated_at' => date('Y-m-d H:i:s')
        ];

        $file = $this->request->getFile('file');
        if ($file && $file->isValid() && !$file->hasMoved()) {
            $data['file'] = $file->getRandomName();
            $file->move(WRITEPATH . 'uploads', $data['file']);
        }

        log_message('info', 'EDIT: ' . json_encode($data));
        return redirect()->to('/dokumen/pengajuan')->with('success', 'Dokumen berhasil diupdate.');
    }

    // Simulasi hapus dokumen
    public function delete($id)
    {
        log_message('info', 'DELETE: Document ID ' . $id);
        return redirect()->to('/dokumen/pengajuan')->with('success', 'Dokumen berhasil dihapus.');
    }
}
