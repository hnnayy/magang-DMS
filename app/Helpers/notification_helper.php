<?php

if (!function_exists('getNotifications')) {
    /**
     * Get notifications for current user
     * @param int|null $userId User ID (optional, will use session if not provided)
     * @param int $limit Maximum number of notifications to fetch
     * @return array Array of notifications
     */
    function getNotifications($userId = null, $limit = 50) {
        // Get user ID from session if not provided
        if (!$userId) {
            $userId = session('user_id');
        }
        
        if (!$userId) {
            return [];
        }
        
        try {
            $db = \Config\Database::connect();
            
            $query = $db->table('notification n')
                ->select('n.id, n.message, n.createddate, n.reference_id, n.createdby, u.username as creator_username, u.fullname as creator_fullname')
                ->join('notification_recipients nr', 'nr.notification_id = n.id', 'inner')
                ->join('user u', 'u.id = n.createdby', 'left')
                ->where('nr.user_id', $userId)
                ->where('nr.status', 0)
                ->orderBy('n.createddate', 'DESC')
                ->limit($limit);
            
            $notifications = $query->get()->getResultArray();
            
            // Process notifications for consistency
            foreach ($notifications as &$notif) {
                if (empty($notif['creator_fullname'])) {
                    $notif['creator_name'] = $notif['creator_username'] ?? 'Pengguna Tidak Dikenal';
                } else {
                    $notif['creator_name'] = $notif['creator_fullname'];
                }
                $notif['creator_id'] = $notif['createdby'] ?? 'N/A';
            }
            
            return $notifications;
            
        } catch (\Exception $e) {
            log_message('error', 'Error fetching notifications in helper: ' . $e->getMessage());
            return [];
        }
    }
}

if (!function_exists('getNotificationCount')) {
    /**
     * Get unread notification count for current user
     * @param int|null $userId User ID (optional, will use session if not provided)
     * @return int Number of unread notifications
     */
    function getNotificationCount($userId = null) {
        if (!$userId) {
            $userId = session('user_id');
        }
        
        if (!$userId) {
            return 0;
        }
        
        try {
            $db = \Config\Database::connect();
            
            $count = $db->table('notification n')
                ->join('notification_recipients nr', 'nr.notification_id = n.id', 'inner')
                ->where('nr.user_id', $userId)
                ->where('nr.status', 0)
                ->countAllResults();
            
            return $count;
            
        } catch (\Exception $e) {
            log_message('error', 'Error counting notifications in helper: ' . $e->getMessage());
            return 0;
        }
    }
}

if (!function_exists('markNotificationAsRead')) {
    /**
     * Mark notification as read
     * @param int $notificationId Notification ID
     * @param int|null $userId User ID (optional, will use session if not provided)
     * @return bool Success status
     */
    function markNotificationAsRead($notificationId, $userId = null) {
        if (!$userId) {
            $userId = session('user_id');
        }
        
        if (!$userId) {
            return false;
        }
        
        try {
            $db = \Config\Database::connect();
            
            $result = $db->table('notification_recipients')
                ->where('notification_id', $notificationId)
                ->where('user_id', $userId)
                ->update(['status' => 1]);
            
            return $result;
            
        } catch (\Exception $e) {
            log_message('error', 'Error marking notification as read: ' . $e->getMessage());
            return false;
        }
    }
}

if (!function_exists('markAllNotificationsAsRead')) {
    /**
     * Mark all notifications as read for user
     * @param int|null $userId User ID (optional, will use session if not provided)
     * @return bool Success status
     */
    function markAllNotificationsAsRead($userId = null) {
        if (!$userId) {
            $userId = session('user_id');
        }
        
        if (!$userId) {
            return false;
        }
        
        try {
            $db = \Config\Database::connect();
            
            $result = $db->table('notification_recipients')
                ->where('user_id', $userId)
                ->where('status', 0)
                ->update(['status' => 1]);
            
            return $result;
            
        } catch (\Exception $e) {
            log_message('error', 'Error marking all notifications as read: ' . $e->getMessage());
            return false;
        }
    }
}