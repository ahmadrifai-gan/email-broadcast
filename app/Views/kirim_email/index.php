<?= $this->extend('layouts/main') ?>

<?= $this->section('title') ?>Kirim Email<?= $this->endSection() ?>
<?= $this->section('page_title') ?>Kirim Email<?= $this->endSection() ?>
<?= $this->section('page_subtitle') ?>Manajemen Kampanye Email<?= $this->endSection() ?>

<?= $this->section('breadcrumb') ?>
<li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
<li class="active">Kirim Email</li>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<?php
    $hitung = ['total' => count($kampanye), 'terjadwal' => 0, 'sedang_dikirim' => 0, 'selesai' => 0];
    foreach ($kampanye as $k) {
        if (isset($hitung[$k['status']])) $hitung[$k['status']]++;
    }
?>

<?php /* Flash message ditampilkan lewat SweetAlert2 (lihat section scripts) */ ?>

<!-- Ringkasan -->
<div class="row">
    <div class="col-md-3 col-sm-6 col-xs-12">
        <div class="info-box">
            <span class="info-box-icon bg-aqua"><i class="fa fa-bullhorn"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Total Kampanye</span>
                <span class="info-box-number"><?= $hitung['total'] ?></span>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6 col-xs-12">
        <div class="info-box">
            <span class="info-box-icon bg-light-blue"><i class="fa fa-clock-o"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Terjadwal</span>
                <span class="info-box-number"><?= $hitung['terjadwal'] ?></span>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6 col-xs-12">
        <div class="info-box">
            <span class="info-box-icon bg-yellow"><i class="fa fa-hourglass-half"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Sedang Dikirim</span>
                <span class="info-box-number"><?= $hitung['sedang_dikirim'] ?></span>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6 col-xs-12">
        <div class="info-box">
            <span class="info-box-icon bg-green"><i class="fa fa-check"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Selesai</span>
                <span class="info-box-number"><?= $hitung['selesai'] ?></span>
            </div>
        </div>
    </div>
</div>

