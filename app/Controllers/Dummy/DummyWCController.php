<?php

namespace App\Controllers\Dummy;

use App\Models\UserModel;
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

        $payload = [
            'sub'      => $user['username'],
            'fullname' => $user['fullname'],
            'iat'      => time(),
            'exp'      => time() + 600 // expired 10 menit
        ];

        $secret = getenv('jwt.secret');
        $token = JWT::encode($payload, $secret, 'HS256');

        // Redirect ke sistem DMS dengan token
        return redirect()->to('http://localhost:8080/generatetoken?token=' . $token);
    }
}
