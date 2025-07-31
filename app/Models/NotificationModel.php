<?php

namespace App\Models;

use CodeIgniter\Model;

class NotificationModel extends Model
{
    protected $table = 'notification';
    protected $primaryKey = 'id';
    protected $allowedFields = ['submenu_id', 'reference_id', 'message', 'createdby', 'createddate'];
    protected $useTimestamps = false;
}
