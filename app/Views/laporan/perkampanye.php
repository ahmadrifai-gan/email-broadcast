<?= $this->extend('layouts/main') ?>

<?= $this->section('title') ?>Laporan Per Kampanye<?= $this->endSection() ?>
<?= $this->section('page_title') ?>Laporan Per Kampanye<?= $this->endSection() ?>
<?= $this->section('page_subtitle') ?>Hasil pengiriman, putaran, dan penerima untuk satu kampanye<?= $this->endSection() ?>

<?= $this->section('breadcrumb') ?>
<li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
<li>Laporan</li>
<li class="active">Per Kampanye</li>
<?= $this->endSection() ?>

<?= $this->section('styles') ?>
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap.min.css">
<style>
    .lap-chart-box { position: relative; height: 270px; }
    .lap-info th { width: 34%; color: #667085; font-weight: 600; font-size: 13px; white-space: nowrap; }
    .lap-info td { font-size: 13px; word-break: break-word; }
    .lap-info > tbody > tr > th, .lap-info > tbody > tr > td { padding: 6px 8px; }
    .lap-stack { height: 22px; margin-bottom: 6px; border-radius: 4px; }
    .lap-err { font-size: 12px; color: #a94442; }
    .info-box .progress-description { font-size: 12px; }
    .nav-tabs-custom > .nav-tabs > li > a { font-weight: 600; }
    .tab-tools { margin-bottom: 10px; }
    .btn-filter.active { font-weight: 700; }
    .lap-rate .progress { margin: 0 0 2px; height: 7px; }

    /* ====== MODAL PEMILIH KAMPANYE ====== */
    #modalPilihKampanye .modal-dialog { width: 900px; max-width: 95%; margin: 30px auto; }
    #modalPilihKampanye .modal-header {
        background: linear-gradient(135deg, #3c8dbc, #5fa8dc);
        color: #fff; border-radius: 4px 4px 0 0;
    }
    #modalPilihKampanye .modal-title { font-weight: 600; }
    #modalPilihKampanye .modal-header .close { color: #fff; opacity: .8; }
    #modalPilihKampanye .modal-header .close:hover { opacity: 1; }

    #tabelPilihKampanye { font-size: 13px; margin-bottom: 0 !important; }
    #tabelPilihKampanye thead th {
        background: #f8f9fa; color: #495057; font-weight: 600; font-size: 12px;
        text-transform: uppercase; letter-spacing: .3px;
        border-bottom: 2px solid #dee2e6 !important;
        white-space: nowrap; vertical-align: middle;
    }
    #tabelPilihKampanye tbody tr { transition: background .15s; cursor: pointer; }
    #tabelPilihKampanye tbody tr:hover { background: #f0f7ff !important; }
    #tabelPilihKampanye tbody tr.aktif { background: #e8f4fd !important; border-left: 3px solid #3c8dbc; }
    #tabelPilihKampanye tbody td { vertical-align: middle !important; }

    .mk-badge {
        display: inline-flex; align-items: center; gap: 5px;
        padding: 3px 9px; border-radius: 10px;
        font-size: 11px; font-weight: 600;
        text-transform: capitalize; white-space: nowrap;
    }
    .mk-badge .dot { width: 6px; height: 6px; border-radius: 50%; background: currentColor; }
    .mk-badge.selesai        { background: #e8f5e9; color: #2e7d32; }
    .mk-badge.sedang_dikirim { background: #fff3e0; color: #e65100; }
    .mk-badge.terjadwal      { background: #e3f2fd; color: #1565c0; }
    .mk-badge.dijeda         { background: #f3e5f5; color: #6a1b9a; }
    .mk-badge.dibatalkan     { background: #ffebee; color: #c62828; }
    .mk-badge.draf,
    .mk-badge.default        { background: #eceff1; color: #546e7a; }
    .mk-badge.sedang_dikirim .dot { animation: mk-pulse 1.5s infinite; }
    @keyframes mk-pulse {
        0%,100% { opacity: 1; transform: scale(1); }
        50%     { opacity: .4; transform: scale(1.4); }
    }

    .mk-current {
        background: #f0f7ff; border: 1px solid #cfe3f5; border-radius: 6px;
        padding: 10px 14px; margin-bottom: 12px;
        display: flex; align-items: center; gap: 10px; font-size: 13px;
    }
    .mk-current i { color: #3c8dbc; font-size: 18px; }

    .btn-pilih { border-radius: 5px; padding: 3px 12px; font-size: 12px; font-weight: 600; }
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<?php if (empty($kampanye)): ?>
    <div class="box box-default">
        <div class="box-body text-center" style="padding:60px 0;">
            <i class="fa fa-inbox fa-3x text-muted"></i>
            <h4 style="margin-top:15px;">Belum ada kampanye</h4>
            <p class="text-muted">Buat kampanye terlebih dahulu agar laporannya bisa ditampilkan.</p>
            <a href="<?= base_url('kirim-email/buat') ?>" class="btn btn-primary btn-sm">
                <i class="fa fa-plus"></i> Buat Kampanye
            </a>
        </div>
    </div>
<?php else: ?>

<?php
$id     = (int) $kampanye['id'];
$rp     = static fn($n) => number_format((int) $n, 0, ',', '.');
$fmtTgl = static fn($d) => $d ? date('d/m/Y H:i:s', strtotime($d)) : '-';
$rateTx = static fn($r) => $r === null ? '-' : str_replace('.', ',', (string) $r) . '%';
$qExport = '?id=' . $id;
$ganda = $ganda ?? [];
$putaran = $putaran ?? [];
$kategori = $kategori ?? [];
$penerima = $penerima ?? [];
$riwayat = $riwayat ?? [];

$badgeClassMap = [
    'draf'           => 'draf',
    'terjadwal'      => 'terjadwal',
    'sedang_dikirim' => 'sedang_dikirim',
    'selesai'        => 'selesai',
    'dijeda'         => 'dijeda',
    'dibatalkan'     => 'dibatalkan',
];
$statusLabelMap = [
    'draf'           => 'Draf',
    'terjadwal'      => 'Terjadwal',
    'sedang_dikirim' => 'Sedang Dikirim',
    'selesai'        => 'Selesai',
    'dijeda'         => 'Dijeda',
    'dibatalkan'     => 'Dibatalkan',
];
?>

<!-- ================= PEMILIH KAMPANYE ================= -->
<div class="box box-default">
    <div class="box-body">
        <form method="get" action="<?= base_url('laporan/per-kampanye') ?>" id="formPilih" class="form-inline">
            <input type="hidden" name="tab" id="inputTab" value="<?= esc($tab) ?>">
            <input type="hidden" name="id" id="inputIdKampanye" value="<?= $id ?>">

            <div class="form-group">
                <label style="margin-right:8px;font-weight:600;font-size:12px;color:#667085;">
                    <i class="fa fa-bullhorn"></i> Kampanye
                </label>
                <button type="button" class="btn btn-default" id="btnPilihKampanye"
                        data-toggle="modal" data-target="#modalPilihKampanye"
                        style="min-width:340px;text-align:left;">
                    <i class="fa fa-list-ul text-primary"></i>
                    <span id="labelKampanyeAktif">
                        #<?= (int) $kampanye['id'] ?> — <?= esc($kampanye['nama_kampanye']) ?>
                        <small class="text-muted">(<?= (int) $kampanye['jumlah_penerima'] ?> penerima)</small>
                    </span>
                    <span class="caret pull-right" style="margin-top:8px;"></span>
                </button>
            </div>

            <div class="pull-right">
                <a href="<?= base_url('kirim-email/detail/' . $id) ?>" class="btn btn-info btn-sm">
                    <i class="fa fa-eye"></i> Halaman Detail &amp; Kirim
                </a>
                <div class="btn-group">
                    <button type="button" class="btn btn-success btn-sm dropdown-toggle" data-toggle="dropdown">
                        <i class="fa fa-file-excel-o"></i> Ekspor CSV <span class="caret"></span>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-right">
                        <li><a href="<?= esc($exportBase . '/penerima' . $qExport) ?>"><i class="fa fa-users"></i> Semua penerima</a></li>
                        <li><a href="<?= esc($exportBase . '/gagal' . $qExport) ?>"><i class="fa fa-times-circle text-red"></i> Penerima gagal saja (<?= $rp($ringkas['gagal']) ?>)</a></li>
                        <li class="divider"></li>
                        <li><a href="<?= esc($exportBase . '/riwayat' . $qExport) ?>"><i class="fa fa-history"></i> Riwayat percobaan kirim</a></li>
                    </ul>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- ================= INFO + KARTU ================= -->
<div class="row">
    <div class="col-md-5">
        <div class="box box-primary">
            <div class="box-header with-border">
                <h3 class="box-title"><i class="fa fa-info-circle"></i> Informasi Kampanye</h3>
                <div class="box-tools pull-right"><span class="label <?= $kampanye['status_class'] ?>"><?= esc($kampanye['status_label']) ?></span></div>
            </div>
            <div class="box-body no-padding">
                <table class="table lap-info" style="margin:0;">
                    <tr><th>Nama</th><td><strong><?= esc($kampanye['nama_kampanye']) ?></strong></td></tr>
                    <tr><th>Subjek</th><td><?= esc($kampanye['subjek_email']) ?></td></tr>
                    <tr><th>Template</th><td><?= esc($kampanye['nama_template'] ?? '-') ?></td></tr>
                    <tr><th>SMTP</th><td>
                        <?php if (!empty($kampanye['smtp_nama'])): ?>
                            <?= esc($kampanye['smtp_nama']) ?> <small class="text-muted">(<?= esc($kampanye['smtp_from'] ?? '') ?>)</small>
                        <?php else: ?>
                            <span class="text-muted">Default aktif</span>
                        <?php endif; ?>
                    </td></tr>
                    <tr><th>Tipe Pengiriman</th><td><?= esc($kampanye['tipe_label']) ?>
                        <?php if ($kampanye['total_putaran'] > 1): ?>
                            <small class="text-muted">— pengiriman <?= $kampanye['jumlah_putaran'] ?>/<?= $kampanye['total_putaran'] ?></small>
                        <?php endif; ?></td></tr>
                    <tr><th>Kecepatan</th><td><?= esc($kampanye['kecepatan_teks']) ?></td></tr>
                    <tr><th>Jadwal</th><td><?= $fmtTgl($kampanye['jadwal_pengiriman'] ?? null) ?></td></tr>
                    <tr><th>Mulai Dikirim</th><td><?= $fmtTgl($kampanye['mulai_dikirim_pada'] ?? null) ?></td></tr>
                    <tr><th>Selesai Dikirim</th><td><?= $fmtTgl($kampanye['selesai_dikirim_pada'] ?? null) ?></td></tr>
                    <tr><th>Durasi</th><td><?= esc($kampanye['durasi_teks']) ?></td></tr>
                    <tr><th>Dibuat</th><td><?= $fmtTgl($kampanye['dibuat_pada'] ?? null) ?></td></tr>
                </table>
            </div>
        </div>
    </div>

    <div class="col-md-7">
        <div class="row">
            <div class="col-sm-6">
                <div class="info-box"><span class="info-box-icon bg-aqua"><i class="fa fa-users"></i></span>
                    <div class="info-box-content"><span class="info-box-text">Total Penerima</span>
                        <span class="info-box-number"><?= $rp($ringkas['total']) ?></span></div></div>
            </div>
            <div class="col-sm-6">
                <div class="info-box"><span class="info-box-icon bg-green"><i class="fa fa-check"></i></span>
                    <div class="info-box-content"><span class="info-box-text">Berhasil</span>
                        <span class="info-box-number"><?= $rp($ringkas['berhasil']) ?></span>
                        <span class="progress-description text-muted">Keberhasilan <?= $rateTx($ringkas['rate']) ?></span></div></div>
            </div>
            <div class="col-sm-6">
                <div class="info-box"><span class="info-box-icon bg-red"><i class="fa fa-times"></i></span>
                    <div class="info-box-content"><span class="info-box-text">Gagal</span>
                        <span class="info-box-number"><?= $rp($ringkas['gagal']) ?></span></div></div>
            </div>
            <div class="col-sm-6">
                <div class="info-box"><span class="info-box-icon bg-yellow"><i class="fa fa-hourglass-half"></i></span>
                    <div class="info-box-content"><span class="info-box-text">Menunggu</span>
                        <span class="info-box-number"><?= $rp($ringkas['menunggu']) ?></span></div></div>
            </div>
        </div>

        <div class="box box-default">
            <div class="box-body">
                <div class="progress lap-stack">
                    <div class="progress-bar progress-bar-success" style="width: <?= $ringkas['pct_berhasil'] ?>%" title="Berhasil"></div>
                    <div class="progress-bar progress-bar-danger"  style="width: <?= $ringkas['pct_gagal'] ?>%"    title="Gagal"></div>
                    <div class="progress-bar progress-bar-warning" style="width: <?= $ringkas['pct_menunggu'] ?>%" title="Menunggu"></div>
                </div>
                <small class="text-muted">
                    Total percobaan kirim: <strong><?= $rp($riw['terkirim'] + $riw['gagal']) ?></strong>
                    (<?= $rp($riw['terkirim']) ?> terkirim, <?= $rp($riw['gagal']) ?> gagal)
                    <?php if ($riw['awal']): ?> • <?= $fmtTgl($riw['awal']) ?> s/d <?= $fmtTgl($riw['akhir']) ?><?php endif; ?>
                </small>
            </div>
        </div>
    </div>
</div>

<?php if (!empty($ganda)): ?>
    <div class="callout callout-warning">
        <h4><i class="fa fa-clone"></i> <?= count($ganda) ?> penerima menerima email lebih dari sekali</h4>
        <p style="margin-bottom:6px;">Pada kampanye ini tercatat beberapa pengiriman <em>terkirim</em> ke alamat yang sama. Periksa apakah pengiriman ulang disengaja.</p>
        <ul style="margin:0;padding-left:18px;">
            <?php foreach (array_slice($ganda, 0, 8) as $g): ?>
                <li><?= esc($g['alamat_email_penerima']) ?> — <strong><?= (int) $g['kali'] ?>×</strong>
                    <small class="text-muted">(<?= $fmtTgl($g['pertama']) ?> &rarr; <?= $fmtTgl($g['terakhir']) ?>)</small></li>
            <?php endforeach; ?>
            <?php if (count($ganda) > 8): ?><li class="text-muted">dan <?= count($ganda) - 8 ?> penerima lainnya…</li><?php endif; ?>
        </ul>
    </div>
<?php endif; ?>

<!-- ================= GRAFIK ================= -->
<div class="row">
    <div class="col-md-8">
        <div class="box box-primary">
            <div class="box-header with-border"><h3 class="box-title"><i class="fa fa-bar-chart"></i> Linimasa Pengiriman <small>(per <?= esc($linimasa['satuan']) ?>)</small></h3></div>
            <div class="box-body">
                <?php if (empty($linimasa['data'])): ?>
                    <p class="text-muted text-center" style="padding:70px 0;"><i class="fa fa-inbox fa-2x"></i><br>Belum ada aktivitas pengiriman.</p>
                <?php else: ?>
                    <div class="lap-chart-box"><canvas id="chartLinimasa"></canvas></div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="box box-primary">
            <div class="box-header with-border"><h3 class="box-title"><i class="fa fa-pie-chart"></i> Status Penerima</h3></div>
            <div class="box-body">
                <?php if ($ringkas['total'] === 0): ?>
                    <p class="text-muted text-center" style="padding:70px 0;">Kampanye ini belum punya penerima.</p>
                <?php else: ?>
                    <div class="lap-chart-box"><canvas id="chartStatus"></canvas></div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- ================= TAB ================= -->
<div class="nav-tabs-custom">
    <ul class="nav nav-tabs" id="tabLaporan">
        <li class="<?= $tab === 'penerima' ? 'active' : '' ?>"><a href="#tabPenerima" data-toggle="tab" data-tab="penerima"><i class="fa fa-users"></i> Penerima <span class="badge bg-aqua"><?= $rp(count($penerima)) ?></span></a></li>
        <li class="<?= $tab === 'putaran' ? 'active' : '' ?>"><a href="#tabPutaran" data-toggle="tab" data-tab="putaran"><i class="fa fa-repeat"></i> Per Putaran <span class="badge bg-aqua"><?= $rp(count($putaran)) ?></span></a></li>
        <li class="<?= $tab === 'kategori' ? 'active' : '' ?>"><a href="#tabKategori" data-toggle="tab" data-tab="kategori"><i class="fa fa-folder-open"></i> Per Kategori</a></li>
        <li class="<?= $tab === 'riwayat' ? 'active' : '' ?>"><a href="#tabRiwayat" data-toggle="tab" data-tab="riwayat"><i class="fa fa-history"></i> Riwayat Percobaan <span class="badge bg-aqua"><?= $rp($totalRiwayat) ?></span></a></li>
    </ul>

    <div class="tab-content">

        <!-- ========== PENERIMA ========== -->
        <div class="tab-pane <?= $tab === 'penerima' ? 'active' : '' ?>" id="tabPenerima">
            <div class="tab-tools clearfix">
                <div class="btn-group btn-group-sm" id="filterStatus">
                    <button type="button" class="btn btn-default btn-filter active" data-filter="">Semua <span class="badge"><?= $rp($ringkas['total']) ?></span></button>
                    <button type="button" class="btn btn-default btn-filter" data-filter="Terkirim">Terkirim <span class="badge"><?= $rp($ringkas['berhasil']) ?></span></button>
                    <button type="button" class="btn btn-default btn-filter" data-filter="Gagal">Gagal <span class="badge"><?= $rp($ringkas['gagal']) ?></span></button>
                    <button type="button" class="btn btn-default btn-filter" data-filter="Menunggu">Menunggu <span class="badge"><?= $rp($ringkas['menunggu']) ?></span></button>
                </div>
                <?php if (count($putaran) > 1): ?>
                    <select id="filterPutaran" class="form-control input-sm" style="display:inline-block;width:auto;margin-left:8px;">
                        <option value="">Semua putaran</option>
                        <?php foreach ($putaran as $p): ?>
                            <option value="<?= (int) $p['putaran_ke'] ?>">Putaran <?= (int) $p['putaran_ke'] ?></option>
                        <?php endforeach; ?>
                    </select>
                <?php endif; ?>
            </div>
            <div class="table-responsive">
                <table id="tabelPenerima" class="table table-bordered table-striped table-hover" style="width:100%;">
                    <thead>
                        <tr>
                            <th width="40">No</th>
                            <th>Putaran</th>
                            <th>Penerima</th>
                            <th>Kategori</th>
                            <th>Status</th>
                            <th class="text-center">Percobaan</th>
                            <th>Waktu Terkirim</th>
                            <th>Keterangan</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($penerima as $i => $p): ?>
                        <tr>
                            <td><?= $i + 1 ?></td>
                            <td><?= $p['putaran_ke'] !== null ? (int) $p['putaran_ke'] : '-' ?></td>
                            <td><?= esc($p['alamat_email_penerima']) ?>
                                <?php if (!empty($p['nama_penerima'])): ?><br><small class="text-muted"><?= esc($p['nama_penerima']) ?></small><?php endif; ?></td>
                            <td><?= esc($p['kategori_nama']) ?></td>
                            <td><span class="label <?= $p['status_class'] ?>"><?= esc($p['status_label']) ?></span></td>
                            <td class="text-center"><?= (int) $p['jumlah_percobaan'] ?></td>
                            <td data-order="<?= esc($p['terkirim_pada'] ?? '') ?>"><?= $fmtTgl($p['terkirim_pada'] ?? null) ?></td>
                            <td>
                                <?php if ($p['status_pengiriman'] === 'gagal'): ?>
                                    <span class="lap-err"><?= esc($p['error_ringkas']) ?></span><br>
                                    <button type="button" class="btn btn-default btn-xs btn-log"
                                            data-email="<?= esc($p['alamat_email_penerima'], 'attr') ?>"
                                            data-pesan="<?= esc((string) ($p['pesan_kesalahan'] ?? ''), 'attr') ?>">
                                        <i class="fa fa-file-text-o"></i> Detail
                                    </button>
                                    <form action="<?= base_url('kirim-email/kirim-ulang/' . (int) $p['id']) ?>" method="post" style="display:inline;"
                                          data-swal-konfirmasi data-ikon="question"
                                          data-judul="Kirim ulang email?"
                                          data-nama="<?= esc($p['alamat_email_penerima'], 'attr') ?>"
                                          data-teks="Email ke {nama} akan dikirim ulang."
                                          data-ya="Ya, kirim ulang" data-loading="Menyiapkan pengiriman ulang...">
                                        <?= csrf_field() ?>
                                        <button type="submit" class="btn btn-warning btn-xs"><i class="fa fa-repeat"></i> Kirim ulang</button>
                                    </form>
                                <?php else: ?>
                                    <span class="text-muted">-</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- ========== PER PUTARAN ========== -->
        <div class="tab-pane <?= $tab === 'putaran' ? 'active' : '' ?>" id="tabPutaran">
            <?php if (empty($putaran)): ?>
                <p class="text-muted text-center" style="padding:40px 0;">Kampanye ini belum memiliki jadwal pengiriman.</p>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-bordered table-striped table-hover">
                        <thead>
                            <tr>
                                <th class="text-center" width="70">Putaran</th>
                                <th>Jadwal</th>
                                <th>Status</th>
                                <th>Mulai</th>
                                <th>Selesai</th>
                                <th>Keterlambatan</th>
                                <th>Durasi</th>
                                <th class="text-center">Penerima</th>
                                <th class="text-center">Berhasil</th>
                                <th class="text-center">Gagal</th>
                                <th class="text-center">Menunggu</th>
                                <th>Keberhasilan</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($putaran as $p): ?>
                            <tr>
                                <td class="text-center"><strong><?= (int) $p['putaran_ke'] ?></strong></td>
                                <td><?= $fmtTgl($p['waktu_kirim']) ?></td>
                                <td><span class="label <?= $p['status_class'] ?>"><?= esc($p['status_label']) ?></span></td>
                                <td><?= $fmtTgl($p['dimulai_pada']) ?></td>
                                <td><?= $fmtTgl($p['selesai_pada']) ?></td>
                                <td class="<?= ($p['terlambat_detik'] ?? 0) > 60 ? 'text-orange' : 'text-muted' ?>"><?= esc($p['terlambat_teks']) ?></td>
                                <td><?= esc($p['durasi_teks']) ?></td>
                                <td class="text-center"><?= $rp($p['total']) ?></td>
                                <td class="text-center text-success"><strong><?= $rp($p['berhasil']) ?></strong></td>
                                <td class="text-center text-danger"><strong><?= $rp($p['gagal']) ?></strong></td>
                                <td class="text-center"><?= $rp($p['menunggu']) ?></td>
                                <td class="lap-rate" style="min-width:90px;">
                                    <?php if ($p['rate'] === null): ?><small class="text-muted">-</small>
                                    <?php else: ?>
                                        <div class="progress"><div class="progress-bar progress-bar-<?= $p['rate'] >= 90 ? 'success' : ($p['rate'] >= 60 ? 'warning' : 'danger') ?>" style="width: <?= (float) $p['rate'] ?>%"></div></div>
                                        <small><?= $rateTx($p['rate']) ?></small>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <small class="text-muted"><i class="fa fa-info-circle"></i> Keterlambatan = selisih antara jadwal dan saat putaran benar-benar mulai diproses.</small>
            <?php endif; ?>
        </div>

        <!-- ========== PER KATEGORI ========== -->
        <div class="tab-pane <?= $tab === 'kategori' ? 'active' : '' ?>" id="tabKategori">
            <?php if (empty($kategori)): ?>
                <p class="text-muted text-center" style="padding:40px 0;">Belum ada penerima.</p>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-bordered table-striped table-hover">
                        <thead>
                            <tr>
                                <th>Kategori Kontak</th>
                                <th class="text-center">Penerima</th>
                                <th class="text-center">Berhasil</th>
                                <th class="text-center">Gagal</th>
                                <th class="text-center">Menunggu</th>
                                <th>Keberhasilan</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($kategori as $k): ?>
                            <?php $d = (int) $k['berhasil'] + (int) $k['gagal']; $r = $d > 0 ? round($k['berhasil'] / $d * 100, 1) : null; ?>
                            <tr>
                                <td><span class="label <?= $k['nama'] === 'Tanpa kategori' ? 'label-default' : 'label-primary' ?>"><?= esc($k['nama']) ?></span></td>
                                <td class="text-center"><strong><?= $rp($k['total']) ?></strong></td>
                                <td class="text-center text-success"><?= $rp($k['berhasil']) ?></td>
                                <td class="text-center text-danger"><?= $rp($k['gagal']) ?></td>
                                <td class="text-center"><?= $rp($k['menunggu']) ?></td>
                                <td class="lap-rate" style="min-width:110px;">
                                    <?php if ($r === null): ?><small class="text-muted">-</small>
                                    <?php else: ?>
                                        <div class="progress"><div class="progress-bar progress-bar-<?= $r >= 90 ? 'success' : ($r >= 60 ? 'warning' : 'danger') ?>" style="width: <?= (float) $r ?>%"></div></div>
                                        <small><?= $rateTx($r) ?></small>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>

        <!-- ========== RIWAYAT PERCOBAAN ========== -->
        <div class="tab-pane <?= $tab === 'riwayat' ? 'active' : '' ?>" id="tabRiwayat">
            <?php if ($totalRiwayat > $limitRiwayat): ?>
                <div class="tab-tools"><span class="text-warning" style="font-size:12px;">
                    <i class="fa fa-warning"></i> Layar menampilkan <?= $rp($limitRiwayat) ?> aktivitas terbaru dari <?= $rp($totalRiwayat) ?>. Gunakan Ekspor CSV untuk seluruh data.
                </span></div>
            <?php endif; ?>
            <div class="table-responsive">
                <table id="tabelRiwayat" class="table table-bordered table-striped table-hover" style="width:100%;">
                    <thead>
                        <tr>
                            <th width="40">No</th>
                            <th>Waktu</th>
                            <th>Penerima</th>
                            <th>Aktivitas</th>
                            <th>Penyedia</th>
                            <th>ID Pesan</th>
                            <th>Keterangan</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($riwayat as $i => $r): ?>
                        <tr>
                            <td><?= $i + 1 ?></td>
                            <td data-order="<?= esc($r['terjadi_pada']) ?>"><?= $fmtTgl($r['terjadi_pada']) ?></td>
                            <td><?= esc($r['alamat_email_penerima']) ?></td>
                            <td><span class="label <?= $r['jenis_aktivitas'] === 'terkirim' ? 'label-success' : 'label-danger' ?>"><?= esc(ucfirst($r['jenis_aktivitas'])) ?></span></td>
                            <td><?= esc($r['penyedia_email'] ?? '-') ?></td>
                            <td><small class="text-muted"><?= esc($r['id_pesan_penyedia'] ?? '-') ?></small></td>
                            <td>
                                <?php if ($r['jenis_aktivitas'] === 'gagal'): ?>
                                    <span class="lap-err"><?= esc($r['error_ringkas']) ?></span>
                                    <button type="button" class="btn btn-default btn-xs btn-log"
                                            data-email="<?= esc($r['alamat_email_penerima'], 'attr') ?>"
                                            data-pesan="<?= esc((string) ($r['detail_gagal'] ?? ''), 'attr') ?>">
                                        <i class="fa fa-file-text-o"></i> Detail
                                    </button>
                                <?php else: ?>
                                    <span class="text-muted">-</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</div>

<?php endif; /* end if kampanye */ ?>

<!-- ============================================================
     MODAL PEMILIH KAMPANYE — WAJIB DI DALAM SECTION 'content'
     ============================================================ -->
<?php if (!empty($daftar)): ?>
<div class="modal fade" id="modalPilihKampanye" tabindex="-1" role="dialog" aria-labelledby="modalPilihKampanyeLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Tutup">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title" id="modalPilihKampanyeLabel">
                    <i class="fa fa-bullhorn"></i> Pilih Kampanye
                </h4>
            </div>
            <div class="modal-body">

                <?php if (!empty($kampanye)): ?>
                <div class="mk-current">
                    <i class="fa fa-check-circle"></i>
                    <div>
                        <strong>Sedang dilihat:</strong>
                        #<?= (int) $kampanye['id'] ?> — <?= esc($kampanye['nama_kampanye']) ?>
                        <span class="mk-badge <?= $badgeClassMap[$kampanye['status']] ?? 'default' ?>" style="margin-left:6px;">
                            <span class="dot"></span> <?= esc($statusLabelMap[$kampanye['status']] ?? ucfirst($kampanye['status'])) ?>
                        </span>
                    </div>
                </div>
                <?php endif; ?>

                <div class="table-responsive">
                    <table id="tabelPilihKampanye" class="table table-hover table-bordered" style="width:100%;">
                        <thead>
                            <tr>
                                <th width="45">#</th>
                                <th>Nama Kampanye</th>
                                <th width="120">Status</th>
                                <th class="text-center" width="80">Penerima</th>
                                <th width="130">Dibuat</th>
                                <th class="text-center" width="80">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($daftar as $d): ?>
                            <?php
                                $isAktif    = (!empty($kampanye) && (int) $d['id'] === (int) $kampanye['id']);
                                $badgeCls   = $badgeClassMap[$d['status']] ?? 'default';
                                $badgeLabel = $statusLabelMap[$d['status']] ?? ucfirst($d['status']);
                            ?>
                            <tr class="<?= $isAktif ? 'aktif' : '' ?>"
                                data-id="<?= (int) $d['id'] ?>"
                                data-nama="<?= esc($d['nama_kampanye'], 'attr') ?>"
                                data-penerima="<?= (int) $d['jumlah_penerima'] ?>">
                                <td class="text-muted" data-order="<?= (int) $d['id'] ?>">#<?= (int) $d['id'] ?></td>
                                <td>
                                    <strong><?= esc($d['nama_kampanye']) ?></strong>
                                    <?php if ($isAktif): ?>
                                        <small class="text-primary"><i class="fa fa-check"></i> aktif</small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="mk-badge <?= $badgeCls ?>">
                                        <span class="dot"></span> <?= esc($badgeLabel) ?>
                                    </span>
                                </td>
                                <td class="text-center">
                                    <strong><?= $rp($d['jumlah_penerima']) ?></strong>
                                </td>
                                <td>
                                    <small class="text-muted">
                                        <?= $d['dibuat_pada'] ? date('d/m/Y H:i', strtotime($d['dibuat_pada'])) : '-' ?>
                                    </small>
                                </td>
                                <td class="text-center">
                                    <?php if ($isAktif): ?>
                                        <button type="button" class="btn btn-default btn-xs btn-pilih" disabled>
                                            <i class="fa fa-check"></i> Dipilih
                                        </button>
                                    <?php else: ?>
                                        <button type="button" class="btn btn-primary btn-xs btn-pilih">
                                            <i class="fa fa-arrow-right"></i> Pilih
                                        </button>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer" style="background:#f8f9fa;">
                <button type="button" class="btn btn-default" data-dismiss="modal">
                    <i class="fa fa-times"></i> Tutup
                </button>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<?= $this->endSection() ?>

<!-- ============================================================
     SECTION SCRIPTS
     ============================================================ -->
<?= $this->section('scripts') ?>
<?php
$flashSwal = [
    'success' => session()->getFlashdata('success'),
    'error'   => session()->getFlashdata('error'),
    'errors'  => (array) session()->getFlashdata('errors'),
];
?>
<?= $this->include('layouts/swal_helper') ?>
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>

<script>
$(function () {

    AppSwal.flash(<?= json_encode($flashSwal, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE) ?>);

    /* ==========================================================
       TABEL PILIH KAMPANYE (dalam modal)
       ========================================================== */
    let tPilih = null;
    if ($('#tabelPilihKampanye').length) {
        tPilih = $('#tabelPilihKampanye').DataTable({
            autoWidth: false,
            pageLength: 8,
            lengthMenu: [[8, 15, 25, 50, -1], [8, 15, 25, 50, 'Semua']],
            order: [[0, 'desc']],
            columnDefs: [
                { targets: 0, orderable: true,  searchable: false, width: '45px' },
                { targets: -1, orderable: false, searchable: false }
            ],
            language: {
                emptyTable:   '<div style="padding:30px;text-align:center;color:#6c757d;"><i class="fa fa-inbox" style="font-size:32px;color:#cbd5e0;"></i><br><br>Belum ada kampanye.</div>',
                zeroRecords:  '<div style="padding:30px;text-align:center;color:#6c757d;"><i class="fa fa-search" style="font-size:32px;color:#cbd5e0;"></i><br><br>Tidak ada data yang cocok.</div>',
                info:         'Menampilkan <b>_START_</b>–<b>_END_</b> dari <b>_TOTAL_</b> kampanye',
                infoEmpty:    'Tidak ada data',
                infoFiltered: '(difilter dari _MAX_ total)',
                lengthMenu:   'Tampilkan _MENU_ data',
                search:       '',
                searchPlaceholder: 'Cari nama kampanye...',
                paginate: { first: '«', last: '»', next: '›', previous: '‹' }
            }
        });
    }

    /* ---------- Aksi pilih ---------- */
    function pilihKampanye(id) {
        $('#inputIdKampanye').val(id);
        $('#formPilih').trigger('submit');
    }

    $(document).on('click', '#tabelPilihKampanye .btn-pilih', function (e) {
        e.stopPropagation();
        if (this.disabled) return;
        const id = $(this).closest('tr').data('id');
        if (!id) return;
        $(this).prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Memuat...');
        $('#modalPilihKampanye').modal('hide');
        setTimeout(() => pilihKampanye(id), 200);
    });

    $(document).on('click', '#tabelPilihKampanye tbody tr', function (e) {
        if ($(e.target).closest('.btn-pilih').length) return;
        if ($(this).hasClass('aktif')) return;
        const id = $(this).data('id');
        if (!id) return;
        $('#modalPilihKampanye').modal('hide');
        setTimeout(() => pilihKampanye(id), 200);
    });

    /* ---------- Reset pencarian saat modal dibuka ---------- */
    $('#modalPilihKampanye').on('shown.bs.modal', function () {
        if (tPilih) {
            tPilih.search('').draw();
            setTimeout(() => {
                $(this).find('.dataTables_filter input').trigger('focus');
            }, 300);
        }
    });

    /* ---------- Enter di search box → pilih baris pertama ---------- */
    $(document).on('keydown', '#modalPilihKampanye .dataTables_filter input', function (e) {
        if (e.key === 'Enter' && tPilih) {
            const rows = tPilih.rows({ search: 'applied' }).nodes();
            if (rows.length > 0) {
                const id = $(rows[0]).data('id');
                $('#modalPilihKampanye').modal('hide');
                setTimeout(() => pilihKampanye(id), 200);
            }
        }
    });

    /* ==========================================================
       TABEL PENERIMA & RIWAYAT
       ========================================================== */
    const bahasa = {
        emptyTable: 'Tidak ada data.', zeroRecords: 'Tidak ada data yang cocok',
        info: 'Menampilkan _START_ - _END_ dari _TOTAL_ data', infoEmpty: 'Tidak ada data',
        infoFiltered: '(difilter dari _MAX_ data)', lengthMenu: 'Tampilkan _MENU_ data', search: 'Cari:',
        paginate: { first: 'Pertama', last: 'Terakhir', next: 'Selanjutnya', previous: 'Sebelumnya' }
    };
    function nomorOtomatis() {
        const api = this.api(), start = api.page.info().start;
        api.column(0, { page: 'current' }).nodes().each(function (cell, i) { cell.innerHTML = start + i + 1; });
    }

    let tPenerima = null;
    if ($('#tabelPenerima').length) {
        tPenerima = $('#tabelPenerima').DataTable({
            autoWidth: false, pageLength: 10, lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, 'Semua']],
            order: [[0, 'asc']],
            columnDefs: [{ targets: 0, orderable: false, searchable: false }, { targets: -1, orderable: false }],
            drawCallback: nomorOtomatis, language: bahasa
        });
    }
    if ($('#tabelRiwayat').length) {
        $('#tabelRiwayat').DataTable({
            autoWidth: false, pageLength: 10, lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, 'Semua']],
            order: [[1, 'desc']],
            columnDefs: [{ targets: 0, orderable: false, searchable: false }, { targets: -1, orderable: false }],
            drawCallback: nomorOtomatis, language: bahasa
        });
    }

    /* ---------- Filter status & putaran ---------- */
    if (tPenerima) {
        $('#filterStatus').on('click', '.btn-filter', function () {
            $('#filterStatus .btn-filter').removeClass('active');
            $(this).addClass('active');
            const f = this.dataset.filter;
            tPenerima.column(4).search(f ? '^' + f + '$' : '', true, false).draw();
        });
        $('#filterPutaran').on('change', function () {
            const v = this.value;
            tPenerima.column(1).search(v ? '^' + v + '$' : '', true, false).draw();
        });
    }

    /* ---------- Ingat tab aktif ---------- */
    $('#tabLaporan a[data-toggle="tab"]').on('shown.bs.tab', function () {
        $('#inputTab').val($(this).data('tab'));
        $.fn.dataTable.tables({ visible: true, api: true }).columns.adjust();
    });

    /* ---------- Detail log kegagalan ---------- */
    $(document).on('click', '.btn-log', function () {
        const email = this.dataset.email, pesan = (this.dataset.pesan || '').trim();
        Swal.fire({
            icon: 'info', title: 'Log Kegagalan', width: 760,
            confirmButtonColor: '#3085d6', confirmButtonText: 'Tutup',
            html: '<div style="text-align:left;font-size:13px;margin-bottom:6px;">Penerima: <b>' + AppSwal.esc(email) + '</b></div>' +
                  '<pre style="text-align:left;white-space:pre-wrap;max-height:340px;overflow:auto;font-size:12px;background:#f7f7f9;padding:10px;border-radius:6px;">' +
                  AppSwal.esc(pesan || '(tidak ada log tersimpan)') + '</pre>'
        });
    });

    /* ---------- Grafik ---------- */
    if (typeof Chart === 'undefined') return;

    const satuan = <?= json_encode($linimasa['satuan'] ?? 'menit') ?>;
    const data   = <?= json_encode($linimasa['data'] ?? [], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_INVALID_UTF8_SUBSTITUTE) ?>;
    function label(b) {
        const tgl = b.slice(8, 10) + '/' + b.slice(5, 7);
        return satuan === 'hari' ? tgl : tgl + ' ' + b.slice(11, 16);
    }
    const elLin = document.getElementById('chartLinimasa');
    if (elLin && data.length) {
        new Chart(elLin, {
            type: 'bar',
            data: {
                labels: data.map(r => label(r.bucket)),
                datasets: [
                    { label: 'Terkirim', data: data.map(r => +r.terkirim), backgroundColor: '#00a65a' },
                    { label: 'Gagal',    data: data.map(r => +r.gagal),    backgroundColor: '#dd4b39' }
                ]
            },
            options: {
                responsive: true, maintainAspectRatio: false,
                scales: { x: { stacked: true }, y: { stacked: true, beginAtZero: true, ticks: { precision: 0 } } },
                plugins: { legend: { position: 'bottom' } }
            }
        });
    }

    const elSt = document.getElementById('chartStatus');
    if (elSt) {
        new Chart(elSt, {
            type: 'doughnut',
            data: {
                labels: ['Berhasil', 'Gagal', 'Menunggu'],
                datasets: [{
                    data: [<?= (int) ($ringkas['berhasil'] ?? 0) ?>, <?= (int) ($ringkas['gagal'] ?? 0) ?>, <?= (int) ($ringkas['menunggu'] ?? 0) ?>],
                    backgroundColor: ['#00a65a', '#dd4b39', '#f39c12']
                }]
            },
            options: { responsive: true, maintainAspectRatio: false, cutout: '62%', plugins: { legend: { position: 'bottom' } } }
        });
    }
});
</script>
<?= $this->endSection() ?>