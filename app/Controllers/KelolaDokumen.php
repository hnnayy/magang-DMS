<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\DocumentCodeModel;
use App\Models\DocumentTypeModel;
use App\Models\DocumentModel;
use App\Models\UnitModel;
use App\Models\UnitParentModel;

class KelolaDokumen extends BaseController
{
    protected $kategoriDokumen = [];
    protected $kodeDokumen = [];
    protected $unitModel;
    protected $unitParentModel;

    public function __construct()
    {
        $this->documentTypeModel = new DocumentTypeModel();
        $this->kodeDokumenModel = new DocumentCodeModel();
        $this->documentModel = new DocumentModel();
        $this->unitModel = new UnitModel();
        $this->unitParentModel = new UnitParentModel();

        $kategori = $this->documentTypeModel->where('status', 1)->findAll();
        $this->kategoriDokumen = array_map(function ($item) {
            return [
                'id' => $item['id'],
                'nama' => $item['name'],
                'kode' => strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $item['name'])),
                'use_predefined_codes' => str_contains($item['description'] ?? '', '[predefined]'),
            ];
        }, $kategori);

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

        $data['unit_parents'] = $this->unitParentModel->where('status', 1)->findAll();
        $data['units'] = $this->unitModel->where('status', 1)->findAll();

        return view('KelolaDokumen/dokumen-create', $data);
    }

    public function getKodeDokumen()
    {
        $jenis = $this->request->getPost('jenis');

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
            ->select('document.*, dt.name AS jenis_dokumen, dc.kode AS kode_dokumen, dc.nama AS nama_kode_dokumen, unit.id AS unit_id, unit.name AS unit_name, unit_parent.id AS unit_parent_id, unit_parent.name AS parent_name')
            ->join('document_type dt', 'dt.id = document.type', 'left')
            ->join('kode_dokumen dc', 'dc.id = document.kode_dokumen_id', 'left')
            ->join('unit', 'unit.id = document.unit_id', 'left')
            ->join('unit_parent', 'unit_parent.id = unit.parent_id', 'left')
            ->where('document.status', 0)
            ->groupBy('document.id')
            ->findAll();

        $data['kategori_dokumen'] = $this->kategoriDokumen;
        $data['documents'] = $documents;
        $data['unit_parents'] = $this->unitParentModel->where('status', 1)->findAll();
        $data['units'] = $this->unitModel->where('status', 1)->findAll();

        return view('KelolaDokumen/daftar-pengajuan', $data);
    }

    public function configJenisDokumen()
    {
        $kategori = $this->documentTypeModel->where('status', 1)->findAll();

        $data['kategori_dokumen'] = array_map(function ($item) {
            return [
                'id' => $item['id'],
                'nama' => $item['name'],
                'kode' => $item['kode'],
                'use_predefined_codes' => str_contains($item['description'] ?? '', '[predefined]'),
            ];
        }, $kategori);

        $kodeList = $this->kodeDokumenModel
            ->select('kode_dokumen.*, document_type.name as jenis_nama')
            ->join('document_type', 'document_type.id = kode_dokumen.document_type_id')
            ->where('kode_dokumen.status', 1)
            ->where('document_type.status', 1)
            ->where('document_type.description', '[predefined]')
            ->findAll();

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
        $jenis = $this->request->getPost('jenis');
        $kode = strtoupper($this->request->getPost('kode'));
        $nama = $this->request->getPost('nama');

        $kategori = $this->documentTypeModel
            ->where('id', $jenis)
            ->where('status', 1)
            ->first();

        if (!$kategori) {
            return redirect()->to('/kelola-dokumen/configJenisDokumen')->with('error', 'Jenis dokumen tidak ditemukan.');
        }

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
    $type = $this->request->getPost('jenis');
    $unitParentId = $this->request->getPost('fakultas');
    $unitId = $this->request->getPost('bagian');

    $data = [
        'type' => $type,
        'unit_id' => $unitId,
        'unit_parent_id' => $unitParentId,
        'title' => $this->request->getPost('nama'),
        'number' => $this->request->getPost('nomor'),
        'revision' => $this->request->getPost('revisi'),
        'description' => $this->request->getPost('keterangan'),
        'updated_at' => date('Y-m-d H:i:s'),
    ];

    $file = $this->request->getFile('file');
    if ($file && $file->isValid() && !$file->hasMoved()) {
        $data['filepath'] = $file->getRandomName();
        $file->move(WRITEPATH . 'uploads', $data['filepath']);
    }

    $documentModel = new \App\Models\DocumentModel();
    $documentModel->update($this->request->getPost('document_id'), $data);

    // Redirect dengan pesan sukses
    return redirect()->to(base_url('daftar-pengajuan'))->with('success', 'Data berhasil disimpan');
}

    public function delete($id)
    {
        if (!$id || !is_numeric($id)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'ID dokumen tidak valid.'
            ]);
        }

        $document = $this->documentModel->find($id);
        if (!$document) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Dokumen tidak ditemukan.'
            ]);
        }

        if (!$this->request->isAJAX()) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Request tidak valid.'
            ]);
        }

        try {
            $result = $this->documentModel->update($id, [
                'status' => 9,
                'updated_at' => date('Y-m-d H:i:s'),
            ]);

            if ($result) {
                return $this->response->setJSON([
                    'success' => true,
                    'message' => 'Dokumen berhasil dihapus.'
                ]);
            } else {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Gagal menghapus dokumen.'
                ]);
            }
        } catch (Exception $e) {
            log_message('error', 'Error deleting document: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Terjadi kesalahan saat menghapus dokumen.'
            ]);
        }
    }

    public function tambah()
    {
        $file = $this->request->getFile('file');

        if (!$file->isValid() || $file->hasMoved()) {
            return redirect()->back()->with('error', 'Upload file gagal.');
        }

        $jenisId = $this->request->getPost('jenis');
        log_message('debug', 'POSTED JENIS DOKUMEN: ' . $jenisId);

        if (!$jenisId || $jenisId == "0" || $jenisId == "") {
            return redirect()->back()->with('error', 'Jenis dokumen belum dipilih.');
        }

        $newName = $file->getRandomName();
        $file->move(ROOTPATH . 'public/uploads', $newName);

        $this->documentModel->insert([
            'type'           => $jenisId,
            'kode_dokumen_id'=> $this->request->getPost('kode_dokumen_id'),
            'number'         => $this->request->getPost('no-dokumen'),
            'date_published' => $this->request->getPost('date_published'),
            'revision'       => $this->request->getPost('revisi') ?? 'Rev. 0',
            'title'          => $this->request->getPost('nama-dokumen'),
            'description'    => $this->request->getPost('keterangan'),
            'unit_id'        => $this->request->getPost('unit_id'),
            'unit_parent_id'  => $this->request->getPost('unit_id'),
            'status'         => 0,
            'createddate'    => date('Y-m-d H:i:s'),
            'createdby'      => 1,
            'filepath'       => $newName,
            'filename'       => $file->getClientName(),
        ]);

        return redirect()->back()->with('success', 'Dokumen berhasil ditambahkan.');
    }

public function daftarPengajuan()
{
    $documentModel = new \App\Models\DocumentModel();

    $documents = $documentModel
        ->select('document.*, parent_unit.name AS parent_name, unit.parent_id')
        ->join('unit', 'document.unit_id = unit.id', 'left')
        ->join('unit AS parent_unit', 'unit.unit_parent_id = parent_unit.id', 'left')
        ->findAll();

    return view('KelolaDokumen/daftar-pengajuan', [
        'documents' => $documents,
        'title'     => 'Daftar Pengajuan Dokumen'
    ]);
}




}