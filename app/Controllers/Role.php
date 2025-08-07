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
        helper('form'); // Helper untuk set_radio, set_value, old di view
    }
    
    // Menampilkan form tambah role
    public function create()
    {
        $data = ['title' => 'Add New Role'];
        return view('Role/role-create', $data);
    }
    
    // Menampilkan daftar role
    public function list()
    {
        $roles = $this->roleModel->where('status !=', 0)->findAll();
        return view('Role/lihat-role', ['roles' => $roles]);
    }
    
    // Soft delete - menggunakan POST sesuai routes
    public function delete()
    {
        $id = $this->request->getPost('id');
        
        if (empty($id)) {
            return redirect()->back()->with('error', 'ID role not valid.');
        }
        
        // Cek apakah role exists
        $role = $this->roleModel->find($id);
        if (!$role) {
            return redirect()->back()->with('error', 'Role not found.');
        }
        
        try {
            $this->roleModel->update($id, ['status' => 0]);
            return redirect()->back()->with('deleted_message', 'Successfully Deleted.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to delete role. Please try again.');
        }
    }
    
    // Update role - menggunakan POST sesuai routes
    public function update()
    {
        $id       = $this->request->getPost('id');
        $namaRole = $this->request->getPost('role_name');
        $level    = $this->request->getPost('role_level');
        $desc     = $this->request->getPost('role_description');
        $status   = $this->request->getPost('role_status');
        
        // Validasi input
        if (empty($id) || empty($namaRole) || empty($level) || empty($desc) || empty($status)) {
            return redirect()->back()->withInput()->with('error', 'All fields must be filled.');
        }
        
        // Cek apakah role exists
        $role = $this->roleModel->find($id);
        if (!$role) {
            return redirect()->back()->with('error', 'Role not found.');
        }
        
        // Cek duplikasi nama dan level (kecuali untuk role yang sedang diupdate)
        $existingRole = $this->roleModel->where([
            'name' => $namaRole,
            'access_level' => $level,
            'status !=' => 0,
            'id !=' => $id  // Exclude current role being updated
        ])->first();
        
        if ($existingRole) {
            return redirect()->back()->with('duplicate_error', 'Role name is already used by another role.');
        }
        
        try {
            $this->roleModel->update($id, [
                'name'         => $namaRole,
                'access_level' => $level,
                'description'  => $desc,
                'status'       => ($status === 'active') ? 1 : 2,
            ]);
            return redirect()->back()->with('updated_message', 'Successfully Updated');
        } catch (\Exception $e) {
            return redirect()->back()->withInput()->with('error', 'Failed to update role. Please try again.');
        }
    }
    
    public function storeRole()
    {
        $nama   = $this->request->getPost('nama');
        $level  = $this->request->getPost('level');
        $desc   = $this->request->getPost('desc');
        $status = $this->request->getPost('status');
        
        if (empty($nama) || empty($level) || empty($desc) || empty($status)) {
            return redirect()->back()->with('error', 'All fields must be filled.');
        }
        
        // Cek duplikasi berdasarkan NAMA dan LEVEL yang sama persis
        // Contoh:
        // 1. "Admin" level "1" (Directorate) - OK
        // 2. "Admin" level "2" (Unit) - OK (nama sama, level beda)
        // 3. "Admin" level "1" (Directorate) - TIDAK BOLEH (persis sama dengan #1)
        $existingRole = $this->roleModel->where([
            'name' => $nama,
            'access_level' => $level,
            'status !=' => 0  // Hanya cek role yang tidak soft deleted
        ])->first();
        
        if ($existingRole) {
            return redirect()->back()->withInput()->with('duplicate_error', 'Role name is already used by another role.');
        }
        
        try {
            $this->roleModel->insert([
                'name'         => $nama,
                'access_level' => $level,
                'description'  => $desc,
                'status'       => ($status === 'active') ? 1 : 2,
            ]);
            
            return redirect()->back()->with('added_message', 'Successfully Added.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to add role. Please try again.');
        }
    }
}