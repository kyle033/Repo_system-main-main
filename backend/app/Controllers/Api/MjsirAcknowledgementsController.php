<?php

namespace App\Controllers\Api;

use App\Models\MjsirAcknowledgementModel;
use CodeIgniter\RESTful\ResourceController;

class MjsirAcknowledgementsController extends ResourceController
{
    protected $modelName = 'App\Models\MjsirAcknowledgementModel';
    protected $format    = 'json';

    public function __construct()
    {
        $origin = getenv('FRONTEND_ORIGIN') ?: 'http://localhost:5173';
        header("Access-Control-Allow-Origin: {$origin}");
        header('Access-Control-Allow-Credentials: true');
        header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, Authorization');

        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            http_response_code(200);
            exit();
        }
    }

    /**
     * GET /api/mjsir-acknowledgements
     */
    public function index()
    {
        try {
            $rows = $this->model
                ->orderBy('date_distributed', 'DESC')
                ->orderBy('id', 'DESC')
                ->findAll();

            return $this->respond([
                'status' => 'success',
                'data'   => $rows,
            ]);
        } catch (\Exception $e) {
            return $this->fail([
                'status'  => 'error',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * POST /api/mjsir-acknowledgements
     */
    public function create()
    {
        try {
            $data = $this->request->getJSON(true);

            if (!$this->model->insert($data)) {
                return $this->fail([
                    'status'  => 'error',
                    'message' => 'Failed to create MJSIR acknowledgement record',
                    'errors'  => $this->model->errors(),
                ], 400);
            }

            $id = $this->model->getInsertID();
            $record = $this->model->find($id);

            return $this->respondCreated([
                'status'  => 'success',
                'message' => 'MJSIR acknowledgement record created successfully',
                'data'    => $record,
            ]);
        } catch (\Exception $e) {
            return $this->fail([
                'status'  => 'error',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * PUT /api/mjsir-acknowledgements/:id
     */
    public function update($id = null)
    {
        try {
            $data = $this->request->getJSON(true);

            if (!$id || !$this->model->find($id)) {
                return $this->failNotFound('MJSIR acknowledgement record not found');
            }

            if (!$this->model->update($id, $data)) {
                return $this->fail([
                    'status'  => 'error',
                    'message' => 'Failed to update MJSIR acknowledgement record',
                    'errors'  => $this->model->errors(),
                ], 400);
            }

            $record = $this->model->find($id);

            return $this->respond([
                'status'  => 'success',
                'message' => 'MJSIR acknowledgement record updated successfully',
                'data'    => $record,
            ]);
        } catch (\Exception $e) {
            return $this->fail([
                'status'  => 'error',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * DELETE /api/mjsir-acknowledgements/:id
     */
    public function delete($id = null)
    {
        try {
            if (!$id || !$this->model->find($id)) {
                return $this->failNotFound('MJSIR acknowledgement record not found');
            }

            if (!$this->model->delete($id)) {
                return $this->fail([
                    'status'  => 'error',
                    'message' => 'Failed to delete MJSIR acknowledgement record',
                ], 400);
            }

            return $this->respondDeleted([
                'status'  => 'success',
                'message' => 'MJSIR acknowledgement record deleted successfully',
            ]);
        } catch (\Exception $e) {
            return $this->fail([
                'status'  => 'error',
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
