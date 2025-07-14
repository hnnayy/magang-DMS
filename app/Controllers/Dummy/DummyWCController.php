<?php

namespace App\Controllers\Dummy;

use App\Models\UserModel;
use App\Models\UserRoleModel;
use Firebase\JWT\JWT;
use CodeIgniter\Controller;

class DummyWCController extends Controller
{
    public function index()
    {
        $userModel = new UserModel();
        $users = $userModel->where('status', 1)->findAll();

        return view('dummy_wc/index', ['users' => $users]);
    }

    public function redirectToDMS()
    {
        $username = $this->request->getPost('username');

        $userModel = new UserModel();
        $user = $userModel->where('username', $username)->first();

        if (!$user) {
            return redirect()->back()->with('error', 'User tidak ditemukan');
        }

        $userRoleModel = new UserRoleModel();
        $userRole = $userRoleModel
            ->where('user_id', $user['id'])
            ->where('status', 1)
            ->first();

        if (!$userRole) {
            return redirect()->back()->with('error', 'Role user tidak ditemukan');
        }

        $payload = [
            'sub'      => $user['username'],
            'fullname' => $user['fullname'],
            'role_id'  => $userRole['role_id'],
            'iat'      => time(),
            'exp'      => time() + 600
        ];

        $secret = getenv('jwt.secret') ?: 'defaultsecret';
        $token = JWT::encode($payload, $secret, 'HS256');

        // return redirect()->to(uri: 'http://localhost:8080/generatetoken?token=' . $token); // buat liat tokennya
        return redirect()->to('http://localhost:8080/parse-token?token=' . $token);

    }
}
