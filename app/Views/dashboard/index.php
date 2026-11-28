<?= $this->extend('layouts/main') ?>

<?= $this->section('title') ?>Dashboard<?= $this->endSection() ?>
<?= $this->section('page_title') ?>Dashboard<?= $this->endSection() ?>
<?= $this->section('page_subtitle') ?>Ringkasan Master Data Sistem<?= $this->endSection() ?>

<?= $this->section('breadcrumb') ?>
<li><a href="<?= site_url('dashboard') ?>"><i class="fa fa-dashboard"></i> Home</a></li>
<li class="active">Dashboard</li>
<?= $this->endSection() ?>

<?= $this->section('styles') ?>
<!-- Panggil CSS kustom Anda -->
<link rel="stylesheet" href="<?= base_url('assets/css/dashboard.css') ?>">
<style>
    /* Sedikit penyesuaian agar card sejajar sempurna di AdminLTE */
    .row-flex { display: flex; flex-wrap: wrap; }
    .row-flex > [class*='col-'] { display: flex; flex-direction: column; }
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<!-- ============================================== -->
<!-- WELCOME BANNER -->
<!-- ============================================== -->
<div class="welcome-banner">
    <span class="wb-badge">
        <i class="fa fa-circle" style="font-size:7px; color:#4ade80; margin-right:4px;"></i>
        Sistem Online & Beroperasi
    </span>
    <h2>
        Selamat Datang, <?= esc(session()->get('name') ?? 'Admin') ?> 👋
    </h2>
    <p>
        Ini adalah pusat kendali data master Anda. Kelola data kontak, template, dan kategori. Untuk melihat status pengiriman atau membuat kampanye, silakan buka menu <strong>Kirim Email</strong>.
    </p>
    <i class="fa fa-database banner-icon"></i>
</div>

<!-- ============================================== -->
<!-- MASTER DATA STAT CARDS -->
<!-- ============================================== -->
<div class="row row-flex gap-tight" style="margin-top: 20px; margin-bottom: 25px;">
    <div class="col-lg-3 col-sm-6">
        <a href="<?= site_url('kontak') ?>" class="stat-card stat-aqua">
            <div class="stat-icon"><i class="fa fa-users"></i></div>
            <div>
                <div class="stat-label">Total Kontak</div>
                <div class="stat-value"><?= number_format($totalKontak ?? 0) ?></div>
            </div>
            <div class="stat-footer">Kelola Data <i class="fa fa-arrow-right"></i></div>
        </a>
    </div>

    <div class="col-lg-3 col-sm-6">
        <a href="<?= site_url('template-email') ?>" class="stat-card stat-purple">
            <div class="stat-icon"><i class="fa fa-file-text-o"></i></div>
            <div>
                <div class="stat-label">Template Email</div>
                <div class="stat-value"><?= number_format($totalTemplate ?? 0) ?></div>
            </div>
            <div class="stat-footer">Kelola Template <i class="fa fa-arrow-right"></i></div>
        </a>
    </div>

    <div class="col-lg-3 col-sm-6">
        <a href="<?= site_url('kategori-template-email') ?>" class="stat-card stat-yellow">
            <div class="stat-icon"><i class="fa fa-tags"></i></div>
            <div>
                <div class="stat-label">Kategori</div>
                <div class="stat-value"><?= number_format($totalKategori ?? 0) ?></div>
            </div>
            <div class="stat-footer">Lihat Kategori <i class="fa fa-arrow-right"></i></div>
        </a>
    </div>

    <div class="col-lg-3 col-sm-6">
        <a href="<?= site_url('kirim-email') ?>" class="stat-card stat-green">
            <div class="stat-icon"><i class="fa fa-bullhorn"></i></div>
            <div>
                <div class="stat-label">Total Kampanye</div>
                <div class="stat-value"><?= number_format($totalKampanye ?? 0) ?></div>
            </div>
            <div class="stat-footer">Lihat Kampanye <i class="fa fa-arrow-right"></i></div>
        </a>
    </div>
</div>

