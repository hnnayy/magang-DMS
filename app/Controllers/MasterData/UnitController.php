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

    public function __construct()
    {
        $this->parentModel = new UnitParentModel();
        $this->unitModel   = new UnitModel();
        helper(['form']);
    }

    /* ───────── INDEX ───────── */
    public function index()
    {
        // Redirect ke daftar unit jika diakses melalui data-master
        return redirect()->to('data-master/unit/list');
    }

    /* ───────── CREATE ───────── */
    public function create()
    {
        // Mengambil semua unit berdasarkan status aktif dan type yang bisa berupa Fakultas (2) dan Directorate (1)
        $fakultas = $this->parentModel
            ->where('status', 1) // Aktif
            ->whereIn('type', [1, 2]) // Mengambil yang tipe Directorate (1) dan Fakultas (2)
            ->findAll();

        return view('DataMaster/unit-create', [
            'fakultas' => $fakultas,
        ]);
    }

    /* ───────── STORE ───────── */
    public function store()
    {
        $rules = [
            'parent_id' => [
                'label' => 'Fakultas/Directorate',
                'rules' => 'required|is_natural_no_zero',
                'errors' => [
                    'required' => '{field} wajib dipilih.',
                ],
            ],
            'unit_name' => [
                'label' => 'Unit',
                'rules' => 'required|alpha_space|max_length[40]',
                'errors' => [
                    'required'    => '{field} wajib diisi.',
                    'alpha_space' => '{field} hanya boleh huruf dan spasi.',
                    'max_length'  => '{field} maksimal 40 karakter.',
                ],
            ],
        ];

        // Validasi input
        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('swal', [
                'icon'  => 'error',
                'title' => 'Oops...',
                'text'  => implode("\n", $this->validator->getErrors()),
            ]);
        }

        // Pengecekan apakah unit dengan nama yang sama sudah ada
        $unitName = $this->request->getPost('unit_name');
        $existingUnit = $this->unitModel->where('name', $unitName)->first();

        if ($existingUnit) {
            return redirect()->back()->withInput()->with('swal', [
                'icon'  => 'error',
                'title' => 'Gagal!',
                'text'  => 'Nama unit sudah terdaftar.',
            ]);
        }

        // Menyimpan unit baru
        $this->unitModel->insert([
            'parent_id' => $this->request->getPost('parent_id'),
            'name'      => $this->request->getPost('unit_name'),
            'status'    => 1, // Status aktif
        ]);

        return redirect()->back()->withInput()->with('swal', [
            'icon'  => 'success',
            'title' => 'Berhasil!',
            'text'  => 'Unit berhasil ditambahkan.',
        ]);
    }

    /* ───────── LIST ───────── */
    public function list()
    {
        // Ambil semua unit dan gabungkan dengan nama fakultas (parent)
        $units = $this->unitModel
            ->join('unit_parent', 'unit_parent.id = unit.parent_id') // Menggabungkan dengan tabel unit_parent
            ->select('unit.*, unit_parent.name as parent_name') // Mengambil nama fakultas sebagai parent_name
            ->findAll();

        // Kirim data ke view
        $data['units'] = $units;

        return view('DataMaster/daftar-unit', $data);
    }
    public function edit($id)
{
    $unit = $this->unitModel->find($id);
    if (! $unit) {
        throw PageNotFoundException::forPageNotFound();
    }

    // Ambil data fakultas yang aktif
    $fakultas = $this->parentModel
        ->where('status', 1)  // Aktif
        ->whereIn('type', [1, 2]) // Mengambil yang tipe Directorate (1) dan Fakultas (2)
        ->findAll();

    // Kirim data unit dan fakultas ke view untuk di-edit
    $data['unit']    = $unit;
    $data['fakultas'] = $fakultas;  // Pastikan data fakultas ada

    return view('DataMaster/unit-edit', $data);
}





    /* ───────── UPDATE ───────── */
    public function update($id)
    {
        $rules = [
            'parent_id' => [
                'label' => 'Fakultas/Directorate',
                'rules' => 'required|is_natural_no_zero',
                'errors' => [
                    'required' => '{field} wajib dipilih.',
                ],
            ],
            'unit_name' => [
                'label' => 'Unit',
                'rules' => 'required|alpha_space|max_length[40]',
                'errors' => [
                    'required'    => '{field} wajib diisi.',
                    'alpha_space' => '{field} hanya boleh huruf dan spasi.',
                    'max_length'  => '{field} maksimal 40 karakter.',
                ],
            ],
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('swal', [
                'icon'  => 'error',
                'title' => 'Oops...',
                'text'  => implode("\n", $this->validator->getErrors()),
            ]);
        }

        // Update unit
        $this->unitModel->update($id, [
            'parent_id' => $this->request->getPost('parent_id'),
            'name'      => $this->request->getPost('unit_name'),
        ]);

        return redirect()->to('data-master/unit/list')->with('swal', [
            'icon'  => 'success',
            'title' => 'Berhasil!',
            'text'  => 'Unit berhasil diperbarui.',
        ]);
    }

    /* ───────── DELETE ───────── */
    public function delete($id)
    {
        $this->unitModel->delete($id);

        // Redirect ke daftar unit setelah penghapusan
        return redirect()->to('data-master/unit/list')->with('swal', [
            'icon'  => 'success',
            'title' => 'Terhapus!',
            'text'  => 'Unit berhasil dihapus.',
        ]);
    }
}
