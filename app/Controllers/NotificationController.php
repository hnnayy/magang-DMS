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
            echo json_encode(['status' => 'error', 'message' => 'Not authenticated']);
            return;
        }

        try {
            $input = json_decode($this->request->getBody(), true);
            
            if (isset($input['mark_all']) && $input['mark_all'] === true) {
                // Mark all notifications as read
                $this->db->table('notification_recipients')
                    ->where('user_id', $userId)
                    ->where('status', 0)
                    ->update(['status' => 1]);
                
                echo json_encode([
                    'status' => 'success', 
                    'message' => 'All notifications marked as read'
                ]);
            } else if (isset($input['notification_id'])) {
                // Mark single notification as read
                $notificationId = $input['notification_id'];
                
                $this->db->table('notification_recipients')
                    ->where('notification_id', $notificationId)
                    ->where('user_id', $userId)
                    ->update(['status' => 1]);
                
                echo json_encode([
                    'status' => 'success', 
                    'message' => 'Notification marked as read'
                ]);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Invalid request']);
            }
            
        } catch (\Exception $e) {
            log_message('error', 'Notification markAsRead error: ' . $e->getMessage());
            echo json_encode(['status' => 'error', 'message' => 'Server error']);
        }
    }

    public function fetch()
    {
        header('Content-Type: application/json');
        
        $userId = session('user_id');
        if (!$userId) {
            echo json_encode(['status' => 'error', 'message' => 'Not authenticated']);
            return;
        }

        try {
            // Get unread notifications (status = 0)
            $notifications = $this->db->table('notification n')
                ->select('n.id, n.message, n.createddate, n.reference_id, n.createdby, u.username as creator_username, u.fullname as creator_fullname')
                ->join('notification_recipients nr', 'nr.notification_id = n.id', 'inner')
                ->join('user u', 'u.id = n.createdby', 'left')
                ->where('nr.user_id', $userId)
                ->where('nr.status', 0)
                ->orderBy('n.createddate', 'DESC')
                ->get()
                ->getResultArray();

            // Process notifications
            foreach ($notifications as &$notif) {
                $notif['creator_name'] = $notif['creator_fullname'] ?: ($notif['creator_username'] ?: 'Unknown User');
                $notif['navigation_url'] = $this->getNavigationUrl($notif);
            }

            echo json_encode([
                'status' => 'success',
                'notifikasi' => $notifications,
                'count' => count($notifications)
            ]);
            
        } catch (\Exception $e) {
            log_message('error', 'Notification fetch error: ' . $e->getMessage());
            echo json_encode(['status' => 'error', 'message' => 'Server error']);
        }
    }

    public function testNotification()
    {
        $userId = session('user_id');
        if (!$userId) {
            echo "User not logged in";
            return;
        }

        try {
            // Insert test notification
            $this->db->table('notification')->insert([
                'message' => 'Test notification at ' . date('Y-m-d H:i:s'),
                'createdby' => $userId,
                'createddate' => date('Y-m-d H:i:s')
            ]);
            
            $notificationId = $this->db->insertID();
            
            // Insert recipient
            $this->db->table('notification_recipients')->insert([
                'notification_id' => $notificationId,
                'user_id' => $userId,
                'status' => 0
            ]);
            
            echo "Test notification created with ID: " . $notificationId;
            
        } catch (\Exception $e) {
            echo "Error: " . $e->getMessage();
        }
    }

    private function getNavigationUrl($notification)
    {
        $message = strtolower($notification['message'] ?? '');
        $referenceId = $notification['reference_id'] ?? null;

        // Document approval notifications
        if ($referenceId && (strpos($message, 'persetujuan') !== false || 
                             strpos($message, 'approve') !== false || 
                             strpos($message, 'disetujui') !== false || 
                             strpos($message, 'ditolak') !== false)) {
            return base_url('document-approval?document_id=' . $referenceId);
        }

        // Document-related notifications
        if ($referenceId && strpos($message, 'dokumen') !== false) {
            return base_url('document-submission-list?document_id=' . $referenceId);
        }

        // General document notifications
        if (strpos($message, 'dokumen') !== false || strpos($message, 'document') !== false) {
            return base_url('document-submission-list');
        }

        // User-related notifications
        if (strpos($message, 'user') !== false || strpos($message, 'pengguna') !== false) {
            return base_url('user-management');
        }

        // Default
        return base_url('dashboard');
    }
}