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

    public function __construct()
    {
        $this->unitParentModel = new UnitParentModel();
        $this->unitModel = new UnitModel();
        $this->userModel = new UserModel();
        $this->roleModel = new RoleModel();
        $this->userRoleModel = new UserRoleModel();
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
        $status = $this->request->getPost('status') === 'active' ? 1 : 2;

        // Log untuk debug
        log_message('debug', 'Form Data: ' . print_r($this->request->getPost(), true));

        // Validasi input kosong
        if (empty($fakultas) || empty($prodi) || empty($username) || empty($fullname) || empty($role)) {
            return redirect()->back()->withInput()->with('error', 'Semua field wajib diisi.')->with('showPopupError', true);
        }

        // Validasi karakter khusus
        if (preg_match('/[;:.,"\'<>!?@#$%^&*()+=]/', $username) || preg_match('/[;:.,"\'<>!?@#$%^&*()+=]/', $fullname)) {
            return redirect()->back()->withInput()->with('error', 'Username atau Full Name tidak boleh mengandung karakter khusus.')->with('showPopupError', true);
        }

        // Cek unit_parent_id
        $unitParent = $this->unitParentModel->where('name', $fakultas)->first();
        if (!$unitParent) {
            return redirect()->back()->withInput()->with('error', 'Fakultas/Direktorat tidak valid.')->with('showPopupError', true);
        }

        $unitParentId = $unitParent['id'];

        // Cek unit_id
        $unit = $this->unitModel->where('name', $prodi)->where('parent_id', $unitParentId)->first();
        if (!$unit) {
            return redirect()->back()->withInput()->with('error', 'Program Studi tidak cocok dengan Fakultas yang dipilih.')->with('showPopupError', true);
        }

        $unitId = $unit['id'];

        // Cek role_id
        $roleData = $this->roleModel->where('name', ucfirst($role))->first();
        if (!$roleData) {
            return redirect()->back()->withInput()->with('error', 'Role tidak valid.')->with('showPopupError', true);
        }

        $roleId = $roleData['id'];

        // Siapkan data user
        $userData = [
            'unit_id' => $unitId,
            'username' => $username,
            'fullname' => $fullname,
            'status' => $status,
            'createdby' => session()->get('user_id') ?? 1
        ];

        try {
            // Simpan user
            $this->userModel->insert($userData);
            $userId = $this->userModel->getInsertID();

            // Simpan user_role
            $userRoleData = [
                'user_id' => $userId,
                'role_id' => $roleId,
                'status' => $status,
                'createdby' => session()->get('user_id') ?? 1
            ];
            $this->userRoleModel->insert($userRoleData);

            return redirect()->to('CreateUser/create')->with('success', 'User berhasil ditambahkan!')->with('showPopup', true);
        } catch (\Exception $e) {
            log_message('error', 'Database Error: ' . $e->getMessage());
            return redirect()->back()->withInput()->with('error', 'Terjadi kesalahan saat menyimpan data.')->with('showPopupError', true);
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

        // Ambil semua data unit_parent (fakultas/direktorat), unit, dan role
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

    // Method untuk Delete User
    public function delete($id)
    {
        try {
            // Hapus user_role terlebih dahulu
            $this->userRoleModel->where('user_id', $id)->delete();
            
            // Hapus user
            $this->userModel->delete($id);
            
            return redirect()->to('CreateUser/list')->with('success', 'User berhasil dihapus!');
        } catch (\Exception $e) {
            log_message('error', 'Delete Error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal menghapus user.');
        }
    }

    // Method untuk Update User
    public function update()
    {
        $id = $this->request->getPost('id');
        $employee = $this->request->getPost('employee');
        $fakultas = $this->request->getPost('fakultas');
        $unit = $this->request->getPost('unit');
        $fullname = $this->request->getPost('fullname');
        $role = $this->request->getPost('role');

        // Validasi input
        if (empty($employee) || empty($fakultas) || empty($unit) || empty($fullname) || empty($role)) {
            return redirect()->back()->with('error', 'Semua field wajib diisi.');
        }

        try {
            // Update user data
            $userData = [
                'username' => $employee,
                'fullname' => $fullname,
                'unit_id' => $unit // Pastikan unit_id sesuai dengan dropdown
            ];
            
            $this->userModel->update($id, $userData);
            
            // Update user role jika ada
            $roleData = $this->roleModel->where('name', ucfirst($role))->first();
            if ($roleData) {
                $this->userRoleModel->where('user_id', $id)->set(['role_id' => $roleData['id']])->update();
            }

            return redirect()->to('CreateUser/list')->with('success', 'User berhasil diupdate!');
        } catch (\Exception $e) {
            log_message('error', 'Update Error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal mengupdate user.');
        }
    }

    // Method untuk Approve User
    public function approve($id)
    {
        try {
            // Update status user menjadi approved (1)
            $this->userModel->update($id, ['status' => 1]);
            
            // Update status user_role juga
            $this->userRoleModel->where('user_id', $id)->set(['status' => 1])->update();
            
            return redirect()->to('CreateUser/list')->with('success', 'User berhasil disetujui!');
        } catch (\Exception $e) {
            log_message('error', 'Approve Error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal menyetujui user.');
        }
    }

    // Method untuk Not Approve User
    public function notapprove()
    {
        $id = $this->request->getPost('id');
        $remark = $this->request->getPost('remark');

        try {
            // Update status user menjadi tidak disetujui (0) dan simpan remark
            $userData = [
                'status' => 0,
                'remark' => $remark
            ];
            
            $this->userModel->update($id, $userData);
            
            // Update status user_role juga
            $this->userRoleModel->where('user_id', $id)->set(['status' => 0])->update();
            
            return redirect()->to('CreateUser/list')->with('success', 'User berhasil ditolak!');
        } catch (\Exception $e) {
            log_message('error', 'Not Approve Error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal menolak user.');
        }
    }

    // Method untuk get unit berdasarkan parent (untuk AJAX)
    public function getUnits($parentId)
    {
        $units = $this->unitModel->where('parent_id', $parentId)->findAll();
        return $this->response->setJSON($units);
    }
}