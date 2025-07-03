<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Home::index');

// CreateUser
$routes->get('create-user', 'CreateUser::index');
$routes->get('create-user/list', 'CreateUser::list');
<<<<<<< HEAD
$routes->get('create-user/create', 'CreateUser::create');
$routes->get('CreateUser/create', 'CreateUser::create');
$routes->post('CreateUser/store', 'CreateUser::store');

$routes->get('KelolaDokumen/getUserUnitData', 'KelolaDokumen::getUserUnitData');
=======
$routes->get('create-user/create', to: 'CreateUser::create');
$routes->get('create-user/create', to: 'CreateUser::edit');
>>>>>>> 8a3a31be84a52a9674b4c649a5ebbadb4d36f9d6


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
