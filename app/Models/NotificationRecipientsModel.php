<?php

namespace App\Models;

use CodeIgniter\Model;

class NotificationRecipientsModel extends Model
{
    protected $table = 'notification_recipients';
    protected $primaryKey = 'id';
    protected $allowedFields = ['notification_id', 'createdby', 'status'];
    protected $useTimestamps = false;
}