<?php

namespace App\Controllers\Dummy;

use App\Controllers\BaseController;
use App\Models\UserModel;
use App\Models\UserRoleModel;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class GenerateTokenController extends BaseController
{
    protected $userModel;
    protected $userRoleModel;

    public function __construct()
    {
        $this->userModel = new UserModel();
        $this->userRoleModel = new UserRoleModel();
    }

    public function generateAllTokens()
    {
        $users = $this->userModel->findAll();
        $secret = getenv('jwt.secret') ?: 'defaultsecret';

        $tokens = [];

        foreach ($users as $user) {
            $userRole = $this->userRoleModel
                ->where('user_id', $user['id'])
                ->where('status', 1)
                ->first();

            if (!$userRole) {
                continue;
            }

            $payload = [
                'iss' => 'dummy-login',
                'sub' => $user['username'],
                'iat' => time(),
                'exp' => time() + 300,
                'role_id' => $userRole['role_id']
            ];

            $token = JWT::encode($payload, $secret, 'HS256');
            $tokens[] = [
                'username' => $user['username'],
                'fullname' => $user['fullname'],
                'role_id'  => $userRole['role_id'],
                'token'    => $token
            ];
        }

        return $this->response->setJSON($tokens);
    }

    public function decodeTokenApi()
    {
        $authHeader = $this->request->getHeaderLine('Authorization');

        if (!$authHeader || !preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
            return $this->response
                ->setStatusCode(400)
                ->setContentType('application/json')
                ->setJSON([
                    'status' => 'error',
                    'message' => 'Authorization header tidak ditemukan atau format salah.'
                ]);
        }

        $token = $matches[1];
        $secretKey = getenv('jwt.secret') ?: 'defaultsecret';

        try {
            $decoded = JWT::decode($token, new Key($secretKey, 'HS256'));
            return $this->response
                ->setStatusCode(200)
                ->setContentType('application/json')
                ->setJSON([
                    'status' => 'success',
                    'data' => $decoded
                ]);
        } catch (\Exception $e) {
            return $this->response
                ->setStatusCode(401)
                ->setContentType('application/json')
                ->setJSON([
                    'status' => 'error',
                    'message' => 'Token tidak valid',
                    'error' => $e->getMessage()
                ]);
        }
    }
}  