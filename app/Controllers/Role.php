<?php
namespace App\Controllers;

use App\Models\RoleModel;
use CodeIgniter\Controller;

class Role extends Controller
{
    protected $roleModel;
    
    public function __construct()
    {
        $this->roleModel = new RoleModel();
        helper(['form', 'url']); // form helper & url helper
    }

    
    //List role aktif
    public function index()
    {
        $roles = $this->roleModel
            ->where('status !=', 0) // hanya role aktif & non-aktif, exclude deleted
            ->orderBy('id', 'DESC')
            ->findAll();

        return view('Role/lihat-role', ['roles' => $roles]);
    }

    
    //Alias untuk route role-list
     
    public function list()
    {
        return $this->index();
    }

    //Form create role
    public function create()
    {
        return view('Role/role-create', [
            'title' => 'Add New Role'
        ]);
    }


     //Simpan role baru (versi utama)
    public function store()
    {
        $nama   = trim($this->request->getPost('nama'));
        $level  = trim($this->request->getPost('level'));
        $desc   = trim($this->request->getPost('desc'));
        $status = trim($this->request->getPost('status'));

        if (empty($nama) || empty($level) || empty($desc) || empty($status)) {
            return redirect()->back()->withInput()->with('error', 'All fields must be filled.');
        }

        // Cek duplikasi nama & level
        $exists = $this->roleModel
            ->where('name', $nama)
            ->where('access_level', $level)
            ->where('status !=', 0)
            ->first();

        if ($exists) {
            return redirect()->back()->withInput()->with('duplicate_error', 'Role name is already used by another role.');
        }

        try {
            $this->roleModel->insert([
                'name'         => $nama,
                'access_level' => $level,
                'description'  => $desc,
                'status'       => ($status === 'active') ? 1 : 2,
            ]);
            return redirect()->back()->with('added_message', 'Successfully Updated.');
        } catch (\Exception $e) {
            return redirect()->back()->withInput()->with('error', 'Failed to add role. Please try again.');
        }
    }

    
    //Alias untuk route create-role/store
    public function storeRole()
    {
        return $this->store();
    }

    
    // Update role
    public function update()
    {
        $id       = $this->request->getPost('id');
        $namaRole = trim($this->request->getPost('role_name'));
        $level    = trim($this->request->getPost('role_level'));
        $desc     = trim($this->request->getPost('role_description'));
        $status   = trim($this->request->getPost('role_status'));

        if (empty($id) || empty($namaRole) || empty($level) || empty($desc) || empty($status)) {
            return redirect()->back()->withInput()->with('error', 'All fields must be filled.');
        }

        $role = $this->roleModel->find($id);
        if (!$role) {
            return redirect()->back()->with('error', 'Role not found.');
        }

        // Cek duplikasi nama & level kecuali role ini
        $exists = $this->roleModel
            ->where('name', $namaRole)
            ->where('access_level', $level)
            ->where('status !=', 0)
            ->where('id !=', $id)
            ->first();

        if ($exists) {
            return redirect()->back()->with('duplicate_error', 'Role name is already used by another role.');
        }

        try {
            $this->roleModel->update($id, [
                'name'         => $namaRole,
                'access_level' => $level,
                'description'  => $desc,
                'status'       => ($status === 'active') ? 1 : 2,
            ]);
            return redirect()->to('/role-list')->with('updated_message', 'Successfully Updated.');
        } catch (\Exception $e) {
            return redirect()->back()->withInput()->with('error', 'Failed to update role. Please try again.');
        }
    }

    
    // Soft delete role
    public function delete()
    {
        $id = $this->request->getPost('id');

        $role = $this->roleModel->find($id);
        if (!$role) {
            return redirect()->back()->with('error', 'Role not found.');
        }

        try {
            $this->roleModel->update($id, ['status' => 0]);
            return redirect()->to('/role-list')->with('deleted_message', 'Successfully Deleted.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to delete role. Please try again.');
        }
    }
}