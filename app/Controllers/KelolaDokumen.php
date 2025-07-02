<?php

namespace App\Controllers;

class KelolaDokumen extends BaseController
{
    public function add()
    {
        return view('KelolaDokumen/dokumen-create');
    }

    public function pengajuan()
    {
        return view('KelolaDokumen/daftar-pengajuan');
    }


}
