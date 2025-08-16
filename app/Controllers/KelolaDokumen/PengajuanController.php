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

class PengajuanController extends BaseController
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
        $this->documentTypeModel = new DocumentTypeModel();
        $this->kodeDokumenModel = new DocumentCodeModel();
        $this->documentModel = new DocumentModel();
        $this->documentApprovalModel = new DocumentApprovalModel();
        $this->documentCodeModel = new DocumentCodeModel();
        $this->documentRevisionModel = new DocumentRevisionModel();
        $this->notificationModel = new NotificationModel();
        $this->notificationRecipientsModel = new NotificationRecipientsModel();
        $this->userModel = new UserModel();
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

    // GET document-submission-list/edit
    public function edit()
    {
        $id = $this->request->getGet('id');
        
        if (!$id) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Invalid document ID'
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
                'message' => 'Document not found'
            ], 404);
        }

        return $this->response->setJSON([
            'success' => true,
            'data' => $document
        ]);
    }

    // POST document-submission-list/update// POST document-submission-list/update
// POST document-submission-list/update
public function update()
{
    $documentId = $this->request->getPost('document_id');

    if (!$documentId) {
        return redirect()->back()->with('error', 'Document ID not found.');
    }

    $jenisId = $this->request->getPost('type');
    $kodeDokumenId = $this->request->getPost('kode_dokumen');
    $kodeCustom = $this->request->getPost('kode_dokumen_custom');
    $namaCustom = $this->request->getPost('nama_dokumen_custom');
    $nomor = $this->request->getPost('nomor');
    $revisi = $this->request->getPost('revisi') ?: 'Rev. 0';
    $nama = $this->request->getPost('nama');
    $keterangan = $this->request->getPost('keterangan');
    $file = $this->request->getFile('file_dokumen');

    // Validasi field wajib
    if (empty($jenisId) || empty($nomor) || empty($nama)) {
        return redirect()->back()->with('error', 'All required fields must be filled.');
    }

    // Get original document
    $originalDocument = $this->documentModel->find($documentId);
    if (!$originalDocument) {
        return redirect()->back()->with('error', 'Document not found in database.');
    }

    // Check if document is already approved
    if ($originalDocument['status'] == 1) {
        return redirect()->back()->with('error', 'Cannot edit approved document.');
    }

    $unitId = $originalDocument['unit_id'] ?? session()->get('unit_id') ?? 99;

    // Handle document code
    $finalKodeDokumenId = null;
    $documentType = null;
    
    if ($jenisId) {
        $documentType = $this->documentTypeModel->where('id', $jenisId)->where('status', 1)->first();
        if ($documentType && str_contains($documentType['description'] ?? '', '[predefined]')) {
            if ($kodeDokumenId) {
                $kodeDokumen = $this->kodeDokumenModel->where('id', $kodeDokumenId)->where('status', 1)->first();
                if ($kodeDokumen) {
                    $finalKodeDokumenId = $kodeDokumenId;
                }
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

    try {
        $this->db->transStart();

        // Mark the original document as Superseded (status 4)
        log_message('debug', 'Marking document ID ' . $documentId . ' as Superseded (status 4)');
        $this->documentModel->update($documentId, ['status' => 4]);

        // Create a new document record
        $newDocumentData = [
            'type' => $jenisId,
            'kode_dokumen_id' => $finalKodeDokumenId,
            'number' => $nomor,
            'date_published' => date('Y-m-d'), // Set current date for new document
            'revision' => $revisi,
            'title' => $nama,
            'description' => $keterangan,
            'unit_id' => $unitId,
            'status' => 0, // New document starts as pending
            'createddate' => date('Y-m-d H:i:s'),
            'createdby' => session('user_id'),
            'standar_ids' => $originalDocument['standar_ids'] ?? '',
            'klausul_ids' => $originalDocument['klausul_ids'] ?? ''
        ];

        log_message('debug', 'Creating new document with data: ' . json_encode($newDocumentData));
        $this->documentModel->insert($newDocumentData);
        $newDocumentId = $this->documentModel->getInsertID();

        if (!$newDocumentId) {
            $errors = $this->documentModel->errors();
            log_message('error', 'New document creation failed. Errors: ' . json_encode($errors));
            throw new \Exception('Failed to create new document: ' . json_encode($errors));
        }

        log_message('debug', 'New document created with ID: ' . $newDocumentId);

        // Handle file upload and revision
        if ($file && $file->isValid() && !$file->hasMoved()) {
            log_message('debug', 'Processing new file upload');
            
            $uploadPath = ROOTPATH . '../storage/uploads';
            if (!is_dir($uploadPath)) {
                mkdir($uploadPath, 0755, true);
            }

            // Validate file type
            $allowedTypes = ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx'];
            $fileExtension = strtolower($file->getClientExtension());
            
            if (!in_array($fileExtension, $allowedTypes)) {
                throw new \Exception('Invalid file type. Only PDF, DOC, DOCX, XLS, XLSX, PPT, and PPTX files are allowed.');
            }

            // Validate file size (max 10MB)
            if ($file->getSize() > 10 * 1024 * 1024) {
                throw new \Exception('File size too large. Maximum file size is 10MB.');
            }

            $newName = $file->getRandomName();
            $file->move($uploadPath, $newName);

            $revisionData = [
                'document_id' => $newDocumentId,
                'revision' => $revisi,
                'filename' => $file->getClientName(),
                'filepath' => 'storage/uploads/' . $newName,
                'filesize' => $file->getSize(),
                'remark' => $keterangan ?: '',
                'createddate' => date('Y-m-d H:i:s'),
                'createdby' => session('user_id'),
            ];

            log_message('debug', 'Inserting new revision for new document: ' . json_encode($revisionData));
            
            $revisionResult = $this->documentRevisionModel->insert($revisionData);
            
            if (!$revisionResult) {
                $revisionErrors = $this->documentRevisionModel->errors();
                log_message('error', 'Document revision insert failed. Errors: ' . json_encode($revisionErrors));
                throw new \Exception('Failed to create document revision: ' . json_encode($revisionErrors));
            }
            log_message('debug', 'Revision inserted with ID: ' . $this->documentRevisionModel->getInsertID());
        } else {
            log_message('debug', 'No new file uploaded, copying existing revision');
            
            // Copy the latest revision from the old document
            $oldRevision = $this->documentRevisionModel
                ->where('document_id', $documentId)
                ->orderBy('id', 'DESC')
                ->first();

            if ($oldRevision) {
                $revisionData = [
                    'document_id' => $newDocumentId,
                    'revision' => $revisi,
                    'filename' => $oldRevision['filename'],
                    'filepath' => $oldRevision['filepath'],
                    'filesize' => $oldRevision['filesize'],
                    'remark' => $keterangan ?: $oldRevision['remark'],
                    'createddate' => date('Y-m-d H:i:s'),
                    'createdby' => session('user_id'),
                ];

                log_message('debug', 'Inserting revision for new document with existing file: ' . json_encode($revisionData));
                
                $revisionResult = $this->documentRevisionModel->insert($revisionData);
                
                if (!$revisionResult) {
                    $revisionErrors = $this->documentRevisionModel->errors();
                    log_message('error', 'Document revision insert failed. Errors: ' . json_encode($revisionErrors));
                    throw new \Exception('Failed to create document revision: ' . json_encode($revisionErrors));
                }
                
                log_message('debug', 'Revision with existing file created successfully for new document');
            } else {
                log_message('warning', 'No previous revision found for document ID: ' . $documentId);
            }
        }

        $this->db->transComplete();

        if ($this->db->transStatus() === false) {
            throw new \Exception('Database transaction failed.');
        }

        log_message('info', 'Document update transaction completed successfully');

        // Create notification for update action
        $documentTypeName = $documentType['name'] ?? 'Unknown Type';
        $documentTitle = $nama;
        $this->createDocumentNotification($newDocumentId, $documentTitle, $documentTypeName);

        return redirect()->to('document-submission-list')->with('success', 'Document successfully updated.');
        
    } catch (\Exception $e) {
        log_message('error', 'Error updating document ID ' . $documentId . ': ' . $e->getMessage());
        log_message('error', 'Stack trace: ' . $e->getTraceAsString());
        return redirect()->back()->with('error', 'Failed to update document: ' . $e->getMessage());
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

    // POST document-submission-list/approve
    public function approve()
{
    date_default_timezone_set('Asia/Jakarta');
    $document_id = $this->request->getPost('document_id');
    $approved_by = $this->request->getPost('approved_by');
    $remarks = $this->request->getPost('remarks');
    $action = trim($this->request->getPost('action') ?? '');
    $approval_date = $this->request->getPost('approval_date');
    $effective_date = $this->request->getPost('effective_date');

    log_message('debug', 'Received data - document_id: ' . $document_id . ', approved_by: ' . $approved_by . ', remarks: ' . $remarks . ', action: ' . $action . ', approval_date: ' . $approval_date . ', effective_date: ' . $effective_date);

    if (!$document_id || !$approved_by) {
        log_message('error', 'Missing required fields: document_id or approved_by');
        return redirect()->back()->with('error', 'Required data is incomplete.');
    }

    // Validate approval_date and effective_date for approve action
    if (strtolower($action) === 'approve' && (!$approval_date || !$effective_date)) {
        log_message('error', 'Missing approval_date or effective_date for approve action');
        return redirect()->back()->with('error', 'Approval date and effective date are required for approval.');
    }

    $validActions = ['approve', 'disapprove'];
    if (!in_array(strtolower($action), $validActions)) {
        log_message('error', 'Invalid action received: ' . $action);
        return redirect()->back()->with('error', 'Invalid action. Action received: ' . $action);
    }

    $document = $this->documentModel
        ->select('document.*, dt.name AS document_type_name')
        ->join('document_type dt', 'dt.id = document.type', 'left')
        ->where('document.id', $document_id)
        ->first();

    if (!$document) {
        log_message('error', 'Document not found for ID: ' . $document_id);
        return redirect()->back()->with('error', 'Document not found.');
    }

    // Check if document is already approved
    if ($document['status'] == 1) {
        log_message('info', 'Attempt to approve already approved document ID: ' . $document_id);
        return redirect()->back()->with('error', 'This document has already been approved.');
    }

    $status = strtolower($action) === 'approve' ? 1 : 2;

    $approver = $this->userModel->find($approved_by);
    $approverName = $approver['fullname'] ?? $approver['username'] ?? 'Unknown User';

    // Use approval_date from form, or current date/time as fallback
    $approveDate = $approval_date ? $approval_date . ' ' . date('H:i:s') : date('Y-m-d H:i:s');

    $data = [
        'document_id' => $document_id,
        'remark' => $remarks,
        'status' => $status,
        'approvedate' => $approveDate,
        'approveby' => $approved_by,
        'effective_date' => $effective_date,
    ];

    try {
        $this->db->transStart();
        $this->documentApprovalModel->insert($data);
        $this->documentModel->update($document_id, ['status' => $status]);
        $this->db->transComplete();

        if ($this->db->transStatus() === false) {
            throw new \Exception('Transaction failed.');
        }

        $actionText = strtolower($action) === 'approve' ? 'approved' : 'disapproved';
        $this->createApprovalNotification($document_id, $document['title'], $document['document_type_name'], $actionText, $approverName, $remarks);

        $successMessage = strtolower($action) === 'approve' ? 'Document successfully approved.' : 'Document successfully disapproved.';
        
        log_message('info', 'Document ' . $document_id . ' processed with status: ' . $status);
        return redirect()->back()->with('success', $successMessage);
    } catch (\Exception $e) {
        log_message('error', 'Error processing approval for document ' . $document_id . ': ' . $e->getMessage());
        return redirect()->back()->with('error', 'Failed to process document: ' . $e->getMessage());
    }
}
    // Method untuk mendapatkan kode dokumen berdasarkan type_id
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

private function showList()
{
    $unitParentModel = new UnitParentModel();

    // Ambil data user saat ini untuk filter akses
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
                  creator_unit_parent.name AS creator_unit_parent_name')
        ->join('document_type dt', 'dt.id = document.type', 'left')
        ->join('unit', 'unit.id = document.unit_id', 'left')
        ->join('unit_parent', 'unit_parent.id = unit.parent_id', 'left')
        ->join('kode_dokumen kd', 'kd.id = document.kode_dokumen_id', 'left')
        ->join('document_revision dr', 'dr.document_id = document.id AND dr.id = (SELECT MAX(id) FROM document_revision WHERE document_id = document.id)', 'left')
        ->join('user creator', 'creator.id = document.createdby', 'left')
        ->join('unit creator_unit', 'creator_unit.id = creator.unit_id', 'left')
        ->join('unit_parent creator_unit_parent', 'creator_unit_parent.id = creator_unit.parent_id', 'left')
        ->where('document.createdby !=', 0)
        ->groupBy('document.id')
        ->where('document.status !=', 4)
        ->orderBy('document.createddate', 'DESC')
        ->findAll();

    // Filter dokumen berdasarkan akses pengguna
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
        // Selalu izinkan melihat dokumen sendiri
        if ($doc['createdby'] == $currentUserId) {
            $canView = true;
        }
        // Level 1 bisa melihat dokumen Level 2 dengan unit atau parent unit yang sama
        elseif ($currentAccessLevel == 1 && $creatorAccessLevel == 2) {
            if ($creatorUnitId == $currentUnitId || $creatorUnitParentId == $currentUnitParentId) {
                $canView = true;
            }
        }

        if ($canView) {
            $filteredDocuments[] = $doc;
        }
    }

    // Ambil data pendukung
    $jenis_dokumen = $this->documentTypeModel->where('status', 1)->findAll();
    $kategori_dokumen = $this->kategoriDokumen;
    $kode_nama_dokumen = $this->documentCodeModel->where('status', 1)->findAll();
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
            'message' => 'Document not found'
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

private function handleGetKodeDokumen()
    {
        $jenisId = $this->request->getPost('jenis');
        
        log_message('debug', 'handleGetKodeDokumen called with jenisId: ' . $jenisId);
        log_message('debug', 'Request method: ' . $this->request->getMethod());
        log_message('debug', 'Is AJAX: ' . ($this->request->isAJAX() ? 'Yes' : 'No'));
        
        if (!$jenisId) {
            log_message('error', 'Type parameter not found');
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Invalid type parameter'
            ], 400);
        }

        try {
            $kodeList = $this->kodeDokumenModel
                ->select('kode_dokumen.id, kode_dokumen.kode, kode_dokumen.nama')
                ->join('document_type', 'document_type.id = kode_dokumen.document_type_id')
                ->where('kode_dokumen.status', 1)
                ->where('document_type.status', 1)
                ->where('document_type.id', $jenisId)
                ->like('document_type.description', '[predefined]')
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
                'message' => 'Server error occurred: ' . $e->getMessage()
            ], 500);
        }
    }

private function serveFile($documentId, $action = 'view')
{
    $userId = session('user_id');
    if (!$userId) {
        throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('You must login to access files.');
    }

    $document = $this->documentModel->find($documentId);
    if (!$document) {
        throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Document not found.');
    }

    // Restrict access to superseded documents unless user is creator or has higher access
    if ($document['status'] == 4) {
        $userRoleId = session('role_id');
        if ($document['createdby'] != $userId && !in_array($userRoleId, [1])) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('You do not have access to superseded documents.');
        }
    }

    $revision = $this->documentRevisionModel
        ->where('document_id', $documentId)
        ->orderBy('id', 'DESC')
        ->first();

    if (!$revision || empty($revision['filepath'])) {
        throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('File not found.');
    }

    $userRoleId = session('role_id');
    $allowedRoleIds = [1, 2];
    
    if (!in_array($userRoleId, $allowedRoleIds) && $document['createdby'] != $userId) {
        throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('You do not have access to this file.');
    }

    $filePath = ROOTPATH . '../' . $revision['filepath'];
    if (!file_exists($filePath)) {
        throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('File not found on server.');
    }

    $file = new File($filePath);
    $mimeType = $file->getMimeType() ?: 'application/octet-stream';
    $disposition = ($action === 'download') ? 'attachment' : 'inline';

    return $this->response
        ->setHeader('Content-Type', $mimeType)
        ->setHeader('Content-Disposition', $disposition . '; filename="' . $revision['filename'] . '"')
        ->setBody(file_get_contents($filePath));
}

