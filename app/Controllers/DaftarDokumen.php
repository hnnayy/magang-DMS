<?php

namespace App\Controllers;

class DaftarDokumen extends BaseController
{
    public function index(): string
    {
        return view('DaftarDokumen/daftar-dokumen');
    }
}
