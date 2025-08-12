<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */


// // Alur dummy untuk login
$routes->get('/', 'Dummy\LoginController::index');
$routes->get('/wc-dummy', 'Dummy\LoginController::index');
$routes->post('/login', 'Dummy\GenerateTokenController::login');
$routes->post('/wc-dummy/login', 'Dummy\GenerateTokenController::login');
$routes->get('/parse-token', 'Dummy\GenerateTokenController::parseToken');
$routes->get('/logout', 'Dummy\GenerateTokenController::logout');

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

// Document Standards 
$routes->get('document-standards', 'MasterData\StandarController::index');
$routes->post('document-standards/store', 'MasterData\StandarController::store');
$routes->post('document-standards/edit', 'MasterData\StandarController::edit');
$routes->post('document-standards/update', 'MasterData\StandarController::update');
$routes->post('document-standards/delete', 'MasterData\StandarController::delete');