private function createDocumentNotification($documentId, $documentTitle, $documentTypeName, $action = 'updated')
{
    try {
        $updaterId = session('user_id');
        $updaterName = session('fullname') ?? session('username') ?? 'User';
        $updaterUnitId = session('unit_id');
        $updaterUnitParentId = session('unit_parent_id');
        $updaterRole = $this->userModel
            ->select('role.access_level')
            ->join('user_role', 'user_role.user_id = user.id', 'left')
            ->join('role', 'role.id = user_role.role_id', 'left')
            ->where('user.id', $updaterId)
            ->where('user.status', 1)
            ->first();

        $updaterAccessLevel = $updaterRole['access_level'] ?? 0;

        log_message('debug', "Creating update notification - Document ID: $documentId, Updater ID: $updaterId, Updater Name: $updaterName, Unit ID: $updaterUnitId, Unit Parent ID: $updaterUnitParentId, Access Level: $updaterAccessLevel");

        $message = "Document '{$documentTitle}' ({$documentTypeName}) has been {$action} by {$updaterName}";
        
        $notificationData = [
            'message' => $message,
            'reference_id' => $documentId,
            'createdby' => $updaterId,
            'createddate' => date('Y-m-d H:i:s')
        ];
        
        $notificationId = $this->notificationModel->insert($notificationData);
        
        if (!$notificationId) {
            log_message('error', 'Failed to create update notification: ' . json_encode($this->notificationModel->errors()));
            return false;
        }

        log_message('debug', "Update notification created with ID: $notificationId");

        // Define recipients based on updater's access level
        $recipients = [];
        if ($updaterAccessLevel == 1) {
            // For access_level 1, include the updater themselves
            $recipients = $this->userModel
                ->select('user.*')
                ->join('user_role', 'user_role.user_id = user.id', 'left')
                ->join('role', 'role.id = user_role.role_id', 'left')
                ->join('unit', 'unit.id = user.unit_id', 'left')
                ->where('user.status', 1)
                ->where('role.access_level', 1)
                ->where('user.unit_id', $updaterUnitId)
                ->where('unit.parent_id', $updaterUnitParentId)
                ->findAll();
        } elseif ($updaterAccessLevel == 2) {
            // For access_level 2, only notify users with access_level 1 in the same unit and parent
            $recipients = $this->userModel
                ->select('user.*')
                ->join('user_role', 'user_role.user_id = user.id', 'left')
                ->join('role', 'role.id = user_role.role_id', 'left')
                ->join('unit', 'unit.id = user.unit_id', 'left')
                ->where('user.id !=', $updaterId)
                ->where('user.status', 1)
                ->where('role.access_level', 1)
                ->where('user.unit_id', $updaterUnitId)
                ->where('unit.parent_id', $updaterUnitParentId)
                ->findAll();
        }

        log_message('debug', "Recipients found: " . count($recipients));
        log_message('debug', "Recipients data: " . json_encode($recipients));
        
        if (empty($recipients)) {
            log_message('warning', 'No recipients found matching criteria: access_level=1, unit_id=' . $updaterUnitId . ', unit_parent_id=' . $updaterUnitParentId);
        }
        
        $successCount = 0;
        $errorCount = 0;
        
        foreach ($recipients as $user) {
            $recipientData = [
                'notification_id' => $notificationId,
                'user_id' => $user['id'],
                'status' => 1 // Unread, aligned with CreateDokumenController
            ];
            
            $insertResult = $this->notificationRecipientsModel->insert($recipientData);
            
            if ($insertResult) {
                $successCount++;
                log_message('debug', "Successfully inserted recipient for user_id: " . $user['id']);
            } else {
                $errorCount++;
                log_message('error', "Failed to insert recipient for user_id: " . $user['id'] . " - Errors: " . json_encode($this->notificationRecipientsModel->errors()));
            }
        }

        log_message('info', "Update notification successfully created with ID: $notificationId. Success: $successCount, Errors: $errorCount");
        
        $savedRecipients = $this->notificationRecipientsModel
            ->where('notification_id', $notificationId)
            ->findAll();
        log_message('debug', "Saved recipients in database: " . json_encode($savedRecipients));

        return $notificationId;

    } catch (\Exception $e) {
        log_message('error', 'Error creating update notification: ' . $e->getMessage());
        log_message('error', 'Stack trace: ' . $e->getTraceAsString());
        return false;
    }
}


    
private function createApprovalNotification($documentId, $documentTitle, $documentTypeName, $action, $approverName, $remarks = '')
{
    try {
        $approverId = session('user_id');
        
        log_message('debug', "Creating approval notification - Document ID: $documentId, Approver: $approverName, Action: $action");
        
        $actionText = $action === 'approved' ? 'approved' : 'disapproved';
        $message = "Document '{$documentTitle}' ({$documentTypeName}) has been {$actionText} by {$approverName}";
        if (!empty($remarks)) {
            $message .= ". Remarks: {$remarks}";
        }
        
        $notificationData = [
            'message' => $message,
            'reference_id' => $documentId,
            'createdby' => $approverId,
            'createddate' => date('Y-m-d H:i:s')
        ];
        
        $notificationId = $this->notificationModel->insert($notificationData);
        
        if (!$notificationId) {
            log_message('error', 'Failed to create approval notification: ' . json_encode($this->notificationModel->errors()));
            return false;
        }

        log_message('debug', "Approval notification created with ID: $notificationId");

        $document = $this->documentModel->find($documentId);
        $documentCreatorId = $document['createdby'] ?? null;
        
        if (!$documentCreatorId) {
            log_message('warning', 'No creator found for document ID: ' . $documentId);
            return $notificationId;
        }

        $creator = $this->userModel
            ->where('id', $documentCreatorId)
            ->where('status', 1)
            ->first();
            
        if (!$creator) {
            log_message('warning', 'Creator not found or inactive for user_id: ' . $documentCreatorId);
            return $notificationId;
        }
        
        $recipientData = [
            'notification_id' => $notificationId,
            'user_id' => $documentCreatorId,
            'status' => 1 // Unread, aligned with CreateDokumenController
        ];
        
        $insertResult = $this->notificationRecipientsModel->insert($recipientData);
        
        if ($insertResult) {
            log_message('debug', "Successfully inserted approval notification recipient for creator_id: " . $documentCreatorId);
        } else {
            log_message('error', "Failed to insert approval notification recipient for creator_id: " . $documentCreatorId . " - Errors: " . json_encode($this->notificationRecipientsModel->errors()));
        }

        log_message('info', "Approval notification successfully created with ID: $notificationId for creator_id: $documentCreatorId");
        
        $savedRecipients = $this->notificationRecipientsModel
            ->where('notification_id', $notificationId)
            ->findAll();
        log_message('debug', "Saved recipients in database: " . json_encode($savedRecipients));

        return $notificationId;

    } catch (\Exception $e) {
        log_message('error', 'Error creating approval notification: ' . $e->getMessage());
        log_message('error', 'Stack trace: ' . $e->getTraceAsString());
        return false;
    }
}


    public function get_history() {
    $document_id = $this->request->getGet('id');
    if (!$document_id) {
        return $this->response->setJSON(['success' => false, 'message' => 'Invalid document ID']);
    }

    $revisionModel = new \App\Models\DocumentRevisionModel();
    $documentModel = new \App\Models\DocumentModel();

    $document = $documentModel->find($document_id);
    if (!$document) {
        return $this->response->setJSON(['success' => false, 'message' => 'Document not found']);
    }

    $history = $revisionModel->where('document_id', $document_id)->findAll();
    return $this->response->setJSON([
        'success' => true,
        'data' => [
            'document' => $document,
            'history' => $history
        ]
    ]);
}
}