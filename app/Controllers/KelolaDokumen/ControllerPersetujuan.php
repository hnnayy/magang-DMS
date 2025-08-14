<?php

namespace App\Controllers\KelolaDokumen;

use App\Controllers\BaseController;
use App\Models\DocumentCodeModel;
use App\Models\DocumentTypeModel;
use App\Models\DocumentModel;
use App\Models\UnitModel;
use App\Models\UnitParentModel;
use App\Models\DocumentApprovalModel;
use App\Models\NotificationModel;
use App\Models\NotificationRecipientsModel;
use App\Models\UserModel;
use App\Models\DocumentRevisionModel;
use CodeIgniter\Files\File;

require_once ROOTPATH . 'vendor/autoload.php';

class ControllerPersetujuan extends BaseController
{
    protected $kategoriDokumen = [];
    protected $kodeDokumen = [];
    protected $documentModel;
    protected $documentRevisionModel;
    protected $notificationModel;
    protected $notificationRecipientsModel;
    protected $userModel;
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
        $this->documentTypeModel = model(DocumentTypeModel::class);
        $this->documentModel = model(DocumentModel::class);
        $this->documentApprovalModel = model(DocumentApprovalModel::class);
        $this->documentRevisionModel = model(DocumentRevisionModel::class);
        $this->notificationModel = model(NotificationModel::class);
        $this->notificationRecipientsModel = model(NotificationRecipientsModel::class);
        $this->userModel = model(UserModel::class);
        $this->kodeDokumenModel = model(DocumentCodeModel::class);
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
            case 'get-kode-dokumen':
                return $this->handleGetKodeDokumen();
            default:
                return $this->showList();
        }
    }

    // POST document-submission-list/store
    public function store()
    {
        $jenisId = $this->request->getPost('type');
        $kodeDokumenId = $this->request->getPost('kode_dokumen');
        $kodeCustom = $this->request->getPost('kode_dokumen_custom');
        $namaCustom = $this->request->getPost('nama_dokumen_custom');
        $nomor = $this->request->getPost('nomor');
        $revisi = $this->request->getPost('revisi') ?? 'Rev. 0';
        $nama = $this->request->getPost('nama');
        $keterangan = $this->request->getPost('keterangan');
        $file = $this->request->getFile('file_dokumen');

        if (empty($jenisId) || empty($nomor) || empty($nama)) {
            return redirect()->back()->with('error', 'All required fields must be filled.');
        }

        // Handle document code based on document type
        $finalKodeDokumenId = null;
        if ($jenisId) {
            $documentType = $this->documentTypeModel->where('id', $jenisId)->where('status', 1)->first();
            if ($documentType && str_contains($documentType['description'] ?? '', '[predefined]')) {
                if ($kodeDokumenId) {
                    $finalKodeDokumenId = $kodeDokumenId;
                }
            } else {
                if ($kodeCustom && $namaCustom) {
                    $existingKode = $this->kodeDokumenModel
                        ->where('document_type_id', $jenisId)
                        ->where('kode', strtoupper($kodeCustom))
                        ->where('nama', $namaCustom)
                        ->first();

                    if ($existingKode) {
                        $finalKodeDokumenId = $existingKode['id'];
                    } else {
                        $newKodeData = [
                            'document_type_id' => $jenisId,
                            'kode' => strtoupper($kodeCustom),
                            'nama' => $namaCustom,
                            'status' => 1,
                            'createddate' => date('Y-m-d H:i:s'),
                            'createdby' => session('user_id')
                        ];

                        $this->kodeDokumenModel->insert($newKodeData);
                        $finalKodeDokumenId = $this->kodeDokumenModel->getInsertID();
                    }
                }
            }
        }

        $data = [
            'type' => $jenisId,
            'kode_dokumen_id' => $finalKodeDokumenId,
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
            $this->db->transStart();
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

            $this->db->transComplete();

            if ($this->db->transStatus() === false) {
                throw new \Exception('Transaction failed.');
            }

            return redirect()->to('document-submission-list')->with('success', 'Document successfully created.');
        } catch (\Exception $e) {
            log_message('error', 'Error creating document: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to create document: ' . $e->getMessage());
        }
    }

    // POST document-submission-list/update
   public function update()
    {
        $documentId = $this->request->getPost('document_id');
        $nama = $this->request->getPost('nama');
        $revisi = $this->request->getPost('revisi') ?: 'Rev. 0';
        $keterangan = $this->request->getPost('keterangan');
        $file = $this->request->getFile('file_dokumen');

        if (empty($documentId) || empty($nama) || empty($revisi)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Document ID, title, and revision are required.'
            ], 400);
        }

        $originalDocument = $this->documentModel->find($documentId);
        if (!$originalDocument) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Document not found in database.'
            ], 404);
        }

        try {
            $this->db->transStart();

            if ($file && $file->isValid() && !$file->hasMoved()) {
                $this->documentModel->update($documentId, ['status' => 4]);

                $newDocumentData = [
                    'type' => $originalDocument['type'],
                    'kode_dokumen_id' => $originalDocument['kode_dokumen_id'],
                    'number' => $originalDocument['number'],
                    'date_published' => date('Y-m-d'),
                    'revision' => $revisi,
                    'title' => $nama,
                    'description' => $originalDocument['description'],
                    'unit_id' => session()->get('unit_id') ?? $originalDocument['unit_id'] ?? 99,
                    'status' => 1, // Keep status as approved when updating
                    'createddate' => date('Y-m-d H:i:s'),
                    'createdby' => session('user_id'),
                    'standar_ids' => $originalDocument['standar_ids'] ?? '',
                    'klausul_ids' => $originalDocument['klausul_ids'] ?? ''
                ];

                $this->documentModel->insert($newDocumentData);
                $newDocumentId = $this->documentModel->getInsertID();

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
                $updateData = [
                    'title' => $nama,
                    'revision' => $revisi,
                    'description' => $keterangan ?: $originalDocument['description'],
                    'status' => 1, // Maintain approved status when updating without file change
                ];

                $this->documentModel->update($documentId, $updateData);

                $oldRevision = $this->documentRevisionModel
                    ->where('document_id', $documentId)
                    ->orderBy('id', 'DESC')
                    ->first();

                if ($oldRevision) {
                    $this->documentRevisionModel->update($oldRevision['id'], [
                        'revision' => $revisi,
                        'remark' => $keterangan ?: $oldRevision['remark'],
                        'createddate' => date('Y-m-d H:i:s'),
                    ]);
                }
            }

            if ($keterangan) {
                $this->documentApprovalModel->insert([
                    'document_id' => $documentId,
                    'remark' => $keterangan,
                    'status' => 0,
                    'createddate' => date('Y-m-d H:i:s'),
                    'createdby' => session('user_id'),
                ]);
            }

            $this->db->transComplete();

            if ($this->db->transStatus() === false) {
                throw new \Exception('Database transaction failed.');
            }

            $documentType = $this->documentTypeModel->where('id', $originalDocument['type'])->first();
            $documentTypeName = $documentType['name'] ?? 'Unknown Type';
            $this->createDocumentNotification($documentId, $nama, $documentTypeName);

            return $this->response->setJSON([
                'success' => true,
                'message' => 'Document successfully updated.'
            ], 200);
        } catch (\Exception $e) {
            log_message('error', 'Error updating document ID ' . $documentId . ': ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Failed to update document: ' . $e->getMessage()
            ], 500);
        }
    }



    // POST document-submission-list/delete
    public function delete()
    {
        $id = $this->request->getPost('document_id');

        if (!$id) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Invalid document ID.'
            ], 400);
        }

        $doc = $this->documentModel->find($id);
        if (!$doc) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Document not found.'
            ], 404);
        }

        try {
            $this->documentModel->update($id, [
                'status' => 3,
                'createdby' => 0
            ]);

            return $this->response->setJSON([
                'success' => true,
                'message' => 'Document successfully deleted.'
            ], 200);
        } catch (\Exception $e) {
            log_message('error', 'Error deleting document ID ' . $id . ': ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'An error occurred while deleting document: ' . $e->getMessage()
            ], 500);
        }
    }

    private function showList()
    {
        $unitParentModel = new UnitParentModel();

        // Get current user information for access filtering
        $currentUserId = session()->get('user_id');
        $currentUnitId = session()->get('unit_id');
        $currentUnitParentId = session()->get('unit_parent_id');
        $currentRoleId = session()->get('role_id');
        $roleModel = new \App\Models\RoleModel();
        $currentUserRole = $roleModel->find($currentRoleId);
        $currentAccessLevel = $currentUserRole['access_level'] ?? 2;

        $documents = $this->documentModel
            ->select('document.*, 
                      dt.name AS jenis_dokumen, 
                      unit.name AS unit_name, 
                      unit_parent.name AS parent_name,
                      unit.parent_id AS unit_parent_id,
                      kd.kode AS kode_dokumen_kode,
                      kd.nama AS kode_dokumen_nama,
                      dr.filename AS filename,
                      dr.filepath AS filepath,
                      creator.fullname AS creator_name,
                      creator.unit_id AS creator_unit_id,
                      creator_unit.parent_id AS creator_unit_parent_id,
                      creator_unit_parent.name AS creator_unit_parent_name,
                      da.remark AS approval_remark')
            ->join('document_type dt', 'dt.id = document.type', 'left')
            ->join('unit', 'unit.id = document.unit_id', 'left')
            ->join('unit_parent', 'unit_parent.id = unit.parent_id', 'left')
            ->join('kode_dokumen kd', 'kd.id = document.kode_dokumen_id', 'left')
            ->join('document_revision dr', 'dr.document_id = document.id AND dr.id = (SELECT MAX(id) FROM document_revision WHERE document_id = document.id)', 'left')
            ->join('user creator', 'creator.id = document.createdby', 'left')
            ->join('unit creator_unit', 'creator_unit.id = creator.unit_id', 'left')
            ->join('unit_parent creator_unit_parent', 'creator_unit_parent.id = creator_unit.parent_id', 'left')
            ->join('document_approval da', 'da.document_id = document.id AND da.id = (SELECT MAX(id) FROM document_approval WHERE document_id = document.id)', 'left')
            ->where('document.createdby !=', 0)
            ->where('document.status', 1) // Only approved documents
            ->groupBy('document.id')
            ->orderBy('document.createddate', 'ASC') // Sort by creation date, oldest first so newest is at the bottom
            ->findAll();

        // Filter documents based on user access
        $filteredDocuments = [];
        foreach ($documents as $doc) {
            $creatorUnitId = $doc['creator_unit_id'] ?? 0;
            $creatorUnitParentId = $doc['creator_unit_parent_id'] ?? 0;
            $creatorAccessLevel = $this->userModel->select('role.access_level')
                ->join('user_role', 'user_role.user_id = user.id')
                ->join('role', 'role.id = user_role.role_id')
                ->where('user.id', $doc['createdby'])
                ->first()['access_level'] ?? 2;

            $canView = false;
            // Always allow viewing own documents
            if ($doc['createdby'] == $currentUserId) {
                $canView = true;
            }
            // Level 1 can view Level 2 documents with same unit or parent unit
            elseif ($currentAccessLevel == 1 && $creatorAccessLevel == 2) {
                if ($creatorUnitId == $currentUnitId || $creatorUnitParentId == $currentUnitParentId) {
                    $canView = true;
                }
            }

            if ($canView) {
                $filteredDocuments[] = $doc;
            }
        }

        // Fetch supporting data
        $jenis_dokumen = $this->documentTypeModel->where('status', 1)->findAll();
        $kategori_dokumen = $this->kategoriDokumen;
        $kode_nama_dokumen = $this->kodeDokumenModel->where('status', 1)->findAll();
        $fakultas_list = $unitParentModel
            ->where('status', 1)
            ->orderBy('name', 'ASC')
            ->findAll();

        $kode_dokumen_by_type = $this->getKodeDokumenByType();

        $data = [
            'documents' => $filteredDocuments,
            'jenis_dokumen' => $jenis_dokumen,
            'kategori_dokumen' => $kategori_dokumen,
            'kode_nama_dokumen' => $kode_nama_dokumen,
            'fakultas_list' => $fakultas_list,
            'kode_dokumen_by_type' => $kode_dokumen_by_type,
            'title' => 'Document Submission List'
        ];

        log_message('debug', 'Filtered documents count: ' . count($filteredDocuments));
        return view('KelolaDokumen/dokumen_persetujuan', $data);
    }

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

   private function handleFileView()
{
    $id = $this->request->getGet('id');
    return $this->serveFile($id, 'view');
}

private function handleFileDownload()
{
    $id = $this->request->getGet('id');
    
    // Debug logging
    log_message('debug', 'handleFileDownload called');
    log_message('debug', 'All GET parameters: ' . print_r($this->request->getGet(), true));
    log_message('debug', 'Document ID from GET: ' . $id);
    
    if (!$id) {
        // Try alternative parameter names
        $id = $this->request->getGet('document_id') ?: $this->request->getGet('doc_id');
        log_message('debug', 'Alternative ID search result: ' . $id);
    }
    
    return $this->serveFile($id, 'download');
}


    private function handleGetHistory()
{
    $document_id = $this->request->getGet('id');
    
    log_message('debug', 'get_history called with document_id: ' . $document_id);
    
    if (!$this->session->get('user_id')) {
        log_message('debug', 'Unauthorized access to get_history');
        return $this->response->setJSON([
            'success' => false,
            'message' => 'Tidak diizinkan'
        ], 401);
    }

    // Fetch the current document
    $document = $this->documentModel
        ->select('document.*, dt.name AS jenis_dokumen, kd.kode AS kode_dokumen_kode, kd.nama AS kode_dokumen_nama')
        ->join('document_type dt', 'dt.id = document.type', 'left')
        ->join('kode_dokumen kd', 'kd.id = document.kode_dokumen_id', 'left')
        ->where('document.id', $document_id)
        ->where('document.createdby !=', 0)
        ->first();

    if (!$document) {
        log_message('debug', 'Dokumen tidak ditemukan untuk id: ' . $document_id);
        return $this->response->setJSON([
            'success' => false,
            'message' => 'Dokumen tidak ditemukan'
        ], 404);
    }

    // Fetch revisions for the current document
    $revisions = $this->documentRevisionModel
        ->select('id AS revision_id, document_id, revision, filename, filepath, filesize, remark, createddate, createdby')
        ->where('document_id', $document_id)
        ->orderBy('createddate', 'DESC')
        ->findAll();

    // Fetch related superseded documents
    $relatedDocuments = $this->documentModel
        ->select('id')
        ->where('number', $document['number'])
        ->where('type', $document['type'])
        ->where('status', 4) // Superseded documents
        ->where('createdby !=', 0)
        ->findAll();

    $relatedDocumentIds = array_column($relatedDocuments, 'id');

    if (!empty($relatedDocumentIds)) {
        $relatedRevisions = $this->documentRevisionModel
            ->select('id AS revision_id, document_id, revision, filename, filepath, filesize, remark, createddate, createdby')
            ->whereIn('document_id', $relatedDocumentIds)
            ->orderBy('createddate', 'DESC')
            ->findAll();
        $revisions = array_merge($revisions, $relatedRevisions);
    }

    // Sort all revisions by createddate
    usort($revisions, function ($a, $b) {
        return strcmp($b['createddate'], $a['createddate']);
    });

    $history = [];
    foreach ($revisions as $revision) {
        // Ambil dokumen terkait berdasarkan document_id dari revisi
        $relatedDocument = $this->documentModel
            ->select('title, number, status')
            ->where('id', $revision['document_id'])
            ->first();

        if ($relatedDocument) {
            $history[] = [
                'revision_id' => $revision['revision_id'],
                'document_id' => $revision['document_id'],
                'revision' => $revision['revision'] ?? 'Rev. 0',
                'filename' => $revision['filename'],
                'filepath' => $revision['filepath'],
                'filesize' => $revision['filesize'],
                'remark' => $revision['remark'],
                'updated_at' => $revision['createddate'],
                'updated_by' => $revision['createdby'],
                'document_title' => $relatedDocument['title'], // Ambil title dari dokumen terkait
                'document_number' => $relatedDocument['number'], // Ambil number dari dokumen terkait
                'status' => $relatedDocument['status'],
            ];
        }
    }

    log_message('debug', 'Jumlah riwayat yang diformat: ' . count($history));
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

public function serveFile($id = null, $action = 'download')
{
    log_message('debug', 'serveFile called with ID: ' . $id . ' and action: ' . $action);
    
    // Coba ambil ID dari berbagai sumber
    if (!$id) {
        $id = $this->request->getGet('id') ?: $this->request->getGet('document_id') ?: $this->request->getPost('id');
        log_message('debug', 'ID retrieved from request: ' . $id);
    }
    
    if (!$id) {
        log_message('error', 'No document ID provided from any source.');
        return $this->response->setJSON([
            'success' => false,
            'message' => 'No document ID provided.'
        ], 400);
    }

    // Get the latest revision for this document
    $revision = $this->documentRevisionModel
        ->where('document_id', $id)
        ->orderBy('createddate', 'DESC')
        ->first();

    if (!$revision) {
        log_message('error', 'Document revision not found for ID: ' . $id);
        return $this->response->setJSON([
            'success' => false,
            'message' => 'Document revision not found.'
        ], 404);
    }

    if (empty($revision['filepath'])) {
        log_message('error', 'Filepath empty for document ID: ' . $id);
        return $this->response->setJSON([
            'success' => false,
            'message' => 'Document file path not found.'
        ], 404);
    }

    $filePath = ROOTPATH . '../' . $revision['filepath'];
    log_message('debug', 'Attempting to serve file from: ' . $filePath);
    
    if (!file_exists($filePath)) {
        log_message('error', 'File not found at: ' . $filePath);
        return $this->response->setJSON([
            'success' => false,
            'message' => 'File not found on server: ' . $filePath
        ], 404);
    }

    $fileName = $revision['filename'] ?? 'document.pdf';
    
    try {
        if ($action === 'view') {
            // For viewing, set appropriate headers
            $mimeType = mime_content_type($filePath);
            return $this->response
                ->setHeader('Content-Type', $mimeType)
                ->setHeader('Content-Disposition', 'inline; filename="' . $fileName . '"')
                ->setBody(file_get_contents($filePath));
        } else {
            // For download, force download
            return $this->response->download($filePath, null)
                ->setFileName($fileName);
        }
    } catch (\Exception $e) {
        log_message('error', 'Error serving file: ' . $e->getMessage());
        return $this->response->setJSON([
            'success' => false,
            'message' => 'Error serving file: ' . $e->getMessage()
        ], 500);
    }
}

    private function createDocumentNotification($documentId, $documentTitle, $documentTypeName)
    {
        $notificationData = [
            'title' => 'New Document Submission',
            'message' => "A new document titled '{$documentTitle}' of type '{$documentTypeName}' has been submitted and requires your approval.",
            'createddate' => date('Y-m-d H:i:s'),
            'createdby' => session('user_id'),
            'type' => 'document_submission',
            'reference_id' => $documentId,
        ];

        $this->db->transStart();
        $this->notificationModel->insert($notificationData);
        $notificationId = $this->notificationModel->getInsertID();

        // Fetch users with access_level 1 for notification recipients
        $recipients = $this->userModel
            ->select('user.id')
            ->join('user_role', 'user_role.user_id = user.id')
            ->join('role', 'role.id = user_role.role_id')
            ->where('role.access_level', 1)
            ->findAll();

        foreach ($recipients as $recipient) {
            $this->notificationRecipientsModel->insert([
                'notification_id' => $notificationId,
                'user_id' => $recipient['id'],
                'is_read' => 0,
                'createddate' => date('Y-m-d H:i:s'),
            ]);
        }

        $this->db->transComplete();
    }

    private function createApprovalNotification($documentId, $documentTitle, $documentTypeName, $actionText, $approverName, $remarks)
    {
        $notificationData = [
            'title' => 'Document ' . ucfirst($actionText),
            'message' => "The document titled '{$documentTitle}' of type '{$documentTypeName}' has been {$actionText} by {$approverName}. Remarks: " . ($remarks ?: 'None'),
            'createddate' => date('Y-m-d H:i:s'),
            'createdby' => session('user_id'),
            'type' => 'document_approval',
            'reference_id' => $documentId,
        ];

        $this->db->transStart();
        $this->notificationModel->insert($notificationData);
        $notificationId = $this->notificationModel->getInsertID();

        // Notify the document creator
        $document = $this->documentModel->find($documentId);
        $this->notificationRecipientsModel->insert([
            'notification_id' => $notificationId,
            'user_id' => $document['createdby'],
            'is_read' => 0,
            'createddate' => date('Y-m-d H:i:s'),
        ]);

        $this->db->transComplete();
    }

    private function handleGetKodeDokumen()
    {
        $typeId = $this->request->getGet('type_id');

        if (!$typeId) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Type ID is required'
            ], 400);
        }

        $kodeDokumen = $this->kodeDokumenModel
            ->where('document_type_id', $typeId)
            ->where('status', 1)
            ->findAll();

        return $this->response->setJSON([
            'success' => true,
            'data' => $kodeDokumen
        ]);
    }
}