<?= $this->extend('layouts/main') ?>

<?= $this->section('title') ?>Detail Kampanye Email<?= $this->endSection() ?>
<?= $this->section('page_title') ?>Detail Kampanye<?= $this->endSection() ?>
<?= $this->section('page_subtitle') ?>Detail Kampanye Email<?= $this->endSection() ?>

<?= $this->section('breadcrumb') ?>
<li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
<li><a href="<?= base_url('kirim-email') ?>">Kirim Email</a></li>
<li class="active">Detail</li>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<?php
    $status  = $kampanye['status'];
    $total   = (int) $kampanye['jumlah_penerima'];
    $pctAwal = $total > 0 ? (int) round((($total - (int) $sisa) / $total) * 100) : 100;
    $bisaKirim = in_array($status, ['draf', 'sedang_dikirim', 'terjadwal'], true);
    $badgeCls = match ($status) {
        'selesai' => 'label-success', 'sedang_dikirim' => 'label-warning', 'terjadwal' => 'label-info', default => 'label-default',
    };
?>

<?php if (session()->getFlashdata('success')): ?>
    <div class="alert alert-success alert-dismissible">
        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
        <i class="icon fa fa-check"></i> <?= esc(session()->getFlashdata('success')) ?>
    </div>
<?php endif; ?>
<?php if (session()->getFlashdata('error')): ?>
    <div class="alert alert-danger alert-dismissible">
        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
        <i class="icon fa fa-ban"></i> <?= esc(session()->getFlashdata('error')) ?>
    </div>
<?php endif; ?>

<!-- Ringkasan angka -->
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
            <span class="info-box-icon bg-green"><i class="fa fa-check-circle"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Berhasil</span>
                <span class="info-box-number" id="berhasilText"><?= (int) $kampanye['jumlah_berhasil'] ?></span>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6 col-xs-12">
        <div class="info-box">
            <span class="info-box-icon bg-red"><i class="fa fa-times-circle"></i></span>
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
    <!-- Informasi kampanye -->
    <div class="col-lg-5">
        <div class="box box-primary">
            <div class="box-header with-border">
                <h3 class="box-title"><i class="fa fa-info-circle"></i> Informasi Kampanye</h3>
                <div class="box-tools pull-right">
                    <a href="<?= base_url('kirim-email') ?>" class="btn btn-default btn-sm">
                        <i class="fa fa-arrow-left"></i> Kembali
                    </a>
                </div>
            </div>
            <div class="box-body no-padding">
                <table class="table table-striped">
                    <tr><th style="width:140px">Template</th><td><?= esc($template['nama_template'] ?? '-') ?></td></tr>
                    <tr><th>Subjek</th><td><?= esc($kampanye['subjek_email']) ?></td></tr>
                    <tr><th>Status</th>
                        <td><span id="statusText" class="label <?= $badgeCls ?>"><?= esc($status) ?></span></td></tr>
                    <?php if (! empty($kampanye['jadwal_pengiriman'])): ?>
                        <tr><th>Jadwal</th><td><?= esc($kampanye['jadwal_pengiriman']) ?></td></tr>
                    <?php endif; ?>
                    <?php if (! empty($kampanye['mulai_dikirim_pada'])): ?>
                        <tr><th>Mulai Dikirim</th><td><?= esc($kampanye['mulai_dikirim_pada']) ?></td></tr>
                    <?php endif; ?>
                    <?php if (! empty($kampanye['selesai_dikirim_pada'])): ?>
                        <tr><th>Selesai</th><td><?= esc($kampanye['selesai_dikirim_pada']) ?></td></tr>
                    <?php endif; ?>
                </table>
            </div>
        </div>
    </div>

    <!-- Kontrol pengiriman -->
    <div class="col-lg-7">
        <div class="box box-success">
            <div class="box-header with-border">
                <h3 class="box-title"><i class="fa fa-send"></i> Pengiriman</h3>
            </div>
            <div class="box-body">
                <?php if ($status === 'terjadwal'): ?>
                    <div class="alert alert-info">
                        <i class="fa fa-clock-o"></i>
                        Dijadwalkan pada <strong><?= esc($kampanye['jadwal_pengiriman']) ?></strong>.
                        Dikirim otomatis saat waktunya tiba (cron, atau selama halaman ini terbuka).
                    </div>
                    <button id="btnKirim" class="btn btn-warning mb-3">
                        <i class="fa fa-bolt"></i> Kirim Sekarang (lewati jadwal)
                    </button>
                <?php elseif ($bisaKirim): ?>
                    <button id="btnKirim" class="btn btn-success mb-3">
                        <i class="fa fa-play"></i> Kirim Sekarang
                    </button>
                <?php endif; ?>

                <div class="progress active mb-3" style="height: 24px;">
                    <div id="progressBar" class="progress-bar progress-bar-success progress-bar-striped"
                         role="progressbar" style="width: <?= $pctAwal ?>%"><?= $pctAwal ?>%</div>
                </div>

                <div class="small text-muted mb-1">Log pengiriman</div>
                <div id="logBox" class="small rounded p-2 bg-dark text-light"
                     style="height:160px;overflow-y:auto;font-family:Consolas,monospace;"></div>
            </div>
        </div>
    </div>
