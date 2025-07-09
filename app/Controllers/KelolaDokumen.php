<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\DocumentCodeModel;
use App\Models\DocumentTypeModel;
use App\Models\DocumentModel;

class KelolaDokumen extends BaseController
{
    protected $kategoriDokumen = [];
    protected $kodeDokumen = [];

public function __construct()
{
    $this->documentTypeModel = new DocumentTypeModel();
    $this->kodeDokumenModel = new DocumentCodeModel();
    $this->documentModel = new DocumentModel();

    // Ambil dan bentuk ulang kategori dokumen
    $kategori = $this->documentTypeModel->where('status', 1)->findAll();
    $this->kategoriDokumen = array_map(function ($item) {
        return [
            'id' => $item['id'],
            'nama' => $item['name'],
            'kode' => strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $item['name'])),
            'use_predefined_codes' => str_contains($item['description'] ?? '', '[predefined]'),
        ];
    }, $kategori);

    // Ambil dan kelompokkan kode dokumen berdasarkan jenis
    $kodeList = $this->kodeDokumenModel
        ->select('kode_dokumen.*, document_type.name as jenis_nama')
        ->join('document_type', 'document_type.id = kode_dokumen.document_type_id')
        ->where('kode_dokumen.status', 1)
        ->where('document_type.status', 1)
        ->like('document_type.description', '[predefined]')
        ->findAll();

    $grouped = [];
    foreach ($kodeList as $item) {
        $key = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $item['jenis_nama']));
        $grouped[$key][] = [
            'id' => $item['id'],
            'kode' => $item['kode'],
            'nama' => $item['nama'],
        ];
    }

    $this->kodeDokumen = $grouped;
}


    public function add(): string
    {
        $data['kategori_dokumen'] = $this->kategoriDokumen;

        $kodeDokumen = $this->kodeDokumenModel
            ->select('kode_dokumen.*, document_type.name as jenis_nama')
            ->join('document_type', 'document_type.id = kode_dokumen.document_type_id')
            ->where('kode_dokumen.status', 1)
            ->orderBy('document_type.name', 'ASC')
            ->orderBy('kode_dokumen.kode', 'ASC')
            ->findAll();

        $data['kode_dokumen'] = [];
        foreach ($kodeDokumen as $item) {
            $data['kode_dokumen'][$item['jenis_nama']][] = $item;
        }

        return view('KelolaDokumen/dokumen-create', $data);
    }

public function getKodeDokumen()
{
    $jenis = $this->request->getPost('jenis'); // ini bentuk 'internal', 'eksternal', dst.

    // Samakan nama jenis dengan format kode
    $matchedType = array_filter($this->kategoriDokumen, fn($item) => $item['kode'] === $jenis);
    $matchedType = reset($matchedType);

    if (!$matchedType || !$matchedType['use_predefined_codes']) {
        return $this->response->setJSON([]);
    }

    $documentTypeId = $matchedType['id'];

    $results = $this->kodeDokumenModel
        ->where('document_type_id', $documentTypeId)
        ->where('status', 1)
        ->findAll();

    return $this->response->setJSON($results);
}



    public function getKodeByJenis()
    {
        $jenis = $this->request->getPost('jenis');

        $documentType = $this->documentTypeModel
            ->where('name', $jenis)
            ->where('status', 1)
            ->first();

        if (!$documentType) {
            return $this->response->setJSON(['kode' => '']);
        }

        $kode = $this->kodeDokumenModel
            ->where('document_type_id', $documentType['id'])
            ->where('status', 1)
            ->orderBy('id', 'ASC')
            ->first();

        return $this->response->setJSON(['kode' => $kode['kode'] ?? '']);
    }

