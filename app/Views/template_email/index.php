<?= $this->extend('layouts/main') ?>

<?= $this->section('title') ?>Data Template Email<?= $this->endSection() ?>
<?= $this->section('page_title') ?>Template Email<?= $this->endSection() ?>
<?= $this->section('page_subtitle') ?>Manajemen Data Template Email<?= $this->endSection() ?>

<?= $this->section('breadcrumb') ?>
<li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
<li class="active">Template Email</li>
<?= $this->endSection() ?>

<?= $this->section('styles') ?>
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap.min.css">
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<div class="box box-primary">
    <div class="box-header with-border">
        <h3 class="box-title"><i class="fa fa-envelope-o"></i> Data Template Email</h3>
        <div class="pull-right">
            <a href="<?= base_url('template-email/tambah') ?>" class="btn btn-primary btn-sm">
                <i class="fa fa-plus"></i> Tambah Template
            </a>
        </div>
    </div>

    <div class="box-body">

        <div class="table-responsive">
            <table id="templateTable" class="table table-bordered table-striped table-hover">
                <thead>
                    <tr>
                        <th width="50">No</th>
                        <th>Nama Template</th>
                        <th>Kategori</th>
                        <th>Subjek Email</th>
                        <th width="80">Versi</th>
                        <th width="90">Status</th>
                        <th width="130">Status Pemeriksaan</th>
                        <th width="140">Dibuat Pada</th>
                        <th width="140">Diperbarui Pada</th>
                        <th width="120">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // =====================================================
                    // DATA STATIS (hardcoded) - Tampilan saja tanpa database
                    // =====================================================
                    $templates = [
                        [
                            'id'                 => 1,
                            'nama_template'      => 'Promo Diskon Akhir Tahun',
                            'kategori'           => 'Promosi',
                            'subjek_email'       => 'Diskon 50% Akhir Tahun!',
                            'status'             => 'aktif',
                            'versi'              => 2,
                            'status_pemeriksaan' => 'sudah_diperiksa',
                            'dibuat_pada'        => '2026-10-13 07:30:00',
                            'diperbarui_pada'    => '2026-10-13 08:15:00',
                        ],
                        [
                            'id'                 => 2,
                            'nama_template'      => 'Ucapan Ulang Tahun Pelanggan',
                            'kategori'           => 'Ulang Tahun',
                            'subjek_email'       => 'Selamat Ulang Tahun!',
                            'status'             => 'draf',
                            'versi'              => 1,
                            'status_pemeriksaan' => 'belum_diperiksa',
                            'dibuat_pada'        => '2026-10-13 07:45:00',
                            'diperbarui_pada'    => '2026-10-13 07:45:00',
                        ],
                        [
                            'id'                 => 3,
                            'nama_template'      => 'Newsletter Bulanan',
                            'kategori'           => 'Promosi',
                            'subjek_email'       => 'Newsletter Bulan Ini',
                            'status'             => 'aktif',
                            'versi'              => 3,
                            'status_pemeriksaan' => 'perlu_perbaikan',
                            'dibuat_pada'        => '2026-10-13 08:00:00',
                            'diperbarui_pada'    => '2026-10-13 09:00:00',
                        ],
                    ];

                    $no = 1;
                    foreach ($templates as $t) : ?>
                        <tr>
                            <td><?= $no++ ?></td>
                            <td>
                                <span class="label label-primary" style="font-size:13px;">
                                    <?= esc($t['nama_template']) ?>
                                </span>
                            </td>
                            <td><?= esc($t['kategori']) ?></td>
                            <td><?= esc($t['subjek_email']) ?></td>
                            <td class="text-center"><?= esc($t['versi']) ?></td>
                            <td class="text-center">
                                <?php if ($t['status'] === 'aktif') : ?>
                                    <span class="label label-success">Aktif</span>
                                <?php elseif ($t['status'] === 'draf') : ?>
                                    <span class="label label-warning">Draf</span>
                                <?php else : ?>
                                    <span class="label label-default"><?= esc($t['status']) ?></span>
                                <?php endif; ?>
                            </td>
                            <td class="text-center">
                                <?php if ($t['status_pemeriksaan'] === 'sudah_diperiksa') : ?>
                                    <span class="label label-success">Sudah Diperiksa</span>
                                <?php elseif ($t['status_pemeriksaan'] === 'perlu_perbaikan') : ?>
                                    <span class="label label-danger">Perlu Perbaikan</span>
                                <?php else : ?>
                                    <span class="label label-default">Belum Diperiksa</span>
                                <?php endif; ?>
                            </td>
                            <td><?= esc($t['dibuat_pada']) ?></td>
                            <td><?= esc($t['diperbarui_pada']) ?></td>
                            <td class="text-center">
                                <a href="<?= base_url('template-email/edit/' . $t['id']) ?>"
                                   class="btn btn-warning btn-xs">
                                    <i class="fa fa-edit"></i>
                                </a>

                                <button type="button" class="btn btn-danger btn-xs btn-hapus"
                                        data-id="<?= $t['id'] ?>"
                                        data-nama="<?= esc($t['nama_template']) ?>">
                                    <i class="fa fa-trash"></i>
                                </button>
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
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
$(document).ready(function () {

    /* DataTables */
    $('#templateTable').DataTable({
        autoWidth: false,
        pageLength: 10,
        lengthMenu: [[5, 10, 25, 50, -1], [5, 10, 25, 50, 'Semua']],
        columnDefs: [
            { targets: 0,  orderable: false, searchable: false },
            { targets: -1, orderable: false, searchable: false }
        ],
        language: {
            url: '//cdn.datatables.net/plug-ins/1.13.4/i18n/id.json'
        }
    });

    /* Konfirmasi Hapus (simulasi) */
    $(document).on('click', '.btn-hapus', function () {
        let nama = $(this).data('nama');

        Swal.fire({
            title: 'Yakin ingin menghapus?',
            html: 'Data <b>' + nama + '</b> tidak dapat dikembalikan!',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Ya, Hapus!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil!',
                    text: 'Data berhasil dihapus (simulasi tampilan).',
                    timer: 2000,
                    showConfirmButton: false
                });
            }
        });
    });

});
</script>
<?= $this->endSection() ?>