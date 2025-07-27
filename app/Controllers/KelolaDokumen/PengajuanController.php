<?php

namespace App\Controllers\KelolaDokumen;

use App\Controllers\BaseController;
use App\Models\DocumentCodeModel;
use App\Models\DocumentTypeModel;
use App\Models\DocumentModel;
use App\Models\UnitModel;
use App\Models\UnitParentModel;
use App\Models\DocumentApprovalModel;
use CodeIgniter\Files\File; 
require_once ROOTPATH . 'vendor/autoload.php';

class PengajuanController extends BaseController
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

    // GET document-submission-list
    public function index()
    {
        // ✅ PERBAIKAN: Handle baik GET maupun POST request
        $action = $this->request->getGet('action') ?: $this->request->getPost('action');
        
        switch ($action) {
            case 'view-file':
                return $this->handleFileView();
            case 'download-file':
                return $this->handleFileDownload();
            case 'get-status':
                return $this->handleGetStatus();
            case 'get-history':
                return $this->handleGetHistory();
            case 'get-kode-dokumen': // ✅ TAMBAHAN: Handle AJAX request
                return $this->handleGetKodeDokumen();
            default:
                return $this->showList();
        }
    }

    // POST document-submission-list/store
    public function store()
    {
        // Logic untuk create dokumen baru
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

        $data = [
            'type' => $jenisId,
            'kode_dokumen_id' => $kodeDokumenId,
            'number' => $nomor,
            'revision' => $revisi,
            'title' => $nama,
            'description' => $keterangan,
            'unit_id' => session()->get('unit_id') ?? 99,
            'status' => 0,
            'createddate' => date('Y-m-d H:i:s'),
            'createdby' => session('user_id'),
        ];

        try {
            $this->documentModel->insert($data);
            $newDocumentId = $this->documentModel->getInsertID();

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
            }

            return redirect()->to('document-submission-list')->with('success', 'Dokumen berhasil ditambahkan.');
        } catch (\Exception $e) {
            log_message('error', 'Error creating document: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal menambahkan dokumen: ' . $e->getMessage());
        }
    }

    // GET document-submission-list/edit
    public function edit()
    {
        $id = $this->request->getGet('id');
        
        if (!$id) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'ID dokumen tidak valid'
            ], 400);
        }

        $document = $this->documentModel
            ->select('document.*, dt.name AS jenis_dokumen, kd.kode AS kode_dokumen_kode, kd.nama AS kode_dokumen_nama')
            ->join('document_type dt', 'dt.id = document.type', 'left')
            ->join('kode_dokumen kd', 'kd.id = document.kode_dokumen_id', 'left')
            ->where('document.id', $id)
            ->first();

        if (!$document) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Dokumen tidak ditemukan'
            ], 404);
        }

        return $this->response->setJSON([
            'success' => true,
            'data' => $document
        ]);
    }

    // POST document-submission-list/update
    public function update()
    {
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

        $originalDocument = $this->documentModel->find($documentId);
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
            'original_document_id' => $originalDocumentId,
        ];

        try {
            $this->documentModel->insert($data);
            $newDocumentId = $this->documentModel->getInsertID();

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

            $this->documentModel->update($documentId, [
                'status' => 3,
                'updated_at' => date('Y-m-d H:i:s'),
            ]);

            return redirect()->to('document-submission-list')->with('success', 'Dokumen berhasil diperbarui.');
        } catch (\Exception $e) {
            log_message('error', 'Error updating document: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal memperbarui dokumen: ' . $e->getMessage());
        }
    }

    // POST document-submission-list/delete
    public function delete()
    {
        $id = $this->request->getPost('document_id');

        if (!$id) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'ID dokumen tidak valid.'
            ], 400);
        }

        $doc = $this->documentModel->find($id);
        if (!$doc) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Dokumen tidak ditemukan.'
            ], 404); 
        }

        try {
            $this->documentModel->update($id, [
                'status' => 3,
                'createdby' => 0
            ]);

            return $this->response->setJSON([
                'success' => true,
                'message' => 'Dokumen berhasil dihapus.'
            ], 200); 
        } catch (\Exception $e) {
            log_message('error', 'Error deleting document ID ' . $id . ': ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Terjadi kesalahan saat menghapus dokumen: ' . $e->getMessage()
            ], 500); 
        }
    }

    // POST document-submission-list/approve
    public function approve()
    {
        date_default_timezone_set('Asia/Jakarta');
        $document_id = $this->request->getPost('document_id');
        $approved_by = $this->request->getPost('approved_by');
        $remarks = $this->request->getPost('remarks');
        $action = trim($this->request->getPost('action') ?? '');

        log_message('debug', 'Received data - document_id: ' . $document_id . ', approved_by: ' . $approved_by . ', remarks: ' . $remarks . ', action: ' . $action);

        if (!$document_id || !$approved_by) {
            log_message('error', 'Missing required fields: document_id or approved_by');
            return redirect()->back()->with('error', 'Data wajib tidak lengkap.');
        }

        $validActions = ['approve', 'disapprove'];
        if (!in_array(strtolower($action), $validActions)) {
            log_message('error', 'Invalid action received: ' . $action);
            return redirect()->back()->with('error', 'Aksi tidak valid. Action received: ' . $action);
        }

        $status = strtolower($action) === 'approve' ? 1 : 2;

        $data = [
            'document_id' => $document_id,
            'remark' => $remarks,
            'status' => $status,
            'approvedate' => date('Y-m-d H:i:s'),
            'approveby' => $approved_by,
        ];

        try {
            $this->db->transStart();
            $this->documentApprovalModel->insert($data);
            $this->documentModel->update($document_id, ['status' => $status]);
            $this->db->transComplete();

            if ($this->db->transStatus() === false) {
                throw new \Exception('Transaksi gagal.');
            }

            log_message('info', 'Document ' . $document_id . ' processed with status: ' . $status);
            return redirect()->back()->with('success', 'Dokumen berhasil diproses.');
        } catch (\Exception $e) {
            log_message('error', 'Error processing approval for document ' . $document_id . ': ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal memproses dokumen: ' . $e->getMessage());
        }
    }

    // ✅ BARU: Method untuk mendapatkan kode dokumen berdasarkan type_id
    private function getKodeDokumenByType()
    {
        log_message('debug', 'Getting kode dokumen by type...');
        
        $kodeList = $this->kodeDokumenModel
            ->select('kode_dokumen.*, document_type.id as type_id, document_type.name as type_name')
            ->join('document_type', 'document_type.id = kode_dokumen.document_type_id')
            ->where('kode_dokumen.status', 1)
            ->where('document_type.status', 1)
            ->like('document_type.description', '[predefined]')
            ->findAll();

        $grouped = [];
        foreach ($kodeList as $item) {
            // ✅ PENTING: Group berdasarkan document_type.id (bukan name)
            $typeId = $item['type_id'];
            $grouped[$typeId][] = [
                'id' => $item['id'],
                'kode' => $item['kode'],
                'nama' => $item['nama'],
            ];
        }
        
        log_message('debug', 'Kode dokumen grouped by type: ' . json_encode($grouped));
        return $grouped;
    }

    // ✅ PERBAIKAN: Method showList dengan tambahan data untuk edit modal
    private function showList()
    {
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
            ->whereIn('document.status', [0, 1, 2])
            ->groupBy('document.id')
            ->findAll();

        $jenis_dokumen = $this->documentTypeModel->where('status', 1)->findAll();
        $kategori_dokumen = $this->kategoriDokumen;
        $kode_nama_dokumen = $this->documentCodeModel->where('status', 1)->findAll();
        $fakultas_list = $unitParentModel
            ->where('status', 1)
            ->orderBy('name', 'ASC')
            ->findAll();

        // ✅ SOLUSI UTAMA: Tambahkan data kode_dokumen_by_type untuk edit modal
        $kode_dokumen_by_type = $this->getKodeDokumenByType();

        $data = [
            'documents' => $documents,
            'jenis_dokumen' => $jenis_dokumen,
            'kategori_dokumen' => $kategori_dokumen,
            'kode_nama_dokumen' => $kode_nama_dokumen,
            'fakultas_list' => $fakultas_list,
            'kode_dokumen_by_type' => $kode_dokumen_by_type, // ✅ KUNCI SOLUSI: Data ini yang dibutuhkan edit modal
            'title' => 'Daftar Pengajuan Dokumen'
        ];

        log_message('debug', 'Documents retrieved: ' . count($documents) . ' documents');
        log_message('debug', 'Kode dokumen by type keys: ' . implode(', ', array_keys($kode_dokumen_by_type)));
        
        return view('KelolaDokumen/daftar-pengajuan', $data);
    }

    private function handleFileView()
    {
        $id = $this->request->getGet('id');
        return $this->serveFile($id, 'view');
    }

    private function handleFileDownload()
    {
        $id = $this->request->getGet('id');
        return $this->serveFile($id, 'download');
    }

    private function handleGetStatus()
    {
        $id = $this->request->getGet('id');
        $document = $this->documentModel->find($id);
        
        if ($document) {
            return $this->response->setJSON([
                'success' => true,
                'data' => [
                    'status' => $document['status']
                ]
            ]);
        }
        
        return $this->response->setJSON([
            'success' => false,
            'message' => 'Dokumen tidak ditemukan'
        ], 404);
    }

    private function handleGetHistory()
    {
        $document_id = $this->request->getGet('id');
        
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

    // ✅ PERBAIKAN UTAMA: Method handleGetKodeDokumen yang sudah diperbaiki
    private function handleGetKodeDokumen()
    {
        // ✅ PENTING: Ambil parameter 'jenis' yang dikirim dari AJAX
        $jenisId = $this->request->getPost('jenis');
        
        log_message('debug', 'handleGetKodeDokumen called with jenisId: ' . $jenisId);
        log_message('debug', 'Request method: ' . $this->request->getMethod());
        log_message('debug', 'Is AJAX: ' . ($this->request->isAJAX() ? 'Yes' : 'No'));
        
        if (!$jenisId) {
            log_message('error', 'Parameter jenis tidak ditemukan');
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Parameter jenis tidak valid'
            ], 400);
        }

        try {
            // ✅ SOLUSI: Gunakan document_type.id bukan document_type.name
            $kodeList = $this->kodeDokumenModel
                ->select('kode_dokumen.id, kode_dokumen.kode, kode_dokumen.nama')
                ->join('document_type', 'document_type.id = kode_dokumen.document_type_id')
                ->where('kode_dokumen.status', 1)
                ->where('document_type.status', 1)
                ->where('document_type.id', $jenisId) // ✅ KUNCI: Gunakan ID bukan name
                ->like('document_type.description', '[predefined]') // ✅ TAMBAHAN: Pastikan hanya predefined
                ->findAll();

            log_message('debug', 'Found kode dokumen count: ' . count($kodeList));
            log_message('debug', 'SQL Query: ' . $this->kodeDokumenModel->getLastQuery());

            $result = array_map(function($item) {
                return [
                    'id' => $item['id'],
                    'kode' => $item['kode'],
                    'nama' => $item['nama']
                ];
            }, $kodeList);

            log_message('debug', 'Returning kode dokumen: ' . json_encode($result));
            
            return $this->response->setJSON($result);
            
        } catch (\Exception $e) {
            log_message('error', 'Error in handleGetKodeDokumen: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Terjadi kesalahan server: ' . $e->getMessage()
            ], 500);
        }
    }

    private function serveFile($documentId, $action = 'view')
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
        
        // Menggunakan role_id dari session untuk pengecekan akses
        $userRoleId = session('role_id');
        $allowedRoleIds = [1, 2]; // Sesuaikan dengan ID role admin dan superadmin di database Anda
        
        // Cek apakses berdasarkan role_id dari session atau ownership dokumen
        if (!in_array($userRoleId, $allowedRoleIds) && $document['createdby'] != $userId) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Anda tidak memiliki akses ke file ini.');
        }

        $filePath = ROOTPATH . '../' . $revision['filepath'];
        if (!file_exists($filePath)) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('File tidak ditemukan di server.');
        }

        $file = new File($filePath);
        $mimeType = $file->getMimeType() ?: 'application/octet-stream';
        $disposition = ($action === 'download') ? 'attachment' : 'inline';

        return $this->response
            ->setHeader('Content-Type', $mimeType)
            ->setHeader('Content-Disposition', $disposition . '; filename="' . $revision['filename'] . '"')
            ->setBody(file_get_contents($filePath));
    }
}