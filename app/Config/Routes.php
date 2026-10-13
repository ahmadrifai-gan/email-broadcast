<?php

use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */
$routes->get('/',        'DashboardController::index');
$routes->get('dashboard','DashboardController::index');
$routes->get('kategori-template-email','KategoriController::index');
$routes->group('kontak', function ($routes) {
    $routes->get('/', 'KontakController::index');
    $routes->post('store', 'KontakController::store');
    $routes->post('update/(:num)', 'KontakController::update/$1');
    $routes->post('delete/(:num)', 'KontakController::delete/$1');
});