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
                }

                log_message('debug', 'Notifications fetched via Cell for user ' . $userId . ': ' . count($notifikasi) . ' notifications');

            } catch (\Exception $e) {
                log_message('error', 'Error fetching notifications in NotificationCell: ' . $e->getMessage());
                $notifikasi = [];
            }
        }

        // Return HTML notification dropdown yang sama persis seperti struktur existing
        return $this->renderNotificationDropdown($notifikasi);
    }

    private function renderNotificationDropdown($notifikasi)
    {
        $html = '<div class="dropdown me-3" id="notificationDropdown">
            <a class="nav-link position-relative" href="#" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="fi fi-rr-bell"></i>';
        
        if (!empty($notifikasi)) {
            $html .= '<span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
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
        </div>';
        
        return $html;
    }
}