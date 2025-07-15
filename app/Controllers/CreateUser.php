<?php

namespace App\Controllers;

use App\Models\UnitParentModel;
use App\Models\UnitModel;
use App\Models\UserModel;
use App\Models\RoleModel;
use App\Models\UserRoleModel;
use CodeIgniter\Controller;

class CreateUser extends Controller
{
    protected $unitParentModel;
    protected $unitModel;
    protected $userModel;
    protected $roleModel;
    protected $userRoleModel;
    protected $submenuModel;
    protected $privilegeModel;

    public function __construct()
    {
        $this->unitParentModel = new UnitParentModel();
        $this->unitModel = new UnitModel();
        $this->userModel = new UserModel();
        $this->roleModel = new RoleModel();
        $this->userRoleModel = new UserRoleModel();
        $this->submenuModel   = new \App\Models\SubmenuModel();
        $this->privilegeModel = new \App\Models\PrivilegeModel();
    }

    public function create()
    {
        $unitParents = $this->unitParentModel->findAll();
        $roles = $this->roleModel
                    ->select('MIN(id) as id, name') 
                    ->groupBy('name')
                    ->findAll();
        $data = [
            'unitParents' => $unitParents,
            'roles'       => $roles,
            'title'       => 'Create User'
        ];
        return view('CreateUser/users-create', $data);
    }

    public function store()
    {
        $parentId = $this->request->getPost('fakultas');  
        $unitId   = $this->request->getPost('unit');      
        $username = $this->request->getPost('username');
        $fullname = $this->request->getPost('fullname');
        $roleName = $this->request->getPost('role');       
        $status   = (int) $this->request->getPost('status');
        if (
            empty($parentId) || empty($unitId) || empty($username) ||
            empty($fullname) || empty($roleName)
        ) {
            return redirect()->back()->withInput()
                            ->with('error', 'Semua field wajib diisi.')
                            ->with('showPopupError', true);
        }
        if (preg_match('/[;:.,"\'<>!?@#$%^&*()+=]/', $username) ||
            preg_match('/[;:.,"\'<>!?@#$%^&*()+=]/', $fullname)) {
            return redirect()->back()->withInput()
                            ->with('error', 'Username atau Full Name tidak boleh mengandung karakter khusus.')
                            ->with('showPopupError', true);
        }
        if ($this->userModel->where('username', $username)->first()) {
            return redirect()->back()->withInput()
                            ->with('error', 'Username sudah digunakan.')
                            ->with('showPopupError', true);
        }
        $parent = $this->unitParentModel->find($parentId);
        if (! $parent) {
            return redirect()->back()->withInput()
                            ->with('error', 'Fakultas/Direktorat tidak valid.')
                            ->with('showPopupError', true);
        }
        $unit = $this->unitModel
                    ->where('id', $unitId)
                    ->where('parent_id', $parentId)
                    ->first();
        if (! $unit) {
            return redirect()->back()->withInput()
                            ->with('error', 'Unit tidak cocok dengan Fakultas yang dipilih.')
                            ->with('showPopupError', true);
        }
        $role = $this->roleModel->where('name', $roleName)->first();
        if (! $role) {
            return redirect()->back()->withInput()
                            ->with('error', 'Role tidak valid.')
                            ->with('showPopupError', true);
        }
        $this->userModel->insert([
            'unit_id'   => $unitId,
            'username'  => $username,
            'fullname'  => $fullname,
            'status'    => $status,
            'createdby' => session()->get('user_id') ?? 1
        ]);
        $userId = $this->userModel->getInsertID();
        $this->userRoleModel->insert([
            'user_id'   => $userId,
            'role_id'   => $role['id'],
            'status'    => $status,
            'createdby' => session()->get('user_id') ?? 1
        ]);

        return redirect()->to('create-user/create')
                        ->with('success', 'User berhasil ditambahkan!')
                        ->with('showPopup', true);
    }
    
