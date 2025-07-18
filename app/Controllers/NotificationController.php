<?php

namespace App\Controllers;

use App\Models\NotificationModel;
use CodeIgniter\Controller;

class NotificationController extends Controller
{
    public function markAsRead()
    {
        $userId = session()->get('user_id');
        $notificationModel = new NotificationModel();

        $notificationModel->where('user_id', $userId)
                          ->where('is_read', 0)
                          ->set(['is_read' => 1])
                          ->update();

        return $this->response->setJSON(['status' => 'success']);
    }

    // Tambahan opsional: ambil notifikasi terbaru via AJAX
    public function fetch()
    {
        $userId = session()->get('user_id');
        $notificationModel = new NotificationModel();

        $notifikasi = $notificationModel
            ->where('user_id', $userId)
            ->orderBy('created_at', 'DESC')
            ->findAll(10); // misalnya ambil 10 notif terbaru

        return view('layout/partials/notifikasi', ['notifikasi' => $notifikasi]);
    }
}
