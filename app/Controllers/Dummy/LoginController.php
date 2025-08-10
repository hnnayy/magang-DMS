<?php

namespace App\Controllers\Dummy;

use App\Controllers\BaseController;
use App\Models\UserModel;
use App\Models\UserRoleModel;
use App\Models\PrivilegeModel;
use App\Models\UserWcModel;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class LoginController extends BaseController
{
    protected $userModel;
    protected $userRoleModel;
    protected $privilegeModel;
    protected $userWcModel;

    public function __construct()
    {
        $this->userModel = new UserModel();
        $this->userRoleModel = new UserRoleModel();
        $this->privilegeModel = new PrivilegeModel();
        $this->userWcModel = new UserWcModel();
    }

    public function index()
    {
        // Cek apakah user sudah login
        if (session()->get('is_logged_in')) {
            return redirect()->to('/dashboard');
        }

        $users = $this->userModel->findAll();
        return view('dummy_wc/index', ['users' => $users]);
    }

    public function login()
    {
        $username = $this->request->getPost('username');

        // Validate username format: only lowercase alphanumeric, no spaces or symbols
        if (!preg_match('/^[a-z0-9]+$/', $username)) {
            return redirect()->back()->with('error', 'Username must contain only lowercase letters and numbers, no spaces or symbols.');
        }

        // Validate if username exists in userWC
        $userWc = $this->userWcModel->where('username', $username)->first();
        if (!$userWc) {
            return redirect()->back()->with('error', 'Username not found in WC system.');
        }

        // Validate if username exists in user (DMS)
        $user = $this->userModel->where('username', $username)->first();
        if (!$user) {
            return redirect()->back()->with('error', 'Username not found in DMS system.');
        }

        // Prepare token
        $payload = [
            'iss' => 'dummy-login',
            'sub' => $username,
            'iat' => time(),
            'exp' => time() + 300
        ];

        $secret = getenv('jwt.secret');
        if (!$secret) {
            throw new \RuntimeException('JWT secret not found in .env');
        }

        $token = JWT::encode($payload, $secret, 'HS256');

        return redirect()->to('/parse-token?token=' . $token);
    }

    public function logout()
    {
        session()->destroy();
        return redirect()->to('/wc-dummy')->with('success', 'You have been logged out successfully.');
    }
}