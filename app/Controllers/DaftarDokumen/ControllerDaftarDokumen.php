<?php

namespace App\Controllers\DaftarDokumen;

use App\Controllers\BaseController;
use App\Models\DocumentModel;
use App\Models\DocumentTypeModel;
use App\Models\StandardModel;
use App\Models\ClauseModel;
use App\Models\DocumentApprovalModel;
use App\Models\DocumentRevisionModel;
use App\Models\UserModel;
use App\Models\RoleModel;
use CodeIgniter\Database\ConnectionInterface;
use Config\Database;
use CodeIgniter\Files\File;

class ControllerDaftarDokumen extends BaseController
{
    protected $documentModel;
    protected $typeModel;
    protected $standardModel;
    protected $clauseModel;
    protected $approvalModel;
    protected $revisionModel;
    protected $userModel;
    protected $roleModel;
    protected $db;

    public function __construct()
    {
        $this->documentModel  = new DocumentModel();
        $this->typeModel      = new DocumentTypeModel();
        $this->standardModel  = new StandardModel();
        $this->clauseModel    = new ClauseModel();
        $this->approvalModel  = new DocumentApprovalModel();
        $this->revisionModel  = new DocumentRevisionModel();
        $this->userModel      = new UserModel();
        $this->roleModel      = new RoleModel();
        $this->db = Database::connect();
    }

