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
    }

    public function create()
    {
        helper('form'); // <-- Tambahkan ini agar bisa pakai set_value & set_radio

        $data = [
            'title' => 'Tambah Fakultas',
        ];

        return view('Faculty/TambahFakultas', $data);
    }

    public function store()
    {
        $name   = $this->request->getPost('name');
        $type   = $this->request->getPost('type');
        $status = $this->request->getPost('status');

        // Validasi sederhana
        if (empty($name) || empty($type) || empty($status)) {
            session()->setFlashdata('error', 'Semua field harus diisi.');
            return redirect()->to('/data-master/fakultas/create')->withInput();
        }

        // Cek apakah nama fakultas sudah ada
        $existingFakultas = $this->unitParentModel->where('name', $name)->first();
        if ($existingFakultas) {
            session()->setFlashdata('error', 'Nama fakultas sudah terdaftar.');
            return redirect()->to('/data-master/fakultas/create')->withInput();
        }

        // Insert ke DB jika tidak ada duplikat
        $this->unitParentModel->insert([
            'name'   => $name,
            'type'   => $type,
            'status' => $status,
        ]);

        session()->setFlashdata('success', 'Fakultas baru berhasil ditambahkan.');
        return redirect()->to('/data-master/fakultas/create');
    }

    public function index()
    {
        $fakultas = $this->unitParentModel->where('status !=', 0)->findAll();

        return view('Faculty/DaftarFakultas', [
            'title'      => 'Daftar Fakultas',
            'unitParent' => $fakultas,
        ]);
    }

    public function softDelete($id)
    {
        if ($this->request->isAJAX()) {
            $this->unitParentModel->update($id, ['status' => 0]);
            return $this->response->setJSON(['success' => true, 'message' => 'Fakultas berhasil dihapus (soft delete).']);
        }

        $this->unitParentModel->update($id, ['status' => 0]);
        session()->setFlashdata('success', 'Fakultas berhasil dihapus (soft delete).');
        return redirect()->to('/data-master/fakultas/list');
    }

    public function update($id)
    {
        if ($this->request->isAJAX()) {
            $name   = $this->request->getPost('name');
            $type   = $this->request->getPost('type');
            $status = $this->request->getPost('status');

            if (empty($name) || empty($type) || empty($status)) {
                return $this->response->setJSON(['success' => false, 'message' => 'Semua field harus diisi.']);
            }

            $this->unitParentModel->update($id, [
                'name'   => $name,
                'type'   => $type,
                'status' => $status,
            ]);

            return $this->response->setJSON(['success' => true, 'message' => 'Fakultas berhasil diperbarui.']);
        }

        $name   = $this->request->getPost('name');
        $type   = $this->request->getPost('type');
        $status = $this->request->getPost('status');

        if (empty($name) || empty($type) || empty($status)) {
            session()->setFlashdata('error', 'Semua field harus diisi.');
            return redirect()->to('/data-master/fakultas/list')->withInput();
        }

        $this->unitParentModel->update($id, [
            'name'   => $name,
            'type'   => $type,
            'status' => $status,
        ]);

        session()->setFlashdata('success', 'Fakultas berhasil diperbarui.');
        return redirect()->to('/data-master/fakultas/list');
    }
}
