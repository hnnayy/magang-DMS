<?php

namespace App\Controllers;

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
        $data = ['title' => 'Tambah Fakultas'];
        return view('Faculty/TambahFakultas', $data);
    }

    public function store()
    {
        $name   = $this->request->getPost('name');
        $type   = $this->request->getPost('type');
        $status = $this->request->getPost('status');

        if (empty($name) || empty($type) || empty($status)) {
            session()->setFlashdata('error', 'Semua field harus diisi.');
            return redirect()->to('/fakultas/create')->withInput();
        }

        $this->unitParentModel->insert([
            'name'   => $name,
            'type'   => $type,
            'status' => $status,
        ]);

        session()->setFlashdata('success', 'Fakultas baru berhasil ditambahkan.');
        return redirect()->to('/fakultas/create');
    }

    public function index()
    {
        $fakultas = $this->unitParentModel->where('status !=', 0)->findAll();

        return view('Faculty/DaftarFakultas', [
            'title'      => 'Daftar Fakultas',
            'unitParent' => $fakultas,
        ]);
    }

    // Versi AJAX soft delete
    public function softDelete($id)
    {
        if ($this->request->isAJAX()) {
            $this->unitParentModel->update($id, ['status' => 0]);
            return $this->response->setJSON(['success' => true, 'message' => 'Fakultas berhasil dihapus (soft delete).']);
        }

        // Fallback biasa
        $this->unitParentModel->update($id, ['status' => 0]);
        session()->setFlashdata('success', 'Fakultas berhasil dihapus (soft delete).');
        return redirect()->to('/fakultas');
    }

    // Versi AJAX update
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

        // Fallback biasa (redirect) kalau bukan AJAX
        $name   = $this->request->getPost('name');
        $type   = $this->request->getPost('type');
        $status = $this->request->getPost('status');

        if (empty($name) || empty($type) || empty($status)) {
            session()->setFlashdata('error', 'Semua field harus diisi.');
            return redirect()->to('/fakultas')->withInput();
        }

        $this->unitParentModel->update($id, [
            'name'   => $name,
            'type'   => $type,
            'status' => $status,
        ]);

        session()->setFlashdata('success', 'Fakultas berhasil diperbarui.');
        return redirect()->to('/fakultas');
    }
}
