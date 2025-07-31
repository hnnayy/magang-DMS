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

    public function initController(\CodeIgniter\HTTP\RequestInterface $request, \CodeIgniter\HTTP\ResponseInterface $response, \Psr\Log\LoggerInterface $logger)
    {
        parent::initController($request, $response, $logger);
        $this->session = \Config\Services::session();
        $this->notificationModel = new NotificationModel();
        $this->userModel = new UserModel();
        $this->documentModel = new DocumentModel();
    }

    public function markAsRead()
    {
        $userId = session()->get('user_id');
        if (!$userId) {
            log_message('error', 'Session user_id tidak ditemukan di markAsRead');
            return $this->response->setJSON(['status' => 'error', 'message' => 'Silakan login kembali']);
        }

        $notificationRecipientsModel = new \App\Models\NotificationRecipientsModel();
        $updated = $notificationRecipientsModel
            ->where('user_id', $userId)
            ->where('status', 0)
            ->set(['status' => 1])
            ->update();

        if ($updated === false) {
            log_message('error', 'Gagal menandai notifikasi sebagai dibaca: ' . json_encode($notificationRecipientsModel->errors()));
            return $this->response->setJSON(['status' => 'error', 'message' => 'Gagal menandai notifikasi']);
        }

        return $this->response->setJSON(['status' => 'success']);
    }

    public function fetch()
    {
        $userId = session()->get('user_id');
        if (!$userId) {
            log_message('error', 'Session user_id tidak ditemukan di fetch');
            return $this->response->setJSON(['status' => 'error', 'notifikasi' => [], 'message' => 'Silakan login kembali']);
        }

        $user = $this->userModel->select('role_id')->where('id', $userId)->first();
        if (!$user || !in_array($user['role_id'], [1, 2])) {
            log_message('debug', 'fetch - User ID ' . $userId . ' does not have approval access (role_id: ' . ($user['role_id'] ?? 'N/A') . ')');
            return $this->response->setJSON(['status' => 'success', 'notifikasi' => []]);
        }

        $notifikasi = $this->notificationModel
            ->select('notification.id, notification.message, notification.createddate, notification.reference_id, 
                     notification.createdby as creator_id, COALESCE(creator.fullname, \'Pengguna Tidak Dikenal\') as creator_name')
            ->join('user as creator', 'creator.id = notification.createdby', 'left')
            ->join('notification_recipients', 'notification_recipients.notification_id = notification.id', 'inner')
            ->join('document', 'document.id = notification.reference_id', 'inner')
            ->where('notification_recipients.user_id', $userId)
            ->where('notification_recipients.status', 0)
            ->groupBy('notification.id')
            ->orderBy('notification.createddate', 'DESC')
            ->findAll();

        log_message('debug', 'fetch - Raw notifications fetched for user_id ' . $userId . ': ' . json_encode($notifikasi));
        log_message('debug', 'fetch - Query executed: ' . $this->notificationModel->getLastQuery());

        foreach ($notifikasi as &$notif) {
            log_message('debug', 'fetch - Processing notification with creator_id: ' . ($notif['creator_id'] ?? 'N/A'));
            if ($notif['creator_name'] === 'Pengguna Tidak Dikenal' && !empty($notif['creator_id'])) {
                $creator = $this->userModel->find($notif['creator_id']);
                log_message('debug', 'fetch - UserModel find result for creator_id ' . $notif['creator_id'] . ': ' . json_encode($creator));
                $notif['creator_name'] = $creator['fullname'] ?? 'Pengguna Tidak Dikenal';
                log_message('debug', 'fetch - Fetched creator name for creator_id ' . $notif['creator_id'] . ': ' . $notif['creator_name']);
            }
            $notif['creator_id'] = $notif['creator_id'] ?? 'N/A';
            $notif['creator_name'] = $notif['creator_name'] ?? 'Pengguna Tidak Dikenal';
        }

        if ($this->request->isAJAX()) {
            return $this->response->setJSON(['status' => 'success', 'notifikasi' => $notifikasi]);
        }

        return view('layout/partials/notifikasi', ['notifikasi' => $notifikasi]);
    }
}