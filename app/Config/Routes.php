<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */

//alur dummy
$routes->get('/wc-dummy', 'Dummy\TokenDummy::index');
$routes->post('/wc-dummy/login', 'Dummy\TokenDummy::login');
$routes->get('/parse-token', 'Dummy\TokenDummy::parseToken');
$routes->get('/generateAllTokens', 'Dummy\TokenDummy::generateAllTokens'); // opsional

if (ENVIRONMENT === 'development') {
    $routes->get('/', fn() => redirect()->to('/wc-dummy'));
}


$routes->get('monev-dashboard', function () {
    $token = session('jwt_token'); // ngambil token dari session

    if (!$token) {
        return redirect()->to('/')->with('error', 'Token tidak tersedia.');
    }

    return redirect()->to('https://clear.celoe.org/?token=' . $token);
});

$routes->post('/api/decode-token', 'Dummy\TokenDummy::apiDecodeToken');


// $routes->get('/', 'Home::index');
$routes->get('/dashboard', 'Home::dashboard');

// CreateUser
$routes->get('create-user', to: 'CreateUser::create');
$routes->get('user-list', 'CreateUser::list');
$routes->post('create-user/store', 'CreateUser::store');
$routes->post('create-user/delete', 'CreateUser::delete');
$routes->post('create-user/update', 'CreateUser::update');

//master data
//unit
$routes->get('create-unit', 'MasterData\UnitController::create');
$routes->get('unit-list', 'MasterData\UnitController::list');
$routes->post('create-unit/store', 'MasterData\UnitController::store');
$routes->get('create-unit/edit', 'MasterData\UnitController::edit');
$routes->post('create-unit/update', 'MasterData\UnitController::update');
$routes->post('create-unit/delete', 'MasterData\UnitController::delete');

//faculty
$routes->get('create-faculty', 'MasterData\FakultasController::create');
$routes->get('faculty-list', 'MasterData\FakultasController::index');
$routes->post('create-faculty/store', 'MasterData\FakultasController::store');
$routes->get('create-faculty/edit', 'MasterData\UnitController::edit');
$routes->post('create-faculty/update/', 'MasterData\FakultasController::update');
$routes->post('create-faculty/delete', 'MasterData\FakultasController::delete');

//menu
$routes->get('create-menu', 'Menu::create');
$routes->get('menu-list', 'Menu::list');
$routes->post('create-menu/store', 'Menu::store');
$routes->post('create-menu/update', 'Menu::update');
$routes->post('create-menu/delete', 'Menu::delete'); 

//submenu
$routes->get('create-submenu', 'SubmenuController::create');
$routes->get('submenu-list', to: 'SubmenuController::list'); 
$routes->post('create-submenu/store', 'SubmenuController::store'); 
$routes->get('create-submenu/edit', 'SubmenuController::edit'); 
$routes->post('create-submenu/update', 'SubmenuController::update'); 
$routes->post('create-submenu/delete', 'SubmenuController::delete'); 

//role
$routes->get('create-role', 'Role::create');             
$routes->get('role-list', 'Role::list'); 
$routes->post('create-role/store', to: 'Role::storeRole');
$routes->post('create-role/delete', 'Role::delete'); 
$routes->post('create-role/update', 'Role::update'); 

//privilege
$routes->get('create-privilege', 'Privilege::create');   
$routes->get('privilege-list', 'Privilege::list');       // Lihat daftar privilege
$routes->post('create-privilege/store', 'Privilege::store');    
$routes->post('create-privilege/update', 'Privilege::update');
$routes->post('create-privilege/delete', 'Privilege::delete');


//document type code
$routes->get('document-type-code', 'KelolaDokumen::configJenisDokumen');
// Routes untuk Document Type (Kategori Dokumen)
$routes->get('document-type', 'MasterData\DocumentTypeController::index');
$routes->post('document-type/add', 'MasterData\DocumentTypeController::add');
$routes->post('document-type/edit', 'MasterData\DocumentTypeController::edit');
$routes->post('document-type/delete', 'MasterData\DocumentTypeController::delete');

// Routes untuk Document Code (Kode Dokumen)
$routes->get('document-code', 'MasterData\DocumentCodeController::index');
$routes->post('document-code/add', 'MasterData\DocumentCodeController::add');
$routes->post('document-code/edit', 'MasterData\DocumentCodeController::edit');
$routes->post('document-code/delete', 'MasterData\DocumentCodeController::delete');


//Kelola Dokumen 
$routes->get('create-document', 'KelolaDokumen\CreateDokumenController::add');
$routes->post('create-document/store', 'KelolaDokumen\CreateDokumenController::tambah');

