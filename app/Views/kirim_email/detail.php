<?= $this->extend('layouts/main') ?>

<?= $this->section('title') ?>Detail Kampanye<?= $this->endSection() ?>
<?= $this->section('page_title') ?>Detail Kampanye<?= $this->endSection() ?>
<?= $this->section('page_subtitle') ?><?= esc($kampanye['nama_kampanye']) ?><?= $this->endSection() ?>

<?= $this->section('breadcrumb') ?>
<li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
<li><a href="<?= base_url('kirim-email') ?>">Kirim Email</a></li>
<li class="active">Detail</li>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<?php
    $status  = $kampanye['status'];
    $tipe    = $kampanye['tipe_pengiriman'] ?? 'sekarang';
    $total   = (int) $kampanye['jumlah_penerima'];
    $totalP  = (int) ($kampanye['total_putaran'] ?? 1);
    $pctAwal = $total > 0 ? (int) round((($total - (int) $sisa) / $total) * 100) : 100;

    $lblStatus = match ($status) {
        'selesai' => 'label-success', 'sedang_dikirim' => 'label-warning', 'terjadwal' => 'label-info',
        'dijeda' => 'label-primary', 'dibatalkan' => 'label-danger', default => 'label-default',
    };
    [$tipeTeks, $tipeCls, $tipeIco] = match ($tipe) {
        'sekarang_berulang' => ['Kirim langsung + berulang', 'label-danger',  'fa-repeat'],
        'terjadwal_berulang', 'berulang' => ['Terjadwal + berulang', 'label-success', 'fa-calendar-plus-o'],
        'terjadwal'         => ['Terjadwal (sekali)',        'label-info',    'fa-clock-o'],
        default             => ['Kirim langsung',            'label-default', 'fa-bolt'],
    };
    $lblRound = ['menunggu' => 'label-default', 'berjalan' => 'label-warning', 'selesai' => 'label-success', 'dilewati' => 'label-danger'];
    $bisaJalan = in_array($status, ['draf', 'terjadwal', 'sedang_dikirim'], true);

    // pengiriman (putaran) -> nomor urut, dan daftar akun tiap pengiriman
    $noPutaran = [];
    foreach ($jadwal as $jd) { $noPutaran[(int) $jd['id']] = (int) $jd['putaran_ke']; }
    $akunPutaran = [];
    foreach ($penerima as $pn) {
        $akunPutaran[(int) ($pn['jadwal_kampanye_id'] ?? 0)][] = $pn['nama_penerima'] ?: $pn['alamat_email_penerima'];
    }
?>

<?php /* Flash message ditampilkan lewat SweetAlert2 (lihat section scripts) */ ?>

