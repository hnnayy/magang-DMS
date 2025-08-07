<?php

namespace App\Controllers\KelolaDokumen;

use App\Controllers\BaseController;
use App\Models\DocumentModel;
use App\Models\DocumentApprovalModel;
use App\Models\UserModel;
use App\Models\RoleModel;
use App\Models\NotificationModel;
use App\Models\NotificationRecipientsModel;
use CodeIgniter\Files\File;

class ControllerPersetujuan extends BaseController
{
    protected $documentModel;
    protected $approvalModel;
    protected $userModel;
    protected $roleModel;
    protected $notificationModel;
    protected $notificationRecipientsModel;
    protected $db;

    public function __construct()
    {
        $this->documentModel = new DocumentModel();
        $this->approvalModel = new DocumentApprovalModel();
        $this->userModel = new UserModel();
        $this->roleModel = new RoleModel();
        $this->notificationModel = new NotificationModel();
        $this->notificationRecipientsModel = new NotificationRecipientsModel();
        $this->db = \Config\Database::connect();
    }

    public function index()
    {
        $currentUserId = session()->get('user_id');
        $currentUserUnitId = session()->get('unit_id');
        $currentUserUnitParentId = session()->get('unit_parent_id');
        $currentUserRoleId = session()->get('role_id');
        $currentUserAccessLevel = session()->get('access_level') ?? 2;

        log_message('debug', "Current User Access Control - ID: {$currentUserId}, Unit: {$currentUserUnitId}, Parent: {$currentUserUnitParentId}, Access Level: {$currentUserAccessLevel}");

        $documentId = $this->request->getGet('document_id');

        $query = $this->documentModel
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
            ->where('document.createddate >', 0)
            ->orderBy('document.id', 'DESC');

        if ($documentId) {
            $query->where('document.id', $documentId);
        }

        $documents = $query->findAll();

        log_message('debug', 'Total documents fetched from database: ' . count($documents));

        $filteredDocuments = [];
        foreach ($documents as $doc) {
            $documentCreatorId = $doc['createdby'] ?? 0;
            $documentCreatorUnitId = $doc['creator_unit_id'] ?? 0;
            $documentCreatorUnitParentId = $doc['creator_unit_parent_id'] ?? 0;
            $documentCreatorAccessLevel = $doc['creator_access_level'] ?? 2;

            // Skip documents with invalid creator ID
            if ($documentCreatorId == 0) {
                log_message('debug', "Document {$doc['id']}: Invalid creator ID - Skipped");
                continue;
            }

            $canViewDocument = false;
            $canEditDocument = false;
            $canDeleteDocument = false;

            // Rule 1: Users can always see and modify their own documents
            if ($documentCreatorId == $currentUserId) {
                $canViewDocument = true;
                $canEditDocument = true;
                $canDeleteDocument = true;
                log_message('debug', "Document {$doc['id']}: Own document - Full access granted");
            } 
            // Rule 2: Higher level users can see and modify documents in same hierarchy
            elseif ($currentUserAccessLevel < $documentCreatorAccessLevel) {
                $sameUnit = ($documentCreatorUnitId == $currentUserUnitId);
                $sameUnitParent = ($documentCreatorUnitParentId == $currentUserUnitParentId);
                $creatorIsSubordinate = ($documentCreatorUnitParentId == $currentUserUnitId);
                $inSameHierarchy = $sameUnit || $sameUnitParent || $creatorIsSubordinate;

                if ($inSameHierarchy) {
                    $canViewDocument = true;
                    $canEditDocument = true;
                    $canDeleteDocument = true;
                    log_message('debug', "Document {$doc['id']}: Higher level access - Full access granted");
                } else {
                    log_message('debug', "Document {$doc['id']}: Different hierarchy - Access denied");
                }
            } 
            // Rule 3: Level 2 users can only see their own documents
            elseif ($currentUserAccessLevel == 2) {
                log_message('debug', "Document {$doc['id']}: Level 2 user - Access denied unless creator");
            }

            if ($canViewDocument) {
                // Add permission flags to document array
                $doc['can_edit'] = $canEditDocument;
                $doc['can_delete'] = $canDeleteDocument;
                $filteredDocuments[] = $doc;
            }
        }

        log_message('debug', 'Documents accessible to current user: ' . count($filteredDocuments));

        return view('KelolaDokumen/dokumen_persetujuan', [
            'documents' => $filteredDocuments,
            'title' => 'Document Approval'
        ]);
    }

