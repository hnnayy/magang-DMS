<?php
namespace App\Controllers\MasterData;

use App\Controllers\BaseController;
use App\Models\ClauseModel;
use App\Models\StandardModel;

class ClauseController extends BaseController
{
    protected $clauseModel;
    protected $standardModel;

    public function __construct()
    {
        $this->clauseModel = new ClauseModel();
        $this->standardModel = new StandardModel();
    }

    public function index()
    {
        $clauses = $this->clauseModel->getWithStandard();
        $standards = $this->standardModel->where('status', 1)->orderBy('nama_standar', 'ASC')->findAll();
        
        $groupedClauses = [];
        foreach ($clauses as $clause) {
            $standardName = $clause['nama_standar'];
            if (!isset($groupedClauses[$standardName])) {
                $groupedClauses[$standardName] = [];
            }
            $groupedClauses[$standardName][] = $clause;
        }

        $data = [
            'title' => 'Clause Management',
            'clauses' => $clauses,
            'groupedClauses' => $groupedClauses,
            'standards' => $standards
        ];
        
        return view('DataMaster/clause', $data);
    }

    public function store()
    {
        // Set JSON response header
        $this->response->setContentType('application/json');

        // Validation rules
        $rules = [
            'standar_id' => [
                'label' => 'Standard',
                'rules' => 'required|numeric',
                'errors' => [
                    'required' => 'Standard is required.',
                    'numeric' => 'Invalid standard selected.'
                ]
            ],
            'nomor_klausul' => [
                'label' => 'Clause Number',
                'rules' => 'required|max_length[100]',
                'errors' => [
                    'required' => 'Clause number is required.',
                    'max_length' => 'Clause number cannot exceed 100 characters.'
                ]
            ],
            'nama_klausul' => [
                'label' => 'Clause Description',
                'rules' => 'required|max_length[500]',
                'errors' => [
                    'required' => 'Clause description is required.',
                    'max_length' => 'Clause description cannot exceed 500 characters.'
                ]
            ]
        ];

        if (!$this->validate($rules)) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Validation failed.',
                'errors' => $this->validator->getErrors()
            ]);
        }

        // Check if standard exists
        $standardExists = $this->standardModel->getActiveById($this->request->getPost('standar_id'));
        if (!$standardExists) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Selected standard does not exist or is inactive.'
            ]);
        }

        // Check for duplicate clause number within the same standard
        $existingClause = $this->clauseModel->where([
            'standar_id' => $this->request->getPost('standar_id'),
            'nomor_klausul' => trim($this->request->getPost('nomor_klausul'))
        ])->first();

        if ($existingClause) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Clause number already exists for this standard.'
            ]);
        }

        try {
            // Prepare data for insertion
            $data = [
                'standar_id' => $this->request->getPost('standar_id'),
                'nomor_klausul' => trim($this->request->getPost('nomor_klausul')),
                'nama_klausul' => trim($this->request->getPost('nama_klausul'))
            ];

            // Insert the clause
            $insertId = $this->clauseModel->insert($data);

            if ($insertId) {
                // Get the inserted clause with standard info
                $newClause = $this->clauseModel->select('clauses.*, standards.nama_standar')
                    ->join('standards', 'standards.id = clauses.standar_id', 'left')
                    ->where('clauses.id', $insertId)
                    ->first();

                return $this->response->setJSON([
                    'status' => 'success',
                    'message' => 'Succesfully added.',
                    'data' => $newClause
                ]);
            } else {
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => 'Failed to add clause. Please try again.'
                ]);
            }
        } catch (\Exception $e) {
            log_message('error', 'Error adding clause: ' . $e->getMessage());
            
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'An error occurred while adding the clause. Please try again.'
            ]);
        }
    }

    public function update()
    {
        // Set JSON response header
        $this->response->setContentType('application/json');
    
            $id = $this->request->getPost('id');
            
            if (!$id) {
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => 'Clause ID is required.'
                ]);
            }

            $rules = [
                'standar_id' => [
                    'label' => 'Standard',
                    'rules' => 'required|numeric',
                    'errors' => [
                        'required' => 'Standard is required.',
                        'numeric' => 'Invalid standard selected.'
                    ]
                ],
                'nomor_klausul' => [
                    'label' => 'Clause Number',
                    'rules' => 'required|max_length[100]',
                    'errors' => [
                        'required' => 'Clause number is required.',
                        'max_length' => 'Clause number cannot exceed 100 characters.'
                    ]
                ],
                'nama_klausul' => [
                    'label' => 'Clause Description',
                    'rules' => 'required|max_length[500]',
                    'errors' => [
                        'required' => 'Clause description is required.',
                        'max_length' => 'Clause description cannot exceed 500 characters.'
                    ]
                ]
            ];

            if (!$this->validate($rules)) {
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => 'Validation failed.',
                    'errors' => $this->validator->getErrors()
                ]);
            }

        // Check if standard exists
        $standardExists = $this->standardModel->getActiveById($this->request->getPost('standar_id'));
        if (!$standardExists) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Selected standard does not exist or is inactive.'
            ]);
        }

        // Check for duplicate clause number within the same standard (excluding current record)
        $duplicateClause = $this->clauseModel->where([
            'standar_id' => $this->request->getPost('standar_id'),
            'nomor_klausul' => trim($this->request->getPost('nomor_klausul')),
            'id !=' => $id
        ])->first();

        if ($duplicateClause) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Clause number already exists for this standard.'
            ]);
        }

        try {
            // Prepare data for update
            $data = [
                'standar_id' => $this->request->getPost('standar_id'),
                'nomor_klausul' => trim($this->request->getPost('nomor_klausul')),
                'nama_klausul' => trim($this->request->getPost('nama_klausul'))
            ];

            // Update the clause
            $updated = $this->clauseModel->update($id, $data);

            if ($updated) {
                // Get the updated clause with standard info
                $updatedClause = $this->clauseModel->select('clauses.*, standards.nama_standar')
                    ->join('standards', 'standards.id = clauses.standar_id', 'left')
                    ->where('clauses.id', $id)
                    ->first();

                return $this->response->setJSON([
                    'status' => 'success',
                    'message' => 'Successfully Updated.',
                    'data' => $updatedClause
                ]);
            } else {
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => 'Failed to update clause. Please try again.'
                ]);
            }
        } catch (\Exception $e) {
            log_message('error', 'Error updating clause: ' . $e->getMessage());
            
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'An error occurred while updating the clause. Please try again.'
            ]);
        }
    }

    public function delete()
    {
        $this->response->setContentType('application/json');
        
        $id = $this->request->getPost('id');
        
        if (!$id) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Clause ID is required.'
            ]);
        }

        $existingClause = $this->clauseModel->find($id);
        if (!$existingClause) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Clause not found.'
            ]);
        }

        try {
            $data = ['status' => 0]; // Tandai sebagai dihapus
            $this->clauseModel->update($id, $data);

            return $this->response->setJSON([
                'status' => 'success',
                'message' => 'Successfully deleted.',
                'token' => csrf_hash()
            ]);
        } catch (\Exception $e) {
            log_message('error', 'Error soft deleting clause: ' . $e->getMessage());
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'An error occurred while soft deleting the clause. Please try again.'
            ]);
        }
    }

    public function getStandards()
    {
        // Set JSON response header
        $this->response->setContentType('application/json');

        try {
            // Get only active standards (status = 1)
            $standards = $this->standardModel->where('status', 1)->orderBy('nama_standar', 'ASC')->findAll();

            return $this->response->setJSON([
                'status' => 'success',
                'data' => $standards
            ]);
        } catch (\Exception $e) {
            log_message('error', 'Error fetching standards: ' . $e->getMessage());
            
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Failed to fetch standards.'
            ]);
        }
    }
}