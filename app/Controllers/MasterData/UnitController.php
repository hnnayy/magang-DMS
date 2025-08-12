<?php
namespace App\Controllers\MasterData;

use App\Controllers\BaseController;
use App\Models\UnitParentModel;
use App\Models\UnitModel;
use App\Models\UserModel;
use CodeIgniter\Exceptions\PageNotFoundException;

/**
 * Controller untuk mengelola data unit.
 */
class UnitController extends BaseController
{
    protected $parentModel;
    protected $unitModel;
    protected $userModel;
    protected $validationRules;

    public function __construct()
    {
        $this->parentModel = new UnitParentModel();
        $this->unitModel   = new UnitModel();
        $this->userModel   = new UserModel();
        helper(['form', 'url']);

        $this->validationRules = [
            'parent_id' => 'required|is_natural_no_zero',
            'unit_name' => 'required|alpha_space|max_length[40]|min_length[3]',
            'status'    => 'required|in_list[1,2]',
        ];
    }

    /**
     * Redirect ke halaman daftar unit.
     *
     * @return \CodeIgniter\HTTP\RedirectResponse
     */
    public function index()
    {
        return redirect()->to('unit-list');
    }

    /**
     * Menampilkan form untuk membuat unit baru.
     *
     * @return string
     */
    public function create()
    {
        $fakultas = $this->parentModel
            ->where('status !=', 0)
            ->orderBy('name', 'ASC')
            ->findAll();

        $data = [
            'fakultas' => $fakultas,
            'title'    => 'Tambah Unit Baru'
        ];

        return view('DataMaster/unit-create', $data);
    }

    /**
     * Menyimpan data unit baru.
     * PERBAIKAN: Alert success akan muncul di halaman create unit
     *
     * @return \CodeIgniter\HTTP\RedirectResponse
     */
    public function store()
    {
        // Validasi input form
        if (!$this->validate($this->validationRules)) {
            return redirect()->back()->with('swal', [
                'icon'  => 'error',
                'title' => 'Validation Failed!',
                'text'  => implode("\n", $this->validator->getErrors()),
            ])->withInput();
        }

        $unitName = trim($this->request->getPost('unit_name'));
        $parentId = $this->request->getPost('parent_id');
        $status   = $this->request->getPost('status');

        // Validasi keberadaan parent_id di database
        $parentExists = $this->parentModel
            ->where('id', $parentId)
            ->where('status !=', 0)
            ->first();

        if (!$parentExists) {
            return redirect()->back()->with('swal', [
                'icon'  => 'error',
                'title' => 'Failed!',
                'text'  => 'Selected Faculty/Directorate is not valid.',
            ])->withInput();
        }

        // Cek duplikasi unit berdasarkan nama, parent_id, dan status
        $existingUnit = $this->unitModel
            ->where('LOWER(name)', strtolower($unitName))
            ->where('parent_id', $parentId)
            ->where('status', $status)
            ->first();

        if ($existingUnit) {
            return redirect()->back()->with('swal', [
                'icon'  => 'error',
                'title' => 'Failed!',
                'text'  => 'Unit name with the same status already exists.',
            ])->withInput();
        }

        // Persiapan data untuk insert
        $insertData = [
            'parent_id'  => $parentId,
            'name'       => $unitName,
            'status'     => $status,
            'created_at' => date('Y-m-d H:i:s'),
        ];

        // Insert data ke database
        $insertResult = $this->unitModel->insert($insertData);

        // PERBAIKAN UTAMA: Redirect back ke halaman create dengan pesan sukses
        // Form akan ter-reset dan menampilkan alert sukses
        if ($insertResult) {
            return redirect()->back()->with('swal', [
                'icon'  => 'success',
                'title' => 'Success!',
                'text'  => 'Unit has been added successfully.',
            ]);
        } else {
            // Jika insert gagal
            return redirect()->back()->with('swal', [
                'icon'  => 'error',
                'title' => 'Failed!',
                'text'  => 'An error occurred while saving unit data.',
            ])->withInput();
        }
    }

    /**
     * Menampilkan daftar unit.
     *
     * @return string
     */
    public function list()
    {
        // Join dengan tabel parent untuk mendapatkan nama fakultas/direktorat
        $units = $this->unitModel
            ->select('unit.*, unit_parent.name as parent_name')
            ->join('unit_parent', 'unit_parent.id = unit.parent_id', 'left')
            ->where('unit.status !=', 0) // Tidak tampilkan data yang sudah dihapus (soft delete)
            ->orderBy('unit.id', 'ASC')
            ->findAll();

        $fakultas = $this->parentModel
            ->where('status !=', 0)
            ->orderBy('name', 'ASC')
            ->findAll();

        $data = [
            'units'    => $units,
            'fakultas' => $fakultas,
            'title'    => 'Daftar Unit'
        ];

        return view('DataMaster/daftar-unit', $data);
    }

