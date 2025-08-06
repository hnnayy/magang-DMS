<?php

namespace App\Cells;

use CodeIgniter\View\Cells\Cell;

class NotificationCell extends Cell
{
    protected $db;

    public function __construct()
    {
        $this->db = \Config\Database::connect();
    }

    public function render(): string
    {
        $userId = session('user_id');
        $notifikasi = [];
        
        if ($userId) {
            try {
                // Hanya ambil notification yang status = 0 (belum dibaca)
                $notifikasi = $this->db->table('notification n')
                    ->select('n.id, n.message, n.createddate, n.reference_id, n.createdby, u.username as creator_username, u.fullname as creator_fullname')
                    ->join('notification_recipients nr', 'nr.notification_id = n.id', 'inner')
                    ->join('user u', 'u.id = n.createdby', 'left')
                    ->where('nr.user_id', $userId)
                    ->where('nr.status', 0) // hanya yang belum dibaca
                    ->orderBy('n.createddate', 'DESC')
                    ->get()
                    ->getResultArray();

                // Process notifications
                foreach ($notifikasi as &$notif) {
                    $notif['creator_name'] = $notif['creator_fullname'] ?: ($notif['creator_username'] ?: 'Unknown User');
                    $notif['navigation_url'] = $this->getNavigationUrl($notif);
                }

            } catch (\Exception $e) {
                log_message('error', 'NotificationCell error: ' . $e->getMessage());
                $notifikasi = [];
            }
        }

        return $this->renderDropdown($notifikasi);
    }

    private function getNavigationUrl($notification)
    {
        $message = strtolower($notification['message'] ?? '');
        $referenceId = $notification['reference_id'] ?? null;

        // Document approval
        if ($referenceId && (strpos($message, 'persetujuan') !== false || 
                             strpos($message, 'approve') !== false || 
                             strpos($message, 'disetujui') !== false || 
                             strpos($message, 'ditolak') !== false)) {
            return base_url('document-approval?document_id=' . $referenceId);
        }

        // Document-related
        if ($referenceId && strpos($message, 'dokumen') !== false) {
            return base_url('document-submission-list?document_id=' . $referenceId);
        }

        if (strpos($message, 'dokumen') !== false || strpos($message, 'document') !== false) {
            return base_url('document-submission-list');
        }

        // User-related
        if (strpos($message, 'user') !== false || strpos($message, 'pengguna') !== false) {
            return base_url('user-management');
        }

        return base_url('dashboard');
    }

    private function renderDropdown($notifikasi)
    {
        $html = '<div class="dropdown me-3" id="notificationDropdown">
            <a class="nav-link position-relative notif-icon-wrapper" href="#" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="fi fi-rr-bell"></i>';
        
        if (!empty($notifikasi)) {
            $html .= '<span class="notif-badge position-absolute translate-middle badge rounded-pill bg-danger">
                ' . count($notifikasi) . '
                <span class="visually-hidden">notifikasi belum dibaca</span>
            </span>';
        }
        
        $html .= '</a>
            <ul class="dropdown-menu dropdown-menu-end border" id="notif-list" style="max-height: 400px; overflow-y: auto; min-width: 320px;">
                <li class="dropdown-header py-2">Notifications</li>';
        
        // Include the notification partial
        $html .= view('layout/partials/notifikasi', ['notifikasi' => $notifikasi]);
        
        $html .= '</ul></div>';
        
        return $html;
    }
}