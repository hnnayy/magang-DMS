<?php

namespace App\Controllers\DaftarDokumen;

use App\Controllers\BaseController;
use App\Models\DocumentModel;
use App\Models\DocumentTypeModel;
use App\Models\StandardModel;
use App\Models\ClauseModel;
use App\Models\DocumentApprovalModel;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class ControllerDaftarDokumen extends BaseController
{
    protected $documentModel;
    protected $typeModel;
    protected $standardModel;
    protected $clauseModel;
    protected $approvalModel;

    public function __construct()
    {
        $this->documentModel  = new DocumentModel();
        $this->typeModel      = new DocumentTypeModel();
        $this->standardModel  = new StandardModel();
        $this->clauseModel    = new ClauseModel();
        $this->approvalModel  = new DocumentApprovalModel();
    }

    public function index()
    {
        $document = $this->documentModel
            ->select('
                document.*,
                document_type.name AS jenis_dokumen,
                document_type.kode AS kode_jenis_dokumen,
                document_approval.approvedate,
                user.fullname AS approved_by_name
            ')
            ->join('document_type', 'document_type.id = document.type', 'left')
            ->join('document_approval', 'document_approval.document_id = document.id', 'left')
            ->join('user', 'user.id = document_approval.approveby', 'left')
            ->where('document.createdby !=', 0)
            ->where('document_approval.status', 1)
            ->findAll();

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

    public function update()
{
    $id = $this->request->getPost('id');
    $data = [
        'type'            => $this->request->getPost('type'),
        'kode_jenis_dokumen' => $this->request->getPost('kode_jenis_dokumen'),
        'number'          => $this->request->getPost('number'),
        'title'           => $this->request->getPost('title'),
        'pemilik'         => $this->request->getPost('pemilik'),
        'revision'        => $this->request->getPost('revision'),
        'date_published'  => $this->request->getPost('date_published'),
        // 'approveby'       => $this->request->getPost('approveby'),
        'approvedate'     => $this->request->getPost('approvedate'),
        'updated_at'      => $this->request->getPost('updated_at'),
    ];

    // Handle file upload
    $file = $this->request->getFile('file');
    if ($file && $file->isValid() && !$file->hasMoved()) {
        $newName = $file->getRandomName();
        $file->move('uploads/', $newName);
        $data['filepath'] = $newName;
    }

    $this->documentModel->update($id, $data);
    return redirect()->to(base_url('daftar-dokumen'))->with('success', 'Dokumen berhasil diperbarui.');
}


    public function delete($id = null)
{
    if ($id === null) {
        return redirect()->back()->with('error', 'ID dokumen tidak ditemukan.');
    }

    $this->documentModel->update($id, [
        'createdby' => 0,
        'updated_at' => date('Y-m-d H:i:s'),
    ]);

    return redirect()->back()->with('success', 'Dokumen berhasil dihapus.');
}
public function exportExcel()
    {
        $dokumen = $this->documentModel
            ->select('
                document.*,
                document_type.name AS jenis_dokumen,
                document_type.kode AS kode_jenis_dokumen,
                document_approval.approvedate,
                user.fullname AS approved_by_name
            ')
            ->join('document_type', 'document_type.id = document.type', 'left')
            ->join('document_approval', 'document_approval.document_id = document.id', 'left')
            ->join('user', 'user.id = document_approval.approveby', 'left')
            ->where('document.createdby !=', 0)
            ->where('document_approval.status', 1)
            ->findAll();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Set header
        $sheet->fromArray([
            'Jenis Dokumen', 'Kode Jenis', 'Nomor', 'Nama Dokumen', 'Revisi',
            'Tanggal Efektif', 'Disetujui Oleh', 'Tanggal Disetujui', 'Last Update'
        ], null, 'A1');

        // Set data
        $rows = 2;
        foreach ($dokumen as $doc) {
            $sheet->fromArray([
                $doc['jenis_dokumen'] ?? '',
                $doc['kode_jenis_dokumen'] ?? '',
                $doc['number'] ?? '',
                $doc['title'] ?? '',
                $doc['revision'] ?? '',
                $doc['date_published'] ?? '',
                $doc['approved_by_name'] ?? '',
                $doc['approvedate'] ?? '',
                $doc['updated_at'] ?? '',
            ], null, 'A' . $rows++);
        }

        // Download
        $filename = 'Daftar_Dokumen_' . date('Ymd_His') . '.xlsx';

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header("Content-Disposition: attachment; filename=\"$filename\"");
        header('Cache-Control: max-age=0');

        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }
}
