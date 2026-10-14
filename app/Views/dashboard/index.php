<?= $this->extend('layouts/main') ?>

<?= $this->section('title') ?>Dashboard<?= $this->endSection() ?>
<?= $this->section('page_title') ?>Dashboard<?= $this->endSection() ?>
<?= $this->section('page_subtitle') ?>Ringkasan Aktivitas Email Blast<?= $this->endSection() ?>

<?= $this->section('breadcrumb') ?>
<li><a href="<?= site_url('dashboard') ?>"><i class="fa fa-dashboard"></i> Home</a></li>
<li class="active">Dashboard</li>
<?= $this->endSection() ?>

<?= $this->section('styles') ?>
<link rel="stylesheet" href="<?= base_url('assets/css/dashboard.css') ?>">
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<!-- ============================================== -->
<!-- WELCOME BANNER -->
<!-- ============================================== -->
<div class="welcome-banner">
    <span class="wb-badge">
        <i class="fa fa-circle" style="font-size:7px; color:#4ade80;"></i>
        Sistem Online & Beroperasi
    </span>
    <h2>
        Selamat Datang, <?= esc(session()->get('name') ?? 'Guest') ?> 👋
    </h2>
    <p>
        Kelola kampanye <strong>Email Blast</strong>, daftar kontak penerima, serta pantau laporan pengiriman secara *real-time* di sini.
    </p>
    <i class="fa fa-paper-plane banner-icon"></i>
</div>

<!-- ============================================== -->
<!-- STAT CARDS -->
<!-- ============================================== -->
<div class="row gap-tight">
    <div class="col-lg-3 col-sm-6">
        <a href="<?= site_url('kontak') ?>" class="stat-card stat-aqua">
            <div class="stat-icon"><i class="fa fa-users"></i></div>
            <div>
                <div class="stat-label">Total Kontak</div>
                <div class="stat-value"><?= number_format($totalKontak ?? 0) ?></div>
            </div>
            <div class="stat-footer">Lihat Data <i class="fa fa-arrow-right"></i></div>
        </a>
    </div>

    <div class="col-lg-3 col-sm-6">
        <a href="<?= site_url('riwayat-pengiriman') ?>" class="stat-card stat-green">
            <div class="stat-icon"><i class="fa fa-paper-plane"></i></div>
            <div>
                <div class="stat-label">Email Terkirim</div>
                <div class="stat-value"><?= number_format($totalTerkirim ?? 0) ?></div>
            </div>
            <div class="stat-footer">Lihat Riwayat <i class="fa fa-arrow-right"></i></div>
        </a>
    </div>

    <div class="col-lg-3 col-sm-6">
        <a href="<?= site_url('template-email') ?>" class="stat-card stat-yellow">
            <div class="stat-icon"><i class="fa fa-file-text-o"></i></div>
            <div>
                <div class="stat-label">Template Email</div>
                <div class="stat-value"><?= number_format($totalTemplate ?? 0) ?></div>
            </div>
            <div class="stat-footer">Kelola Template <i class="fa fa-arrow-right"></i></div>
        </a>
    </div>

    <div class="col-lg-3 col-sm-6">
        <a href="<?= site_url('kategori-template-email') ?>" class="stat-card stat-purple">
            <div class="stat-icon"><i class="fa fa-tags"></i></div>
            <div>
                <div class="stat-label">Kategori</div>
                <div class="stat-value"><?= number_format($totalKategori ?? 0) ?></div>
            </div>
            <div class="stat-footer">Lihat Kategori <i class="fa fa-arrow-right"></i></div>
        </a>
    </div>
</div>

