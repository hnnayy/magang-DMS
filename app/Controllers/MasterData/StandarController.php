<?php

namespace App\Controllers\MasterData;

use App\Controllers\BaseController;
use App\Models\StandardModel;

class StandarController extends BaseController
{
    protected $standardModel;

    public function __construct()
    {
        $this->standardModel = new StandardModel();
    }

    public function index()
    {
        $data = [
            'title' => 'Document Standards List',
            'standards' => $this->standardModel->where('status', 1)->orderBy('id', 'ASC')->findAll()
        ];
        
        return view('DataMaster/standar', $data);
    }

    public function store()
    {
        if (!$this->validate([
            'nama_standar' => 'required|max_length[255]'
        ])) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $this->validator->getErrors()
            ]);
        }

        $data = [
            'nama_standar' => $this->request->getPost('nama_standar'),
            'status' => 1
        ];

        if ($this->standardModel->insert($data)) {
            return $this->response->setJSON([
                'success' => true,
                'message' => 'Standard added successfully'
            ]);
        } else {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Failed to add standard'
            ]);
        }
    }

    public function edit()
    {
        $id = $this->request->getPost('id');
        $standard = $this->standardModel->find($id);
        
        if (!$standard || $standard['status'] == 0) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Standard not found or inactive'
            ]);
        }

        return $this->response->setJSON([
            'success' => true,
            'data' => $standard
        ]);
    }

    public function update()
    {
        $id = $this->request->getPost('id');
        
        $standard = $this->standardModel->find($id);
        if (!$standard || $standard['status'] == 0) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Standard not found or inactive'
            ]);
        }

        if (!$this->validate([
            'nama_standar' => 'required|max_length[255]'
        ])) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $this->validator->getErrors()
            ]);
        }

        $data = [
            'nama_standar' => $this->request->getPost('nama_standar')
        ];

        if ($this->standardModel->update($id, $data)) {
            return $this->response->setJSON([
                'success' => true,
                'message' => 'Standard updated successfully'
            ]);
        } else {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Failed to update standard'
            ]);
        }
    }

    public function delete()
    {
        $id = $this->request->getPost('id');
        
        $standard = $this->standardModel->find($id);
        if (!$standard || $standard['status'] == 0) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Standard not found or inactive'
            ]);
        }

        $data = [
            'status' => 0
        ];

        if ($this->standardModel->update($id, $data)) {
            return $this->response->setJSON([
                'success' => true,
                'message' => 'Standard deactivated successfully'
            ]);
        } else {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Failed to deactivate standard'
            ]);
        }
    }
}