    public function list()
    {
        $users = $this->userModel
            ->select('
                user.id,
                user.username,
                user.fullname,
                unit.id           AS unit_id,
                unit.parent_id    AS parent_id,
                unit.name         AS unit_name,
                unit_parent.name  AS parent_name,
                role.name         AS role_name
            ')
            ->join('unit', 'unit.id = user.unit_id')
            ->join('unit_parent', 'unit_parent.id = unit.parent_id')
            ->join('user_role', 'user_role.user_id = user.id', 'left')
            ->join('role', 'role.id = user_role.role_id', 'left')
            ->where('user.status', 1) 
            ->findAll();

        $unitParents = $this->unitParentModel->findAll();
        $units = $this->unitModel->findAll();
        $roles = $this->roleModel->findAll();
        return view('CreateUser/daftar-users', [
            'users' => $users,
            'unitParents' => $unitParents,
            'units' => $units,
            'roles' => $roles,
        ]);
    }

    public function index()
    {
        return redirect()->to('CreateUser/list');
    }

    public function delete($id)
    {
        if (! $this->userModel->find($id)) {
            return $this->response->setStatusCode(404)
                                ->setJSON(['error' => 'User tidak ditemukan']);
        }
        if ($this->userModel->softDeleteById($id)) {
            return $this->response->setJSON(['message' => 'User berhasil dihapus']);
        }
        return $this->response->setStatusCode(500)
                            ->setJSON(['error' => 'Gagal menghapus user']);
    }

    public function update()
    {
        $id        = $this->request->getPost('id');
        $username  = $this->request->getPost('username');
        $parentId  = $this->request->getPost('fakultas'); 
        $unitId    = $this->request->getPost('unit');     
        $fullname  = $this->request->getPost('fullname');
        $roleName  = $this->request->getPost('role');    
        $status = (int) $this->request->getPost('status');
        if (empty($parentId) || empty($unitId) || empty($username) ||
            empty($fullname) || empty($roleName)) {
            return $this->response->setStatusCode(400)
                                ->setJSON(['error' => 'Semua field wajib diisi.']);
        }
        $parent = $this->unitParentModel->find($parentId);
        if (! $parent) {
            return $this->response->setJSON(['error' => 'Fakultas tidak valid.']);
        }
        $unit = $this->unitModel->where('id', $unitId)
                                ->where('parent_id', $parentId)
                                ->first();
        if (! $unit) {
            return $this->response->setJSON(['error' => 'Unit tidak cocok dengan fakultas.']);
        }
        $role = $this->roleModel->where('name', $roleName)->first();
        if (! $role) {
            return $this->response->setJSON(['error' => 'Role tidak valid.']);
        }
        $existingUser = $this->userModel
            ->where('username', $username)
            ->where('id !=', $id)
            ->first();

        if ($existingUser) {
            return $this->response->setStatusCode(400)
                                ->setJSON(['error' => 'Username sudah digunakan oleh user lain.']);
        }
        $this->userModel->update($id, [
            'username' => $username,
            'fullname' => $fullname,
            'unit_id'  => $unitId,
            'status'   => $status, 
        ]);
        $exists = $this->userRoleModel->where('user_id', $id)->first();
        if ($exists) {
            $this->userRoleModel->where('user_id', $id)
                                ->set(['role_id' => $role['id']])
                                ->update();
        } else {
            $this->userRoleModel->insert([
                'user_id'   => $id,
                'role_id'   => $role['id'],
                'status'    => 1,
                'createdby' => session()->get('user_id') ?? 1,
            ]);
        }

        return $this->response->setJSON(['message' => 'User berhasil diperbarui']);
    }

    public function getUnits($parentId)
    {
        $units = $this->unitModel->where('parent_id', $parentId)->findAll();
        return $this->response->setJSON($units);
    }

    public function createRole()
    {
        $data = ['title' => 'Tambah Role Baru'];
        return view('CreateUser/users-role', $data);
    }

    public function storeRole()
    {
        $nama   = $this->request->getPost('nama');
        $level  = $this->request->getPost('level');
        $desc   = $this->request->getPost('desc');
        $status = $this->request->getPost('status');

        if (empty($nama) || empty($level) || empty($desc) || empty($status)) {
            return redirect()->back()->withInput()->with('error', 'Semua field harus diisi.');
        }
        $this->roleModel->insert([
            'name'         => $nama,
            'access_level' => $level,
            'description'  => $desc,
            'status'       => ($status === 'active') ? 1 : 2,
        ]);

        return redirect()->to('/create-user/user-role')->with('success', 'Role baru berhasil ditambahkan.');
    }

    public function privilege()
    {
        $data = [
            'title'    => 'Tambah Privilege',
            'roles'    => $this->roleModel
                               ->where('status', 1)
                               ->orderBy('name')
                               ->findAll(),
            'submenus' => $this->submenuModel
                               ->select('submenu.id, submenu.name,
                                         menu.name as menu_name')
                               ->join('menu','menu.id = submenu.parent','left')
                               ->where('submenu.status', 1)
                               ->orderBy('menu.name, submenu.name')
                               ->findAll(),
        ];
        return view('CreateUser/privilege', $data);
    }

    public function storePrivilege()
    {
        $roleId   = $this->request->getPost('role');
        $submenu  = $this->request->getPost('submenu');   
        $actions  = $this->request->getPost('privileges'); 
        if (! $this->roleModel->find($roleId))
            return $this->response->setJSON(['error'=>'Role tidak ditemukan'])->setStatusCode(404);
        if (empty($submenu))
            return $this->response->setJSON(['error'=>'Pilih minimal satu submenu'])->setStatusCode(400);
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

    public function softDelete($id)
    {
        if ($this->userModel->delete($id)) {
            return $this->response->setJSON(['status' => 'ok']);
        }
        return $this->response->setStatusCode(500);
    }
}



