<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\SubmenuModel;
use App\Models\MenuModel;

class SubmenuController extends BaseController
{
    protected $submenuModel;
    protected $menuModel;

    public function __construct()
    {
        $this->submenuModel = new SubmenuModel();
        $this->menuModel = new MenuModel();
    }

    public function create()
    {
        $data['menus'] = $this->menuModel->where('status', 1)->findAll();  // hanya menu aktif
        return view('Submenu/submenu-create', $data);
    }

public function store()
{
    $rules = [
        'parent'  => 'required|integer',
        'submenu' => [
            'label' => 'Submenu',
            'rules' => 'required|min_length[3]|max_length[40]|regex_match[/^\S+\s+\S+/]',
            'errors' => [
                'regex_match' => 'Submenu harus terdiri dari minimal dua kata.'
            ]
        ],
        'status'  => 'required|in_list[1,2]'
    ];

    if (! $this->validate($rules)) {
        $errors = $this->validator->getErrors();
        return redirect()->back()->withInput()->with('validation', $errors);
    }

    $parentId = $this->request->getPost('parent');
    $submenuName = trim(strtolower($this->request->getPost('submenu'))); // normalized lowercase & trimmed

    // Cek duplikat nama submenu dalam menu yang sama (ignore case & trim)
    $existing = $this->submenuModel
        ->where('parent', $parentId)
        ->where('LOWER(TRIM(name))', $submenuName)
        ->where('status !=', 0)
        ->first();

    if ($existing) {
        return redirect()->back()->withInput()->with('swal', [
            'icon'  => 'error',
            'title' => 'Gagal!',
            'text'  => 'Nama submenu sudah ada dalam menu yang sama.'
        ]);
    }

    $data = [
        'parent' => $parentId,
        'name'   => $this->request->getPost('submenu'),
        'status' => $this->request->getPost('status')
    ];

    $this->submenuModel->save($data);

    return redirect()->back()->with('swal', [
        'icon'  => 'success',
        'title' => 'Berhasil!',
        'text'  => 'Submenu berhasil ditambahkan.'
    ]);
}



    public function list()
    {
        $data['submenus'] = $this->submenuModel
            ->select('submenu.*, menu.name AS parent_name')
            ->join('menu', 'menu.id = submenu.parent')
            ->where('submenu.status !=', 0) // Exclude soft-deleted items
            ->findAll();

        $data['menus'] = $this->menuModel->whereIn('status', [1, 2])->findAll();

        return view('Submenu/lihat-submenu', $data);
    }

    public function edit($id)
    {
        $submenu = $this->submenuModel->find($id);
        if (! $submenu) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Submenu tidak ditemukan');
        }

        $data['menus']   = $this->menuModel->where('status', 1)->findAll();
        $data['submenu'] = $submenu;

        return view('Submenu/submenu-edit', $data);
    }

   public function update($id)
{
    $rules = [
        'parent'  => 'required|integer',
        'submenu' => [
            'label' => 'Submenu',
            'rules' => 'required|min_length[3]|max_length[40]|regex_match[/^\S+(?:\s+\S+)+$/]',
            'errors' => [
                'regex_match' => 'Submenu harus terdiri dari minimal dua kata.'
            ]
        ],
        'status'  => 'required|in_list[1,2]',
    ];

    if (! $this->validate($rules)) {
        return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
    }

    $existing = $this->submenuModel->find($id);
    if (! $existing) {
        throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Submenu tidak ditemukan');
    }

    $parentId = $this->request->getPost('parent');
    $submenuName = trim(strtolower($this->request->getPost('submenu')));

    // Cek duplikat (ignore case, trim, exclude current ID)
    $duplicate = $this->submenuModel
        ->where('parent', $parentId)
        ->where('LOWER(TRIM(name))', $submenuName)
        ->where('id !=', $id)
        ->where('status !=', 0)
        ->first();

    if ($duplicate) {
        return redirect()->back()->withInput()->with('swal', [
            'icon'  => 'error',
            'title' => 'Gagal!',
            'text'  => 'Nama submenu sudah digunakan pada menu yang sama.'
        ]);
    }

    $data = [
        'parent' => $parentId,
        'name'   => $this->request->getPost('submenu'),
        'status' => $this->request->getPost('status')
    ];

    $this->submenuModel->update($id, $data);

    return redirect()->to('/submenu/lihat-submenu')->with('swal', [
        'icon'  => 'success',
        'title' => 'Berhasil!',
        'text'  => 'Submenu berhasil diperbarui.'
    ]);
}



    public function delete($id)
    {
        if (! $this->submenuModel->find($id)) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Submenu tidak ditemukan');
        }

        // Soft delete by setting status to 0
        $this->submenuModel->update($id, ['status' => 0]);

        return redirect()->to('/submenu/lihat-submenu')->with('success', 'Submenu berhasil dihapus.');
    }
}