public function pengajuan()
{
    $documents = $this->documentModel
        ->select('document.*, dt.name AS jenis_dokumen, unit.name AS unit_name, unit_parent.name AS parent_name')
        ->join('document_type dt', 'dt.id = document.type', 'left')  // PAKAI ALIAS "dt"
        ->join('unit', 'unit.id = document.unit_id', 'left')
        ->join('unit_parent', 'unit_parent.id = unit.parent_id', 'left')
        ->where('document.status', 0)
        ->groupBy('document.id')
        ->findAll();

    // Use the mapped kategoriDokumen
    $data['kategori_dokumen'] = $this->kategoriDokumen;
    $data['documents'] = $documents;

    

    return view('KelolaDokumen/daftar-pengajuan', $data);
}




    public function configJenisDokumen()
{
    // Ambil jenis dokumen aktif
    $kategori = $this->documentTypeModel->where('status', 1)->findAll();

    // Map kategori
    $data['kategori_dokumen'] = array_map(function ($item) {
        return [
            'id' => $item['id'],
            'nama' => $item['name'],
            'kode' => $item['kode'],
            'use_predefined_codes' => str_contains($item['description'] ?? '', '[predefined]'),
        ];
    }, $kategori);

    // Ambil hanya kode_dokumen yang terkait jenis dokumen predefined
    $kodeList = $this->kodeDokumenModel
        ->select('kode_dokumen.*, document_type.name as jenis_nama')
        ->join('document_type', 'document_type.id = kode_dokumen.document_type_id')
        ->where('kode_dokumen.status', 1)
        ->where('document_type.status', 1)
        ->where('document_type.description', '[predefined]') // << ini yang penting
        ->findAll();

    // Kelompokkan berdasarkan jenis
    $grouped = [];
    foreach ($kodeList as $item) {
        $key = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $item['jenis_nama']));
        $grouped[$key][] = [
            'id' => $item['id'],
            'kode' => $item['kode'],
            'nama' => $item['nama']
        ];
    }

    $data['kode_dokumen'] = $grouped;

    return view('KelolaDokumen/config-jenis-dokumen', $data);
}


    public function addKategori()
    {
        $nama = strtoupper($this->request->getPost('nama'));
        $kode = strtoupper($this->request->getPost('kode'));
        $use_predefined = $this->request->getPost('use_predefined_codes') ? true : false;

        if (empty($nama) || empty($kode)) {
            return redirect()->to('/kelola-dokumen/configJenisDokumen')->with('error', 'Nama dan kode kategori harus diisi.');
        }

        $description = $use_predefined ? '[predefined]' : null;

        $this->documentTypeModel->save([
            'name' => $nama,
            'kode' => $kode,
            'description' => $description,
            'status' => 1,
        ]);

        return redirect()->to('/kelola-dokumen/configJenisDokumen')->with('success', 'Kategori berhasil ditambahkan.');
    }

    public function editKategori()
    {
        $id = $this->request->getPost('id');
        $nama = strtoupper($this->request->getPost('nama'));
        $kode = strtoupper($this->request->getPost('kode'));
        log_message('debug', json_encode($this->request->getPost()));

        $use_predefined = $this->request->getPost('use_predefined_codes') ? true : false;

        if (empty($id) || empty($nama) || empty($kode)) {
            return redirect()->to('/kelola-dokumen/configJenisDokumen')->with('error', 'Semua field harus diisi.');
        }

        $description = $use_predefined ? '[predefined]' : null;

        $this->documentTypeModel->update($id, [
            'name' => $nama,
            'kode' => $kode,
            'description' => $description,
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        return redirect()->to('/kelola-dokumen/configJenisDokumen')->with('success', 'Kategori berhasil diupdate.');
    }

    public function deleteKategori()
    {
        $id = $this->request->getPost('id');

        if (!$id) {
            return redirect()->to('/kelola-dokumen/configJenisDokumen')->with('error', 'ID tidak valid.');
        }

        $this->documentTypeModel->update($id, [
            'status' => 0,
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        return redirect()->to('/kelola-dokumen/configJenisDokumen')->with('success', 'Kategori berhasil dihapus.');
    }


        public function addKode()
        {
            $jenis = $this->request->getPost('jenis'); // <- ID dari select option
            $kode = strtoupper($this->request->getPost('kode'));
            $nama = $this->request->getPost('nama');
            
            $kategori = $this->documentTypeModel
            ->where('id', $jenis)
            ->where('status', 1)
            ->first();

        if (!$kategori) {
            return redirect()->to('/kelola-dokumen/configJenisDokumen')->with('error', 'Jenis dokumen tidak ditemukan.');
        }


            // Optional: Cek duplikasi
            $existing = $this->kodeDokumenModel
                ->where('kode', $kode)
                ->where('document_type_id', $kategori['id'])
                ->first();

            if ($existing) {
                return redirect()->to('/kelola-dokumen/configJenisDokumen')->with('error', 'Kode dokumen sudah ada.');
            }

            $this->kodeDokumenModel->save([
                'document_type_id' => $kategori['id'],
                'kode' => $kode,
                'nama' => $nama,
                'status' => 1,
            ]);

            return redirect()->to('/kelola-dokumen/configJenisDokumen')->with('success', 'Kode dokumen berhasil ditambahkan.');
        }


    public function editKode()
    {
        $id = $this->request->getPost('id');
        $data = [
            'kode' => strtoupper($this->request->getPost('kode')),
            'nama' => $this->request->getPost('nama')
        ];

        $this->kodeDokumenModel->update($id, $data);

        return redirect()->back()->with('success', 'Kode dokumen berhasil diperbarui.');
    }

    public function delete_kode()
    {
        $id = $this->request->getPost('id');
        if (!$id) {
            return redirect()->back()->with('error', 'ID tidak valid.');
        }

        $this->kodeDokumenModel->delete($id);
        return redirect()->back()->with('success', 'Kode dokumen berhasil dihapus.');
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
            'remark' => $this->request->getPost('remark'),
            'status' => 1,
            'approvedate' => date('Y-m-d H:i:s'),
            'approveby' => 1
        ];

        log_message('info', 'APPROVAL: ' . json_encode($data));
        return redirect()->to('/dokumen/pengajuan')->with('success', 'Dokumen berhasil di-approve.');
    }

public function edit()
{
    log_message('debug', 'FULL POST: ' . json_encode($this->request->getPost()));
     log_message('debug', 'FULL POST: ' . json_encode($this->request->getPost()));

    $type = $this->request->getPost('jenis'); // ← Cek apakah ini null/0

    $data = [
        'type' => $type,
        'unit_id' => $this->request->getPost('fakultas'),
        'title' => $this->request->getPost('nama'),
        'number' => $this->request->getPost('nomor'),
        'revision' => $this->request->getPost('revisi'),
        'description' => $this->request->getPost('keterangan'),
        'updated_at' => date('Y-m-d H:i:s'),
    ];

    // File handler
    $file = $this->request->getFile('file');
    if ($file && $file->isValid() && !$file->hasMoved()) {
        $data['filepath'] = $file->getRandomName();
        $file->move(WRITEPATH . 'uploads', $data['filepath']);
    }

    $documentModel = new \App\Models\DocumentModel();
    $documentModel->update($this->request->getPost('document_id'), $data);

    return redirect()->to('/kelola-dokumen/pengajuan')->with('success', 'Dokumen berhasil diupdate.');
}


    public function delete($id)
    {
        log_message('info', 'DELETE: Document ID ' . $id);
        return redirect()->to('/dokumen/pengajuan')->with('success', 'Dokumen berhasil dihapus.');
    }

    
public function tambah()
{
    $file = $this->request->getFile('file');

    // Cek jika file valid
    if (!$file->isValid() || $file->hasMoved()) {
        return redirect()->back()->with('error', 'Upload file gagal.');
    }

    // ✅ VALIDASI JENIS DOKUMEN
    $jenisId = $this->request->getPost('jenis');

    
    // ✅ LOG untuk debugging
    log_message('debug', 'POSTED JENIS DOKUMEN: ' . $jenisId);

    if (!$jenisId || $jenisId == "0" || $jenisId == "") {
        return redirect()->back()->with('error', 'Jenis dokumen belum dipilih.');
    }
    

    // Lanjut simpan file
    $newName = $file->getRandomName();
    $file->move(ROOTPATH . 'public/uploads', $newName);
    
    // ✅ Simpan ke database
    $documentModel = new \App\Models\DocumentModel();
    $documentModel->insert([
        'type'         => $jenisId, // ← Ini harus ID valid dari document_type
        'number'       => $this->request->getPost('no-dokumen'),
        'date_published' => date('Y-m-d'),
        'revision'     => $this->request->getPost('revisi') ?? 'Rev. 0',
        'title'        => $this->request->getPost('nama-dokumen'),
        'description'  => $this->request->getPost('keterangan'),
        'unit_id'      => $this->request->getPost('unit_id') ?? 99,
        'status'       => 0,
        'createddate'  => date('Y-m-d H:i:s'),
        'createdby'    => 1,
        'filepath'     => $newName,
    ]);

  

    return redirect()->to(base_url('/kelola-dokumen/pengajuan'))->with('success', 'Dokumen berhasil ditambahkan.');
}







}
