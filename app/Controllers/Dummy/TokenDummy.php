<?php

namespace App\Controllers\Dummy;

use App\Models\UserModel;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use CodeIgniter\Controller;

class TokenDummy extends Controller
{
    protected $userModel;

    public function __construct()
    {
        $this->userModel = new UserModel();
    }

    // http://localhost:8080/generatetoken
    public function generateAllTokens()
    {
        $users = $this->userModel
            ->distinct()
            ->select('username, fullname')
            ->where('status', 1)
            ->findAll();

        $secret = getenv('jwt.secret') ?: 'defaultsecret';

        $results = [];

        foreach ($users as $user) {
            $payload = [
                'sub'      => $user['username'],
                'fullname' => $user['fullname'],
                'iat'      => time(),
                'exp'      => time() + 300,
                'jti'      => uniqid() // optional: token ID biar unik
            ];


            $token = JWT::encode($payload, $secret, 'HS256');

            $results[] = [
                'username' => $user['username'],
                'fullname' => $user['fullname'],
                'token'    => $token,
                'payload'  => $payload,
            ];
        }

        return $this->response->setJSON([
            'generated_at' => date('Y-m-d H:i:s'),
            'count'        => count($results),
            'data'         => $results,
        ]);
    }

    // http://localhost:8080/parse-token?token=xxx
    public function parseToken()
    {
        $token = $this->request->getGet('token');
        if (!$token) {
            return $this->response->setStatusCode(400)
                ->setJSON(['error' => 'Token tidak diberikan']);
        }

        $secret = getenv('jwt.secret') ?: 'defaultsecret';

        try {
            $decoded = JWT::decode($token, new Key($secret, 'HS256'));

            session()->set([
                'username'     => $decoded->sub,
                'fullname'     => $decoded->fullname ?? '',
                'is_logged_in' => true,
            ]);

            return $this->response->setJSON([
                'status'  => 'success',
                'message' => 'Token valid',
                'data'    => [
                    'username'    => $decoded->sub,
                    'fullname'    => $decoded->fullname ?? '',
                    'issued_at'   => date('Y-m-d H:i:s', $decoded->iat),
                    'expires_at'  => date('Y-m-d H:i:s', $decoded->exp),
                ]
            ]);
        } catch (\Exception $e) {
            return $this->response->setStatusCode(401)->setJSON([
                'status'  => 'error',
                'message' => 'Token tidak valid',
                'error'   => $e->getMessage()
            ]);
        }
    }
}
