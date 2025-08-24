<?php

namespace App\Controllers\Dummy;

use App\Controllers\BaseController;

class LoginController extends BaseController
{
    public function index()
    {
        return view('dummy_wc/information');
    }
}