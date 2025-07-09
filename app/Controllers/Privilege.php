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
        $this->roleModel      = new RoleModel();
        $this->submenuModel   = new SubmenuModel();
    }

    public function create()
    {
        $data = [
            'title'    => 'Tambah Privilege',
            'roles'    => $this->roleModel
                               ->where('status', 1)
                               ->orderBy('name')
                               ->findAll(),
            'submenus' => $this->submenuModel
                               ->select('submenu.id, submenu.name, menu.name as menu_name')
                               ->join('menu','menu.id = submenu.parent','left')
                               ->where('submenu.status', 1)
                               ->orderBy('menu.name, submenu.name')
                               ->findAll(),
        ];

        return view('Privilege/privilege-create', $data);
    }

    public function store()
    {
        $roleId   = $this->request->getPost('role');
        $submenu  = $this->request->getPost('submenu');
        $actions  = $this->request->getPost('privileges');

        if (! $this->roleModel->find($roleId)) {
            return $this->response->setJSON(['error'=>'Role tidak ditemukan'])->setStatusCode(404);
        }

        if (empty($submenu)) {
            return $this->response->setJSON(['error'=>'Pilih minimal satu submenu'])->setStatusCode(400);
        }

        foreach ($submenu as $sid) {
            if (! $this->submenuModel->find($sid)) continue;

            $this->privilegeModel->insert([
                'role_id'    => $roleId,
                'submenu_id' => $sid,
                'create'     => in_array('create',  $actions) ? 1 : 0,
                'update'     => in_array('update',  $actions) ? 1 : 0,
                'delete'     => in_array('delete',  $actions) ? 1 : 0,
                'approve'    => in_array('read',    $actions) ? 1 : 0,
            ]);
        }

        return $this->response->setJSON(['message'=>'Privilege berhasil disimpan']);
    }
}
