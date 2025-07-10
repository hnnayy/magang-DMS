<?php
namespace App\Controllers;
use App\Controllers\BaseController;
use App\Models\MenuModel;
class Menu extends BaseController
{
    protected $menuModel;

    public function __construct()
    {
        $this->menuModel = new MenuModel(); 
    }

    public function create()
    {
        return view('Menu/create-menu', ['title' => 'Tambah Menu']);
    }

    public function index()
    {
        $data['menus'] = $this->menuModel->where('status !=', 0)->findAll();
        return view('Menu/lihat-menu', $data);
    }

    public function list()
    {
        $data['menus'] = $this->menuModel->where('status !=', 0)->findAll();
        return view('Menu/lihat-menu', $data);
    }

    public function store()
    {
        $validation = \Config\Services::validation();

        $rules = [
            'menu_name' => 'required|min_length[3]|max_length[50]',
            'icon'      => 'required|regex_match[/^[a-z0-9\-]+$/]'
        ];

        $data = [
            'name'   => $this->request->getPost('menu_name'),
            'icon'   => $this->request->getPost('icon'),
            'status' => $this->request->getPost('status') == '1' ? 1 : 2,
        ];

        if ($this->menuModel->insert($data)) {
                return redirect()->back()->with('success', 'Menu berhasil ditambahkan.');
            } else {
                return redirect()->back()->with('error', 'Gagal menambahkan menu.');
            }

        if (!$this->validate($rules)) {
            return view('Menu/create-menu', [
                'title' => 'Tambah Menu',
                'validation' => $validation,
            ]);
        }

    }

    public function delete($id)
    {
        $this->menuModel->update($id, ['status' => 0]);
        return redirect()->to(base_url('Menu'))->with('success', 'Menu berhasil dihapus.');
    }

    public function update($id)
    {
        $data = $this->request->getPost();
        $this->menuModel->update($id, [
            'name'   => $data['menu_name'],
            'icon'   => $data['icon'],
            'status' => $data['status'] == '1' ? 1 : 2, 
        ]);

        return redirect()->to(base_url('Menu'))->with('success', 'Menu berhasil diperbarui.');
    }


}
