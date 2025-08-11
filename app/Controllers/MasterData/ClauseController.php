<?php
namespace App\Controllers\MasterData;

use App\Controllers\BaseController;

class ClauseController extends BaseController
{
    public function index()
    {
        $data = [
            'title' => 'Clause Management'
        ];
        
        return view('DataMaster/clause', $data);
    }
}