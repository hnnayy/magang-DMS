<?php

namespace App\Filters;

use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Filters\FilterInterface;
use App\Models\PrivilegeModel;
use App\Models\SubmenuModel;

class PrivilegeFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $session = session();

        if (!$session->get('role_id')) {
            return redirect()->to('/wc-dummy')->with('error', 'Anda harus login terlebih dahulu.');
        }

        $roleId = $session->get('role_id');
        $uri = service('uri');
        $currentRoute = $uri->getSegment(1);

        $privilegeModel = new PrivilegeModel();
        $submenuModel = new SubmenuModel();

        // Ambil semua submenu yang punya privilege untuk role ini (tanpa cek create/update/delete/approve)
        $accessibleSubmenus = $privilegeModel->select('submenu_id')
            ->where('role_id', $roleId)
            ->findAll();

        $submenuIds = array_column($accessibleSubmenus, 'submenu_id');

        if (empty($submenuIds)) {
            return redirect()->to('/wc-dummy')->with('error', 'Anda tidak memiliki akses ke halaman apa pun.');
        }

        $submenus = $submenuModel->whereIn('id', $submenuIds)->findAll();

        helper('slug');
        $isAuthorized = false;
        foreach ($submenus as $submenu) {
            if (slugify($submenu['name']) === $currentRoute) {
                $isAuthorized = true;
                break;
            }
        }

        if (!$isAuthorized) {
            return redirect()->to('/wc-dummy')->with('error', 'Anda tidak memiliki akses ke halaman ini.');
        }

        return;
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // Tidak digunakan
    }
}
