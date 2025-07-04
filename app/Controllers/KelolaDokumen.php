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
            ['id' => 1, 'kode' => 'IK-001', 'nama' => 'Instruksi Kerja - Perubahan Data'],
            ['id' => 2, 'kode' => 'IK-002', 'nama' => 'Instruksi Kerja - Backup Data'],
            ['id' => 3, 'kode' => 'SOP-001', 'nama' => 'Standard Operating Procedure - Admin'],
            ['id' => 4, 'kode' => 'MAN-001', 'nama' => 'Manual - Penggunaan Sistem'],
        ],
        'eksternal' => [
            ['id' => 5, 'kode' => 'EXT-001', 'nama' => 'Dokumen Eksternal - Kerjasama'],
            ['id' => 6, 'kode' => 'EXT-002', 'nama' => 'Dokumen Eksternal - Vendor'],
            ['id' => 7, 'kode' => 'REG-001', 'nama' => 'Regulasi - Pemerintah'],
            ['id' => 8, 'kode' => 'REG-002', 'nama' => 'Regulasi - Industri'],
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

    // ==================== CRUD KATEGORI DOKUMEN ====================

    // Tambah kategori dokumen
    public function addKategori()
    {
        $nama = $this->request->getPost('nama');
        $kode = $this->request->getPost('kode');
        $use_predefined = $this->request->getPost('use_predefined_codes') ? true : false;

        $data = [
            'id' => count($this->kategoriDokumen) + 1, // Auto increment dummy
            'nama' => $nama,
            'kode' => $kode,
            'use_predefined_codes' => $use_predefined,
            'status' => 1,
            'created_at' => date('Y-m-d H:i:s')
        ];

        log_message('info', 'ADD KATEGORI: ' . json_encode($data));
        
        // Simulate success/failure
        if (empty($nama) || empty($kode)) {
            return redirect()->to('/dokumen/config-kategori')->with('error', 'Nama dan kode kategori harus diisi.');
        }
        
        return redirect()->to('/dokumen/config-kategori')->with('success', 'Kategori "' . $nama . '" berhasil ditambahkan.');
    }

    // Edit kategori dokumen
    public function editKategori()
    {
        $id = $this->request->getPost('id');
        $nama = $this->request->getPost('nama');
        $kode = $this->request->getPost('kode');
        $use_predefined = $this->request->getPost('use_predefined_codes') ? true : false;

        $data = [
            'id' => $id,
            'nama' => $nama,
            'kode' => $kode,
            'use_predefined_codes' => $use_predefined,
            'updated_at' => date('Y-m-d H:i:s')
        ];

        log_message('info', 'EDIT KATEGORI ID ' . $id . ': ' . json_encode($data));
        
        // Simulate validation
        if (empty($nama) || empty($kode)) {
            return redirect()->to('/dokumen/config-kategori')->with('error', 'Nama dan kode kategori harus diisi.');
        }
        
        // Check if ID exists (dummy check)
        $kategori = array_filter($this->kategoriDokumen, fn($item) => $item['id'] == $id);
        if (empty($kategori)) {
            return redirect()->to('/dokumen/config-kategori')->with('error', 'Kategori tidak ditemukan.');
        }
        
        return redirect()->to('/dokumen/config-kategori')->with('success', 'Kategori "' . $nama . '" berhasil diupdate.');
    }

    // Hapus kategori dokumen
    public function deleteKategori()
    {
        $id = $this->request->getPost('id');
        
        log_message('info', 'DELETE KATEGORI: ID ' . $id);
        
        // Check if ID exists (dummy check)
        $kategori = array_filter($this->kategoriDokumen, fn($item) => $item['id'] == $id);
        if (empty($kategori)) {
            return redirect()->to('/dokumen/config-kategori')->with('error', 'Kategori tidak ditemukan.');
        }
        
        $kategori = reset($kategori);
        return redirect()->to('/dokumen/config-kategori')->with('success', 'Kategori "' . $kategori['nama'] . '" berhasil dihapus.');
    }

    // ==================== CRUD KODE DOKUMEN ====================

    // Tambah kode dokumen
    public function addKode()
    {
        $jenis = $this->request->getPost('jenis');
        $kode = $this->request->getPost('kode');
        $nama = $this->request->getPost('nama');

        $data = [
            'id' => rand(100, 999), // Random ID untuk dummy
            'jenis' => $jenis,
            'kode' => $kode,
            'nama' => $nama,
            'created_at' => date('Y-m-d H:i:s')
        ];

        log_message('info', 'ADD KODE: ' . json_encode($data));
        
        // Simulate validation
        if (empty($jenis) || empty($kode) || empty($nama)) {
            return redirect()->to('/dokumen/config-kategori')->with('error', 'Semua field harus diisi.');
        }
        
        // Check if jenis exists
        $kategori = array_filter($this->kategoriDokumen, fn($item) => $item['kode'] === $jenis);
        if (empty($kategori)) {
            return redirect()->to('/dokumen/config-kategori')->with('error', 'Jenis dokumen tidak ditemukan.');
        }
        
        return redirect()->to('/dokumen/config-kategori')->with('success', 'Kode dokumen "' . $kode . '" berhasil ditambahkan.');
    }

    // Edit kode dokumen
    public function editKode()
    {
        $id = $this->request->getPost('id');
        $jenis = $this->request->getPost('jenis');
        $kode = $this->request->getPost('kode');
        $nama = $this->request->getPost('nama');

        $data = [
            'id' => $id,
            'jenis' => $jenis,
            'kode' => $kode,
            'nama' => $nama,
            'updated_at' => date('Y-m-d H:i:s')
        ];

        log_message('info', 'EDIT KODE ID ' . $id . ': ' . json_encode($data));
        
        // Simulate validation
        if (empty($jenis) || empty($kode) || empty($nama)) {
            return redirect()->to('/dokumen/config-kategori')->with('error', 'Semua field harus diisi.');
        }
        
        // Check if jenis exists
        $kategori = array_filter($this->kategoriDokumen, fn($item) => $item['kode'] === $jenis);
        if (empty($kategori)) {
            return redirect()->to('/dokumen/config-kategori')->with('error', 'Jenis dokumen tidak ditemukan.');
        }
        
        // Dummy check if kode exists
        $found = false;
        foreach ($this->kodeDokumen as $jenisKode => $kodes) {
            foreach ($kodes as $item) {
                if ($item['id'] == $id) {
                    $found = true;
                    break 2;
                }
            }
        }
        
        if (!$found) {
            return redirect()->to('/dokumen/config-kategori')->with('error', 'Kode dokumen tidak ditemukan.');
        }
        
        return redirect()->to('/dokumen/config-kategori')->with('success', 'Kode dokumen "' . $kode . '" berhasil diupdate.');
    }

    // Hapus kode dokumen
    public function deleteKode()
    {
        $id = $this->request->getPost('id');
        
        log_message('info', 'DELETE KODE: ID ' . $id);
        
        // Dummy check if kode exists
        $found = false;
        $kodeToDelete = null;
        foreach ($this->kodeDokumen as $jenisKode => $kodes) {
            foreach ($kodes as $item) {
                if ($item['id'] == $id) {
                    $found = true;
                    $kodeToDelete = $item;
                    break 2;
                }
            }
        }
        
        if (!$found) {
            return redirect()->to('/dokumen/config-kategori')->with('error', 'Kode dokumen tidak ditemukan.');
        }
        
        return redirect()->to('/dokumen/config-kategori')->with('success', 'Kode dokumen "' . $kodeToDelete['kode'] . '" berhasil dihapus.');
    }

    // ==================== EXISTING METHODS ====================

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