<?
namespace App\Filters;

use App\Models\PrivilegeModel;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Filters\FilterInterface;

class PrivilegeFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $session = session();

        if (!$session->get('is_logged_in') || !$session->get('role_id')) {
            return redirect()->to('/login');
        }

        // Pastikan argument privilege disediakan, misal: ['create-user', 'can_create']
        if (!$arguments || count($arguments) < 2) {
            return redirect()->to('/unauthorized');
        }

        [$submenuSlug, $privilegeType] = $arguments;

        $roleId = $session->get('role_id');
        $privilegeModel = new PrivilegeModel();

        // Ambil ID submenu berdasarkan slug
        $submenu = db_connect()
            ->table('submenu')
            ->select('id')
            ->where('slug', $submenuSlug)
            ->where('status', 1)
            ->get()
            ->getRow();

        if (!$submenu) {
            return redirect()->to('/unauthorized');
        }

        $hasPrivilege = $privilegeModel
            ->where('role_id', $roleId)
            ->where('submenu_id', $submenu->id)
            ->where("can_$privilegeType", 1)
            ->first();

        if (!$hasPrivilege) {
            return redirect()->to('/unauthorized');
        }

        // Akses diperbolehkan
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
    }
}
