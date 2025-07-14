<?php

namespace App\Controllers;

use App\Models\FakultasModel;
use CodeIgniter\Controller;

class FakultasController extends Controller
{
    // Menampilkan form tambah fakultas
    public function create()
    {
        $data = [
            'title' => 'Tambah Fakultas',  // Judul halaman
        ];
        return view('Faculty/TambahFakultas', $data);  // Pastikan pathnya benar
    }

    // Menyimpan data fakultas
    public function store()
    {
        $model = new FakultasModel();

        // Validasi inputan dari form
        if (!$this->validate([
            'nama'  => 'required|max_length[255]',
            'level' => 'required|in_list[1,2]',
            'status'=> 'required|in_list[active,inactive]',
        ])) {
            // Jika validasi gagal, kembalikan ke form dengan error
            return redirect()->to('/fakultas/create')->withInput()->with('errors', $this->validator->getErrors());
        }

        // Simpan data fakultas ke dalam database
        $model->save([
            'nama'   => $this->request->getPost('nama'),
            'level'  => $this->request->getPost('level'),
            'status' => $this->request->getPost('status'),
        ]);

        // Redirect ke halaman daftar fakultas dengan pesan sukses
        return redirect()->to('/fakultas')->with('success', 'Fakultas berhasil ditambahkan!');
    }
}
