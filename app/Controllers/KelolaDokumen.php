<?php

namespace App\Controllers;

class KelolaDokumen extends BaseController
{
    public function add()
    {
        return view('KelolaDokumen/add-dokumen');
    }

    public function pengajuan()
    {
        return view('KelolaDokumen/daftar-pengajuan');
    }


}
