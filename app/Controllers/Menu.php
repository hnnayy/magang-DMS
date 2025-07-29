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

    // Tampilkan form tambah menu
    public function create()
    {
        return view('Menu/create-menu', [
            'title' => 'Add Menu'
        ]);
    }

    // Tampilkan daftar menu
    public function list()
    {
        $data = [
            'title' => 'Menu List',
            'menus' => $this->menuModel->where('status !=', 0)->findAll()
        ];

        return view('Menu/lihat-menu', $data);
    }

    // Proses simpan menu baru
    public function store()
    {
        $validation = \Config\Services::validation();

        $rules = [
            'menu_name' => [
                'rules' => 'required|min_length[3]|max_length[50]|is_unique[menu.name]',
                'errors' => [
                    'required' => 'Menu name is required.',
                    'min_length' => 'Menu name must be at least 3 characters.',
                    'max_length' => 'Menu name must not exceed 50 characters.',
                    'is_unique' => 'Menu name is already used by another menu.'
                ]
            ],
            'icon' => [
                'rules' => 'required|regex_match[/^[a-z0-9\s\-]+$/]',
                'errors' => [
                    'required' => 'Icon is required.',
                    'regex_match' => 'Icon can only contain lowercase letters, numbers, spaces, and hyphens (-).'
                ]
            ],
            'status' => [
                'rules' => 'required|in_list[1,2]',
                'errors' => [
                    'required' => 'Status is required.',
                    'in_list' => 'Status is invalid.'
                ]
            ]
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()
                ->withInput()
                ->with('error', implode(' ', $validation->getErrors()));
        }

        $data = [
            'name'   => trim($this->request->getPost('menu_name')),
            'icon'   => trim($this->request->getPost('icon')), // tidak ubah spasi
            'status' => $this->request->getPost('status') == '1' ? 1 : 2,
        ];

        if ($this->menuModel->insert($data)) {
            return redirect()->back()->with('added_message', 'Successfully Added');
        }

        return redirect()->back()->with('error', 'Failed to add menu.');
    }

    // Proses update menu
    public function update()
    {
        $id = $this->request->getPost('id');

        if (!$id) {
            return redirect()->to(base_url('menu-list'))->with('error', 'ID menu tidak ditemukan.');
        }

        $validation = \Config\Services::validation();

        $rules = [
            'menu_name' => [
                'rules' => "required|min_length[3]|max_length[50]|is_unique[menu.name,id,{$id}]",
                'errors' => [
                    'required'   => 'Menu name is required.',
                    'min_length' => 'Menu name must be at least 3 characters.',
                    'max_length' => 'Menu name must not exceed 50 characters.',
                    'is_unique'  => 'Menu name is already used by another menu.'
                ]
            ],
            'icon' => [
                'rules' => 'required|regex_match[/^[a-z0-9\s\-]+$/]',
                'errors' => [
                    'required'    => 'Icon is required.',
                    'regex_match' => 'Icon can only contain lowercase letters, numbers, spaces, and hyphens (-).'
                ]
            ],
            'status' => [
                'rules' => 'required|in_list[1,2]',
                'errors' => [
                    'required' => 'Status is required.',
                    'in_list'  => 'Status is invalid'
                ]
            ]
        ];

        if (!$this->validate($rules)) {
            return redirect()->to(base_url('menu-list'))
                ->withInput()
                ->with('validation', $validation)
                ->with('error', 'Validation failed. Please check your input again');
        }

        $updateData = [
            'name'   => trim($this->request->getPost('menu_name')),
            'icon'   => trim($this->request->getPost('icon')), // tidak ubah spasi
            'status' => $this->request->getPost('status') == '1' ? 1 : 2,
        ];

        if ($this->menuModel->update($id, $updateData)) {
            return redirect()->to(base_url('menu-list'))->with('updated_message', 'Successfully Updated');
        }

        return redirect()->to(base_url('menu-list'))->with('error', 'Failed to update menu.');
    }

    // Soft delete menu (ubah status jadi 0)
    public function delete()
    {
        $id = $this->request->getPost('id');

        if (!$id) {
            return redirect()->to(base_url('menu-list'))->with('error', 'Menu ID is not found.');
        }

        if ($this->menuModel->update($id, ['status' => 0])) {
            return redirect()->to(base_url('menu-list'))->with('deleted_message', 'Successfully Deleted');
        }

        return redirect()->to(base_url('menu-list'))->with('error', 'Failed to delete menu.');
    }
}
