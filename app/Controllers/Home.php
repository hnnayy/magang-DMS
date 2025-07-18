<?php

namespace App\Controllers;
use App\Models\NotificationModel;

class Home extends BaseController
{
    public function index(): string
    {   
        $notifModel = new NotificationModel();
        $userId = session()->get('user_id'); // asumsi sudah login
        
        $notifikasi = $notifModel
            ->where('user_id', $userId)
            ->orderBy('created_at', 'DESC')
            ->findAll(5); // ambil 5 notifikasi terbaru

        return view('dashboard', [
            'notifikasi' => $notifikasi,
        ]);
    }
}
