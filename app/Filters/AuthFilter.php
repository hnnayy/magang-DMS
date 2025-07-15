<?php

namespace App\Filters;

use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Filters\FilterInterface;
use App\Models\PrivilegeModel;

class AuthFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $session = session();
        $role_id = $session->get('role_id');
        $currentSubmenu = $arguments[0] ?? ''; // Nama submenu dari route filter

        $privilegeModel = new PrivilegeModel();
        $access = $privilegeModel->getAccess($role_id, $currentSubmenu);

        if (!$access) {
            return redirect()->to('/unauthorized');
        }

        // Simpan ke sesi agar bisa dipakai di view
        session()->set('privilege', $access);
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // Nothing here
    }
}

