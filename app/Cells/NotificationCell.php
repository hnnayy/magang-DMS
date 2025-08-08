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
                $notifikasi = $this->db->table('notification n')
                    ->select('n.id, n.message, n.createddate, n.reference_id, n.createdby, u.username as creator_username, u.fullname as creator_fullname, nr.status')
                    ->join('notification_recipients nr', 'nr.notification_id = n.id', 'inner')
                    ->join('user u', 'u.id = n.createdby', 'left')
                    ->where('nr.user_id', $userId)
                    ->orderBy('n.createddate', 'DESC')
                    ->get()
                    ->getResultArray();

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

    if ($referenceId && (strpos($message, 'persetujuan') !== false || 
                        strpos($message, 'approve') !== false || 
                        strpos($message, 'disetujui') !== false || 
                        strpos($message, 'ditolak') !== false)) {
        return base_url('document-approval?document_id=' . $referenceId);
    }

    if ($referenceId && (strpos($message, 'dokumen') !== false || strpos($message, 'document') !== false)) {
        return base_url('document-submission-list?document_id=' . $referenceId . '&open_history=true');
    }

    if (strpos($message, 'dokumen') !== false || strpos($message, 'document') !== false) {
        return base_url('document-submission-list?open_history=true');
    }

    if (strpos($message, 'user') !== false || strpos($message, 'pengguna') !== false) {
        return base_url('user-management');
    }

    return base_url('dashboard');
}

    private function renderDropdown($notifikasi)
    {
        $html = '<div class="notification-container" id="notificationDropdown">
            <a class="notif-icon-wrapper" href="javascript:void(0)">
                <i class="fi fi-rr-bell"></i>';
        
        if (!empty($notifikasi)) {
            $unreadCount = count(array_filter($notifikasi, fn($notif) => $notif['status'] == 1));
            if ($unreadCount > 0) {
                $html .= '<span class="notif-badge">' . $unreadCount . '</span>';
            }
        }
        
        $html .= '</a>
            <div class="notification-menu" id="notif-list">
                <div class="notification-header">Notifications</div>';
        
        $html .= view('layout/partials/notifikasi', ['notifikasi' => $notifikasi]);
        
        $html .= '</div></div>';
        
        return $html;
    }
}