    public function index()
    {
        // Get current user information from session
        $currentUserId = session()->get('user_id');
        $currentUserUnitId = session()->get('unit_id');
        $currentUserUnitParentId = session()->get('unit_parent_id');
        $currentUserRoleId = session()->get('role_id');
        $currentUserAccessLevel = session()->get('access_level') ?? 2;

        log_message('debug', "Current User Access Control - ID: {$currentUserId}, Unit: {$currentUserUnitId}, Parent: {$currentUserUnitParentId}, Access Level: {$currentUserAccessLevel}");

        // Fetch all documents with complete creator information and organizational data
        $documents = $this->documentModel
            ->select('
                document.id,
                document.title,
                document.number,
                document.revision,
                document.date_published,
                document.createdby,
                document.type,
                document.unit_id,
                document.kode_dokumen_id,
                document.standar_ids,
                document.klausul_ids,
                document.status,
                dt.name AS jenis_dokumen,
                dt.kode AS kode_jenis_dokumen,
                unit.name AS unit_name,
                unit_parent.name AS parent_name,
                kd.kode AS kode_dokumen_kode,
                kd.nama AS kode_dokumen_nama,
                document_approval.approvedate,
                document_approval.approveby,
                user_approver.fullname AS approved_by_name,
                document_revision.filename,
                document_revision.filepath,
                creator.id AS createdby_id,
                creator.fullname AS creator_fullname,
                creator.fullname AS createdby_name,
                creator.unit_id AS creator_unit_id,
                creator_unit.parent_id AS creator_unit_parent_id,
                creator_role.access_level AS creator_access_level
            ')
            ->join('document_type dt', 'dt.id = document.type', 'left')
            ->join('unit', 'unit.id = document.unit_id', 'left')
            ->join('unit_parent', 'unit_parent.id = unit.parent_id', 'left')
            ->join('kode_dokumen kd', 'kd.id = document.kode_dokumen_id', 'left')
            ->join('document_approval', 'document_approval.document_id = document.id', 'left')
            ->join('user user_approver', 'user_approver.id = document_approval.approveby', 'left')
            ->join('document_revision', 'document_revision.document_id = document.id AND document_revision.id = (SELECT MAX(id) FROM document_revision WHERE document_id = document.id)', 'left')
            ->join('user AS creator', 'creator.id = document.createdby', 'left')
            ->join('unit AS creator_unit', 'creator_unit.id = creator.unit_id', 'left')
            ->join('user_role AS creator_user_role', 'creator_user_role.user_id = creator.id AND creator_user_role.status = 1', 'left')
            ->join('role AS creator_role', 'creator_role.id = creator_user_role.role_id', 'left')
            ->where('document.status', 1)
            ->where('document.createddate >', 0)
            ->groupBy('document.id')
            ->orderBy('document.id', 'DESC')
            ->findAll();

        log_message('debug', 'Total documents fetched from database: ' . count($documents));

        // Filter documents based on hierarchical access control
        $filteredDocuments = [];
        foreach ($documents as $doc) {
            $documentCreatorId = $doc['createdby_id'] ?? 0;
            $documentCreatorUnitId = $doc['creator_unit_id'] ?? 0;
            $documentCreatorUnitParentId = $doc['creator_unit_parent_id'] ?? 0;
            $documentCreatorAccessLevel = $doc['creator_access_level'] ?? 2;
            
            $canViewDocument = false;
            
            // Access Control Rules:
            // Rule 1: Users can always see their own documents
            if ($documentCreatorId == $currentUserId) {
                $canViewDocument = true;
                log_message('debug', "Document {$doc['id']}: Own document - Access granted");
            }
            // Rule 2: Higher level users (level 1) can see lower level documents in same hierarchy
            elseif ($currentUserAccessLevel < $documentCreatorAccessLevel) {
                $sameUnit = ($documentCreatorUnitId == $currentUserUnitId);
                $sameUnitParent = ($documentCreatorUnitParentId == $currentUserUnitParentId);
                $creatorIsSubordinate = ($documentCreatorUnitParentId == $currentUserUnitId);
                $inSameHierarchy = $sameUnit || $sameUnitParent || $creatorIsSubordinate;
                
                if ($inSameHierarchy) {
                    $canViewDocument = true;
                    log_message('debug', "Document {$doc['id']}: Higher level access - Creator Level {$documentCreatorAccessLevel}, Current Level {$currentUserAccessLevel} - Access granted");
                } else {
                    log_message('debug', "Document {$doc['id']}: Different hierarchy - Access denied");
                }
            }
            // Rule 3: Level 2 users can only see their own documents
            elseif ($currentUserAccessLevel == 2) {
                log_message('debug', "Document {$doc['id']}: Level 2 user can only see own documents - Access denied unless creator");
            }
            
            // Skip documents with invalid creator ID
            if ($documentCreatorId == 0) {
                log_message('debug', "Document {$doc['id']}: Invalid creator ID - Skipped");
                continue;
            }
            
            if ($canViewDocument) {
                $filteredDocuments[] = $doc;
            }
        }

        log_message('debug', 'Documents accessible to current user: ' . count($filteredDocuments));

        // Process documents to ensure proper data structure for the view
        foreach ($filteredDocuments as &$doc) {
            // Ensure createdby shows the creator's full name
            $doc['createdby'] = $doc['creator_fullname'] ?? $doc['createdby'] ?? 'Unknown User';
            
            // Ensure we have proper IDs for access control
            $doc['createdby_id'] = $doc['createdby_id'] ?? $doc['createdby'] ?? 0;
            
            // Set default values for missing data
            $doc['creator_unit_id'] = $doc['creator_unit_id'] ?? 0;
            $doc['creator_unit_parent_id'] = $doc['creator_unit_parent_id'] ?? 0;
            $doc['creator_access_level'] = $doc['creator_access_level'] ?? 2;
            
            log_message('debug', "Document {$doc['id']}: Creator ID {$doc['createdby_id']}, Unit {$doc['creator_unit_id']}, Parent {$doc['creator_unit_parent_id']}, Access Level {$doc['creator_access_level']}");
        }

        // Get additional data for the view
        $kategori_dokumen = $this->typeModel->findAll();
        $standards = $this->standardModel->findAll();
        $clauses = $this->clauseModel->getWithStandard();

        log_message('debug', 'Documents passed to view: ' . count($filteredDocuments));

        return view('DaftarDokumen/daftar_dokumen', [
            'title'            => 'Daftar Dokumen',
            'document'         => $filteredDocuments,
            'kategori_dokumen' => $kategori_dokumen,
            'standards'        => $standards,
            'clauses'          => $clauses,
        ]);
    }

    public function updateDokumen()
    {
        header('Content-Type: application/json');

        $validation = \Config\Services::validation();
        $validation->setRules([
            'id' => 'required|numeric',
            'standar' => 'required|is_array',
            'klausul' => 'required|is_array',
            'date_published' => 'required|valid_date',
            'approveby' => 'permit_empty|numeric',
            'approvedate' => 'permit_empty|valid_date'
        ]);

        if (!$validation->withRequest($this->request)->run()) {
            log_message('error', 'Validation failed: ' . json_encode($validation->getErrors()));
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Validasi gagal: ' . implode(', ', $validation->getErrors()),
                'swal' => [
                    'title' => 'Gagal!',
                    'text' => implode(', ', $validation->getErrors()),
                    'icon' => 'error',
                    'confirmButtonColor' => '#dc3545'
                ]
            ]);
        }

