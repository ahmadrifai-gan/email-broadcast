<?= $this->extend('layouts/main') ?>

<?= $this->section('title') ?>Laporan Template Email<?= $this->endSection() ?>
<?= $this->section('page_title') ?>Laporan Template Email<?= $this->endSection() ?>
<?= $this->section('page_subtitle') ?>Ringkasan Penggunaan dan Status Template<?= $this->endSection() ?>

<?= $this->section('breadcrumb') ?>
<li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
<li class="active">Laporan Template</li>
<?= $this->endSection() ?>

<?= $this->section('styles') ?>
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap.min.css">
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<div class="row">
    <!-- KOTAK RINGKASAN -->
    <div class="col-md-3 col-sm-6 col-xs-12">
        <div class="info-box">
            <span class="info-box-icon bg-aqua"><i class="fa fa-file-text-o"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Total Template</span>
                <span class="info-box-number"><?= count($laporan) ?></span>
            </div>
        </div>
    </div>
    
    <div class="col-md-3 col-sm-6 col-xs-12">
        <div class="info-box">
            <span class="info-box-icon bg-green"><i class="fa fa-check"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Template Aktif</span>
                <span class="info-box-number">
                    <?= count(array_filter($laporan, fn($item) => $item['status'] === 'aktif')) ?>
                </span>
            </div>
        </div>
    </div>

    <div class="col-md-3 col-sm-6 col-xs-12">
        <div class="info-box">
            <span class="info-box-icon bg-yellow"><i class="fa fa-edit"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Template Draf</span>
                <span class="info-box-number">
                    <?= count(array_filter($laporan, fn($item) => $item['status'] === 'draf')) ?>
                </span>
            </div>
        </div>
    </div>

    <div class="col-md-3 col-sm-6 col-xs-12">
        <div class="info-box">
            <span class="info-box-icon bg-red"><i class="fa fa-paper-plane"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Total Email Terkirim</span>
                <span class="info-box-number">
                    <?= array_sum(array_column($laporan, 'sukses_kirim')) ?>
                </span>
            </div>
        </div>
    </div>
</div>

<div class="box box-primary">
    <div class="box-header with-border">
        <h3 class="box-title"><i class="fa fa-bar-chart"></i> Detail Laporan Template</h3>
    </div>
    <div class="box-body">
        <div class="table-responsive">
            <table id="laporanTable" class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th width="50">No</th>
                        <th>Nama Template</th>
                        <th width="100">Status Template</th>
                        <th width="120">Total Dipakai (Kampanye)</th>
                        <th width="120">Sukses Terkirim</th>
                        <th width="120">Pending / Gagal</th>
                        <th>Dibuat Pada</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $no = 1; foreach ($laporan as $row) : ?>
                        <tr>
                            <td><?= $no++ ?></td>
                            <td><?= esc($row['nama_template']) ?></td>
                            <td class="text-center">
                                <?php if ($row['status'] === 'aktif') : ?>
                                    <span class="label label-success">Aktif</span>
                                <?php elseif ($row['status'] === 'draf') : ?>
                                    <span class="label label-warning">Draf</span>
                                <?php else : ?>
                                    <span class="label label-default"><?= esc($row['status']) ?></span>
                                <?php endif; ?>
                            </td>
                            <td class="text-center"><?= $row['total_kampanye'] ?> x</td>
                            <td class="text-center text-success"><strong><?= $row['sukses_kirim'] ?></strong></td>
                            <td class="text-center text-warning"><strong><?= $row['pending_kirim'] + $row['gagal_kirim'] ?></strong></td>
                            <td><?= date('d M Y H:i', strtotime($row['dibuat_pada'])) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap.min.js"></script>
<script>
    $(document).ready(function() {
        $('#laporanTable').DataTable({
            autoWidth: false,
            pageLength: 10,
            language: { url: '//cdn.datatables.net/plug-ins/1.13.4/i18n/id.json' }
        });
    });
</script>
<?= $this->endSection() ?>