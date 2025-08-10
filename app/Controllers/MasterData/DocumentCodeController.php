<?php

namespace App\Controllers\MasterData;

use App\Models\DocumentCodeModel;
use App\Models\DocumentTypeModel;
use CodeIgniter\Controller;

class DocumentCodeController extends Controller
{
    protected $kodeDokumenModel;
    protected $documentTypeModel;

    public function __construct()
    {
        $this->kodeDokumenModel = new DocumentCodeModel();
        $this->documentTypeModel = new DocumentTypeModel();
    }

    public function index()
    {
        $kategori = $this->documentTypeModel
            ->select('id, name, kode, description, status')
            ->where('status', 1)
            ->findAll();
        
        // Mapping data kategori untuk konsistensi dengan view
        $data['kategori_dokumen'] = array_map(function ($item) {
            return [
                'id' => $item['id'],
                'nama' => $item['name'], // mapping dari 'name' ke 'nama'
                'kode' => $item['kode'],
                'use_predefined_codes' => str_contains($item['description'] ?? '', '[predefined]'), // logika yang sama dengan DocumentTypeController
            ];
        }, $kategori);

        $kodeList = $this->kodeDokumenModel
            ->select('kode_dokumen.*, document_type.name as kategori_nama')
            ->join('document_type', 'document_type.id = kode_dokumen.document_type_id')
            ->where('kode_dokumen.status', 1)
            ->where('document_type.status', 1)
            ->where('document_type.description', '[predefined]')
            ->findAll();

        $data['kode_dokumen'] = $kodeList;

        return view('DataMaster/document-code', $data);
    }

    public function add()
    {
        $jenis = $this->request->getPost('jenis');
        $kode = strtoupper($this->request->getPost('kode'));
        $nama = $this->request->getPost('nama');

        $kategori = $this->documentTypeModel
            ->where('id', $jenis)
            ->where('status', 1)
            ->first();

        if (!$kategori) {
            return redirect()->to('/document-code')->with('error', 'Document type is not found');
        }

        $existingKode = $this->kodeDokumenModel
            ->where('kode', $kode)
            ->where('document_type_id', $kategori['id'])
            ->first();

        if ($existingKode) {
            return redirect()->to('/document-code')->with('error', 'Document code already exist.');
        }

        $existingNama = $this->kodeDokumenModel
            ->where('nama', $nama)
            ->where('document_type_id', $kategori['id'])
            ->first();

        if ($existingNama) {
            return redirect()->to('/document-code')->with('error', 'Document name already exists in this document type.');
        }

        $this->kodeDokumenModel->save([
            'document_type_id' => $kategori['id'],
            'kode' => $kode,
            'nama' => $nama,
            'status' => 1,
        ]);

        return redirect()->to('/document-code')->with('added_message', 'Successfully Added');
    }

    public function edit()
    {
        $id = $this->request->getPost('id');
        $kode = strtoupper($this->request->getPost('kode'));
        $nama = $this->request->getPost('nama');
        $document_type_id = $this->request->getPost('document_type_id');
        
        $existing = $this->kodeDokumenModel->find($id);
        if (!$existing) {
            return redirect()->to('/document-code')->with('error', 'Document code is not found.');
        }

        // Cek duplikasi nama dalam kategori yang sama
        $dupe = $this->kodeDokumenModel
            ->where('nama', $nama)
            ->where('document_type_id', $document_type_id)
            ->where('id !=', $id)
            ->first();

        if ($dupe) {
            return redirect()->to('/document-code')->with('error', 'Document name already exists in this document type.');
        }

        $this->kodeDokumenModel->update($id, [
            'kode' => $kode,
            'nama' => $nama,
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        return redirect()->to('/document-code')->with('updated_message', 'Successfully Updated');
    }

    public function delete()
    {
        $id = $this->request->getPost('id');
        if (!$id) {
            return redirect()->to('/document-code')->with('error', 'ID is invalid.');
        }

        $this->kodeDokumenModel->update($id, [
            'status' => 0,
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        return redirect()->to('/document-code')->with('deleted_message', 'Successfully Deleted.');
    }
}