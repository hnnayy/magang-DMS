<?php
namespace App\Controllers\MasterData;

use App\Models\DocumentTypeModel;
use CodeIgniter\Controller;

class DocumentTypeController extends Controller
{
    protected $documentTypeModel;
    
    public function __construct()
    {
        $this->documentTypeModel = new DocumentTypeModel();
    }
    
    public function index()
    {
        $kategori = $this->documentTypeModel->where('status', 1)->findAll();
        $data['kategori_dokumen'] = array_map(function ($item) {
            return [
                'id' => $item['id'],
                'nama' => $item['name'],
                'kode' => $item['kode'],
                'use_predefined_codes' => str_contains($item['description'] ?? '', '[predefined]'),
            ];
        }, $kategori);
        
        return view('DataMaster/document-type', $data);
    }
    
    public function add()
    {
        $nama = strtoupper(trim($this->request->getPost('nama')));
        $kode = strtoupper(trim($this->request->getPost('kode')));
        // Fix: checkbox akan mengirim value="1" jika dicentang, null jika tidak
        $use_predefined = $this->request->getPost('use_predefined') === '1';
        
        // Debug log
        log_message('debug', 'Add document type called with data: ' . json_encode([
            'nama' => $nama,
            'kode' => $kode,
            'use_predefined' => $use_predefined
        ]));
        
        if (empty($nama)) {
            session()->setFlashdata('error', 'Category name is required.');
            return redirect()->back();
        }
        
        // Check for existing name
        $existingName = $this->documentTypeModel
            ->where('UPPER(name)', $nama)
            ->where('status', 1)
            ->first();
            
        if ($existingName) {
            session()->setFlashdata('error', 'Category name already exists.');
            return redirect()->back();
        }
        
        // Check for existing kode only if kode is provided and not empty
        if (!empty($kode)) {
            $existingKode = $this->documentTypeModel
                ->where('UPPER(kode)', $kode)
                ->where('status', 1)
                ->first();
                
            if ($existingKode) {
                session()->setFlashdata('error', 'Category code already exists.');
                return redirect()->back();
            }
        }
        
        try {
            $description = $use_predefined ? '[predefined]' : null;
            
            $data = [
                'name' => $nama,
                'description' => $description,
                'status' => 1,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ];
            
            // Only add kode if it's not empty
            if (!empty($kode)) {
                $data['kode'] = $kode;
            }
            
            $result = $this->documentTypeModel->save($data);
            
            if ($result) {
                session()->setFlashdata('added_message', 'Successfully Added.');
                log_message('debug', 'Successfully added');
            } else {
                throw new \Exception('Insert failed');
            }
            
        } catch (\Exception $e) {
            log_message('error', 'Error adding document type: ' . $e->getMessage());
            session()->setFlashdata('error', 'Failed to add category. Please try again.');
        }
        
        return redirect()->back();
    }
    
    public function edit()
    {
        $id = $this->request->getPost('id');
        $nama = strtoupper(trim($this->request->getPost('nama')));
        $kode = strtoupper(trim($this->request->getPost('kode')));
        $use_predefined = $this->request->getPost('use_predefined') === '1'; // Fix: checkbox handling
        
        // Debug log
        log_message('debug', 'Edit document type called with data: ' . json_encode([
            'id' => $id,
            'nama' => $nama,
            'kode' => $kode,
            'use_predefined' => $use_predefined
        ]));
        
        if (empty($id) || empty($nama)) {
            session()->setFlashdata('error', 'Category ID and name are required.');
            return redirect()->back();
        }
        
        // Check for existing name excluding current record
        $existingName = $this->documentTypeModel
            ->where('UPPER(name)', $nama)
            ->where('id !=', $id)
            ->where('status', 1)
            ->first();
            
        if ($existingName) {
            session()->setFlashdata('error', 'Category name already exists.');
            return redirect()->back();
        }
        
        // Check for existing kode only if kode is provided and not empty
        if (!empty($kode)) {
            $existingKode = $this->documentTypeModel
                ->where('UPPER(kode)', $kode)
                ->where('id !=', $id)
                ->where('status', 1)
                ->first();
                
            if ($existingKode) {
                session()->setFlashdata('error', 'Category code already exists.');
                return redirect()->back();
            }
        }
        
        try {
            $description = $use_predefined ? '[predefined]' : null;
            
            $data = [
                'name' => $nama,
                'description' => $description,
                'updated_at' => date('Y-m-d H:i:s'),
            ];
            
            // Only update kode if it's provided
            if (!empty($kode)) {
                $data['kode'] = $kode;
            }
            
            $result = $this->documentTypeModel->update($id, $data);
            
            if ($result) {
                session()->setFlashdata('updated_message', 'Successfully Updated.');
                log_message('debug', 'Successfully Updated');
            } else {
                throw new \Exception('Update failed');
            }
            
        } catch (\Exception $e) {
            log_message('error', 'Error updating document type: ' . $e->getMessage());
            session()->setFlashdata('error', 'Failed to update category. Please try again.');
        }
        
        return redirect()->back();
    }
    
    public function delete()
    {
        $id = $this->request->getPost('id');
        
        // Debug log
        log_message('debug', 'Delete document type called with id: ' . $id);
        
        if (!$id) {
            session()->setFlashdata('error', 'ID is invalid.');
            return redirect()->back();
        }
        
        try {
            $result = $this->documentTypeModel->update($id, [
                'status' => 0,
                'updated_at' => date('Y-m-d H:i:s'),
            ]);
            
            if ($result) {
                session()->setFlashdata('deleted_message', 'Successfully Deleted.');
                log_message('debug', 'Document type deleted successfully');
            } else {
                throw new \Exception('Delete failed');
            }
            
        } catch (\Exception $e) {
            log_message('error', 'Error deleting document type: ' . $e->getMessage());
            session()->setFlashdata('error', 'Failed to delete category. Please try again.');
        }
        
        return redirect()->back();
    }
}

