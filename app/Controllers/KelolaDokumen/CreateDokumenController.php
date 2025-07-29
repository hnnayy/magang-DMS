<?php

namespace App\Controllers\KelolaDokumen;

use App\Controllers\BaseController;
use App\Models\DocumentCodeModel;
use App\Models\DocumentTypeModel;
use App\Models\DocumentModel;
use App\Models\UnitModel;
use App\Models\UnitParentModel;
use App\Models\DocumentApprovalModel;
use CodeIgniter\Files\File; 
use FPDF;
require_once ROOTPATH . 'vendor/autoload.php';

class CreateDokumenController extends BaseController
{
    protected $kategoriDokumen = [];
    protected $kodeDokumen = [];
    protected $documentModel;
    protected $documentRevisionModel;
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

        // Prepare data untuk view - menggunakan logika yang sama dengan controller lama
        $data['kategori_dokumen'] = $this->kategoriDokumen;
        
        // Siapkan kode dokumen berdasarkan kategori (sama seperti controller lama)
        $kodeDokumen = $this->kodeDokumenModel
            ->select('kode_dokumen.*, document_type.name as jenis_nama, document_type.id as document_type_id')
            ->join('document_type', 'document_type.id = kode_dokumen.document_type_id')
            ->where('kode_dokumen.status', 1)
            ->orderBy('document_type.name', 'ASC')
            ->orderBy('kode_dokumen.kode', 'ASC')
            ->findAll();

        // Group kode dokumen berdasarkan document_type_id untuk JavaScript
        $groupedKodeDokumen = [];
        foreach ($kodeDokumen as $item) {
            $groupedKodeDokumen[$item['document_type_id']][] = [
                'id' => $item['id'],
                'kode' => $item['kode'],
                'nama' => $item['nama']
            ];
        }
        
        // Juga buat grouping berdasarkan nama jenis (untuk kompatibilitas dengan view lama jika diperlukan)
        $grouped = [];
        foreach ($kodeDokumen as $item) {
            $grouped[$item['jenis_nama']][] = $item;
        }

        $data['kode_dokumen_by_type'] = $groupedKodeDokumen;
        $data['kode_dokumen'] = $grouped;
        $data['unit'] = $unitData;
        
        return view('KelolaDokumen/dokumen-create', $data);
    }

    // Method ini bisa dihapus karena tidak akan digunakan lagi
    public function getKodeDokumen()
    {
        $jenis = $this->request->getPost('jenis');

        $matchedType = array_filter($this->kategoriDokumen, fn($item) => $item['kode'] === $jenis);
        $matchedType = reset($matchedType);

        if (!$matchedType || !$matchedType['use_predefined_codes']) {
            return $this->response->setJSON([]);
        }
        $documentTypeId = $matchedType['id'];
        $results = $this->kodeDokumenModel
            ->where('document_type_id', $documentTypeId)
            ->where('status', 1)
            ->findAll();
        return $this->response->setJSON($results);
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

        // Cek apakah jenis dokumen menggunakan predefined codes atau tidak
        $documentType = $this->documentTypeModel->find($jenisId);
        $usePredefined = str_contains($documentType['description'] ?? '', '[predefined]');

        $kodeDokumenId = null;

        // Validasi dan handle kode dokumen berdasarkan jenis
        if ($usePredefined) {
            // Untuk predefined codes, ambil dari dropdown
            $kodeDokumenId = $this->request->getPost('kode_dokumen_id');
            if (!$kodeDokumenId) {
                return redirect()->back()->with('error', 'Kode dokumen belum dipilih.');
            }
        } else {
            // Untuk non-predefined codes, buat entry baru di tabel kode_dokumen
            $kodeCustom = $this->request->getPost('kode-dokumen-custom');
            $namaCustom = $this->request->getPost('nama-dokumen-custom');
            
            if (!$kodeCustom || !$namaCustom) {
                return redirect()->back()->with('error', 'Kode dokumen dan nama dokumen custom wajib diisi.');
            }

            // Cek apakah kode dokumen sudah ada untuk jenis ini
            $existingKode = $this->kodeDokumenModel
                ->where('document_type_id', $jenisId)
                ->where('kode', $kodeCustom)
                ->where('status', 1)
                ->first();

            if ($existingKode) {
                return redirect()->back()->with('error', 'Kode dokumen "' . $kodeCustom . '" sudah ada untuk jenis ini.');
            }

            // Insert kode dokumen baru
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

            // Tentukan nama dokumen berdasarkan jenis
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

            return redirect()->to('/create-document')->with('success', 'Dokumen berhasil ditambahkan.');
            
        } catch (\Exception $e) {
            $this->documentModel->db->transRollback();
            log_message('error', 'Error in tambah method: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal menyimpan dokumen: ' . $e->getMessage());
        }
    }
}