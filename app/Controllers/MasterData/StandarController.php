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
        // Validation rules
        $validationRules = [
            'nama_standar' => [
                'rules' => 'required|max_length[255]|is_unique[standards.nama_standar,status=1]',
                'errors' => [
                    'required' => 'Standard Name is required.',
                    'max_length' => 'Standard Name cannot exceed 255 characters.',
                    'is_unique' => 'The Standard Name already exists.'
                ]
            ],
            'description' => [
                'rules' => 'permit_empty|max_length[65535]',
                'errors' => [
                    'max_length' => 'Description cannot exceed 65,535 characters.'
                ]
            ]
        ];

        // Validate the input
        if (!$this->validate($validationRules)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $this->validator->getErrors()
            ]);
        }

        // Prepare data for insertion
        $data = [
            'nama_standar' => $this->request->getPost('nama_standar'),
            'description' => $this->request->getPost('description') ?: null,
            'status' => 1
        ];

        // Insert data into the database
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
            'data' => [
                'id' => $standard['id'],
                'nama_standar' => $standard['nama_standar'],
                'description' => $standard['description']
            ]
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

        // Validation rules
        $validationRules = [
            'nama_standar' => [
                'rules' => "required|max_length[255]|is_unique[standards.nama_standar,id,{$id},status=1]",
                'errors' => [
                    'required' => 'Standard Name is required.',
                    'max_length' => 'Standard Name cannot exceed 255 characters.',
                    'is_unique' => 'The Standard Name already exists.'
                ]
            ],
            'description' => [
                'rules' => 'permit_empty|max_length[65535]',
                'errors' => [
                    'max_length' => 'Description cannot exceed 65,535 characters.'
                ]
            ]
        ];

        // Validate the input
        if (!$this->validate($validationRules)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $this->validator->getErrors()
            ]);
        }

        // Prepare data for update
        $data = [
            'nama_standar' => $this->request->getPost('nama_standar'),
            'description' => $this->request->getPost('description') ?: null
        ];

        // Update data in the database
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

        // Soft delete by setting status to 0
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