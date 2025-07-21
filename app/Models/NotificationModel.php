<?php

namespace App\Models;

use CodeIgniter\Model;

class NotificationModel extends Model
{
    protected $table            = 'notifications';
    protected $primaryKey       = 'id';
    protected $allowedFields    = ['user_id', 'document_id', 'title', 'message', 'link', 'is_read'];
    
    // Aktifkan timestamps
    protected $useTimestamps    = true;
    protected $createdField     = 'created_at';
    protected $updatedField     = ''; // tidak ada kolom updated_at
}
