<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Home::index');

// CreateUser
$routes->get('create-user', 'CreateUser::index');
$routes->get('create-user/list', 'CreateUser::list');
$routes->get('create-user/create', to: 'CreateUser::create');
$routes->get('create-user/create', to: 'CreateUser::edit');


// DataMaster
$routes->get('data-master', 'DataMaster::index');
$routes->get('data-master/list', 'DataMaster::list');
$routes->get('data-master/create', 'DataMaster::create');

// KelolaDokumen 
$routes->get('dokumen/add', 'KelolaDokumen::add');
$routes->get('dokumen/pengajuan', 'KelolaDokumen::pengajuan');
$routes->get('dokumen/edit', 'KelolaDokumen::edit');


// DaftarDokumen 
$routes->get('dokumen/daftar', 'DaftarDokumen::index');

// PersetujuanDokumen 
$routes->get('dokumen/persetujuan', 'PersetujuanDokumen::index');


