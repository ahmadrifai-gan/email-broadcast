<?= $this->extend('layouts/main') ?>

<?= $this->section('page_title') ?>Kontak<?= $this->endSection() ?>

<?= $this->section('breadcrumb') ?>
<li><a href="<?= site_url('/') ?>"><i class="fa fa-dashboard"></i> Home</a></li>
<li class="active">Kontak</li>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<?php
$errors = session()->getFlashdata('errors') ?? [];
$modalAksi = session()->getFlashdata('modal_aksi'); // terisi jika validasi gagal

$kelas = static fn(string $f) => isset($errors[$f]) ? 'has-error' : '';
$pesan = static fn(string $f) => isset($errors[$f])
    ? '<span class="help-block pesan-error">' . esc($errors[$f]) . '</span>'
    : '';
?>

<?php if (session()->getFlashdata('sukses')): ?>
    <div class="alert alert-success"><?= esc(session()->getFlashdata('sukses')) ?></div>
<?php endif; ?>
<?php if (session()->getFlashdata('gagal')): ?>
    <div class="alert alert-danger"><?= esc(session()->getFlashdata('gagal')) ?></div>
<?php endif; ?>

<?php $detailImport = session()->getFlashdata('detail_import') ?? []; ?>
<?php if (! empty($detailImport)): ?>
    <div class="alert alert-warning">
        <strong>Baris yang dilewati (maks. 30 ditampilkan):</strong>
        <ul style="margin:5px 0 0 15px">
            <?php foreach ($detailImport as $d): ?>
                <li><?= esc($d) ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<div class="box box-primary">
    <div class="box-header with-border">
        <h3 class="box-title">Daftar Kontak</h3>
        <div class="box-tools pull-right">
            <!-- TOMBOL TAMBAH KONTAK (MEMBUKA MODAL PILIHAN) -->
            <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#modalPilihanTambah">
                <i class="fa fa-plus"></i> Tambah Kontak
            </button>
        </div>
    </div>

    <div class="box-body">
        <form method="get" action="<?= site_url('kontak') ?>" class="row" style="margin-bottom:15px">
            <div class="col-sm-4" style="margin-bottom:5px">
                <select name="kategori" class="form-control">
                    <option value="">Semua kategori</option>
                    <?php foreach ($daftarKategori as $kat): ?>
                        <option value="<?= $kat['id'] ?>" <?= (string) $kat['id'] === (string) $kategoriDipilih ? 'selected' : '' ?>>
                            <?= esc($kat['nama']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-sm-8">
                <div class="input-group">
                    <input type="text" name="cari" value="<?= esc($cari) ?>" class="form-control"
                        placeholder="Cari nama, perusahaan, email, no HP, kota, provinsi...">
                    <span class="input-group-btn">
                        <button class="btn btn-default" type="submit"><i class="fa fa-search"></i> Cari</button>
                    </span>
                </div>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-bordered table-hover">
                <thead>
                    <tr>
                        <th style="width:50px">No</th>
                        <th>Kategori</th>
                        <th>Nama</th>
                        <th>Perusahaan</th>
                        <th>No HP</th>
                        <th>Email</th>
                        <th>Kota / Provinsi</th>
                        <th class="text-center">Tidak Terkirim</th>
                        <th style="width:150px" class="text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($kontak)): ?>
                        <tr>
                            <td colspan="9" class="text-center text-muted">Belum ada data.</td>
                        </tr>
                    <?php else: ?>
                        <?php $no = 1 + (($pager->getCurrentPage() - 1) * $pager->getPerPage()); ?>
                        <?php foreach ($kontak as $k): ?>
                            <tr>
                                <td><?= $no++ ?></td>
                                <td><?= esc($k['kategori_nama'] ?? '-') ?></td>
                                <td><?= esc($k['nama'] ?? '-') ?></td>
                                <td><?= esc($k['nama_perusahaan'] ?? '-') ?></td>
                                <td><?= esc($k['no_hp'] ?? '-') ?></td>
                                <td><?= esc($k['email'] ?? '-') ?></td>
                                <td><?= esc(trim(($k['kota_kabupaten'] ?? '') . ', ' . ($k['provinsi'] ?? ''), ', ') ?: '-') ?>
                                </td>
                                <td class="text-center"><?= (int) ($k['jumlah_tidak_terkirim'] ?? 0) ?></td>
                                <td class="text-right">
                                    <button type="button" class="btn btn-warning btn-xs" data-toggle="modal"
                                        data-target="#modalKontak" data-judul="Ubah Kontak"
                                        data-aksi="<?= site_url('kontak/perbarui/' . $k['id']) ?>"
                                        data-kontak="<?= esc(json_encode($k), 'attr') ?>">
                                        <i class="fa fa-pencil"></i> Ubah
                                    </button>
                                    <form action="<?= site_url('kontak/hapus/' . $k['id']) ?>" method="post"
                                        style="display:inline" onsubmit="return confirm('Hapus kontak ini?')">
                                        <?= csrf_field() ?>
                                        <button type="submit" class="btn btn-danger btn-xs"><i class="fa fa-trash"></i>
                                            Hapus</button>
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

<!-- ============================================== -->
<!-- MODAL PILIHAN TAMBAH KONTAK (4 OPSI) -->
<!-- ============================================== -->
<div class="modal fade" id="modalPilihanTambah" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Tutup"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title"><i class="fa fa-plus"></i> Pilih Metode Tambah Kontak</h4>
            </div>
            <div class="modal-body text-center">
                <p class="text-muted" style="margin-bottom: 20px;">Silakan pilih metode yang ingin Anda gunakan untuk menambahkan kontak baru.</p>
                <div class="row">
                    <!-- OPSI 1: GMAPS -->
                    <!-- OPSI 1: GMAPS -->
                    <div class="col-xs-6 col-md-3" style="margin-bottom: 15px;">
                        <a href="<?= site_url('scrapper') ?>" class="btn btn-default btn-block btn-lg">
                            <i class="fa fa-google" style="color: #db4437; font-size: 24px;"></i><br>Gmaps
                        </a>
                    </div>
                    <!-- OPSI 2: IMPORT EXCEL -->
                    <div class="col-xs-6 col-md-3" style="margin-bottom: 15px;">
                        <button type="button" class="btn btn-default btn-block btn-lg" data-dismiss="modal" data-toggle="modal" data-target="#modalImport">
                            <i class="fa fa-file-excel-o" style="color: #1e7145; font-size: 24px;"></i><br>Import Excel
                        </button>
                    </div>
                    <!-- OPSI 3: MANUAL -->
                    <div class="col-xs-6 col-md-3" style="margin-bottom: 15px;">
                        <button type="button" class="btn btn-default btn-block btn-lg" data-dismiss="modal" data-toggle="modal" data-target="#modalKontak" data-judul="Tambah Kontak Manual" data-aksi="<?= site_url('kontak/simpan') ?>" data-kontak="{}">
                            <i class="fa fa-pencil" style="color: #f39c12; font-size: 24px;"></i><br>Manual
                        </button>
                    </div>
                    <!-- OPSI 4: EXCEL WEB -->
                    <div class="col-xs-6 col-md-3" style="margin-bottom: 15px;">
                        <button type="button" class="btn btn-default btn-block btn-lg" data-dismiss="modal" data-toggle="modal" data-target="#modalImportWeb">
                            <i class="fa fa-globe" style="color: #00c0ef; font-size: 24px;"></i><br>Excel Web
                        </button>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Batal</button>
            </div>
        </div>
    </div>
</div>

<!-- ============================================== -->
<!-- MODAL MANUAL (TAMBAH / UBAH) -->
<!-- ============================================== -->
<div class="modal fade" id="modalKontak" tabindex="-1" role="dialog" aria-labelledby="modalKontakJudul">
    <div class="modal-dialog modal-lg" role="document">
        <form id="formKontak" action="<?= $modalAksi ?? site_url('kontak/simpan') ?>" method="post"
            class="modal-content">
            <?= csrf_field() ?>

            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Tutup"><span
                        aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="modalKontakJudul">
                    <?= $modalAksi && str_contains($modalAksi, 'perbarui') ? 'Ubah Kontak' : 'Tambah Kontak Manual' ?>
                </h4>
            </div>

            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group <?= $kelas('kategori_id') ?>">
                            <label class="control-label" for="kategori_id">Kategori</label>
                            <select name="kategori_id" id="kategori_id" class="form-control">
                                <option value="">- Tanpa kategori -</option>
                                <?php foreach ($daftarKategori as $kat): ?>
                                    <option value="<?= $kat['id'] ?>" <?= (string) old('kategori_id') === (string) $kat['id'] ? 'selected' : '' ?>>
                                        <?= esc($kat['nama']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <?= $pesan('kategori_id') ?>
                        </div>

                        <div class="form-group <?= $kelas('nama') ?>">
                            <label class="control-label" for="nama">Nama</label>
                            <input type="text" name="nama" id="nama" maxlength="150" class="form-control"
                                value="<?= esc(old('nama', ''), 'attr') ?>">
                            <?= $pesan('nama') ?>
                        </div>

                        <div class="form-group <?= $kelas('nama_perusahaan') ?>">
                            <label class="control-label" for="nama_perusahaan">Nama perusahaan <span
                                    class="text-red">*</span></label>
                            <input type="text" name="nama_perusahaan" id="nama_perusahaan" maxlength="150" required
                                class="form-control" value="<?= esc(old('nama_perusahaan', ''), 'attr') ?>">
                            <?= $pesan('nama_perusahaan') ?>
                        </div>

                        <div class="form-group <?= $kelas('email') ?>">
                            <label class="control-label" for="email">Email <span class="text-red">*</span></label>
                            <input type="email" name="email" id="email" maxlength="254" required class="form-control"
                                value="<?= esc(old('email', ''), 'attr') ?>">
                            <?= $pesan('email') ?>
                        </div>

                        <div class="form-group <?= $kelas('no_hp') ?>">
                            <label class="control-label" for="no_hp">No HP</label>
                            <input type="text" name="no_hp" id="no_hp" maxlength="20" class="form-control"
                                value="<?= esc(old('no_hp', ''), 'attr') ?>">
                            <?= $pesan('no_hp') ?>
                        </div>

                        <div class="form-group <?= $kelas('website') ?>">
                            <label class="control-label" for="website">Website</label>
                            <input type="text" name="website" id="website" maxlength="255" class="form-control"
                                value="<?= esc(old('website', ''), 'attr') ?>">
                            <?= $pesan('website') ?>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group <?= $kelas('kota_kabupaten') ?>">
                            <label class="control-label" for="kota_kabupaten">Kota / Kabupaten</label>
                            <input type="text" name="kota_kabupaten" id="kota_kabupaten" maxlength="100"
                                class="form-control" value="<?= esc(old('kota_kabupaten', ''), 'attr') ?>">
                            <?= $pesan('kota_kabupaten') ?>
                        </div>

                        <div class="form-group <?= $kelas('provinsi') ?>">
                            <label class="control-label" for="provinsi">Provinsi</label>
                            <input type="text" name="provinsi" id="provinsi" maxlength="100" class="form-control"
                                value="<?= esc(old('provinsi', ''), 'attr') ?>">
                            <?= $pesan('provinsi') ?>
                        </div>

                        <div class="form-group">
                            <label class="control-label" for="produk_perusahaan">Produk perusahaan</label>
                            <textarea name="produk_perusahaan" id="produk_perusahaan" rows="3"
                                class="form-control"><?= esc(old('produk_perusahaan', '')) ?></textarea>
                        </div>

                        <div class="form-group <?= $kelas('sumber_data') ?>">
                            <label class="control-label" for="sumber_data">Sumber data</label>
                            <input type="text" name="sumber_data" id="sumber_data" maxlength="150" class="form-control"
                                value="<?= esc(old('sumber_data', ''), 'attr') ?>">
                            <?= $pesan('sumber_data') ?>
                        </div>

                        <div class="form-group">
                            <label class="control-label" for="catatan">Catatan</label>
                            <textarea name="catatan" id="catatan" rows="3"
                                class="form-control"><?= esc(old('catatan', '')) ?></textarea>
                        </div>

                        <div class="form-group <?= $kelas('jumlah_tidak_terkirim') ?>">
                            <label class="control-label" for="jumlah_tidak_terkirim">Jumlah tidak terkirim</label>
                            <input type="number" min="0" name="jumlah_tidak_terkirim" id="jumlah_tidak_terkirim"
                                class="form-control" value="<?= esc(old('jumlah_tidak_terkirim', '0'), 'attr') ?>">
                            <?= $pesan('jumlah_tidak_terkirim') ?>
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-primary">Simpan</button>
            </div>
        </form>
    </div>
</div>

<!-- ============================================== -->
<!-- MODAL IMPORT EXCEL -->
<!-- ============================================== -->
<div class="modal fade" id="modalImport" tabindex="-1" role="dialog" aria-labelledby="modalImportJudul">
    <div class="modal-dialog" role="document">
        <form action="<?= site_url('kontak/import') ?>" method="post" enctype="multipart/form-data" class="modal-content">
            <?= csrf_field() ?>

            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Tutup"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="modalImportJudul">Import Kontak dari Excel</h4>
            </div>

            <div class="modal-body">
                <p>
                    Gunakan file dengan header seperti template.
                    <a href="<?= site_url('kontak/template') ?>"><i class="fa fa-download"></i> Unduh template</a>
                </p>

                <div class="form-group">
                    <label for="file_excel" class="control-label">File Excel (.xlsx, .xls, .csv)</label>
                    <input type="file" name="file_excel" id="file_excel" accept=".xlsx,.xls,.csv" required>
                </div>

                <div class="checkbox">
                    <label>
                        <input type="checkbox" name="perbarui" value="1">
                        Perbarui data jika email sudah ada (jika tidak dicentang, baris tersebut dilewati)
                    </label>
                </div>

                <p class="text-muted" style="margin-bottom:0">
                    Kolom wajib: <strong>ALAMAT EMAIL</strong> dan <strong>NAMA PERUSAHAAN</strong> (atau NAMA).
                    Kategori yang belum ada akan dibuat otomatis.
                </p>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-success"><i class="fa fa-upload"></i> Import</button>
            </div>
        </form>
    </div>
</div>

<!-- ============================================== -->
<!-- MODAL GMAPS (DUMMY / PLACEHOLDER) -->
<!-- ============================================== -->
<div class="modal fade" id="modalGmaps" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                <h4 class="modal-title"><i class="fa fa-google" style="color: #db4437;"></i> Tarik Data dari Gmaps</h4>
            </div>
            <div class="modal-body">
                <div class="alert alert-info">
                    <i class="fa fa-info-circle"></i> Fitur Scrapper Gmaps sedang dalam tahap integrasi. Halaman ini akan diarahkan ke menu Scrapper Gmaps.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<!-- ============================================== -->
<!-- MODAL EXCEL WEB (DUMMY / PLACEHOLDER) -->
<!-- ============================================== -->
<div class="modal fade" id="modalImportWeb" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                <h4 class="modal-title"><i class="fa fa-globe" style="color: #00c0ef;"></i> Import Excel dari Web</h4>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label>URL File Excel</label>
                    <input type="text" class="form-control" placeholder="https://example.com/data.xlsx">
                    <small class="text-muted">Fitur ini akan mengambil file Excel langsung dari URL web.</small>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-primary" disabled>Import (Segera Hadir)</button>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    $(function() {
        var $modal = $('#modalKontak');
        var fields = [
            'kategori_id', 'nama', 'nama_perusahaan', 'email', 'no_hp', 'website',
            'kota_kabupaten', 'provinsi', 'produk_perusahaan', 'sumber_data',
            'catatan', 'jumlah_tidak_terkirim'
        ];

        // Isi modal sesuai tombol yang diklik (tambah / ubah)
        $modal.on('show.bs.modal', function(e) {
            var btn = e.relatedTarget;
            if (!btn) return; // dibuka otomatis setelah error validasi

            var $btn = $(btn);
            var data = $btn.data('kontak') || {};

            $('#formKontak').attr('action', $btn.data('aksi'));
            $('#modalKontakJudul').text($btn.data('judul'));

            fields.forEach(function(f) {
                var nilai = data[f];
                if (nilai === null || nilai === undefined) {
                    nilai = (f === 'jumlah_tidak_terkirim') ? 0 : '';
                }
                $('#' + f).val(nilai);
            });

            $modal.find('.has-error').removeClass('has-error');
            $modal.find('.pesan-error').remove();
        });

        $modal.on('shown.bs.modal', function() {
            $('#nama_perusahaan').focus();
        });

        <?php if ($modalAksi): ?>
            // Buka lagi modal jika validasi gagal
            $modal.modal('show');
        <?php endif; ?>
    });
</script>
<?= $this->endSection() ?>