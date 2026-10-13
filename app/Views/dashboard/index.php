<?= $this->extend('layouts/main') ?>

<?= $this->section('breadcrumb') ?>
<li><a href="<?= site_url('dashboard') ?>"><i class="fa fa-dashboard"></i> Home</a></li>
<li class="active">Dashboard</li>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="row">
  <div class="col-md-3 col-sm-6 col-xs-12">
    <div class="info-box">
      <span class="info-box-icon bg-aqua"><i class="fa fa-users"></i></span>
      <div class="info-box-content">
        <span class="info-box-text">Total Kontak</span>
        <span class="info-box-number"><?= number_format($totalKontak ?? 0) ?></span>
      </div>
    </div>
  </div>
  <div class="col-md-3 col-sm-6 col-xs-12">
    <div class="info-box">
      <span class="info-box-icon bg-green"><i class="fa fa-paper-plane"></i></span>
      <div class="info-box-content">
        <span class="info-box-text">Email Terkirim</span>
        <span class="info-box-number"><?= number_format($totalTerkirim ?? 0) ?></span>
      </div>
    </div>
  </div>
</div>

<div class="box box-primary">
  <div class="box-header with-border">
    <h3 class="box-title">Selamat Datang, <?= esc(session()->get('name') ?? 'Guest') ?></h3>
  </div>
  <div class="box-body">
    Dashboard Email Blast - KWT Sitara
  </div>
</div>
<?= $this->endSection() ?>