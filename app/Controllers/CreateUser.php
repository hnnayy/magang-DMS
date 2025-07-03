<?php

namespace App\Controllers;

class CreateUser extends BaseController
{
    public function index()
    {
        return view('CreateUser/daftar-users');
    }

    public function list()
    {
        return view('CreateUser/daftar-users');
    }

    public function create()
    {
        return view('CreateUser/users-create');
    }

    
}
