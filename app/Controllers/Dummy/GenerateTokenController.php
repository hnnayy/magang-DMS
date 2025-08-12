<?php

namespace App\Controllers\Dummy;

use App\Controllers\BaseController;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class GenerateTokenController extends BaseController
{
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

        // Validate if username exists in user (DMS) and is active (status = 1)
        $user = $this->userModel->where('username', $username)->where('status', 1)->first();
        if (!$user) {
            return redirect()->back()->with('error', 'Username not found in DMS system or account is inactive.');
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

    public function parseToken()
    {
        $token = $this->request->getGet('token');

        if (!$token) {
            log_message('warning', 'Token not provided in URL.');
            return view('dummy_wc/dashboard_token', ['error' => 'Token not found. Please login again.']);
        }

        log_message('info', '===[ TOKEN RECEIVED ]===');
        log_message('info', $token);

        $secret = getenv('jwt.secret');
        if (!$secret) {
            log_message('critical', 'JWT secret key not found in .env');
            return view('dummy_wc/dashboard_token', ['error' => 'System configuration error occurred. Please contact administrator.']);
        }

        try {
            $decoded = JWT::decode($token, new Key($secret, 'HS256'));

            log_message('info', "===[ TOKEN DECODED PAYLOAD ]===\n" . print_r((array)$decoded, true));

            // Fetch user data
            $user = $this->userModel
                ->select('user.*, unit.name as unit_name, unit.parent_id, unit_parent.name as parent_name')
                ->join('unit', 'unit.id = user.unit_id', 'left')
                ->join('unit_parent', 'unit_parent.id = unit.parent_id', 'left')
                ->where('user.username', $decoded->sub)
                ->where('user.status', 1) // Ensure user is active
                ->first();

            if (!$user) {
                log_message('error', 'User not found or inactive for username: ' . $decoded->sub);
                return view('dummy_wc/dashboard_token', ['error' => 'User not found or inactive. Please check your username.']);
            }

            log_message('info', "===[ USER FOUND ]===\n" . print_r($user, true));

            // Fetch active user role with access_level
            $userRole = $this->userRoleModel
                ->select('user_role.*, role.access_level, role.name as role_name')
                ->join('role', 'role.id = user_role.role_id', 'left')
                ->where('user_role.user_id', $user['id'])
                ->where('user_role.status', 1)
                ->first();

            if (!$userRole) {
                log_message('error', 'User does not have an active role. ID: ' . $user['id']);
                return view('dummy_wc/dashboard_token', ['error' => 'User does not have an active role. Please contact administrator.']);
            }

            log_message('info', "===[ ACTIVE ROLE FOUND ]===\n" . print_r($userRole, true));

            // Fetch privileges and submenu names
            $privileges = $this->privilegeModel
                ->select('submenu.id as submenu_id, submenu.name as submenu_name, can_create, can_update, can_delete, can_approve')
                ->join('submenu', 'submenu.id = privilege.submenu_id')
                ->where('privilege.role_id', $userRole['role_id'])
                ->findAll();

            // Build privileges and accessible routes structure
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

            log_message('info', "===[ ACCESS LEVEL ]===");
            log_message('info', "Role: {$userRole['role_name']} | Access Level: {$userRole['access_level']}");

            log_message('info', "===[ ACCESSIBLE ROUTES ]===");
            foreach ($accessibleRoutes as $route) {
                log_message('info', "- $route");
            }

            // Store session with access_level
            session()->set([
                'user_id'          => $user['id'],
                'username'         => $user['username'],
                'fullname'         => $user['fullname'],
                'role_id'          => $userRole['role_id'],
                'role_name'        => $userRole['role_name'] ?? '-',
                'access_level'     => $userRole['access_level'] ?? 0,
                'unit_id'          => $user['unit_id'],
                'unit_name'        => $user['unit_name'] ?? '-',
                'unit_parent_id'   => $user['parent_id'] ?? null,
                'parent_name'      => $user['parent_name'] ?? '-',
                'is_logged_in'     => true,
                'jwt_token'        => $token,
                'privileges'       => $privilegeArray,
                'accessible_routes' => $accessibleRoutes,
            ]);

            log_message('info', "===[ SESSION STORED FOR USER ]=== {$user['username']} | Access Level: {$userRole['access_level']}");

            // If token is valid, redirect directly to dashboard
            return redirect()->to('/dashboard');

        } catch (\Firebase\JWT\ExpiredException $e) {
            log_message('error', 'Token expired: ' . $e->getMessage());
            return view('dummy_wc/dashboard_token', ['error' => 'Login session has expired. Please login again.']);

        } catch (\Firebase\JWT\SignatureInvalidException $e) {
            log_message('error', 'Invalid token signature: ' . $e->getMessage());
            return view('dummy_wc/dashboard_token', ['error' => 'Invalid token. Please login again.']);

        } catch (\Firebase\JWT\BeforeValidException $e) {
            log_message('error', 'Token not yet valid: ' . $e->getMessage());
            return view('dummy_wc/dashboard_token', ['error' => 'Token is not yet valid. Please try again.']);

        } catch (\Exception $e) {
            log_message('error', 'Token parsing error: ' . $e->getMessage());
            return view('dummy_wc/dashboard_token', ['error' => 'An error occurred while processing login. Please try again or contact administrator.']);
        }
    }
}