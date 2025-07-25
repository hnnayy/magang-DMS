<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use CodeIgniter\HTTP\CLIRequest;
use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

// Model
use App\Models\ClauseModel;
use App\Models\DocumentApprovalModel;
use App\Models\DocumentCodeModel;
use App\Models\DocumentModel;
use App\Models\DocumentNumberModel;
use App\Models\DocumentRevisionModel;
use App\Models\DocumentTypeModel;
use App\Models\FakultasModel;
use App\Models\MenuModel;
use App\Models\NotificationModel;
use App\Models\PrivilegeModel;
use App\Models\RoleModel;
use App\Models\StandardModel;
use App\Models\SubmenuModel;
use App\Models\UnitModel;
use App\Models\UnitParentModel;
use App\Models\UserModel;
use App\Models\UserRoleModel;
use App\Models\UserWcModel;

abstract class BaseController extends Controller
{
    // Semua instance model
    protected $clauseModel;
    protected $documentApprovalModel;
    protected $documentCodeModel;
    protected $documentModel;
    protected $documentNumberModel;
    protected $documentRevisionModel;
    protected $documentTypeModel;
    protected $fakultasModel;
    protected $menuModel;
    protected $notificationModel;
    protected $privilegeModel;
    protected $roleModel;
    protected $standardModel;
    protected $submenuModel;
    protected $unitModel;
    protected $unitParentModel;
    protected $userModel;
    protected $userRoleModel;
    protected $userWcModel;

    protected $request;
    protected $helpers = ['sidebar', 'slug', 'privilege'];

    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        parent::initController($request, $response, $logger);

        // Inisialisasi semua model
        $this->clauseModel            = new ClauseModel();
        $this->documentApprovalModel = new DocumentApprovalModel();
        $this->documentCodeModel     = new DocumentCodeModel();
        $this->documentModel         = new DocumentModel();
        $this->documentNumberModel   = new DocumentNumberModel();
        $this->documentRevisionModel = new DocumentRevisionModel();
        $this->documentTypeModel     = new DocumentTypeModel();
        $this->fakultasModel         = new FakultasModel();
        $this->menuModel             = new MenuModel();
        $this->notificationModel     = new NotificationModel();
        $this->privilegeModel        = new PrivilegeModel();
        $this->roleModel             = new RoleModel();
        $this->standardModel         = new StandardModel();
        $this->submenuModel          = new SubmenuModel();
        $this->unitModel             = new UnitModel();
        $this->unitParentModel       = new UnitParentModel();
        $this->userModel             = new UserModel();
        $this->userRoleModel         = new UserRoleModel();
        $this->userWcModel           = new UserWcModel();

        // Set session privileges jika belum tersedia
        if (session()->get('role_id') && !session()->has('privileges')) {
            $roleId = session()->get('role_id');

            $rawPrivileges = $this->privilegeModel
                ->select('privilege.*, submenu.name as submenu_name')
                ->join('submenu', 'submenu.id = privilege.submenu_id')
                ->where('role_id', $roleId)
                ->findAll();

            $privileges = [];

            foreach ($rawPrivileges as $p) {
                $slug = slugify($p['submenu_name']);
                $privileges[$slug] = [
                    'can_create'  => (bool) $p['can_create'],
                    'can_update'  => (bool) $p['can_update'],
                    'can_delete'  => (bool) $p['can_delete'],
                    'can_approve' => (bool) $p['can_approve'],
                ];
            }

            session()->set('privileges', $privileges);
        }
    }

    /**
     * Cek apakah role memiliki akses pada submenu tertentu.
     */
    protected function hasAccess($submenu_id, $action)
    {
        $role_id = session()->get('role_id');
        if (!$role_id) return false;

        $query = $this->privilegeModel
            ->where('role_id', $role_id)
            ->where('submenu_id', $submenu_id)
            ->get()
            ->getRow();

        return $query ? (bool) $query->$action : false;
    }

    /**
     * Paksa akses tertentu, jika tidak ada akses lempar error.
     */
    protected function requireAccess($submenu_id, $action)
    {
        if (!$this->hasAccess($submenu_id, $action)) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound("Akses ditolak untuk aksi: $action");
        }
    }
}