    public function update()
    {
        $id = $this->request->getPost('document_id');
        $currentUserId = session()->get('user_id');
        $currentUserAccessLevel = session()->get('access_level') ?? 2;

        if (!$id || !$this->documentModel->find($id)) {
            return $this->response->setJSON([
                'status' => 'error',
                'error' => 'Document not found.'
            ]);
        }

        $document = $this->getDocumentWithCreatorInfo($id);
        if (!$document) {
            return $this->response->setJSON([
                'status' => 'error',
                'error' => 'Document not found.'
            ]);
        }

        $canUpdate = $this->canUserModifyDocument($document, $currentUserId, $currentUserAccessLevel);
        if (!$canUpdate) {
            return $this->response->setJSON([
                'status' => 'error',
                'error' => 'You do not have permission to update this document.'
            ]);
        }

        // Validate input
        $title = trim($this->request->getPost('title'));
        $revision = trim($this->request->getPost('revision'));
        $remark = trim($this->request->getPost('remark'));

        if (empty($title) || empty($revision)) {
            return $this->response->setJSON([
                'status' => 'error',
                'error' => 'Title and Revision fields are required.'
            ]);
        }

        $updateData = [
            'title' => $title,
            'revision' => $revision,
            'updatedby' => $currentUserId,
            'updateddate' => time()
        ];

        $updated = $this->documentModel->update($id, $updateData);
        
        if (!$updated) {
            return $this->response->setJSON([
                'status' => 'error',
                'error' => 'Failed to update document.'
            ]);
        }

        $approvalUpdated = $this->approvalModel
            ->where('document_id', $id)
            ->set([
                'remark' => $remark,
                'updatedby' => $currentUserId,
                'updateddate' => time()
            ])
            ->update();

        if (!$approvalUpdated) {
            log_message('warning', "Document {$id} updated but approval remark update failed");
        }

        $this->createNotification(
            "Document '{$title}' has been updated by " . session()->get('fullname'),
            $id,
            $currentUserId,
            [$document['createdby']]
        );

        log_message('info', "Document {$id} updated by user {$currentUserId}");
        return $this->response->setJSON([
            'status' => 'success',
            'message' => 'Document updated successfully.'
        ]);
    }

