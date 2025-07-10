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
        $roleId = $this->request->getPost('role');
        $submenus = $this->request->getPost('submenu');
        $actions = $this->request->getPost('privileges');

        if (! $this->roleModel->find($roleId)) {
            return $this->response->setJSON(['error' => 'Role tidak ditemukan'])->setStatusCode(404);
        }

        if (empty($submenus)) {
            return $this->response->setJSON(['error' => 'Pilih minimal satu submenu'])->setStatusCode(400);
        }

        // Hapus privilege lama untuk role ini agar tidak ganda
        $this->privilegeModel->where('role_id', $roleId)->delete();

        foreach ($submenus as $submenuId) {
            if (! $this->submenuModel->find($submenuId)) continue;

            $this->privilegeModel->insert([
                'role_id'    => $roleId,
                'submenu_id' => $submenuId,
                'create'     => in_array('create', $actions) ? 1 : 0,
                'read'       => in_array('read', $actions) ? 1 : 0,
                'update'     => in_array('update', $actions) ? 1 : 0,
                'delete'     => in_array('delete', $actions) ? 1 : 0,
            ]);
        }

        return $this->response->setJSON(['message' => 'Privilege berhasil disimpan']);
    }

    public function list()
    {
        $raw = $this->privilegeModel
            ->select('privilege.*, role.name as role_name, submenu.name as submenu_name')
            ->join('role', 'role.id = privilege.role_id')
            ->join('submenu', 'submenu.id = privilege.submenu_id')
            ->findAll();

        $grouped = [];

        foreach ($raw as $item) {
            $roleId = $item['role_id'];
            $roleName = $item['role_name'];

            if (!isset($grouped[$roleId])) {
                $grouped[$roleId] = [
                    'role'    => $roleName,
                    'submenu' => [],
                    'actions' => []
                ];
            }

            $grouped[$roleId]['submenu'][] = $item['submenu_name'];

            $actions = [];
            if ($item['create']) $actions[] = 'create';
            if ($item['read']) $actions[] = 'read';
            if ($item['update']) $actions[] = 'update';
            if ($item['delete']) $actions[] = 'delete';

            $grouped[$roleId]['actions'] = array_unique(array_merge(
                $grouped[$roleId]['actions'],
                $actions
            ));
        }

        return view('Privilege/lihat-privilege', [
            'title' => 'Lihat Privilege',
            'privileges' => array_values($grouped)
        ]);
    }
}
