<?php

// namespace App\Controllers;

// class CreateUser extends BaseController
// {
//     public function index()
//     {
//         return view('CreateUser/daftar-users', $data);
//     }

//     public function list()
//     {
//         return view('CreateUser/daftar-users');
//     }

//     public function create()
//     {
//         $rules  =>
//         return view('CreateUser/users-create');
//     }

    
// }

namespace App\Controllers;

use App\Models\UnitParentModel;
use App\Models\UnitModel;
use App\Models\UserModel;
use App\Models\RoleModel;
use CodeIgniter\Controller;

class CreateUser extends Controller
{
    protected $unitParentModel;
    protected $unitModel;
    protected $userModel;
    protected $roleModel;

    public function __construct()
    {
        $this->unitParentModel = new UnitParentModel();
        $this->unitModel = new UnitModel();
        $this->userModel = new UserModel();
        $this->roleModel = new RoleModel();
    }

    
    public function create()
    {
        $data = [
            'unitParents' => $this->unitParentModel->findAll(),
            'roles' => $this->roleModel->findAll(),
            'title' => 'Create User'
        ];
        return view('CreateUser/users-create', $data);
    }
    

    public function store()
    {
        // Ambil data dari form
        $fakultas = $this->request->getPost('fakultas');
        $prodi = $this->request->getPost('prodi');
        $username = $this->request->getPost('username');
        $fullname = $this->request->getPost('fullname');
        $role = $this->request->getPost('role');
        $status = $this->request->getPost('status') === 'active' ? 1 : 2; // Konversi ke 1/2

        // Log data untuk debugging
        log_message('debug', 'Form Data: ' . print_r($this->request->getPost(), true));

        // Validasi data
        if (empty($fakultas) || empty($prodi) || empty($username) || empty($fullname) || empty($role) || empty($status)) {
            return redirect()->back()->withInput()->with('error', 'All fields are required.');
        }

        // Cari unit_parent_id berdasarkan fakultas
        $unitParent = $this->unitParentModel->where('name', $fakultas)->first();
        if (!$unitParent) {
            log_message('debug', 'Unit Parent Query: ' . $this->unitParentModel->getLastQuery()); // Debugging
            return redirect()->back()->withInput()->with('error', 'Invalid Faculty/Directorate selected. Expected: ' . $fakultas);
        }
        $unitParentId = $unitParent['id'];

        // Cari unit_id berdasarkan prodi dan parent_id
        $unit = $this->unitModel->where('name', $prodi)->where('parent_id', $unitParentId)->first();
        if (!$unit) {
            log_message('debug', 'Unit Query: ' . $this->unitModel->getLastQuery()); // Debugging query
            return redirect()->back()->withInput()->with('error', 'Invalid Program Study selected for the chosen Faculty.');
        }
        $unitId = $unit['id'];

        // Cari role_id berdasarkan role
        $roleData = $this->roleModel->where('name', ucfirst($role))->first();
        if (!$roleData) {
            return redirect()->back()->withInput()->with('error', 'Invalid Role selected.');
        }
        $roleId = $roleData['id'];

        // Siapkan data untuk tabel user
        $userData = [
            'unit_id' => $unitId,
            'username' => $username,
            'fullname' => $fullname,
            'status' => $status, // Gunakan 1 atau 2
            'createdby' => session()->get('user_id') ?? 1
        ];

        // Simpan ke database
        try {
            $this->userModel->insert($userData);
            $userId = $this->userModel->getInsertID();

            // Simpan hubungan user-role
            $userRoleData = [
                'user_id' => $userId,
                'role_id' => $roleId,
                'status' => $status,
                'createdby' => session()->get('user_id') ?? 1
            ];

            $userRoleModel = new \App\Models\UserRoleModel();
            $userRoleModel->insert($userRoleData);

            return redirect()->to('CreateUser/create')->with('success', 'User created successfully!')->with('showPopup', true);
        } catch (\Exception $e) {
            log_message('error', 'Database Error: ' . $e->getMessage());
            return redirect()->back()->withInput()->with('error', 'An error occurred while saving data.');
        }
    }

    public function list()
    {
        $users = $this->userModel
            ->select('user.id, user.username, user.fullname, unit.name as unit_name, unit_parent.name as parent_name, role.name as role_name')
            ->join('unit', 'unit.id = user.unit_id')
            ->join('unit_parent', 'unit_parent.id = unit.parent_id')
            ->join('user_role', 'user_role.user_id = user.id', 'left')
            ->join('role', 'role.id = user_role.role_id', 'left')
            ->findAll();

        return view('CreateUser/daftar-users', ['users' => $users]);
    }

}