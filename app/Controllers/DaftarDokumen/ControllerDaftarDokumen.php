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
use CodeIgniter\Database\ConnectionInterface;
use Config\Database;

class ControllerDaftarDokumen extends BaseController
{
    protected $documentModel;
    protected $typeModel;
    protected $standardModel;
    protected $clauseModel;
    protected $approvalModel;
    protected $revisionModel;
    protected $db;

    public function __construct()
    {
        $this->documentModel  = new DocumentModel();
        $this->typeModel      = new DocumentTypeModel();
        $this->standardModel  = new StandardModel();
        $this->clauseModel    = new ClauseModel();
        $this->approvalModel  = new DocumentApprovalModel();
        $this->revisionModel  = new DocumentRevisionModel();
        $this->db = Database::connect(); // Inisialisasi database secara langsung
    }

    public function index()
    {
        $document = $this->documentModel
            ->select('
                document.*,
                dt.name AS jenis_dokumen,
                dt.kode AS kode_jenis_dokumen,
                unit.name AS unit_name,
                unit_parent.name AS parent_name,
                kd.kode AS kode_dokumen_kode,
                kd.nama AS kode_dokumen_nama,
                document_approval.approvedate,
                document_approval.approveby,
                user_approver.fullname AS approved_by_name,
                document_revision.createdby AS revision_creator_id,
                user_creator.fullname AS pemilik,
                user_document_owner.fullname AS createdby_name,
                document.status,
                document.createdby,
                document_revision.filename,
                document_revision.filepath
            ')
            ->join('document_type dt', 'dt.id = document.type', 'left')
            ->join('unit', 'unit.id = document.unit_id', 'left')
            ->join('unit_parent', 'unit_parent.id = unit.parent_id', 'left')
            ->join('kode_dokumen kd', 'kd.id = document.kode_dokumen_id', 'left')
            ->join('document_approval', 'document_approval.document_id = document.id', 'left')
            ->join('user user_approver', 'user_approver.id = document_approval.approveby', 'left')
            ->join('document_revision', 'document_revision.document_id = document.id AND document_revision.id = (SELECT MAX(id) FROM document_revision WHERE document_id = document.id)', 'left')
            ->join('user user_creator', 'user_creator.id = document_revision.createdby', 'left')
            ->join('user user_document_owner', 'user_document_owner.id = document.createdby', 'left')
            ->where('document.status', 1)
            ->groupBy('document.id')
            ->findAll();

        $document = array_values($document);

        foreach ($document as &$doc) {
            $doc['createdby'] = $doc['createdby_name'] ?? $doc['createdby'];
        }

        $kategori_dokumen = $this->typeModel->findAll();
        $standards        = $this->standardModel->findAll();
        $clauses          = $this->clauseModel->getWithStandard();

        return view('DaftarDokumen/daftar_dokumen', [
            'title'            => 'Daftar Dokumen',
            'document'         => $document,
            'kategori_dokumen' => $kategori_dokumen,
            'standards'        => $standards,
            'clauses'          => $clauses,
        ]);
    }

    public function updateDokumen()
    {
        header('Content-Type: application/json'); // Ensure JSON response

        $validation = \Config\Services::validation();
        $validation->setRules([
            'id' => 'required|numeric',
            'standar' => 'required|is_array',
            'klausul' => 'required|is_array',
            'date_published' => 'required|valid_date',
            'approveby' => 'permit_empty|numeric', // Opsional, untuk sinkronisasi dengan document_approval
            'approvedate' => 'permit_empty|valid_date' // Opsional, untuk sinkronisasi dengan document_approval
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
        $standar = $this->request->getPost('standar') ?? [];
        $klausul = $this->request->getPost('klausul') ?? [];
        $datePublished = $this->request->getPost('date_published');
        $approveBy = $this->request->getPost('approveby') ?? null;
        $approveDate = $this->request->getPost('approvedate') ?? null;

        // Data untuk tabel document
        $dataDocument = [
            'standar_ids' => !empty($standar) ? implode(',', $standar) : '',
            'klausul_ids' => !empty($klausul) ? implode(',', $klausul) : '',
            'date_published' => $datePublished
        ];

        // Data untuk tabel document_approval
        $dataApproval = [
            'document_id' => $id,
            'standar_ids' => !empty($standar) ? implode(',', $standar) : '', // Sinkronisasi standar
            'klausul_ids' => !empty($klausul) ? implode(',', $klausul) : '', // Sinkronisasi klausul
            'approveby' => $approveBy,
            'approvedate' => $approveDate ? date('Y-m-d H:i:s', strtotime($approveDate)) : null
        ];

        log_message('debug', 'Update data for document ID ' . $id . ': ' . json_encode($dataDocument));
        log_message('debug', 'Update data for approval ID ' . $id . ': ' . json_encode($dataApproval));

        try {
            $existing = $this->documentModel->find($id);
            if (!$existing) {
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

            // Update document table
            $this->documentModel->update($id, $dataDocument);

            // Update or create approval record
            $existingApproval = $this->approvalModel->where('document_id', $id)->first();
            if ($existingApproval) {
                $this->approvalModel->update($existingApproval['id'], $dataApproval);
            } else {
                $this->approvalModel->insert($dataApproval);
            }

            if ($this->documentModel->affectedRows() > 0 || $this->approvalModel->affectedRows() > 0 || $this->approvalModel->getInsertID()) {
                log_message('info', 'Document ID ' . $id . ' and approval updated successfully.');
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
        if (!$id) {
            return redirect()->back()->with('error', 'ID dokumen tidak ditemukan.');
        }

        // Periksa apakah $this->db terinisialisasi
        if ($this->db === null) {
            log_message('error', 'Database connection is null in delete method for document ID: ' . $id);
            return redirect()->back()->with('error', 'Terjadi kesalahan koneksi database.');
        }

        try {
            // Mulai transaksi untuk memastikan konsistensi data
            $this->db->transStart();

            // Update status di tabel document menjadi 3
            $updatedDocument = $this->documentModel->update($id, [
                'status' => 3, // Soft delete untuk document
            ]);

            // Update status di tabel document_approval menjadi 0
            $existingApproval = $this->approvalModel->where('document_id', $id)->first();
            if ($existingApproval) {
                $this->approvalModel->update($existingApproval['id'], [
                    'status' => 0, // Soft delete untuk document_approval
                ]);
            }

            // Selesaikan transaksi
            $this->db->transComplete();

            if ($this->db->transStatus() === FALSE || !$updatedDocument) {
                return redirect()->back()->with('error', 'Dokumen tidak ditemukan atau gagal dihapus.');
            }

            return redirect()->back()->with('success', 'Dokumen berhasil dihapus (soft delete).');
        } catch (\Exception $e) {
            if ($this->db !== null) {
                $this->db->transRollback(); // Rollback jika ada error
            }
            log_message('error', 'Error deleting document ID ' . $id . ': ' . $e->getMessage());
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function serveFile()
    {
        $userId = session('user_id');
        if (!$userId) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Anda harus login untuk mengakses file.');
        }

        $documentId = $this->request->getGet('id');
        if (!$documentId) {
            log_message('error', 'No document ID provided in serveFile request');
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('ID dokumen tidak ditemukan.');
        }

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

        $file = new \CodeIgniter\Files\File($filePath);
        $mimeType = $file->getMimeType() ?: 'application/octet-stream';
        $action = $this->request->getGet('action') ?? 'view';
        $disposition = ($action === 'download') ? 'attachment' : 'inline';

        return $this->response
            ->setHeader('Content-Type', $mimeType)
            ->setHeader('Content-Disposition', $disposition . '; filename="' . ($revision['filename'] ?? basename($revision['filepath'])) . '"')
            ->setBody(file_get_contents($filePath));
    }
}