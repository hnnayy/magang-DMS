<?php

namespace App\Controllers;

use App\Models\PrivilegeModel;
use App\Models\RoleModel;
use App\Models\SubmenuModel;
use CodeIgniter\Controller;

class Privilege extends Controller
{
    protected $privilegeModel;
    protected $roleModel;
    protected $submenuModel;

    public function __construct()
    {
        $this->privilegeModel = new PrivilegeModel();
        $this->roleModel = new RoleModel();
        $this->submenuModel = new SubmenuModel();
    }

    public function create()
    {
        $data = [
            'title' => 'Tambah Privilege',
            'roles' => $this->roleModel
                            ->where('status', 1)
                            ->orderBy('name')
                            ->findAll(),
            'submenus' => $this->submenuModel
                               ->select('submenu.id, submenu.name, menu.name as menu_name')
                               ->join('menu', 'menu.id = submenu.parent', 'left')
                               ->where('submenu.status', 1)
                               ->orderBy('menu.name, submenu.name')
                               ->findAll(),
        ];

        return view('Privilege/privilege-create', $data);
    }

    public function store()
    {
        $roleId   = $this->request->getPost('role');
        $submenus = $this->request->getPost('submenu');
        $actions  = (array) $this->request->getPost('privileges');

        if (!$this->roleModel->find($roleId)) {
            return $this->response->setJSON(['error' => 'Role tidak ditemukan'])->setStatusCode(404);
        }

        if (empty($submenus)) {
            return $this->response->setJSON(['error' => 'Pilih minimal satu submenu'])->setStatusCode(400);
        }

        foreach ($submenus as $submenuId) {
            if (!$this->submenuModel->find($submenuId)) continue;

            $data = [
                'can_create'  => in_array('create', $actions) ? 1 : 0,
                'can_update'  => in_array('update', $actions) ? 1 : 0,
                'can_delete'  => in_array('delete', $actions) ? 1 : 0,
                'can_approve' => in_array('approve', $actions) ? 1 : 0,
            ];

            $existing = $this->privilegeModel
                ->where('role_id', $roleId)
                ->where('submenu_id', $submenuId)
                ->first();

            if ($existing) {
                $this->privilegeModel
                    ->where('role_id', $roleId)
                    ->where('submenu_id', $submenuId)
                    ->set($data)
                    ->update();
            } else {
                $data['role_id'] = $roleId;
                $data['submenu_id'] = $submenuId;
                $this->privilegeModel->insert($data);
            }
        }

        return redirect()->to(base_url('privilege/create'))->with('success', 'Privilege berhasil disimpan');
    }

    public function list()
    {
        $raw = $this->privilegeModel
            ->select('privilege.id, privilege.role_id, privilege.submenu_id, privilege.can_create, privilege.can_update, privilege.can_delete, privilege.can_approve, role.name as role_name, submenu.name as submenu_name, menu.name as menu_name')
            ->join('role', 'role.id = privilege.role_id')
            ->join('submenu', 'submenu.id = privilege.submenu_id')
            ->join('menu', 'menu.id = submenu.parent', 'left')
            ->orderBy('role.name, submenu.name')
            ->findAll();

        $result = [];

        foreach ($raw as $item) {
            $actions = [];
            if ($item['can_create'])  $actions[] = 'create';
            if ($item['can_update'])  $actions[] = 'update';
            if ($item['can_delete'])  $actions[] = 'delete';
            if ($item['can_approve']) $actions[] = 'approve';

            $result[] = [
                'id'          => $item['id'],
                'role_id'     => $item['role_id'],
                'role'        => $item['role_name'],
                'submenu'     => $item['menu_name'] . ' > ' . $item['submenu_name'],
                'submenu_ids' => [$item['submenu_id']],
                'actions'     => $actions
            ];
        }

        $submenus = $this->submenuModel
            ->select('submenu.id, submenu.name, menu.name as menu_name')
            ->join('menu', 'menu.id = submenu.parent', 'left')
            ->where('submenu.status', 1)
            ->orderBy('menu.name, submenu.name')
            ->findAll();

        return view('Privilege/lihat-privilege', [
            'title'      => 'Lihat Privilege',
            'privileges' => $result,
            'submenus'   => $submenus,
            'roles'      => $this->roleModel->where('status', 1)->orderBy('name')->findAll()
        ]);
    }

    public function update()
    {
        $id = $this->request->getPost('id');
        $roleId = $this->request->getPost('role');
        $submenus = (array) $this->request->getPost('submenu');
        $actions = (array) $this->request->getPost('privileges');

        if (!$id || !is_numeric($id)) {
            return $this->response->setJSON(['error' => 'ID privilege tidak valid'])->setStatusCode(400);
        }

        if (empty($submenus)) {
            return $this->response->setJSON(['error' => 'Pilih minimal satu submenu'])->setStatusCode(400);
        }

        $privilege = $this->privilegeModel->find($id);
        if (!$privilege) {
            return $this->response->setJSON(['error' => 'Privilege tidak ditemukan'])->setStatusCode(404);
        }

        // Jika hanya satu submenu, lakukan update langsung
        if (count($submenus) === 1) {
            $submenuId = $submenus[0];

            $data = [
                'role_id'     => $roleId,
                'submenu_id'  => $submenuId,
                'can_create'  => in_array('create', $actions) ? 1 : 0,
                'can_update'  => in_array('update', $actions) ? 1 : 0,
                'can_delete'  => in_array('delete', $actions) ? 1 : 0,
                'can_approve' => in_array('approve', $actions) ? 1 : 0,
            ];

            $this->privilegeModel->update($id, $data);
        } else {
            // Jika lebih dari satu submenu, hapus data lama lalu insert ulang
            $this->privilegeModel->delete($id);

            foreach ($submenus as $submenuId) {
                $data = [
                    'role_id'     => $roleId,
                    'submenu_id'  => $submenuId,
                    'can_create'  => in_array('create', $actions) ? 1 : 0,
                    'can_update'  => in_array('update', $actions) ? 1 : 0,
                    'can_delete'  => in_array('delete', $actions) ? 1 : 0,
                    'can_approve' => in_array('approve', $actions) ? 1 : 0,
                ];
                $this->privilegeModel->insert($data);
            }
        }

        return $this->response->setJSON(['message' => 'Privilege berhasil diperbarui']);
    }

    public function delete()
    {
        $id = $this->request->getPost('id');

        if (!$id || !is_numeric($id)) {
            return $this->response->setJSON(['error' => 'ID privilege tidak valid'])->setStatusCode(400);
        }

        $existing = $this->privilegeModel->find($id);
        if (!$existing) {
            return $this->response->setJSON(['error' => 'Privilege tidak ditemukan'])->setStatusCode(404);
        }

        try {
            $this->privilegeModel->delete($id);
        } catch (\Exception $e) {
            return $this->response->setJSON(['error' => 'Gagal menghapus privilege: ' . $e->getMessage()])->setStatusCode(500);
        }

        return $this->response->setJSON(['message' => 'Privilege berhasil dihapus']);
    }
}
