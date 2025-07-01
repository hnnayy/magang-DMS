<?php

namespace App\Controllers;

class DataMaster extends BaseController
{
    public function index()
    {
        return view('DataMaster/daftar-unit');
    }

    public function list()
    {
        return view('DataMaster/daftar-unit');
    }

    public function create()
    {
        return view('DataMaster/unit-create');
    }
}
