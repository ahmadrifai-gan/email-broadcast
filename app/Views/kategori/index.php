<?= $this->extend('layouts/main') ?>

<?= $this->section('page_title') ?>Kategori<?= $this->endSection() ?>

<?= $this->section('breadcrumb') ?>
<li><a href="<?= site_url('/') ?>"><i class="fa fa-dashboard"></i> Home</a></li>
<li class="active">Kategori</li>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<?php
$errors    = session()->getFlashdata('errors') ?? [];
$modalAksi = session()->getFlashdata('modal_aksi'); // terisi jika validasi gagal
?>

<?php if (session()->getFlashdata('sukses')): ?>
    <div class="alert alert-success"><?= esc(session()->getFlashdata('sukses')) ?></div>
<?php endif; ?>
<?php if (session()->getFlashdata('gagal')): ?>
    <div class="alert alert-danger"><?= esc(session()->getFlashdata('gagal')) ?></div>
<?php endif; ?>

<div class="box box-primary">
    <div class="box-header with-border">
        <h3 class="box-title">Daftar Kategori</h3>
        <div class="box-tools pull-right">
            <button type="button" class="btn btn-primary btn-sm"
                    data-toggle="modal" data-target="#modalKategori"
                    data-judul="Tambah Kategori"
                    data-aksi="<?= site_url('kategori/simpan') ?>"
                    data-nama="">
                <i class="fa fa-plus"></i> Tambah Kategori
            </button>
        </div>
    </div>

    <div class="box-body">
        <form method="get" action="<?= site_url('kategori') ?>" style="margin-bottom:15px">
            <div class="input-group">
                <input type="text" name="cari" value="<?= esc($cari) ?>" class="form-control" placeholder="Cari kategori...">
                <span class="input-group-btn">
                    <button class="btn btn-default" type="submit"><i class="fa fa-search"></i> Cari</button>
                </span>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-bordered table-hover">
                <thead>
                    <tr>
                        <th style="width:60px">No</th>
                        <th>Nama</th>
                        <th style="width:180px">Dibuat</th>
                        <th style="width:150px" class="text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (empty($kategori)): ?>
                    <tr><td colspan="4" class="text-center text-muted">Belum ada data.</td></tr>
                <?php else: ?>
                    <?php $no = 1 + (($pager->getCurrentPage() - 1) * $pager->getPerPage()); ?>
                    <?php foreach ($kategori as $k): ?>
                        <tr>
                            <td><?= $no++ ?></td>
                            <td><?= esc($k['nama']) ?></td>
                            <td><?= esc($k['dibuat_pada'] ?? '-') ?></td>
                            <td class="text-right">
                                <button type="button" class="btn btn-warning btn-xs"
                                        data-toggle="modal" data-target="#modalKategori"
                                        data-judul="Ubah Kategori"
                                        data-aksi="<?= site_url('kategori/perbarui/' . $k['id']) ?>"
                                        data-nama="<?= esc($k['nama'], 'attr') ?>">
                                    <i class="fa fa-pencil"></i> Ubah
                                </button>
                                <form action="<?= site_url('kategori/hapus/' . $k['id']) ?>" method="post" style="display:inline"
                                      onsubmit="return confirm('Hapus kategori ini?')">
                                    <?= csrf_field() ?>
                                    <button type="submit" class="btn btn-danger btn-xs"><i class="fa fa-trash"></i> Hapus</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="box-footer clearfix">
        <?= $pager->links() ?>
    </div>
</div>

<!-- Modal tambah / ubah -->
<div class="modal fade" id="modalKategori" tabindex="-1" role="dialog" aria-labelledby="modalKategoriJudul">
    <div class="modal-dialog" role="document">
        <form id="formKategori" action="<?= $modalAksi ?? site_url('kategori/simpan') ?>" method="post" class="modal-content">
            <?= csrf_field() ?>

            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Tutup"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="modalKategoriJudul">
                    <?= $modalAksi && str_contains($modalAksi, 'perbarui') ? 'Ubah Kategori' : 'Tambah Kategori' ?>
                </h4>
            </div>

            <div class="modal-body">
                <div class="form-group <?= isset($errors['nama']) ? 'has-error' : '' ?>" id="grupNama">
                    <label for="nama" class="control-label">Nama kategori</label>
                    <input type="text" id="nama" name="nama" maxlength="100" required
                           class="form-control" value="<?= esc(old('nama', ''), 'attr') ?>">
                    <?php if (isset($errors['nama'])): ?>
                        <span class="help-block" id="pesanNama"><?= esc($errors['nama']) ?></span>
                    <?php endif; ?>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-primary">Simpan</button>
            </div>
        </form>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
$(function () {
    var $modal = $('#modalKategori');

    // Isi modal sesuai tombol yang diklik (tambah / ubah)
    $modal.on('show.bs.modal', function (e) {
        var btn = e.relatedTarget;
        if (!btn) return; // dibuka otomatis setelah error validasi

        var $btn = $(btn);
        $('#formKategori').attr('action', $btn.data('aksi'));
        $('#nama').val($btn.data('nama'));
        $('#modalKategoriJudul').text($btn.data('judul'));
        $('#grupNama').removeClass('has-error');
        $('#pesanNama').remove();
    });

    $modal.on('shown.bs.modal', function () { $('#nama').focus(); });

    <?php if ($modalAksi): ?>
    // Buka lagi modal jika validasi gagal
    $modal.modal('show');
    <?php endif; ?>
});
</script>
<?= $this->endSection() ?>