<!-- Tabel kampanye -->
<div class="box box-primary">
    <div class="box-header with-border">
        <h3 class="box-title"><i class="fa fa-list-ul"></i> Daftar Kampanye</h3>
        <div class="box-tools pull-right">
            <a href="<?= base_url('kirim-email/buat') ?>" class="btn btn-primary btn-sm">
                <i class="fa fa-plus"></i> Buat Kampanye
            </a>
        </div>
    </div>
    <div class="box-body">
        <div class="table-responsive">
            <table id="kampanyeTable" class="table table-bordered table-striped table-hover" style="width:100%;">
                <thead>
                    <tr>
                        <th width="50">No</th>
                        <th>Nama Kampanye</th>
                        <th>Template</th>
                        <th width="140">SMTP</th>
                        <th>Subjek</th>
                        <th width="120">Status</th>
                        <th width="150">Tipe</th>
                        <th class="text-center" width="80">Penerima</th>
                        <th class="text-center" width="80">Berhasil</th>
                        <th class="text-center" width="80">Gagal</th>
                        <th width="150">Jadwal / Berikutnya</th>
                        <th width="100">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                <?php /* PENTING: jangan render baris "colspan" untuk data kosong -> DataTables error.
                         Pesan kosong ditangani DataTables (language.emptyTable). */ ?>
                <?php foreach ($kampanye as $i => $k): ?>
                    <?php
                        $badge = match ($k['status']) {
                            'selesai'        => 'label-success',
                            'sedang_dikirim' => 'label-warning',
                            'terjadwal'      => 'label-info',
                            'dijeda'         => 'label-primary',
                            'dibatalkan'     => 'label-danger',
                            default          => 'label-default',
                        };
                    ?>
                    <tr>
                        <td><?= $i + 1 ?></td>
                        <td><strong><?= esc($k['nama_kampanye']) ?></strong></td>
                        <td><?= esc($k['nama_template'] ?? '-') ?></td>
                        <?php /* ▼▼ SMTP ▼▼ */ ?>
                        <td>
                            <?php if (! empty($k['smtp_nama'])): ?>
                                <span class="label label-primary" title="<?= esc($k['smtp_from'] ?? '') ?>">
                                    <i class="fa fa-server"></i> <?= esc($k['smtp_nama']) ?>
                                </span>
                            <?php else: ?>
                                <small class="text-muted" title="Menggunakan SMTP yang sedang aktif (atau .env)">
                                    <i class="fa fa-server"></i> Default aktif
                                </small>
                            <?php endif; ?>
                        </td>
                        <?php /* ▲▲ SMTP ▲▲ */ ?>
                        <td><?= esc($k['subjek_email']) ?></td>
                        <td><span class="label <?= $badge ?>"><?= esc(str_replace('_', ' ', $k['status'])) ?></span></td>
                        <td>
                            <?php
                                $tp  = $k['tipe_pengiriman'] ?? 'sekarang';
                                $tot = (int) ($k['total_putaran'] ?? 1);
                                [$lbl, $cls, $ico] = match ($tp) {
                                    'sekarang_berulang' => ['Langsung + berulang', 'label-danger',  'fa-repeat'],  // dibagi ke beberapa pengiriman
                                    'terjadwal_berulang', 'berulang' => ['Terjadwal + berulang', 'label-success', 'fa-calendar-plus-o'],
                                    'terjadwal'         => ['Terjadwal',           'label-info',    'fa-clock-o'],
                                    default             => ['Langsung',            'label-default', 'fa-bolt'],
                                };
                            ?>
                            <span class="label <?= $cls ?>"><i class="fa <?= $ico ?>"></i> <?= $lbl ?></span>
                            <?php if ((int) ($k['kecepatan_per_menit'] ?? 0) > 0): ?>
                                <br><small class="text-muted"><i class="fa fa-<?= (int) ($k['kecepatan_maks'] ?? 0) > 0 ? 'random' : 'tachometer' ?>"></i> <?= (int) $k['kecepatan_per_menit'] ?><?= (int) ($k['kecepatan_maks'] ?? 0) > 0 ? '&ndash;' . (int) $k['kecepatan_maks'] : '' ?>/menit</small>
                            <?php endif; ?>
                            <?php if ($tot > 1): ?>
                                <br><small class="text-muted">Pengiriman <?= (int) ($k['jumlah_putaran'] ?? 0) ?>/<?= $tot ?></small>
                            <?php endif; ?>
                        </td>
                        <td class="text-center"><?= (int) $k['jumlah_penerima'] ?></td>
                        <td class="text-center text-success"><strong><?= (int) $k['jumlah_berhasil'] ?></strong></td>
                        <td class="text-center text-danger"><strong><?= (int) $k['jumlah_gagal'] ?></strong></td>
                        <td><?= esc($k['jadwal_pengiriman'] ?? '-') ?></td>
                        <td class="text-center" style="white-space:nowrap;">
                            <a href="<?= base_url('kirim-email/detail/' . $k['id']) ?>" class="btn btn-info btn-xs" title="Detail">
                                <i class="fa fa-eye"></i>
                            </a>
                            <form action="<?= base_url('kirim-email/hapus/' . $k['id']) ?>" method="post" style="display:inline;"
                                  data-swal-konfirmasi data-ikon="warning" data-bahaya="1"
                                  data-judul="Hapus kampanye?"
                                  data-nama="<?= esc($k['nama_kampanye'], 'attr') ?>"
                                  data-teks="<?= esc('Kampanye {nama} akan dihapus' . ($k['status'] === 'sedang_dikirim' ? ' meskipun sedang dalam proses pengiriman' : '') . '. Tindakan ini tidak dapat dibatalkan.', 'attr') ?>"
                                  data-ya="Ya, Hapus!" data-loading="Menghapus kampanye...">
                                <?= csrf_field() ?>
                                <button type="submit" class="btn btn-danger btn-xs" title="Hapus"><i class="fa fa-trash"></i></button>
                            </form>
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

/* ==========================================================
   MUAT DATATABLES SETELAH DOM SIAP
   (aman walau @section scripts dirender sebelum jQuery;
    tidak memuat ulang jika layout sudah punya DataTables)
   ========================================================== */
const DT_CSS = 'https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap.min.css';
const DT_JS  = 'https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js';
const DT_BS  = 'https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap.min.js';

function muatScript(src) {
    return new Promise((ok, gagal) => {
        const s = document.createElement('script');
        s.src = src;
        s.onload = ok;
        s.onerror = () => gagal(new Error('Gagal memuat ' + src));
        document.head.appendChild(s);
    });
}

function muatCss(href) {
    if (document.querySelector('link[href="' + href + '"]')) return;
    const l = document.createElement('link');
    l.rel = 'stylesheet';
    l.href = href;
    document.head.appendChild(l);
}

