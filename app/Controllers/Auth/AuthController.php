<?php

namespace App\Controllers\Auth;

use App\Models\UnitParentModel;
use App\Models\UnitModel;
use App\Models\UserModel;
use App\Models\RoleModel;
use App\Models\UserRoleModel;
use App\Models\SubmenuModel;
use App\Models\PrivilegeModel;
use CodeIgniter\Controller;

class AuthController extends Controller
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
        $this->unitModel       = new UnitModel();
        $this->userModel       = new UserModel();
        $this->roleModel       = new RoleModel();
        $this->userRoleModel   = new UserRoleModel();
        $this->submenuModel    = new SubmenuModel();
        $this->privilegeModel  = new PrivilegeModel();
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
            'title'       => 'Create User',
        ];

        return view('Auth/register', $data);
    }

    public function store()
    {
        $parentId = $this->request->getPost('fakultas');
        $unitId   = $this->request->getPost('unit');
        $username = strtolower($this->request->getPost('username'));
        $fullname = $this->request->getPost('fullname');
        $roleName = $this->request->getPost('role');
        $status   = (int) $this->request->getPost('status');

        // Validasi field kosong
        if (
            empty($parentId) || empty($unitId) ||
            empty($username) || empty($fullname) || empty($roleName)
        ) {
            return redirect()->back()->withInput()
                ->with('error', 'Semua field wajib diisi.')
                ->with('showPopupError', true);
        }

        // Validasi karakter khusus
        if (
            preg_match('/[;:.,"\'<>!?@#$%^&*()+=]/', $username) ||
            preg_match('/[;:.,"\'<>!?@#$%^&*()+=]/', $fullname)
        ) {
            return redirect()->back()->withInput()
                ->with('error', 'Username atau Full Name tidak boleh mengandung karakter khusus.')
                ->with('showPopupError', true);
        }

        // Validasi Fakultas/Parent
        $parent = $this->unitParentModel->find($parentId);
        if (! $parent) {
            return redirect()->back()->withInput()
                ->with('error', 'Fakultas/Direktorat tidak valid.')
                ->with('showPopupError', true);
        }

        // Validasi Unit
        $unit = $this->unitModel
                    ->where('id', $unitId)
                    ->where('parent_id', $parentId)
                    ->first();
        if (! $unit) {
            return redirect()->back()->withInput()
                ->with('error', 'Unit tidak cocok dengan Fakultas yang dipilih.')
                ->with('showPopupError', true);
        }

        // Validasi Role
        $role = $this->roleModel->where('name', $roleName)->first();
        if (! $role) {
            return redirect()->back()->withInput()
                ->with('error', 'Role tidak valid.')
                ->with('showPopupError', true);
        }

        // Simpan user
        $this->userModel->insert([
            'unit_id'   => $unitId,
            'username'  => $username,
            'fullname'  => $fullname,
            'status'    => $status,
            'createdby' => session()->get('user_id') ?? 1,
        ]);
        $userId = $this->userModel->getInsertID();

        // Simpan ke user_role
        $this->userRoleModel->insert([
            'user_id'   => $userId,
            'role_id'   => $role['id'],
            'status'    => $status,
            'createdby' => session()->get('user_id') ?? 1,
        ]);

        return redirect()->to('auth/login')
            ->with('success', 'User berhasil ditambahkan!')
            ->with('showPopup', true);
    }

    public function getUnits($parentId)
    {
        if (! is_numeric($parentId)) {
            return $this->response->setStatusCode(400)->setJSON([
                'error' => 'ID tidak valid.'
            ]);
        }

        $units = $this->unitModel->where('parent_id', $parentId)->findAll();

        return $this->response->setJSON($units);
    }

    public function login()
{
    return view('Auth/login', ['title' => 'Login']);
}

public function doLogin()
{
    $username = $this->request->getPost('username');

   $user = $this->userModel
    ->select('user.*, role.name as role_name')
    ->join('user_role', 'user_role.user_id = user.id')
    ->join('role', 'role.id = user_role.role_id')
    ->where('user.username', $username)
    ->where('user.status', 1)
    ->first();


    if (! $user) {
        return redirect()->back()->withInput()->with('error', 'User tidak ditemukan atau tidak aktif.');
    }

    // Ambil semua submenu + privilege untuk role user
$privileges = $this->privilegeModel
    ->select('submenu.name as submenu_name, submenu.id as submenu_id, privilege.create, privilege.update, privilege.delete, privilege.approve')
    ->join('submenu', 'submenu.id = privilege.submenu_id')
    ->where('privilege.role_id', $user['role_id']) // pastikan role_id ini hasil dari user yang login
    ->findAll();

session()->set([
    'user_id'     => $user['id'],
    'fullname'    => $user['fullname'],
    'role_id'     => $user['role_id'],
    'role_name'   => $user['role_name'],
    'privileges'  => $privileges
]);


    return redirect()->to('/')->with('success', 'Login berhasil!');
}

public function logout()
{
    session()->destroy();
    return redirect()->to('/auth/login')->with('success', 'Berhasil logout.');
}

}
