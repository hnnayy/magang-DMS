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
        // Check if user is already logged in
        if (session()->get('is_logged_in')) {
            return redirect()->to('/dashboard');
        }

        $users = $this->userModel->findAll();
        return view('dummy_wc/index', ['users' => $users]);
    }

    
}