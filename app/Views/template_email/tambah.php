<?= $this->extend('layouts/main') ?>

<?= $this->section('title') ?>Tambah Template Email<?= $this->endSection() ?>
<?= $this->section('page_title') ?>Tambah Template Email<?= $this->endSection() ?>
<?= $this->section('page_subtitle') ?>Formulir Tambah Data Template Email<?= $this->endSection() ?>

<?= $this->section('breadcrumb') ?>
<li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
<li><a href="<?= base_url('template-email') ?>">Template Email</a></li>
<li class="active">Tambah</li>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<div class="row">
    <div class="col-md-12">

        <div class="box box-primary">
            <div class="box-header with-border">
                <h3 class="box-title"><i class="fa fa-plus"></i> Formulir Tambah Template Email</h3>
            </div>

            <form action="#" method="post" id="formTambah">
                <div class="box-body">

                    <div class="form-group">
                        <label>Nama Template <span class="text-danger">*</span></label>
                        <input type="text" name="nama_template" class="form-control"
                               placeholder="Masukkan nama template" maxlength="150" required>
                    </div>

                    <div class="form-group">
                        <label>Kategori Template <span class="text-danger">*</span></label>
                        <select name="kategori_template_id" class="form-control" required>
                            <option value="">-- Pilih Kategori --</option>
                            <option value="1">Promosi</option>
                            <option value="2">Ulang Tahun</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Subjek Email <span class="text-danger">*</span></label>
                        <input type="text" name="subjek_email" class="form-control"
                               placeholder="Masukkan subjek email" maxlength="255" required>
                    </div>

                    <div class="form-group">
                        <label>Isi HTML <span class="text-danger">*</span></label>
                        <textarea name="isi_html" class="form-control" rows="6"
                                  placeholder="Masukkan isi HTML template" required></textarea>
                    </div>

                    <div class="form-group">
                        <label>Isi Teks</label>
                        <textarea name="isi_teks" class="form-control" rows="4"
                                  placeholder="Masukkan isi teks (opsional)"></textarea>
                    </div>

                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Status</label>
                                <select name="status" class="form-control">
                                    <option value="draf">Draf</option>
                                    <option value="aktif">Aktif</option>
                                    <option value="nonaktif">Nonaktif</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Versi</label>
                                <input type="number" name="versi" class="form-control" value="1" min="1">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Status Pemeriksaan</label>
                                <select name="status_pemeriksaan" class="form-control">
                                    <option value="belum_diperiksa">Belum Diperiksa</option>
                                    <option value="sudah_diperiksa">Sudah Diperiksa</option>
                                    <option value="perlu_perbaikan">Perlu Perbaikan</option>
                                </select>
                            </div>
                        </div>
                    </div>

                </div>

                <div class="box-footer">
                    <a href="<?= base_url('template-email') ?>" class="btn btn-default">
                        <i class="fa fa-arrow-left"></i> Kembali
                    </a>
                    <button type="submit" class="btn btn-primary pull-right">
                        <i class="fa fa-save"></i> Simpan
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
    $('#formTambah').on('submit', function (e) {
        e.preventDefault();

        Swal.fire({
            icon: 'success',
            title: 'Berhasil!',
            text: 'Data berhasil disimpan (simulasi tampilan).',
            timer: 2000,
            showConfirmButton: false
        }).then(() => {
            window.location.href = '<?= base_url('template-email') ?>';
        });
    });
});
</script>
<?= $this->endSection() ?>