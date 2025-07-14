<?php

namespace App\Filters;

use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Filters\FilterInterface;

class AuthFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $session = session();

        if (!$session->get('is_logged_in')) {
            return redirect()->to('/login');
        }

        if (!$session->get('role_id')) {
            return redirect()->to('/unauthorized');
        }

        // Bisa tambahkan validasi submenu jika mau validasi spesifik
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // Kosong
    }
}