<!-- Ringkasan -->
<div class="row">
    <div class="col-md-3 col-sm-6 col-xs-12">
        <div class="info-box">
            <span class="info-box-icon bg-aqua"><i class="fa fa-users"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Total Penerima</span>
                <span class="info-box-number"><?= $total ?></span>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6 col-xs-12">
        <div class="info-box">
            <span class="info-box-icon bg-green"><i class="fa fa-check"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Berhasil</span>
                <span class="info-box-number" id="berhasilText"><?= (int) $kampanye['jumlah_berhasil'] ?></span>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6 col-xs-12">
        <div class="info-box">
            <span class="info-box-icon bg-red"><i class="fa fa-times"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Gagal</span>
                <span class="info-box-number" id="gagalText"><?= (int) $kampanye['jumlah_gagal'] ?></span>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6 col-xs-12">
        <div class="info-box">
            <span class="info-box-icon bg-yellow"><i class="fa fa-hourglass-half"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Sisa Antrean</span>
                <span class="info-box-number" id="sisaText"><?= (int) $sisa ?></span>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Informasi -->
    <div class="col-md-5">
        <div class="box box-primary">
            <div class="box-header with-border">
                <h3 class="box-title"><i class="fa fa-info-circle"></i> Informasi Kampanye</h3>
                <div class="box-tools pull-right">
                    <a href="<?= base_url('kirim-email') ?>" class="btn btn-default btn-xs"><i class="fa fa-arrow-left"></i> Kembali</a>
                </div>
            </div>
            <div class="box-body no-padding">
                <table class="table">
                    <tr><th style="width:150px">Template</th><td><?= esc($template['nama_template'] ?? '-') ?></td></tr>

                    <?php /* ▼▼ SMTP ▼▼ */ ?>
                    <tr>
                        <th>SMTP Pengirim</th>
                        <td>
                            <?php if (! empty($smtpInfo)): ?>
                                <span class="label label-primary">
                                    <i class="fa fa-server"></i> <?= esc($smtpInfo['nama']) ?>
                                </span>
                                <small class="text-muted">(<?= esc($smtpInfo['from_email']) ?>)</small>
                                <?php if (! empty($kampanye['smtp_setting_id'])): ?>
                                    <br><small class="text-muted"><i class="fa fa-check"></i> dipilih khusus untuk kampanye ini</small>
                                <?php else: ?>
                                    <br><small class="text-muted"><i class="fa fa-info-circle"></i> default aktif (kampanye tidak memilih SMTP sendiri)</small>
                                <?php endif; ?>
                            <?php else: ?>
                                <span class="text-muted"><i class="fa fa-file-text-o"></i> Menggunakan konfigurasi .env</span>
                                <br><small class="text-muted">Belum ada SMTP tersimpan yang dipilih / aktif.</small>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php /* ▲▲ SMTP ▲▲ */ ?>

                    <tr><th>Subjek</th><td><?= esc($kampanye['subjek_email']) ?></td></tr>
                    <tr><th>Tipe</th><td><span class="label <?= $tipeCls ?>"><i class="fa <?= $tipeIco ?>"></i> <?= $tipeTeks ?></span></td></tr>
                    <tr><th>Status</th>
                        <td><span id="statusText" class="label <?= $lblStatus ?>"><?= esc(str_replace('_', ' ', $status)) ?></span></td></tr>
                    <?php $kec = (int) ($kampanye['kecepatan_per_menit'] ?? 0); $kecMaks = (int) ($kampanye['kecepatan_maks'] ?? 0); ?>
                    <?php if ($totalP <= 1): ?>
                    <tr><th>Kecepatan kirim</th>
                        <td>
                            <?php if ($kec > 0 && $kecMaks > $kec): ?>
                                <span class="label label-danger"><i class="fa fa-random"></i> acak <?= $kec ?>&ndash;<?= $kecMaks ?> email / menit</span>
                                <small class="text-muted">(± <?= (int) ceil($total / (($kec + $kecMaks) / 2)) ?> menit)</small>
                            <?php elseif ($kec > 0): ?>
                                <span class="label label-danger"><i class="fa fa-tachometer"></i> <?= $kec ?> email / menit</span>
                                <small class="text-muted">(± <?= (int) ceil($total / $kec) ?> menit)</small>
                            <?php else: ?>
                                Secepatnya <small class="text-muted">(tanpa batas per menit)</small>
                            <?php endif; ?>
                        </td></tr>
                    <?php endif; ?>
                    <?php if ($totalP > 1): ?>
                        <tr><th>Pola</th>
                            <td>Penerima <strong>dibagi</strong> ke <?= $totalP ?> pengiriman <small class="text-muted">(tiap akun menerima 1 kali)</small></td></tr>
                        <tr><th>Pengiriman selesai</th><td id="putaranText"><?= (int) $kampanye['jumlah_putaran'] ?> / <?= $totalP ?></td></tr>
                    <?php endif; ?>
                    <?php if ($status === 'terjadwal' && ! empty($kampanye['jadwal_pengiriman'])): ?>
                        <tr><th>Pengiriman berikutnya</th><td><?= esc($kampanye['jadwal_pengiriman']) ?></td></tr>
                    <?php endif; ?>
                    <?php if (! empty($kampanye['mulai_dikirim_pada'])): ?>
                        <tr><th>Mulai dikirim</th><td><?= esc($kampanye['mulai_dikirim_pada']) ?></td></tr>
                    <?php endif; ?>
                    <?php if (! empty($kampanye['selesai_dikirim_pada'])): ?>
                        <tr><th>Selesai</th><td><?= esc($kampanye['selesai_dikirim_pada']) ?></td></tr>
                    <?php endif; ?>
                </table>
            </div>
        </div>
    </div>

    <!-- Kontrol -->
    <div class="col-md-7">
        <div class="box box-success">
            <div class="box-header with-border">
                <h3 class="box-title"><i class="fa fa-send"></i> Pengiriman</h3>
            </div>
            <div class="box-body">

                <?php if ($status === 'terjadwal'): ?>
                    <div class="callout callout-info" style="margin-bottom:10px;">
                        <p><i class="fa fa-clock-o"></i>
                            <?= $totalP > 1 ? 'Pengiriman berikutnya' : 'Dijadwalkan' ?> pada
                            <strong><?= esc($kampanye['jadwal_pengiriman']) ?></strong>.
                            Dikirim otomatis (cron, atau selama halaman ini terbuka).</p>
                    </div>
                <?php elseif ($status === 'dijeda'): ?>
                    <div class="callout callout-warning" style="margin-bottom:10px;">
                        <p><i class="fa fa-pause"></i> Kampanye <strong>dijeda</strong>. Tidak ada email yang dikirim sampai dilanjutkan.</p>
                    </div>
                <?php elseif ($status === 'dibatalkan'): ?>
                    <div class="callout callout-danger" style="margin-bottom:10px;">
                        <p><i class="fa fa-ban"></i> Kampanye <strong>dibatalkan</strong>. Sisa jadwal tidak akan dikirim.</p>
                    </div>
                <?php endif; ?>

                <div style="margin-bottom:12px;">
                    <?php if ($status === 'terjadwal'): ?>
                        <button id="btnKirim" type="button" class="btn btn-warning"><i class="fa fa-bolt"></i> Kirim Sekarang (lewati jadwal)</button>
                    <?php elseif ($status === 'draf'): ?>
                        <button id="btnKirim" type="button" class="btn btn-success"><i class="fa fa-play"></i> Kirim Sekarang</button>
                    <?php elseif ($status === 'sedang_dikirim'): ?>
                        <button id="btnKirim" type="button" class="btn btn-success"><i class="fa fa-play"></i> Lanjutkan Kirim</button>
                    <?php endif; ?>

                    <?php if (in_array($status, ['terjadwal', 'sedang_dikirim'], true)): ?>
                        <form action="<?= base_url('kirim-email/jeda/' . $kampanye['id']) ?>" method="post" style="display:inline;"
                              data-swal-konfirmasi data-ikon="question"
                              data-judul="Jeda kampanye?"
                              data-teks="Pengiriman dihentikan sementara dan bisa dilanjutkan kapan saja."
                              data-ya="Ya, jeda" data-loading="Menjeda kampanye...">
                            <?= csrf_field() ?>
                            <button type="submit" class="btn btn-default"><i class="fa fa-pause"></i> Jeda</button>
                        </form>
                    <?php endif; ?>
                    <?php if ($status === 'dijeda'): ?>
                        <form action="<?= base_url('kirim-email/lanjut/' . $kampanye['id']) ?>" method="post" style="display:inline;">
                            <?= csrf_field() ?>
                            <button type="submit" class="btn btn-success"><i class="fa fa-play"></i> Lanjutkan</button>
                        </form>
                    <?php endif; ?>
                    <?php if (in_array($status, ['terjadwal', 'sedang_dikirim', 'dijeda'], true)): ?>
                        <form action="<?= base_url('kirim-email/batal/' . $kampanye['id']) ?>" method="post" style="display:inline;"
                              data-swal-konfirmasi data-ikon="warning" data-bahaya="1"
                              data-judul="Batalkan kampanye?"
                              data-nama="<?= esc($kampanye['nama_kampanye'], 'attr') ?>"
                              data-teks="Kampanye {nama} akan dibatalkan. Sisa jadwal dan penerima yang belum menerima email tidak akan dikirim."
                              data-ya="Ya, batalkan" data-batal="Kembali" data-loading="Membatalkan kampanye...">
                            <?= csrf_field() ?>
                            <button type="submit" class="btn btn-danger"><i class="fa fa-ban"></i> Batalkan</button>
                        </form>
                    <?php endif; ?>
                </div>

                <div class="progress progress-striped" style="height:22px;">
                    <div id="progressBar" class="progress-bar progress-bar-success" role="progressbar"
                         style="width: <?= $pctAwal ?>%; line-height:22px;"><?= $pctAwal ?>%</div>
                </div>

                <small class="text-muted">Log pengiriman</small>
                <div id="logBox" style="height:150px;overflow-y:auto;background:#1e1e1e;color:#ddd;padding:8px;border-radius:3px;font-family:Consolas,monospace;font-size:12px;"></div>
            </div>
        </div>
    </div>
