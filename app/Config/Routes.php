<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Home::index');

// CreateUser
$routes->get('create-user', 'CreateUser::index');
$routes->get('create-user/list', 'CreateUser::list');
$routes->get('create-user/create', 'CreateUser::create');
$routes->get('CreateUser/create', 'CreateUser::create');
$routes->post('CreateUser/store', 'CreateUser::store');
$routes->get('CreateUser/getUnits/(:num)', 'CreateUser::getUnits/$1');
$routes->delete('CreateUser/delete/(:num)', 'CreateUser::delete/$1');
$routes->post('CreateUser/update', 'CreateUser::update');

$routes->get('create-user/user-role', 'CreateUser::createRole');
$routes->get('create-user/user-privilege', 'CreateUser::privilege');
$routes->post('create-user/store-role', 'CreateUser::storeRole'); // jika ada form post
//cipa
$routes->post('create-role/store', 'CreateUser::storeRole');

//Privilege
$routes->get('create-user/privilege',        'CreateUser::privilege');
$routes->post('create-user/privilege/store', 'CreateUser::storePrivilege');

// DataMaster
$routes->get('data-master', 'DataMaster::index');
$routes->get('data-master/list', 'DataMaster::list');
$routes->get('data-master/create', 'DataMaster::create');
$routes->post('data-master/store', 'DataMaster::store');
$routes->get('data-master/unit/(:num)/edit', 'DataMaster::edit/$1');
$routes->post('data-master/unit/(:num)/update', 'DataMaster::update/$1');
$routes->post('data-master/unit/(:num)/delete', 'DataMaster::delete/$1');
$routes->get('data-master/export/csv', 'DataMaster::exportCsv');
$routes->get('data-master/export/excel', 'DataMaster::exportExcel');
$routes->get('data-master/export/pdf', 'DataMaster::exportPdf');
$routes->get('data-master/export/print', 'DataMaster::exportPrint');

// KelolaDokumen
$routes->get('dokumen/add', 'KelolaDokumen::add');
$routes->get('dokumen/pengajuan', 'KelolaDokumen::pengajuan');
$routes->get('dokumen/config-jenis-dokumen', 'KelolaDokumen::configJenisDokumen');


$routes->get('kelola-dokumen/configJenisDokumen', 'KelolaDokumen::configJenisDokumen');
$routes->get('dokumen/config-kategori', 'KelolaDokumen::configJenisDokumen');
$routes->post('/kelola-dokumen/get-kode-by-jenis', 'KelolaDokumen::getKodeByJenis');
$routes->post('admin/dokumen/delete-kode', 'KelolaDokumen::delete_kode');

// DaftarDokumen
$routes->get('dokumen/daftar', 'DaftarDokumen::index');

// PersetujuanDokumen
$routes->get('dokumen/persetujuan', 'PersetujuanDokumen::index');

// CRUD routes for categories (gunakan prefiks admin untuk konsistensi dengan form)
$routes->post('admin/dokumen/add-kategori', 'KelolaDokumen::addKategori');
$routes->post('admin/dokumen/edit-kategori', 'KelolaDokumen::editKategori');
$routes->post('admin/dokumen/delete-kategori', 'KelolaDokumen::deleteKategori');

// CRUD routes for codes
$routes->post('admin/dokumen/add-kode', 'KelolaDokumen::addKode');
$routes->post('admin/dokumen/edit-kode', 'KelolaDokumen::editKode');
$routes->post('admin/dokumen/delete-kode', 'KelolaDokumen::deleteKode');


$routes->get('profile', 'Profil::index');


//cipa
$routes->post('create-role/store', 'CreateUser::storeRole');
$routes->post('dokumen/get-kode-dokumen', 'KelolaDokumen::getKodeDokumen');
$routes->post('kelola-dokumen/tambah', 'KelolaDokumen::tambah');



// Role
$routes->get('role/create', 'Role::create');              // Menampilkan form tambah role
$routes->post('role/store', 'Role::store');              // Menyimpan role baru ke database
$routes->get('role/list', 'Role::list');                // Menampilkan daftar role (tidak dihapus)
$routes->post('role/delete/(:num)', 'Role::delete/$1'); // "Menghapus" role (soft delete), param $1 = id
$routes->post('role/update/(:num)', 'Role::update/$1'); // Mengupdate role, param $1 = id


//privilege
$routes->get('privilege/create', 'Privilege::create');          // Form tambah privilege
$routes->post('privilege/store', 'Privilege::store');    // Proses simpan
$routes->get('privilege/list', 'Privilege::list');       // Lihat daftar privilege


//Menu
//Cipa menu
$routes->get('Menu', 'Menu::index');
$routes->get('Menu/create', 'Menu::create');
$routes->post('Menu/store', 'Menu::store');
$routes->get('Menu/list', 'Menu::list');
$routes->get('Menu/lihat-menu', 'Menu::list');
$routes->get('Menu', 'Menu::index');
$routes->get('Menu/create', 'Menu::create');
$routes->post('Menu/store', 'Menu::store');
$routes->post('Menu/update/(:num)', 'Menu::update/$1');
$routes->post('Menu/delete/(:num)', 'Menu::delete/$1'); 


// submenu
$routes->get('submenu/create', 'SubmenuController::create');
$routes->post('submenu/store', 'SubmenuController::store'); //  untuk submit form create
$routes->get('submenu/lihat-submenu', 'SubmenuController::list'); //  list submenu

$routes->get('submenu/edit/(:num)', 'SubmenuController::edit/$1'); //  form edit
$routes->post('submenu/update/(:num)', 'SubmenuController::update/$1'); //  simpan edit
$routes->post('submenu/delete/(:num)', 'SubmenuController::delete/$1'); 



//CIP
$routes->post('kelola-dokumen/edit', 'KelolaDokumen::edit');

$routes->get('daftar-pengajuan', 'KelolaDokumen::daftarPengajuan');
