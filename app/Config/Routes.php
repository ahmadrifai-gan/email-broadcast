<?php

use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */
$routes->get('/',        'DashboardController::index');
$routes->get('dashboard','DashboardController::index');
$routes->get('kategori-template-email','KategoriController::index');
$routes->get('kontak', 'KontakController::index');