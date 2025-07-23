<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\DocumentCodeModel;
use App\Models\DocumentTypeModel;
use App\Models\DocumentModel;
use App\Models\UnitModel;
use App\Models\UnitParentModel;
use App\Models\DocumentApprovalModel;
use CodeIgniter\Files\File; 
use FPDF;
require_once ROOTPATH . 'vendor/autoload.php';

class KelolaDokumen extends BaseController
{
    protected $kategoriDokumen = [];
    protected $kodeDokumen = [];
    protected $documentModel;
    protected $documentRevisionModel;
    protected $db;
    protected $helpers = ['url', 'form'];
    protected $session;


    public function initController(\CodeIgniter\HTTP\RequestInterface $request, \CodeIgniter\HTTP\ResponseInterface $response, \Psr\Log\LoggerInterface $logger)
    {
        parent::initController($request, $response, $logger);
        $this->session = \Config\Services::session();
    }

    public function __construct()
    {
        $this->documentTypeModel = new DocumentTypeModel();
        $this->kodeDokumenModel = new DocumentCodeModel();
        $this->documentModel = new DocumentModel();
        $this->documentApprovalModel = new DocumentApprovalModel();
        $this->documentCodeModel = new \App\Models\DocumentCodeModel(); 
        $this->documentRevisionModel = new \App\Models\DocumentRevisionModel();
        $this->db = \Config\Database::connect();
       
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
    $documentModel = new \App\Models\DocumentModel();
    $unitParentModel = new \App\Models\UnitParentModel();

    $documents = $this->documentModel
        ->select('document.*, 
                  dt.name AS jenis_dokumen, 
                  unit.name AS unit_name, 
                  unit_parent.name AS parent_name,
                  unit.parent_id AS unit_parent_id,
                  kd.kode AS kode_dokumen_kode,
                  kd.nama AS kode_dokumen_nama,
                  dr.filename AS filename,
                  dr.filepath AS filepath')
        ->join('document_type dt', 'dt.id = document.type', 'left')
        ->join('unit', 'unit.id = document.unit_id', 'left')
        ->join('unit_parent', 'unit_parent.id = unit.parent_id', 'left')
        ->join('kode_dokumen kd', 'kd.id = document.kode_dokumen_id', 'left')
        ->join('document_revision dr', 'dr.document_id = document.id', 'left')
        ->where('document.createdby !=', 0)
        ->where('document.status', 0) // Hanya status 0 (menunggu)
        ->groupBy('document.id')
        ->findAll();

    $jenis_dokumen = $this->documentTypeModel->where('status', 1)->findAll();
    $kategori_dokumen = $this->kategoriDokumen;
    $kode_nama_dokumen = $this->documentCodeModel->where('status', 1)->findAll();
    $fakultas_list = $unitParentModel
        ->where('status', 1)
        ->orderBy('name', 'ASC')
        ->findAll();

    $data = [
        'documents' => $documents,
        'jenis_dokumen' => $jenis_dokumen,
        'kategori_dokumen' => $kategori_dokumen,
        'kode_nama_dokumen' => $kode_nama_dokumen,
        'fakultas_list' => $fakultas_list,
        'title' => 'Daftar Pengajuan Dokumen'
    ];

    log_message('debug', 'Documents retrieved: ' . json_encode($documents));

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

public function edit($id)
{
    $document = $this->documentModel->find($id);
    $jenis_dokumen = $this->documentTypeModel->where('status', 1)->findAll();
    $kode_nama_dokumen = $this->documentCodeModel->where('status', 1)->findAll();

    return view('dokumen/edit', [
        'document' => $document,
        'jenis_dokumen' => $jenis_dokumen,
        'kode_nama_dokumen' => $kode_nama_dokumen,
    ]);
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

    $uploadPath = ROOTPATH . '../storage/uploads';
    if (!is_dir($uploadPath)) {
        mkdir($uploadPath, 0755, true);
    }

    $newName = $file->getRandomName();
    $file->move($uploadPath, $newName);

    $unitId = $this->request->getPost('unit_id') ?? 99;
    $unitModel = new \App\Models\UnitModel();
    $unitData = $unitModel->select('parent_id')->where('id', $unitId)->first();
    $unitParentId = $unitData['parent_id'] ?? null;

    $this->documentModel->db->transStart();

    $result = $this->documentModel->db->query('SHOW TABLE STATUS LIKE "document"')->getRow();
    $nextId = $result->Auto_increment;

    $this->documentModel->insert([
        'type' => $jenisId,
        'kode_dokumen_id' => $this->request->getPost('kode_dokumen_id'),
        'number' => $this->request->getPost('no-dokumen'),
        'date_published' => $this->request->getPost('date_published'),
        'revision' => $this->request->getPost('revisi') ?? 'Rev. 0',
        'title' => $this->request->getPost('nama-dokumen'),
        'description' => $this->request->getPost('keterangan'),
        'unit_id' => $unitId,
        'status' => 0,
        'createddate' => date('Y-m-d H:i:s'),
        'createdby' => session('user_id'),
        'original_document_id' => $nextId, // Set original_document_id to the new ID
    ]);

    $documentId = $this->documentModel->getInsertID();

    $this->documentRevisionModel->insert([
        'document_id' => $documentId,
        'revision' => $this->request->getPost('revisi') ?? 'Rev. 0',
        'filename' => $file->getClientName(),
        'filepath' => 'storage/uploads/' . $newName,
        'filesize' => $file->getSize(),
        'remark' => $this->request->getPost('keterangan'),
        'createddate' => date('Y-m-d H:i:s'),
        'createdby' => session('user_id'),
    ]);

    $this->documentModel->db->transComplete();

    if ($this->documentModel->db->transStatus() === false) {
        return redirect()->back()->with('error', 'Gagal menyimpan dokumen.');
    }

    return redirect()->to('/tambah-dokumen')->with('success', 'Dokumen berhasil ditambahkan.');
}




public function approvepengajuan()
{
    date_default_timezone_set('Asia/Jakarta');
    $document_id   = $this->request->getPost('document_id');
    $approved_by   = $this->request->getPost('approved_by');
    $remarks       = $this->request->getPost('remarks');
    $action        = $this->request->getPost('action'); 

    $status = $action === 'approve' ? 2 : 1;

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
            'status' => 3,
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

    $jenisId = $this->request->getPost('type');
    $kodeDokumenId = $this->request->getPost('kode_dokumen');
    $nomor = $this->request->getPost('nomor');
    $revisi = $this->request->getPost('revisi') ?? 'Rev. 0';
    $nama = $this->request->getPost('nama');
    $keterangan = $this->request->getPost('keterangan');
    $file = $this->request->getFile('file_dokumen');

    if (empty($jenisId) || empty($kodeDokumenId) || empty($nomor) || empty($nama)) {
        return redirect()->back()->with('error', 'Semua field wajib harus diisi.');
    }

    $documentType = $this->documentTypeModel->where('id', $jenisId)->where('status', 1)->first();
    if (!$documentType) {
        return redirect()->back()->with('error', 'Jenis dokumen tidak valid.');
    }

    $kodeDokumen = $this->kodeDokumenModel->where('id', $kodeDokumenId)->where('status', 1)->first();
    if (!$kodeDokumen) {
        return redirect()->back()->with('error', 'Kode dokumen tidak valid.');
    }

    $originalDocument = $documentModel->find($documentId);
    if (!$originalDocument) {
        return redirect()->back()->with('error', 'Dokumen tidak ditemukan di database.');
    }
    $unitId = $originalDocument['unit_id'] ?? session()->get('unit_id') ?? 99;
    $originalDocumentId = $originalDocument['original_document_id'] ?? $documentId;

    $data = [
        'type' => $jenisId,
        'kode_dokumen_id' => $kodeDokumenId,
        'number' => $nomor,
        'revision' => $revisi,
        'title' => $nama,
        'description' => $keterangan,
        'unit_id' => $unitId,
        'status' => 0,
        'createddate' => date('Y-m-d H:i:s'),
        'createdby' => session('user_id'),
        'original_document_id' => $originalDocumentId, // Use the original_document_id from the original document
    ];

    try {
        $documentModel->insert($data);
        $newDocumentId = $documentModel->getInsertID();

        if ($file && $file->isValid() && !$file->hasMoved()) {
            $uploadPath = ROOTPATH . '../storage/uploads';
            if (!is_dir($uploadPath)) {
                mkdir($uploadPath, 0755, true);
            }

            $newName = $file->getRandomName();
            $file->move($uploadPath, $newName);

            $this->documentRevisionModel->insert([
                'document_id' => $newDocumentId,
                'revision' => $revisi,
                'filename' => $file->getClientName(),
                'filepath' => 'storage/uploads/' . $newName,
                'filesize' => $file->getSize(),
                'remark' => $keterangan,
                'createddate' => date('Y-m-d H:i:s'),
                'createdby' => session('user_id'),
            ]);
        } else {
            $oldRevision = $this->documentRevisionModel
                ->where('document_id', $documentId)
                ->orderBy('id', 'DESC')
                ->first();

            if ($oldRevision) {
                $this->documentRevisionModel->insert([
                    'document_id' => $newDocumentId,
                    'revision' => $revisi,
                    'filename' => $oldRevision['filename'],
                    'filepath' => $oldRevision['filepath'],
                    'filesize' => $oldRevision['filesize'],
                    'remark' => $keterangan,
                    'createddate' => date('Y-m-d H:i:s'),
                    'createdby' => session('user_id'),
                ]);
            }
        }

        $documentModel->update($documentId, [
            'status' => 3,
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        return redirect()->to('/kelola-dokumen/pengajuan')->with('success', 'Dokumen baru berhasil ditambahkan.');
    } catch (\Exception $e) {
        log_message('error', 'Error creating new document: ' . $e->getMessage());
        return redirect()->back()->with('error', 'Gagal menambahkan dokumen baru: ' . $e->getMessage());
    }
}



public function get_history($document_id)
{
    log_message('debug', 'get_history called with document_id: ' . $document_id);
    if (!$this->session->get('user_id')) {
        log_message('debug', 'Unauthorized access to get_history');
        return $this->response->setJSON([
            'success' => false,
            'message' => 'Unauthorized'
        ], 401);
    }

    $document = $this->documentModel
        ->select('document.*, dt.name AS jenis_dokumen, kd.kode AS kode_dokumen_kode, kd.nama AS kode_dokumen_nama')
        ->join('document_type dt', 'dt.id = document.type', 'left')
        ->join('kode_dokumen kd', 'kd.id = document.kode_dokumen_id', 'left')
        ->where('document.id', $document_id)
        ->where('document.createdby !=', 0)
        ->first();

    if (!$document) {
        log_message('debug', 'Document not found for id: ' . $document_id);
        return $this->response->setJSON([
            'success' => false,
            'message' => 'Dokumen tidak ditemukan'
        ], 404);
    }

    $originalDocumentId = $document['original_document_id'] ?? $document_id;
    log_message('debug', 'Original Document ID: ' . $originalDocumentId);

    $historyDocuments = $this->documentModel
        ->select('document.*, dt.name AS jenis_dokumen, kd.kode AS kode_dokumen_kode, kd.nama AS kode_dokumen_nama')
        ->join('document_type dt', 'dt.id = document.type', 'left')
        ->join('kode_dokumen kd', 'kd.id = document.kode_dokumen_id', 'left')
        ->where('document.original_document_id', $originalDocumentId)
        ->where('document.createdby !=', 0)
        ->orderBy('document.createddate', 'DESC')
        ->findAll();

    log_message('debug', 'History documents count: ' . count($historyDocuments));

    $history = [];
    foreach ($historyDocuments as $doc) {
        $revisions = $this->documentRevisionModel
            ->select('id, document_id, revision, filename, filepath, filesize, remark, createddate, createdby')
            ->where('document_id', $doc['id'])
            ->orderBy('createddate', 'DESC')
            ->findAll();
        foreach ($revisions as $revision) {
            $history[] = [
                'id' => $revision['id'],
                'document_id' => $revision['document_id'],
                'revision' => $revision['revision'] ?? 'Rev. 0',
                'filename' => $revision['filename'],
                'filepath' => $revision['filepath'],
                'filesize' => $revision['filesize'],
                'remark' => $revision['remark'],
                'updated_at' => $revision['createddate'],
                'updated_by' => $revision['createdby'],
                'document_title' => $doc['title'],
                'document_number' => $doc['number'],
                'status' => $doc['status'],
            ];
        }
    }

    log_message('debug', 'Formatted history count: ' . count($history));
    return $this->response->setJSON([
        'success' => true,
        'data' => [
            'document' => [
                'id' => $document['id'],
                'title' => $document['title'],
                'jenis_dokumen' => $document['jenis_dokumen'],
                'kode_dokumen_kode' => $document['kode_dokumen_kode'],
                'kode_dokumen_nama' => $document['kode_dokumen_nama'],
            ],
            'history' => $history,
        ],
    ]);
}















public function serveFile($documentId)
{
    $userId = session('user_id');
    if (!$userId) {
        throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Anda harus login untuk mengakses file.');
    }

    $revision = $this->documentRevisionModel
        ->where('document_id', $documentId)
        ->orderBy('id', 'DESC')
        ->first();

    if (!$revision || empty($revision['filepath'])) {
        throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('File tidak ditemukan.');
    }

    $document = $this->documentModel->find($documentId);
    $allowedRoles = ['admin', 'superadmin'];
    $userRole = session('role');
    if (!in_array($userRole, $allowedRoles) && $document['createdby'] != $userId) {
        throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Anda tidak memiliki akses ke file ini.');
    }

    $filePath = ROOTPATH . '../' . $revision['filepath'];
    if (!file_exists($filePath)) {
        throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('File tidak ditemukan di server.');
    }

    $file = new File($filePath);
    $mimeType = $file->getMimeType() ?: 'application/octet-stream';

    $action = $this->request->getGet('action') ?? 'view';
    $disposition = ($action === 'download') ? 'attachment' : 'inline';

    return $this->response
        ->setHeader('Content-Type', $mimeType)
        ->setHeader('Content-Disposition', $disposition . '; filename="' . $revision['filename'] . '"')
        ->setBody(file_get_contents($filePath));
}

}