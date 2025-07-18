<?php

namespace App\Filters;

use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Filters\FilterInterface;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class AuthFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $token = $request->getCookie('jwt_token'); // atau ambil dari header

        if (!$token) {
            return redirect()->to('/wc-login');
        }

        try {
            $key = getenv('JWT_SECRET_KEY');
            $decoded = JWT::decode($token, new Key($key, 'HS256'));

            // Simpan ke session kalau belum
            if (!session()->has('user_data')) {
                session()->set('user_data', [
                    'user_id' => $decoded->user_id,
                    'role_id' => $decoded->role_id,
                    'username' => $decoded->username,
                ]);
            }

        } catch (\Exception $e) {
            return redirect()->to('/wc-login')->with('error', 'Sesi tidak valid.');
        }

        return null;
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // Kosongkan
    }
}