<!-- ============================================== -->
<!-- ROW: QUICK ACTION & SYSTEM STATUS -->
<!-- ============================================== -->
<div class="row gap-tight">
    
    <!-- KOLOM KIRI: QUICK ACTION -->
    <div class="col-md-8">
        <div class="panel quick-action" style="height: 100%; margin-bottom: 20px;">
            <h3 class="panel-title">
                <span class="pt-icon"><i class="fa fa-bolt"></i></span>
                Aksi Cepat
            </h3>

            <div class="row row-flex">
                <div class="col-md-6" style="margin-bottom: 15px;">
                    <a href="<?= site_url('kirim-email') ?>" class="qa-btn qa-green" style="margin-bottom: 0; height: 100%;">
                        <span class="qa-icon"><i class="fa fa-paper-plane"></i></span>
                        <span class="qa-text">
                            Buka Menu Kirim Email
                            <small>Buat kampanye & pantau status pengiriman</small>
                        </span>
                        <i class="fa fa-chevron-right qa-arrow"></i>
                    </a>
                </div>

                <div class="col-md-6" style="margin-bottom: 15px;">
                    <a href="<?= site_url('template-email') ?>" class="qa-btn qa-blue" style="margin-bottom: 0; height: 100%;">
                        <span class="qa-icon"><i class="fa fa-file-text-o"></i></span>
                        <span class="qa-text">
                            Kelola Template Email
                            <small>Atur template yang tersedia</small>
                        </span>
                        <i class="fa fa-chevron-right qa-arrow"></i>
                    </a>
                </div>
                
                <div class="col-md-6" style="margin-bottom: 15px;">
                    <a href="<?= site_url('kontak') ?>" class="qa-btn qa-aqua" style="margin-bottom: 0; height: 100%;">
                        <span class="qa-icon"><i class="fa fa-user-plus"></i></span>
                        <span class="qa-text">
                            Tambah Kontak
                            <small>Tambah penerima email baru</small>
                        </span>
                        <i class="fa fa-chevron-right qa-arrow"></i>
                    </a>
                </div>

                <div class="col-md-6" style="margin-bottom: 15px;">
                    <a href="<?= site_url('kategori-template-email') ?>" class="qa-btn qa-orange" style="margin-bottom: 0; height: 100%;">
                        <span class="qa-icon"><i class="fa fa-tags"></i></span>
                        <span class="qa-text">
                            Kategori Template
                            <small>Kelola pengelompokan template</small>
                        </span>
                        <i class="fa fa-chevron-right qa-arrow"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- KOLOM KANAN: SYSTEM STATUS (Memanfaatkan CSS Anda) -->
    <div class="col-md-4">
        <div class="panel" style="height: 100%; margin-bottom: 20px;">
            <h3 class="panel-title">
                <span class="pt-icon"><i class="fa fa-server"></i></span>
                Status Sistem
            </h3>
            
            <div class="sys-progress">
                <div class="sp-head">
                    <span>Kapasitas Pengiriman</span>
                    <b>Normal</b>
                </div>
                <div class="sp-bar">
                    <div class="sp-green" style="width: 75%;"></div>
                </div>
            </div>

            <div class="sys-progress">
                <div class="sp-head">
                    <span>Server SMTP</span>
                    <b>Aktif</b>
                </div>
                <div class="sp-bar">
                    <div class="sp-aqua" style="width: 100%;"></div>
                </div>
            </div>

            <div class="sys-progress">
                <div class="sp-head">
                    <span>Penyimpanan Log</span>
                    <b>32% Terpakai</b>
                </div>
                <div class="sp-bar">
                    <div class="sp-yellow" style="width: 32%;"></div>
                </div>
            </div>

            <div class="sys-status-footer">
                <span class="dot-live"></span>
                <span class="status-text">Semua Layanan Berjalan Baik</span>
                <span class="status-time">Terakhir diperbarui: <?= date('d M Y, H:i') ?> WIB</span>
            </div>
        </div>
    </div>

</div>

<?= $this->endSection() ?>