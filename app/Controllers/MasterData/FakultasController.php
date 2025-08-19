<?php
namespace App\Controllers\MasterData;

use App\Models\UnitParentModel;
use CodeIgniter\Controller;

class FakultasController extends Controller
{
    protected $unitParentModel;
    
    public function __construct()
    {
        $this->unitParentModel = new UnitParentModel();
        helper('form');
    }
    
    public function create()
    {
        $data = ['title' => 'Create Faculty'];
        return view('Faculty/TambahFakultas', $data);
    }
    
    public function store()
    {
        $nama   = $this->request->getPost('name');
        $status = $this->request->getPost('status');
        
        // Debug untuk memastikan data diterima
        log_message('debug', 'Store method called with data: ' . json_encode([
            'nama' => $nama,
            'status' => $status
        ]));
        
        // HAPUS VALIDASI FIELD KOSONG - biarkan client-side yang handle
        // Hanya validasi untuk duplicate check (tetap pake modal)
        
        // Cek apakah nama fakultas sudah ada (hanya untuk type=2/Faculty)
        if ($this->unitParentModel->where('name', $nama)->where('type', 2)->first()) {
            session()->setFlashdata('swal', [
                'icon'  => 'error',
                'title' => 'Error!',
                'text'  => 'Faculty name already exists.',
            ]);
            
            // Redirect tanpa withInput() agar form kosong
            return redirect()->back();
        }
        
        try {
            $result = $this->unitParentModel->insert([
                'type'        => 2, // 2=Faculty
                'name'        => $nama,
                'status'      => (int)$status,
            ]);
            
            if ($result) {
                session()->setFlashdata('swal', [
                    'icon'  => 'success',
                    'title' => 'Success',
                    'text'  => 'Successfully Added.',
                ]);
                
                log_message('debug', 'Success alert set in session');
                
                // Redirect ke halaman create untuk form baru yang kosong
                return redirect()->to('create-faculty');
            } else {
                throw new \Exception('Insert failed');
            }
            
        } catch (\Exception $e) {
            log_message('error', 'Error inserting faculty: ' . $e->getMessage());
            
            session()->setFlashdata('swal', [
                'icon'  => 'error',
                'title' => 'Error!',
                'text'  => 'Failed to add faculty. Please try again.',
            ]);
            
            // Redirect tanpa withInput() agar form kosong
            return redirect()->back();
        }
    }
    
    public function index()
    {
        $fakultas = $this->unitParentModel
            ->where('type', 2) // 2=Faculty
            ->where('status !=', 0)
            ->findAll();
        return view('Faculty/DaftarFakultas', ['unitParent' => $fakultas]);
    }
    
    public function edit()
    {
        $id = $this->request->getGet('id');
        
        if (empty($id)) {
            session()->setFlashdata('swal', [
                'icon'  => 'error',
                'title' => 'Error!',
                'text'  => 'Faculty ID is required.',
            ]);
            return redirect()->to('faculty-list');
        }
        
        $fakultas = $this->unitParentModel
            ->where('id', $id)
            ->where('type', 2) // 2=Faculty
            ->first();
        if (!$fakultas) {
            session()->setFlashdata('swal', [
                'icon'  => 'error',
                'title' => 'Error!',
                'text'  => 'Faculty not found.',
            ]);
            return redirect()->to('faculty-list');
        }
        
        $data = [
            'title' => 'Edit Faculty',
            'fakultas' => $fakultas
        ];
        
        return view('Faculty/EditFakultas', $data);
    }
    
    public function update()
    {
        $id     = $this->request->getPost('id');
        $nama   = $this->request->getPost('name');
        $status = $this->request->getPost('status');
        
        // Debug log
        log_message('debug', 'Update method called with data: ' . json_encode([
            'id' => $id,
            'nama' => $nama,
            'status' => $status
        ]));
        
        if (empty($id)) {
            session()->setFlashdata('swal', [
                'icon'  => 'error',
                'title' => 'Error!',
                'text'  => 'Faculty ID is required.',
            ]);
            return redirect()->to('faculty-list');
        }
        
        $fakultas = $this->unitParentModel
            ->where('id', $id)
            ->where('type', 2) // 2=Faculty
            ->first();
        if (!$fakultas) {
            session()->setFlashdata('swal', [
                'icon'  => 'error',
                'title' => 'Error!',
                'text'  => 'Faculty not found.',
            ]);
            return redirect()->to('faculty-list');
        }
        
        // Check if name already exists (exclude current record, only for Faculty type)
        $existingFakultas = $this->unitParentModel
            ->where('name', $nama)
            ->where('type', 2) // 2=Faculty
            ->where('id !=', $id)
            ->first();
        if ($existingFakultas) {
            session()->setFlashdata('swal', [
                'icon'  => 'error',
                'title' => 'Error!',
                'text'  => 'Faculty name already exists.',
            ]);
            return redirect()->to('faculty-list');
        }
        
        try {
            $updateData = [
                'name'   => $nama,
                'status' => (int)$status,
                // timestamp akan otomatis di-update oleh model
            ];
            
            $result = $this->unitParentModel->update($id, $updateData);
            
            if ($result) {
                session()->setFlashdata('swal', [
                    'icon'  => 'success',
                    'title' => 'Success',
                    'text'  => 'Successfully Updated.',
                ]);
                log_message('debug', 'Update success alert set in session');
            } else {
                throw new \Exception('Update failed');
            }
            
        } catch (\Exception $e) {
            log_message('error', 'Error updating faculty: ' . $e->getMessage());
            
            session()->setFlashdata('swal', [
                'icon'  => 'error',
                'title' => 'Error!',
                'text'  => 'Failed to update faculty. Please try again.',
            ]);
        }
        
        return redirect()->to('faculty-list');
    }
    
    public function delete()
    {
        $id = $this->request->getPost('id');
        
        // Debug log
        log_message('debug', 'Delete method called with id: ' . $id);
        
        if (empty($id)) {
            session()->setFlashdata('swal', [
                'icon'  => 'error',
                'title' => 'Error!',
                'text'  => 'Faculty ID is required.',
            ]);
            return redirect()->to('faculty-list');
        }
        
        $fakultas = $this->unitParentModel->find($id);
        if (!$fakultas) {
            session()->setFlashdata('swal', [
                'icon'  => 'error',
                'title' => 'Error!',
                'text'  => 'Faculty not found.',
            ]);
            return redirect()->to('faculty-list');
        }
        
        try {
            $result = $this->unitParentModel->update($id, ['status' => 0]);
            
            if ($result) {
                session()->setFlashdata('swal', [
                    'icon'  => 'success',
                    'title' => 'Success',
                    'text'  => 'Successfully deleted.',
                ]);
                log_message('debug', 'Delete success alert set in session');
            } else {
                throw new \Exception('Delete failed');
            }
            
        } catch (\Exception $e) {
            log_message('error', 'Error deleting faculty: ' . $e->getMessage());
            
            session()->setFlashdata('swal', [
                'icon'  => 'error',
                'title' => 'Error!',
                'text'  => 'Failed to delete faculty. Please try again.',
            ]);
        }
        
        return redirect()->to('faculty-list');
    }
}