<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\DocumentCodeModel;
use App\Models\DocumentTypeModel;
use App\Models\DocumentModel;

require_once ROOTPATH . 'vendor/autoload.php';

use FPDF;


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
            ->select('document.*, dt.name AS jenis_dokumen, unit.name AS unit_name, unit_parent.name AS parent_name')
            ->join('document_type dt', 'dt.id = document.type', 'left')
            ->join('unit', 'unit.id = document.unit_id', 'left')
            ->join('unit_parent', 'unit_parent.id = unit.parent_id', 'left')
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
        log_message('debug', 'POSTED JENIS DOKUMEN: ' . $jenisId);

        if (!$jenisId || $jenisId == "0" || $jenisId == "") {
            return redirect()->back()->with('error', 'Jenis dokumen belum dipilih.');
        }

        $newName = $file->getRandomName();
        $file->move(ROOTPATH . 'public/uploads', $newName);

        $this->documentModel->insert([
            'type'           => $jenisId,
            'kode_dokumen_id'=> $this->request->getPost('kode_dokumen_id'), // <- tambahkan
            'number'         => $this->request->getPost('no-dokumen'),
            'date_published' => $this->request->getPost('date_published'),
            'revision'       => $this->request->getPost('revisi') ?? 'Rev. 0',
            'title'          => $this->request->getPost('nama-dokumen'),
            'description'    => $this->request->getPost('keterangan'),
            'unit_id'        => $this->request->getPost('unit_id') ?? 99,
            'status'         => 0,
            'createddate'    => date('Y-m-d H:i:s'),
            'createdby'      => 1,
            'filepath'       => $newName,
            'filename'       => $file->getClientName(), // <- tambahkan
        ]);

        return redirect()->back()->with('success', 'Dokumen berhasil ditambahkan.');

    }


//DAFTAR-PENGAJUAN
public function daftarPengajuan()
{
    $documentModel = new \App\Models\DocumentModel();

    $documents = $documentModel
        ->select('document.*, parent_unit.name AS parent_name, unit.parent_id')
        ->join('unit', 'document.unit_id = unit.id', 'left')
        ->join('unit AS parent_unit', 'unit.unit_parent_id = parent_unit.id', 'left')
        ->where('document.createdby !=', 0) // ← tambahkan baris ini
        ->findAll();

    return view('KelolaDokumen/daftar-pengajuan', [
        'documents' => $documents,
        'title'     => 'Daftar Pengajuan Dokumen'
    ]);
}

public function approvePengajuan()
{
    $id = $this->request->getPost('document_id');
    $action = $this->request->getPost('action');
    $approvedBy = $this->request->getPost('approved_by');
    $approvalDate = $this->request->getPost('approval_date');
    $remarks = $this->request->getPost('remarks');

    // Validasi dasar
    if (!$id || !$action || !in_array($action, ['approve', 'disapprove'])) {
        return redirect()->back()->with('error', 'Data tidak valid.');
    }

    $status = ($action === 'approve') ? 1 : 2; // 1 = approve, 2 = disapprove

    $approvalModel = new \App\Models\DocumentApprovalModel();

    $approvalModel->save([
        'document_id' => $id,
        'remark'      => $remarks,
        'status'      => $status,
        'approvedate' => $approvalDate,
        'approveby'   => $approvedBy
    ]);

    $message = $status === 1 ? 'Dokumen berhasil disetujui.' : 'Dokumen tidak disetujui.';
    return redirect()->back()->with('success', $message);
}

public function generateSignedPDF()
{
    $pdf = new FPDF();
    $pdf->AddPage();

    // Tulis isi dokumen (contoh aja)
    $pdf->SetFont('Arial', 'B', 14);
    $pdf->Cell(0, 10, 'Ini adalah isi dokumen.', 0, 1);

    // Tambah gambar tanda tangan (atur X, Y, Width)
    $pdf->Image('assets/images/ttd/ttd.jpg', 150, 240, 40); // posisi di pojok kanan bawah

    // Simpan ke folder uploads/signed
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

    // Soft delete: set createdby = 0
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

    // Upload file jika ada file baru
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

    // Jangan update unit_id dan unit_parent_id (dibiarkan seperti sebelumnya)
    $documentModel->update($documentId, $data);

    return redirect()->back()->with('success', 'Dokumen berhasil diperbarui.');
}





}


