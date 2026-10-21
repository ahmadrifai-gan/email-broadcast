<?= $this->extend('layouts/main') ?>

<?= $this->section('title') ?>Kirim Email<?= $this->endSection() ?>
<?= $this->section('page_title') ?>Kirim Email<?= $this->endSection() ?>
<?= $this->section('page_subtitle') ?>Manajemen Kampanye Email<?= $this->endSection() ?>

<?= $this->section('breadcrumb') ?>
<li><a href="<?= site_url('dashboard') ?>"><i class="fa fa-dashboard"></i> Home</a></li>
<li class="active">Kirim Email</li>
<?= $this->endSection() ?>

<?= $this->section('styles') ?>
<!-- Panggil CSS Premium untuk Stat Cards -->
<link rel="stylesheet" href="<?= base_url('assets/css/dashboard.css') ?>">
<!-- CSS DataTables -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap.min.css">
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<?php
    $hitung = ['total' => count($kampanye), 'terjadwal' => 0, 'sedang_dikirim' => 0, 'selesai' => 0];
    foreach ($kampanye as $k) {
        if (isset($hitung[$k['status']])) $hitung[$k['status']]++;
    }
?>

<!-- ============================================== -->
<!-- STAT CARDS (PREMIUM UI) -->
<!-- Menambahkan margin-top dan margin-bottom agar ada jarak -->
<!-- ============================================== -->
<div class="row gap-tight" style="margin-top: 15px; margin-bottom: 25px;">
    <div class="col-lg-3 col-sm-6">
        <div class="stat-card stat-aqua">
            <div class="stat-icon"><i class="fa fa-bullhorn"></i></div>
            <div>
                <div class="stat-label">Total Kampanye</div>
                <div class="stat-value"><?= number_format($hitung['total']) ?></div>
            </div>
            <div class="stat-footer">Semua Kampanye</div>
        </div>
    </div>

    <div class="col-lg-3 col-sm-6">
        <div class="stat-card stat-purple">
            <div class="stat-icon"><i class="fa fa-clock-o"></i></div>
            <div>
                <div class="stat-label">Terjadwal</div>
                <div class="stat-value"><?= number_format($hitung['terjadwal']) ?></div>
            </div>
            <div class="stat-footer">Menunggu Waktu Kirim</div>
        </div>
    </div>

    <div class="col-lg-3 col-sm-6">
        <div class="stat-card stat-yellow">
            <div class="stat-icon"><i class="fa fa-hourglass-half"></i></div>
            <div>
                <div class="stat-label">Sedang Dikirim</div>
                <div class="stat-value"><?= number_format($hitung['sedang_dikirim']) ?></div>
            </div>
            <div class="stat-footer">Proses Antrean</div>
        </div>
    </div>

    <div class="col-lg-3 col-sm-6">
        <div class="stat-card stat-green">
            <div class="stat-icon"><i class="fa fa-check"></i></div>
            <div>
                <div class="stat-label">Selesai</div>
                <div class="stat-value"><?= number_format($hitung['selesai']) ?></div>
            </div>
            <div class="stat-footer">Kampanye Berhasil</div>
        </div>
    </div>
</div>

