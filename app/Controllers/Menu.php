<?php
namespace App\Controllers;
use App\Controllers\BaseController;
use App\Models\MenuModel;
class Menu extends BaseController
{
    protected $menuModel;

    public function __construct()
    {
        $this->menuModel = new MenuModel(); 
    }

    public function create()
    {
        return view('Menu/create-menu', ['title' => 'Tambah Menu']);
    }

    public function index()
    {
        $data['menus'] = $this->menuModel->where('status !=', 0)->findAll();
        return view('Menu/lihat-menu', $data);
    }

    public function list()
    {
        $data['menus'] = $this->menuModel->where('status !=', 0)->findAll();
        return view('Menu/lihat-menu', $data);
    }

    public function store()
    {
        $validation = \Config\Services::validation();
        $rules = [
            'menu_name' => [
                'rules' => 'required|min_length[3]|max_length[50]|is_unique[menu.name]',
                'errors' => [
                    'min_length' => 'Nama menu minimal 3 karakter.',
                    'is_unique' => 'Nama menu sudah terdaftar.'
                ]
            ],
            'icon' => [
                'rules' => 'required|regex_match[/^[a-z0-9\-]+$/]',
                'errors' => [
                    'regex_match' => 'Icon hanya boleh berisi huruf kecil, angka, dan tanda minus (-).'
                ]
            ]
        ];
        if (!$this->validate($rules)) {
            return redirect()->back()
                ->withInput()
                ->with('error', implode(' ', $validation->getErrors()));
        }
        $data = [
            'name'   => $this->request->getPost('menu_name'),
            'icon'   => $this->request->getPost('icon'),
            'status' => $this->request->getPost('status') == '1' ? 1 : 2,
        ];
        if ($this->menuModel->insert($data)) {
            return redirect()->back()->with('success', 'Menu berhasil ditambahkan.');
        } else {
            return redirect()->back()->with('error', 'Gagal menambahkan menu.');
        }
    }

    public function delete($id)
    {
        $this->menuModel->update($id, ['status' => 0]);
        return redirect()->to(base_url('Menu'))->with('success', 'Menu berhasil dihapus.');
    }

    public function update($id)
{
    $data = $this->request->getPost();

    $validation = \Config\Services::validation();

    $rules = [
        'menu_name' => [
            'rules' => "required|min_length[3]|max_length[50]|is_unique[menu.name,id,{$id}]",
            'errors' => [
                'required'    => 'Nama menu wajib diisi.',
                'min_length'  => 'Nama menu minimal 3 karakter.',
                'max_length'  => 'Nama menu maksimal 50 karakter.',
                'is_unique'   => 'Nama menu sudah digunakan oleh menu lain.'
            ]
        ],
        'icon' => [
            'rules' => 'required|regex_match[/^[a-z0-9\-]+$/]',
            'errors' => [
                'required'     => 'Icon wajib diisi.',
                'regex_match'  => 'Icon hanya boleh berisi huruf kecil, angka, dan tanda minus (-).'
            ]
        ],
        'status' => [
            'rules' => 'required|in_list[1,2]',
            'errors' => [
                'required' => 'Status wajib dipilih.',
                'in_list'  => 'Status tidak valid.'
            ]
        ],
    ];

    if (!$this->validate($rules)) {
        return redirect()->back()
            ->withInput()
            ->with('validation', $this->validator)
            ->with('error', 'Validasi gagal. Silakan periksa kembali input Anda.');
    }

    // Simpan ke database
    $this->menuModel->update($id, [
        'name'   => trim($data['menu_name']),
        'icon'   => trim($data['icon']),
        'status' => $data['status'] == '1' ? 1 : 2,
    ]);

    return redirect()->to(base_url('Menu'))->with('success', 'Menu berhasil diperbarui.');
}



}
