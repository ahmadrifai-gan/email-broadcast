<?php

use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */
$routes->get('/',        'DashboardController::index');
$routes->get('dashboard','DashboardController::index');