</div>

<!-- Daftar penerima -->
<div class="box box-default">
    <div class="box-header with-border">
        <h3 class="box-title"><i class="fa fa-list-check"></i> Daftar Penerima</h3>
    </div>
    <div class="box-body no-padding">
        <div class="table-responsive">
            <table class="table table-bordered table-striped table-hover">
                <thead>
                    <tr>
                        <th width="50">#</th><th>Email</th><th>Nama</th><th>Status</th>
                        <th class="text-center" width="90">Percobaan</th><th>Terkirim Pada</th><th>Pesan</th><th width="130">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($penerima as $i => $p): ?>
                    <tr>
                        <td><?= $i + 1 ?></td>
                        <td><?= esc($p['alamat_email_penerima']) ?></td>
                        <td><?= esc($p['nama_penerima'] ?? '-') ?></td>
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
                            <?php if ($p['status_pengiriman'] === 'gagal'): ?>
                                <form action="<?= base_url('kirim-email/kirim-ulang/' . $p['id']) ?>" method="post" class="d-inline">
                                    <?= csrf_field() ?>
                                    <button type="submit" class="btn btn-warning btn-xs">
                                        <i class="fa fa-refresh"></i> Kirim Ulang
                                    </button>
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
<script>
(function () {
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
    const sleep  = ms => new Promise(r => setTimeout(r, ms));

    function log(teks, cls) {
        const d = document.createElement('div');
        if (cls) d.className = cls;
        d.textContent = teks;
        logBox.appendChild(d);
        logBox.scrollTop = logBox.scrollHeight;
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

    function setBtn(disabled, teks) {
        if (!btn) return;
        btn.disabled = disabled;
        btn.innerHTML = teks;
    }

    async function mulaiKirim() {
        if (sedangJalan) return;
        sedangJalan = true;
        setBtn(true, '<i class="fa fa-spinner fa-spin"></i> Mengirim...');

        const st = document.getElementById('statusText');
        st.textContent = 'sedang_dikirim';
        st.className = 'label label-warning';

        while (true) {
            let data;
            try {
                data = await post(URL_PROSES);
            } catch (e) {
                log('Error jaringan / token: ' + e.message + ' (muat ulang halaman)', 'text-danger');
                break;
            }

            if (!data.success) {
                log('ERROR: ' + data.message, 'text-danger');
                break;
            }

            (data.batch || []).forEach(b => {
                log(b.email + ' — ' + (b.sukses ? '✅ terkirim' : '❌ ' + b.pesan), b.sukses ? 'text-success' : 'text-danger');
            });

            document.getElementById('berhasilText').textContent = data.berhasil;
            document.getElementById('gagalText').textContent    = data.gagal;
            document.getElementById('sisaText').textContent     = data.sisa;

            const pct = TOTAL > 0 ? Math.round(((TOTAL - data.sisa) / TOTAL) * 100) : 100;
            const bar = document.getElementById('progressBar');
            bar.style.width = pct + '%';
            bar.textContent = pct + '%';

            if (data.selesai) {
                st.textContent = 'selesai';
                st.className = 'label label-success';
                setBtn(true, '<i class="fa fa-check"></i> Selesai');
                log('— SELESAI —', 'fw-bold text-primary');
                setTimeout(() => { location.href = location.pathname; }, 1500);
                return;
            }

            await sleep((data.batch || []).length ? 300 : 2000);
        }

        sedangJalan = false;
        setBtn(false, '<i class="fa fa-play"></i> Kirim Sekarang');
    }

    if (btn) btn.addEventListener('click', mulaiKirim);

    if (AUTO && btn && STATUS_AWAL !== 'selesai') {
        mulaiKirim();
    }

    if (STATUS_AWAL === 'terjadwal') {
        setInterval(async () => {
            if (sedangJalan) return;
            try {
                const d = await post(URL_JADWAL);
                if (d.success && (d.ids || []).includes(ID)) {
                    log('Waktu pengiriman tiba, memulai kirim otomatis...', 'text-info');
                    mulaiKirim();
                }
            } catch (e) { }
        }, 15000);
    }
})();
</script>
<?= $this->endSection() ?>