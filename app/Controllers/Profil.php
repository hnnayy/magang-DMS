<?php

namespace App\Controllers;

use App\Models\UserModel;

class Profil extends BaseController
{
    protected $userModel;

    public function __construct()
    {
        $this->userModel = new UserModel();
    }

    public function index(): string
    {
        return view('layout/profile');
    }

    public function update()
    {
        $userId = session('user_id');
        if (!$userId) {
            return redirect()->back()->with('error', 'Session tidak valid.');
        }

        $username = $this->request->getPost('username');
        $fullname = $this->request->getPost('fullname');

        // Update ke database
        $this->userModel->update($userId, [
            'username' => $username,
            'fullname' => $fullname,
        ]);

        // Update ke session
        session()->set([
            'username' => $username,
            'fullname' => $fullname,
        ]);

        return redirect()->to('/profile')->with('success', 'Profil berhasil diperbarui.');
    }
}
