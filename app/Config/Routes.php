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


//hanin 11-07-2025
$routes->get('daftar-pengajuan', 'KelolaDokumen::pengajuan');
$routes->post('kelola-dokumen/approvepengajuan', 'KelolaDokumen::approvePengajuan');
$routes->post('kelola-dokumen/deletepengajuan', 'KelolaDokumen::deletePengajuan');
$routes->post('kelola-dokumen/updatepengajuan', 'KelolaDokumen::updatePengajuan');

$routes->get('/kelola-dokumen/config-jenis-dokumen', 'KelolaDokumen\ControllerConfigKategori::configJenisDokumen');
$routes->post('/kelola-dokumen/add-kategori', 'KelolaDokumen\ControllerConfigKategori::addKategori');
$routes->post('/kelola-dokumen/edit-kategori', 'KelolaDokumen\ControllerConfigKategori::editKategori');
$routes->post('/kelola-dokumen/delete-kategori', 'KelolaDokumen\ControllerConfigKategori::deleteKategori');
$routes->post('/kelola-dokumen/add-kode', 'KelolaDokumen\ControllerConfigKategori::addKode');
$routes->post('/kelola-dokumen/edit-kode', 'KelolaDokumen\ControllerConfigKategori::editKode');
$routes->post('/kelola-dokumen/delete-kode', 'KelolaDokumen\ControllerConfigKategori::delete_kode');

$routes->get('/dokumen/cetak-signed', 'KelolaDokumen::generateSignedPDF');


$routes->get('kelola-dokumen/persetujuan', 'KelolaDokumen\ControllerPersetujuan::index');
$routes->post('kelola-dokumen/persetujuan/update', 'KelolaDokumen\ControllerPersetujuan::update');
$routes->post('kelola-dokumen/persetujuan/delete', 'KelolaDokumen\ControllerPersetujuan::delete');

//daftar dokumen
$routes->get('daftar-dokumen', 'DaftarDokumen\ControllerDaftarDokumen::index');
$routes->post('daftar-dokumen/delete/(:num)', 'DaftarDokumen\ControllerDaftarDokumen::delete/$1');
$routes->post('daftar-dokumen/update', 'DaftarDokumen\ControllerDaftarDokumen::updateDokumen');


//CIPA PRIVILAGE
$route['privilege/update'] = 'privilege/update';
$route['privilege/delete'] = 'privilege/delete';
$routes->post('privilege/update', 'Privilege::update');
$routes->post('privilege/delete', 'Privilege::delete');

$routes->get('privilege/lihat-privilege', 'privilege::list');


//14-07-2025 10.18 HANIN
//DUMMY
$routes->get('generatetoken', 'Dummy\TokenDummy::generateAllTokens');
$routes->get('parse-token', 'Dummy\TokenDummy::parseToken');

$routes->get('wc-dummy', 'Dummy\DummyWCController::index');
$routes->post('wc-dummy/login', 'Dummy\DummyWCController::redirectToDMS');

// $routes->get('dashboard', 'Home::index', ['filter' => 'auth']);


//naya 1407
$routes->post('privilege/update', 'Privilege::update');

//sutra 1507
// Fakultas
$routes->get('fakultas', 'FakultasController::index');                  // Menampilkan daftar fakultas
$routes->get('fakultas/create', 'FakultasController::create');         // Menampilkan form tambah fakultas
$routes->post('fakultas/store', 'FakultasController::store');          // Menyimpan fakultas baru ke database
$routes->post('fakultas/update/(:num)', 'FakultasController::update/$1'); // Memperbarui data fakultas
$routes->post('fakultas/delete/(:num)', 'FakultasController::softDelete/$1'); // Soft delete (ubah status atau flag deleted)


