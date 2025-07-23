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
        return redirect()->to('data-master/unit/list');
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
                'title' => 'Validasi Gagal!',
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
                'title' => 'Gagal!',
                'text'  => 'Fakultas/Directorate yang dipilih tidak valid.',
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
                'title' => 'Gagal!',
                'text'  => 'Nama unit dengan status yang sama sudah terdaftar.',
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

        return redirect()->to('tambah-unit')->with('swal', [
            'icon'  => 'success',
            'title' => 'Berhasil!',
            'text'  => 'Unit berhasil ditambahkan.',
        ]);
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

    public function update($id = null)
    {
        if (!is_numeric($id) || $id <= 0) {
            return redirect()->to('data-master/unit/list')->with('swal', [
                'icon'  => 'error',
                'title' => 'Error!',
                'text'  => 'ID unit tidak valid.',
            ]);
        }

        $unit = $this->unitModel
            ->where('id', $id)
            ->where('status !=', 0)
            ->first();

        if (!$unit) {
            return redirect()->to('data-master/unit/list')->with('swal', [
                'icon'  => 'error',
                'title' => 'Error!',
                'text'  => 'Unit tidak ditemukan.',
            ]);
        }

        if (!$this->validate($this->validationRules)) {
            return redirect()->back()->withInput()->with('swal', [
                'icon'  => 'error',
                'title' => 'Validasi Gagal!',
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
                'title' => 'Gagal!',
                'text'  => 'Nama unit dengan status yang sama sudah terdaftar.',
            ]);
        }

        $updateData = [
            'parent_id'  => $parentId,
            'name'       => $unitName,
            'status'     => $status,
            'updated_at' => date('Y-m-d H:i:s')
        ];

        $this->unitModel->update($id, $updateData);

        return redirect()->to('data-master/unit/list')->with('swal', [
            'icon'  => 'success',
            'title' => 'Berhasil!',
            'text'  => 'Unit berhasil diperbarui.',
        ]);
    }

    public function delete($id = null)
    {
        if (!is_numeric($id) || $id <= 0) {
            return redirect()->to('data-master/unit/list')->with('swal', [
                'icon'  => 'error',
                'title' => 'Error!',
                'text'  => 'ID unit tidak valid.',
            ]);
        }

        $unit = $this->unitModel->find($id);

        if (!$unit) {
            return redirect()->to('data-master/unit/list')->with('swal', [
                'icon'  => 'error',
                'title' => 'Error!',
                'text'  => 'Unit tidak ditemukan.',
            ]);
        }

        $this->unitModel->update($id, [
            'status'     => 0,
            'updated_at' => date('Y-m-d H:i:s')
        ]);

        return redirect()->to('data-master/unit/list')->with('swal', [
            'icon'  => 'success',
            'title' => 'Terhapus!',
            'text'  => 'Unit berhasil dihapus.',
        ]);
    }
}
