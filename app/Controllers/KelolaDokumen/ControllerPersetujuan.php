<?php

namespace App\Controllers\KelolaDokumen;

use App\Controllers\BaseController;
use App\Models\DocumentModel;
use App\Models\DocumentApprovalModel;
use CodeIgniter\Files\File;

class ControllerPersetujuan extends BaseController
{
    protected $documentModel;
    protected $approvalModel;

    public function __construct()
    {
        $this->documentModel = new DocumentModel();
        $this->approvalModel = new DocumentApprovalModel();
    }

    public function index()
    {
        $documents = $this->documentModel
            ->select('
                document.id,
                document.title,
                document.revision,
                document_approval.remark,
                document_approval.id AS approval_id,
                document_approval.approvedate,
                document_type.name AS jenis_dokumen,
                CONCAT(kode_dokumen.kode, " - ", kode_dokumen.nama) AS kode_nama_dokumen,
                unit.name AS unit_name,
                unit_parent.name AS parent_name,
                document_revision.filename,
                document_revision.filepath
            ')
            ->join('document_approval', 'document.id = document_approval.document_id')
            ->join('document_type', 'document_type.id = document.type', 'left')
            ->join('kode_dokumen', 'kode_dokumen.id = document.kode_dokumen_id', 'left')
            ->join('unit', 'unit.id = document.unit_id', 'left')
            ->join('unit_parent', 'unit_parent.id = unit.parent_id', 'left')
            ->join('document_revision', 'document_revision.document_id = document.id AND document_revision.id = (SELECT MAX(id) FROM document_revision WHERE document_id = document.id)', 'left')
            ->where('document_approval.status', 1)
            ->where('document.status !=', 0)
            ->findAll();

        log_message('debug', 'Documents fetched: ' . json_encode($documents));
        return view('KelolaDokumen/dokumen_persetujuan', [
            'documents' => $documents,
            'title'     => 'Persetujuan Dokumen'
        ]);
    }

    public function update()
    {
        $id = $this->request->getPost('document_id');

        if (!$id || !$this->documentModel->find($id)) {
            return redirect()->back()->with('error', 'Document not found.');
        }

        // Update document
        $this->documentModel->update($id, [
            'title'      => $this->request->getPost('title'),
            'revision'   => $this->request->getPost('revision'),
        ]);

        // Update remark from approval
        $this->approvalModel
            ->where('document_id', $id)
            ->set('remark', $this->request->getPost('remark'))
            ->update();

        return redirect()->back()->with('success', 'Document successfully updated.');
    }

    public function delete()
    {
        $id = $this->request->getPost('document_id');

        if (!$id || !$this->documentModel->find($id)) {
            return redirect()->back()->with('error', 'Document not found.');
        }

        // Soft delete: update createddate in document
        $this->documentModel->update($id, ['createddate' => 0]);

        // Update status document_approval to 0
        $this->approvalModel
            ->where('document_id', $id)
            ->set('status', 0)
            ->update();

        return redirect()->back()->with('success', 'Document successfully deleted.');
    }

    public function serveFile()
    {
        $userId = session('user_id');
        if (!$userId) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('You must log in to access the file.');
        }

        $documentId = $this->request->getGet('id'); // Ambil ID dari query string, misalnya ?id=1
        if (!$documentId) {
            log_message('error', 'No document ID provided in serveFile request');
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Document ID not found.');
        }

        $revision = $this->documentModel
            ->select('document_revision.filepath, document_revision.filename')
            ->join('document_revision', 'document_revision.document_id = document.id AND document_revision.id = (SELECT MAX(id) FROM document_revision WHERE document_id = document.id)', 'left')
            ->where('document.id', $documentId)
            ->first();

        log_message('debug', 'ServeFile data for ID ' . $documentId . ': ' . json_encode($revision));

        if (!$revision || empty($revision['filepath'])) {
            log_message('error', 'No revision data for document ID: ' . $documentId);
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('File not found.');
        }

        $filePath = ROOTPATH . '..' . DIRECTORY_SEPARATOR . $revision['filepath'];
        log_message('debug', 'ServeFile checking path: ' . $filePath . ', Exists: ' . (file_exists($filePath) ? 'true' : 'false'));

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

        return $this->response
            ->setHeader('Content-Type', $mimeType)
            ->setHeader('Content-Disposition', $disposition . '; filename="' . ($revision['filename'] ?? basename($revision['filepath'])) . '"')
            ->setBody(file_get_contents($filePath));
    }
}