</div>

<!-- Jadwal pengiriman -->
<div class="box box-warning">
    <div class="box-header with-border">
        <h3 class="box-title"><i class="fa fa-calendar"></i> Jadwal Pengiriman</h3>
        <div class="box-tools pull-right"><span class="label label-primary"><?= (int) $kampanye['jumlah_putaran'] ?> / <?= $totalP ?> selesai</span></div>
    </div>
    <div class="box-body no-padding">
        <div class="table-responsive">
            <table class="table table-striped" style="margin-bottom:0;">
                <thead>
                    <tr><th width="70">Ke-</th><th>Waktu kirim</th><th>Status</th><th class="text-center">Akun</th><th>Daftar akun</th><th>Dimulai</th><th>Selesai</th>
                        <th class="text-center">Berhasil</th><th class="text-center">Gagal</th></tr>
                </thead>
                <tbody>
                <?php if (empty($jadwal)): ?>
                    <tr><td colspan="9" class="text-center text-muted">Tidak ada data jadwal.</td></tr>
                <?php else: foreach ($jadwal as $j): ?>
                    <tr>
                        <td><strong><?= (int) $j['putaran_ke'] ?></strong></td>
                        <td><?= esc($j['waktu_kirim']) ?></td>
                        <td><span class="label <?= $lblRound[$j['status']] ?? 'label-default' ?>"><?= esc($j['status']) ?></span></td>
                        <?php $lst = $akunPutaran[(int) $j['id']] ?? []; ?>
                        <td class="text-center"><strong><?= (int) $j['jumlah_penerima'] ?></strong></td>
                        <td class="small" title="<?= esc(implode(', ', $lst)) ?>">
                            <?= esc(implode(', ', array_slice($lst, 0, 3))) ?><?= count($lst) > 3 ? ' <span class="text-muted">+' . (count($lst) - 3) . ' lainnya</span>' : '' ?>
                        </td>
                        <td><?= esc($j['dimulai_pada'] ?? '-') ?></td>
                        <td><?= esc($j['selesai_pada'] ?? '-') ?></td>
                        <td class="text-center text-green"><?= (int) $j['jumlah_berhasil'] ?></td>
                        <td class="text-center text-red"><?= (int) $j['jumlah_gagal'] ?></td>
                    </tr>
                <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Daftar penerima -->
