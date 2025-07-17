<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\DocumentCodeModel;
use App\Models\DocumentTypeModel;
use App\Models\DocumentModel;
use App\Models\UnitModel;
use App\Models\UnitParentModel;
use App\Models\DocumentApprovalModel;
use FPDF;
require_once ROOTPATH . 'vendor/autoload.php';

class KelolaDokumen extends BaseController
{
    protected $kategoriDokumen = [];
    protected $kodeDokumen = [];

    public function __construct()
    {
        $this->documentTypeModel = new DocumentTypeModel();
        $this->kodeDokumenModel = new DocumentCodeModel();
        $this->documentModel = new DocumentModel();
        $this->documentApprovalModel = new DocumentApprovalModel();
       
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
        $unitId = session()->get('unit_id');
        $unitModel = new UnitModel();

        $unitData = $unitModel
            ->select('unit.*, unit_parent.name as parent_name')
            ->join('unit_parent', 'unit_parent.id = unit.parent_id', 'left')
            ->where('unit.id', $unitId)
            ->first();
        $data['kategori_dokumen'] = $this->kategoriDokumen;
        $kodeDokumen = $this->kodeDokumenModel
            ->select('kode_dokumen.*, document_type.name as jenis_nama')
            ->join('document_type', 'document_type.id = kode_dokumen.document_type_id')
            ->where('kode_dokumen.status', 1)
            ->orderBy('document_type.name', 'ASC')
            ->orderBy('kode_dokumen.kode', 'ASC')
            ->findAll();

        $grouped = [];
        foreach ($kodeDokumen as $item) {
            $grouped[$item['jenis_nama']][] = $item;
        }
        $data['kode_dokumen'] = $grouped;
        $data['unit'] = $unitData;
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
            ->select('document.*, 
                    dt.name AS jenis_dokumen, 
                    unit.name AS unit_name, 
                    unit_parent.name AS parent_name,
                    kd.kode AS kode_dokumen_kode,
                    kd.nama AS kode_dokumen_nama')
            ->join('document_type dt', 'dt.id = document.type', 'left')
            ->join('unit', 'unit.id = document.unit_id', 'left')
            ->join('unit_parent', 'unit_parent.id = unit.parent_id', 'left')
            ->join('kode_dokumen kd', 'kd.id = document.kode_dokumen_id', 'left')
            
            ->where('document.status', 0)
            ->groupBy('document.id')
            ->findAll();

        $data['kategori_dokumen'] = $this->kategoriDokumen;
        $data['documents'] = $documents;

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
        $existing = $this->documentTypeModel
            ->groupStart()
                ->where('UPPER(name)', $nama)
                ->orWhere('UPPER(kode)', $kode)
            ->groupEnd()
            ->where('status', 1)
            ->first();

        if ($existing) {
            return redirect()->to('/kelola-dokumen/configJenisDokumen')->with('error', 'Nama atau kode kategori sudah ada.');
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
        $existing = $this->documentTypeModel
            ->groupStart()
                ->where('UPPER(name)', $nama)
                ->orWhere('UPPER(kode)', $kode)
            ->groupEnd()
            ->where('id !=', $id)
            ->where('status', 1)
            ->first();

        if ($existing) {
            return redirect()->to('/kelola-dokumen/configJenisDokumen')->with('error', 'Nama atau kode kategori sudah digunakan oleh kategori lain.');
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
        $existingKode = $this->kodeDokumenModel
            ->where('kode', $kode)
            ->where('document_type_id', $kategori['id'])
            ->first();

        if ($existingKode) {
            return redirect()->to('/kelola-dokumen/configJenisDokumen')->with('error', 'Kode dokumen sudah ada.');
        }
        $existingNama = $this->kodeDokumenModel
            ->where('nama', $nama)
            ->where('document_type_id', $kategori['id'])
            ->first();

        if ($existingNama) {
            return redirect()->to('/kelola-dokumen/configJenisDokumen')->with('error', 'Nama dokumen sudah ada dalam jenis dokumen ini.');
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
        $kode = strtoupper($this->request->getPost('kode'));
        $nama = $this->request->getPost('nama');
        $existing = $this->kodeDokumenModel->find($id);
        if (!$existing) {
            return redirect()->back()->with('error', 'Data kode dokumen tidak ditemukan.');
        }
        $dupe = $this->kodeDokumenModel
            ->where('nama', $nama)
            ->where('document_type_id', $existing['document_type_id'])
            ->where('id !=', $id)
            ->first();

        if ($dupe) {
            return redirect()->back()->with('error', 'Nama dokumen sudah ada dalam jenis dokumen ini.');
        }

        $this->kodeDokumenModel->update($id, [
            'kode' => $kode,
            'nama' => $nama
        ]);

        return redirect()->back()->with('success', 'Kode dokumen berhasil diperbarui.');
    }


public function delete_kode()
{
    $id = $this->request->getPost('id');
    if (!$id) {
        return redirect()->back()->with('error', 'ID tidak valid.');
    }

    // Soft delete: update status jadi 0
    $this->kodeDokumenModel->update($id, [
        'status' => 0,
        'updated_at' => date('Y-m-d H:i:s'),
    ]);

    return redirect()->back()->with('success', 'Kode dokumen berhasil dihapus.');
}


    
    public function edit()
    {
        $type = $this->request->getPost('jenis');

        $data = [
            'type' => $type,
            'unit_id' => $this->request->getPost('fakultas'),
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

    if (!$file->isValid() || $file->hasMoved()) {
        return redirect()->back()->with('error', 'Upload file gagal.');
    }

    $jenisId = $this->request->getPost('jenis');
    if (!$jenisId || $jenisId == "0" || $jenisId == "") {
        return redirect()->back()->with('error', 'Jenis dokumen belum dipilih.');
    }

    $newName = $file->getRandomName();
    $file->move(ROOTPATH . 'public/uploads', $newName);
    $unitId = $this->request->getPost('unit_id') ?? 99;
    $unitModel = new \App\Models\UnitModel();
    $unitData = $unitModel->select('parent_id')->where('id', $unitId)->first();
    $unitParentId = $unitData['parent_id'] ?? null;

    $this->documentModel->insert([
        'type'            => $jenisId,
        'kode_dokumen_id' => $this->request->getPost('kode_dokumen_id'),
        'number'          => $this->request->getPost('no-dokumen'),
        'date_published'  => $this->request->getPost('date_published'),
        'revision'        => $this->request->getPost('revisi') ?? 'Rev. 0',
        'title'           => $this->request->getPost('nama-dokumen'),
        'description'     => $this->request->getPost('keterangan'),
        'unit_id'         => $unitId,
        'unit_parent_id'  => $unitParentId ?? 0, 
        'status'          => 0,
        'createddate'     => date('Y-m-d H:i:s'),
        'createdby'       => 1,
        'filepath'        => $newName,
        'filename'        => $file->getClientName(),
    ]);

    return redirect()->back()->with('success', 'Dokumen berhasil ditambahkan.');
}

//DAFTAR-PENGAJUAN
    public function daftarPengajuan()
    {
        $documentModel = new \App\Models\DocumentModel();

        $documents = $documentModel
        ->select('document.*, 
                dt.name AS jenis_dokumen,
                parent_unit.name AS parent_name, 
                unit.name AS unit_name,
                unit.unit_parent_id, 
                kode_dokumen.kode AS kode_dokumen_kode, 
                kode_dokumen.nama AS kode_dokumen_nama')
        ->join('unit', 'document.unit_id = unit.id', 'left')
        ->join('unit AS parent_unit', 'unit.unit_parent_id = parent_unit.id', 'left')
        ->join('kode_dokumen', 'kode_dokumen.id = document.kode_dokumen_id', 'left')
        ->join('document_type dt', 'dt.id = document.type', 'left') 
        ->where('document.createdby !=', 0)
        ->findAll();


        return view('KelolaDokumen/daftar-pengajuan', [
            'documents' => $documents,
            'kategori_dokumen' => $this->kategoriDokumen,
            'title'     => 'Daftar Pengajuan Dokumen'
        ]);
    }

    public function approvepengajuan()
    {
        date_default_timezone_set('Asia/Jakarta');
        $document_id   = $this->request->getPost('document_id');
        $approved_by   = $this->request->getPost('approved_by');
        $remarks       = $this->request->getPost('remarks');
        $action        = $this->request->getPost('action'); 

        $status = $action === 'approve' ? 1 : 2;

        $data = [
            'document_id' => $document_id,
            'remark'      => $remarks,
            'status'      => $status,
            'approvedate' => date('Y-m-d H:i:s'),
            'approveby'   => $approved_by,
        ];

        $this->documentApprovalModel->insert($data);
        $this->documentModel->update($document_id, ['status' => $status]);

        return redirect()->back()->with('success', 'Dokumen berhasil diproses.');
    }

    public function generateSignedPDF()
    {
        $pdf = new FPDF();
        $pdf->AddPage();
        $pdf->SetFont('Arial', 'B', 14);
        $pdf->Cell(0, 10, 'Ini adalah isi dokumen.', 0, 1);
        $pdf->Image('assets/images/ttd/ttd.jpg', 150, 240, 40); 
        $path = 'public/uploads/signed/signed_' . time() . '.pdf';
        $pdf->Output('F', $path);

        return $this->response->download($path, null);
    }

    public function deletePengajuan()
    {
        $id = $this->request->getPost('document_id');

        $doc = $this->documentModel->find($id);
        if (!$id || !$doc) {
            return redirect()->back()->with('error', 'Dokumen tidak ditemukan.');
        }
        $this->documentModel->update($id, [
            'createdby' => 0,
        ]);

        return redirect()->back()->with('success', 'Dokumen berhasil dihapus (soft delete).');
    }


    public function updatepengajuan()
    {
        $documentModel = new DocumentModel();
        $documentId = $this->request->getPost('document_id');
        if (!$documentId) {
            return redirect()->back()->with('error', 'ID dokumen tidak ditemukan.');
        }
        $jenisId = $this->request->getPost('jenis');
        $file = $this->request->getFile('file_dokumen');
        $newName = null;
        if ($file && $file->isValid() && !$file->hasMoved()) {
            $newName = $file->getRandomName();
            $file->move(ROOTPATH . 'public/uploads', $newName);
        }

        $data = [
            'type'            => $jenisId,
            'kode_dokumen_id' => $this->request->getPost('kode_dokumen'),
            'number'          => $this->request->getPost('nomor'),
            'revision'        => $this->request->getPost('revisi') ?? 'Rev. 0',
            'title'           => $this->request->getPost('nama'),
            'description'     => $this->request->getPost('keterangan'),
            'status'          => 0,
        ];

        if ($newName) {
            $data['filepath'] = $newName;
            $data['filename'] = $file->getClientName();
        }
        $documentModel->update($documentId, $data);

        return redirect()->back()->with('success', 'Dokumen berhasil diperbarui.');
    }
    public function persetujuan()
    {
        $documents = $this->documentModel
            ->select('document.*, 
                    dt.name AS jenis_dokumen,
                    unit.name AS unit_name,
                    unit_parent.name AS parent_name,
                    kd.kode AS kode_dokumen_kode,
                    kd.nama AS kode_dokumen_nama,
                    da.remark,
                    da.approveby,
                    da.approvedate')
            ->join('document_type dt', 'dt.id = document.type', 'left')
            ->join('unit', 'unit.id = document.unit_id', 'left')
            ->join('unit_parent', 'unit_parent.id = unit.parent_id', 'left')
            ->join('kode_dokumen kd', 'kd.id = document.kode_dokumen_id', 'left')
            ->join('document_approval da', 'da.document_id = document.id', 'left')
            ->where('document.status', 1)
            ->orderBy('document.updated_at', 'DESC')
            ->findAll();

        dd($documents); 
    }
}


