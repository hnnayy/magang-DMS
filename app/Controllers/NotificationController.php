<?php
namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\NotificationModel;
use App\Models\UserModel;
use App\Models\DocumentModel;

class NotificationController extends BaseController
{
    protected $notificationModel;
    protected $userModel;
    protected $documentModel;
    protected $session;
    protected $db;

    public function initController(\CodeIgniter\HTTP\RequestInterface $request, \CodeIgniter\HTTP\ResponseInterface $response, \Psr\Log\LoggerInterface $logger)
    {
        parent::initController($request, $response, $logger);
        $this->session = \Config\Services::session();
        $this->notificationModel = new NotificationModel();
        $this->userModel = new UserModel();
        $this->documentModel = new DocumentModel();
        $this->db = \Config\Database::connect();
    }

    public function markAsRead()
    {
        $userId = session()->get('user_id');
        if (!$userId) {
            log_message('error', 'Session user_id tidak ditemukan di markAsRead');
            return $this->response->setJSON(['status' => 'error', 'message' => 'Silakan login kembali']);
        }

        try {
            // Support untuk mark individual notification
            $input = $this->request->getJSON(true);
            $notificationId = $input['notification_id'] ?? null;

            if ($notificationId) {
                // Mark specific notification as read
                $updated = $this->db->table('notification_recipients')
                    ->where('user_id', $userId)
                    ->where('notification_id', $notificationId)
                    ->where('status', 0)
                    ->update(['status' => 1]);

                log_message('debug', "Marked notification ID: $notificationId as read for user_id: $userId");
            } else {
                // Mark all notifications as read (existing functionality)
                $updated = $this->db->table('notification_recipients')
                    ->where('user_id', $userId)
                    ->where('status', 0)
                    ->update(['status' => 1]);

                log_message('debug', "Marked all notifications as read for user_id: $userId");
            }

            if ($updated === false) {
                log_message('error', 'Gagal menandai notifikasi sebagai dibaca untuk user_id: ' . $userId);
                return $this->response->setJSON(['status' => 'error', 'message' => 'Gagal menandai notifikasi']);
            }

            return $this->response->setJSON(['status' => 'success']);

        } catch (\Exception $e) {
            log_message('error', 'Exception in markAsRead: ' . $e->getMessage());
            return $this->response->setJSON(['status' => 'error', 'message' => 'Terjadi kesalahan sistem']);
        }
    }

    public function fetch()
    {
        $userId = session()->get('user_id');
        if (!$userId) {
            log_message('error', 'Session user_id tidak ditemukan di fetch');
            return $this->response->setJSON(['status' => 'error', 'notifikasi' => [], 'message' => 'Silakan login kembali']);
        }

        try {
            // Query yang lebih robust untuk fetch notifications
            $query = $this->db->table('notification n')
                ->select('n.id, n.message, n.createddate, n.reference_id, n.createdby, u.username as creator_username, u.fullname as creator_fullname')
                ->join('notification_recipients nr', 'nr.notification_id = n.id', 'inner')
                ->join('user u', 'u.id = n.createdby', 'left')
                ->where('nr.user_id', $userId)
                ->where('nr.status', 0)
                ->orderBy('n.createddate', 'DESC');
            
            $notifikasi = $query->get()->getResultArray();

            log_message('debug', 'Raw notifications fetched for user_id ' . $userId . ': ' . json_encode($notifikasi));

            // Process notifications untuk format yang konsisten
            foreach ($notifikasi as &$notif) {
                // Fallback untuk creator name
                if (empty($notif['creator_fullname'])) {
                    $notif['creator_name'] = $notif['creator_username'] ?? 'Pengguna Tidak Dikenal';
                } else {
                    $notif['creator_name'] = $notif['creator_fullname'];
                }
                
                // Ensure consistent field names
                $notif['creator_id'] = $notif['createdby'] ?? 'N/A';
                
                // Tambahkan navigation URL
                $notif['navigation_url'] = $this->getNavigationUrl($notif);
                
                log_message('debug', 'Processed notification ID: ' . $notif['id'] . ' with navigation_url: ' . $notif['navigation_url']);
            }

            if ($this->request->isAJAX()) {
                return $this->response->setJSON(['status' => 'success', 'notifikasi' => $notifikasi]);
            }

            return view('layout/partials/notifikasi', ['notifikasi' => $notifikasi]);

        } catch (\Exception $e) {
            log_message('error', 'Exception in fetch notifications: ' . $e->getMessage());
            return $this->response->setJSON(['status' => 'error', 'notifikasi' => [], 'message' => 'Terjadi kesalahan sistem']);
        }
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

    /**
     * Method untuk testing - bisa dihapus setelah testing selesai
     */
    public function testNotification()
    {
        $userId = session()->get('user_id');
        
        // Test query untuk melihat data
        $notificationCount = $this->db->table('notification')->countAllResults();
        $recipientCount = $this->db->table('notification_recipients')->countAllResults();
        $userNotifCount = $this->db->table('notification_recipients')->where('user_id', $userId)->countAllResults();
        
        $result = [
            'total_notifications' => $notificationCount,
            'total_recipients' => $recipientCount,
            'user_notifications' => $userNotifCount,
            'current_user_id' => $userId
        ];
        
        log_message('debug', 'Test notification result: ' . json_encode($result));
        
        return $this->response->setJSON($result);
    }
}