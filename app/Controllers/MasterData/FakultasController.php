<?php

namespace App\Controllers\MasterData;

use App\Models\UnitParentModel;
use CodeIgniter\Controller;

class FakultasController extends Controller
{
    protected $unitParentModel;

    public function __construct()
    {
        $this->unitParentModel = new UnitParentModel();
        helper('form');
    }

    public function create()
    {
        $data = ['title' => 'Create Faculty'];
        return view('Faculty/TambahFakultas', $data);
    }

    public function store()
    {
        $nama   = $this->request->getPost('name');
        $type   = $this->request->getPost('type');
        $status = $this->request->getPost('status');

        if (empty($nama) || empty($type) || empty($status)) {
            return redirect()->back()->withInput()->with('error', 'All fields must be filled in.');
        }

        if ($this->unitParentModel->where('name', $nama)->first()) {
            return redirect()->back()->withInput()->with('error', 'Faculty names are listed.');
        }

        $result = $this->unitParentModel->insert([
            'name'        => $nama,
            'type'        => $type,
            'description' => null,
            'status'      => (int)$status,
        ]);

        if ($result) {
            return redirect()->to('create-faculty')->with('added_message', 'Successfully Added');
        } else {
            return redirect()->back()->withInput()->with('error', 'Failed to add faculty.');
        }
    }

    public function index()
    {
        $fakultas = $this->unitParentModel->where('status !=', 0)->findAll();
        return view('Faculty/DaftarFakultas', ['unitParent' => $fakultas]);
    }

    public function edit()
    {
        $id = $this->request->getGet('id');
        
        if (empty($id)) {
            session()->setFlashdata('error', 'Faculty ID is required.');
            return redirect()->to('faculty-list');
        }

        $fakultas = $this->unitParentModel->find($id);
        if (!$fakultas) {
            session()->setFlashdata('error', 'Faculty not found.');
            return redirect()->to('faculty-list');
        }

        $data = [
            'title' => 'Edit Faculty',
            'fakultas' => $fakultas
        ];
        
        return view('Faculty/EditFakultas', $data);
    }

    public function update()
    {
        $id     = $this->request->getPost('id');
        $nama   = $this->request->getPost('name');
        $type   = $this->request->getPost('type');
        $status = $this->request->getPost('status');

        if (empty($id) || empty($nama) || empty($type) || empty($status)) {
            session()->setFlashdata('error', 'All fields are required.');
            return redirect()->to('faculty-list');
        }

        $fakultas = $this->unitParentModel->find($id);
        if (!$fakultas) {
            session()->setFlashdata('error', 'Faculty not found.');
            return redirect()->to('faculty-list');
        }

        // Check if name already exists (exclude current record)
        $existingFakultas = $this->unitParentModel->where('name', $nama)->where('id !=', $id)->first();
        if ($existingFakultas) {
            session()->setFlashdata('error', 'Faculty name already exists.');
            return redirect()->to('faculty-list');
        }

        $updateData = [
            'name'   => $nama,
            'type'   => $type,
            'status' => (int)$status
        ];

        $result = $this->unitParentModel->update($id, $updateData);

        if ($result) {
            session()->setFlashdata('updated_message', 'Successfully Updated.');
        } else {
            session()->setFlashdata('error', 'Failed to update faculty.');
        }

        return redirect()->to('faculty-list');
    }

    public function delete()
    {
        $id = $this->request->getPost('id');
        
        if (empty($id)) {
            session()->setFlashdata('error', 'Faculty ID is required.');
            return redirect()->to('faculty-list');
        }

        $fakultas = $this->unitParentModel->find($id);
        if (!$fakultas) {
            session()->setFlashdata('error', 'Faculty not found.');
            return redirect()->to('faculty-list');
        }

        $result = $this->unitParentModel->update($id, ['status' => 0]);
        
        if ($result) {
            session()->setFlashdata('deleted_message', 'Successfully Deleted.');
        } else {
            session()->setFlashdata('error', 'Failed to delete faculty.');
        }
        
        return redirect()->to('faculty-list');
    }
}