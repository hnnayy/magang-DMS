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

// DataMaster
$routes->get('data-master', 'DataMaster::index');
$routes->get('data-master/list', 'DataMaster::list');
$routes->get('data-master/create', 'DataMaster::create');

// KelolaDokumen 
$routes->get('dokumen/add', 'KelolaDokumen::add');
$routes->get('dokumen/pengajuan', 'KelolaDokumen::pengajuan');

// DaftarDokumen 
$routes->get('dokumen/daftar', 'DaftarDokumen::index');

// PersetujuanDokumen 
$routes->get('dokumen/persetujuan', 'PersetujuanDokumen::index');