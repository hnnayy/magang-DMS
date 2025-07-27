<?php

namespace App\Controllers\Dummy;

use App\Controllers\BaseController;
use App\Models\UserModel;
use App\Models\UserRoleModel;
use App\Models\PrivilegeModel; 
use App\Models\UserWcModel; // ⬅️ Tambahkan ini
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class TokenDummy extends BaseController
{
    protected $userModel;
    protected $userRoleModel;
    protected $privilegeModel; // ⬅️ Tambahkan ini


    public function __construct()
    {
        $this->userModel = new UserModel();
        $this->userRoleModel = new UserRoleModel();
        $this->privilegeModel = new PrivilegeModel(); // ✅ WAJIB
        $this->userWcModel = new UserWcModel(); // ⬅️ Tambahkan ini


    }

    public function index()
    {
        $users = $this->userModel->findAll(); // Ambil semua user
        return view('dummy_wc/index', ['users' => $users]);
    }

    public function login()
{
    $username = $this->request->getPost('username');

    // Validasi apakah ada di userWC
    $userWc = $this->userWcModel->where('username', $username)->first();
    if (!$userWc) {
        return redirect()->back()->with('error', 'Username tidak ditemukan di sistem WC.');
    }

    // Validasi apakah ada di user (DMS)
    $user = $this->userModel->where('username', $username)->first();
    if (!$user) {
        return redirect()->back()->with('error', 'Username tidak ditemukan di sistem DMS.');
    }

    // Validasi role
    $userRole = $this->userRoleModel
        ->where('user_id', $user['id'])
        ->where('status', 1)
        ->first();

    if (!$userRole) {
        return redirect()->back()->with('error', 'User tidak memiliki role aktif.');
    }

    // Siapkan token
    $payload = [
        'iss' => 'dummy-login',
        'sub' => $username,
        'iat' => time(),
        'exp' => time() + 300,
        'role_id' => $userRole['role_id']
    ];

    $secret = getenv('jwt.secret');
    if (!$secret) {
        throw new \RuntimeException('JWT secret tidak ditemukan di .env');
    }

    $token = JWT::encode($payload, $secret, 'HS256');

    return redirect()->to('/parse-token?token=' . $token);
}


public function parseToken()
{
    $token = $this->request->getGet('token');
    $redirect = $this->request->getGet('redirect'); // optional ?redirect=dashboard

    if (!$token) {
        log_message('warning', 'Token tidak diberikan di URL.');
        return $this->response->setStatusCode(400)
            ->setJSON(['error' => 'Token tidak diberikan']);
    }

    log_message('info', '===[ TOKEN DITERIMA ]===');
    log_message('info', $token);

    $secret = getenv('jwt.secret');
    if (!$secret) {
        log_message('critical', 'JWT secret key tidak ditemukan di .env');
        throw new \RuntimeException('JWT secret key tidak ditemukan di .env');
    }

    try {
        $decoded = JWT::decode($token, new Key($secret, 'HS256'));

        log_message('info', "===[ TOKEN DECODED PAYLOAD ]===\n" . print_r((array)$decoded, true));

        // Ambil data user
        $user = $this->userModel
            ->select('user.*, unit.name as unit_name, unit.parent_id, unit_parent.name as parent_name')
            ->join('unit', 'unit.id = user.unit_id', 'left')
            ->join('unit_parent', 'unit_parent.id = unit.parent_id', 'left')
            ->where('user.username', $decoded->sub)
            ->first();

        if (!$user) {
            log_message('error', 'User tidak ditemukan untuk username: ' . $decoded->sub);
            return $this->response->setStatusCode(404)->setJSON([
                'status' => 'error',
                'message' => 'User tidak ditemukan'
            ]);
        }

        log_message('info', "===[ USER DITEMUKAN ]===\n" . print_r($user, true));

        // Ambil role user aktif
        $userRole = $this->userRoleModel
            ->where('user_id', $user['id'])
            ->where('status', 1)
            ->first();

        if (!$userRole) {
            log_message('error', 'User tidak memiliki role aktif. ID: ' . $user['id']);
            return $this->response->setStatusCode(403)->setJSON([
                'status' => 'error',
                'message' => 'User tidak memiliki role aktif.'
            ]);
        }

        log_message('info', "===[ ROLE AKTIF DITEMUKAN ]===\n" . print_r($userRole, true));

        // Ambil privileges dan nama submenu
        $privileges = $this->privilegeModel
            ->select('submenu.id as submenu_id, submenu.name as submenu_name, can_create, can_update, can_delete, can_approve')
            ->join('submenu', 'submenu.id = privilege.submenu_id')
            ->where('privilege.role_id', $userRole['role_id'])
            ->findAll();

        // Bangun struktur privileges dan route akses
        $privilegeArray = [];
        $accessibleRoutes = [];

        foreach ($privileges as $priv) {
            $slug = slugify($priv['submenu_name']);

            $privilegeArray[$slug] = [
                'can_create'  => (int) $priv['can_create'],
                'can_update'  => (int) $priv['can_update'],
                'can_delete'  => (int) $priv['can_delete'],
                'can_approve' => (int) $priv['can_approve'],
            ];

            $accessibleRoutes[] = $slug;

            if ($priv['can_create']) {
                $accessibleRoutes[] = "$slug/store";
            }
            if ($priv['can_update']) {
                $accessibleRoutes[] = "$slug/edit";
                $accessibleRoutes[] = "$slug/update";
            }
            if ($priv['can_delete']) {
                $accessibleRoutes[] = "$slug/delete";
            }
            if ($priv['can_approve']) {
                $accessibleRoutes[] = "$slug/approve";
            }
        }

        $accessibleRoutes = array_values(array_unique($accessibleRoutes));

        log_message('info', "===[ PRIVILEGES PER SUBMENU ]===");
        foreach ($privilegeArray as $slug => $priv) {
            log_message('info', "- $slug:");
            log_message('info', "    can_create  : {$priv['can_create']}");
            log_message('info', "    can_update  : {$priv['can_update']}");
            log_message('info', "    can_delete  : {$priv['can_delete']}");
            log_message('info', "    can_approve : {$priv['can_approve']}");
        }

        log_message('info', "===[ ACCESSIBLE ROUTES ]===");
        foreach ($accessibleRoutes as $route) {
            log_message('info', "- $route");
        }

        // Simpan sesi
        session()->set([
            'user_id'          => $user['id'],
            'username'         => $user['username'],
            'fullname'         => $user['fullname'],
            'role_id'          => $userRole['role_id'],
            'unit_id'          => $user['unit_id'],
            'unit_name'        => $user['unit_name'] ?? '-',
            'unit_parent_id'   => $user['parent_id'] ?? null,
            'parent_name'      => $user['parent_name'] ?? '-',
            'is_logged_in'     => true,
            'jwt_token'        => $token,
            'privileges'       => $privilegeArray,
            'accessible_routes'=> $accessibleRoutes,
        ]);

        log_message('info', "===[ SESI DISIMPAN UNTUK USER ]=== {$user['username']}");

        if ($redirect === 'dashboard') {
            log_message('info', 'Redirecting to /dashboard ...');
            return redirect()->to('/dashboard');
        }

        // Info token
        $expiryTime = date('n/j/Y, g:i:s A', $decoded->exp);
        $currentTime = time();
        $timeLeft = $decoded->exp - $currentTime;
        $timeLeftFormatted = $timeLeft > 0 ? $timeLeft . 's' : '0s';

        log_message('info', "===[ TOKEN EXPIRES ]=== $expiryTime | Time left: $timeLeftFormatted");

        return view('dummy_wc/dashboard_token', [
            'username'        => $user['username'],
            'fullname'        => $user['fullname'],
            'role_id'         => $userRole['role_id'],
            'token'           => $token,
            'expiry_time'     => $expiryTime,
            'time_left'       => $timeLeftFormatted,
            'is_valid'        => $timeLeft > 0,
            'decoded_payload' => $decoded,
        ]);

    } catch (\Exception $e) {
        log_message('error', 'Token tidak valid: ' . $e->getMessage());

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