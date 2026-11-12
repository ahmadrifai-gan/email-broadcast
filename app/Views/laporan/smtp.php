<?= $this->extend('layouts/main') ?>

<?= $this->section('title') ?>Laporan SMTP<?= $this->endSection() ?>
<?= $this->section('page_title') ?>Laporan SMTP<?= $this->endSection() ?>
<?= $this->section('page_subtitle') ?>Ringkasan kuota dan performa akun pengirim<?= $this->endSection() ?>

<?= $this->section('breadcrumb') ?>
<li><a href="<?= site_url('dashboard') ?>"><i class="fa fa-dashboard"></i> Home</a></li>
<li class="active">Laporan SMTP</li>
<?= $this->endSection() ?>

<?= $this->section('styles') ?>
<style>
    .smtp-summary .info-box {
        min-height: 100px;
        margin-bottom: 20px;
        border-radius: 4px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, .08);
    }

    .smtp-summary .info-box-icon {
        height: 100px;
        line-height: 100px;
    }

    .smtp-summary .info-box-content {
        padding: 18px 10px;
    }

    .smtp-summary .info-box-number {
        font-size: 24px;
        line-height: 28px;
    }

    .smtp-report .box-header {
        padding: 15px 20px;
    }

    .smtp-report .box-title {
        font-weight: 600;
    }

    .smtp-report .table {
        margin-bottom: 0;
    }

    .smtp-report .table > thead > tr > th {
        background: #f7f9fb;
        border-bottom: 1px solid #e5e9ed;
        color: #52606d;
        font-size: 12px;
        text-transform: uppercase;
        letter-spacing: .25px;
        white-space: nowrap;
    }

    .smtp-report .table > tbody > tr > td {
        vertical-align: middle;
    }

    .smtp-name {
        color: #2f3b45;
        font-weight: 600;
    }

    .smtp-email {
        display: inline-block;
        max-width: 220px;
        overflow: hidden;
        text-overflow: ellipsis;
        vertical-align: bottom;
        white-space: nowrap;
    }

    .smtp-usage {
        min-width: 150px;
    }

    .smtp-usage .progress {
        height: 8px;
        margin: 0 0 5px;
        border-radius: 4px;
        background: #edf1f5;
    }

    .smtp-usage .progress-bar {
        border-radius: 4px;
    }

    .smtp-report .box-footer {
        background: #fafbfc;
        border-top-color: #edf0f2;
        line-height: 1.6;
    }

    @media (max-width: 767px) {
        .smtp-summary .info-box {
            margin-bottom: 15px;
        }

        .smtp-report .box-header {
            padding: 12px 15px;
        }
    }
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<div class="row smtp-summary">
    <div class="col-lg-3 col-sm-6">
        <div class="info-box">
            <span class="info-box-icon bg-aqua"><i class="fa fa-server"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Jumlah SMTP</span>
                <span class="info-box-number"><?= number_format($ringkasan['jumlah_smtp']) ?></span>
                <small class="text-muted"><?= number_format($ringkasan['jumlah_aktif']) ?> aktif</small>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-sm-6">
        <div class="info-box">
            <span class="info-box-icon bg-blue"><i class="fa fa-bar-chart"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Total kuota harian</span>
                <span class="info-box-number"><?= number_format($ringkasan['kuota']) ?></span>
                <small class="text-muted">Batas seluruh SMTP</small>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-sm-6">
        <div class="info-box">
            <span class="info-box-icon bg-yellow"><i class="fa fa-paper-plane"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Terpakai (24 jam)</span>
                <span class="info-box-number"><?= number_format($ringkasan['terpakai']) ?></span>
                <small class="text-muted">Email terkirim</small>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-sm-6">
        <div class="info-box">
            <span class="info-box-icon bg-green"><i class="fa fa-check-circle"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Sisa kuota</span>
                <span class="info-box-number"><?= number_format($ringkasan['sisa']) ?></span>
                <small class="text-muted">Tersedia hari ini</small>
            </div>
        </div>
    </div>
</div>

<div class="box box-primary smtp-report">
    <div class="box-header with-border">
        <h3 class="box-title"><i class="fa fa-line-chart"></i> Kuota dan pemakaian per SMTP</h3>
    </div>
    <div class="box-body no-padding">
        <div class="table-responsive">
            <table class="table table-bordered table-hover">
                <thead>
                    <tr>
                        <th style="width: 45px">No</th>
                        <th>SMTP</th>
                        <th>Status</th>
                        <th class="text-right">Kuota/hari</th>
                        <th class="text-right">Terpakai<br><small>(24 jam)</small></th>
                        <th class="text-right">Sisa</th>
                        <th class="smtp-usage">Pemakaian</th>
                        <th class="text-right">Gagal<br><small>(24 jam)</small></th>
                        <th class="text-right">Total<br>terkirim</th>
                        <th class="text-right">Total<br>gagal</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (empty($baris)): ?>
                    <tr><td colspan="10" class="text-center text-muted py-4">Belum ada SMTP.</td></tr>
                <?php endif; ?>

                <?php foreach ($baris as $i => $b): ?>
                    <?php
                        $warna = $b['persen'] >= 90 ? 'bg-danger' : ($b['persen'] >= 70 ? 'bg-warning' : 'bg-success');
                    ?>
                    <tr>
                        <td><?= $i + 1 ?></td>
                        <td>
                            <span class="smtp-name"><?= esc($b['nama']) ?></span><br>
                            <small class="text-muted smtp-email" title="<?= esc($b['from_email'], 'attr') ?>"><?= esc($b['from_email']) ?></small>
                        </td>
                        <td>
                            <?php if ($b['aktif']): ?>
                                <span class="label label-success"><i class="fa fa-check"></i> Aktif</span>
                            <?php else: ?>
                                <span class="label label-default">Nonaktif</span>
                            <?php endif; ?>
                        </td>
                        <td class="text-right"><?= number_format($b['kuota']) ?></td>
                        <td class="text-right"><?= number_format($b['terpakai']) ?></td>
                        <td class="text-right"><strong><?= number_format($b['sisa']) ?></strong></td>
                        <td>
                            <div class="smtp-usage">
                                <div class="progress">
                                    <div class="progress-bar <?= $warna ?>" style="width: <?= min(100, max(0, $b['persen'])) ?>%"></div>
                                </div>
                                <small class="text-muted"><?= $b['persen'] ?>% terpakai</small>
                            </div>
                        </td>
                        <td class="text-right"><?= number_format($b['gagal_24j']) ?></td>
                        <td class="text-right"><?= number_format($b['terkirim_total']) ?></td>
                        <td class="text-right"><?= number_format($b['gagal_total']) ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <div class="box-footer small text-muted">
        <i class="fa fa-info-circle"></i>
            Kuota dihitung dari jumlah email terkirim dalam 24 jam terakhir.
            <?php if ($kuotaPerSmtp): ?>
                SMTP tanpa kuota khusus memakai batas bawaan <?= number_format($kuotaDefault) ?> email/hari.
            <?php else: ?>
                Semua SMTP memakai batas bawaan <?= number_format($kuotaDefault) ?> email/hari (EMAIL_DAILY_LIMIT).
            <?php endif; ?>
    </div>
</div>

<?= $this->endSection() ?>