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
