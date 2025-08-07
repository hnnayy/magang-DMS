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
        $units = $this->unitModel->findAll();

        $roles = $this->roleModel
                    ->select('MIN(id) as id, name') 
                    ->groupBy('name')
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

        // Validasi basic - tidak menggunakan popup, biarkan client-side validation menangani
        if (
            empty($parentId) || empty($unitId) || empty($username) ||
            empty($fullname) || empty($roleName)
        ) {
            // Kembali tanpa popup, biarkan invalid feedback bekerja
            return redirect()->back()->withInput();
        }

        // Validasi karakter khusus - gunakan popup karena ini validasi khusus
        if (preg_match('/[;:.,"\'<>!?@#$%^&*()+=]/', $username) ||
            preg_match('/[;:.,"\'<>!?@#$%^&*()+=]/', $fullname)) {
            return redirect()->back()->withInput()
                            ->with('error', 'Username or Full Name must not contain special characters.')
                            ->with('showPopupError', true);
        }

        // Validasi username sudah ada - gunakan popup karena ini validasi database
        if ($this->userModel->where('username', $username)->first()) {
            return redirect()->back()->withInput()
                            ->with('error', 'Username already in use.')
                            ->with('showPopupError', true);
        }

        // Validasi fakultas tidak ditemukan - gunakan popup karena ini kesalahan data
        $parent = $this->unitParentModel->find($parentId);
        if (! $parent) {
            return redirect()->back()->withInput()
                            ->with('error', 'Faculty/Directorate is invalid.')
                            ->with('showPopupError', true);
        }

        // Validasi unit tidak sesuai fakultas - gunakan popup karena ini kesalahan relasi data
        $unit = $this->unitModel
                    ->where('id', $unitId)
                    ->where('parent_id', $parentId)
                    ->first();
        if (! $unit) {
            return redirect()->back()->withInput()
                            ->with('error', 'Unit does not match the selected Faculty.')
                            ->with('showPopupError', true);
        }

        // Validasi role tidak ditemukan - gunakan popup karena ini kesalahan data
        $role = $this->roleModel->where('name', $roleName)->first();
        if (! $role) {
            return redirect()->back()->withInput()
                            ->with('error', 'Role is invalid.')
                            ->with('showPopupError', true);
        }

        // Insert data jika semua validasi berhasil
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

        return redirect()->back()->with('success', 'User successfully created.');
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
        ->join('unit', 'unit.id = user.unit_id', 'left') // Ubah ke LEFT JOIN
        ->join('unit_parent', 'unit_parent.id = unit.parent_id', 'left')
        ->join('user_role', 'user_role.user_id = user.id', 'left')
        ->join('role', 'role.id = user_role.role_id', 'left')
        ->where('user.status IS NOT NULL') // Sementara ganti kondisi
        ->findAll();

    log_message('debug', 'Users retrieved: ' . json_encode($users)); // Debug

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

    public function delete()
    {
        $id = $this->request->getPost('id');

        if (! $id || ! $this->userModel->find($id)) {
            return $this->response->setStatusCode(404)
                                  ->setJSON(['error' => 'User not found']);
        }

        if ($this->userModel->softDeleteById($id)) {
            return $this->response->setJSON(['deleted_message' => 'Successfully Deleted']);
        }

        return $this->response->setStatusCode(500)
                              ->setJSON(['error' => 'Failed Deleted User']);
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

        $role = $this->roleModel->where('name', $roleName)->first();
        if (! $role) {
            return $this->response->setJSON(['error' => 'Role note valid']);
        }

        $existingUser = $this->userModel
            ->where('username', $username)
            ->where('id !=', $id)
            ->first();

        if ($existingUser) {
            return $this->response->setStatusCode(400)
                                ->setJSON(['error' => 'Username is already used by another user.']);
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