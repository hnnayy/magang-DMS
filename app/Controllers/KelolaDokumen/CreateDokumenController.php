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
        $this->documentRevisionModel = new \App\Models\DocumentRevisionModel();
        $this->notificationModel = new NotificationModel();
        $this->notificationRecipientsModel = new NotificationRecipientsModel();
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
        log_message('debug', 'add - Method add dipanggil pada ' . date('Y-m-d H:i:s'));
        $unitId = session()->get('unit_id');
        if (!$unitId) {
            log_message('error', 'add - Session unit_id tidak ditemukan');
            return redirect()->to('/login')->with('error', 'Silakan login kembali.');
        }

        $unitModel = new UnitModel();
        $unitData = $unitModel
            ->select('unit.*, unit_parent.name as parent_name')
            ->join('unit_parent', 'unit_parent.id = unit.parent_id', 'left')
            ->where('unit.id', $unitId)
            ->first();

        if (!$unitData) {
            log_message('error', 'add - Unit ID ' . $unitId . ' tidak ditemukan');
            return redirect()->to('/login')->with('error', 'Unit tidak valid.');
        }

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

        $data['kategori_dokumen'] = $this->kategoriDokumen;
        $data['kode_dokumen_by_type'] = $groupedKodeDokumen;
        $data['kode_dokumen'] = $this->kodeDokumen;
        $data['unit'] = $unitData;

        return view('KelolaDokumen/dokumen-create', $data);
    }

    private function getApprovers($submenuId)
    {
        $db = \Config\Database::connect();
        $userId = session('user_id');

        $user = $db->table('user')
            ->select('unit_id')
            ->where('id', $userId)
            ->get()
            ->getRowArray();

        if (!$user || empty($user['unit_id'])) {
            log_message('error', 'getApprovers - Unit ID tidak ditemukan untuk user_id: ' . $userId);
            return [];
        }

        $query = $db->table('user')
            ->select('id')
            ->where('unit_id', $user['unit_id'])
            ->whereIn('role_id', [1, 2]) // Approver roles
            ->where('status', 1)
            ->where('id !=', $userId); // Exclude the creator

        log_message('debug', 'getApprovers - Query: ' . $query->getCompiledSelect() . ' - Count: ' . $query->countAllResults(false));
        $result = $query->get();

        if ($result === false) {
            log_message('error', 'getApprovers - Query failed: ' . $db->getLastQuery());
            return [];
        }

        $approvers = $result->getResultArray();
        log_message('debug', 'getApprovers - Approvers found: ' . json_encode($approvers));
        return array_column($approvers, 'id');
    }

    public function tambah()
    {
        if (!session('user_id')) {
            log_message('error', 'tambah - Session user_id tidak ditemukan');
            return redirect()->to('/login')->with('error', 'Silakan login kembali.');
        }

        log_message('debug', 'tambah - POST data: ' . json_encode($this->request->getPost()));
        $currentUserId = session('user_id');
        if (!$currentUserId) {
            log_message('error', 'tambah - Invalid or empty user_id from session');
            return redirect()->to('/login')->with('error', 'Sesi pengguna tidak valid.');
        }
        log_message('debug', 'tambah - Current user_id from session: ' . $currentUserId);

        $file = $this->request->getFile('file');
        if (!$file->isValid() || $file->hasMoved()) {
            log_message('error', 'tambah - Upload file gagal: ' . $file->getErrorString());
            return redirect()->back()->with('error', 'Upload file gagal: ' . $file->getErrorString());
        }

        $jenisId = $this->request->getPost('jenis');
        if (!$jenisId || $jenisId == "0" || $jenisId == "") {
            log_message('error', 'tambah - Jenis dokumen belum dipilih');
            return redirect()->back()->with('error', 'Jenis dokumen belum dipilih.');
        }

        $documentType = $this->documentTypeModel->find($jenisId);
        if (!$documentType) {
            log_message('error', 'tambah - Jenis dokumen ID ' . $jenisId . ' tidak ditemukan');
            return redirect()->back()->with('error', 'Jenis dokumen tidak valid.');
        }
        $usePredefined = str_contains($documentType['description'] ?? '', '[predefined]');

        $kodeDokumenId = null;
        if ($usePredefined) {
            $kodeDokumenId = $this->request->getPost('kode_dokumen_id');
            if (!$kodeDokumenId) {
                log_message('error', 'tambah - Kode dokumen belum dipilih');
                return redirect()->back()->with('error', 'Kode dokumen belum dipilih.');
            }
            $kodeExists = $this->kodeDokumenModel->find($kodeDokumenId);
            if (!$kodeExists) {
                log_message('error', 'tambah - Kode dokumen ID ' . $kodeDokumenId . ' tidak ditemukan');
                return redirect()->back()->with('error', 'Kode dokumen tidak valid.');
            }
        } else {
            $kodeCustom = $this->request->getPost('kode-dokumen-custom');
            $namaCustom = $this->request->getPost('nama-dokumen-custom');
            if (!$kodeCustom || !$namaCustom) {
                log_message('error', 'tambah - Kode dokumen dan nama dokumen custom wajib diisi');
                return redirect()->back()->with('error', 'Kode dokumen dan nama dokumen custom wajib diisi.');
            }

            $existingKode = $this->kodeDokumenModel
                ->where('document_type_id', $jenisId)
                ->where('kode', $kodeCustom)
                ->where('status', 1)
                ->first();

            if ($existingKode) {
                log_message('error', 'tambah - Kode dokumen "' . $kodeCustom . '" sudah ada');
                return redirect()->back()->with('error', 'Kode dokumen "' . $kodeCustom . '" sudah ada untuk jenis ini.');
            }

            $kodeDokumenData = [
                'document_type_id' => $jenisId,
                'kode' => $kodeCustom,
                'nama' => $namaCustom,
                'status' => 1,
                'createddate' => date('Y-m-d H:i:s'),
                'createdby' => $currentUserId
            ];

            if (!$this->kodeDokumenModel->insert($kodeDokumenData)) {
                log_message('error', 'tambah - Gagal insert kode_dokumen: ' . json_encode($this->kodeDokumenModel->errors()));
                return redirect()->back()->with('error', 'Gagal menyimpan kode dokumen: ' . json_encode($this->kodeDokumenModel->errors()));
            }
            $kodeDokumenId = $this->kodeDokumenModel->getInsertID();
        }

        $uploadPath = ROOTPATH . '../storage/uploads';
        if (!is_dir($uploadPath)) {
            mkdir($uploadPath, 0755, true);
        }

        $newName = $file->getRandomName();
        if (!$file->move($uploadPath, $newName)) {
            log_message('error', 'tambah - Gagal memindahkan file ke ' . $uploadPath . ': ' . $file->getErrorString());
            return redirect()->back()->with('error', 'Gagal menyimpan file: ' . $file->getErrorString());
        }

        $unitId = $this->request->getPost('unit_id') ?? 99;
        $unitModel = new \App\Models\UnitModel();
        $unitData = $unitModel->select('parent_id')->where('id', $unitId)->first();
        if (!$unitData) {
            log_message('error', 'tambah - Unit ID ' . $unitId . ' tidak ditemukan');
            return redirect()->back()->with('error', 'Unit tidak valid.');
        }

        $this->documentModel->db->transStart();

        try {
            $result = $this->documentModel->db->query('SHOW TABLE STATUS LIKE "document"')->getRow();
            if (!$result) {
                throw new \Exception('Gagal mendapatkan auto_increment untuk tabel document');
            }
            $nextId = $result->Auto_increment;

            $namaDokumen = $usePredefined ? 
                $this->request->getPost('nama-dokumen') : 
                $this->request->getPost('nama-dokumen-custom');

            if (empty($namaDokumen)) {
                throw new \Exception('Nama dokumen tidak boleh kosong');
            }

            $noDokumen = $this->request->getPost('no-dokumen');
            $datePublished = $this->request->getPost('date_published');
            if (empty($noDokumen) || empty($datePublished)) {
                throw new \Exception('Nomor dokumen dan tanggal publikasi wajib diisi');
            }

            $documentData = [
                'type' => $jenisId,
                'kode_dokumen_id' => $kodeDokumenId,
                'number' => $noDokumen,
                'date_published' => $datePublished,
                'revision' => $this->request->getPost('revisi') ?? 'Rev. 0',
                'title' => $namaDokumen,
                'description' => $this->request->getPost('keterangan') ?? '',
                'unit_id' => $unitId,
                'status' => 0,
                'createddate' => date('Y-m-d H:i:s'),
                'createdby' => $currentUserId,
                'original_document_id' => $nextId,
            ];

            if (!$this->documentModel->insert($documentData)) {
                throw new \Exception('Gagal insert document: ' . json_encode($this->documentModel->errors()));
            }
            $documentId = $this->documentModel->getInsertID();

            $revisionData = [
                'document_id' => $documentId,
                'revision' => $this->request->getPost('revisi') ?? 'Rev. 0',
                'filename' => $file->getClientName(),
                'filepath' => 'storage/uploads/' . $newName,
                'filesize' => $file->getSize(),
                'remark' => $this->request->getPost('keterangan') ?? '',
                'createddate' => date('Y-m-d H:i:s'),
                'createdby' => $currentUserId,
            ];

            if (!$this->documentRevisionModel->insert($revisionData)) {
                throw new \Exception('Gagal insert document_revision: ' . json_encode($this->documentRevisionModel->errors()));
            }

            $submenuId = 1; // Replace with logic to determine submenu_id
            $notificationData = [
                'submenu_id' => $submenuId,
                'reference_id' => $documentId,
                'message' => 'Dokumen baru "' . $namaDokumen . '" ditambahkan, menunggu persetujuan.',
                'createdby' => $currentUserId,
                'createddate' => date('Y-m-d H:i:s'),
            ];

            log_message('debug', 'tambah - Notification data before insert: ' . json_encode($notificationData));
            $notificationInsert = $this->notificationModel->insert($notificationData);
            if (!$notificationInsert) {
                throw new \Exception('Gagal insert notification: ' . json_encode($this->notificationModel->errors()));
            }

            $notificationId = $this->notificationModel->getInsertID();
            $approvers = $this->getApprovers($submenuId);

            if (!empty($approvers)) {
                foreach ($approvers as $userId) {
                    if ($userId === 0 || $userId === null) {
                        log_message('error', 'tambah - Invalid user_id detected: ' . $userId);
                        continue; // Skip invalid user IDs
                    }
                    $recipientData = [
                        'notification_id' => $notificationId,
                        'user_id' => $userId,
                        'status' => 0,
                    ];
                    log_message('debug', 'tambah - Inserting recipient for user_id: ' . $userId);
                    if (!$this->notificationRecipientsModel->insert($recipientData)) {
                        log_message('error', 'tambah - Failed to insert recipient for user_id ' . $userId . ': ' . json_encode($this->notificationRecipientsModel->errors()));
                        throw new \Exception('Gagal insert notification_recipients for user_id ' . $userId);
                    }
                }
            } else {
                log_message('info', 'tambah - No approvers found for submenu_id: ' . $submenuId);
            }

            $this->documentModel->db->transComplete();
            log_message('debug', 'tambah - Transaction status: ' . ($this->documentModel->db->transStatus() ? 'Success' : 'Failed'));

            if ($this->documentModel->db->transStatus() === false) {
                throw new \Exception('Transaksi gagal: ' . json_encode($this->documentModel->db->error()));
            }

            return redirect()->to('/create-document')->with('added_message', 'Dokumen berhasil ditambahkan.')->with('trigger_notification', true);
        } catch (\Exception $e) {
            $this->documentModel->db->transRollback();
            log_message('error', 'tambah - Error in tambah: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal menyimpan dokumen: ' . $e->getMessage());
        }
    }

    public function approval($id = null): string
    {
        if (!$id) {
            log_message('error', 'approval - No reference_id provided for approval');
            return redirect()->to('/document-submission-list')->with('error', 'Invalid document ID.');
        }

        $document = $this->documentModel->find($id);
        if (!$document) {
            log_message('error', 'approval - Document with ID ' . $id . ' not found');
            return redirect()->to('/document-submission-list')->with('error', 'Document not found.');
        }

        return redirect()->to('/document-submission-list?reference_id=' . $id);
    }
}