<div class="box box-default">
    <div class="box-header with-border">
        <h3 class="box-title"><i class="fa fa-list"></i> Daftar Penerima</h3>
    </div>
    <div class="box-body">
        <div class="table-responsive">
            <table class="table table-bordered table-striped table-hover">
                <thead>
                    <tr>
                        <th width="50">#</th><th>Email</th><th>Nama</th><th class="text-center">Pengiriman</th><th>Status</th>
                        <th class="text-center">Percobaan</th><th>Terkirim Pada</th><th>Pesan</th><th width="120">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($penerima as $i => $p): ?>
                    <tr>
                        <td><?= $i + 1 ?></td>
                        <td><?= esc($p['alamat_email_penerima']) ?></td>
                        <td><?= esc($p['nama_penerima'] ?? '-') ?></td>
                        <td class="text-center"><?= isset($noPutaran[(int) ($p['jadwal_kampanye_id'] ?? 0)]) ? 'Ke-' . $noPutaran[(int) $p['jadwal_kampanye_id']] : '-' ?></td>
                        <td>
                            <?php $cls = match ($p['status_pengiriman']) {
                                'terkirim' => 'label-success', 'gagal' => 'label-danger', 'mengirim' => 'label-warning', default => 'label-default',
                            }; ?>
                            <span class="label <?= $cls ?>"><?= esc($p['status_pengiriman']) ?></span>
                        </td>
                        <td class="text-center"><?= (int) $p['jumlah_percobaan'] ?></td>
                        <td><?= esc($p['terkirim_pada'] ?? '-') ?></td>
                        <td class="small text-muted"><?= esc(mb_substr($p['pesan_kesalahan'] ?? '-', 0, 100)) ?></td>
                        <td>
                            <?php if ($p['status_pengiriman'] === 'gagal' && $status === 'selesai'): ?>
                                <form action="<?= base_url('kirim-email/kirim-ulang/' . $p['id']) ?>" method="post" style="display:inline;"
                                      data-swal-konfirmasi data-ikon="question"
                                      data-judul="Kirim ulang email?"
                                      data-nama="<?= esc($p['alamat_email_penerima'] ?? '', 'attr') ?>"
                                      data-teks="Email ke {nama} akan dikirim ulang."
                                      data-ya="Ya, kirim ulang" data-loading="Menyiapkan pengiriman ulang...">
                                    <?= csrf_field() ?>
                                    <button type="submit" class="btn btn-warning btn-xs"><i class="fa fa-refresh"></i> Kirim Ulang</button>
                                </form>
                            <?php endif; ?>
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
<?php
    $flashSwal = [
        'success' => session()->getFlashdata('success'),
        'error'   => session()->getFlashdata('error'),
        'errors'  => (array) session()->getFlashdata('errors'),
    ];
