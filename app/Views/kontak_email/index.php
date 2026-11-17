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

$detailImport = session()->getFlashdata('detail_import') ?? [];
$halaman      = $pager->getCurrentPage();
$totalData    = method_exists($pager, 'getTotal') ? $pager->getTotal() : count($kontak);
$totalHalaman = method_exists($pager, 'getPageCount') ? $pager->getPageCount() : 1;
$adaFilter    = ($cari !== '' && $cari !== null) || ($kategoriDipilih !== '' && $kategoriDipilih !== null);
?>

<style>
    /* ===== Tampilan halaman Kontak (meniru gaya halaman Perusahaan) ===== */
    .kp-head { display: flex; flex-wrap: wrap; gap: 12px; align-items: center; justify-content: space-between; margin-bottom: 18px; }
    .kp-sub { color: #6c757d; font-size: 13px; margin: 0; }
    .kp-head-actions { display: flex; flex-wrap: wrap; gap: 8px; }

    .kp-btn { border-radius: 6px; font-weight: 500; padding: 7px 14px; }
    .kp-btn-outline-success { color: #198754; background: #fff; border: 1px solid #198754; }
    .kp-btn-outline-success:hover, .kp-btn-outline-success:focus { color: #fff; background: #198754; }
    .kp-btn-outline-danger { color: #dc3545; background: #fff; border: 1px solid #dc3545; }
    .kp-btn-outline-danger:hover, .kp-btn-outline-danger:focus { color: #fff; background: #dc3545; }
    .kp-btn-primary { color: #fff; background: #0d6efd; border: 1px solid #0d6efd; }
    .kp-btn-primary:hover, .kp-btn-primary:focus { color: #fff; background: #0b5ed7; border-color: #0a58ca; }
    .kp-btn-secondary { color: #fff; background: #6c757d; border: 1px solid #6c757d; }
    .kp-btn-secondary:hover, .kp-btn-secondary:focus { color: #fff; background: #5c636a; }

    .kp-alert { border-radius: 8px; box-shadow: 0 1px 3px rgba(0, 0, 0, .08); }

    .kp-card { background: #fff; border: 1px solid #e7e9ed; border-radius: 10px; box-shadow: 0 1px 3px rgba(16, 24, 40, .07); margin-bottom: 20px; overflow: hidden; }
    .kp-card-head { padding: 14px 18px; border-bottom: 1px solid #e7e9ed; display: flex; align-items: center; justify-content: space-between; gap: 10px; }
    .kp-card-body { padding: 18px; }
    .kp-card-foot { padding: 12px 18px; border-top: 1px solid #e7e9ed; display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 10px; }
    .kp-total { font-weight: 600; color: #212529; }
    .kp-muted { color: #6c757d; }

    .kp-filter .input-group-addon { background: #f8f9fa; color: #6c757d; border-radius: 6px 0 0 6px; }
    .kp-filter .form-control { height: 38px; border-radius: 0 6px 6px 0; box-shadow: none; }
    .kp-filter select.form-control { border-radius: 6px; }
    .kp-filter .form-control:focus { border-color: #86b7fe; }
    .kp-filter-row > div { margin-bottom: 8px; }

    .kp-table { width: 100%; margin: 0; border-collapse: collapse; }
    .kp-table th { background: #f8f9fa; color: #495057; font-weight: 600; font-size: 13px; padding: 12px 14px; border-bottom: 1px solid #dee2e6; white-space: nowrap; }
    .kp-table td { padding: 12px 14px; border-top: 1px solid #eef0f2; vertical-align: middle; font-size: 13.5px; color: #374151; }
    .kp-table tbody tr:first-child td { border-top: 0; }
    .kp-table tbody tr:hover { background: #f6f9ff; }
    .kp-no { color: #6c757d; font-size: 12.5px; }
    .kp-name { font-weight: 600; color: #1f2937; }
    .kp-small { font-size: 12px; color: #6b7280; }
    .kp-line { max-width: 220px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
    .kp-line .fa { width: 14px; color: #9aa1ab; margin-right: 3px; }

    .kp-badge { display: inline-block; padding: 3px 9px; border-radius: 6px; font-size: 12px; font-weight: 500; border: 1px solid #dee2e6; background: #f8f9fa; color: #6c757d; line-height: 1.4; }
    .kp-badge-primary { color: #0d6efd; background: #eef4ff; border-color: #cfe0ff; }
    .kp-badge-danger { color: #dc3545; background: #fdecee; border-color: #f6c7cc; }
    .kp-badge-success { color: #198754; background: #e9f7ef; border-color: #bfe5cf; }
    a.kp-badge:hover { text-decoration: none; opacity: .85; }

    .kp-actions { display: inline-flex; gap: 4px; align-items: center; }
    .kp-actions form { margin: 0; display: inline-block; }
    .kp-icon-btn { width: 32px; height: 32px; padding: 0; display: inline-flex; align-items: center; justify-content: center; background: #fff; border: 1px solid #dee2e6; border-radius: 6px; cursor: pointer; }
    .kp-icon-btn:hover { background: #f1f3f5; }
    .kp-icon-edit { color: #0d6efd; }
    .kp-icon-del { color: #dc3545; }

    .kp-empty { padding: 44px 10px; text-align: center; color: #6c757d; }
    .kp-empty .fa { display: block; font-size: 30px; margin-bottom: 8px; }

    .kp-card-foot .pagination { margin: 0; }
    .kp-card-foot .pagination > li > a, .kp-card-foot .pagination > li > span { font-size: 13px; padding: 4px 10px; color: #0d6efd; }
    .kp-card-foot .pagination > .active > a, .kp-card-foot .pagination > .active > span { background: #0d6efd; border-color: #0d6efd; color: #fff; }

    /* Modal */
    .kp-modal .modal-content { border: 0; border-radius: 10px; overflow: hidden; }
    .kp-modal .modal-header { background: #f8f9fa; border-bottom: 1px solid #e7e9ed; padding: 14px 18px; }
    .kp-modal .modal-title { font-weight: 600; }
    .kp-modal .modal-title .fa { color: #0d6efd; margin-right: 6px; }
    .kp-modal .modal-footer { background: #fff; border-top: 1px solid #e7e9ed; }
    .kp-modal .form-control { border-radius: 0 6px 6px 0; box-shadow: none; }
    .kp-modal textarea.form-control, .kp-modal select.form-control { border-radius: 6px; }
    .kp-modal .input-group-addon { background: #f8f9fa; color: #6c757d; border-radius: 6px 0 0 6px; min-width: 40px; }
    .kp-modal label.control-label { font-weight: 600; font-size: 13px; color: #495057; }
    .kp-section { font-size: 13px; font-weight: 600; color: #0d6efd; border-bottom: 1px solid #e7e9ed; padding-bottom: 6px; margin: 0 0 14px; }
    .kp-info { background: #e8f3fe; border: 1px solid #cfe2ff; color: #084298; border-radius: 8px; padding: 10px 14px; margin-bottom: 14px; font-size: 13px; }
    .kp-drop { border: 2px dashed #ced4da; border-radius: 8px; background: #fafbfc; padding: 18px; text-align: center; }
    .kp-drop .fa { font-size: 28px; color: #198754; display: block; margin-bottom: 8px; }
    .kp-drop input[type=file] { margin: 0 auto; }
</style>

<!-- HEADER -->
<div class="kp-head">
    <div>
        <p class="kp-sub">Daftar seluruh kontak perusahaan beserta kategori, email, dan nomor HP untuk keperluan broadcast.</p>
    </div>
    <div class="kp-head-actions">
        <button type="button" class="btn kp-btn kp-btn-outline-success" data-toggle="modal" data-target="#modalImport">
            <i class="fa fa-file-excel-o"></i> Import Excel
        </button>
        <button type="button" class="btn kp-btn kp-btn-primary" data-toggle="modal" data-target="#modalKontak"
            data-judul="Tambah Kontak" data-aksi="<?= site_url('kontak/simpan') ?>" data-kontak="{}">
            <i class="fa fa-plus"></i> Tambah Kontak
        </button>
    </div>
</div>

<!-- NOTIFIKASI -->
<?php if (session()->getFlashdata('sukses')): ?>
    <div class="alert alert-success alert-dismissible kp-alert">
        <button type="button" class="close" data-dismiss="alert" aria-label="Tutup">&times;</button>
        <i class="fa fa-check-circle"></i> <?= esc(session()->getFlashdata('sukses')) ?>
    </div>
<?php endif; ?>
<?php if (session()->getFlashdata('gagal')): ?>
    <div class="alert alert-danger alert-dismissible kp-alert">
        <button type="button" class="close" data-dismiss="alert" aria-label="Tutup">&times;</button>
        <i class="fa fa-exclamation-circle"></i> <?= esc(session()->getFlashdata('gagal')) ?>
    </div>
<?php endif; ?>
<?php if (!empty($detailImport)): ?>
    <div class="alert alert-warning alert-dismissible kp-alert">
        <button type="button" class="close" data-dismiss="alert" aria-label="Tutup">&times;</button>
        <strong><i class="fa fa-warning"></i> Baris yang dilewati (maks. 30 ditampilkan):</strong>
        <ul style="margin:5px 0 0 15px">
            <?php foreach ($detailImport as $d): ?>
                <li><?= esc($d) ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<!-- FILTER & PENCARIAN -->
<div class="kp-card">
    <div class="kp-card-body">
        <form method="get" action="<?= site_url('kontak') ?>" class="kp-filter">
            <div class="row kp-filter-row">
                <div class="col-md-5">
                    <div class="input-group">
                        <span class="input-group-addon"><i class="fa fa-search"></i></span>
                        <input type="text" name="cari" value="<?= esc($cari) ?>" class="form-control"
                            placeholder="Cari nama, perusahaan, email, no HP, kota, provinsi...">
                    </div>
                </div>
                <div class="col-md-4">
                    <select name="kategori" class="form-control" onchange="this.form.submit()">
                        <option value="">Semua kategori</option>
                        <?php foreach ($daftarKategori as $kat): ?>
                            <option value="<?= $kat['id'] ?>" <?= (string) $kat['id'] === (string) $kategoriDipilih ? 'selected' : '' ?>>
                                <?= esc($kat['nama']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn kp-btn kp-btn-secondary" style="width:<?= $adaFilter ? '70' : '100' ?>%">
                        Filter
                    </button>
                    <?php if ($adaFilter): ?>
                        <a href="<?= site_url('kontak') ?>" class="btn kp-btn kp-btn-outline-danger" title="Reset filter">
                            <i class="fa fa-undo"></i>
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- TABEL DATA -->
<div class="kp-card">
    <div class="kp-card-head">
        <span class="kp-total">Total: <?= number_format($totalData) ?> Kontak</span>
    </div>

    <div class="table-responsive">
        <table class="kp-table">
            <thead>
                <tr>
                    <th style="width:60px">No</th>
                    <th>Perusahaan</th>
                    <th>Kategori</th>
                    <th>Kontak</th>
                    <th>Kota / Provinsi</th>
                    <th>Tautan</th>
                    <th class="text-center">Tidak Terkirim</th>
                    <th class="text-center" style="width:110px">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($kontak)): ?>
                    <tr>
                        <td colspan="8">
                            <div class="kp-empty">
                                <i class="fa fa-info-circle"></i>
                                Belum ada kontak yang ditemukan. Tambahkan kontak baru atau import dari Excel.
                            </div>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php $no = 1 + (($halaman - 1) * $pager->getPerPage()); ?>
                    <?php foreach ($kontak as $k): ?>
                        <?php
                        $lokasi  = trim(($k['kota_kabupaten'] ?? '') . ', ' . ($k['provinsi'] ?? ''), ', ');
                        $web     = trim((string) ($k['website'] ?? ''));
                        $adaWeb  = $web !== '' && $web !== '-';
                        $urlWeb  = $adaWeb ? (strpos($web, 'http') === 0 ? $web : 'https://' . $web) : '';
                        $gagal   = (int) ($k['jumlah_tidak_terkirim'] ?? 0);
                        ?>
                        <tr>
                            <td class="kp-no"><?= $no++ ?></td>
                            <td>
                                <div class="kp-name"><?= esc($k['nama_perusahaan'] ?? '-') ?></div>
                                <?php if (!empty($k['nama'])): ?>
                                    <div class="kp-small"><i class="fa fa-user-o"></i> <?= esc($k['nama']) ?></div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if (!empty($k['kategori_nama'])): ?>
                                    <span class="kp-badge kp-badge-primary"><?= esc($k['kategori_nama']) ?></span>
                                <?php else: ?>
                                    <span class="kp-muted">-</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="kp-line" title="<?= esc($k['email'] ?? '', 'attr') ?>">
                                    <i class="fa fa-envelope-o"></i><?= esc($k['email'] ?? '-') ?>
                                </div>
                                <div class="kp-line kp-small">
                                    <i class="fa fa-phone"></i><?= esc($k['no_hp'] ?: '-') ?>
                                </div>
                            </td>
                            <td><?= esc($lokasi ?: '-') ?></td>
                            <td>
                                <?php if ($adaWeb): ?>
                                    <a href="<?= esc($urlWeb, 'attr') ?>" target="_blank" rel="noopener" class="kp-badge kp-badge-primary"
                                        title="<?= esc($web, 'attr') ?>"><i class="fa fa-globe"></i> Web</a>
                                <?php else: ?>
                                    <span class="kp-badge"><i class="fa fa-globe"></i> -</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-center">
                                <span class="kp-badge <?= $gagal > 0 ? 'kp-badge-danger' : '' ?>"><?= $gagal ?></span>
                            </td>
                            <td class="text-center">
                                <div class="kp-actions">
                                    <button type="button" class="kp-icon-btn" title="Ubah kontak" data-toggle="modal"
                                        data-target="#modalKontak" data-judul="Ubah Kontak"
                                        data-aksi="<?= site_url('kontak/perbarui/' . $k['id']) ?>"
                                        data-kontak="<?= esc(json_encode($k), 'attr') ?>">
                                        <i class="fa fa-pencil kp-icon-edit"></i>
                                    </button>
                                    <form action="<?= site_url('kontak/hapus/' . $k['id']) ?>" method="post"
                                        onsubmit="return confirm('Hapus kontak ini?')">
                                        <?= csrf_field() ?>
                                        <button type="submit" class="kp-icon-btn" title="Hapus kontak">
                                            <i class="fa fa-trash kp-icon-del"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <div class="kp-card-foot">
        <span class="kp-muted" style="font-size:13px">
            Halaman <?= $halaman ?> dari <?= max(1, $totalHalaman) ?> (Total <?= number_format($totalData) ?> data)
        </span>
        <?= $pager->links() ?>
    </div>
</div>

<!-- Modal tambah / ubah -->
<div class="modal fade kp-modal" id="modalKontak" tabindex="-1" role="dialog" aria-labelledby="modalKontakJudul">
    <div class="modal-dialog modal-lg" role="document">
        <form id="formKontak" action="<?= esc($modalAksi ?? site_url('kontak/simpan'), 'attr') ?>" method="post"
            class="modal-content">
            <?= csrf_field() ?>

            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Tutup"><span
                        aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">
                    <i class="fa fa-address-book-o"></i>
                    <span id="modalKontakJudul"><?= $modalAksi && str_contains($modalAksi, 'perbarui') ? 'Ubah Kontak' : 'Tambah Kontak' ?></span>
                </h4>
            </div>

            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6">
                        <h5 class="kp-section">Data kontak</h5>

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
                            <div class="input-group">
                                <span class="input-group-addon"><i class="fa fa-user-o"></i></span>
                                <input type="text" name="nama" id="nama" maxlength="150" class="form-control"
                                    value="<?= esc(old('nama', ''), 'attr') ?>">
                            </div>
                            <?= $pesan('nama') ?>
                        </div>

                        <div class="form-group <?= $kelas('nama_perusahaan') ?>">
                            <label class="control-label" for="nama_perusahaan">Nama perusahaan <span
                                    class="text-red">*</span></label>
                            <div class="input-group">
                                <span class="input-group-addon"><i class="fa fa-building-o"></i></span>
                                <input type="text" name="nama_perusahaan" id="nama_perusahaan" maxlength="150" required
                                    class="form-control" value="<?= esc(old('nama_perusahaan', ''), 'attr') ?>">
                            </div>
                            <?= $pesan('nama_perusahaan') ?>
                        </div>

                        <div class="form-group <?= $kelas('email') ?>">
                            <label class="control-label" for="email">Email <span class="text-red">*</span></label>
                            <div class="input-group">
                                <span class="input-group-addon"><i class="fa fa-envelope-o"></i></span>
                                <input type="email" name="email" id="email" maxlength="254" required class="form-control"
                                    value="<?= esc(old('email', ''), 'attr') ?>">
                            </div>
                            <?= $pesan('email') ?>
                        </div>

                        <div class="form-group <?= $kelas('no_hp') ?>">
                            <label class="control-label" for="no_hp">No HP</label>
                            <div class="input-group">
                                <span class="input-group-addon"><i class="fa fa-phone"></i></span>
                                <input type="text" name="no_hp" id="no_hp" maxlength="20" class="form-control"
                                    value="<?= esc(old('no_hp', ''), 'attr') ?>">
                            </div>
                            <?= $pesan('no_hp') ?>
                        </div>

                        <div class="form-group <?= $kelas('website') ?>">
                            <label class="control-label" for="website">Website</label>
                            <div class="input-group">
                                <span class="input-group-addon"><i class="fa fa-globe"></i></span>
                                <input type="text" name="website" id="website" maxlength="255" class="form-control"
                                    value="<?= esc(old('website', ''), 'attr') ?>">
                            </div>
                            <?= $pesan('website') ?>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <h5 class="kp-section">Lokasi &amp; informasi tambahan</h5>

                        <div class="form-group <?= $kelas('kota_kabupaten') ?>">
                            <label class="control-label" for="kota_kabupaten">Kota / Kabupaten</label>
                            <div class="input-group">
                                <span class="input-group-addon"><i class="fa fa-map-marker"></i></span>
                                <input type="text" name="kota_kabupaten" id="kota_kabupaten" maxlength="100"
                                    class="form-control" value="<?= esc(old('kota_kabupaten', ''), 'attr') ?>">
                            </div>
                            <?= $pesan('kota_kabupaten') ?>
                        </div>

                        <div class="form-group <?= $kelas('provinsi') ?>">
                            <label class="control-label" for="provinsi">Provinsi</label>
                            <div class="input-group">
                                <span class="input-group-addon"><i class="fa fa-map-o"></i></span>
                                <input type="text" name="provinsi" id="provinsi" maxlength="100" class="form-control"
                                    value="<?= esc(old('provinsi', ''), 'attr') ?>">
                            </div>
                            <?= $pesan('provinsi') ?>
                        </div>

                        <div class="form-group">
                            <label class="control-label" for="produk_perusahaan">Produk perusahaan</label>
                            <textarea name="produk_perusahaan" id="produk_perusahaan" rows="3"
                                class="form-control"><?= esc(old('produk_perusahaan', '')) ?></textarea>
                        </div>

                        <div class="form-group <?= $kelas('sumber_data') ?>">
                            <label class="control-label" for="sumber_data">Sumber data</label>
                            <div class="input-group">
                                <span class="input-group-addon"><i class="fa fa-database"></i></span>
                                <input type="text" name="sumber_data" id="sumber_data" maxlength="150" class="form-control"
                                    value="<?= esc(old('sumber_data', ''), 'attr') ?>">
                            </div>
                            <?= $pesan('sumber_data') ?>
                        </div>

                        <div class="form-group">
                            <label class="control-label" for="catatan">Catatan</label>
                            <textarea name="catatan" id="catatan" rows="3"
                                class="form-control"><?= esc(old('catatan', '')) ?></textarea>
                        </div>

                        <div class="form-group <?= $kelas('jumlah_tidak_terkirim') ?>">
                            <label class="control-label" for="jumlah_tidak_terkirim">Jumlah tidak terkirim</label>
                            <div class="input-group">
                                <span class="input-group-addon"><i class="fa fa-ban"></i></span>
                                <input type="number" min="0" name="jumlah_tidak_terkirim" id="jumlah_tidak_terkirim"
                                    class="form-control" value="<?= esc(old('jumlah_tidak_terkirim', '0'), 'attr') ?>">
                            </div>
                            <?= $pesan('jumlah_tidak_terkirim') ?>
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn kp-btn kp-btn-secondary" data-dismiss="modal">Batal</button>
                <button type="submit" class="btn kp-btn kp-btn-primary"><i class="fa fa-floppy-o"></i> Simpan</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal import Excel -->
<div class="modal fade kp-modal" id="modalImport" tabindex="-1" role="dialog" aria-labelledby="modalImportJudul">
    <div class="modal-dialog" role="document">
        <form action="<?= site_url('kontak/import') ?>" method="post" enctype="multipart/form-data" class="modal-content">
            <?= csrf_field() ?>

            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Tutup"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="modalImportJudul"><i class="fa fa-file-excel-o" style="color:#198754"></i> Import Kontak dari Excel</h4>
            </div>

            <div class="modal-body">
                <div class="kp-info">
                    <i class="fa fa-info-circle"></i>
                    Gunakan file dengan header seperti template.
                    <a href="<?= site_url('kontak/template') ?>"><i class="fa fa-download"></i> Unduh template</a>
                </div>

                <div class="form-group">
                    <label for="file_excel" class="control-label">File Excel (.xlsx, .xls, .csv)</label>
                    <div class="kp-drop">
                        <i class="fa fa-cloud-upload"></i>
                        <input type="file" name="file_excel" id="file_excel" accept=".xlsx,.xls,.csv" required>
                    </div>
                </div>

                <div class="checkbox">
                    <label>
                        <input type="checkbox" name="perbarui" value="1">
                        Perbarui data jika email sudah ada (jika tidak dicentang, baris tersebut dilewati)
                    </label>
                </div>

                <p class="kp-muted" style="margin-bottom:0;font-size:13px">
                    Kolom wajib: <strong>ALAMAT EMAIL</strong> dan <strong>NAMA PERUSAHAAN</strong> (atau NAMA).
                    Kategori yang belum ada akan dibuat otomatis.
                </p>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn kp-btn kp-btn-secondary" data-dismiss="modal">Batal</button>
                <button type="submit" class="btn kp-btn kp-btn-primary" style="background:#198754;border-color:#198754">
                    <i class="fa fa-upload"></i> Import
                </button>
            </div>
        </form>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    $(function () {
        var $modal = $('#modalKontak');
        var fields = [
            'kategori_id', 'nama', 'nama_perusahaan', 'email', 'no_hp', 'website',
            'kota_kabupaten', 'provinsi', 'produk_perusahaan', 'sumber_data',
            'catatan', 'jumlah_tidak_terkirim'
        ];

        // Isi modal sesuai tombol yang diklik (tambah / ubah)
        $modal.on('show.bs.modal', function (e) {
            var btn = e.relatedTarget;
            if (!btn) return; // dibuka otomatis setelah error validasi

            var $btn = $(btn);
            var data = $btn.data('kontak') || {};

            $('#formKontak').attr('action', $btn.data('aksi'));
            $('#modalKontakJudul').text($btn.data('judul'));

            fields.forEach(function (f) {
                var nilai = data[f];
                if (nilai === null || nilai === undefined) {
                    nilai = (f === 'jumlah_tidak_terkirim') ? 0 : '';
                }
                $('#' + f).val(nilai);
            });

            $modal.find('.has-error').removeClass('has-error');
            $modal.find('.pesan-error').remove();
        });

        $modal.on('shown.bs.modal', function () { $('#nama_perusahaan').focus(); });

        <?php if ($modalAksi): ?>
            // Buka lagi modal jika validasi gagal
            $modal.modal('show');
        <?php endif; ?>
    });
</script>
<?= $this->endSection() ?>