<?php

namespace App\Controllers;

use CodeIgniter\Controller;

class NotificationController extends Controller
{
    protected $db;
    
    public function __construct()
    {
        $this->db = \Config\Database::connect();
    }

    public function markAsRead()
    {
        header('Content-Type: application/json');
        
        $userId = session('user_id');
        if (!$userId) {
            return json_encode(['status' => 'error', 'message' => 'Not authenticated']);
        }

        try {
            $input = json_decode($this->request->getBody(), true);
            
            if (isset($input['mark_all']) && $input['mark_all'] === true) {
                $this->db->table('notification_recipients')
                    ->where('user_id', $userId)
                    ->where('status', 1)
                    ->update(['status' => 2]);
                
                return json_encode([
                    'status' => 'success', 
                    'message' => 'All notifications marked as read'
                ]);
            } elseif (isset($input['notification_id'])) {
                $notificationId = $input['notification_id'];
                
                $this->db->table('notification_recipients')
                    ->where('notification_id', $notificationId)
                    ->where('user_id', $userId)
                    ->update(['status' => 2]);
                
                return json_encode([
                    'status' => 'success', 
                    'message' => 'Notification marked as read'
                ]);
            }
            
            return json_encode(['status' => 'error', 'message' => 'Invalid request']);
        } catch (\Exception $e) {
            log_message('error', 'Notification markAsRead error: ' . $e->getMessage());
            return json_encode(['status' => 'error', 'message' => 'Server error']);
        }
    }

    public function fetch()
    {
        header('Content-Type: application/json');
        
        $userId = session('user_id');
        if (!$userId) {
            return json_encode(['status' => 'error', 'message' => 'Not authenticated']);
        }

        try {
            $notifications = $this->db->table('notification n')
                ->select('n.id, n.message, n.createddate, n.reference_id, n.createdby, u.username as creator_username, u.fullname as creator_fullname, nr.status')
                ->join('notification_recipients nr', 'nr.notification_id = n.id', 'inner')
                ->join('user u', 'u.id = n.createdby', 'left')
                ->where('nr.user_id', $userId)
                ->orderBy('n.createddate', 'DESC')
                ->get()
                ->getResultArray();

            foreach ($notifications as &$notif) {
                $notif['creator_name'] = $notif['creator_fullname'] ?: ($notif['creator_username'] ?: 'Unknown User');
                $notif['navigation_url'] = $this->getNavigationUrl($notif);
            }

            return json_encode([
                'status' => 'success',
                'notifikasi' => $notifications,
                'count' => count($notifications)
            ]);
        } catch (\Exception $e) {
            log_message('error', 'Notification fetch error: ' . $e->getMessage());
            return json_encode(['status' => 'error', 'message' => 'Server error']);
        }
    }

    public function testNotification()
    {
        $userId = session('user_id');
        if (!$userId) {
            return "User not logged in";
        }

        try {
            $this->db->table('notification')->insert([
                'message' => 'Test notification at ' . date('Y-m-d H:i:s'),
                'createdby' => $userId,
                'createddate' => date('Y-m-d H:i:s')
            ]);
            
            $notificationId = $this->db->insertID();
            
            $this->db->table('notification_recipients')->insert([
                'notification_id' => $notificationId,
                'user_id' => $userId,
                'status' => 1
            ]);
            
            return "Test notification created with ID: " . $notificationId;
        } catch (\Exception $e) {
            return "Error: " . $e->getMessage();
        }
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
            return base_url('document-submission-list?document_id=' . $referenceId);
        }

        if (strpos($message, 'dokumen') !== false || strpos($message, 'document') !== false) {
            return base_url('document-submission-list');
        }

        if (strpos($message, 'user') !== false || strpos($message, 'pengguna') !== false) {
            return base_url('user-management');
        }

        return base_url('dashboard');
    }
}