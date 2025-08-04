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
        $this->documentCodeModel = new \App\Models\DocumentCodeModel(); 
        $this->documentRevisionModel = new \App\Models\DocumentRevisionModel();
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

        // Handle document code berdasarkan tipe dokumen
        $finalKodeDokumenId = null;
        if ($jenisId) {
            // Check apakah document type menggunakan predefined codes
            $documentType = $this->documentTypeModel->where('id', $jenisId)->where('status', 1)->first();
            if ($documentType && str_contains($documentType['description'] ?? '', '[predefined]')) {
                // Use predefined code
                if ($kodeDokumenId) {
                    $finalKodeDokumenId = $kodeDokumenId;
                }
            } else {
                // Handle custom code
                if ($kodeCustom && $namaCustom) {
                    // Check if custom code already exists
                    $existingKode = $this->kodeDokumenModel
                        ->where('document_type_id', $jenisId)
                        ->where('kode', strtoupper($kodeCustom))
                        ->where('nama', $namaCustom)
                        ->first();
                    
                    if ($existingKode) {
                        $finalKodeDokumenId = $existingKode['id'];
                    } else {
                        // Create new custom code
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

            // NO NOTIFICATION FOR CREATE ACTION - REMOVED
            // Document creation does not generate notifications

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

        // Get original document
        $originalDocument = $this->documentModel->find($documentId);
        if (!$originalDocument) {
            return redirect()->back()->with('error', 'Document not found in database.');
        }

        $unitId = $originalDocument['unit_id'] ?? session()->get('unit_id') ?? 99;
        $originalDocumentId = $originalDocument['original_document_id'] ?? $documentId;

        // Handle document code dengan pengecekan tipe dokumen yang benar
        $finalKodeDokumenId = null;
        $documentType = null;
        
        if ($jenisId) {
            // Check apakah document type menggunakan predefined codes
            $documentType = $this->documentTypeModel->where('id', $jenisId)->where('status', 1)->first();
            if ($documentType && str_contains($documentType['description'] ?? '', '[predefined]')) {
                // Use predefined code
                if ($kodeDokumenId) {
                    $kodeDokumen = $this->kodeDokumenModel->where('id', $kodeDokumenId)->where('status', 1)->first();
                    if ($kodeDokumen) {
                        $finalKodeDokumenId = $kodeDokumenId;
                    }
                }
            } else {
                // Handle custom code
                if ($kodeCustom && $namaCustom) {
                    // Check if custom code already exists for this document type
                    $existingKode = $this->kodeDokumenModel
                        ->where('document_type_id', $jenisId)
                        ->where('kode', strtoupper($kodeCustom))
                        ->where('nama', $namaCustom)
                        ->first();
                
                    if ($existingKode) {
                        $finalKodeDokumenId = $existingKode['id'];
                    } else {
                        // Create new custom code
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

        // Prepare document data
        $data = [
            'unit_id' => $unitId,
            'status' => 0,
            'createddate' => date('Y-m-d H:i:s'),
            'createdby' => session('user_id'),
            'original_document_id' => $originalDocumentId,
        ];

        // Add non-empty fields only
        if ($jenisId) $data['type'] = $jenisId;
        if ($finalKodeDokumenId) $data['kode_dokumen_id'] = $finalKodeDokumenId;
        if ($nomor) $data['number'] = $nomor;
        if ($revisi) $data['revision'] = $revisi;
        if ($nama) $data['title'] = $nama;
        if ($keterangan) $data['description'] = $keterangan;

        try {
            $this->documentModel->insert($data);
            $newDocumentId = $this->documentModel->getInsertID();

            // Handle file upload
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
                    'remark' => $keterangan ?: '',
                    'createddate' => date('Y-m-d H:i:s'),
                    'createdby' => session('user_id'),
                ]);
            } else {
                // Copy file from original document if no new file uploaded
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
                        'remark' => $keterangan ?: $oldRevision['remark'],
                        'createddate' => date('Y-m-d H:i:s'),
                        'createdby' => session('user_id'),
                    ]);
                }
            }

            // Mark original document as superseded
            $this->documentModel->update($documentId, [
                'status' => 3,
            ]);

            // CREATE NOTIFICATION FOR UPDATE ACTION
            $documentTypeName = $documentType['name'] ?? 'Unknown Type';
            $documentTitle = $nama ?: $originalDocument['title'];
            $this->createDocumentNotification($newDocumentId, $documentTitle, $documentTypeName);

            return redirect()->to('document-submission-list')->with('success', 'Document successfully updated.');
        } catch (\Exception $e) {
            log_message('error', 'Error updating document: ' . $e->getMessage());
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

            // NO NOTIFICATION FOR DELETE ACTION
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

        log_message('debug', 'Received data - document_id: ' . $document_id . ', approved_by: ' . $approved_by . ', remarks: ' . $remarks . ', action: ' . $action);

        if (!$document_id || !$approved_by) {
            log_message('error', 'Missing required fields: document_id or approved_by');
            return redirect()->back()->with('error', 'Required data is incomplete.');
        }

        $validActions = ['approve', 'disapprove'];
        if (!in_array(strtolower($action), $validActions)) {
            log_message('error', 'Invalid action received: ' . $action);
            return redirect()->back()->with('error', 'Invalid action. Action received: ' . $action);
        }

        $status = strtolower($action) === 'approve' ? 1 : 2;

        // Get document details for notification
        $document = $this->documentModel
            ->select('document.*, dt.name AS document_type_name')
            ->join('document_type dt', 'dt.id = document.type', 'left')
            ->where('document.id', $document_id)
            ->first();

        if (!$document) {
            log_message('error', 'Document not found for ID: ' . $document_id);
            return redirect()->back()->with('error', 'Document not found.');
        }

        // Get approver details
        $approver = $this->userModel->find($approved_by);
        $approverName = $approver['fullname'] ?? $approver['username'] ?? 'Unknown User';

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
                throw new \Exception('Transaction failed.');
            }

            // CREATE NOTIFICATION FOR APPROVAL ACTION
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

    // FIXED: Updated showList method that matches your actual database structure
    private function showList()
    {
        $unitParentModel = new \App\Models\UnitParentModel();

        // Enhanced query to include creator information and hierarchical data
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
            ->join('document_revision dr', 'dr.document_id = document.id', 'left')
            ->join('user creator', 'creator.id = document.createdby', 'left')
            ->join('unit creator_unit', 'creator_unit.id = creator.unit_id', 'left')
            ->join('unit_parent creator_unit_parent', 'creator_unit_parent.id = creator_unit.parent_id', 'left')
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

        $kode_dokumen_by_type = $this->getKodeDokumenByType();

        $data = [
            'documents' => $documents,
            'jenis_dokumen' => $jenis_dokumen,
            'kategori_dokumen' => $kategori_dokumen,
            'kode_nama_dokumen' => $kode_nama_dokumen,
            'fakultas_list' => $fakultas_list,
            'kode_dokumen_by_type' => $kode_dokumen_by_type,
            'title' => 'Document Submission List'
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
                'message' => 'Document not found'
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

        $revision = $this->documentRevisionModel
            ->where('document_id', $documentId)
            ->orderBy('id', 'DESC')
            ->first();

        if (!$revision || empty($revision['filepath'])) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('File not found.');
        }

        $document = $this->documentModel->find($documentId);
        
        // Menggunakan role_id dari session untuk pengecekan akses
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

    /**
     * Create notification for document updates - ENGLISH MESSAGES
     * Only users with access_level=1, same unit_id, and same unit_parent_id receive notifications
     */
    private function createDocumentNotification($documentId, $documentTitle, $documentTypeName, $action = 'updated')
    {
        try {
            $updaterId = session('user_id');
            $updaterName = session('fullname') ?? session('username') ?? 'User';
            $updaterUnitId = session('unit_id');
            $updaterUnitParentId = session('unit_parent_id');
            
            log_message('debug', "Creating update notification - Document ID: $documentId, Updater ID: $updaterId, Updater Name: $updaterName, Unit ID: $updaterUnitId, Unit Parent ID: $updaterUnitParentId");

            // Create notification message - ENGLISH ONLY
            $message = "Document '{$documentTitle}' ({$documentTypeName}) has been {$action} by {$updaterName}";
            
            // Insert into notification table
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

            // Get users with access_level=1, same unit_id, and same unit_parent_id
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
            
            log_message('debug', "Recipients found: " . count($recipients));
            log_message('debug', "Recipients data: " . json_encode($recipients));
            
            if (empty($recipients)) {
                log_message('warning', 'No recipients found matching criteria: access_level=1, unit_id=' . $updaterUnitId . ', unit_parent_id=' . $updaterUnitParentId);
            }
            
            // Insert into notification_recipients table for each user
            $successCount = 0;
            $errorCount = 0;
            
            foreach ($recipients as $user) {
                $recipientData = [
                    'notification_id' => $notificationId,
                    'user_id' => $user['id'],
                    'status' => 0 // 0 = unread, 1 = read
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
            
            return $notificationId;

        } catch (\Exception $e) {
            log_message('error', 'Error creating update notification: ' . $e->getMessage());
            log_message('error', 'Stack trace: ' . $e->getTraceAsString());
            return false;
        }
    }

    /**
     * Create notification for document approval/disapproval - ENGLISH MESSAGES
     * Only the document creator receives the notification
     */
    private function createApprovalNotification($documentId, $documentTitle, $documentTypeName, $action, $approverName, $remarks = '')
    {
        try {
            $approverId = session('user_id');
            
            log_message('debug', "Creating approval notification - Document ID: $documentId, Approver: $approverName, Action: $action");
            
            // Create notification message - ENGLISH ONLY
            $actionText = $action === 'approved' ? 'approved' : 'disapproved';
            $message = "Document '{$documentTitle}' ({$documentTypeName}) has been {$actionText} by {$approverName}";
            if (!empty($remarks)) {
                $message .= ". Remarks: {$remarks}";
            }
            
            // Insert into notification table
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

            // Get document creator
            $document = $this->documentModel->find($documentId);
            $documentCreatorId = $document['createdby'] ?? null;
            
            if (!$documentCreatorId) {
                log_message('warning', 'No creator found for document ID: ' . $documentId);
                return $notificationId;
            }

            // Verify creator exists and is active
            $creator = $this->userModel
                ->where('id', $documentCreatorId)
                ->where('status', 1)
                ->first();
                
            if (!$creator) {
                log_message('warning', 'Creator not found or inactive for user_id: ' . $documentCreatorId);
                return $notificationId;
            }
            
            // Insert into notification_recipients table for document creator
            $recipientData = [
                'notification_id' => $notificationId,
                'user_id' => $documentCreatorId,
                'status' => 0 // 0 = unread, 1 = read
            ];
            
            $insertResult = $this->notificationRecipientsModel->insert($recipientData);
            
            if ($insertResult) {
                log_message('debug', "Successfully inserted approval notification recipient for creator_id: " . $documentCreatorId);
            } else {
                log_message('error', "Failed to insert approval notification recipient for creator_id: " . $documentCreatorId . " - Errors: " . json_encode($this->notificationRecipientsModel->errors()));
            }

            log_message('info', "Approval notification successfully created with ID: $notificationId for creator_id: $documentCreatorId");
            
            return $notificationId;

        } catch (\Exception $e) {
            log_message('error', 'Error creating approval notification: ' . $e->getMessage());
            log_message('error', 'Stack trace: ' . $e->getTraceAsString());
            return false;
        }
    }
}