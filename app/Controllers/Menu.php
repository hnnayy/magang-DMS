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
        return view('Menu/create-menu', [
            'title' => 'Add Menu'
        ]);
    }

    public function list()
    {
        $data = [
            'title' => 'Menu List',
            'menus' => $this->menuModel->where('status !=', 0)->findAll()
        ];
        return view('Menu/lihat-menu', $data);
    }

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
            return $this->response->setJSON([
                'success' => false,
                'message' => implode(' ', $validation->getErrors())
            ]);
        }

        $data = [
            'name'   => trim($this->request->getPost('menu_name')),
            'icon'   => trim($this->request->getPost('icon')),
            'status' => $this->request->getPost('status') == '1' ? 1 : 2,
        ];

        if ($this->menuModel->insert($data)) {
            return $this->response->setJSON(['success' => true]);
        }

        return $this->response->setJSON(['success' => false, 'message' => 'Failed to add menu.']);
    }

    public function update()
    {
        $id = $this->request->getPost('id');
        
        if (!$id) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'ID menu tidak ditemukan.'
            ]);
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
            return $this->response->setJSON([
                'success' => false,
                'message' => implode(' ', $validation->getErrors())
            ]);
        }

        $updateData = [
            'name'   => trim($this->request->getPost('menu_name')),
            'icon'   => trim($this->request->getPost('icon')),
            'status' => $this->request->getPost('status') == '1' ? 1 : 2,
        ];

        if ($this->menuModel->update($id, $updateData)) {
            return $this->response->setJSON(['success' => true]);
        }

        return $this->response->setJSON(['success' => false, 'message' => 'Failed to update menu.']);
    }

    public function delete()
    {
        $id = $this->request->getPost('id');
        
        if (!$id) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Menu ID is not found.'
            ]);
        }

        if ($this->menuModel->update($id, ['status' => 0])) {
            return $this->response->setJSON(['success' => true]);
        }

        return $this->response->setJSON(['success' => false, 'message' => 'Failed to delete menu.']);
    }
}
