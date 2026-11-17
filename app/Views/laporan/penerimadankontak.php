<?= $this->extend('layouts/main') ?>

<?= $this->section('title') ?>Laporan Penerima & Kontak<?= $this->endSection() ?>
<?= $this->section('page_title') ?>Laporan Penerima &amp; Kontak<?= $this->endSection() ?>
<?= $this->section('page_subtitle') ?>Kontak per kategori dan riwayat penerima kampanye<?= $this->endSection() ?>

<?= $this->section('breadcrumb') ?>
<li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
<li>Laporan</li>
<li class="active">Penerima &amp; Kontak</li>
<?= $this->endSection() ?>

<?= $this->section('styles') ?>
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap.min.css">
<style>
    .lap-filter .form-group { margin-right: 10px; margin-bottom: 8px; }
    .lap-filter label { margin-right: 5px; font-weight: 600; font-size: 12px; color: #667085; }
    .lap-chart-box { position: relative; height: 300px; }
    .info-box .progress-description { font-size: 12px; }
    .lap-err { font-size: 12px; color: #a94442; }
    .nav-tabs-custom > .nav-tabs > li > a { font-weight: 600; }
    .tab-tools { margin-bottom: 10px; }
    tfoot.lap-total th { background: #f4f6f9; }
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<?php
$rp     = static fn($n) => number_format((int) $n, 0, ',', '.');
$fmtTgl = static fn($d) => $d ? date('d/m/Y H:i', strtotime($d)) : '-';
$rate   = static function ($b, $g) {
    $d = (int) $b + (int) $g;
    return $d > 0 ? str_replace('.', ',', (string) round($b / $d * 100, 1)) . '%' : '-';
};
$tabAktif = $filter['tab'];
?>

<!-- ================= FILTER ================= -->
<div class="box box-default">
    <div class="box-body lap-filter">
        <form method="get" action="<?= base_url('laporan/penerima-kontak') ?>" id="formFilter" class="form-inline">
            <input type="hidden" name="tab" id="inputTab" value="<?= esc($tabAktif) ?>">

            <div class="form-group">
                <label for="dari">Dari</label>
                <input type="date" name="dari" id="dari" class="form-control input-sm" value="<?= esc($filter['dari'] ?? '') ?>">
            </div>
            <div class="form-group">
                <label for="sampai">Sampai</label>
                <input type="date" name="sampai" id="sampai" class="form-control input-sm" value="<?= esc($filter['sampai'] ?? '') ?>">
            </div>
            <div class="form-group">
                <label for="kategori">Kategori kontak</label>
                <select name="kategori" id="kategori" class="form-control input-sm">
                    <option value="">Semua kategori</option>
                    <?php foreach ($kategoriList as $kt): ?>
                        <option value="<?= (int) $kt['id'] ?>" <?= $filter['kategori'] === (string) $kt['id'] ? 'selected' : '' ?>><?= esc($kt['nama']) ?></option>
                    <?php endforeach; ?>
                    <option value="none" <?= $filter['kategori'] === 'none' ? 'selected' : '' ?>>Tanpa kategori</option>
                </select>
            </div>
            <div class="form-group">
                <label for="kampanye">Kampanye</label>
                <select name="kampanye" id="kampanye" class="form-control input-sm" style="max-width:220px;">
                    <option value="">Semua kampanye</option>
                    <?php foreach ($kampanyeList as $kp): ?>
                        <option value="<?= (int) $kp['id'] ?>" <?= (int) $filter['kampanye'] === (int) $kp['id'] ? 'selected' : '' ?>>
                            #<?= (int) $kp['id'] ?> — <?= esc($kp['nama_kampanye']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label for="status_kontak">Status kontak</label>
                <select name="status_kontak" id="status_kontak" class="form-control input-sm">
                    <option value="">Semua</option>
                    <option value="pernah"      <?= $filter['status_kontak'] === 'pernah' ? 'selected' : '' ?>>Pernah dikirimi</option>
                    <option value="belum"       <?= $filter['status_kontak'] === 'belum' ? 'selected' : '' ?>>Belum pernah dikirimi</option>
                    <option value="bermasalah"  <?= $filter['status_kontak'] === 'bermasalah' ? 'selected' : '' ?>>Bermasalah</option>
                    <option value="tanpa_email" <?= $filter['status_kontak'] === 'tanpa_email' ? 'selected' : '' ?>>Tanpa email</option>
                </select>
            </div>
            <div class="form-group">
                <label for="status_kirim">Status pengiriman</label>
                <select name="status_kirim" id="status_kirim" class="form-control input-sm">
                    <option value="">Semua</option>
                    <option value="terkirim" <?= $filter['status_kirim'] === 'terkirim' ? 'selected' : '' ?>>Terkirim</option>
                    <option value="gagal"    <?= $filter['status_kirim'] === 'gagal' ? 'selected' : '' ?>>Gagal</option>
                    <option value="menunggu" <?= $filter['status_kirim'] === 'menunggu' ? 'selected' : '' ?>>Menunggu</option>
                </select>
            </div>
            <button type="submit" class="btn btn-primary btn-sm"><i class="fa fa-filter"></i> Terapkan</button>
            <a href="<?= base_url('laporan/penerima-kontak') ?>" class="btn btn-default btn-sm"><i class="fa fa-refresh"></i> Reset</a>
        </form>
        <small class="text-muted">
            <i class="fa fa-info-circle"></i>
            Periode &amp; kampanye memengaruhi hitungan pengiriman. <strong>Kategori</strong> dan <strong>Status kontak</strong> berlaku di tab Daftar Kontak dan Penerima;
            <strong>Status pengiriman</strong> hanya berlaku di tab Penerima Kampanye.
            Kontak <em>bermasalah</em> = pernah gagal tetapi belum pernah berhasil, atau tercatat tidak terkirim.
        </small>
    </div>
</div>

<!-- ================= KARTU RINGKASAN ================= -->
<div class="row">
    <div class="col-lg-2 col-md-4 col-sm-6 col-xs-12">
        <div class="info-box"><span class="info-box-icon bg-aqua"><i class="fa fa-address-book-o"></i></span>
            <div class="info-box-content"><span class="info-box-text">Total Kontak</span>
                <span class="info-box-number"><?= $rp($kartu['total_kontak']) ?></span>
                <span class="progress-description text-muted"><?= $rp($kartu['tanpa_email']) ?> tanpa email</span></div></div>
    </div>
    <div class="col-lg-2 col-md-4 col-sm-6 col-xs-12">
        <div class="info-box"><span class="info-box-icon bg-green"><i class="fa fa-paper-plane"></i></span>
            <div class="info-box-content"><span class="info-box-text">Pernah Dikirimi</span>
                <span class="info-box-number"><?= $rp($kartu['pernah_dikirim']) ?></span></div></div>
    </div>
    <div class="col-lg-2 col-md-4 col-sm-6 col-xs-12">
        <div class="info-box"><span class="info-box-icon bg-yellow"><i class="fa fa-hourglass-o"></i></span>
            <div class="info-box-content"><span class="info-box-text">Belum Pernah</span>
                <span class="info-box-number"><?= $rp($kartu['belum_dikirim']) ?></span></div></div>
    </div>
    <div class="col-lg-2 col-md-4 col-sm-6 col-xs-12">
        <div class="info-box"><span class="info-box-icon bg-red"><i class="fa fa-exclamation-triangle"></i></span>
            <div class="info-box-content"><span class="info-box-text">Bermasalah</span>
                <span class="info-box-number"><?= $rp($kartu['bermasalah']) ?></span></div></div>
    </div>
    <div class="col-lg-2 col-md-4 col-sm-6 col-xs-12">
        <div class="info-box"><span class="info-box-icon bg-light-blue"><i class="fa fa-check"></i></span>
            <div class="info-box-content"><span class="info-box-text">Email Berhasil</span>
                <span class="info-box-number"><?= $rp($kartu['email_berhasil']) ?></span></div></div>
    </div>
    <div class="col-lg-2 col-md-4 col-sm-6 col-xs-12">
        <div class="info-box"><span class="info-box-icon bg-maroon"><i class="fa fa-times"></i></span>
            <div class="info-box-content"><span class="info-box-text">Email Gagal</span>
                <span class="info-box-number"><?= $rp($kartu['email_gagal']) ?></span>
                <span class="progress-description text-muted">Keberhasilan <?= $rate($kartu['email_berhasil'], $kartu['email_gagal']) ?></span></div></div>
    </div>
</div>

<!-- ================= TAB ================= -->
<div class="nav-tabs-custom">
    <ul class="nav nav-tabs" id="tabLaporan">
        <li class="<?= $tabAktif === 'kategori' ? 'active' : '' ?>"><a href="#tabKategori" data-toggle="tab" data-tab="kategori"><i class="fa fa-folder-open"></i> Per Kategori</a></li>
        <li class="<?= $tabAktif === 'kontak' ? 'active' : '' ?>"><a href="#tabKontak" data-toggle="tab" data-tab="kontak"><i class="fa fa-address-book-o"></i> Daftar Kontak <span class="badge bg-aqua"><?= $rp(count($kontak)) ?></span></a></li>
        <li class="<?= $tabAktif === 'penerima' ? 'active' : '' ?>"><a href="#tabPenerima" data-toggle="tab" data-tab="penerima"><i class="fa fa-users"></i> Penerima Kampanye <span class="badge bg-aqua"><?= $rp($totalPenerima) ?></span></a></li>
    </ul>

    <div class="tab-content">

        <!-- ========== TAB 1: PER KATEGORI ========== -->
        <div class="tab-pane <?= $tabAktif === 'kategori' ? 'active' : '' ?>" id="tabKategori">
            <div class="tab-tools clearfix">
                <a href="<?= esc($exportBase . '/kategori' . $queryString) ?>" class="btn btn-success btn-sm pull-right"><i class="fa fa-file-excel-o"></i> Ekspor CSV</a>
            </div>
            <div class="row">
                <div class="col-md-5">
                    <?php if (empty($perKategori)): ?>
                        <p class="text-muted text-center" style="padding:60px 0;">Belum ada kategori kontak.</p>
                    <?php else: ?>
                        <div class="lap-chart-box"><canvas id="chartKategori"></canvas></div>
                    <?php endif; ?>
                </div>
                <div class="col-md-7">
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped table-hover" id="tabelKategori">
                            <thead>
                                <tr>
                                    <th>Kategori</th>
                                    <th class="text-center">Kontak</th>
                                    <th class="text-center">Pernah Dikirimi</th>
                                    <th class="text-center">Belum</th>
                                    <th class="text-center">Bermasalah</th>
                                    <th class="text-center">Berhasil</th>
                                    <th class="text-center">Gagal</th>
                                    <th class="text-center">Keberhasilan</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php $tot = ['kontak' => 0, 'pernah' => 0, 'belum' => 0, 'masalah' => 0, 'ber' => 0, 'gag' => 0]; ?>
                            <?php foreach ($perKategori as $k): ?>
                                <?php
                                $tot['kontak'] += $k['jumlah_kontak']; $tot['pernah'] += $k['pernah_dikirim']; $tot['belum'] += $k['belum_dikirim'];
                                $tot['masalah'] += $k['bermasalah'];   $tot['ber'] += $k['email_berhasil'];   $tot['gag'] += $k['email_gagal'];
                                ?>
                                <tr>
                                    <?php
                                    $linkKat = base_url('laporan/penerima-kontak') . '?' . http_build_query(array_filter(
                                        array_merge($filter, [
                                            'kategori'      => $k['kategori_id'] === 0 ? 'none' : (string) $k['kategori_id'],
                                            'status_kontak' => '',
                                            'tab'           => 'kontak',
                                        ]),
                                        fn($v) => $v !== null && $v !== ''
                                    ));
                                    ?>
                                    <td>
                                        <a href="<?= esc($linkKat) ?>" title="Lihat kontak di kategori ini">
                                            <span class="label <?= $k['kategori_id'] === 0 ? 'label-default' : 'label-primary' ?>"><?= esc($k['nama']) ?></span>
                                        </a>
                                    </td>
                                    <td class="text-center"><strong><?= $rp($k['jumlah_kontak']) ?></strong></td>
                                    <td class="text-center text-success"><?= $rp($k['pernah_dikirim']) ?></td>
                                    <td class="text-center text-yellow"><?= $rp($k['belum_dikirim']) ?></td>
                                    <td class="text-center text-danger"><?= $rp($k['bermasalah']) ?></td>
                                    <td class="text-center"><?= $rp($k['email_berhasil']) ?></td>
                                    <td class="text-center"><?= $rp($k['email_gagal']) ?></td>
                                    <td class="text-center"><?= $rate($k['email_berhasil'], $k['email_gagal']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                            <tfoot class="lap-total">
                                <tr>
                                    <th>Total</th>
                                    <th class="text-center"><?= $rp($tot['kontak']) ?></th>
                                    <th class="text-center"><?= $rp($tot['pernah']) ?></th>
                                    <th class="text-center"><?= $rp($tot['belum']) ?></th>
                                    <th class="text-center"><?= $rp($tot['masalah']) ?></th>
                                    <th class="text-center"><?= $rp($tot['ber']) ?></th>
                                    <th class="text-center"><?= $rp($tot['gag']) ?></th>
                                    <th class="text-center"><?= $rate($tot['ber'], $tot['gag']) ?></th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
            <p class="text-muted" style="margin:5px 0 0;font-size:12px;">
                <i class="fa fa-lightbulb-o"></i> Klik nama kategori untuk melihat seluruh kontaknya di tab <strong>Daftar Kontak</strong>, lalu gunakan Ekspor CSV untuk mengunduhnya.
            </p>
        </div>

        <!-- ========== TAB 2: DAFTAR KONTAK ========== -->
        <div class="tab-pane <?= $tabAktif === 'kontak' ? 'active' : '' ?>" id="tabKontak">
            <div class="tab-tools clearfix">
                <a href="<?= esc($exportBase . '/kontak' . $queryString) ?>" class="btn btn-success btn-sm pull-right"><i class="fa fa-file-excel-o"></i> Ekspor CSV</a>
            </div>
            <div class="table-responsive">
                <table id="tabelKontak" class="table table-bordered table-striped table-hover" style="width:100%;">
                    <thead>
                        <tr>
                            <th width="40">No</th>
                            <th>Nama</th>
                            <th>Perusahaan</th>
                            <th>Email</th>
                            <th>Kota</th>
                            <th>Kategori</th>
                            <th class="text-center">Dikirim</th>
                            <th class="text-center">Berhasil</th>
                            <th class="text-center">Gagal</th>
                            <th class="text-center">Kampanye</th>
                            <th>Terakhir Terkirim</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($kontak as $i => $c): ?>
                        <tr>
                            <td><?= $i + 1 ?></td>
                            <td><strong><?= esc($c['nama'] ?? '-') ?></strong></td>
                            <td><?= esc($c['nama_perusahaan'] ?? '-') ?></td>
                            <td><?= esc($c['email'] ?? '-') ?></td>
                            <td><?= esc($c['kota_kabupaten'] ?? '-') ?></td>
                            <td><span class="label <?= $c['kategori_id'] ? 'label-primary' : 'label-default' ?>"><?= esc($c['kategori_nama']) ?></span></td>
                            <td class="text-center"><?= $rp($c['total_kirim']) ?></td>
                            <td class="text-center text-success"><?= $rp($c['berhasil']) ?></td>
                            <td class="text-center text-danger"><?= $rp($c['gagal']) ?></td>
                            <td class="text-center"><?= $rp($c['jml_kampanye']) ?></td>
                            <td data-order="<?= esc($c['terakhir_terkirim'] ?? '') ?>"><?= $fmtTgl($c['terakhir_terkirim'] ?? null) ?></td>
                            <td><span class="label <?= $c['status_class'] ?>"><?= esc($c['status_label']) ?></span>
                                <?php if ($c['jumlah_tidak_terkirim'] > 0): ?>
                                    <br><small class="text-muted"><?= (int) $c['jumlah_tidak_terkirim'] ?>x tidak terkirim</small>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- ========== TAB 3: PENERIMA KAMPANYE ========== -->
        <div class="tab-pane <?= $tabAktif === 'penerima' ? 'active' : '' ?>" id="tabPenerima">
            <div class="tab-tools clearfix">
                <?php if ($totalPenerima > $limitPenerima): ?>
                    <span class="text-warning" style="font-size:12px;">
                        <i class="fa fa-warning"></i> Layar menampilkan <?= $rp($limitPenerima) ?> data terbaru dari <?= $rp($totalPenerima) ?>.
                        Persempit dengan filter, atau gunakan Ekspor CSV untuk seluruh data.
                    </span>
                <?php endif; ?>
                <a href="<?= esc($exportBase . '/penerima' . $queryString) ?>" class="btn btn-success btn-sm pull-right"><i class="fa fa-file-excel-o"></i> Ekspor CSV</a>
            </div>
            <div class="table-responsive">
                <table id="tabelPenerima" class="table table-bordered table-striped table-hover" style="width:100%;">
                    <thead>
                        <tr>
                            <th width="40">No</th>
                            <th>Waktu</th>
                            <th>Kampanye</th>
                            <th>Penerima</th>
                            <th>Kategori</th>
                            <th>Status</th>
                            <th class="text-center">Percobaan</th>
                            <th>Keterangan</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($penerima as $i => $p): ?>
                        <tr>
                            <td><?= $i + 1 ?></td>
                            <td data-order="<?= esc($p['waktu'] ?? '') ?>"><?= $fmtTgl($p['waktu'] ?? null) ?></td>
                            <td><a href="<?= base_url('kirim-email/detail/' . (int) $p['kampanye_email_id']) ?>"><?= esc($p['nama_kampanye']) ?></a></td>
                            <td>
                                <?= esc($p['alamat_email_penerima']) ?>
                                <?php if (!empty($p['nama_penerima'])): ?><br><small class="text-muted"><?= esc($p['nama_penerima']) ?></small><?php endif; ?>
                            </td>
                            <td><?= esc($p['kategori_nama']) ?></td>
                            <td><span class="label <?= $p['status_class'] ?>"><?= esc($p['status_label']) ?></span></td>
                            <td class="text-center"><?= (int) $p['jumlah_percobaan'] ?></td>
                            <td>
                                <?php if ($p['status_pengiriman'] === 'gagal'): ?>
                                    <span class="lap-err"><?= esc($p['error_ringkas']) ?></span>
                                    <button type="button" class="btn btn-default btn-xs btn-log"
                                            data-email="<?= esc($p['alamat_email_penerima'], 'attr') ?>"
                                            data-pesan="<?= esc((string) ($p['pesan_kesalahan'] ?? ''), 'attr') ?>">
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

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<?= $this->include('layouts/swal_helper') ?>
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>

<script>
$(function () {

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

    $('#tabelKontak').DataTable({
        autoWidth: false, pageLength: 10, lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, 'Semua']],
        order: [[1, 'asc']],
        columnDefs: [{ targets: 0, orderable: false, searchable: false }],
        drawCallback: nomorOtomatis, language: bahasa
    });
    $('#tabelPenerima').DataTable({
        autoWidth: false, pageLength: 10, lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, 'Semua']],
        order: [[1, 'desc']],
        columnDefs: [{ targets: 0, orderable: false, searchable: false }, { targets: -1, orderable: false }],
        drawCallback: nomorOtomatis, language: bahasa
    });

    /* ---------- Ingat tab aktif (ikut terkirim saat filter diterapkan) ---------- */
    $('#tabLaporan a[data-toggle="tab"]').on('shown.bs.tab', function () {
        $('#inputTab').val($(this).data('tab'));
        $.fn.dataTable.tables({ visible: true, api: true }).columns.adjust();
    });

    /* ---------- Detail log kegagalan ---------- */
    $(document).on('click', '.btn-log', function () {
        const email = this.dataset.email, pesan = (this.dataset.pesan || '').trim();
        Swal.fire({
            icon: 'info',
            title: 'Log Kegagalan',
            width: 760,
            confirmButtonColor: '#3085d6',
            confirmButtonText: 'Tutup',
            html: '<div style="text-align:left;font-size:13px;margin-bottom:6px;">Penerima: <b>' + AppSwal.esc(email) + '</b></div>' +
                  '<pre style="text-align:left;white-space:pre-wrap;max-height:340px;overflow:auto;font-size:12px;background:#f7f7f9;padding:10px;border-radius:6px;">' +
                  AppSwal.esc(pesan || '(tidak ada log tersimpan)') + '</pre>'
        });
    });

    /* ---------- Grafik per kategori ---------- */
    if (typeof Chart === 'undefined') return;
    const data = <?= json_encode(array_map(fn($k) => [
        'nama' => $k['nama'], 'pernah' => $k['pernah_dikirim'], 'belum' => $k['belum_dikirim'],
    ], $perKategori), JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
    const el = document.getElementById('chartKategori');
    if (el && data.length) {
        new Chart(el, {
            type: 'bar',
            data: {
                labels: data.map(d => d.nama),
                datasets: [
                    { label: 'Pernah dikirimi', data: data.map(d => d.pernah), backgroundColor: '#00a65a' },
                    { label: 'Belum pernah',    data: data.map(d => d.belum),  backgroundColor: '#f39c12' }
                ]
            },
            options: {
                indexAxis: 'y', responsive: true, maintainAspectRatio: false,
                scales: { x: { stacked: true, beginAtZero: true, ticks: { precision: 0 } }, y: { stacked: true } },
                plugins: { legend: { position: 'bottom' } }
            }
        });
    }
});
</script>
<?= $this->endSection() ?>