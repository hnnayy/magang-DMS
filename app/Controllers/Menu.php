<?php

namespace App\Controllers;

use App\Controllers\BaseController;

class Menu extends BaseController
{
    // Tampilkan form tambah menu
    public function index()
    {
        return view('Menu/create-menu', [
            'title' => 'Tambah Menu'
        ]);
    }

    // Proses simpan menu baru (sementara tidak berfungsi karena tidak pakai database)
    public function store()
    {
        // Hanya redirect saja tanpa simpan ke database
        return redirect()->to('/Menu/create-menu')->with('success', 'Simulasi: Menu berhasil ditambahkan.');
    }

    // Tampilkan daftar menu (pakai data dummy)
    public function viewList()
    {
        $data['menus'] = [
            ['id' => 1, 'menu_name' => 'Beranda'],
            ['id' => 2, 'menu_name' => 'Tentang Kami'],
            ['id' => 3, 'menu_name' => 'Kontak'],
        ];

        return view('Menu/lihat-menu', $data);
    }

    // Edit menu (dummy)
    public function edit($id)
    {
        // Dummy data untuk simulasi
        $data['menu'] = ['id' => $id, 'menu_name' => 'Menu Contoh', 'status' => 'aktif'];

        return view('Menu/edit-menu', $data);
    }

    // Update menu (dummy)
    public function update($id)
    {
        return redirect()->to('/Menu/lihat-menu')->with('success', 'Simulasi: Menu berhasil diperbarui.');
    }

    // Hapus menu (dummy)
    public function delete($id)
    {
        return redirect()->to('/Menu/lihat-menu')->with('success', 'Simulasi: Menu berhasil dihapus.');
    }
}