    public function delete()
    {
        $id = $this->request->getPost('document_id');
        $currentUserId = session()->get('user_id');
        $currentUserAccessLevel = session()->get('access_level') ?? 2;

        if (!$id || !$this->documentModel->find($id)) {
            return $this->response->setJSON([
                'status' => 'error',
                'error' => 'Document not found.'
            ]);
        }

        $document = $this->getDocumentWithCreatorInfo($id);
        if (!$document) {
            return $this->response->setJSON([
                'status' => 'error',
                'error' => 'Document not found.'
            ]);
        }

        $canDelete = $this->canUserModifyDocument($document, $currentUserId, $currentUserAccessLevel);
        if (!$canDelete) {
            return $this->response->setJSON([
                'status' => 'error',
                'error' => 'You do not have permission to delete this document.'
            ]);
        }

        $this->documentModel->update($id, [
            'createddate' => 0,
            'updatedby' => $currentUserId,
            'updateddate' => time()
        ]);

        $this->approvalModel
            ->where('document_id', $id)
            ->set([
                'status' => 0,
                'updatedby' => $currentUserId,
                'updateddate' => time()
            ])
            ->update();

        $this->createNotification(
            "Document '{$document['title']}' has been deleted by " . session()->get('fullname'),
            $id,
            $currentUserId,
            [$document['createdby']]
        );

        log_message('info', "Document {$id} deleted by user {$currentUserId}");
        return $this->response->setJSON([
            'status' => 'success',
            'message' => 'Document deleted successfully.'
        ]);
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

        $document = $this->getDocumentWithCreatorInfo($documentId);
        if (!$document) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Document not found.'
            ]);
        }

        $documentCreatorAccessLevel = $document['creator_access_level'] ?? 2;
        if ($currentUserAccessLevel >= $documentCreatorAccessLevel) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'You do not have sufficient access level to approve this document.'
            ]);
        }

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

        $approveData = [
            'status' => 2,
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
            $this->createNotification(
                "Document '{$document['title']}' has been approved by " . session()->get('fullname'),
                $documentId,
                $currentUserId,
                [$document['createdby']]
            );

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

        $document = $this->getDocumentWithCreatorInfo($documentId);
        if (!$document) {
            log_message('error', 'Document not found for ID: ' . $documentId);
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Document not found.');
        }

        $documentCreatorId = $document['createdby'] ?? 0;
        $documentCreatorUnitId = $document['creator_unit_id'] ?? 0;
        $documentCreatorUnitParentId = $document['creator_unit_parent_id'] ?? 0;
        $documentCreatorAccessLevel = $document['creator_access_level'] ?? 2;

        $canAccessFile = false;

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
        }

        if (!$canAccessFile) {
            log_message('warning', "User {$userId} attempted to access file for document {$documentId} without permission");
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('You do not have permission to access this file.');
        }

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

    private function canUserModifyDocument($document, $currentUserId, $currentUserAccessLevel)
    {
        $documentCreatorId = $document['createdby'] ?? 0;
        $documentCreatorAccessLevel = $document['creator_access_level'] ?? 2;

        // Can always modify own documents
        if ($documentCreatorId == $currentUserId) {
            return true;
        }

        // Higher level users can modify documents in same hierarchy
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

    private function createNotification($message, $referenceId, $createdBy, $recipientIds)
    {
        try {
            $notificationData = [
                'message' => $message,
                'reference_id' => $referenceId,
                'createdby' => $createdBy,
                'createddate' => date('Y-m-d H:i:s')
            ];

            $notificationId = $this->notificationModel->insert($notificationData);

            if (!$notificationId) {
                log_message('error', 'Failed to create notification: ' . json_encode($this->notificationModel->errors()));
                return false;
            }

            log_message('debug', "Notification created with ID: $notificationId");

            $successCount = 0;
            $errorCount = 0;

            foreach ($recipientIds as $recipientId) {
                $recipientData = [
                    'notification_id' => $notificationId,
                    'user_id' => $recipientId,
                    'status' => 1
                ];

                log_message('debug', "Inserting recipient: " . json_encode($recipientData));

                $insertResult = $this->notificationRecipientsModel->insert($recipientData);

                if ($insertResult) {
                    $successCount++;
                    log_message('debug', "Successfully inserted recipient for user_id: $recipientId");
                } else {
                    $errorCount++;
                    log_message('error', "Failed to insert recipient for user_id: $recipientId - Errors: " . json_encode($this->notificationRecipientsModel->errors()));
                }
            }

            log_message('info', "Notification created with ID: $notificationId. Success: $successCount, Errors: $errorCount");
            return $notificationId;
        } catch (\Exception $e) {
            log_message('error', 'Error creating notification: ' . $e->getMessage());
            return false;
        }
    }

    public function getUnreadCount()
    {
        $currentUserId = session()->get('user_id');
        $unreadCount = $this->notificationRecipientsModel
            ->where('user_id', $currentUserId)
            ->where('status', 1)
            ->countAllResults();

        return $this->response->setJSON([
            'status' => 'success',
            'count' => $unreadCount
        ]);
    }
}