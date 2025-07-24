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

    // Tampilkan semua menu yang tidak dihapus
    public function index()
    {
        $data['menus'] = $this->menuModel->where('status !=', 0)->findAll();
        return view('Menu/lihat-menu', $data);
    }

    // Alias dari index (kalau ada kebutuhan pemisahan rute)
    public function list()
    {
        return $this->index();
    }

    // Proses simpan menu baru
    public function store()
    {
        $validation = \Config\Services::validation();

        $rules = [
            'menu_name' => [
                'rules' => 'required|min_length[3]|max_length[50]|is_unique[menu.name]',
                'errors' => [
                    'required' => 'Nama menu wajib diisi.',
                    'min_length' => 'Nama menu minimal 3 karakter.',
                    'max_length' => 'Nama menu maksimal 50 karakter.',
                    'is_unique' => 'Nama menu sudah terdaftar.'
                ]
            ],
            'icon' => [
                'rules' => 'required|regex_match[/^[a-z0-9\s\-]+$/]',
                'errors' => [
                    'required' => 'Icon wajib diisi.',
                    'regex_match' => 'Icon hanya boleh huruf kecil, angka, spasi, dan tanda minus (-).'
                ]
            ],
            'status' => [
                'rules' => 'required|in_list[1,2]',
                'errors' => [
                    'required' => 'Status wajib dipilih.',
                    'in_list' => 'Status tidak valid.'
                ]
            ]
        ];

        // Validasi gagal
        if (!$this->validate($rules)) {
            return redirect()->back()
                ->withInput()
                ->with('error', implode(' ', $validation->getErrors()));
        }

        // Ubah icon: ganti spasi ke tanda minus
        $iconInput = $this->request->getPost('icon');
        $icon = str_replace(' ', '-', strtolower(trim($iconInput)));

        $data = [
            'name'   => trim($this->request->getPost('menu_name')),
            'icon'   => $icon,
            'status' => $this->request->getPost('status') == '1' ? 1 : 2,
        ];

        if ($this->menuModel->insert($data)) {
            return redirect()->back()->with('added_message', 'Successfully Added');
        }

        return redirect()->back()->with('error', 'Failed to add menu.');
    }

    // Soft delete menu (ubah status jadi 0)
    public function delete($id)
    {
        $this->menuModel->update($id, ['status' => 0]);
        session()->setFlashdata('deleted_message', 'Successfully Deleted');
        return redirect()->to(base_url('Menu'))->with('success', 'Failed to add menu');
    }

    // Proses update menu
    public function update($id)
    {
        $data = $this->request->getPost();
        $validation = \Config\Services::validation();

        $rules = [
            'menu_name' => [
                'rules' => "required|min_length[3]|max_length[50]|is_unique[menu.name,id,{$id}]",
                'errors' => [
                    'required'   => 'Nama menu wajib diisi.',
                    'min_length' => 'Nama menu minimal 3 karakter.',
                    'max_length' => 'Nama menu maksimal 50 karakter.',
                    'is_unique'  => 'Nama menu sudah digunakan oleh menu lain.'
                ]
            ],
            'icon' => [
                'rules' => 'required|regex_match[/^[a-z0-9\s-]+$/]',
                'errors' => [
                    'required'    => 'Icon wajib diisi.',
                    'regex_match' =>  'Icon hanya boleh huruf kecil, angka, spasi, dan tanda minus (-).'
                ]
            ],
            'status' => [
                'rules' => 'required|in_list[1,2]',
                'errors' => [
                    'required' => 'Status wajib dipilih.',
                    'in_list'  => 'Status tidak valid.'
                ]
            ]
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()
                ->withInput()
                ->with('validation', $validation)
                ->with('error', 'Validasi gagal. Silakan periksa kembali input Anda.');
        }

        $icon = str_replace(' ', '-', strtolower(trim($data['icon'])));

        $updateData = [
            'name'   => trim($data['menu_name']),
            'icon'   => $icon,
            'status' => $data['status'] == '1' ? 1 : 2,
        ];

        $this->menuModel->update($id, $updateData);

        return redirect()->to(base_url('Menu'))->with('updated_message', 'Successfully Updated');
    }
}