<!-- ============================================== -->
<!-- QUICK ACTION + INFO CARDS -->
<!-- ============================================== -->
<div class="row gap-tight mt-28">

    <!-- Quick Action -->
    <div class="col-md-4">
        <div class="panel quick-action" style="height:100%;">
            <h3 class="panel-title">
                <span class="pt-icon"><i class="fa fa-bolt"></i></span>
                Aksi Cepat
            </h3>

            <a href="<?= site_url('kirim-email') ?>" class="qa-btn qa-green">
                <span class="qa-icon"><i class="fa fa-paper-plane"></i></span>
                <span class="qa-text">
                    Kirim Email Baru
                    <small>Buat &amp; kirim email langsung</small>
                </span>
                <i class="fa fa-chevron-right qa-arrow"></i>
            </a>

            <a href="<?= site_url('template-email') ?>" class="qa-btn qa-blue">
                <span class="qa-icon"><i class="fa fa-file-text-o"></i></span>
                <span class="qa-text">
                    Kelola Template Email
                    <small>Atur template yang tersedia</small>
                </span>
                <i class="fa fa-chevron-right qa-arrow"></i>
            </a>

            <a href="<?= site_url('kontak') ?>" class="qa-btn qa-aqua">
                <span class="qa-icon"><i class="fa fa-user-plus"></i></span>
                <span class="qa-text">
                    Tambah Kontak
                    <small>Tambah penerima email baru</small>
                </span>
                <i class="fa fa-chevron-right qa-arrow"></i>
            </a>

            <a href="<?= site_url('riwayat-pengiriman') ?>" class="qa-btn qa-orange">
                <span class="qa-icon"><i class="fa fa-history"></i></span>
                <span class="qa-text">
                    Lihat Riwayat
                    <small>Cek status pengiriman</small>
                </span>
                <i class="fa fa-chevron-right qa-arrow"></i>
            </a>
        </div>
    </div>

    <!-- Info Cards -->
    <div class="col-md-8">
        <div class="row gap-tight">

            <div class="col-sm-6">
                <div class="info-card ic-aqua">
                    <div class="ic-header">
                        <div class="ic-icon"><i class="fa fa-envelope-o"></i></div>
                        <div>
                            <p class="ic-title">Email Bulan Ini</p>
                            <div class="ic-value"><?= number_format($emailBulanIni ?? 0) ?></div>
                        </div>
                    </div>
                    <div class="ic-progress">
                        <div class="bar-aqua" style="width: <?= min(100, ($emailBulanIni ?? 0)) ?>%"></div>
                    </div>
                    <p class="ic-desc">
                        <i class="fa fa-bullseye"></i> Target: <?= number_format($targetBulanan ?? 100) ?> email
                    </p>
                </div>
            </div>

            <div class="col-sm-6">
                <div class="info-card ic-green">
                    <div class="ic-header">
                        <div class="ic-icon"><i class="fa fa-check-circle-o"></i></div>
                        <div>
                            <p class="ic-title">Berhasil Terkirim</p>
                            <div class="ic-value"><?= number_format($totalBerhasil ?? 0) ?></div>
                        </div>
                    </div>
                    <div class="ic-progress">
                        <div class="bar-green" style="width: <?= min(100, ($totalBerhasil ?? 0)) ?>%"></div>
                    </div>
                    <p class="ic-desc">
                        <i class="fa fa-check"></i> Dari total keseluruhan
                    </p>
                </div>
            </div>

            <div class="col-sm-6">
                <div class="info-card ic-yellow">
                    <div class="ic-header">
                        <div class="ic-icon"><i class="fa fa-clock-o"></i></div>
                        <div>
                            <p class="ic-title">Menunggu Kirim</p>
                            <div class="ic-value"><?= number_format($totalMenunggu ?? 0) ?></div>
                        </div>
                    </div>
                    <div class="ic-progress">
                        <div class="bar-yellow" style="width: <?= min(100, ($totalMenunggu ?? 0)) ?>%"></div>
                    </div>
                    <p class="ic-desc">
                        <i class="fa fa-hourglass-half"></i> Dalam antrean sistem
                    </p>
                </div>
            </div>

            <div class="col-sm-6">
                <div class="info-card ic-red">
                    <div class="ic-header">
                        <div class="ic-icon"><i class="fa fa-times-circle-o"></i></div>
                        <div>
                            <p class="ic-title">Gagal Kirim</p>
                            <div class="ic-value"><?= number_format($totalGagal ?? 0) ?></div>
                        </div>
                    </div>
                    <div class="ic-progress">
                        <div class="bar-red" style="width: <?= min(100, ($totalGagal ?? 0)) ?>%"></div>
                    </div>
                    <p class="ic-desc">
                        <i class="fa fa-exclamation-circle"></i> Perlu ditinjau ulang
                    </p>
                </div>
            </div>

        </div>
    </div>

