<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\DocumentCodeModel;
use App\Models\DocumentTypeModel;
use App\Models\DocumentModel;
use App\Models\UnitModel;
use App\Models\UnitParentModel;
use App\Models\DocumentApprovalModel;
use FPDF;
require_once ROOTPATH . 'vendor/autoload.php';

class KelolaDokumen extends BaseController
{
    protected $kategoriDokumen = [];
    protected $kodeDokumen = [];

    public function __construct()
    {
        $this->documentTypeModel = new DocumentTypeModel();
        $this->kodeDokumenModel = new DocumentCodeModel();
        $this->documentModel = new DocumentModel();
        $this->documentApprovalModel = new DocumentApprovalModel();
        $this->documentCodeModel = new \App\Models\DocumentCodeModel(); 
        $this->documentRevisionModel = new \App\Models\DocumentRevisionModel();


       
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
            ->select('kode_dokumen.*, document_type.name as jenis_nama')
            ->join('document_type', 'document_type.id = kode_dokumen.document_type_id')
            ->where('kode_dokumen.status', 1)
            ->orderBy('document_type.name', 'ASC')
            ->orderBy('kode_dokumen.kode', 'ASC')
            ->findAll();

        $grouped = [];
        foreach ($kodeDokumen as $item) {
            $grouped[$item['jenis_nama']][] = $item;
        }
        $data['kode_dokumen'] = $grouped;
        $data['unit'] = $unitData;
        return view('KelolaDokumen/dokumen-create', $data);
    }
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
public function pengajuan()
{
    $documents = $this->documentModel
        ->select('document.*, 
                dt.name AS jenis_dokumen, 
                unit.name AS unit_name, 
                unit_parent.name AS parent_name,
                kd.kode AS kode_dokumen_kode,
                kd.nama AS kode_dokumen_nama')
        ->join('document_type dt', 'dt.id = document.type', 'left')
        ->join('unit', 'unit.id = document.unit_id', 'left')
        ->join('unit_parent', 'unit_parent.id = unit.parent_id', 'left')
        ->join('kode_dokumen kd', 'kd.id = document.kode_dokumen_id', 'left')
        ->where('document.status', 0)
        ->groupBy('document.id')
        ->findAll();

    $jenis_dokumen = $this->documentTypeModel->where('status', 1)->findAll();
    $kategori_dokumen = $this->kategoriDokumen;
    $kode_nama_dokumen = $this->kodeDokumenModel->where('status', 1)->findAll(); // Tambahkan ini

    $data = [
        'documents' => $documents,
        'jenis_dokumen' => $jenis_dokumen,
        'kategori_dokumen' => $kategori_dokumen,
        'kode_nama_dokumen' => $kode_nama_dokumen // Tambahkan ke data
    ];

    return view('KelolaDokumen/daftar-pengajuan', $data);
}



    public function configJenisDokumen()
    {
        $kategori = $this->documentTypeModel->where('status', 1)->findAll();

        $data['kategori_dokumen'] = array_map(function ($item) {
            return [
                'id' => $item['id'],
                'nama' => $item['name'],
                'kode' => $item['kode'],
                'use_predefined_codes' => str_contains($item['description'] ?? '', '[predefined]'),
            ];
        }, $kategori);
        $kodeList = $this->kodeDokumenModel
            ->select('kode_dokumen.*, document_type.name as jenis_nama')
            ->join('document_type', 'document_type.id = kode_dokumen.document_type_id')
            ->where('kode_dokumen.status', 1)
            ->where('document_type.status', 1)
            ->where('document_type.description', '[predefined]')
            ->findAll();
        $grouped = [];
        foreach ($kodeList as $item) {
            $key = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $item['jenis_nama']));
            $grouped[$key][] = [
                'id' => $item['id'],
                'kode' => $item['kode'],
                'nama' => $item['nama']
            ];
        }
        $data['kode_dokumen'] = $grouped;
        return view('KelolaDokumen/config-jenis-dokumen', $data);
    }

    public function addKategori()
    {
        $nama = strtoupper($this->request->getPost('nama'));
        $kode = strtoupper($this->request->getPost('kode'));
        $use_predefined = $this->request->getPost('use_predefined_codes') ? true : false;

        if (empty($nama) || empty($kode)) {
            return redirect()->to('/kelola-dokumen/configJenisDokumen')->with('error', 'Nama dan kode kategori harus diisi.');
        }
        $existing = $this->documentTypeModel
            ->groupStart()
                ->where('UPPER(name)', $nama)
                ->orWhere('UPPER(kode)', $kode)
            ->groupEnd()
            ->where('status', 1)
            ->first();

        if ($existing) {
            return redirect()->to('/kelola-dokumen/configJenisDokumen')->with('error', 'Nama atau kode kategori sudah ada.');
        }
        $description = $use_predefined ? '[predefined]' : null;
        $this->documentTypeModel->save([
            'name' => $nama,
            'kode' => $kode,
            'description' => $description,
            'status' => 1,
        ]);
        return redirect()->to('/kelola-dokumen/configJenisDokumen')->with('success', 'Kategori berhasil ditambahkan.');
    }

        public function editKategori()
    {
        $id = $this->request->getPost('id');
        $nama = strtoupper($this->request->getPost('nama'));
        $kode = strtoupper($this->request->getPost('kode'));
        $use_predefined = $this->request->getPost('use_predefined_codes') ? true : false;

        if (empty($id) || empty($nama) || empty($kode)) {
            return redirect()->to('/kelola-dokumen/configJenisDokumen')->with('error', 'Semua field harus diisi.');
        }
        $existing = $this->documentTypeModel
            ->groupStart()
                ->where('UPPER(name)', $nama)
                ->orWhere('UPPER(kode)', $kode)
            ->groupEnd()
            ->where('id !=', $id)
            ->where('status', 1)
            ->first();

        if ($existing) {
            return redirect()->to('/kelola-dokumen/configJenisDokumen')->with('error', 'Nama atau kode kategori sudah digunakan oleh kategori lain.');
        }

        $description = $use_predefined ? '[predefined]' : null;

        $this->documentTypeModel->update($id, [
            'name' => $nama,
            'kode' => $kode,
            'description' => $description,
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        return redirect()->to('/kelola-dokumen/configJenisDokumen')->with('success', 'Kategori berhasil diupdate.');
    }


    public function deleteKategori()
    {
        $id = $this->request->getPost('id');
        if (!$id) {
            return redirect()->to('/kelola-dokumen/configJenisDokumen')->with('error', 'ID tidak valid.');
        }
        $this->documentTypeModel->update($id, [
            'status' => 0,
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
        return redirect()->to('/kelola-dokumen/configJenisDokumen')->with('success', 'Kategori berhasil dihapus.');
    }

    public function addKode()
    {
        $jenis = $this->request->getPost('jenis');
        $kode = strtoupper($this->request->getPost('kode'));
        $nama = $this->request->getPost('nama');

        $kategori = $this->documentTypeModel
            ->where('id', $jenis)
            ->where('status', 1)
            ->first();

        if (!$kategori) {
            return redirect()->to('/kelola-dokumen/configJenisDokumen')->with('error', 'Jenis dokumen tidak ditemukan.');
        }
        $existingKode = $this->kodeDokumenModel
            ->where('kode', $kode)
            ->where('document_type_id', $kategori['id'])
            ->first();

        if ($existingKode) {
            return redirect()->to('/kelola-dokumen/configJenisDokumen')->with('error', 'Kode dokumen sudah ada.');
        }
        $existingNama = $this->kodeDokumenModel
            ->where('nama', $nama)
            ->where('document_type_id', $kategori['id'])
            ->first();

        if ($existingNama) {
            return redirect()->to('/kelola-dokumen/configJenisDokumen')->with('error', 'Nama dokumen sudah ada dalam jenis dokumen ini.');
        }

        $this->kodeDokumenModel->save([
            'document_type_id' => $kategori['id'],
            'kode' => $kode,
            'nama' => $nama,
            'status' => 1,
        ]);

        return redirect()->to('/kelola-dokumen/configJenisDokumen')->with('success', 'Kode dokumen berhasil ditambahkan.');
    }


    public function editKode()
    {
        $id = $this->request->getPost('id');
        $kode = strtoupper($this->request->getPost('kode'));
        $nama = $this->request->getPost('nama');
        $existing = $this->kodeDokumenModel->find($id);
        if (!$existing) {
            return redirect()->back()->with('error', 'Data kode dokumen tidak ditemukan.');
        }
        $dupe = $this->kodeDokumenModel
            ->where('nama', $nama)
            ->where('document_type_id', $existing['document_type_id'])
            ->where('id !=', $id)
            ->first();

        if ($dupe) {
            return redirect()->back()->with('error', 'Nama dokumen sudah ada dalam jenis dokumen ini.');
        }

        $this->kodeDokumenModel->update($id, [
            'kode' => $kode,
            'nama' => $nama
        ]);

        return redirect()->back()->with('success', 'Kode dokumen berhasil diperbarui.');
    }


public function delete_kode()
{
    $id = $this->request->getPost('id');
    if (!$id) {
        return redirect()->back()->with('error', 'ID tidak valid.');
    }

    // Soft delete: update status jadi 0
    $this->kodeDokumenModel->update($id, [
        'status' => 0,
        'updated_at' => date('Y-m-d H:i:s'),
    ]);

    return redirect()->back()->with('success', 'Kode dokumen berhasil dihapus.');
}

public function edit($id)
{
    $document = $this->documentModel->find($id);
    $jenis_dokumen = $this->documentTypeModel->where('status', 1)->findAll();
    $kode_nama_dokumen = $this->documentCodeModel->where('status', 1)->findAll();

    return view('dokumen/edit', [
        'document' => $document,
        'jenis_dokumen' => $jenis_dokumen,
        'kode_nama_dokumen' => $kode_nama_dokumen,
    ]);
}




    public function delete($id)
    {
        log_message('info', 'DELETE: Document ID ' . $id);
        return redirect()->to('/dokumen/pengajuan')->with('success', 'Dokumen berhasil dihapus.');
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

    $newName = $file->getRandomName();
    $file->move(ROOTPATH . 'public/uploads', $newName);

    $unitId = $this->request->getPost('unit_id') ?? 99;
    $unitModel = new \App\Models\UnitModel();
    $unitData = $unitModel->select('parent_id')->where('id', $unitId)->first();
    $unitParentId = $unitData['parent_id'] ?? null;

    // Simpan dokumen
    $this->documentModel->insert([
        'type'            => $jenisId,
        'kode_dokumen_id' => $this->request->getPost('kode_dokumen_id'),
        'number'          => $this->request->getPost('no-dokumen'),
        'date_published'  => $this->request->getPost('date_published'),
        'revision'        => $this->request->getPost('revisi') ?? 'Rev. 0',
        'title'           => $this->request->getPost('nama-dokumen'),
        'description'     => $this->request->getPost('keterangan'),
        'unit_id'         => $unitId,
        'status'          => 0,
        'createddate'     => date('Y-m-d H:i:s'),
        'createdby'       => session('user_id'),
    ]);

    // Pastikan documentId diambil setelah insert
    $documentId = $this->documentModel->getInsertID();

    // Simpan revisi dokumen
    $this->documentRevisionModel->insert([
        'document_id' => $documentId,
        'revision'    => $this->request->getPost('revisi') ?? 'Rev. 0',
        'filename'    => $file->getClientName(),
        'filepath'    => $newName,
        'filesize'    => $file->getSize(),
        'remark'      => $this->request->getPost('keterangan'),
        'createddate' => date('Y-m-d H:i:s'),
        'createdby'   => session('user_id'),
    ]);

    return redirect()->to('/tambah-dokumen')->with('success', 'Dokumen berhasil ditambahkan.');
}

//     // Kirim notifikasi ke user satu unit dan parent unit
//     $currentUserId = session()->get('user_id');
//     $userModel = new \App\Models\UserModel();
//     $targetUsers = $userModel->where('status', 1)
//         ->where('id !=', $currentUserId)
//         ->groupStart()
//             ->where('unit_id', $unitId)
//             ->orWhere('unit_id IN (SELECT id FROM unit WHERE parent_id = ' . $unitParentId . ')')
//         ->groupEnd()
//         ->findAll();

//     $notifModel = new \App\Models\NotificationModel();
//     foreach ($targetUsers as $user) {
//         $notifModel->insert([
//             'user_id'    => $user['id'],
//             'title'      => 'Dokumen Baru diunggah',
//             'message'    => 'Ada dokumen baru berjudul "' . $this->request->getPost('nama-dokumen') . '" dari unit yang sama.',
//             'link'       => '/dokumen/detail/' . $documentId,
//             'created_at' => date('Y-m-d H:i:s'),
//         ]);
//     }

// }

//DAFTAR-PENGAJUAN
    public function daftarPengajuan()
    {
        $documentModel = new \App\Models\DocumentModel();

        $documents = $documentModel
        ->select('document.*, 
                dt.name AS jenis_dokumen,
                parent_unit.name AS parent_name, 
                unit.name AS unit_name,
                unit.unit_parent_id, 
                kode_dokumen.kode AS kode_dokumen_kode, 
                kode_dokumen.nama AS kode_dokumen_nama')
        ->join('unit', 'document.unit_id = unit.id', 'left')
        ->join('unit AS parent_unit', 'unit.unit_parent_id = parent_unit.id', 'left')
        ->join('kode_dokumen', 'kode_dokumen.id = document.kode_dokumen_id', 'left')
        ->join('document_type dt', 'dt.id = document.type', 'left') 
        ->where('document.createdby !=', 0)
        ->findAll();


        return view('KelolaDokumen/daftar-pengajuan', [
            'documents' => $documents,
            'kategori_dokumen' => $this->kategoriDokumen,
            'title'     => 'Daftar Pengajuan Dokumen'
        ]);
    }

    public function approvepengajuan()
    {
        date_default_timezone_set('Asia/Jakarta');
        $document_id   = $this->request->getPost('document_id');
        $approved_by   = $this->request->getPost('approved_by');
        $remarks       = $this->request->getPost('remarks');
        $action        = $this->request->getPost('action'); 

        $status = $action === 'approve' ? 2 : 1;

        $data = [
            'document_id' => $document_id,
            'remark'      => $remarks,
            'status'      => $status,
            'approvedate' => date('Y-m-d H:i:s'),
            'approveby'   => $approved_by,
        ];

        $this->documentApprovalModel->insert($data);
        $this->documentModel->update($document_id, ['status' => $status]);

        return redirect()->back()->with('success', 'Dokumen berhasil diproses.');
    }

    public function generateSignedPDF()
    {
        $pdf = new FPDF();
        $pdf->AddPage();
        $pdf->SetFont('Arial', 'B', 14);
        $pdf->Cell(0, 10, 'Ini adalah isi dokumen.', 0, 1);
        $pdf->Image('assets/images/ttd/ttd.jpg', 150, 240, 40); 
        $path = 'public/uploads/signed/signed_' . time() . '.pdf';
        $pdf->Output('F', $path);

        return $this->response->download($path, null);
    }

    public function deletePengajuan()
    {
        $id = $this->request->getPost('document_id');

        $doc = $this->documentModel->find($id);
        if (!$id || !$doc) {
            return redirect()->back()->with('error', 'Dokumen tidak ditemukan.');
        }
        $this->documentModel->update($id, [
            'status' => 3,
            'createdby' => 0,
        ]);

        return redirect()->back()->with('success', 'Dokumen berhasil dihapus (soft delete).');
    }


    public function updatepengajuan()
{
    $documentModel = new DocumentModel();
    $documentId = $this->request->getPost('document_id');

    // Validasi ID dokumen
    if (!$documentId) {
        return redirect()->back()->with('error', 'ID dokumen tidak ditemukan.');
    }

    // Validasi dokumen ada di database
    $document = $documentModel->find($documentId);
    if (!$document) {
        return redirect()->back()->with('error', 'Dokumen tidak ditemukan di database.');
    }

    // Ambil data dari form
    $jenisId = $this->request->getPost('type');
    $kodeDokumenId = $this->request->getPost('kode_dokumen');
    $nomor = $this->request->getPost('nomor');
    $revisi = $this->request->getPost('revisi') ?? 'Rev. 0';
    $nama = $this->request->getPost('nama');
    $keterangan = $this->request->getPost('keterangan');
    $file = $this->request->getFile('file_dokumen');

    // Validasi input wajib
    if (empty($jenisId) || empty($kodeDokumenId) || empty($nomor) || empty($nama)) {
        return redirect()->back()->with('error', 'Semua field wajib harus diisi.');
    }

    // Validasi jenis dokumen
    $documentType = $this->documentTypeModel->where('id', $jenisId)->where('status', 1)->first();
    if (!$documentType) {
        return redirect()->back()->with('error', 'Jenis dokumen tidak valid.');
    }

    // Validasi kode dokumen
    $kodeDokumen = $this->kodeDokumenModel->where('id', $kodeDokumenId)->where('status', 1)->first();
    if (!$kodeDokumen) {
        return redirect()->back()->with('error', 'Kode dokumen tidak valid.');
    }

    // Siapkan data untuk update
    $data = [
        'type'            => $jenisId,
        'kode_dokumen_id' => $kodeDokumenId,
        'number'          => $nomor,
        'revision'        => $revisi,
        'title'           => $nama,
        'description'     => $keterangan,
        'status'          => 0,
    ];

    // Penanganan file upload
    if ($file && $file->isValid() && !$file->hasMoved()) {
        $newName = $file->getRandomName();
        $file->move(ROOTPATH . 'public/uploads', $newName);
        $data['filepath'] = $newName;
        $data['filename'] = $file->getClientName();
    }

    // Lakukan pembaruan di database
    try {
        $updated = $documentModel->update($documentId, $data);
        if ($updated) {
            return redirect()->back()->with('success', 'Dokumen berhasil diperbarui.');
        } else {
            return redirect()->back()->with('error', 'Gagal memperbarui dokumen. Tidak ada perubahan yang dilakukan.');
        }
    } catch (\Exception $e) {
        log_message('error', 'Error updating document: ' . $e->getMessage());
        return redirect()->back()->with('error', 'Gagal memperbarui dokumen: ' . $e->getMessage());
    }
}


// public function persetujuan()
//     {
//         $documents = $this->documentModel
//             ->select('document.*, 
//                     dt.name AS jenis_dokumen,
//                     unit.name AS unit_name,
//                     unit_parent.name AS parent_name,
//                     kd.kode AS kode_dokumen_kode,
//                     kd.nama AS kode_dokumen_nama,
//                     da.remark,
//                     da.approveby,
//                     da.approvedate')
//             ->join('document_type dt', 'dt.id = document.type', 'left')
//             ->join('unit', 'unit.id = document.unit_id', 'left')
//             ->join('unit_parent', 'unit_parent.id = unit.parent_id', 'left')
//             ->join('kode_dokumen kd', 'kd.id = document.kode_dokumen_id', 'left')
//             ->join('document_approval da', 'da.document_id = document.id', 'left')
//             ->where('document.status', 1)
//             ->orderBy('document.updated_at', 'DESC')
//             ->findAll();

//         dd($documents); 
//     }
// }


}