    /**
     * Menampilkan form untuk mengedit unit.
     *
     * @return string|\CodeIgniter\HTTP\RedirectResponse
     */
    public function edit()
    {
        $id = $this->request->getGet('id');

        // Validasi ID parameter
        if (!is_numeric($id) || $id <= 0) {
            return redirect()->to('unit-list')->with('swal', [
                'icon'  => 'error',
                'title' => 'Error!',
                'text'  => 'Invalid Unit ID.',
            ]);
        }

        // Cari data unit berdasarkan ID
        $unit = $this->unitModel
            ->where('id', $id)
            ->where('status !=', 0) // Pastikan unit tidak dalam status deleted
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
            ->orderBy('name', 'ASC')
            ->findAll();

        $data = [
            'unit'     => $unit,
            'fakultas' => $fakultas,
            'title'    => 'Edit Unit'
        ];

        return view('DataMaster/unit-edit', $data);
    }

    /**
     * Memperbarui data unit.
     *
     * @return \CodeIgniter\HTTP\RedirectResponse
     */
    public function update()
    {
        $id = $this->request->getPost('id');

        // Validasi ID parameter
        if (!is_numeric($id) || $id <= 0) {
            return redirect()->to('unit-list')->with('swal', [
                'icon'  => 'error',
                'title' => 'Error!',
                'text'  => 'Invalid Unit ID.',
            ]);
        }

        // Pastikan unit exists dan tidak dalam status deleted
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

        // Validasi input form
        if (!$this->validate($this->validationRules)) {
            return redirect()->back()->with('swal', [
                'icon'  => 'error',
                'title' => 'Validation Failed!',
                'text'  => implode("\n", $this->validator->getErrors()),
            ])->withInput();
        }

        $unitName = trim($this->request->getPost('unit_name'));
        $parentId = $this->request->getPost('parent_id');
        $status   = $this->request->getPost('status');

        // Cek duplikasi unit (exclude current ID)
        $existingUnit = $this->unitModel
            ->where('LOWER(name)', strtolower($unitName))
            ->where('parent_id', $parentId)
            ->where('status', $status)
            ->where('id !=', $id) // Exclude current record dari pengecekan duplikasi
            ->first();

        if ($existingUnit) {
            return redirect()->back()->with('swal', [
                'icon'  => 'error',
                'title' => 'Failed',
                'text'  => 'Unit name with the same status already exists.',
            ])->withInput();
        }

        // Persiapan data untuk update
        $updateData = [
            'parent_id'  => $parentId,
            'name'       => $unitName,
            'status'     => $status,
            'updated_at' => date('Y-m-d H:i:s')
        ];

        // Update data ke database
        $updateResult = $this->unitModel->update($id, $updateData);

        if ($updateResult) {
            return redirect()->to('unit-list')->with('swal', [
                'icon'  => 'success',
                'title' => 'Success',
                'text'  => 'Unit has been updated successfully.',
            ]);
        } else {
            return redirect()->back()->with('swal', [
                'icon'  => 'error',
                'title' => 'Failed!',
                'text'  => 'An error occurred while updating unit data.',
            ])->withInput();
        }
    }

    /**
     * Soft delete unit dan update user yang menggunakan unit ini.
     */
    public function delete()
    {
        $id = $this->request->getPost('id');

        // Validasi ID parameter
        if (!is_numeric($id) || $id <= 0) {
            return redirect()->to('unit-list')->with('swal', [
                'icon'  => 'error',
                'title' => 'Error!',
                'text'  => 'Invalid Unit ID.',
            ]);
        }

        // Cari data unit berdasarkan ID
        $unit = $this->unitModel->find($id);

        if (!$unit) {
            return redirect()->to('unit-list')->with('swal', [
                'icon'  => 'error',
                'title' => 'Error',
                'text'  => 'Unit not found.',
            ]);
        }

        // Update users yang menggunakan unit ini
        $this->userModel->where('unit_id', $id)->set(['unit_id' => null])->update();

        // Soft delete unit
        $deleteResult = $this->unitModel->update($id, [
            'status'     => 0,
            'updated_at' => date('Y-m-d H:i:s')
        ]);

        if ($deleteResult) {
            return redirect()->to('unit-list')->with('swal', [
                'icon'  => 'success',
                'title' => 'Success',
                'text'  => 'Unit has been deleted successfully.',
            ]);
        } else {
            return redirect()->to('unit-list')->with('swal', [
                'icon'  => 'error',
                'title' => 'Failed!',
                'text'  => 'An error occurred while deleting unit.',
            ]);
        }
    }
}