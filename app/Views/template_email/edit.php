<?= $this->extend('layouts/main') ?>

<?= $this->section('title') ?>Edit Template Email<?= $this->endSection() ?>
<?= $this->section('page_title') ?>Edit Template Email<?= $this->endSection() ?>
<?= $this->section('page_subtitle') ?>Formulir Edit Data Template Email<?= $this->endSection() ?>

<?= $this->section('breadcrumb') ?>
<li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
<li><a href="<?= base_url('template-email') ?>">Template Email</a></li>
<li class="active">Edit</li>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<?php
// =====================================================
// DATA STATIS (hardcoded) - Tampilan saja tanpa database
// Di aplikasi nyata, data ini dikirim dari controller.
// =====================================================
$data = [
    'id'                 => 1,
    'nama_template'      => 'Promo Diskon Akhir Tahun',
    'kategori_template_id' => 1,
    'subjek_email'       => 'Diskon 50% Akhir Tahun!',
    'isi_html'           => '<h1>Promo Akhir Tahun</h1><p>Dapatkan diskon hingga 50%.</p>',
    'isi_teks'           => 'Promo Akhir Tahun - Diskon hingga 50%',
    'status'             => 'aktif',
    'versi'              => 2,
    'status_pemeriksaan' => 'sudah_diperiksa',
];
?>

<div class="row">
    <div class="col-md-12">

        <div class="box box-warning">
            <div class="box-header with-border">
                <h3 class="box-title"><i class="fa fa-edit"></i> Formulir Edit Template Email</h3>
            </div>

            <form action="#" method="post" id="formEdit">
                <div class="box-body">

                    <div class="form-group">
                        <label>Nama Template <span class="text-danger">*</span></label>
                        <input type="text" name="nama_template" class="form-control"
                               value="<?= esc($data['nama_template']) ?>"
                               placeholder="Masukkan nama template" maxlength="150" required>
                    </div>

                    <div class="form-group">
                        <label>Kategori Template <span class="text-danger">*</span></label>
                        <select name="kategori_template_id" class="form-control" required>
                            <option value="">-- Pilih Kategori --</option>
                            <option value="1" <?= $data['kategori_template_id'] == 1 ? 'selected' : '' ?>>Promosi</option>
                            <option value="2" <?= $data['kategori_template_id'] == 2 ? 'selected' : '' ?>>Ulang Tahun</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Subjek Email <span class="text-danger">*</span></label>
                        <input type="text" name="subjek_email" class="form-control"
                               value="<?= esc($data['subjek_email']) ?>"
                               placeholder="Masukkan subjek email" maxlength="255" required>
                    </div>

                    <div class="form-group">
                        <label>Isi HTML <span class="text-danger">*</span></label>
                        <textarea name="isi_html" class="form-control" rows="6"
                                  placeholder="Masukkan isi HTML template" required><?= esc($data['isi_html']) ?></textarea>
                    </div>

                    <div class="form-group">
                        <label>Isi Teks</label>
                        <textarea name="isi_teks" class="form-control" rows="4"
                                  placeholder="Masukkan isi teks (opsional)"><?= esc($data['isi_teks']) ?></textarea>
                    </div>

                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Status</label>
                                <select name="status" class="form-control">
                                    <option value="draf" <?= $data['status'] === 'draf' ? 'selected' : '' ?>>Draf</option>
                                    <option value="aktif" <?= $data['status'] === 'aktif' ? 'selected' : '' ?>>Aktif</option>
                                    <option value="nonaktif" <?= $data['status'] === 'nonaktif' ? 'selected' : '' ?>>Nonaktif</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Versi</label>
                                <input type="number" name="versi" class="form-control"
                                       value="<?= esc($data['versi']) ?>" min="1">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Status Pemeriksaan</label>
                                <select name="status_pemeriksaan" class="form-control">
                                    <option value="belum_diperiksa" <?= $data['status_pemeriksaan'] === 'belum_diperiksa' ? 'selected' : '' ?>>Belum Diperiksa</option>
                                    <option value="sudah_diperiksa" <?= $data['status_pemeriksaan'] === 'sudah_diperiksa' ? 'selected' : '' ?>>Sudah Diperiksa</option>
                                    <option value="perlu_perbaikan" <?= $data['status_pemeriksaan'] === 'perlu_perbaikan' ? 'selected' : '' ?>>Perlu Perbaikan</option>
                                </select>
                            </div>
                        </div>
                    </div>

                </div>

                <div class="box-footer">
                    <a href="<?= base_url('template-email') ?>" class="btn btn-default">
                        <i class="fa fa-arrow-left"></i> Kembali
                    </a>
                    <button type="submit" class="btn btn-warning pull-right">
                        <i class="fa fa-save"></i> Update
                    </button>
                </div>
            </form>
        </div>

    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
$(document).ready(function () {
    $('#formEdit').on('submit', function (e) {
        e.preventDefault();

        Swal.fire({
            icon: 'success',
            title: 'Berhasil!',
            text: 'Data berhasil diperbarui (simulasi tampilan).',
            timer: 2000,
            showConfirmButton: false
        }).then(() => {
            window.location.href = '<?= base_url('template-email') ?>';
        });
    });
});
</script>
<?= $this->endSection() ?>