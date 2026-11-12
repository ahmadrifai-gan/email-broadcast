<?= $this->extend('layouts/main') ?>

<?= $this->section('title') ?>Ringkasan Kampanye<?= $this->endSection() ?>
<?= $this->section('page_title') ?>Ringkasan Kampanye<?= $this->endSection() ?>
<?= $this->section('page_subtitle') ?>Laporan Aktivitas Email Broadcast<?= $this->endSection() ?>

<?= $this->section('breadcrumb') ?>
<li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
<li>Laporan</li>
<li class="active">Ringkasan Kampanye</li>
<?= $this->endSection() ?>

<?= $this->section('styles') ?>
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap.min.css">
<style>
    .lap-filter .form-group { margin-right: 10px; margin-bottom: 8px; }
    .lap-filter label { margin-right: 5px; font-weight: 600; font-size: 12px; color: #667085; }
    .lap-chart-box { position: relative; height: 290px; }
    .lap-status-table td, .lap-status-table th { padding: 5px 8px !important; font-size: 13px; }
    .lap-rate { min-width: 90px; }
    .lap-rate .progress { margin: 0 0 2px; height: 7px; }
    .info-box .progress-description { font-size: 12px; }
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<?php
$fmtTgl = static fn($d) => $d ? date('d/m/Y H:i', strtotime($d)) : '-';
$rp     = static fn($n) => number_format((int) $n, 0, ',', '.');
$rateTx = static fn($r) => $r === null ? '-' : str_replace('.', ',', (string) $r) . '%';
?>

<!-- ================= FILTER ================= -->
<div class="box box-default">
    <div class="box-body lap-filter">
        <form method="get" action="<?= base_url('laporan/ringkasan-kampanye') ?>" id="formFilter" class="form-inline">
            <div class="form-group">
                <label for="dari">Dari</label>
                <input type="date" name="dari" id="dari" class="form-control input-sm" value="<?= esc($filter['dari'] ?? '') ?>">
            </div>
            <div class="form-group">
                <label for="sampai">Sampai</label>
                <input type="date" name="sampai" id="sampai" class="form-control input-sm" value="<?= esc($filter['sampai'] ?? '') ?>">
            </div>
            <div class="form-group">
                <label for="status">Status</label>
                <select name="status" id="status" class="form-control input-sm">
                    <option value="">Semua status</option>
                    <?php foreach ($statusMap as $kode => [$label]): ?>
                        <option value="<?= esc($kode) ?>" <?= $filter['status'] === $kode ? 'selected' : '' ?>>
                            <?= esc($label) ?><?= isset($statusCount[$kode]) ? ' (' . (int) $statusCount[$kode] . ')' : '' ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <button type="submit" class="btn btn-primary btn-sm"><i class="fa fa-filter"></i> Terapkan</button>
            <a href="<?= base_url('laporan/ringkasan-kampanye') ?>" class="btn btn-default btn-sm"><i class="fa fa-refresh"></i> Reset</a>
            <a href="<?= esc($exportUrl) ?>" class="btn btn-success btn-sm pull-right"><i class="fa fa-file-excel-o"></i> Ekspor CSV</a>

            <div style="margin-top:8px;">
                <span class="text-muted" style="font-size:12px;">Periode cepat:</span>
                <div class="btn-group btn-group-xs" id="presetPeriode">
                    <button type="button" class="btn btn-default" data-preset="hari">Hari ini</button>
                    <button type="button" class="btn btn-default" data-preset="7">7 hari</button>
                    <button type="button" class="btn btn-default" data-preset="30">30 hari</button>
                    <button type="button" class="btn btn-default" data-preset="bulan">Bulan ini</button>
                    <button type="button" class="btn btn-default" data-preset="semua">Semua waktu</button>
                </div>
                <small class="text-muted" style="margin-left:8px;">
                    Periode dihitung dari tanggal mulai kirim (atau jadwal / tanggal dibuat bila belum dikirim).
                </small>
            </div>
        </form>
    </div>
</div>

<!-- ================= KARTU RINGKASAN ================= -->
<div class="row">
    <div class="col-md-3 col-sm-6 col-xs-12">
        <div class="info-box">
            <span class="info-box-icon bg-aqua"><i class="fa fa-bullhorn"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Total Kampanye</span>
                <span class="info-box-number"><?= $rp($ringkas['kampanye']) ?></span>
                <span class="progress-description text-muted">Rata-rata durasi: <?= esc($ringkas['rata_durasi']) ?></span>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6 col-xs-12">
        <div class="info-box">
            <span class="info-box-icon bg-light-blue"><i class="fa fa-users"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Total Penerima</span>
                <span class="info-box-number"><?= $rp($ringkas['penerima']) ?></span>
                <span class="progress-description text-muted"><?= $rp($ringkas['menunggu']) ?> masih menunggu</span>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6 col-xs-12">
        <div class="info-box">
            <span class="info-box-icon bg-green"><i class="fa fa-check"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Email Berhasil</span>
                <span class="info-box-number"><?= $rp($ringkas['berhasil']) ?></span>
                <div class="progress" style="margin:3px 0;height:5px;">
                    <div class="progress-bar progress-bar-success" style="width: <?= (float) ($ringkas['rate'] ?? 0) ?>%"></div>
                </div>
                <span class="progress-description text-muted">Keberhasilan <?= $rateTx($ringkas['rate']) ?> dari yang diproses</span>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6 col-xs-12">
        <div class="info-box">
            <span class="info-box-icon bg-red"><i class="fa fa-times"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Email Gagal</span>
                <span class="info-box-number"><?= $rp($ringkas['gagal']) ?></span>
                <span class="progress-description text-muted">Lihat alasan di Laporan Penerima</span>
            </div>
        </div>
    </div>
</div>

<!-- ================= GRAFIK ================= -->
<div class="row">
    <div class="col-md-8">
        <div class="box box-primary">
            <div class="box-header with-border">
                <h3 class="box-title"><i class="fa fa-bar-chart"></i> Aktivitas Pengiriman per Hari</h3>
            </div>
            <div class="box-body">
                <?php if (empty($harian)): ?>
                    <p class="text-muted text-center" style="padding:70px 0;"><i class="fa fa-inbox fa-2x"></i><br>Belum ada aktivitas pengiriman pada periode ini.</p>
                <?php else: ?>
                    <div class="lap-chart-box"><canvas id="chartHarian"></canvas></div>
                    <small class="text-muted">Dihitung dari riwayat percobaan kirim; satu penerima bisa dicoba lebih dari sekali.</small>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="box box-primary">
            <div class="box-header with-border">
                <h3 class="box-title"><i class="fa fa-pie-chart"></i> Status Kampanye</h3>
            </div>
            <div class="box-body">
                <?php if (empty($statusCount)): ?>
                    <p class="text-muted text-center" style="padding:70px 0;">Tidak ada kampanye pada periode ini.</p>
                <?php else: ?>
                    <div class="lap-chart-box" style="height:200px;"><canvas id="chartStatus"></canvas></div>
                    <table class="table table-condensed lap-status-table" style="margin-top:10px;">
                        <?php foreach ($statusMap as $kode => [$label, $cls]): ?>
                            <?php if (!empty($statusCount[$kode])): ?>
                                <tr>
                                    <td><span class="label <?= $cls ?>"><?= esc($label) ?></span></td>
                                    <td class="text-right"><strong><?= (int) $statusCount[$kode] ?></strong></td>
                                </tr>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </table>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- ================= TABEL KAMPANYE ================= -->
<div class="box box-primary">
    <div class="box-header with-border">
        <h3 class="box-title"><i class="fa fa-list-ul"></i> Rincian per Kampanye</h3>
    </div>
    <div class="box-body">
        <div class="table-responsive">
            <table id="tabelKampanye" class="table table-bordered table-striped table-hover" style="width:100%;">
                <thead>
                    <tr>
                        <th width="40">No</th>
                        <th>Kampanye</th>
                        <th>Template</th>
                        <th>SMTP</th>
                        <th>Status</th>
                        <th>Tipe</th>
                        <th class="text-center">Penerima</th>
                        <th class="text-center">Berhasil</th>
                        <th class="text-center">Gagal</th>
                        <th>Keberhasilan</th>
                        <th>Mulai Kirim</th>
                        <th>Durasi</th>
                        <th width="60">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($kampanye as $i => $k): ?>
                    <tr>
                        <td><?= $i + 1 ?></td>
                        <td>
                            <strong><?= esc($k['nama_kampanye']) ?></strong><br>
                            <small class="text-muted"><?= esc($k['subjek_email']) ?></small>
                        </td>
                        <td><?= esc($k['nama_template'] ?? '-') ?></td>
                        <td><?= !empty($k['smtp_nama']) ? esc($k['smtp_nama']) : '<small class="text-muted">Default aktif</small>' ?></td>
                        <td><span class="label <?= $k['status_class'] ?>"><?= esc($k['status_label']) ?></span></td>
                        <td>
                            <?= esc($k['tipe_label']) ?>
                            <?php if ($k['total_putaran'] > 1): ?>
                                <br><small class="text-muted">Pengiriman <?= $k['jumlah_putaran'] ?>/<?= $k['total_putaran'] ?></small>
                            <?php endif; ?>
                        </td>
                        <td class="text-center"><?= $rp($k['total_penerima']) ?></td>
                        <td class="text-center text-success"><strong><?= $rp($k['berhasil']) ?></strong></td>
                        <td class="text-center text-danger"><strong><?= $rp($k['gagal']) ?></strong></td>
                        <td class="lap-rate" data-order="<?= $k['rate'] ?? -1 ?>">
                            <?php if ($k['rate'] === null): ?>
                                <small class="text-muted">-</small>
                            <?php else: ?>
                                <div class="progress"><div class="progress-bar progress-bar-<?= $k['rate'] >= 90 ? 'success' : ($k['rate'] >= 60 ? 'warning' : 'danger') ?>" style="width: <?= (float) $k['rate'] ?>%"></div></div>
                                <small><?= $rateTx($k['rate']) ?></small>
                            <?php endif; ?>
                        </td>
                        <td data-order="<?= esc($k['tanggal_acuan'] ?? '') ?>"><?= $fmtTgl($k['mulai_dikirim_pada'] ?? null) ?></td>
                        <td data-order="<?= $k['durasi_detik'] ?? -1 ?>"><?= esc($k['durasi_teks']) ?></td>
                        <td class="text-center">
                            <a href="<?= base_url('kirim-email/detail/' . $k['id']) ?>" class="btn btn-info btn-xs" title="Lihat detail kampanye"><i class="fa fa-eye"></i></a>
                        </td>
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
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>

<script>
$(function () {

    /* ---------- DataTables ---------- */
    $('#tabelKampanye').DataTable({
        autoWidth: false,
        pageLength: 10,
        lengthMenu: [[5, 10, 25, 50, -1], [5, 10, 25, 50, 'Semua']],
        order: [[10, 'desc']],
        columnDefs: [
            { targets: 0,  orderable: false, searchable: false },
            { targets: -1, orderable: false, searchable: false }
        ],
        drawCallback: function () {
            const api = this.api(), start = api.page.info().start;
            api.column(0, { page: 'current' }).nodes().each(function (cell, i) { cell.innerHTML = start + i + 1; });
        },
        language: {
            emptyTable: 'Tidak ada kampanye pada periode ini.',
            zeroRecords: 'Tidak ada data yang cocok',
            info: 'Menampilkan _START_ - _END_ dari _TOTAL_ kampanye',
            infoEmpty: 'Tidak ada data', infoFiltered: '(difilter dari _MAX_ data)',
            lengthMenu: 'Tampilkan _MENU_ data', search: 'Cari:',
            paginate: { first: 'Pertama', last: 'Terakhir', next: 'Selanjutnya', previous: 'Sebelumnya' }
        }
    });

    /* ---------- Periode cepat ---------- */
    function lokal(d) {
        const p = n => String(n).padStart(2, '0');
        return d.getFullYear() + '-' + p(d.getMonth() + 1) + '-' + p(d.getDate());
    }
    $('#presetPeriode').on('click', 'button', function () {
        const t = new Date(), p = this.dataset.preset;
        let dari = '', sampai = lokal(t);
        if (p === 'hari')       { dari = lokal(t); }
        else if (p === '7')     { const d = new Date(t); d.setDate(d.getDate() - 6);  dari = lokal(d); }
        else if (p === '30')    { const d = new Date(t); d.setDate(d.getDate() - 29); dari = lokal(d); }
        else if (p === 'bulan') { dari = lokal(new Date(t.getFullYear(), t.getMonth(), 1)); }
        else                    { dari = ''; sampai = ''; }       // semua waktu
        $('#dari').val(dari);
        $('#sampai').val(sampai);
        $('#formFilter').trigger('submit');
    });

    /* ---------- Grafik ---------- */
    if (typeof Chart === 'undefined') return;

    const harian = <?= json_encode($harian, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
    const elHarian = document.getElementById('chartHarian');
    if (elHarian && harian.length) {
        new Chart(elHarian, {
            type: 'bar',
            data: {
                labels: harian.map(r => r.tanggal.slice(8, 10) + '/' + r.tanggal.slice(5, 7)),
                datasets: [
                    { label: 'Terkirim', data: harian.map(r => +r.terkirim), backgroundColor: '#00a65a' },
                    { label: 'Gagal',    data: harian.map(r => +r.gagal),    backgroundColor: '#dd4b39' }
                ]
            },
            options: {
                responsive: true, maintainAspectRatio: false,
                scales: { x: { stacked: true }, y: { stacked: true, beginAtZero: true, ticks: { precision: 0 } } },
                plugins: { legend: { position: 'bottom' } }
            }
        });
    }

    const status = <?= json_encode(array_map(
        fn($kode) => ['label' => $statusMap[$kode][0], 'jumlah' => (int) ($statusCount[$kode] ?? 0)],
        array_values(array_filter(array_keys($statusMap), fn($k) => !empty($statusCount[$k])))
    ), JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
    const warna = { 'Draf': '#d2d6de', 'Terjadwal': '#00c0ef', 'Sedang dikirim': '#f39c12', 'Selesai': '#00a65a', 'Dijeda': '#3c8dbc', 'Dibatalkan': '#dd4b39' };
    const elStatus = document.getElementById('chartStatus');
    if (elStatus && status.length) {
        new Chart(elStatus, {
            type: 'doughnut',
            data: {
                labels: status.map(s => s.label),
                datasets: [{ data: status.map(s => s.jumlah), backgroundColor: status.map(s => warna[s.label] || '#999') }]
            },
            options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, cutout: '62%' }
        });
    }
});
</script>
<?= $this->endSection() ?>