//document submission list (pengajuan)
$routes->get('document-submission-list', 'KelolaDokumen\PengajuanController::index');
$routes->post('document-submission-list/store', 'KelolaDokumen\PengajuanController::store');
$routes->get('document-submission-list/edit', 'KelolaDokumen\PengajuanController::edit');
$routes->post('document-submission-list/update', 'KelolaDokumen\PengajuanController::update');
$routes->post('document-submission-list/delete', 'KelolaDokumen\PengajuanController::delete');
$routes->post('document-submission-list/approve', 'KelolaDokumen\PengajuanController::approve');
$routes->get('document-submission-list/view-file/(:num)', 'KelolaDokumen\PengajuanController::viewFile/$1');
$routes->get('document-submission-list/download-file/(:num)', 'KelolaDokumen\PengajuanController::downloadFile/$1');
$routes->get('document-submission-list/get-history/(:num)', 'KelolaDokumen\PengajuanController::getHistory/$1');

//document approval
$routes->get('document-approval', 'KelolaDokumen\ControllerPersetujuan::index');
$routes->post('document-approval/update', 'KelolaDokumen\ControllerPersetujuan::update');
$routes->post('document-approval/delete', 'KelolaDokumen\ControllerPersetujuan::delete');
$routes->get('document-approval/serveFile', 'KelolaDokumen\ControllerPersetujuan::serveFile');

//document-list
$routes->get('document-list', 'DaftarDokumen\ControllerDaftarDokumen::index');
$routes->post('document-list/delete', 'DaftarDokumen\ControllerDaftarDokumen::delete');
$routes->post('document-list/update', 'DaftarDokumen\ControllerDaftarDokumen::updateDokumen');
$routes->get('document-list/serveFile', 'DaftarDokumen\ControllerDaftarDokumen::serveFile');



//notif cipa
$routes->post('notification/markAsRead', 'NotificationController::markAsRead');
$routes->get('notification/fetch', 'NotificationController::fetch');
$routes->post('notification/fetch', 'NotificationController::fetch'); // Support POST juga
$routes->get('notification/test', 'NotificationController::testNotification'); // Temporary untuk testing




//=====================================================================================================
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
$routes->get('data-master', 'MasterData\UnitController::index');
$routes->get('lihat-unit', 'MasterData\UnitController::list');
$routes->get('tambah-unit', 'MasterData\UnitController::create');
$routes->post('data-master/unit/store', 'MasterData\UnitController::store');
$routes->get('data-master/unit/(:num)/edit', 'MasterData\UnitController::edit/$1');
$routes->post('data-master/unit/(:num)/update', 'MasterData\UnitController::update/$1');
$routes->post('data-master/unit/(:num)/delete', 'MasterData\UnitController::delete/$1');
$routes->get('data-master/unit/list', 'MasterData\UnitController::list');



//sutra 1507
// Fakultas
// Routes untuk FakultasController (MasterData)
// Fakultas - MasterData
$routes->get('data-master/fakultas/create', 'MasterData\FakultasController::create');
$routes->get('tambah-fakultas', 'MasterData\FakultasController::create'); // alias
$routes->post('data-master/fakultas/store', 'MasterData\FakultasController::store');
$routes->get('data-master/fakultas/list', 'MasterData\FakultasController::index');
$routes->get('lihat-fakultas', 'MasterData\FakultasController::index'); // alias
$routes->post('data-master/fakultas/delete/(:num)', 'MasterData\FakultasController::delete/$1');
$routes->post('data-master/fakultas/update/(:num)', 'MasterData\FakultasController::update/$1');
// Soft delete (ubah status atau flag deleted)

$routes->post('tambah-fakultas/store', 'MasterData\FakultasController::store');
$routes->post('tambah-fakultas/delete/(:num)', 'MasterData\FakultasController::delete/$1');


// KelolaDokumen
$routes->get('tambah-dokumen', 'KelolaDokumen::add');
$routes->get('daftar-pengajuan', 'KelolaDokumen::pengajuan');
$routes->get('dokumen/config-jenis-dokumen', 'KelolaDokumen::configJenisDokumen');


$routes->get('kelola-dokumen/configJenisDokumen', 'KelolaDokumen::configJenisDokumen');
$routes->get('jenis-kode-dokumen', 'KelolaDokumen::configJenisDokumen');