        $id = $this->request->getPost('id');
        $currentUserId = session()->get('user_id');
        $currentUserAccessLevel = session()->get('access_level') ?? 2;

        // Check if document exists
        $document = $this->getDocumentWithCreatorInfo($id);
        if (!$document) {
            log_message('error', 'Document not found for ID: ' . $id);
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Dokumen tidak ditemukan.',
                'swal' => [
                    'title' => 'Error',
                    'text' => 'Dokumen tidak ditemukan.',
                    'icon' => 'error',
                    'confirmButtonColor' => '#dc3545'
                ]
            ]);
        }

        // Check if user has permission to update this document
        $canUpdate = $this->canUserModifyDocument($document, $currentUserId, $currentUserAccessLevel);
        if (!$canUpdate) {
            log_message('warning', "User {$currentUserId} attempted to update document {$id} without permission");
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Anda tidak memiliki izin untuk mengubah dokumen ini.',
                'swal' => [
                    'title' => 'Error',
                    'text' => 'Anda tidak memiliki izin untuk mengubah dokumen ini.',
                    'icon' => 'error',
                    'confirmButtonColor' => '#dc3545'
                ]
            ]);
        }

        $standar = $this->request->getPost('standar') ?? [];
        $klausul = $this->request->getPost('klausul') ?? [];
        $datePublished = $this->request->getPost('date_published');
        $approveBy = $this->request->getPost('approveby') ?? null;
        $approveDate = $this->request->getPost('approvedate') ?? null;

        // Data untuk tabel document
        $dataDocument = [
            'standar_ids' => !empty($standar) ? implode(',', $standar) : '',
            'klausul_ids' => !empty($klausul) ? implode(',', $klausul) : '',
            'date_published' => $datePublished,
            'updatedby' => $currentUserId,
            'updateddate' => time()
        ];

        // Data untuk tabel document_approval
        $dataApproval = [
            'document_id' => $id,
            'standar_ids' => !empty($standar) ? implode(',', $standar) : '',
            'klausul_ids' => !empty($klausul) ? implode(',', $klausul) : '',
            'approveby' => $approveBy,
            'approvedate' => $approveDate ? date('Y-m-d H:i:s', strtotime($approveDate)) : null,
            'updatedby' => $currentUserId,
            'updateddate' => time()
        ];

        log_message('debug', 'Update data for document ID ' . $id . ': ' . json_encode($dataDocument));
        log_message('debug', 'Update data for approval ID ' . $id . ': ' . json_encode($dataApproval));

        try {
            // Start transaction
            $this->db->transStart();

            // Update document table
            $this->documentModel->update($id, $dataDocument);

            // Update or create approval record
            $existingApproval = $this->approvalModel->where('document_id', $id)->first();
            if ($existingApproval) {
                $this->approvalModel->update($existingApproval['id'], $dataApproval);
            } else {
                $this->approvalModel->insert($dataApproval);
            }

            // Complete transaction
            $this->db->transComplete();

            if ($this->db->transStatus() === FALSE) {
                log_message('error', 'Transaction failed for document ID ' . $id);
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => 'Terjadi kesalahan saat memperbarui dokumen.',
                    'swal' => [
                        'title' => 'Error',
                        'text' => 'Terjadi kesalahan saat memperbarui dokumen.',
                        'icon' => 'error',
                        'confirmButtonColor' => '#dc3545'
                    ]
                ]);
            }

            if ($this->documentModel->affectedRows() > 0 || $this->approvalModel->affectedRows() > 0 || $this->approvalModel->getInsertID()) {
                log_message('info', 'Document ID ' . $id . ' and approval updated successfully by user ' . $currentUserId);
                return $this->response->setJSON([
                    'status' => 'success',
                    'message' => 'Dokumen dan approval berhasil diperbarui.',
                    'swal' => [
                        'title' => 'Berhasil!',
                        'text' => 'Dokumen dan approval berhasil diperbarui.',
                        'icon' => 'success',
                        'confirmButtonColor' => '#6f42c1'
                    ]
                ]);
            } else {
                log_message('warning', 'No changes made for document ID ' . $id . ' (data may be identical).');
                return $this->response->setJSON([
                    'status' => 'warning',
                    'message' => 'Tidak ada perubahan pada dokumen.',
                    'swal' => [
                        'title' => 'Peringatan',
                        'text' => 'Tidak ada perubahan pada dokumen.',
                        'icon' => 'warning',
                        'confirmButtonColor' => '#ffc107'
                    ]
                ]);
            }
        } catch (\Exception $e) {
            // Rollback transaction on error
            $this->db->transRollback();
            log_message('error', 'Error updating document ID ' . $id . ': ' . $e->getMessage());
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Terjadi kesalahan: ' . $e->getMessage(),
                'swal' => [
                    'title' => 'Error',
                    'text' => 'Terjadi kesalahan saat memperbarui dokumen.',
                    'icon' => 'error',
                    'confirmButtonColor' => '#dc3545'
                ]
            ]);
        }
    }

    public function delete()
    {
        $id = $this->request->getPost('id');
        $currentUserId = session()->get('user_id');
        $currentUserAccessLevel = session()->get('access_level') ?? 2;

        if (!$id) {
            return redirect()->back()->with('error', 'ID dokumen tidak ditemukan.');
        }

        // Check if document exists
        $document = $this->getDocumentWithCreatorInfo($id);
        if (!$document) {
            return redirect()->back()->with('error', 'Dokumen tidak ditemukan.');
        }

        // Check if user has permission to delete this document
        $canDelete = $this->canUserModifyDocument($document, $currentUserId, $currentUserAccessLevel);
        if (!$canDelete) {
            log_message('warning', "User {$currentUserId} attempted to delete document {$id} without permission");
            return redirect()->back()->with('error', 'Anda tidak memiliki izin untuk menghapus dokumen ini.');
        }

        if ($this->db === null) {
            log_message('error', 'Database connection is null in delete method for document ID: ' . $id);
            return redirect()->back()->with('error', 'Terjadi kesalahan koneksi database.');
        }

        try {
            // Start transaction
            $this->db->transStart();

            // Soft delete: Update status di tabel document menjadi 3
            $this->documentModel->update($id, [
                'status' => 3,
                'updatedby' => $currentUserId,
                'updateddate' => time()
            ]);

            // Update status di tabel document_approval menjadi 0
            $existingApproval = $this->approvalModel->where('document_id', $id)->first();
            if ($existingApproval) {
                $this->approvalModel->update($existingApproval['id'], [
                    'status' => 0,
                    'updatedby' => $currentUserId,
                    'updateddate' => time()
                ]);
            }

            // Complete transaction
            $this->db->transComplete();

            if ($this->db->transStatus() === FALSE) {
                log_message('error', 'Transaction failed for document deletion ID ' . $id);
                return redirect()->back()->with('error', 'Terjadi kesalahan saat menghapus dokumen.');
            }

            log_message('info', "Document {$id} deleted by user {$currentUserId}");
            return redirect()->back()->with('success', 'Dokumen berhasil dihapus.');
        } catch (\Exception $e) {
            if ($this->db !== null) {
                $this->db->transRollback();
            }
            log_message('error', 'Error deleting document ID ' . $id . ': ' . $e->getMessage());
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function serveFile()
    {
        $userId = session('user_id');
        $currentUserAccessLevel = session()->get('access_level') ?? 2;
        $currentUserUnitId = session()->get('unit_id');
        $currentUserUnitParentId = session()->get('unit_parent_id');

        if (!$userId) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Anda harus login untuk mengakses file.');
        }

        $documentId = $this->request->getGet('id');
        if (!$documentId) {
            log_message('error', 'No document ID provided in serveFile request');
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('ID dokumen tidak ditemukan.');
        }

        // Get document with creator information
        $document = $this->getDocumentWithCreatorInfo($documentId);
        if (!$document) {
            log_message('error', 'Document not found for ID: ' . $documentId);
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Dokumen tidak ditemukan.');
        }

        // Check access permissions using the same logic as the view
        $documentCreatorId = $document['createdby'] ?? 0;
        $documentCreatorUnitId = $document['creator_unit_id'] ?? 0;
        $documentCreatorUnitParentId = $document['creator_unit_parent_id'] ?? 0;
        $documentCreatorAccessLevel = $document['creator_access_level'] ?? 2;

        $canAccessFile = false;

        // Access Control Rules:
        // Rule 1: Users can always access their own documents
        if ($documentCreatorId == $userId) {
            $canAccessFile = true;
        }
        // Rule 2: Higher level users can access lower level documents in same hierarchy
        elseif ($currentUserAccessLevel < $documentCreatorAccessLevel) {
            $sameUnit = ($documentCreatorUnitId == $currentUserUnitId);
            $sameUnitParent = ($documentCreatorUnitParentId == $currentUserUnitParentId);
            $creatorIsSubordinate = ($documentCreatorUnitParentId == $currentUserUnitId);
            $inSameHierarchy = $sameUnit || $sameUnitParent || $creatorIsSubordinate;
            
            if ($inSameHierarchy) {
                $canAccessFile = true;
            }
        }
        // Rule 3: Level 2 users can only access their own documents
        elseif ($currentUserAccessLevel == 2) {
            $canAccessFile = false;
        }

        if (!$canAccessFile) {
            log_message('warning', "User {$userId} attempted to access file for document {$documentId} without permission");
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Anda tidak memiliki izin untuk mengakses file ini.');
        }

        // Get file information
        $revision = $this->documentModel
            ->select('document_revision.filepath, document_revision.filename')
            ->join('document_revision', 'document_revision.document_id = document.id AND document_revision.id = (SELECT MAX(id) FROM document_revision WHERE document_id = document.id)', 'left')
            ->where('document.id', $documentId)
            ->first();

        log_message('debug', 'ServeFile data for ID ' . $documentId . ': ' . json_encode($revision));

        if (!$revision || empty($revision['filepath'])) {
            log_message('error', 'No revision data for document ID: ' . $documentId);
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('File tidak ditemukan.');
        }

        $filePath = ROOTPATH . '..' . DIRECTORY_SEPARATOR . $revision['filepath'];
        log_message('debug', 'ServeFile checking path: ' . $filePath . ', Exists: ' . (file_exists($filePath) ? 'true' : 'false'));

        if (!file_exists($filePath)) {
            log_message('error', 'File not found at: ' . $filePath);
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('File tidak ditemukan di server.');
        }

        if (!is_readable($filePath)) {
            log_message('error', 'File not readable at: ' . $filePath);
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('File tidak dapat diakses.');
        }

        $file = new File($filePath);
        $mimeType = $file->getMimeType() ?: 'application/octet-stream';
        $action = $this->request->getGet('action') ?? 'view';
        $disposition = ($action === 'download') ? 'attachment' : 'inline';

        log_message('info', "User {$userId} accessed file for document {$documentId}");

        return $this->response
            ->setHeader('Content-Type', $mimeType)
            ->setHeader('Content-Disposition', $disposition . '; filename="' . ($revision['filename'] ?? basename($revision['filepath'])) . '"')
            ->setHeader('Content-Length', filesize($filePath))
            ->setBody(file_get_contents($filePath));
    }

    /**
     * Get document with creator information
     */
    private function getDocumentWithCreatorInfo($documentId)
    {
        return $this->documentModel
            ->select('
                document.*,
                creator.id AS createdby_id,
                creator.fullname AS creator_fullname,
                creator.unit_id AS creator_unit_id,
                creator_unit.parent_id AS creator_unit_parent_id,
                creator_role.access_level AS creator_access_level
            ')
            ->join('user AS creator', 'creator.id = document.createdby', 'left')
            ->join('unit AS creator_unit', 'creator_unit.id = creator.unit_id', 'left')
            ->join('user_role AS creator_user_role', 'creator_user_role.user_id = creator.id AND creator_user_role.status = 1', 'left')
            ->join('role AS creator_role', 'creator_role.id = creator_user_role.role_id', 'left')
            ->where('document.id', $documentId)
            ->first();
    }

    /**
     * Check if current user can modify (update/delete) the document
     */
    private function canUserModifyDocument($document, $currentUserId, $currentUserAccessLevel)
    {
        $documentCreatorId = $document['createdby'] ?? 0;
        $documentCreatorAccessLevel = $document['creator_access_level'] ?? 2;
        
        // Users can always modify their own documents
        if ($documentCreatorId == $currentUserId) {
            return true;
        }
        
        // Higher level users can modify lower level documents in same hierarchy
        if ($currentUserAccessLevel < $documentCreatorAccessLevel) {
            $currentUserUnitId = session()->get('unit_id');
            $currentUserUnitParentId = session()->get('unit_parent_id');
            $documentCreatorUnitId = $document['creator_unit_id'] ?? 0;
            $documentCreatorUnitParentId = $document['creator_unit_parent_id'] ?? 0;
            
            $sameUnit = ($documentCreatorUnitId == $currentUserUnitId);
            $sameUnitParent = ($documentCreatorUnitParentId == $currentUserUnitParentId);
            $creatorIsSubordinate = ($documentCreatorUnitParentId == $currentUserUnitId);
            $inSameHierarchy = $sameUnit || $sameUnitParent || $creatorIsSubordinate;
            
            return $inSameHierarchy;
        }
        
        return false;
    }
}