<!-- ============================================== -->
<!-- TABEL KAMPANYE (STANDAR ADMINLTE) -->
<!-- ============================================== -->
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
                        <th>Subjek</th>
                        <th width="120">Status</th>
                        <th class="text-center" width="80">Penerima</th>
                        <th class="text-center" width="80">Berhasil</th>
                        <th class="text-center" width="80">Gagal</th>
                        <th width="140">Jadwal</th>
                        <th width="100">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($kampanye as $i => $k): ?>
                    <?php
                        // Menggunakan badge standar AdminLTE
                        $badge = match ($k['status']) {
                            'selesai'        => 'label-success',
                            'sedang_dikirim' => 'label-warning',
                            'terjadwal'      => 'label-info',
                            default          => 'label-default',
                        };
                    ?>
                    <tr>
                        <td><?= $i + 1 ?></td>
                        <td><strong><?= esc($k['nama_kampanye']) ?></strong></td>
                        <td><?= esc($k['nama_template'] ?? '-') ?></td>
                        <td><?= esc($k['subjek_email']) ?></td>
                        <td><span class="label <?= $badge ?>"><?= esc(str_replace('_', ' ', $k['status'])) ?></span></td>
                        <td class="text-center"><?= (int) $k['jumlah_penerima'] ?></td>
                        <td class="text-center text-success"><strong><?= (int) $k['jumlah_berhasil'] ?></strong></td>
                        <td class="text-center text-danger"><strong><?= (int) $k['jumlah_gagal'] ?></strong></td>
                        <td><?= esc($k['jadwal_pengiriman'] ?? '-') ?></td>
                        <td class="text-center" style="white-space:nowrap;">
                            <a href="<?= base_url('kirim-email/detail/' . $k['id']) ?>" class="btn btn-info btn-xs" title="Detail">
                                <i class="fa fa-eye"></i>
                            </a>
                            <form action="<?= base_url('kirim-email/hapus/' . $k['id']) ?>" method="post" class="form-delete" style="display:inline;">
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
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap.min.js"></script>
<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
$(document).ready(function () {

    $('#kampanyeTable').DataTable({
        autoWidth: false,
        pageLength: 10,
        lengthMenu: [[5, 10, 25, 50, -1], [5, 10, 25, 50, 'Semua']],
        order: [],
        columnDefs: [
            { targets: 0,  orderable: false, searchable: false },
            { targets: -1, orderable: false, searchable: false }
        ],
        language: {
            url: '//cdn.datatables.net/plug-ins/1.13.4/i18n/id.json'
        }
    });

    /* Flash message → SweetAlert2 */
    <?php if (session()->getFlashdata('success')) : ?>
        Swal.fire({ icon: 'success', title: 'Berhasil!', text: <?= json_encode(session()->getFlashdata('success')) ?>, timer: 2000, showConfirmButton: false });
    <?php endif; ?>
    <?php if (session()->getFlashdata('error')) : ?>
        Swal.fire({ icon: 'error', title: 'Gagal!', text: <?= json_encode(session()->getFlashdata('error')) ?> });
    <?php endif; ?>
    <?php if (session()->getFlashdata('errors')) : ?>
        Swal.fire({ icon: 'warning', title: 'Validasi Gagal', html: `<?= implode('<br>', array_map('esc', session()->getFlashdata('errors'))) ?>` });
    <?php endif; ?>

    /* Konfirmasi Hapus */
    $(document).on('submit', '.form-delete', function (e) {
        e.preventDefault();
        let form = this;
        Swal.fire({
            title: 'Yakin ingin menghapus?',
            text: 'Data yang dihapus tidak dapat dikembalikan!',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Ya, Hapus!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) form.submit();
        });
    });

});

/* AUTO-POLLING KAMPANYE TERJADWAL */
(function () {
    'use strict';
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
        try {
            const data = await post('<?= base_url('kirim-email/proses-terjadwal') ?>');
            if (data.success && data.ids && data.ids.length > 0) {
                for (const id of data.ids) await prosesKampanye(id);
                setTimeout(() => location.reload(), 1500);
            }
        } catch (e) {
            console.warn('[Auto-Kirim] gagal cek terjadwal:', e);
        } finally {
            sedangCek = false;
        }
    }

    async function prosesKampanye(id) {
        while (true) {
            try {
                const data = await post('<?= base_url('kirim-email/proses') ?>/' + id);
                if (!data.success || data.selesai) break;
                await new Promise(r => setTimeout(r, (data.batch || []).length ? 300 : 2000));
            } catch (e) {
                console.warn('[Auto-Kirim] proses error:', e);
                break;
            }
        }
    }

    setTimeout(cekTerjadwal, 3000);
    setInterval(cekTerjadwal, 30000);
})();
</script>
<?= $this->endSection() ?>