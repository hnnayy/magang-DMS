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
        $data = ['title' => 'Tambah Fakultas Baru'];
        return view('Faculty/TambahFakultas', $data);
    }

    public function store()
    {
        $nama   = $this->request->getPost('name');
        $type   = $this->request->getPost('type');
        $status = $this->request->getPost('status');

        if (empty($nama) || empty($type) || empty($status)) {
            return redirect()->back()->withInput()->with('error', 'Semua field harus diisi.');
        }

        if ($this->unitParentModel->where('name', $nama)->first()) {
            return redirect()->back()->withInput()->with('error', 'Nama fakultas sudah terdaftar.');
        }

        $this->unitParentModel->insert([
            'name'        => $nama,
            'type'        => $type,
            'description' => null,
            'status'      => (int)$status,
        ]);

        session()->setFlashdata('success', 'Fakultas baru berhasil ditambahkan.');
        return redirect()->to('/data-master/fakultas/create');
    }

    public function index()
    {
        $fakultas = $this->unitParentModel->where('status !=', 0)->findAll();
        return view('Faculty/DaftarFakultas', ['unitParent' => $fakultas]);
    }

    public function delete($id)
    {
        $this->unitParentModel->update($id, ['status' => 0]);
        session()->setFlashdata('success', 'berhasil dihapus.');
        return redirect()->to('/data-master/fakultas/list');
    }

    public function update($id)
    {
        $nama   = $this->request->getPost('name');
        $type   = $this->request->getPost('type');
        $status = $this->request->getPost('status');

        if (empty($nama) || empty($type) || empty($status)) {
            session()->setFlashdata('error', 'Semua field harus diisi.');
            return redirect()->to('/data-master/fakultas/list');
        }

        $fakultas = $this->unitParentModel->find($id);
        if (!$fakultas) {
            session()->setFlashdata('error', 'Fakultas tidak ditemukan.');
            return redirect()->to('/data-master/fakultas/list');
        }

        $updateData = [
            'name'   => $nama,
            'type'   => $type,
            'status' => (int)$status
        ];

        log_message('debug', 'Update data: ' . json_encode($updateData));
        log_message('debug', 'Update ID: ' . $id);

        $result = $this->unitParentModel->update($id, $updateData);

        if ($result) {
            session()->setFlashdata('success', 'Fakultas berhasil diupdate.');
        } else {
            session()->setFlashdata('error', 'Gagal mengupdate fakultas.');
        }

        return redirect()->to('/data-master/fakultas/list');
    }
}
