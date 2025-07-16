<?php

namespace App\Controllers\KelolaDokumen;

use App\Controllers\BaseController;
use App\Models\DocumentModel;
use App\Models\DocumentApprovalModel;

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
                document.*,
                document_approval.remark,
                document_approval.id AS approval_id,
                document_approval.approvedate,
                document_type.name AS jenis_dokumen,
                unit.name AS unit_name,
                unit_parent.name AS parent_name
            ')
            ->join('document_approval', 'document.id = document_approval.document_id')
            ->join('document_type', 'document_type.id = document.type', 'left')
            ->join('unit', 'unit.id = document.unit_id', 'left')
            ->join('unit_parent', 'unit_parent.id = unit.parent_id', 'left') // memastikan parent_name tersedia
            ->where('document_approval.status', 1)
            ->where('document.createddate !=', 0)
            ->findAll();

        return view('KelolaDokumen/dokumen_persetujuan', [
            'documents' => $documents,
            'title'     => 'Persetujuan Dokumen'
        ]);
    }

    public function update()
    {
        $id = $this->request->getPost('document_id');

        if (!$id || !$this->documentModel->find($id)) {
            return redirect()->back()->with('error', 'Dokumen tidak ditemukan.');
        }

        // Update dokumen
        $this->documentModel->update($id, [
            'title'      => $this->request->getPost('title'),
            'revision'   => $this->request->getPost('revision'),
            'updated_at' => date('Y-m-d H:i:s')
        ]);

        // Update remark dari approval
        $this->approvalModel
            ->where('document_id', $id)
            ->set('remark', $this->request->getPost('remark'))
            ->update();

        return redirect()->back()->with('success', 'Dokumen berhasil diperbarui.');
    }

    public function delete()
    {
        $id = $this->request->getPost('document_id');

        if (!$id || !$this->documentModel->find($id)) {
            return redirect()->back()->with('error', 'Dokumen tidak ditemukan.');
        }

        // Soft delete: mark createddate = 0
        $this->documentModel->update($id, ['createddate' => 0]);

        return redirect()->back()->with('success', 'Dokumen berhasil dihapus.');
    }
}
