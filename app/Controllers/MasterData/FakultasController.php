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
        helper(['form', 'url']);
    }

    // Fungsi untuk menampilkan form tambah fakultas
    public function create()
    {
        return view('Faculty/TambahFakultas', [
            'title' => 'Tambah Fakultas'
        ]);
    }

    // Fungsi untuk menyimpan fakultas baru
    public function store()
    {
        $data = $this->request->getPost();

        // Validasi input
        if (!$this->unitParentModel->validate($data)) {
            return redirect()->to('/data-master/fakultas/create')->withInput()->with('errors', $this->unitParentModel->errors());
        }

        // Cek jika nama fakultas sudah ada
        if ($this->unitParentModel->where('name', $data['name'])->first()) {
            session()->setFlashdata('error', 'Nama fakultas sudah terdaftar.');
            return redirect()->to('/data-master/fakultas/create')->withInput();
        }

        // Simpan fakultas baru
        if ($this->unitParentModel->save($data)) {
            session()->setFlashdata('success', 'Fakultas baru berhasil ditambahkan.');
        } else {
            session()->setFlashdata('error', 'Fakultas gagal ditambahkan.');
        }

        return redirect()->to('/data-master/fakultas/list');
    }

    // Fungsi untuk menampilkan daftar fakultas (status != 0)
    public function index()
    {
        // Ambil data dengan status 1 (Active) dan 2 (Inactive), tapi tidak 0 (Deleted)
        $fakultas = $this->unitParentModel->where('status !=', 0)->findAll();

        return view('Faculty/DaftarFakultas', [
            'title'      => 'Daftar Fakultas',
            'unitParent' => $fakultas,
        ]);
    }

    // Fungsi untuk menghapus fakultas (soft delete, update status ke 0)
    public function delete($id)
    {
        try {
            // Cari data fakultas berdasarkan ID
            $fakultas = $this->unitParentModel->find($id);
            
            if (!$fakultas) {
                session()->setFlashdata('error', 'Data fakultas tidak ditemukan.');
                return redirect()->to('/data-master/fakultas/list');
            }

            // Update status menjadi 0 (soft delete)
            $updateData = [
                'status' => 0,
                'updated_at' => date('Y-m-d H:i:s')
            ];

            if ($this->unitParentModel->update($id, $updateData)) {
                session()->setFlashdata('success', 'Fakultas berhasil dihapus.');
            } else {
                session()->setFlashdata('error', 'Fakultas gagal dihapus.');
            }

        } catch (\Exception $e) {
            session()->setFlashdata('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }

        return redirect()->to('/data-master/fakultas/list');
    }

    // Fungsi untuk memperbarui data fakultas
    public function update($id)
    {
        try {
            $data = $this->request->getPost();

            // Validasi input
            if (!$this->unitParentModel->validate($data)) {
                session()->setFlashdata('error', 'Data tidak valid.');
                return redirect()->to('/data-master/fakultas/list');
            }

            // Cari fakultas berdasarkan ID
            $fakultas = $this->unitParentModel->find($id);
            if (!$fakultas) {
                session()->setFlashdata('error', 'Data fakultas tidak ditemukan.');
                return redirect()->to('/data-master/fakultas/list');
            }

            // Tambahkan timestamp untuk updated_at
            $data['updated_at'] = date('Y-m-d H:i:s');

            // Update data fakultas
            if ($this->unitParentModel->update($id, $data)) {
                session()->setFlashdata('success', 'Fakultas berhasil diperbarui.');
            } else {
                session()->setFlashdata('error', 'Fakultas gagal diperbarui.');
            }

        } catch (\Exception $e) {
            session()->setFlashdata('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }

        return redirect()->to('/data-master/fakultas/list');
    }
}
