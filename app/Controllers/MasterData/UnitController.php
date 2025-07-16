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

    /* ───────── CREATE ───────── */
    public function create()
    {
        $fakultas = $this->parentModel
            ->where('type', 2)   // 2 = Fakultas
            ->where('status', 1) // Aktif
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
                'label' => 'Fakultas',
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
            // Jika unit sudah ada, kirimkan pesan error ke view
            return redirect()->back()->withInput()->with('swal', [
                'icon'  => 'error',
                'title' => 'Gagal!',
                'text'  => 'Nama unit sudah terdaftar.',
            ]);
        }

        // Jika nama unit belum ada, lanjutkan untuk menambahkan unit baru
        $this->unitModel->insert([
            'parent_id' => $this->request->getPost('parent_id'),
            'name'      => $this->request->getPost('unit_name'),
            'status'    => 1,
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
            ->join('unit_parent', 'unit_parent.id = unit.parent_id') // Menggabungkan dengan tabel unit_parent (bukan unit_parents)
            ->select('unit.*, unit_parent.name as parent_name') // Mengambil nama fakultas sebagai parent_name
            ->findAll();

        // Kirim data ke view
        $data['units'] = $units;

        return view('DataMaster/daftar-unit', $data);
    }

    /* ───────── EDIT ───────── */
    public function edit($id)
    {
        $unit = $this->unitModel->find($id);
        if (! $unit) {
            throw PageNotFoundException::forPageNotFound();
        }

        $fakultas = $this->parentModel
            ->where('type', 2)
            ->where('status', 1)
            ->findAll();

        $data['unit']    = $unit;
        $data['fakultas'] = $fakultas;

        return view('DataMaster/unit-edit', $data);
    }

    /* ───────── UPDATE ───────── */
    public function update($id)
    {
        $rules = [
            'parent_id' => [
                'label' => 'Fakultas',
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

        $this->unitModel->update($id, [
            'parent_id' => $this->request->getPost('parent_id'),
            'name'      => $this->request->getPost('unit_name'),
        ]);

        return redirect()->to('data-master')->with('swal', [
            'icon'  => 'success',
            'title' => 'Berhasil!',
            'text'  => 'Unit berhasil diperbarui.',
        ]);
    }

    /* ───────── DELETE ───────── */
    public function delete($id)
    {
        $this->unitModel->delete($id);

        return redirect()->to('data-master')->with('swal', [
            'icon'  => 'success',
            'title' => 'Terhapus!',
            'text'  => 'Unit berhasil dihapus.',
        ]);
    }
}
