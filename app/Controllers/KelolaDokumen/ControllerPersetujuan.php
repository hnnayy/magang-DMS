<?php

namespace App\Controllers\KelolaDokumen;

use App\Controllers\BaseController;
use App\Models\DocumentModel;
use App\Models\DocumentApprovalModel;
use App\Models\UserModel;
use App\Models\RoleModel;
use CodeIgniter\Files\File;

class ControllerPersetujuan extends BaseController
{
    protected $documentModel;
    protected $approvalModel;
    protected $userModel;
    protected $roleModel;

    public function __construct()
    {
        $this->documentModel = new DocumentModel();
        $this->approvalModel = new DocumentApprovalModel();
        $this->userModel = new UserModel();
        $this->roleModel = new RoleModel();
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

        // Fetch all documents with creator information and organizational data
        $documents = $this->documentModel
            ->select('
                document.id,
                document.title,
                document.revision,
                document.createdby,
                document_approval.remark,
                document_approval.id AS approval_id,
                document_approval.approvedate,
                document_type.name AS jenis_dokumen,
                CONCAT(kode_dokumen.kode, " - ", kode_dokumen.nama) AS kode_nama_dokumen,
                unit.name AS unit_name,
                unit_parent.name AS parent_name,
                document_revision.filename,
                document_revision.filepath,
                creator.fullname AS creator_fullname,
                creator.unit_id AS creator_unit_id,
                creator_unit.parent_id AS creator_unit_parent_id,
                creator_role.access_level AS creator_access_level
            ')
            ->join('document_approval', 'document.id = document_approval.document_id')
            ->join('document_type', 'document_type.id = document.type', 'left')
            ->join('kode_dokumen', 'kode_dokumen.id = document.kode_dokumen_id', 'left')
            ->join('unit', 'unit.id = document.unit_id', 'left')
            ->join('unit_parent', 'unit_parent.id = unit.parent_id', 'left')
            ->join('document_revision', 'document_revision.document_id = document.id AND document_revision.id = (SELECT MAX(id) FROM document_revision WHERE document_id = document.id)', 'left')
            ->join('user AS creator', 'creator.id = document.createdby', 'left')
            ->join('unit AS creator_unit', 'creator_unit.id = creator.unit_id', 'left')
            ->join('user_role AS creator_user_role', 'creator_user_role.user_id = creator.id AND creator_user_role.status = 1', 'left')
            ->join('role AS creator_role', 'creator_role.id = creator_user_role.role_id', 'left')
            ->where('document_approval.status', 1)
            ->where('document.status !=', 0)
            ->where('document.createddate >', 0) // Exclude soft deleted documents
            ->orderBy('document.id', 'DESC')
            ->findAll();

        // Log the raw query and results for debugging
        log_message('debug', 'Total documents fetched from database: ' . count($documents));
        
        // Filter documents based on hierarchical access control
        $filteredDocuments = [];
        foreach ($documents as $doc) {
            $documentCreatorId = $doc['createdby'] ?? 0;
            $documentCreatorUnitId = $doc['creator_unit_id'] ?? 0;
            $documentCreatorUnitParentId = $doc['creator_unit_parent_id'] ?? 0;
            $documentCreatorAccessLevel = $doc['creator_access_level'] ?? 2;
            
            $canViewDocument = false;
            
            // Access Control Rules Implementation:
            
            // Rule 1: Users can always see their own documents
            if ($documentCreatorId == $currentUserId) {
                $canViewDocument = true;
                log_message('debug', "Document {$doc['id']}: Own document - Access granted");
            }
            // Rule 2: Higher level users (level 1) can see lower level documents in same hierarchy
            elseif ($currentUserAccessLevel < $documentCreatorAccessLevel) {
                // Check organizational hierarchy
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

        return view('KelolaDokumen/dokumen_persetujuan', [
            'documents' => $filteredDocuments,
            'title'     => 'Document Approval'
        ]);
    }

    public function update()
    {
        $id = $this->request->getPost('document_id');
        $currentUserId = session()->get('user_id');
        $currentUserAccessLevel = session()->get('access_level') ?? 2;

        if (!$id || !$this->documentModel->find($id)) {
            return redirect()->back()->with('error', 'Document not found.');
        }

        // Check if user has permission to update this document
        $document = $this->getDocumentWithCreatorInfo($id);
        if (!$document) {
            return redirect()->back()->with('error', 'Document not found.');
        }

        $canUpdate = $this->canUserModifyDocument($document, $currentUserId, $currentUserAccessLevel);
        if (!$canUpdate) {
            return redirect()->back()->with('error', 'You do not have permission to update this document.');
        }

        // Update document
        $updateData = [
            'title'      => $this->request->getPost('title'),
            'revision'   => $this->request->getPost('revision'),
            'updatedby'  => $currentUserId,
            'updateddate'=> time()
        ];

        $this->documentModel->update($id, $updateData);

        // Update remark in approval table
        $this->approvalModel
            ->where('document_id', $id)
            ->set([
                'remark' => $this->request->getPost('remark'),
                'updatedby' => $currentUserId,
                'updateddate' => time()
            ])
            ->update();

        log_message('info', "Document {$id} updated by user {$currentUserId}");
        return redirect()->back()->with('updated_message', 'Document updated successfully.');
    }

    public function delete()
    {
        $id = $this->request->getPost('document_id');
        $currentUserId = session()->get('user_id');
        $currentUserAccessLevel = session()->get('access_level') ?? 2;

        if (!$id || !$this->documentModel->find($id)) {
            return redirect()->back()->with('error', 'Document not found.');
        }

        // Check if user has permission to delete this document
        $document = $this->getDocumentWithCreatorInfo($id);
        if (!$document) {
            return redirect()->back()->with('error', 'Document not found.');
        }

        $canDelete = $this->canUserModifyDocument($document, $currentUserId, $currentUserAccessLevel);
        if (!$canDelete) {
            return redirect()->back()->with('error', 'You do not have permission to delete this document.');
        }

        // Soft delete: update createddate to 0 in document table
        $this->documentModel->update($id, [
            'createddate' => 0,
            'updatedby' => $currentUserId,
            'updateddate' => time()
        ]);

        // Update status in document_approval to 0
        $this->approvalModel
            ->where('document_id', $id)
            ->set([
                'status' => 0,
                'updatedby' => $currentUserId,
                'updateddate' => time()
            ])
            ->update();

        log_message('info', "Document {$id} deleted by user {$currentUserId}");
        return redirect()->back()->with('deleted_message', 'Document deleted successfully.');
    }

    public function approve()
    {
        $documentId = $this->request->getPost('document_id');
        $currentUserId = session()->get('user_id');
        $currentUserAccessLevel = session()->get('access_level') ?? 2;

        if (!$documentId) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Document ID is required.'
            ]);
        }

        // Check if document exists
        $document = $this->getDocumentWithCreatorInfo($documentId);
        if (!$document) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Document not found.'
            ]);
        }

        // Check if user can approve this document (only higher level users can approve lower level documents)
        $documentCreatorAccessLevel = $document['creator_access_level'] ?? 2;
        if ($currentUserAccessLevel >= $documentCreatorAccessLevel) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'You do not have sufficient access level to approve this document.'
            ]);
        }

        // Check organizational hierarchy
        $currentUserUnitId = session()->get('unit_id');
        $currentUserUnitParentId = session()->get('unit_parent_id');
        $documentCreatorUnitId = $document['creator_unit_id'] ?? 0;
        $documentCreatorUnitParentId = $document['creator_unit_parent_id'] ?? 0;

        $sameUnit = ($documentCreatorUnitId == $currentUserUnitId);
        $sameUnitParent = ($documentCreatorUnitParentId == $currentUserUnitParentId);
        $creatorIsSubordinate = ($documentCreatorUnitParentId == $currentUserUnitId);
        $inSameHierarchy = $sameUnit || $sameUnitParent || $creatorIsSubordinate;

        if (!$inSameHierarchy) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'You can only approve documents from your organizational hierarchy.'
            ]);
        }

        // Update approval status
        $approveData = [
            'status' => 2, // Approved status
            'approvedate' => time(),
            'approvedby' => $currentUserId,
            'updatedby' => $currentUserId,
            'updateddate' => time()
        ];

        $updated = $this->approvalModel
            ->where('document_id', $documentId)
            ->set($approveData)
            ->update();

        if ($updated) {
            log_message('info', "Document {$documentId} approved by user {$currentUserId}");
            return $this->response->setJSON([
                'status' => 'success',
                'message' => 'Document approved successfully.'
            ]);
        } else {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Failed to approve document.'
            ]);
        }
    }

    public function serveFile()
    {
        $userId = session('user_id');
        $currentUserAccessLevel = session()->get('access_level') ?? 2;
        $currentUserUnitId = session()->get('unit_id');
        $currentUserUnitParentId = session()->get('unit_parent_id');

        if (!$userId) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('You must log in to access the file.');
        }

        $documentId = $this->request->getGet('id');
        if (!$documentId) {
            log_message('error', 'No document ID provided in serveFile request');
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Document ID not found.');
        }

        // Get document with creator information
        $document = $this->getDocumentWithCreatorInfo($documentId);
        if (!$document) {
            log_message('error', 'Document not found for ID: ' . $documentId);
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Document not found.');
        }

        // Check access permissions
        $documentCreatorId = $document['createdby'] ?? 0;
        $documentCreatorUnitId = $document['creator_unit_id'] ?? 0;
        $documentCreatorUnitParentId = $document['creator_unit_parent_id'] ?? 0;
        $documentCreatorAccessLevel = $document['creator_access_level'] ?? 2;

        $canAccessFile = false;

        // Same access control rules as in index method
        if ($documentCreatorId == $userId) {
            $canAccessFile = true;
        } elseif ($currentUserAccessLevel < $documentCreatorAccessLevel) {
            $sameUnit = ($documentCreatorUnitId == $currentUserUnitId);
            $sameUnitParent = ($documentCreatorUnitParentId == $currentUserUnitParentId);
            $creatorIsSubordinate = ($documentCreatorUnitParentId == $currentUserUnitId);
            $inSameHierarchy = $sameUnit || $sameUnitParent || $creatorIsSubordinate;
            
            if ($inSameHierarchy) {
                $canAccessFile = true;
            }
        } elseif ($currentUserAccessLevel == 2) {
            // Level 2 users can only access their own files
            $canAccessFile = false;
        }

        if (!$canAccessFile) {
            log_message('warning', "User {$userId} attempted to access file for document {$documentId} without permission");
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('You do not have permission to access this file.');
        }

        // Get file information
        $revision = $this->documentModel
            ->select('document_revision.filepath, document_revision.filename')
            ->join('document_revision', 'document_revision.document_id = document.id AND document_revision.id = (SELECT MAX(id) FROM document_revision WHERE document_id = document.id)', 'left')
            ->where('document.id', $documentId)
            ->first();

        if (!$revision || empty($revision['filepath'])) {
            log_message('error', 'No file data for document ID: ' . $documentId);
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('File not found.');
        }

        $filePath = ROOTPATH . '..' . DIRECTORY_SEPARATOR . $revision['filepath'];
        
        if (!file_exists($filePath)) {
            log_message('error', 'File not found at: ' . $filePath);
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('File not found on server.');
        }

        if (!is_readable($filePath)) {
            log_message('error', 'File not readable at: ' . $filePath);
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('File cannot be accessed.');
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