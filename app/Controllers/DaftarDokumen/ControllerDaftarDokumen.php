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

class ControllerDaftarDokumen extends BaseController
{
    protected $documentModel;
    protected $typeModel;
    protected $standardModel;
    protected $clauseModel;
    protected $approvalModel;
    protected $revisionModel;

    public function __construct()
    {
        $this->documentModel  = new DocumentModel();
        $this->typeModel      = new DocumentTypeModel();
        $this->standardModel  = new StandardModel();
        $this->clauseModel    = new ClauseModel();
        $this->approvalModel  = new DocumentApprovalModel();
        $this->revisionModel  = new DocumentRevisionModel();
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
                user_document_owner.fullname AS createdby_name
            ')
            ->join('document_type dt', 'dt.id = document.type', 'left')
            ->join('unit', 'unit.id = document.unit_id', 'left')
            ->join('unit_parent', 'unit_parent.id = unit.parent_id', 'left')
            ->join('kode_dokumen kd', 'kd.id = document.kode_dokumen_id', 'left')
            ->join('document_approval', 'document_approval.document_id = document.id', 'left')
            ->join('user user_approver', 'user_approver.id = document_approval.approveby', 'left')
            ->join('document_revision', 'document_revision.document_id = document.id', 'left')
            ->join('user user_creator', 'user_creator.id = document_revision.createdby', 'left')
            ->join('user user_document_owner', 'user_document_owner.id = document.createdby', 'left')
            ->where('document.createdby !=', 0)
            ->where('document_approval.status', 1)
            ->groupBy('document.id')
            ->findAll();

        // Ubah ID jadi nama pemilik jika tersedia
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
    public function update()
    {
        $validation = \Config\Services::validation();
        
        $validation->setRules([
            'id' => 'required|numeric',
            'type' => 'required',
            'title' => 'required|min_length[3]',
            'file' => 'permit_empty|uploaded[file]|max_size[file,10240]|ext_in[file,pdf,doc,docx,xls,xlsx]'
        ]);

        if (!$validation->withRequest($this->request)->run()) {
            return redirect()->back()->withInput()->with('errors', $validation->getErrors());
        }

        $id = $this->request->getPost('id');
        
        // Prepare data untuk update
        $data = [
            'type'               => $this->request->getPost('type'),
            'kode_jenis_dokumen' => $this->request->getPost('kode_jenis_dokumen'),
            'number'             => $this->request->getPost('number'),
            'title'              => $this->request->getPost('title'),
            'revision'           => $this->request->getPost('revision'),
            'date_published'     => $this->request->getPost('date_published'),
            'updated_at'         => date('Y-m-d H:i:s'),
        ];

        // Handle file upload
        $file = $this->request->getFile('file');
        if ($file && $file->isValid() && !$file->hasMoved()) {
            // Hapus file lama jika ada
            $oldDocument = $this->documentModel->find($id);
            if ($oldDocument && !empty($oldDocument['filepath'])) {
                $oldFilePath = ROOTPATH . 'public/uploads/' . $oldDocument['filepath'];
                if (file_exists($oldFilePath)) {
                    unlink($oldFilePath);
                }
            }

            // Upload file baru
            $newName = $file->getRandomName();
            $file->move(ROOTPATH . 'public/uploads/', $newName);
            $data['filepath'] = $newName;
            $data['filename'] = $file->getClientName();
        }

        // Handle pemilik dokumen update (createdby)
        $pemilikName = $this->request->getPost('createdby');
        if ($pemilikName) {
            // Cari user berdasarkan nama lengkap
            $userModel = new \App\Models\UserModel(); // Sesuaikan dengan model User Anda
            $user = $userModel->where('fullname', $pemilikName)->first();
            
            if ($user) {
                $data['createdby'] = $user['id'];
            } else {
                // Jika tidak ditemukan user dengan nama tersebut, biarkan input sebagai nama langsung
                // Atau Anda bisa menambahkan validasi error di sini
                log_message('warning', "User dengan nama '{$pemilikName}' tidak ditemukan");
            }
        }

        // Update document
        try {
            $this->documentModel->update($id, $data);

            // Update approval jika ada perubahan
            $approveBy = $this->request->getPost('approveby');
            $approveDate = $this->request->getPost('approvedate');
            
            if ($approveBy || $approveDate) {
                $approvalData = [];
                
                // Jika approveBy berupa nama, cari ID user-nya
                if ($approveBy) {
                    $userModel = new \App\Models\UserModel();
                    $approverUser = $userModel->where('fullname', $approveBy)->first();
                    $approvalData['approveby'] = $approverUser ? $approverUser['id'] : $approveBy;
                }
                
                if ($approveDate) {
                    $approvalData['approvedate'] = $approveDate;
                }
                
                if (!empty($approvalData)) {
                    $this->approvalModel->where('document_id', $id)->set($approvalData)->update();
                }
            }

            return redirect()->to(base_url('daftar-dokumen'))
                ->with('success', 'Dokumen berhasil diperbarui.');
                
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function delete($id = null)
    {
        if ($id === null) {
            return redirect()->back()->with('error', 'ID dokumen tidak ditemukan.');
        }

        try {
            // Soft delete dengan mengubah createdby menjadi 0
            $updated = $this->documentModel->update($id, [
                'createdby'  => 0,
                'updated_at' => date('Y-m-d H:i:s'),
            ]);

            if ($updated) {
                return redirect()->back()->with('success', 'Dokumen berhasil dihapus.');
            } else {
                return redirect()->back()->with('error', 'Dokumen tidak ditemukan atau tidak dapat dihapus.');
            }
            
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }
}