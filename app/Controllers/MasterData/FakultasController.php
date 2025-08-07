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
            return redirect()->back()->with('swal', [
                'icon'  => 'error',
                'title' => 'Error!',
                'text'  => 'All fields must be filled in.',
            ]);
        }

        if ($this->unitParentModel->where('name', $nama)->first()) {
            return redirect()->back()->with('swal', [
                'icon'  => 'error',
                'title' => 'Error!',
                'text'  => 'Faculty name already exists.',
            ]);
        }

        $result = $this->unitParentModel->insert([
            'name'        => $nama,
            'type'        => $type,
            'description' => null,
            'status'      => (int)$status,
        ]);

        if ($result) {
            return redirect()->to('create-faculty')->with('swal', [
                'icon'  => 'success',
                'title' => 'Success!',
                'text'  => 'Faculty has been successfully created.',
            ]);
        } else {
            return redirect()->back()->with('swal', [
                'icon'  => 'error',
                'title' => 'Error!',
                'text'  => 'Failed to add faculty.',
            ]);
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
            return redirect()->to('faculty-list')->with('swal', [
                'icon'  => 'error',
                'title' => 'Error!',
                'text'  => 'Faculty ID is required.',
            ]);
        }

        $fakultas = $this->unitParentModel->find($id);
        if (!$fakultas) {
            return redirect()->to('faculty-list')->with('swal', [
                'icon'  => 'error',
                'title' => 'Error!',
                'text'  => 'Faculty not found.',
            ]);
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
            return redirect()->to('faculty-list')->with('swal', [
                'icon'  => 'error',
                'title' => 'Error!',
                'text'  => 'All fields are required.',
            ]);
        }

        $fakultas = $this->unitParentModel->find($id);
        if (!$fakultas) {
            return redirect()->to('faculty-list')->with('swal', [
                'icon'  => 'error',
                'title' => 'Error!',
                'text'  => 'Faculty not found.',
            ]);
        }

        // Check if name already exists (exclude current record)
        $existingFakultas = $this->unitParentModel->where('name', $nama)->where('id !=', $id)->first();
        if ($existingFakultas) {
            return redirect()->to('faculty-list')->with('swal', [
                'icon'  => 'error',
                'title' => 'Error!',
                'text'  => 'Faculty name already exists.',
            ]);
        }

        $updateData = [
            'name'   => $nama,
            'type'   => $type,
            'status' => (int)$status
        ];

        $result = $this->unitParentModel->update($id, $updateData);

        if ($result) {
            return redirect()->to('faculty-list')->with('swal', [
                'icon'  => 'success',
                'title' => 'Success!',
                'text'  => 'Faculty has been successfully updated.',
            ]);
        } else {
            return redirect()->to('faculty-list')->with('swal', [
                'icon'  => 'error',
                'title' => 'Error!',
                'text'  => 'Failed to update faculty.',
            ]);
        }
    }

    public function delete()
    {
        $id = $this->request->getPost('id');
        
        if (empty($id)) {
            return redirect()->to('faculty-list')->with('swal', [
                'icon'  => 'error',
                'title' => 'Error!',
                'text'  => 'Faculty ID is required.',
            ]);
        }

        $fakultas = $this->unitParentModel->find($id);
        if (!$fakultas) {
            return redirect()->to('faculty-list')->with('swal', [
                'icon'  => 'error',
                'title' => 'Error!',
                'text'  => 'Faculty not found.',
            ]);
        }

        $result = $this->unitParentModel->update($id, ['status' => 0]);
        
        if ($result) {
            return redirect()->to('faculty-list')->with('swal', [
                'icon'  => 'success',
                'title' => 'Success!',
                'text'  => 'Faculty has been successfully deleted.',
            ]);
        } else {
            return redirect()->to('faculty-list')->with('swal', [
                'icon'  => 'error',
                'title' => 'Error!',
                'text'  => 'Failed to delete faculty.',
            ]);
        }
    }
}