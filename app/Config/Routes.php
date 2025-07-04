<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Home::index');

// CreateUser
// CreateUser
$routes->get('create-user', 'CreateUser::index');       // GET: /create-user
$routes->get('create-user/list', 'CreateUser::list');   // GET: /create-user/list
$routes->get('create-user/create', 'CreateUser::create'); // GET: /create-user/create
$routes->get('CreateUser/create', 'CreateUser::create'); // GET: /CreateUser/create (beda kapital, tapi boleh kalau memang mau support dua-duanya)
$routes->post('CreateUser/store', 'CreateUser::store'); // POST: /CreateUser/store
$routes->get('CreateUser/getUnits/(:num)', 'CreateUser::getUnits/$1');

// DataMaster
$routes->get('data-master', 'DataMaster::index'); //daftar unit
$routes->get('data-master/list', 'DataMaster::list'); // alias (opsional)
$routes->get('data-master/create', 'DataMaster::create'); // form Tambah Unit
$routes->post('data-master/store',  'DataMaster::store');   // proses submit
$routes->get ('data-master/unit/(:num)/edit',    'DataMaster::edit/$1');    // form Edit Unit
$routes->post('data-master/unit/(:num)/update',  'DataMaster::update/$1');  // simpan perubahan
$routes->post('data-master/unit/(:num)/delete',  'DataMaster::delete/$1');  // hapus unit
$routes->get('data-master/export/csv',   'DataMaster::exportCsv');
$routes->get('data-master/export/excel', 'DataMaster::exportExcel');
$routes->get('data-master/export/pdf',   'DataMaster::exportPdf');
$routes->get('data-master/export/print', 'DataMaster::exportPrint');


// KelolaDokumen 
$routes->get('dokumen/add', 'KelolaDokumen::add');
$routes->get('dokumen/pengajuan', 'KelolaDokumen::pengajuan');
$routes->get('dokumen/config-jenis-dokumen', 'KelolaDokumen::configJenisDokumen');

// DaftarDokumen 
$routes->get('dokumen/daftar', 'DaftarDokumen::index');

// PersetujuanDokumen 
$routes->get('dokumen/persetujuan', 'PersetujuanDokumen::index');

// CRUD routes for categories
$routes->post('dokumen/add-kategori', 'KelolaDokumen::addKategori');
$routes->post('dokumen/edit-kategori', 'KelolaDokumen::editKategori');
$routes->post('dokumen/delete-kategori', 'KelolaDokumen::deleteKategori');

// CRUD routes for codes
$routes->post('dokumen/add-kode', 'KelolaDokumen::addKode');
$routes->post('dokumen/edit-kode', 'KelolaDokumen::editKode');
$routes->post('dokumen/delete-kode', 'KelolaDokumen::deleteKode');