$routes->get('dokumen/config-kategori', 'KelolaDokumen::configJenisDokumen');
$routes->post('/kelola-dokumen/get-kode-by-jenis', 'KelolaDokumen::getKodeByJenis');
$routes->post('admin/dokumen/delete-kode', 'KelolaDokumen::delete_kode');

// DaftarDokumen
$routes->get('dokumen/daftar', 'DaftarDokumen::index');

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
$routes->get('tambah-role', 'Role::create');              // Menampilkan form tambah role
$routes->post('role/store', 'Role::store');              // Menyimpan role baru ke database
$routes->get('lihat-role', 'Role::list');                // Menampilkan daftar role (tidak dihapus)
$routes->post('role/delete/(:num)', 'Role::delete/$1'); // "Menghapus" role (soft delete), param $1 = id
$routes->post('role/update/(:num)', 'Role::update/$1'); // Mengupdate role, param $1 = id


//privilege
$routes->get('tambah-privilege', 'Privilege::create');          // Form tambah privilege
$routes->post('privilege/store', 'Privilege::store');    // Proses simpan
$routes->get('lihat-privilege', 'Privilege::list');       // Lihat daftar privilege


//Menu
//Cipa menu
$routes->get('Menu', 'Menu::index');
$routes->get('tambah-menu', 'Menu::create');
$routes->post('Menu/store', 'Menu::store');
$routes->get('Menu/list', 'Menu::list');
$routes->get('lihat-menu', 'Menu::list');
$routes->get('Menu', 'Menu::index');
$routes->get('Menu/create', 'Menu::create');
$routes->post('Menu/store', 'Menu::store');
$routes->post('Menu/update/(:num)', 'Menu::update/$1');
$routes->post('Menu/delete/(:num)', 'Menu::delete/$1'); 


// submenu
$routes->get('tambah-submenu', 'SubmenuController::create');
$routes->post('submenu/store', 'SubmenuController::store'); //  untuk submit form create
$routes->get('lihat-submenu', 'SubmenuController::list'); //  list submenu

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


$routes->get('persetujuan-dokumen', 'KelolaDokumen\ControllerPersetujuan::index');
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
// $routes->get('generatetoken', 'Dummy\TokenDummy::generateAllTokens');
// $routes->get('parse-token', 'Dummy\TokenDummy::parseToken');

// $routes->get('wc-dummy', 'Dummy\DummyWCController::index');
// $routes->post('wc-dummy/login', 'Dummy\DummyWCController::redirectToDMS');

// $routes->get('dashboard', 'Home::index', ['filter' => 'auth']);


//naya 1407
$routes->post('privilege/update', 'Privilege::update');



$routes->get('/wc-dummy', 'Dummy\TokenDummy::index');
$routes->post('/wc-dummy/login', 'Dummy\TokenDummy::login');
$routes->get('/parse-token', 'Dummy\TokenDummy::parseToken');
$routes->get('/generateAllTokens', 'Dummy\TokenDummy::generateAllTokens'); // opsional

if (ENVIRONMENT === 'development') {
    $routes->get('/', fn() => redirect()->to('/wc-dummy'));
}


$routes->get('monev-dashboard', function () {
    $token = session('jwt_token'); // ngambil token dari session

    if (!$token) {
        return redirect()->to('/')->with('error', 'Token tidak tersedia.');
    }

    return redirect()->to('https://clear.celoe.org/?token=' . $token);
});

$routes->post('/api/decode-token', 'Dummy\TokenDummy::apiDecodeToken');


//23-07-2025
//nisrina
$routes->post('daftar-dokumen/update', 'DaftarDokumen\ControllerDaftarDokumen::update');
$routes->post('daftar-dokumen/delete/(:num)', 'DaftarDokumen\ControllerDaftarDokumen::delete/$1');

//cipa
//cipa file
$routes->get('kelola-dokumen/file/(:num)', 'KelolaDokumen::serveFile/$1');
$routes->get('kelola-dokumen/file/(:num)', 'KelolaDokumen::serveFile/$1');
$routes->get('kelola-dokumen/get-history/(:num)', 'KelolaDokumen::get_history/$1');
$routes->post('document-list/update', 'DaftarDokumen\ControllerDaftarDokumen::updateDokumen');
$routes->get('document-list/serveFile', 'DaftarDokumen\ControllerDaftarDokumen::serveFile');
// cipa
$routes->get('document-approval/serveFile', 'KelolaDokumen\ControllerPersetujuan::serveFile');
$routes->get('document-list/serveFile', 'DaftarDokumen\ControllerDaftarDokumen::serveFile');
$routes->post('document-list/update', 'DaftarDokumen\ControllerDaftarDokumen::update');