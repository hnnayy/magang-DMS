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
        // Filter hanya unit parent dan role yang aktif
        $unitParents = $this->unitParentModel->where('status', 1)->findAll();
        $units = $this->unitModel->findAll();
        $roles = $this->roleModel
                    ->select('id, name') 
                    ->where('status', 1)
                    ->findAll();
                    
        $data = [
            'unitParents' => $unitParents,
            'roles'       => $roles,
            'units'       => $units,
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

        // Validasi basic fields
        if (
            empty($parentId) || empty($unitId) || empty($username) ||
            empty($fullname) || empty($roleName)
        ) {
            return redirect()->back()->withInput();
        }

        // Validasi karakter khusus
        if (preg_match('/[;:.,"\'<>!?@#$%^&*()+=]/', $username) ||
            preg_match('/[;:.,"\'<>!?@#$%^&*()+=]/', $fullname)) {
            return redirect()->back()->withInput()
                            ->with('error', 'Username or Full Name must not contain special characters.')
                            ->with('showPopupError', true);
        }

        // Validasi username sudah ada
        if ($this->userModel->where('username', $username)->first()) {
            return redirect()->back()->withInput()
                            ->with('error', 'Username already in use.')
                            ->with('showPopupError', true);
        }

        // Validasi fakultas tidak ditemukan
        $parent = $this->unitParentModel->find($parentId);
        if (! $parent) {
            return redirect()->back()->withInput()
                            ->with('error', 'Faculty/Directorate is invalid.')
                            ->with('showPopupError', true);
        }

        // Validasi unit tidak sesuai fakultas
        $unit = $this->unitModel
                    ->where('id', $unitId)
                    ->where('parent_id', $parentId)
                    ->first();
        if (! $unit) {
            return redirect()->back()->withInput()
                            ->with('error', 'Unit does not match the selected Faculty.')
                            ->with('showPopupError', true);
        }

        // Validasi role harus aktif
        $role = $this->roleModel
                    ->where('name', $roleName)
                    ->where('status', 1)
                    ->first();
        if (! $role) {
            return redirect()->back()->withInput()
                            ->with('error', 'Role is invalid or inactive.')
                            ->with('showPopupError', true);
        }

        // Insert data user
        $this->userModel->insert([
            'unit_id'   => $unitId,
            'username'  => $username,
            'fullname'  => $fullname,
            'status'    => $status,
            'createdby' => session()->get('user_id') ?? 1
        ]);

        $userId = $this->userModel->getInsertID();

        // Insert user role
        $this->userRoleModel->insert([
            'user_id'   => $userId,
            'role_id'   => $role['id'],
            'status'    => $status,
            'createdby' => session()->get('user_id') ?? 1
        ]);

        return redirect()->back()->with('success', 'User successfully created.');
    }
    
    public function list()
    {
        $users = $this->userModel
            ->select('
                user.id,
                user.username,
                user.fullname,
                user.status,
                unit.id           AS unit_id,
                unit.parent_id    AS parent_id,
                unit.name         AS unit_name,
                unit_parent.name  AS parent_name,
                role.name         AS role_name
            ')
            ->join('unit', 'unit.id = user.unit_id', 'left')
            ->join('unit_parent', 'unit_parent.id = unit.parent_id', 'left')
            ->join('user_role', 'user_role.user_id = user.id', 'left')
            ->join('role', 'role.id = user_role.role_id', 'left')
            ->where('user.status', 1)
            ->findAll();

        log_message('debug', 'Users retrieved: ' . json_encode($users));

        // Filter hanya data yang aktif untuk dropdown
        $unitParents = $this->unitParentModel->where('status', 1)->findAll();
        $units = $this->unitModel->findAll();
        $roles = $this->roleModel->where('status', 1)->findAll();
        
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

    public function delete()
    {
        $this->response->setContentType('application/json');
        
        $id = $this->request->getPost('id');

        if (! $id) {
            return $this->response->setStatusCode(400)
                                  ->setJSON(['error' => 'User ID is required']);
        }

        $user = $this->userModel->find($id);
        if (! $user) {
            return $this->response->setStatusCode(404)
                                  ->setJSON(['error' => 'User not found']);
        }

        try {
            $result = $this->userModel->softDeleteById($id);
            
            if ($result) {
                return $this->response->setJSON(['deleted_message' => 'Successfully Deleted']);
            } else {
                return $this->response->setStatusCode(500)
                                      ->setJSON(['error' => 'Failed to delete user']);
            }
        } catch (\Exception $e) {
            log_message('error', 'Delete user error: ' . $e->getMessage());
            return $this->response->setStatusCode(500)
                                  ->setJSON(['error' => 'An error occurred while deleting user']);
        }
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
                                ->setJSON(['error' => 'All fields are required..']);
        }

        $parent = $this->unitParentModel->find($parentId);
        if (! $parent) {
            return $this->response->setJSON(['error' => 'Fakultas not valid.']);
        }

        $unit = $this->unitModel->where('id', $unitId)
                                ->where('parent_id', $parentId)
                                ->first();
        if (! $unit) {
            return $this->response->setJSON(['error' => 'Unit does not match the faculty.']);
        }

        // Validasi role harus aktif saat update
        $role = $this->roleModel
                    ->where('name', $roleName)
                    ->where('status', 1)
                    ->first();
        if (! $role) {
            return $this->response->setJSON(['error' => 'Role not valid or inactive']);
        }

        $existingUser = $this->userModel
            ->where('username', $username)
            ->where('id !=', $id)
            ->first();

        if ($existingUser) {
            return $this->response->setStatusCode(400)
                                ->setJSON(['error' => 'Username is already used by another user.']);
        }

        // Update user data
        $this->userModel->update($id, [
            'username' => $username,
            'fullname' => $fullname,
            'unit_id'  => $unitId,
            'status'   => $status, 
        ]);

        // Update user role
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

        return $this->response->setJSON(['updated_message' => 'Successfully Updated']);
    }
    
    public function softDelete($id)
    {
        if ($this->userModel->delete($id)) {
            return $this->response->setJSON(['status' => 'ok']);
        }
        return $this->response->setStatusCode(500);
    }
}