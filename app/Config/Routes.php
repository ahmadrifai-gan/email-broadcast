<?php

use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */
$routes->get('/',        'DashboardController::index');
$routes->group('kategori-template-email', function ($routes) {
    $routes->get('/',            'KategoriController::index');
    $routes->post('store',       'KategoriController::store');
    $routes->post('update/(:num)','KategoriController::update/$1');
    $routes->post('delete/(:num)','KategoriController::delete/$1');
});

$routes->group('kontak', function ($routes) {
    $routes->get('/', 'KontakController::index');
    $routes->post('store', 'KontakController::store');
    $routes->post('update/(:num)', 'KontakController::update/$1');
    $routes->post('delete/(:num)', 'KontakController::delete/$1');
    $routes->post('import', 'KontakController::import');
});

$routes->get('template-email', 'TemplateController::index');
$routes->get('template-email/tambah', 'TemplateController::tambah');
$routes->get('template-email/edit/(:num)', 'TemplateController::edit/$1');