</div>

<!-- ============================================== -->
<!-- PENGIRIMAN TERBARU + STATUS SISTEM -->
<!-- ============================================== -->
<div class="row gap-tight">

    <!-- Riwayat Pengiriman -->
    <div class="col-md-8">
        <div class="panel">
            <h3 class="panel-title">
                <span class="pt-icon"><i class="fa fa-history"></i></span>
                Pengiriman Terbaru
                <a href="<?= site_url('riwayat-pengiriman') ?>" class="pt-link">
                    Lihat Semua <i class="fa fa-arrow-right"></i>
                </a>
            </h3>

            <div class="table-wrapper">
                <table class="modern-table">
                    <thead>
                        <tr>
                            <th width="50">#</th>
                            <th>Penerima</th>
                            <th>Template</th>
                            <th width="150">Tanggal</th>
                            <th width="120">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>01</td>
                            <td>info@contoh.co.id</td>
                            <td>Promo Bulan Ini</td>
                            <td>2025-01-03 14:20</td>
                            <td><span class="badge-soft success">Terkirim</span></td>
                        </tr>
                        <tr>
                            <td>02</td>
                            <td>contact@majubersama.com</td>
                            <td>Notifikasi Sistem</td>
                            <td>2025-01-03 10:05</td>
                            <td><span class="badge-soft success">Terkirim</span></td>
                        </tr>
                        <tr>
                            <td>03</td>
                            <td>halo@digitalnusantara.id</td>
                            <td>Promo Akhir Tahun</td>
                            <td>2025-01-02 16:45</td>
                            <td><span class="badge-soft warning">Menunggu</span></td>
                        </tr>
                        <tr>
                            <td>04</td>
                            <td>admin@situsku.com</td>
                            <td>Verifikasi Akun</td>
                            <td>2025-01-02 09:15</td>
                            <td><span class="badge-soft danger">Gagal</span></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Status Sistem -->
    <div class="col-md-4">
        <div class="panel" style="height: calc(100% - 28px);">
            <h3 class="panel-title">
                <span class="pt-icon"><i class="fa fa-info-circle"></i></span>
                Status Sistem
            </h3>

            <div class="sys-progress">
                <div class="sp-head">
                    <span>Kapasitas Kontak</span>
                    <span><b><?= number_format($totalKontak ?? 0) ?></b> / 5.000</span>
                </div>
                <div class="sp-bar">
                    <div class="sp-aqua" style="width: <?= min(100, (($totalKontak ?? 0) / 5000) * 100) ?>%"></div>
                </div>
            </div>

            <div class="sys-progress">
                <div class="sp-head">
                    <span>Pengiriman Hari Ini</span>
                    <span><b><?= number_format($emailHariIni ?? 0) ?></b> / 500</span>
                </div>
                <div class="sp-bar">
                    <div class="sp-green" style="width: <?= min(100, (($emailHariIni ?? 0) / 500) * 100) ?>%"></div>
                </div>
            </div>

            <div class="sys-progress">
                <div class="sp-head">
                    <span>Template Aktif</span>
                    <span><b><?= number_format($totalTemplate ?? 0) ?></b></span>
                </div>
                <div class="sp-bar">
                    <div class="sp-yellow" style="width: 65%"></div>
                </div>
            </div>

            <div class="sys-status-footer">
                <span class="dot-live"></span>
                <span class="status-text">Sistem berjalan normal</span>
                <span class="status-time">
                    <i class="fa fa-refresh"></i>
                    Terakhir diperbarui: <?= date('d M Y H:i') ?>
                </span>
            </div>
        </div>
    </div>

</div>

<?= $this->endSection() ?>