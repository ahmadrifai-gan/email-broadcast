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

//Kontak
$routes->group('kontak', static function ($routes) {
    // Kontak (CRUD + import Excel)
    $routes->get('/',                'KontakController::index');
    $routes->post('simpan',          'KontakController::simpan');
    $routes->post('perbarui/(:num)', 'KontakController::perbarui/$1');
    $routes->post('hapus/(:num)',    'KontakController::hapus/$1');
    $routes->post('import',          'KontakController::import');
    $routes->get('template',         'KontakController::template');

    // Perusahaan (detail, hapus massal, import ke supplier)
    $routes->get('detail/(:num)',         'KontakController::detail/$1');
    $routes->post('bulk-delete',          'KontakController::bulkDelete');
    $routes->get('import-preview',        'KontakController::importPreview');
    $routes->post('import-single/(:num)', 'KontakController::importSingle/$1');
    $routes->post('import-all',           'KontakController::importAll');
});

$routes->group('template-email', ['namespace' => 'App\Controllers'], function ($routes) {
    $routes->get('/',              'TemplateController::index');
    $routes->get('tambah',         'TemplateController::tambah');
    $routes->post('store',         'TemplateController::store');
    $routes->get('edit/(:num)',    'TemplateController::edit/$1');
    $routes->post('update/(:num)', 'TemplateController::update/$1');
    $routes->post('delete/(:num)', 'TemplateController::delete/$1');
    $routes->post('cek-spam-sistem', 'TemplateController::cekSpamSistem');
    $routes->post('cek-spam-ai',     'TemplateController::cekSpamAI');
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
    $routes->post('jeda/(:num)',        'KirimEmailController::jeda/$1');
    $routes->post('lanjut/(:num)',      'KirimEmailController::lanjut/$1');
    $routes->post('batal/(:num)',       'KirimEmailController::batal/$1');
    $routes->post('hapus/(:num)',       'KirimEmailController::hapus/$1');
});


$routes->get('smtp',                   'SmtpSettingsController::index');
$routes->get('smtp/create',            'SmtpSettingsController::create');
$routes->post('smtp',                  'SmtpSettingsController::store');
$routes->get('smtp/(:num)/edit',       'SmtpSettingsController::edit/$1');
$routes->post('smtp/(:num)',           'SmtpSettingsController::update/$1');
$routes->get('smtp/(:num)/activate',   'SmtpSettingsController::activate/$1');
$routes->get('smtp/(:num)/delete',     'SmtpSettingsController::delete/$1');
$routes->post('smtp/(:num)/test',      'SmtpSettingsController::test/$1');

$routes->group('riwayat-pengiriman', function ($routes) {
    $routes->get('/', 'RiwayatPengirimanController::index');
});

$routes->group('kategori', static function ($routes) {
    $routes->get('/', 'KategoriKontakController::index');
    $routes->post('simpan', 'KategoriKontakController::simpan');
    $routes->post('perbarui/(:num)', 'KategoriKontakController::perbarui/$1');
    $routes->post('hapus/(:num)', 'KategoriKontakController::hapus/$1');
});

$routes->get('laporan-smtp-kuota', 'LaporanSmtpController::index');
$routes->get('laporan-template', 'LaporanTemplateController::index');

// Rute untuk Laporan Ringkasan Kampanye
$routes->get('laporan/ringkasan-kampanye', 'RingkasanKampanyeController::ringkasanKampanye');
$routes->get('laporan/ringkasan-kampanye/export', 'RingkasanKampanyeController::exportRingkasanKampanye');

// Rute untuk Laporan Penerima & Kontak
$routes->get('laporan/penerima-kontak', 'PenerimaKontakController::penerimaKontak');
$routes->get('laporan/penerima-kontak/export/(:segment)', 'PenerimaKontakController::exportPenerimaKontak/$1');
$routes->group('scrapper', function ($routes) {
    $routes->get('/', 'Travel::index');
    $routes->post('scrape', 'Travel::scrape');
    $routes->get('edit-data', 'Travel::editData');
    $routes->get('edit-data/(:segment)', 'Travel::editDataKeyword/$1');
    $routes->get('edit/(:num)', 'Travel::edit/$1');
    $routes->post('update/(:num)', 'Travel::update/$1');
    $routes->get('delete/(:num)', 'Travel::delete/$1');
    $routes->get('export-excel/(:segment)', 'Travel::exportExcel/$1');
    
    // Fitur cek status
    $routes->post('check-website/(:num)', 'Travel::checkWebsite/$1');
    $routes->post('check-website-bulk', 'Travel::checkWebsiteBulk');
    $routes->post('check-wa/(:num)', 'Travel::checkWaNumber/$1');
    $routes->post('check-wa-bulk', 'Travel::checkWaNumberBulk');
    
    // Fitur import ke kontak
    $routes->get('import-ke-kontak', 'Travel::importKeKontak');
});

$routes->group('import-excel',  function ($routes) {
    $routes->get('/',         'ImportExcel::index');
    $routes->post('preview',  'ImportExcel::preview');
    $routes->post('process',  'ImportExcel::process');
    $routes->post('from-tei', 'ImportExcel::importFromTei');
});

$routes->group('pengaturan', function($routes) {

    $routes->get('/', 'PengaturanAI::index');

    $routes->post('save', 'PengaturanAI::save');

    $routes->post('test-connection', 'PengaturanAI::testConnection');

});

$routes->group('analisis-ai', function ($routes) {
    $routes->get('/', 'AnalisisAI::index');                 // halaman utama
    $routes->get('queue', 'AnalisisAI::getQueue');          // ambil antrean
    $routes->post('process-one', 'AnalisisAI::processOne'); // proses 1 perusahaan
});