?>
<?= $this->include('layouts/swal_helper') ?>
<script>
(function () {
'use strict';

function mulai() {
    AppSwal.flash(<?= json_encode($flashSwal, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE) ?>);
    const URL_PROSES  = '<?= base_url('kirim-email/proses/' . $kampanye['id']) ?>';
    const URL_JADWAL  = '<?= base_url('kirim-email/proses-terjadwal') ?>';
    const ID          = <?= (int) $kampanye['id'] ?>;
    const TOTAL       = <?= $total ?>;
    const STATUS_AWAL = '<?= esc($status, 'js') ?>';
    const AUTO        = <?= $auto ? 'true' : 'false' ?>;

    let csrfName = '<?= csrf_header() ?>';
    let csrfHash = '<?= csrf_hash() ?>';
    let sedangJalan = false;

    const btn    = document.getElementById('btnKirim');
    const logBox = document.getElementById('logBox');
    const el     = id => document.getElementById(id);
    const sleep  = ms => new Promise(r => setTimeout(r, ms));

    function log(teks, warna) {
        const d = document.createElement('div');
        if (warna) d.style.color = warna;
        d.textContent = teks;                 // textContent -> aman dari HTML injection
        logBox.appendChild(d);
        logBox.scrollTop = logBox.scrollHeight;
    }

    function setStatus(teks, kelas) {
        const st = el('statusText');
        st.textContent = teks.replace(/_/g, ' ');
        st.className = 'label ' + kelas;
    }

    async function post(url) {
        const resp = await fetch(url, {
            method: 'POST',
            headers: { 'X-Requested-With': 'XMLHttpRequest', [csrfName]: csrfHash }
        });
        const data = await resp.json();
        if (data.csrf) csrfHash = data.csrf;
        return data;
    }

    function setBtn(disabled, html) {
        if (!btn) return;
        btn.disabled = disabled;
        btn.innerHTML = html;
    }

    // Kuota email/menit habis -> tunggu menit berikutnya dengan hitung mundur di tombol
    async function tungguKuota(detik) {
        log('⏳ Batas email per menit tercapai. Menunggu menit berikutnya (± ' + detik + ' detik)...', '#f0ad4e');
        AppSwal.toast('warning', 'Batas email per menit tercapai. Menunggu menit berikutnya...', 4500);
        for (let s = detik + 1; s > 0; s--) {
            setBtn(true, '<i class="fa fa-hourglass-half"></i> Menunggu kuota menit berikutnya (' + s + ' dtk)');
            await sleep(1000);
        }
        setBtn(true, '<i class="fa fa-spinner fa-spin"></i> Mengirim...');
    }

    async function mulaiKirim() {
        if (sedangJalan) return;
        sedangJalan = true;
        setBtn(true, '<i class="fa fa-spinner fa-spin"></i> Mengirim...');
        setStatus('sedang_dikirim', 'label-warning');
        AppSwal.toast('info', 'Pengiriman dimulai', 2200);

        let pertama = true;
        while (true) {
            let data;
            try {
                // panggilan pertama membawa ?mulai=1 (memulai putaran walau belum waktunya)
                data = await post(URL_PROSES + (pertama ? '?mulai=1' : ''));
                pertama = false;
            } catch (e) {
                log('Error jaringan / token: ' + e.message + ' (muat ulang halaman)', '#ff6b6b');
                AppSwal.info({ ikon: 'error', judul: 'Koneksi terputus', teks: 'Pengiriman terhenti karena gangguan jaringan atau token kedaluwarsa. Muat ulang halaman lalu klik "Lanjutkan Kirim".', tombol: 'Mengerti' });
                break;
            }

            if (!data.success) {
                log('ERROR: ' + data.message, '#ff6b6b');
                AppSwal.info({ ikon: 'error', judul: 'Pengiriman terhenti', teks: data.message || 'Terjadi kesalahan pada server.', tombol: 'Mengerti' });
                break;
            }

            (data.batch || []).forEach(b => {
                log(b.email + ' — ' + (b.sukses ? '✅ terkirim' : '❌ ' + b.pesan), b.sukses ? '#5fd38d' : '#ff6b6b');
            });

            el('berhasilText').textContent = data.berhasil;
            el('gagalText').textContent    = data.gagal;
            el('sisaText').textContent     = data.sisa;

            const pct = TOTAL > 0 ? Math.round(((TOTAL - data.sisa) / TOTAL) * 100) : 100;
            const bar = el('progressBar');
            bar.style.width = pct + '%';
            bar.textContent = pct + '%';

            if (data.batas_harian) {
                log('⚠ Batas kirim harian (EMAIL_DAILY_LIMIT) tercapai. Sisa akan dilanjutkan otomatis oleh cron bila jatah sudah tersedia.', '#f0ad4e');
                AppSwal.info({ ikon: 'warning', judul: 'Batas kirim harian tercapai', teks: 'Sisa penerima akan dilanjutkan otomatis bila jatah harian sudah tersedia.', tombol: 'Mengerti' });
                break;
            }

            if (data.selesai) {
                if (data.status === 'terjadwal') {
                    setStatus('terjadwal', 'label-info');
                    setBtn(true, '<i class="fa fa-check"></i> Pengiriman selesai');
                    log('— PENGIRIMAN ' + data.putaran + '/' + data.total_putaran + ' SELESAI. Berikutnya: ' + data.berikutnya + ' —', '#5bc0de');
                } else {
                    setStatus('selesai', 'label-success');
                    setBtn(true, '<i class="fa fa-check"></i> Selesai');
                    log('— SELESAI —', '#5bc0de');
                }
                const nBerhasil = parseInt(el('berhasilText').textContent, 10) || 0;
                const nGagal    = parseInt(el('gagalText').textContent, 10) || 0;
                const judulSelesai = (data.status === 'terjadwal')
                    ? 'Pengiriman ' + data.putaran + '/' + data.total_putaran + ' selesai'
                    : 'Pengiriman selesai';
                let htmlSelesai = '<table class="swal-ringkasan">' +
                    '<tr><th>Berhasil</th><td style="color:#00a65a;font-weight:700;">' + nBerhasil + ' email</td></tr>' +
                    '<tr><th>Gagal</th><td style="color:' + (nGagal > 0 ? '#dd4b39' : '#667085') + ';font-weight:700;">' + nGagal + ' email</td></tr>';
                if (data.status === 'terjadwal' && data.berikutnya) {
                    htmlSelesai += '<tr><th>Berikutnya</th><td>' + AppSwal.esc(data.berikutnya) + '</td></tr>';
                }
                htmlSelesai += '</table>';
                await AppSwal.info({ ikon: nGagal > 0 ? 'warning' : 'success', judul: judulSelesai, html: htmlSelesai, tombol: 'Lihat Hasil', timer: 4000 });
                location.href = location.pathname;
                return;
            }

            // kuota per menit habis -> tunggu sampai menit berikutnya
            if (data.tunggu_detik > 0 && !(data.batch || []).length) {
                await tungguKuota(data.tunggu_detik);
                continue;
            }

            // batch kosong = penerima sedang dipegang proses lain -> tunggu agak lama
            await sleep((data.batch || []).length ? 300 : 2000);
        }

        sedangJalan = false;
        setBtn(false, '<i class="fa fa-play"></i> Lanjutkan Kirim');
    }

    // Proses kampanye lain (hasil klaim polling) tanpa mengubah tampilan halaman ini
    async function prosesTanpaTampilan(cid) {
        while (true) {
            try {
                const d = await post('<?= base_url('kirim-email/proses') ?>/' + cid);
                if (!d.success || d.selesai || d.batas_harian) break;
                await sleep(d.tunggu_detik > 0 ? (d.tunggu_detik + 1) * 1000 : ((d.batch || []).length ? 300 : 2000));
            } catch (e) { break; }
        }
    }

    // Klik tombol kirim -> minta konfirmasi dulu (mode AUTO dari halaman sebelumnya tidak perlu konfirmasi)
    async function klikKirim() {
        if (sedangJalan) return;
        const sisa = parseInt(el('sisaText').textContent, 10) || 0;
        let judul = 'Mulai kirim sekarang?', ikon = 'question', ya = 'Ya, kirim';
        let html  = 'Email akan dikirim ke <b>' + sisa + '</b> penerima yang belum menerima. Jangan tutup halaman ini selama proses berjalan.';

        if (STATUS_AWAL === 'terjadwal') {
            judul = 'Lewati jadwal & kirim sekarang?';
            ikon  = 'warning';
            html  = 'Kampanye ini terjadwal. Jika dilanjutkan, email dikirim <b>sekarang</b> ke <b>' + sisa + '</b> penerima tanpa menunggu jadwal.';
        } else if (STATUS_AWAL === 'sedang_dikirim') {
            judul = 'Lanjutkan pengiriman?';
            ya    = 'Ya, lanjutkan';
            html  = 'Pengiriman dilanjutkan ke <b>' + sisa + '</b> penerima yang tersisa.';
        }
        const ok = await AppSwal.konfirmasi({ judul: judul, html: html, ikon: ikon, ya: ya });
        if (ok) mulaiKirim();
    }

    if (btn) btn.addEventListener('click', klikKirim);

    // Dari form "Kirim langsung" / "Kirim ulang" -> mulai otomatis
    if (AUTO && btn && ['draf', 'terjadwal', 'sedang_dikirim'].includes(STATUS_AWAL)) {
        mulaiKirim();
    }

    // Kampanye terjadwal: selama halaman terbuka, cek tiap 15 detik lalu kirim otomatis saat waktunya tiba
    if (STATUS_AWAL === 'terjadwal' && !AUTO) {
        setInterval(async () => {
            if (sedangJalan) return;
            try {
                const d = await post(URL_JADWAL);
                if (d.success && (d.ids || []).length) {
                    for (const cid of d.ids) {
                        if (cid === ID) {
                            log('Waktu pengiriman tiba, memulai kirim otomatis...', '#5bc0de');
                            await mulaiKirim();
                        } else {
                            await prosesTanpaTampilan(cid);   // kampanye lain yang ikut jatuh tempo
                        }
                    }
                }
            } catch (e) { /* coba lagi 15 detik */ }
        }, 15000);
    }
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', mulai);
} else {
    mulai();
}

})();
</script>
<?= $this->endSection() ?>