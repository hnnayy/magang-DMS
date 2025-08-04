<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\NotificationModel;
use App\Models\NotificationRecipientsModel;

class Home extends BaseController
{
    protected $notificationModel;
    protected $notificationRecipientsModel;
    protected $session;
    protected $db;

    public function initController(\CodeIgniter\HTTP\RequestInterface $request, \CodeIgniter\HTTP\ResponseInterface $response, \Psr\Log\LoggerInterface $logger)
    {
        parent::initController($request, $response, $logger);
        $this->session = \Config\Services::session();
        $this->notificationModel = new NotificationModel();
        $this->notificationRecipientsModel = new NotificationRecipientsModel();
        $this->db = \Config\Database::connect();
    }

    public function index()
    {
        $userId = session('user_id');
        if (!$userId) {
            log_message('error', 'Session user_id tidak ditemukan');
            return redirect()->to('/login')->with('error', 'Silakan login kembali.');
        }

        // PERUBAHAN: Hapus semua kode fetch notifikasi karena sudah di-handle oleh NotificationCell
        // Notifikasi akan otomatis muncul di header melalui view_cell('NotificationCell::render')

        return view('layout/main_layout', [
            // PERUBAHAN: Hapus 'notifikasi' => $notifikasi karena tidak perlu lagi
            // Add other data as needed
            'title' => 'Dashboard DMS - Telkom University'
        ]);
    }

    public function dashboard(): string
    {   
        // PERUBAHAN: Hapus semua kode fetch notifikasi dari dashboard juga
        // NotificationCell akan otomatis handle notifikasi di header
        
        return view('dashboard', [
            // PERUBAHAN: Hapus 'notifikasi' => $notifikasi karena tidak perlu lagi
            'title' => 'Dashboard'
        ]);
    }
}