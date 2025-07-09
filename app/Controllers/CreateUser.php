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

        // Ambil role unik berdasarkan nama (misalnya admin, kepala unit, dll)
        $roles = $this->roleModel
                    ->select('MIN(id) as id, name') // ambil satu id acak (terkecil)
                    ->groupBy('name')
                    ->findAll();

        $data = [
            'unitParents' => $unitParents,
            'roles'       => $roles,
            'title'       => 'Create User'
        ];

        return view('CreateUser/users-create', $data);
    }


    /* ─────────────────────────  SIMPAN USER BARU  ───────────────────────── */
    public function store()
    {
        // -------- Ambil data POST --------
        $parentId = $this->request->getPost('fakultas');   // ID fakultas/direktorat
        $unitId   = $this->request->getPost('unit');       // ID unit/bagian
        $username = $this->request->getPost('username');
        $fullname = $this->request->getPost('fullname');
        $roleName = $this->request->getPost('role');       // nama role (e.g. admin)
        $status = (int) $this->request->getPost('status');

        // -------- Validasi field kosong --------
        if (
            empty($parentId) || empty($unitId) || empty($username) ||
            empty($fullname) || empty($roleName)
        ) {
            return redirect()->back()->withInput()
                            ->with('error', 'Semua field wajib diisi.')
                            ->with('showPopupError', true);
        }

        // -------- Validasi karakter khusus --------
        if (preg_match('/[;:.,"\'<>!?@#$%^&*()+=]/', $username) ||
            preg_match('/[;:.,"\'<>!?@#$%^&*()+=]/', $fullname)) {
            return redirect()->back()->withInput()
                            ->with('error', 'Username atau Full Name tidak boleh mengandung karakter khusus.')
                            ->with('showPopupError', true);
        }

        // -------- Cek keberadaan Fakultas & Unit --------
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

        // -------- Cek role --------
        $role = $this->roleModel->where('name', $roleName)->first();
        if (! $role) {
            return redirect()->back()->withInput()
                            ->with('error', 'Role tidak valid.')
                            ->with('showPopupError', true);
        }

        // -------- Siapkan & simpan user --------
        $this->userModel->insert([
            'unit_id'   => $unitId,
            'username'  => $username,
            'fullname'  => $fullname,
            'status'    => $status,
            'createdby' => session()->get('user_id') ?? 1
        ]);
        $userId = $this->userModel->getInsertID();

        // -------- Simpan user_role --------
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
            ->where('user.status', 1) // Tambahkan baris ini
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
        if (! $this->userModel->find($id)) {
            return $this->response->setStatusCode(404)
                                ->setJSON(['error' => 'User tidak ditemukan']);
        }

        // status = 0  ➜ dianggap soft‑deleted
        if ($this->userModel->softDeleteById($id)) {
            return $this->response->setJSON(['message' => 'User berhasil dihapus (soft)']);
        }

        return $this->response->setStatusCode(500)
                            ->setJSON(['error' => 'Gagal menghapus user']);
    }




    // Method untuk Update User
    /* ─────────────────────────  UPDATE USER  ───────────────────────── */
    public function update()
    {
        // 1. Ambil data POST
        $id        = $this->request->getPost('id');
        $username  = $this->request->getPost('employee');
        $parentId  = $this->request->getPost('fakultas'); // ID Fakultas
        $unitId    = $this->request->getPost('unit');     // ID Unit
        $fullname  = $this->request->getPost('fullname');
        $roleName  = $this->request->getPost('role');     // nama role
        $status = (int) $this->request->getPost('status');

        // 2. Validasi field kosong
        if (empty($parentId) || empty($unitId) || empty($username) ||
            empty($fullname) || empty($roleName)) {
            return $this->response->setStatusCode(400)
                                ->setJSON(['error' => 'Semua field wajib diisi.']);
        }

        // 3. Validasi Fakultas & Unit
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

        // 4. Validasi Role
        $role = $this->roleModel->where('name', $roleName)->first();
        if (! $role) {
            return $this->response->setJSON(['error' => 'Role tidak valid.']);
        }

        // 5. Update tabel user
        $this->userModel->update($id, [
            'username' => $username,
            'fullname' => $fullname,
            'unit_id'  => $unitId,
            'status'   => $status, 
        ]);

        // 6. Update / insert user_role
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

//    public function privilege()
//     {
//         return view('CreateUser/privilege');
// }

//Create role
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

        // Simpan ke database
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
        $submenu  = $this->request->getPost('submenu');   // array id
        $actions  = $this->request->getPost('privileges'); // create/update/delete/read

        // validasi role & submenu (pastikan ada di DB)
        if (! $this->roleModel->find($roleId))
            return $this->response->setJSON(['error'=>'Role tidak ditemukan'])->setStatusCode(404);

        if (empty($submenu))
            return $this->response->setJSON(['error'=>'Pilih minimal satu submenu'])->setStatusCode(400);

        // masukkan satu‑per‑satu submenu
        foreach ($submenu as $sid) {
            if (! $this->submenuModel->find($sid)) continue; // lewati yg tak valid

            $this->privilegeModel->insert([
                'role_id'    => $roleId,
                'submenu_id' => $sid,
                'create'     => in_array('create',  $actions) ? 1 : 0,
                'update'     => in_array('update',  $actions) ? 1 : 0,
                'delete'     => in_array('delete',  $actions) ? 1 : 0,
                'approve'    => in_array('read',    $actions) ? 1 : 0, // atau `read`
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