async function inisialisasiTabel() {
    const $ = window.jQuery;
    if (!$) {
        console.error('[Kampanye] jQuery tidak ditemukan. Pastikan layout memuat jQuery.');
        return;
    }

    if (!$.fn.DataTable) {
        muatCss(DT_CSS);
        await muatScript(DT_JS);
        await muatScript(DT_BS);
    }

    $('#kampanyeTable').DataTable({
        autoWidth: false,
        pageLength: 10,
        lengthMenu: [[5, 10, 25, 50, -1], [5, 10, 25, 50, 'Semua']],
        order: [],                                   // pertahankan urutan dari server (terbaru di atas)
        columnDefs: [
            { targets: 0,  orderable: false, searchable: false },
            { targets: -1, orderable: false, searchable: false }
        ],
        // Nomor urut selalu berurutan walau sedang dicari / pindah halaman
        drawCallback: function () {
            const api   = this.api();
            const start = api.page.info().start;
            api.column(0, { page: 'current' }).nodes().each(function (cell, i) {
                cell.innerHTML = start + i + 1;
            });
        },
        // Bahasa Indonesia ditulis langsung (tanpa request ke CDN -> tidak kena masalah CORS)
        language: {
            emptyTable:     '<i class="fa fa-inbox fa-2x"></i><br>Belum ada kampanye.',
            zeroRecords:    'Tidak ada data yang cocok',
            info:           'Menampilkan _START_ - _END_ dari _TOTAL_ data',
            infoEmpty:      'Tidak ada data',
            infoFiltered:   '(difilter dari _MAX_ total data)',
            lengthMenu:     'Tampilkan _MENU_ data',
            search:         'Cari:',
            processing:     'Memproses...',
            paginate: { first: 'Pertama', last: 'Terakhir', next: 'Selanjutnya', previous: 'Sebelumnya' }
        }
    });
}

function mulai() {
    AppSwal.flash(<?= json_encode($flashSwal, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE) ?>);
    inisialisasiTabel().catch(function (e) { console.error('[Kampanye] DataTables gagal:', e); });
    mulaiPolling();
}

/* ==========================================================
   AUTO-POLLING KAMPANYE TERJADWAL
   ========================================================== */
function mulaiPolling() {
    let csrfName = '<?= csrf_header() ?>';
    let csrfHash = '<?= csrf_hash() ?>';
    let sedangCek = false;

    async function post(url) {
        const resp = await fetch(url, {
            method: 'POST',
            headers: { 'X-Requested-With': 'XMLHttpRequest', [csrfName]: csrfHash }
        });
        const data = await resp.json();
        if (data.csrf) csrfHash = data.csrf;
        return data;
    }

    async function cekTerjadwal() {
        if (sedangCek) return;
        sedangCek = true;
        let tutupToast = null;
        try {
            const data = await post('<?= base_url('kirim-email/proses-terjadwal') ?>');
            if (data.success && data.ids && data.ids.length > 0) {
                tutupToast = AppSwal.progressToast('Mengirim ' + data.ids.length + ' kampanye terjadwal...');
                for (const id of data.ids) await prosesKampanye(id);
                tutupToast();
                tutupToast = null;
                await AppSwal.toast('success', 'Kampanye terjadwal selesai diproses', 1800);
                location.reload();
            }
        } catch (e) {
            console.warn('[Auto-Kirim] gagal cek terjadwal:', e);
            if (tutupToast) { tutupToast(); AppSwal.toast('error', 'Pengiriman terjadwal terhenti. Muat ulang halaman.', 5000); }
        } finally {
            sedangCek = false;
        }
    }

    async function prosesKampanye(id) {
        while (true) {
            try {
                const data = await post('<?= base_url('kirim-email/proses') ?>/' + id);
                // berhenti juga saat batas harian tercapai (sebelumnya loop tidak pernah berhenti)
                if (!data.success || data.selesai || data.batas_harian) break;
                await new Promise(r => setTimeout(r, data.tunggu_detik > 0 ? (data.tunggu_detik + 1) * 1000 : ((data.batch || []).length ? 300 : 2000)));
            } catch (e) {
                console.warn('[Auto-Kirim] proses error:', e);
                break;
            }
        }
    }

    setTimeout(cekTerjadwal, 3000);
    setInterval(cekTerjadwal, 30000);
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', mulai);
} else {
    mulai();
}

})();
</script>
<?= $this->endSection() ?>