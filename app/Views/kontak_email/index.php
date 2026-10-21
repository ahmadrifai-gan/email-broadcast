<?php
$errors  = session()->getFlashdata('errors');
$modal   = session()->getFlashdata('modal');   // 'tambah' | 'edit'
$editId  = session()->getFlashdata('edit_id');
$success = session()->getFlashdata('success');
$error   = session()->getFlashdata('error');
?>
<?= $this->extend('layouts/main') ?>

<?= $this->section('title') ?>Data Kontak<?= $this->endSection() ?>
<?= $this->section('page_title') ?>Data Kontak<?= $this->endSection() ?>
<?= $this->section('page_subtitle') ?>Manajemen Data Kontak<?= $this->endSection() ?>

<?= $this->section('breadcrumb') ?>
<li>
    <a href="<?= base_url() ?>">
        <i class="fa fa-dashboard"></i> Home
    </a>
</li>
<li class="active">Data Kontak</li>
<?= $this->endSection() ?>

<?= $this->section('styles') ?>
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap.min.css">
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<!-- Pesan sukses / error -->
<?php if ($success): ?>
    <div class="alert alert-success alert-dismissible">
        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
        <i class="fa fa-check"></i> <?= esc($success) ?>
    </div>
<?php endif; ?>
<?php if ($error): ?>
    <div class="alert alert-danger alert-dismissible">
        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
        <i class="fa fa-ban"></i> <?= esc($error) ?>
    </div>
<?php endif; ?>

<div class="box box-primary">

    <div class="box-header with-border">
        <h3 class="box-title">
            <i class="fa fa-address-book"></i> Data Kontak
        </h3>
        <div class="pull-right">
            <button type="button" class="btn btn-primary btn-sm"
                    data-toggle="modal" data-target="#modalTambah">
                <i class="fa fa-plus"></i> Tambah Kontak
            </button>
        </div>
    </div>

    <form action="<?= site_url('kontak/import') ?>" method="post" enctype="multipart/form-data">
    <?= csrf_field() ?>
    <input type="file" name="file_excel" accept=".xlsx,.xls" required>
    <button type="submit">Import Excel</button>
</form>

<?php if (session()->getFlashdata('import_errors')): ?>
    <ul>
        <?php foreach (session()->getFlashdata('import_errors') as $e): ?>
            <li><?= esc($e) ?></li>
        <?php endforeach ?>
    </ul>
<?php endif ?>

    <div class="box-body">
        <div class="table-responsive">
            <table id="kontakTable" class="table table-bordered table-striped table-hover">
                <thead>
                <tr>
                    <th width="50">No</th>
                    <th width="60">ID</th>
                    <th>Nama Perusahaan</th>
                    <th>Email</th>
                    <th>Domisili</th>
                    <th width="120">Aksi</th>
                </tr>
                </thead>
                <tbody>
                <?php $no = 1; foreach ($perusahaan as $row): ?>
                    <tr>
                        <td><?= $no++ ?></td>
                        <td><?= esc($row['id']) ?></td>
                        <td>
                            <span class="label label-primary" style="font-size:13px;">
                                <?= esc($row['nama_perusahaan']) ?>
                            </span>
                        </td>
                        <td><?= esc($row['email']) ?></td>
                        <td><?= esc($row['domisili']) ?></td>
                        <td class="text-center">
                            <button type="button"
                                    class="btn btn-warning btn-xs btn-edit"
                                    data-id="<?= esc($row['id'], 'attr') ?>"
                                    data-nama="<?= esc($row['nama_perusahaan'], 'attr') ?>"
                                    data-email="<?= esc($row['email'], 'attr') ?>"
                                    data-domisili="<?= esc($row['domisili'], 'attr') ?>"
                                    data-toggle="modal" data-target="#editModal">
                                <i class="fa fa-edit"></i>
                            </button>

                            <form action="<?= base_url('kontak/delete/' . $row['id']) ?>"
                                  method="post" style="display:inline-block;"
                                  onsubmit="return confirm('Yakin ingin menghapus data ini?');">
                                <?= csrf_field() ?>
                                <button type="submit" class="btn btn-danger btn-xs">
                                    <i class="fa fa-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- ============ MODAL TAMBAH ============ -->
