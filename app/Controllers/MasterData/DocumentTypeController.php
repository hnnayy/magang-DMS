<?php

namespace App\Controllers\MasterData;

use App\Models\DocumentTypeModel;
use CodeIgniter\Controller;

class DocumentTypeController extends Controller
{
    protected $documentTypeModel;

    public function __construct()
    {
        $this->documentTypeModel = new DocumentTypeModel();
    }

    public function index()
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

        return view('DataMaster/document-type', $data);
    }

    public function add()
    {
        $nama = strtoupper(trim($this->request->getPost('nama')));
        $kode = strtoupper(trim($this->request->getPost('kode')));
        // Fix: checkbox akan mengirim value="1" jika dicentang, null jika tidak
        $use_predefined = $this->request->getPost('use_predefined') === '1';

        if (empty($nama)) {
            return redirect()->back()->with('error', 'Category name is required.');
        }

        // Check for existing name
        $existingName = $this->documentTypeModel
            ->where('UPPER(name)', $nama)
            ->where('status', 1)
            ->first();

        if ($existingName) {
            return redirect()->back()->with('error', 'Category name already exists.');
        }

        // Check for existing kode only if kode is provided and not empty
        if (!empty($kode)) {
            $existingKode = $this->documentTypeModel
                ->where('UPPER(kode)', $kode)
                ->where('status', 1)
                ->first();

            if ($existingKode) {
                return redirect()->back()->with('error', 'Category code already exists.');
            }
        }

        $description = $use_predefined ? '[predefined]' : null;

        $data = [
            'name' => $nama,
            'description' => $description,
            'status' => 1,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ];

        // Only add kode if it's not empty
        if (!empty($kode)) {
            $data['kode'] = $kode;
        }

        $result = $this->documentTypeModel->save($data);

        if ($result) {
            return redirect()->back()->with('added_message', 'Successfully Updated');
        } else {
            return redirect()->back()->with('error', 'Failed to add category..');
        }
    }

    public function edit()
    {
        $id = $this->request->getPost('id');
        $nama = strtoupper(trim($this->request->getPost('nama')));
        $kode = strtoupper(trim($this->request->getPost('kode')));
        $use_predefined = $this->request->getPost('use_predefined') === '1'; // Fix: checkbox handling

        if (empty($id) || empty($nama)) {
            return redirect()->back()->with('error', 'Category ID and name already exists.');
        }

        // Check for existing name excluding current record
        $existingName = $this->documentTypeModel
            ->where('UPPER(name)', $nama)
            ->where('id !=', $id)
            ->where('status', 1)
            ->first();

        if ($existingName) {
            return redirect()->back()->with('error', 'Category name already exists.');
        }

        // Check for existing kode only if kode is provided and not empty
        if (!empty($kode)) {
            $existingKode = $this->documentTypeModel
                ->where('UPPER(kode)', $kode)
                ->where('id !=', $id)
                ->where('status', 1)
                ->first();

            if ($existingKode) {
                return redirect()->back()->with('error', 'Category code already exists.');
            }
        }

        $description = $use_predefined ? '[predefined]' : null;

        $data = [
            'name' => $nama,
            'description' => $description,
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        // Only update kode if it's provided
        if (!empty($kode)) {
            $data['kode'] = $kode;
        }

        $result = $this->documentTypeModel->update($id, $data);

        if ($result) {
            return redirect()->back()->with('updated_message', 'Successfully Updated');
        } else {
            return redirect()->back()->with('error', 'Failed to update category.');
        }
    }

    public function delete()
    {
        $id = $this->request->getPost('id');
        if (!$id) {
            return redirect()->back()->with('error', 'ID is invalid.');
        }

        $result = $this->documentTypeModel->update($id, [
            'status' => 0,
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        if ($result) {
            return redirect()->back()->with('deleted_message', 'Successfully deleted.');
        } else {
            return redirect()->back()->with('error', 'Failed delete category.');
        }
    }
}