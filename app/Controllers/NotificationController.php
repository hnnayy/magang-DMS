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
            // Gunakan query builder langsung untuk memastikan update berhasil
            $updated = $this->db->table('notification_recipients')
                ->where('user_id', $userId)
                ->where('status', 0)
                ->update(['status' => 1]);

            if ($updated === false) {
                log_message('error', 'Gagal menandai notifikasi sebagai dibaca untuk user_id: ' . $userId);
                return $this->response->setJSON(['status' => 'error', 'message' => 'Gagal menandai notifikasi']);
            }

            log_message('debug', "Successfully marked notifications as read for user_id: $userId");
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
            // PERBAIKAN: Query yang lebih robust untuk fetch notifications
            $query = $this->db->table('notification n')
                ->select('n.id, n.message, n.createddate, n.reference_id, n.createdby, u.username as creator_username, u.fullname as creator_fullname')
                ->join('notification_recipients nr', 'nr.notification_id = n.id', 'inner')
                ->join('user u', 'u.id = n.createdby', 'left')
                ->where('nr.user_id', $userId)
                ->where('nr.status', 0)
                ->orderBy('n.createddate', 'DESC');
            
            $notifikasi = $query->get()->getResultArray();

            log_message('debug', 'Raw notifications fetched for user_id ' . $userId . ': ' . json_encode($notifikasi));
            log_message('debug', 'Query executed: ' . $this->db->getLastQuery());

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
                
                log_message('debug', 'Processed notification ID: ' . $notif['id'] . ' with creator_name: ' . $notif['creator_name']);
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