<?php

namespace App\Controllers;

use App\Models\RoleModel;
use CodeIgniter\Controller;

class Role extends Controller
{
    protected $roleModel;

    public function __construct()
    {
        $this->roleModel = new RoleModel();
        helper('form'); // ⬅️ Tambahkan ini supaya set_radio, set_value, old bisa digunakan di view
    }

    // Menampilkan form tambah role
    public function create()
    {
        $data = ['title' => 'Tambah Role Baru'];
        return view('Role/role-create', $data);
    }

    // Menyimpan role baru
    public function store()
    {
        $nama   = $this->request->getPost('nama');
        $level  = $this->request->getPost('level');
        $desc   = $this->request->getPost('desc');
        $status = $this->request->getPost('status');

        if (empty($nama) || empty($level) || empty($desc) || empty($status)) {
            return redirect()->back()->withInput()->with('error', 'Semua field harus diisi.');
        }

        $this->roleModel->insert([
            'name'         => $nama,
            'access_level' => $level,
            'description'  => $desc,
            'status'       => ($status === 'active') ? 1 : 2, // 1: active, 2: inactive
        ]);

        session()->setFlashdata('success', 'Role baru berhasil ditambahkan.');
        return redirect()->to('/role/create');
    }

    // Menampilkan daftar role
    public function list()
    {
        $roles = $this->roleModel->where('status !=', 0)->findAll();
        return view('Role/lihat-role', ['roles' => $roles]);
    }

    // Soft delete
    public function delete($id)
    {
        $this->roleModel->update($id, ['status' => 0]);
        session()->setFlashdata('success', 'Role berhasil dihapus (soft delete).');
        return redirect()->to('/role/list');
    }

    // Update role
    public function update($id)
    {
        $namaRole = $this->request->getPost('role_name');
        $level    = $this->request->getPost('role_level');
        $desc     = $this->request->getPost('role_description');
        $status   = $this->request->getPost('role_status');

        if (empty($namaRole) || empty($level) || empty($desc) || empty($status)) {
            session()->setFlashdata('error', 'Semua field harus diisi.');
            return redirect()->to('/role/list')->withInput();
        }

        $this->roleModel->update($id, [
            'name'         => $namaRole,
            'access_level' => $level,
            'description'  => $desc,
            'status'       => ($status === 'active') ? 1 : 2,
        ]);

        session()->setFlashdata('success', 'Role berhasil diupdate.');
        return redirect()->to('/role/list');
    }
}
