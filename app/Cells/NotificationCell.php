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
                $query = $this->db->table('notification n')
                    ->select('n.id, n.message, n.createddate, n.reference_id, n.createdby, u.username as creator_username, u.fullname as creator_fullname')
                    ->join('notification_recipients nr', 'nr.notification_id = n.id', 'inner')
                    ->join('user u', 'u.id = n.createdby', 'left')
                    ->where('nr.user_id', $userId)
                    ->where('nr.status', 0)
                    ->orderBy('n.createddate', 'DESC');
                
                $notifikasi = $query->get()->getResultArray();

                // Process notifications untuk konsistensi
                foreach ($notifikasi as &$notif) {
                    if (empty($notif['creator_fullname'])) {
                        $notif['creator_name'] = $notif['creator_username'] ?? 'Pengguna Tidak Dikenal';
                    } else {
                        $notif['creator_name'] = $notif['creator_fullname'];
                    }
                    $notif['creator_id'] = $notif['createdby'] ?? 'N/A';
                    
                    // Tentukan URL navigasi berdasarkan tipe notifikasi
                    $notif['navigation_url'] = $this->getNavigationUrl($notif);
                }

                log_message('debug', 'Notifications fetched via Cell for user ' . $userId . ': ' . count($notifikasi) . ' notifications');

            } catch (\Exception $e) {
                log_message('error', 'Error fetching notifications in NotificationCell: ' . $e->getMessage());
                $notifikasi = [];
            }
        }

        return $this->renderNotificationDropdown($notifikasi);
    }

    /**
     * Method untuk menentukan URL navigasi berdasarkan tipe notifikasi
     */
    private function getNavigationUrl($notification)
    {
        $message = strtolower($notification['message'] ?? '');
        $referenceId = $notification['reference_id'] ?? null;

        log_message('debug', 'Processing navigation URL for notification ID: ' . ($notification['id'] ?? 'N/A') . ', message: ' . $message . ', reference_id: ' . ($referenceId ?? 'N/A'));

        // Prioritize approval-related notifications with reference_id
        if ($referenceId && (strpos($message, 'persetujuan') !== false || 
                             strpos($message, 'approve') !== false || 
                             strpos($message, 'disetujui') !== false || 
                             strpos($message, 'ditolak') !== false || 
                             strpos($message, 'disapprove') !== false)) {
            log_message('debug', 'Notification ID: ' . ($notification['id'] ?? 'N/A') . ' routed to document-approval with document_id=' . $referenceId);
            return base_url('document-approval?document_id=' . $referenceId);
        }

        // Other document-related notifications with reference_id
        if ($referenceId && strpos($message, 'dokumen') !== false) {
            log_message('debug', 'Notification ID: ' . ($notification['id'] ?? 'N/A') . ' routed to document-submission-list with document_id=' . $referenceId);
            return base_url('document-submission-list?document_id=' . $referenceId);
        }

        // Fallback for general document-related notifications without reference_id
        if (strpos($message, 'dokumen') !== false) {
            log_message('debug', 'Notification ID: ' . ($notification['id'] ?? 'N/A') . ' routed to document-submission-list (no reference_id)');
            return base_url('document-submission-list');
        }

        // User-related notifications
        if (strpos($message, 'user') !== false || strpos($message, 'pengguna') !== false) {
            log_message('debug', 'Notification ID: ' . ($notification['id'] ?? 'N/A') . ' routed to user-management');
            return base_url('user-management');
        }

        // Default navigation
        log_message('debug', 'Notification ID: ' . ($notification['id'] ?? 'N/A') . ' routed to default dashboard');
        return base_url('dashboard');
    }

    private function renderNotificationDropdown($notifikasi)
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
                <li class="dropdown-header py-2">
                    Notifications
                </li>';
        
        // Load partial view yang sudah ada
        $html .= view('layout/partials/notifikasi', ['notifikasi' => $notifikasi]);
        
        $html .= '</ul>
        </div>
        
        <!-- CSS untuk styling notifikasi -->
        <style>
        .notif-badge {
            top: -2px;
            right: -8px;
            min-width: 18px;
            height: 18px;
            font-size: 11px;
            display: flex;
            align-items: center;
            justify-content: center;
            line-height: 1;
        }
        
        .notification-item {
            transition: background-color 0.2s ease;
            border-left: 3px solid transparent;
        }
        
        .notification-item:hover {
            background-color: #f8f9fa !important;
            border-left-color: #007bff;
        }
        
        .unread-indicator {
            top: 50%;
            left: 8px;
            transform: translateY(-50%);
            width: 6px;
            height: 6px;
            background-color: #dc3545;
            border-radius: 50%;
            animation: pulse-dot 2s infinite;
        }
        
        @keyframes pulse-dot {
            0% { opacity: 1; transform: translateY(-50%) scale(1); }
            50% { opacity: 0.7; transform: translateY(-50%) scale(1.1); }
            100% { opacity: 1; transform: translateY(-50%) scale(1); }
        }
        
        .notification-content {
            margin-left: 20px;
        }
        
        .notification-item.read .unread-indicator {
            display: none;
        }
        </style>';
        
        return $html;
    }
}