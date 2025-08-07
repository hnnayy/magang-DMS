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
        $data['menus'] = $this->menuModel->where('status', 1)->findAll();
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
            // HAPUS withInput() agar form tidak menyimpan input
            return redirect()->back()->with('validation', $errors);
        }

        $parentId = $this->request->getPost('parent');
        $submenuName = trim(strtolower($this->request->getPost('submenu')));

        // Cek duplikat nama submenu dalam menu yang sama (ignore case & trim)
        $existing = $this->submenuModel
            ->where('parent', $parentId)
            ->where('LOWER(TRIM(name))', $submenuName)
            ->where('status !=', 0)
            ->first();

        if ($existing) {
            // HAPUS withInput() agar form kosong setelah error duplikasi
            return redirect()->back()->with('swal', [
                'icon'  => 'error',
                'title' => 'Failed!',
                'text'  => 'Submenu name is already used by another menu.'
            ]);
        }

        $data = [
            'parent' => $parentId,
            'name'   => $this->request->getPost('submenu'),
            'status' => $this->request->getPost('status')
        ];

        $this->submenuModel->save($data);

        return redirect()->to('create-submenu')->with('added_message', 'Successfully Added');
    }

    // Method untuk check duplicate secara real-time (opsional)
    public function checkDuplicate()
    {
        $parentId = $this->request->getPost('parent');
        $submenuName = trim(strtolower($this->request->getPost('submenu')));

        if (empty($parentId) || empty($submenuName)) {
            return $this->response->setJSON([
                'exists' => false
            ]);
        }

        $existing = $this->submenuModel
            ->where('parent', $parentId)
            ->where('LOWER(TRIM(name))', $submenuName)
            ->where('status !=', 0)
            ->first();

        return $this->response->setJSON([
            'exists' => $existing ? true : false
        ]);
    }

    public function list()
    {
        $data['submenus'] = $this->submenuModel
            ->select('submenu.*, menu.name AS parent_name')
            ->join('menu', 'menu.id = submenu.parent')
            ->where('submenu.status !=', 0)
            ->findAll();

        $data['menus'] = $this->menuModel->whereIn('status', [1, 2])->findAll();

        return view('Submenu/lihat-submenu', $data);
    }

    public function edit()
    {
        $id = $this->request->getGet('id');
        
        if (!$id) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('ID submenu tidak ditemukan');
        }

        $submenu = $this->submenuModel->find($id);
        if (! $submenu) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Submenu tidak ditemukan');
        }

        $data['menus']   = $this->menuModel->where('status', 1)->findAll();
        $data['submenu'] = $submenu;

        return view('Submenu/submenu-edit', $data);
    }

    public function update()
    {
        $id = $this->request->getPost('id');
        
        if (!$id) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('ID submenu tidak ditemukan');
        }

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
            // HAPUS withInput() untuk update juga
            return redirect()->back()->with('errors', $this->validator->getErrors());
        }

        $existing = $this->submenuModel->find($id);
        if (! $existing) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Submenu tidak ditemukan');
        }

        $parentId = $this->request->getPost('parent');
        $submenuName = trim(strtolower($this->request->getPost('submenu')));

        $duplicate = $this->submenuModel
            ->where('parent', $parentId)
            ->where('LOWER(TRIM(name))', $submenuName)
            ->where('id !=', $id)
            ->where('status !=', 0)
            ->first();

        if ($duplicate) {
            // HAPUS withInput() agar form kosong setelah error duplikasi
            return redirect()->back()->with('swal', [
                'icon'  => 'error',
                'title' => 'Failed!',
                'text'  => 'Menu name is already used by another menu.'
            ]);
        }

        $data = [
            'parent' => $parentId,
            'name'   => $this->request->getPost('submenu'),
            'status' => $this->request->getPost('status')
        ];

        $this->submenuModel->update($id, $data);

        return redirect()->back()->with('updated_message', 'Successfully Updated');
    }

    public function delete()
    {
        $id = $this->request->getPost('id');
        
        if (!$id) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('ID submenu tidak ditemukan');
        }

        if (! $this->submenuModel->find($id)) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Submenu tidak ditemukan');
        }

        $this->submenuModel->update($id, ['status' => 0]);

        session()->setFlashdata('deleted_message', 'Successfully Deleted');
        return redirect()->back()->with('success', 'Submenu successfully deleted.');
    }
}