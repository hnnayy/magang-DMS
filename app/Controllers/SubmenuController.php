<?php

namespace App\Controllers;

use App\Controllers\BaseController;

class SubmenuController extends BaseController
{
    
    public function create()
    {
        return view('Submenu/submenu-create');
          
    }
    public function list()
    {
        return view('Submenu/lihat-submenu');
          
    }

   

}
