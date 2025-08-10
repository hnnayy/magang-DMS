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
    public function index()
    {
        // Cek apakah user sudah login
        if (session()->get('is_logged_in')) {
            return redirect()->to('/dashboard');
        }

        $users = $this->userModel->findAll();
        return view('dummy_wc/index', ['users' => $users]);
    }

    
}