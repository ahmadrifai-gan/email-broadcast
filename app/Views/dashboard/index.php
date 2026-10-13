<?= $this->extend('layouts/main') ?>

<?= $this->section('title') ?>Dashboard<?= $this->endSection() ?>
<?= $this->section('page_title') ?>Dashboard<?= $this->endSection() ?>
<?= $this->section('page_subtitle') ?>Ringkasan Aktivitas Email Blast<?= $this->endSection() ?>

<?= $this->section('breadcrumb') ?>
<li>
    <a href="<?= site_url('dashboard') ?>">
        <i class="fa fa-dashboard"></i> Home
    </a>
</li>
<li class="active">Dashboard</li>
<?= $this->endSection() ?>

<?= $this->section('styles') ?>
<style>
    /* Welcome banner */
    .welcome-banner {
        background: linear-gradient(135deg, #00a65a 0%, #007d43 100%);
        color: #fff;
        padding: 25px 30px;
        border-radius: 4px;
        margin-bottom: 20px;
        position: relative;
        overflow: hidden;
        box-shadow: 0 2px 6px rgba(0,0,0,.1);
    }
    .welcome-banner h2 {
        margin: 0 0 6px 0;
        font-weight: 600;
        color: #fff;
    }
    .welcome-banner p {
        margin: 0;
        opacity: .9;
        font-size: 14px;
    }
    .welcome-banner .banner-icon {
        position: absolute;
        right: 25px;
        top: 50%;
        transform: translateY(-50%);
        font-size: 110px;
        opacity: .12;
        color: #fff;
    }

    /* Small box lebih rapi */
    .small-box {
        border-radius: 4px;
        box-shadow: 0 1px 3px rgba(0,0,0,.08);
        transition: all .2s;
    }
    .small-box:hover {
        transform: translateY(-3px);
        box-shadow: 0 6px 12px rgba(0,0,0,.12);
    }

    /* Quick action */
    .quick-action .btn {
        width: 100%;
        text-align: left;
        padding: 12px 15px;
        margin-bottom: 8px;
        font-weight: 500;
        border-radius: 4px;
    }
    .quick-action .btn i {
        margin-right: 8px;
        width: 18px;
        text-align: center;
    }

    /* Info box progress */
    .info-box .progress {
        height: 4px;
        margin: 6px 0 0 0;
        background: rgba(0,0,0,.08);
    }
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<!-- ============================================== -->
<!-- WELCOME BANNER -->
<!-- ============================================== -->
<div class="welcome-banner">
    <h2>
        <i class="fa fa-hand-peace-o"></i>
        Selamat Datang, <?= esc(session()->get('name') ?? 'Guest') ?>!
    </h2>
    <p>
        Dashboard <strong>Email Blast</strong> — kelola kontak, template, dan pengiriman email dalam satu tempat.
    </p>
    <i class="fa fa-paper-plane banner-icon"></i>
</div>

<!-- ============================================== -->
<!-- SMALL BOXES -->
<!-- ============================================== -->
<div class="row">

    <div class="col-lg-3 col-xs-6">
        <div class="small-box bg-aqua">
            <div class="inner">
                <h3><?= number_format($totalKontak ?? 0) ?></h3>
                <p>Total Kontak</p>
            </div>
            <div class="icon">
                <i class="fa fa-users"></i>
            </div>
            <a href="<?= site_url('kontak') ?>" class="small-box-footer">
                Lihat Data <i class="fa fa-arrow-circle-right"></i>
            </a>
        </div>
    </div>

    <div class="col-lg-3 col-xs-6">
        <div class="small-box bg-green">
            <div class="inner">
                <h3><?= number_format($totalTerkirim ?? 0) ?></h3>
                <p>Email Terkirim</p>
            </div>
            <div class="icon">
                <i class="fa fa-paper-plane"></i>
            </div>
            <a href="<?= site_url('riwayat-pengiriman') ?>" class="small-box-footer">
                Lihat Riwayat <i class="fa fa-arrow-circle-right"></i>
            </a>
        </div>
    </div>

    <div class="col-lg-3 col-xs-6">
        <div class="small-box bg-yellow">
            <div class="inner">
                <h3><?= number_format($totalTemplate ?? 0) ?></h3>
                <p>Template Email</p>
            </div>
            <div class="icon">
                <i class="fa fa-file-text-o"></i>
            </div>
            <a href="<?= site_url('template-email') ?>" class="small-box-footer">
                Kelola Template <i class="fa fa-arrow-circle-right"></i>
            </a>
        </div>
    </div>

    <div class="col-lg-3 col-xs-6">
        <div class="small-box bg-purple">
            <div class="inner">
                <h3><?= number_format($totalKategori ?? 0) ?></h3>
                <p>Kategori Template</p>
            </div>
            <div class="icon">
                <i class="fa fa-tags"></i>
            </div>
            <a href="<?= site_url('kategori-template-email') ?>" class="small-box-footer">
                Lihat Kategori <i class="fa fa-arrow-circle-right"></i>
            </a>
        </div>
    </div>

</div>

<div class="row">

    <!-- ============================================== -->
    <!-- QUICK ACTION -->
    <!-- ============================================== -->
    <div class="col-md-4">

        <div class="box box-primary">
            <div class="box-header with-border">
                <h3 class="box-title">
                    <i class="fa fa-bolt"></i> Aksi Cepat
                </h3>
            </div>
            <div class="box-body quick-action">

                <a href="<?= site_url('kirim-email') ?>" class="btn btn-success">
                    <i class="fa fa-paper-plane"></i> Kirim Email Baru
                </a>

                <a href="<?= site_url('template-email') ?>" class="btn btn-primary">
                    <i class="fa fa-file-text-o"></i> Kelola Template Email
                </a>

                <a href="<?= site_url('kontak') ?>" class="btn btn-info">
                    <i class="fa fa-user-plus"></i> Tambah Kontak
                </a>

                <a href="<?= site_url('riwayat-pengiriman') ?>" class="btn btn-warning">
                    <i class="fa fa-history"></i> Lihat Riwayat
                </a>

            </div>
        </div>

    </div>

    <!-- ============================================== -->
    <!-- INFO BOX + PROGRESS -->
    <!-- ============================================== -->
    <div class="col-md-8">

        <div class="row">

            <div class="col-md-6">
                <div class="info-box">
                    <span class="info-box-icon bg-aqua">
                        <i class="fa fa-envelope-o"></i>
                    </span>
                    <div class="info-box-content">
                        <span class="info-box-text">Email Bulan Ini</span>
                        <span class="info-box-number"><?= number_format($emailBulanIni ?? 0) ?></span>
                        <div class="progress">
                            <div class="progress-bar bg-aqua"
                                 style="width: <?= min(100, ($emailBulanIni ?? 0)) ?>%"></div>
                        </div>
                        <span class="progress-description">
                            Target: <?= number_format($targetBulanan ?? 100) ?> email
                        </span>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="info-box">
                    <span class="info-box-icon bg-green">
                        <i class="fa fa-check-circle-o"></i>
                    </span>
                    <div class="info-box-content">
                        <span class="info-box-text">Berhasil Terkirim</span>
                        <span class="info-box-number"><?= number_format($totalBerhasil ?? 0) ?></span>
                        <div class="progress">
                            <div class="progress-bar bg-green"
                                 style="width: <?= min(100, ($totalBerhasil ?? 0)) ?>%"></div>
                        </div>
                        <span class="progress-description">
                            Dari total pengiriman
                        </span>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="info-box">
                    <span class="info-box-icon bg-yellow">
                        <i class="fa fa-clock-o"></i>
                    </span>
                    <div class="info-box-content">
                        <span class="info-box-text">Menunggu Kirim</span>
                        <span class="info-box-number"><?= number_format($totalMenunggu ?? 0) ?></span>
                        <div class="progress">
                            <div class="progress-bar bg-yellow"
                                 style="width: <?= min(100, ($totalMenunggu ?? 0)) ?>%"></div>
                        </div>
                        <span class="progress-description">
                            Dalam antrean
                        </span>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="info-box">
                    <span class="info-box-icon bg-red">
                        <i class="fa fa-times-circle-o"></i>
                    </span>
                    <div class="info-box-content">
                        <span class="info-box-text">Gagal Kirim</span>
                        <span class="info-box-number"><?= number_format($totalGagal ?? 0) ?></span>
                        <div class="progress">
                            <div class="progress-bar bg-red"
                                 style="width: <?= min(100, ($totalGagal ?? 0)) ?>%"></div>
                        </div>
                        <span class="progress-description">
                            Perlu ditinjau ulang
                        </span>
                    </div>
                </div>
            </div>

        </div>

    </div>

</div>

<!-- ============================================== -->
<!-- AKTIVITAS TERBARU -->
<!-- ============================================== -->
<div class="row">

    <div class="col-md-8">

        <div class="box box-success">
            <div class="box-header with-border">
                <h3 class="box-title">
                    <i class="fa fa-history"></i> Pengiriman Terbaru
                </h3>
                <div class="box-tools pull-right">
                    <a href="<?= site_url('riwayat-pengiriman') ?>" class="btn btn-box-tool">
                        <i class="fa fa-external-link"></i> Lihat Semua
                    </a>
                </div>
            </div>

            <div class="box-body no-padding">
                <table class="table table-striped">
                    <thead>
                    <tr>
                        <th width="40">#</th>
                        <th>Penerima</th>
                        <th>Template</th>
                        <th width="120">Tanggal</th>
                        <th width="90">Status</th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr>
                        <td>1</td>
                        <td>info@contoh.co.id</td>
                        <td>Promo Bulan Ini</td>
                        <td>2025-01-03 14:20</td>
                        <td><span class="label label-success">Terkirim</span></td>
                    </tr>
                    <tr>
                        <td>2</td>
                        <td>contact@majubersama.com</td>
                        <td>Notifikasi Sistem</td>
                        <td>2025-01-03 10:05</td>
                        <td><span class="label label-success">Terkirim</span></td>
                    </tr>
                    <tr>
                        <td>3</td>
                        <td>halo@digitalnusantara.id</td>
                        <td>Promo Akhir Tahun</td>
                        <td>2025-01-02 16:45</td>
                        <td><span class="label label-warning">Menunggu</span></td>
                    </tr>
                    <tr>
                        <td>4</td>
                        <td>admin@situsku.com</td>
                        <td>Verifikasi Akun</td>
                        <td>2025-01-02 09:15</td>
                        <td><span class="label label-danger">Gagal</span></td>
                    </tr>
                    </tbody>
                </table>
            </div>
        </div>

    </div>

    <div class="col-md-4">

        <div class="box box-info">
            <div class="box-header with-border">
                <h3 class="box-title">
                    <i class="fa fa-info-circle"></i> Status Sistem
                </h3>
            </div>
            <div class="box-body">

                <div class="progress-group">
                    <span class="progress-text">Kapasitas Kontak</span>
                    <span class="progress-number">
                        <b><?= number_format($totalKontak ?? 0) ?></b> / 5.000
                    </span>
                    <div class="progress sm">
                        <div class="progress-bar progress-bar-aqua"
                             style="width: <?= min(100, (($totalKontak ?? 0) / 5000) * 100) ?>%"></div>
                    </div>
                </div>

                <div class="progress-group">
                    <span class="progress-text">Pengiriman Hari Ini</span>
                    <span class="progress-number">
                        <b><?= number_format($emailHariIni ?? 0) ?></b> / 500
                    </span>
                    <div class="progress sm">
                        <div class="progress-bar progress-bar-green"
                             style="width: <?= min(100, (($emailHariIni ?? 0) / 500) * 100) ?>%"></div>
                    </div>
                </div>

                <div class="progress-group">
                    <span class="progress-text">Template Aktif</span>
                    <span class="progress-number">
                        <b><?= number_format($totalTemplate ?? 0) ?></b>
                    </span>
                    <div class="progress sm">
                        <div class="progress-bar progress-bar-yellow"
                             style="width: 65%"></div>
                    </div>
                </div>

                <hr>

                <p class="text-muted text-center" style="margin:0;">
                    <i class="fa fa-circle text-success"></i>
                    Sistem berjalan normal
                    <br>
                    <small>Terakhir diperbarui: <?= date('d M Y H:i') ?></small>
                </p>

            </div>
        </div>

    </div>

</div>

<?= $this->endSection() ?>