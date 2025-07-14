<?php

namespace App\Controllers\Dummy;

use App\Models\UserModel;
use App\Models\UserRoleModel;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use CodeIgniter\Controller;

class TokenDummy extends Controller
{
    protected $userModel;
    protected $userRoleModel;

    public function __construct()
    {
        $this->userModel = new UserModel();
        $this->userRoleModel = new UserRoleModel();
    }

    // http://localhost:8080/generatetoken
    public function generateAllTokens()
    {
        $users = $this->userModel
            ->select('user.id, user.username, user.fullname, user_role.role_id')
            ->join('user_role', 'user_role.user_id = user.id')
            ->where('user.status', 1)
            ->where('user_role.status', 1)
            ->findAll();

        $secret = getenv('jwt.secret') ?: 'defaultsecret';
        $results = [];

        foreach ($users as $user) {
            $payload = [
                'sub'      => $user['username'],
                'fullname' => $user['fullname'],
                'role_id'  => $user['role_id'],
                'iat'      => time(),
                'exp'      => time() + 300,
                'jti'      => uniqid()
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
    $token = $this->request->getGet('token'); // ⛳ Ambil token dari URL
    if (!$token) {
        return $this->response->setStatusCode(400)
            ->setJSON(['error' => 'Token tidak diberikan']);
    }

    $secret = getenv('jwt.secret') ?: 'defaultsecret';

    try {
        $decoded = JWT::decode($token, new Key($secret, 'HS256'));

        // Cari user dari database berdasarkan username di token
        $user = $this->userModel->where('username', $decoded->sub)->first();
        if (!$user) {
            return $this->response->setStatusCode(404)->setJSON([
                'status' => 'error',
                'message' => 'User tidak ditemukan'
            ]);
        }

        // Ambil role dari tabel user_role
        $userRole = $this->userRoleModel
            ->where('user_id', $user['id'])
            ->where('status', 1)
            ->first();

        if (!$userRole) {
            return $this->response->setStatusCode(403)->setJSON([
                'status' => 'error',
                'message' => 'User tidak memiliki role aktif.'
            ]);
        }

        // Simpan informasi ke session
        session()->set([
            'user_id'     => $user['id'],
            'username'    => $user['username'],
            'fullname'    => $user['fullname'],
            'role_id'     => $userRole['role_id'],
            'is_logged_in'=> true
        ]);

        return redirect()->to('/dashboard');
    } catch (\Exception $e) {
        return $this->response->setStatusCode(401)->setJSON([
            'status' => 'error',
            'message' => 'Token tidak valid',
            'error'   => $e->getMessage()
        ]);
    }
}

}
