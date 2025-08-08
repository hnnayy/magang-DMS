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
use FPDF;

require_once ROOTPATH . 'vendor/autoload.php';

class CreateDokumenController extends BaseController
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
            ->select('kode_dokumen.*, document_type.name as jenis_nama, document_type.id as document_type_id')
            ->join('document_type', 'document_type.id = kode_dokumen.document_type_id')
            ->where('kode_dokumen.status', 1)
            ->orderBy('document_type.name', 'ASC')
            ->orderBy('kode_dokumen.kode', 'ASC')
            ->findAll();

        $groupedKodeDokumen = [];
        foreach ($kodeDokumen as $item) {
            $groupedKodeDokumen[$item['document_type_id']][] = [
                'id' => $item['id'],
                'kode' => $item['kode'],
                'nama' => $item['nama']
            ];
        }

        $grouped = [];
        foreach ($kodeDokumen as $item) {
            $grouped[$item['jenis_nama']][] = $item;
        }

        $data['kode_dokumen_by_type'] = $groupedKodeDokumen;
        $data['kode_dokumen'] = $grouped;
        $data['unit'] = $unitData;

        return view('KelolaDokumen/dokumen-create', $data);
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

        $documentType = $this->documentTypeModel->find($jenisId);
        $usePredefined = str_contains($documentType['description'] ?? '', '[predefined]');

        $kodeDokumenId = null;

        if ($usePredefined) {
            $kodeDokumenId = $this->request->getPost('kode_dokumen_id');
            if (!$kodeDokumenId) {
                return redirect()->back()->with('error', 'Kode dokumen belum dipilih.');
            }
        } else {
            $kodeCustom = $this->request->getPost('kode-dokumen-custom');
            $namaCustom = $this->request->getPost('nama-dokumen-custom');

            if (!$kodeCustom || !$namaCustom) {
                return redirect()->back()->with('error', 'Kode dokumen dan nama dokumen custom wajib diisi.');
            }

            $existingKode = $this->kodeDokumenModel
                ->where('document_type_id', $jenisId)
                ->where('kode', $kodeCustom)
                ->where('status', 1)
                ->first();

            if ($existingKode) {
                return redirect()->back()->with('error', 'Kode dokumen "' . $kodeCustom . '" sudah ada untuk jenis ini.');
            }

            $kodeDokumenData = [
                'document_type_id' => $jenisId,
                'kode' => $kodeCustom,
                'nama' => $namaCustom,
                'status' => 1,
                'createddate' => date('Y-m-d H:i:s'),
                'createdby' => session('user_id')
            ];

            $this->kodeDokumenModel->insert($kodeDokumenData);
            $kodeDokumenId = $this->kodeDokumenModel->getInsertID();
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

        try {
            $result = $this->documentModel->db->query('SHOW TABLE STATUS LIKE "document"')->getRow();
            $nextId = $result->Auto_increment;

            $namaDokumen = $usePredefined ? 
                $this->request->getPost('nama-dokumen') : 
                $this->request->getPost('nama-dokumen-custom');

            $this->documentModel->insert([
                'type' => $jenisId,
                'kode_dokumen_id' => $kodeDokumenId,
                'number' => $this->request->getPost('no-dokumen'),
                'date_published' => $this->request->getPost('date_published'),
                'revision' => $this->request->getPost('revisi') ?? 'Rev. 0',
                'title' => $namaDokumen,
                'description' => $this->request->getPost('keterangan'),
                'unit_id' => $unitId,
                'status' => 0,
                'createddate' => date('Y-m-d H:i:s'),
                'createdby' => session('user_id'),
                'original_document_id' => $nextId,
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
                throw new \Exception('Transaction failed');
            }

            $this->createDocumentNotification($documentId, $namaDokumen, $documentType['name']);

            return redirect()->to('/create-document')->with('added_message', 'Successfully Added.')->with('refresh_notif', true);

        } catch (\Exception $e) {
            $this->documentModel->db->transRollback();
            log_message('error', 'Error in tambah method: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to save document: ' . $e->getMessage());
        }
    }

    private function createDocumentNotification($documentId, $documentTitle, $documentTypeName)
{
    try {
        $creatorId = session('user_id');
        $creatorName = session('fullname') ?? session('username') ?? 'User';
        $creatorUnitId = session('unit_id');
        $creatorUnitParentId = session('unit_parent_id');

        log_message('debug', "Creating notification - Creator ID: $creatorId, Creator Name: $creatorName, Unit ID: $creatorUnitId, Unit Parent ID: $creatorUnitParentId");

        $message = "New document '{$documentTitle}' has been added by {$creatorName}";

        $notificationData = [
            'message' => $message,
            'reference_id' => $documentId,
            'createdby' => $creatorId,
            'createddate' => date('Y-m-d H:i:s')
        ];

        $notificationId = $this->notificationModel->insert($notificationData);

        if (!$notificationId) {
            log_message('error', 'Gagal membuat notifikasi: ' . json_encode($this->notificationModel->errors()));
            return false;
        }

        log_message('debug', "Notification created with ID: $notificationId");

        // Fetch users with access_level 1, same unit_id, and same unit_parent_id, excluding the creator
        $recipients = $this->userModel
            ->select('user.*')
            ->join('user_role', 'user_role.user_id = user.id', 'left')
            ->join('role', 'role.id = user_role.role_id', 'left')
            ->join('unit', 'unit.id = user.unit_id', 'left')
            ->where('user.id !=', $creatorId)
            ->where('user.status', 1)
            ->where('user_role.status', 1)
            ->where('role.access_level', 1)
            ->where('user.unit_id', $creatorUnitId)
            ->where('unit.parent_id', $creatorUnitParentId)
            ->findAll();

        log_message('debug', "Recipients found: " . count($recipients));
        log_message('debug', "Recipients data: " . json_encode($recipients));

        if (empty($recipients)) {
            log_message('warning', 'No recipients found.');
        }

        $successCount = 0;
        $errorCount = 0;

        foreach ($recipients as $user) {
            $recipientData = [
                'notification_id' => $notificationId,
                'user_id' => $user['id'],
                'status' => 1 // Unread, disesuaikan dengan frontend
            ];

            log_message('debug', "Inserting recipient: " . json_encode($recipientData));

            $insertResult = $this->notificationRecipientsModel->insert($recipientData);

            if ($insertResult) {
                $successCount++;
                log_message('debug', "Successfully inserted recipient for user_id: " . $user['id']);
            } else {
                $errorCount++;
                log_message('error', "Failed to insert recipient for user_id: " . $user['id'] . " - Errors: " . json_encode($this->notificationRecipientsModel->errors()));
            }
        }

        log_message('info', "Notifikasi dokumen berhasil dibuat dengan ID: $notificationId. Success: $successCount, Errors: $errorCount");

        $savedRecipients = $this->notificationRecipientsModel
            ->where('notification_id', $notificationId)
            ->findAll();
        log_message('debug', "Saved recipients in database: " . json_encode($savedRecipients));

        return $notificationId;

    } catch (\Exception $e) {
        log_message('error', 'Error creating document notification: ' . $e->getMessage());
        log_message('error', 'Stack trace: ' . $e->getTraceAsString());
        return false;
    }
}
    
}