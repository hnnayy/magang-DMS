<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\RoleModel;

class CreateRole extends BaseController
{
    protected $roleModel;

    public function __construct()
    {
        $this->roleModel = new RoleModel();
    }

    public function index()
    {
        $data = [
            'title' => 'Tambah Role Baru'
        ];

        return view('CreateUser/users-role', $data);
    }

    public function store()
    {
        $nama   = $this->request->getPost('nama');
        $level  = $this->request->getPost('level');
        $desc   = $this->request->getPost('desc');
        $status = $this->request->getPost('status');

        // Validasi sederhana (bisa dikembangkan)
        if (empty($nama) || empty($level) || empty($desc) || empty($status)) {
            return redirect()->back()->withInput()->with('error', 'Semua field harus diisi.');
        }

        // Simpan ke DB
        $this->roleModel->insert([
            'name'        => $nama,
            'level'       => $level,
            'description' => $desc,
            'status'      => $status
        ]);

        return redirect()->to('/create-role')->with('success', 'Role baru berhasil ditambahkan.');
    }
}
