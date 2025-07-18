<?php

namespace App\Controllers\Dummy;

use App\Controllers\BaseController;
use App\Models\UserModel;
use App\Models\UserRoleModel;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class TokenDummy extends BaseController
{
    protected $userModel;
    protected $userRoleModel;

    public function __construct()
    {
        $this->userModel = new UserModel();
        $this->userRoleModel = new UserRoleModel();
    }

    public function index()
    {
        $users = $this->userModel->findAll(); // Ambil semua user
        return view('dummy_wc/index', ['users' => $users]);
    }

    public function login()
    {
        $username = $this->request->getPost('username');

        $user = $this->userModel->where('username', $username)->first();

        if (!$user) {
            return redirect()->back()->with('error', 'User tidak ditemukan.');
        }

        $userRole = $this->userRoleModel
            ->where('user_id', $user['id'])
            ->where('status', 1)
            ->first();

        if (!$userRole) {
            return redirect()->back()->with('error', 'User tidak memiliki role aktif.');
        }

        $payload = [
            'iss' => 'dummy-login',
            'sub' => $username,
            'iat' => time(),
            'exp' => time() + 300,
            'role_id' => $userRole['role_id']
        ];

        $secret = getenv('jwt.secret');

        if (!$secret) {
            throw new \RuntimeException('JWT secret not configured in .env (jwt.secret)');
        }

        $token = JWT::encode($payload, $secret, 'HS256');

        // Redirect ke parseToken dengan token di URL
        return redirect()->to('/parse-token?token=' . $token);
    }

    public function parseToken()
{
    $token = $this->request->getGet('token');
    $redirect = $this->request->getGet('redirect'); // opsional: ?redirect=dashboard

    if (!$token) {
        return $this->response->setStatusCode(400)
            ->setJSON(['error' => 'Token tidak diberikan']);
    }

    $secret = getenv('jwt.secret');
    if (!$secret) {
        throw new \RuntimeException('JWT secret key tidak ditemukan di .env');
    }

    try {
        $decoded = JWT::decode($token, new Key($secret, 'HS256'));

        $user = $this->userModel
            ->select('user.*, unit.name as unit_name, unit.parent_id, unit_parent.name as parent_name')
            ->join('unit', 'unit.id = user.unit_id', 'left')
            ->join('unit_parent', 'unit_parent.id = unit.parent_id', 'left')
            ->where('user.username', $decoded->sub)
            ->first();

        if (!$user) {
            return $this->response->setStatusCode(404)->setJSON([
                'status' => 'error',
                'message' => 'User tidak ditemukan'
            ]);
        }

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

        // Simpan sesi
        session()->set([
            'user_id'        => $user['id'],
            'username'       => $user['username'],
            'fullname'       => $user['fullname'],
            'role_id'        => $userRole['role_id'],
            'unit_id'        => $user['unit_id'],
            'unit_name'      => $user['unit_name'] ?? '-',
            'unit_parent_id' => $user['parent_id'] ?? null,
            'parent_name'    => $user['parent_name'] ?? '-',
            'is_logged_in'   => true,
            'jwt_token'      => $token
        ]);

        // Redirect jika diminta
        if ($redirect === 'dashboard') {
            return redirect()->to('/dashboard');
        }

        // Hitung waktu kedaluwarsa token
        $expiryTime = date('n/j/Y, g:i:s A', $decoded->exp);
        $currentTime = time();
        $timeLeft = $decoded->exp - $currentTime;
        $timeLeftFormatted = $timeLeft > 0 ? $timeLeft . 's' : '0s';

        // Siapkan data untuk view
        $tokenData = [
            'username' => $user['username'],
            'fullname' => $user['fullname'],
            'role_id' => $userRole['role_id'],
            'token' => $token,
            'expiry_time' => $expiryTime,
            'time_left' => $timeLeftFormatted,
            'is_valid' => $timeLeft > 0,
            'decoded_payload' => $decoded
        ];

        return view('dummy_wc/dashboard_token', $tokenData);

    } catch (\Exception $e) {
        return $this->response->setStatusCode(401)->setJSON([
            'status' => 'error',
            'message' => 'Token tidak valid',
            'error'   => $e->getMessage()
        ]);
    }
}

    // OPTIONAL: debug method kalau kamu butuh generate token secara manual
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



}