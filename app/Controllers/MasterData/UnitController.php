<?php

namespace App\Controllers\MasterData;

use App\Controllers\BaseController;
use App\Models\UnitParentModel;
use App\Models\UnitModel;
use CodeIgniter\Exceptions\PageNotFoundException;

class UnitController extends BaseController
{
    protected $parentModel;
    protected $unitModel;
    protected $validationRules;

    public function __construct()
    {
        $this->parentModel = new UnitParentModel();
        $this->unitModel   = new UnitModel();
        helper(['form', 'url']);

        $this->validationRules = [
            'parent_id' => 'required|is_natural_no_zero',
            'unit_name' => 'required|alpha_space|max_length[40]|min_length[3]',
            'status'    => 'required|in_list[1,2]',
        ];
    }

    public function index()
    {
        return redirect()->to('unit-list');
    }

    public function create()
    {
        $fakultas = $this->parentModel
            ->where('status !=', 0)
            ->whereIn('type', [1, 2])
            ->orderBy('name', 'ASC')
            ->findAll();

        $data = [
            'fakultas' => $fakultas,
            'title'    => 'Tambah Unit Baru'
        ];

        return view('DataMaster/unit-create', $data);
    }

    public function store()
    {
        if (!$this->validate($this->validationRules)) {
            return redirect()->back()->withInput()->with('swal', [
                'icon'  => 'error',
                'title' => 'Validation failed!',
                'text'  => implode("\n", $this->validator->getErrors()),
            ]);
        }

        $unitName = trim($this->request->getPost('unit_name'));
        $parentId = $this->request->getPost('parent_id');
        $status   = $this->request->getPost('status');

        $parentExists = $this->parentModel
            ->where('id', $parentId)
            ->where('status !=', 0)
            ->first();

        if (!$parentExists) {
            return redirect()->back()->withInput()->with('swal', [
                'icon'  => 'error',
                'title' => 'Failed!',
                'text'  => 'The selected Faculty/Directorate is not valid.',
            ]);
        }

        $existingUnit = $this->unitModel
            ->where('LOWER(name)', strtolower($unitName))
            ->where('parent_id', $parentId)
            ->where('status', $status)
            ->first();

        if ($existingUnit) {
            return redirect()->back()->withInput()->with('swal', [
                'icon'  => 'error',
                'title' => 'Failed!',
                'text'  => 'Unit name with the same status is already registered.',
            ]);
        }

        $insertData = [
            'parent_id'  => $parentId,
            'name'       => $unitName,
            'status'     => $status,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ];

        $this->unitModel->insert($insertData);

        return redirect()->to('create-unit')->with('added_message', 'Successfully Added');
    }

    public function list()
    {
        $units = $this->unitModel
            ->select('unit.*, unit_parent.name as parent_name, unit_parent.type as parent_type')
            ->join('unit_parent', 'unit_parent.id = unit.parent_id', 'left')
            ->where('unit.status !=', 0)
            ->orderBy('unit_parent.name', 'ASC')
            ->orderBy('unit.name', 'ASC')
            ->findAll();

        $fakultas = $this->parentModel
            ->where('status !=', 0)
            ->whereIn('type', [1, 2])
            ->orderBy('name', 'ASC')
            ->findAll();

        $data = [
            'units'    => $units,
            'fakultas' => $fakultas,
            'title'    => 'Daftar Unit'
        ];

        return view('DataMaster/daftar-unit', $data);
    }

    public function edit()
    {
        $id = $this->request->getGet('id');

        if (!is_numeric($id) || $id <= 0) {
            return redirect()->to('unit-list')->with('swal', [
                'icon'  => 'error',
                'title' => 'Error!',
                'text'  => 'Invalid Unit ID.',
            ]);
        }

        $unit = $this->unitModel
            ->where('id', $id)
            ->where('status !=', 0)
            ->first();

        if (!$unit) {
            return redirect()->to('unit-list')->with('swal', [
                'icon'  => 'error',
                'title' => 'Error!',
                'text'  => 'Unit not found.',
            ]);
        }

        $fakultas = $this->parentModel
            ->where('status !=', 0)
            ->whereIn('type', [1, 2])
            ->orderBy('name', 'ASC')
            ->findAll();

        $data = [
            'unit'     => $unit,
            'fakultas' => $fakultas,
            'title'    => 'Edit Unit'
        ];

        return view('DataMaster/unit-edit', $data);
    }

    public function update()
    {
        $id = $this->request->getPost('id');

        if (!is_numeric($id) || $id <= 0) {
            return redirect()->to('unit-list')->with('swal', [
                'icon'  => 'error',
                'title' => 'Error!',
                'text'  => 'Invalid Unit ID.',
            ]);
        }

        $unit = $this->unitModel
            ->where('id', $id)
            ->where('status !=', 0)
            ->first();

        if (!$unit) {
            return redirect()->to('unit-list')->with('swal', [
                'icon'  => 'error',
                'title' => 'Error!',
                'text'  => 'Unit not found.',
            ]);
        }

        if (!$this->validate($this->validationRules)) {
            return redirect()->back()->withInput()->with('swal', [
                'icon'  => 'error',
                'title' => 'Validation failed!',
                'text'  => implode("\n", $this->validator->getErrors()),
            ]);
        }

        $unitName = trim($this->request->getPost('unit_name'));
        $parentId = $this->request->getPost('parent_id');
        $status   = $this->request->getPost('status');

        $existingUnit = $this->unitModel
            ->where('LOWER(name)', strtolower($unitName))
            ->where('parent_id', $parentId)
            ->where('status', $status)
            ->where('id !=', $id)
            ->first();

        if ($existingUnit) {
            return redirect()->back()->withInput()->with('swal', [
                'icon'  => 'error',
                'title' => 'Failed!',
                'text'  => 'Unit name with the same status is already registered.',
            ]);
        }

        $updateData = [
            'parent_id'  => $parentId,
            'name'       => $unitName,
            'status'     => $status,
            'updated_at' => date('Y-m-d H:i:s')
        ];

        $this->unitModel->update($id, $updateData);

        return redirect()->to('unit-list')->with('updated_message', 'Successfully Updated');
    }

    public function delete()
    {
        $id = $this->request->getPost('id');

        if (!is_numeric($id) || $id <= 0) {
            return redirect()->to('unit-list')->with('swal', [
                'icon'  => 'error',
                'title' => 'Error!',
                'text'  => 'Unit ID not valid.',
            ]);
        }

        $unit = $this->unitModel->find($id);

        if (!$unit) {
            return redirect()->to('unit-list')->with('swal', [
                'icon'  => 'error',
                'title' => 'Error!',
                'text'  => 'Unit not found.',
            ]);
        }

        $this->unitModel->update($id, [
            'status'     => 0,
            'updated_at' => date('Y-m-d H:i:s')
        ]);

        return redirect()->to('unit-list')->with('deleted_message', 'Successfully Deleted');
    }
}