<div class="modal fade" id="modalTambah" tabindex="-1" role="dialog" aria-labelledby="modalTambahLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form action="<?= base_url('kontak/store') ?>" method="post">
                <?= csrf_field() ?>

                <div class="modal-header bg-primary">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                    <h4 class="modal-title" id="modalTambahLabel">
                        <i class="fa fa-plus-circle"></i> Tambah Kontak
                    </h4>
                </div>

                <div class="modal-body">
                    <?php if ($errors && $modal === 'tambah'): ?>
                        <div class="alert alert-danger">
                            <ul class="list-unstyled" style="margin:0;">
                                <?php foreach ($errors as $e): ?>
                                    <li><?= esc($e) ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>

                    <div class="form-group">
                        <label for="tambah_nama"><i class="fa fa-building-o"></i> Nama Perusahaan</label>
                        <input type="text" id="tambah_nama" name="nama_perusahaan" class="form-control"
                               placeholder="Masukkan nama perusahaan" autocomplete="off" maxlength="150"
                               value="<?= $modal === 'tambah' ? esc(old('nama_perusahaan'), 'attr') : '' ?>">
                    </div>

                    <div class="form-group">
                        <label for="tambah_email"><i class="fa fa-envelope-o"></i> Email</label>
                        <input type="email" id="tambah_email" name="email" class="form-control"
                               placeholder="Masukkan email" autocomplete="off" maxlength="254"
                               value="<?= $modal === 'tambah' ? esc(old('email'), 'attr') : '' ?>">
                    </div>

                    <div class="form-group">
                        <label for="tambah_domisili"><i class="fa fa-map-marker"></i> Domisili</label>
                        <input type="text" id="tambah_domisili" name="domisili" class="form-control"
                               placeholder="Masukkan domisili" autocomplete="off" maxlength="150"
                               value="<?= $modal === 'tambah' ? esc(old('domisili'), 'attr') : '' ?>">
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">
                        <i class="fa fa-times"></i> Batal
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fa fa-save"></i> Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ============ MODAL EDIT ============ -->
<div class="modal fade" id="editModal" tabindex="-1" role="dialog" aria-labelledby="editModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form id="formEdit" action="" method="post">
                <?= csrf_field() ?>

                <div class="modal-header bg-yellow">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                    <h4 class="modal-title" id="editModalLabel">
                        <i class="fa fa-pencil-square-o"></i> Edit Kontak
                    </h4>
                </div>

                <div class="modal-body">
                    <?php if ($errors && $modal === 'edit'): ?>
                        <div class="alert alert-danger">
                            <ul class="list-unstyled" style="margin:0;">
                                <?php foreach ($errors as $e): ?>
                                    <li><?= esc($e) ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>

                    <div class="form-group">
                        <label for="edit_nama"><i class="fa fa-building-o"></i> Nama Perusahaan</label>
                        <input type="text" id="edit_nama" name="nama_perusahaan" class="form-control"
                               placeholder="Masukkan nama perusahaan" autocomplete="off" maxlength="150">
                    </div>

                    <div class="form-group">
                        <label for="edit_email"><i class="fa fa-envelope-o"></i> Email</label>
                        <input type="email" id="edit_email" name="email" class="form-control"
                               placeholder="Masukkan email" autocomplete="off" maxlength="254">
                    </div>

                    <div class="form-group">
                        <label for="edit_domisili"><i class="fa fa-map-marker"></i> Domisili</label>
                        <input type="text" id="edit_domisili" name="domisili" class="form-control"
                               placeholder="Masukkan domisili" autocomplete="off" maxlength="150">
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">
                        <i class="fa fa-times"></i> Batal
                    </button>
                    <button type="submit" class="btn btn-warning">
                        <i class="fa fa-refresh"></i> Update
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap.min.js"></script>

<script>
$(document).ready(function () {

    var baseUpdateUrl = '<?= base_url('kontak/update') ?>';

    $('#kontakTable').DataTable({
        autoWidth: false,
        pageLength: 10,
        lengthMenu: [[5, 10, 25, 50, -1], [5, 10, 25, 50, 'Semua']],
        columnDefs: [
            { targets: [0, 1], orderable: false, searchable: false },
            { targets: -1,     orderable: false, searchable: false }
        ],
        language: {
            url: '//cdn.datatables.net/plug-ins/1.13.4/i18n/id.json'
        }
    });

    // Isi modal edit dari atribut data-* tombol (event delegation)
    $(document).on('click', '.btn-edit', function () {
        var btn = $(this);
        $('#formEdit').attr('action', baseUpdateUrl + '/' + btn.data('id'));
        $('#edit_nama').val(btn.data('nama'));
        $('#edit_email').val(btn.data('email'));
        $('#edit_domisili').val(btn.data('domisili'));
    });

    <?php if ($modal === 'tambah'): ?>
    // Validasi gagal saat tambah: buka lagi modal tambah
    $('#modalTambah').modal('show');
    <?php elseif ($modal === 'edit'): ?>
    // Validasi gagal saat edit: buka lagi modal edit dengan input terakhir
    $('#formEdit').attr('action', baseUpdateUrl + '/<?= (int) $editId ?>');
    $('#edit_nama').val(<?= json_encode(old('nama_perusahaan')) ?>);
    $('#edit_email').val(<?= json_encode(old('email')) ?>);
    $('#edit_domisili').val(<?= json_encode(old('domisili')) ?>);
    $('#editModal').modal('show');
    <?php endif; ?>

});
</script>
<?= $this->endSection() ?>