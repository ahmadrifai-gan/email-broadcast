<?php

use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */
$routes->get('/', 'DashboardController::index');

$routes->group('kategori-template-email', function ($routes) {
    $routes->get('/',              'KategoriController::index');
    $routes->post('store',         'KategoriController::store');
    $routes->post('update/(:num)', 'KategoriController::update/$1');
    $routes->post('delete/(:num)', 'KategoriController::delete/$1');
});

// Kontak
$routes->group('kontak', static function ($routes) {
    $routes->get('/', 'KontakController::index');
    $routes->post('simpan', 'KontakController::simpan');
    $routes->post('perbarui/(:num)', 'KontakController::perbarui/$1');
    $routes->post('hapus/(:num)', 'KontakController::hapus/$1');

    // Import Excel
    $routes->get('template', 'KontakController::template');
    $routes->post('import', 'KontakController::import');
});

$routes->group('template-email', ['namespace' => 'App\Controllers'], function ($routes) {
    $routes->get('/',              'TemplateController::index');
    $routes->get('tambah',         'TemplateController::tambah');
    $routes->post('store',         'TemplateController::store');
    $routes->get('edit/(:num)',    'TemplateController::edit/$1');
    $routes->post('update/(:num)', 'TemplateController::update/$1');
    $routes->post('delete/(:num)', 'TemplateController::delete/$1');
    $routes->post('cek-spam',      'TemplateController::cekSpam');
    $routes->post('upload-gambar', 'TemplateController::uploadGambar');
});

$routes->group('kirim-email', function ($routes) {
    $routes->get('/',                   'KirimEmailController::index');
    $routes->get('buat',                'KirimEmailController::buat');
    $routes->post('simpan',             'KirimEmailController::simpan');
    $routes->get('detail/(:num)',       'KirimEmailController::detail/$1');
    $routes->post('proses/(:num)',      'KirimEmailController::proses/$1');
    $routes->post('proses-terjadwal',   'KirimEmailController::prosesTerjadwal');
    $routes->get('cron/(:segment)',     'KirimEmailController::cron/$1');   // dipanggil cron eksternal (GET)
    $routes->post('kirim-ulang/(:num)', 'KirimEmailController::kirimUlang/$1');
    $routes->post('hapus/(:num)',       'KirimEmailController::hapus/$1');
});

$routes->group('riwayat-pengiriman', function ($routes) {
    $routes->get('/', 'RiwayatPengirimanController::index');
});

$routes->group('kategori', static function ($routes) {
    $routes->get('/', 'KategoriKontakController::index');
    $routes->post('simpan', 'KategoriKontakController::simpan');
    $routes->post('perbarui/(:num)', 'KategoriKontakController::perbarui/$1');
    $routes->post('hapus/(:num)', 'KategoriKontakController::hapus/$1');
});