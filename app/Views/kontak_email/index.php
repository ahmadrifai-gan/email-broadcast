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
$adaFilter    = ($cari !== '') || ($kategoriDipilih !== '') || ($statusDipilih !== '');
?>

<style>
    /* ===== Tampilan halaman Kontak (meniru gaya halaman Perusahaan) ===== */
    .kp-head {
        display: flex;
        flex-wrap: wrap;
        gap: 12px;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 18px;
    }

    .kp-sub {
        color: #6c757d;
        font-size: 13px;
        margin: 0;
    }

    .kp-head-actions {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
    }

    .kp-btn {
        border-radius: 6px;
        font-weight: 500;
        padding: 7px 14px;
    }

    .kp-btn-outline-success {
        color: #198754;
        background: #fff;
        border: 1px solid #198754;
    }

    .kp-btn-outline-success:hover,
    .kp-btn-outline-success:focus {
        color: #fff;
        background: #198754;
    }

    .kp-btn-outline-danger {
        color: #dc3545;
        background: #fff;
        border: 1px solid #dc3545;
    }

    .kp-btn-outline-danger:hover,
    .kp-btn-outline-danger:focus {
        color: #fff;
        background: #dc3545;
    }

    .kp-btn-primary {
        color: #fff;
        background: #0d6efd;
        border: 1px solid #0d6efd;
    }

    .kp-btn-primary:hover,
    .kp-btn-primary:focus {
        color: #fff;
        background: #0b5ed7;
        border-color: #0a58ca;
    }

    .kp-btn-secondary {
        color: #fff;
        background: #6c757d;
        border: 1px solid #6c757d;
    }

    .kp-btn-secondary:hover,
    .kp-btn-secondary:focus {
        color: #fff;
        background: #5c636a;
    }

    .kp-alert {
        border-radius: 8px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, .08);
    }

    .kp-card {
        background: #fff;
        border: 1px solid #e7e9ed;
        border-radius: 10px;
        box-shadow: 0 1px 3px rgba(16, 24, 40, .07);
        margin-bottom: 20px;
        overflow: hidden;
    }

    .kp-card-head {
        padding: 14px 18px;
        border-bottom: 1px solid #e7e9ed;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
    }

    .kp-card-body {
        padding: 18px;
    }

    .kp-card-foot {
        padding: 12px 18px;
        border-top: 1px solid #e7e9ed;
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
    }

    .kp-total {
        font-weight: 600;
        color: #212529;
    }

    .kp-muted {
        color: #6c757d;
    }

    .kp-filter .input-group-addon {
        background: #f8f9fa;
        color: #6c757d;
        border-radius: 6px 0 0 6px;
    }

    .kp-filter .form-control {
        height: 38px;
        border-radius: 0 6px 6px 0;
        box-shadow: none;
    }

    .kp-filter select.form-control {
        border-radius: 6px;
    }

    .kp-filter .form-control:focus {
        border-color: #86b7fe;
    }

    .kp-filter-row>div {
        margin-bottom: 8px;
    }

    .kp-table {
        width: 100%;
        margin: 0;
        border-collapse: collapse;
    }

    .kp-table th {
        background: #f8f9fa;
        color: #495057;
        font-weight: 600;
        font-size: 13px;
        padding: 12px 14px;
        border-bottom: 1px solid #dee2e6;
        white-space: nowrap;
    }

    .kp-table td {
        padding: 12px 14px;
        border-top: 1px solid #eef0f2;
        vertical-align: middle;
        font-size: 13.5px;
        color: #374151;
    }

    .kp-table tbody tr:first-child td {
        border-top: 0;
    }

    .kp-table tbody tr:hover {
        background: #f6f9ff;
    }

    .kp-no {
        color: #6c757d;
        font-size: 12.5px;
    }

    .kp-name {
        font-weight: 600;
        color: #1f2937;
    }

    .kp-small {
        font-size: 12px;
        color: #6b7280;
    }

    .kp-line {
        max-width: 220px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .kp-line .fa {
        width: 14px;
        color: #9aa1ab;
        margin-right: 3px;
    }

    .kp-badge {
        display: inline-block;
        padding: 3px 9px;
        border-radius: 6px;
        font-size: 12px;
        font-weight: 500;
        border: 1px solid #dee2e6;
        background: #f8f9fa;
        color: #6c757d;
        line-height: 1.4;
    }

    .kp-badge-primary {
        color: #0d6efd;
        background: #eef4ff;
        border-color: #cfe0ff;
    }

    .kp-badge-danger {
        color: #dc3545;
        background: #fdecee;
        border-color: #f6c7cc;
    }

    .kp-badge-success {
        color: #198754;
        background: #e9f7ef;
        border-color: #bfe5cf;
    }

    a.kp-badge:hover {
        text-decoration: none;
        opacity: .85;
    }

    .kp-actions {
        display: inline-flex;
        gap: 4px;
        align-items: center;
    }

    .kp-actions form {
        margin: 0;
        display: inline-block;
    }

    .kp-icon-btn {
        width: 32px;
        height: 32px;
        padding: 0;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: #fff;
        border: 1px solid #dee2e6;
        border-radius: 6px;
        cursor: pointer;
    }

    .kp-icon-btn:hover {
        background: #f1f3f5;
    }

    .kp-icon-edit {
        color: #0d6efd;
    }

    .kp-icon-del {
        color: #dc3545;
    }

    .kp-empty {
        padding: 44px 10px;
        text-align: center;
        color: #6c757d;
    }

    .kp-empty .fa {
        display: block;
        font-size: 30px;
        margin-bottom: 8px;
    }

    .kp-card-foot .pagination {
        margin: 0;
    }

    .kp-card-foot .pagination>li>a,
    .kp-card-foot .pagination>li>span {
        font-size: 13px;
        padding: 4px 10px;
        color: #0d6efd;
    }

    .kp-card-foot .pagination>.active>a,
    .kp-card-foot .pagination>.active>span {
        background: #0d6efd;
        border-color: #0d6efd;
        color: #fff;
    }

    /* Modal */
    .kp-modal .modal-content {
        border: 0;
        border-radius: 10px;
        overflow: hidden;
    }

    .kp-modal .modal-header {
        background: #f8f9fa;
        border-bottom: 1px solid #e7e9ed;
        padding: 14px 18px;
    }

    .kp-modal .modal-title {
        font-weight: 600;
    }

    .kp-modal .modal-title .fa {
        color: #0d6efd;
        margin-right: 6px;
    }

    .kp-modal .modal-footer {
        background: #fff;
        border-top: 1px solid #e7e9ed;
    }

    .kp-modal .form-control {
        border-radius: 0 6px 6px 0;
        box-shadow: none;
    }

    .kp-modal textarea.form-control,
    .kp-modal select.form-control {
        border-radius: 6px;
    }

    .kp-modal .input-group-addon {
        background: #f8f9fa;
        color: #6c757d;
        border-radius: 6px 0 0 6px;
        min-width: 40px;
    }

    .kp-modal label.control-label {
        font-weight: 600;
        font-size: 13px;
        color: #495057;
    }

    .kp-section {
        font-size: 13px;
        font-weight: 600;
        color: #0d6efd;
        border-bottom: 1px solid #e7e9ed;
        padding-bottom: 6px;
        margin: 0 0 14px;
    }

    .kp-info {
        background: #e8f3fe;
        border: 1px solid #cfe2ff;
        color: #084298;
        border-radius: 8px;
        padding: 10px 14px;
        margin-bottom: 14px;
        font-size: 13px;
    }

    .kp-drop {
        border: 2px dashed #ced4da;
        border-radius: 8px;
        background: #fafbfc;
        padding: 18px;
        text-align: center;
    }

    .kp-drop .fa {
        font-size: 28px;
        color: #198754;
        display: block;
        margin-bottom: 8px;
    }

    .kp-drop input[type=file] {
        margin: 0 auto;
    }

    .kp-head {
        display: flex;
        flex-wrap: wrap;
        gap: 12px;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 18px;
    }

    .kp-sub {
        color: #6c757d;
        font-size: 13px;
        margin: 0;
    }

    .kp-btn {
        border-radius: 6px;
        font-weight: 500;
        padding: 7px 14px;
    }

    .kp-btn-primary {
        color: #fff;
        background: #0d6efd;
        border: 1px solid #0d6efd;
    }

    .kp-btn-primary:hover,
    .kp-btn-primary:focus {
        color: #fff;
        background: #0b5ed7;
    }

    .kp-btn-secondary {
        color: #fff;
        background: #6c757d;
        border: 1px solid #6c757d;
    }

    .kp-btn-secondary:hover,
    .kp-btn-secondary:focus {
        color: #fff;
        background: #5c636a;
    }

    .kp-btn-danger {
        color: #fff;
        background: #dc3545;
        border: 1px solid #dc3545;
    }

    .kp-btn-danger:hover,
    .kp-btn-danger:focus {
        color: #fff;
        background: #bb2d3b;
    }

    .kp-btn-outline-primary {
        color: #0d6efd;
        background: #fff;
        border: 1px solid #0d6efd;
    }

    .kp-btn-outline-primary:hover,
    .kp-btn-outline-primary:focus {
        color: #fff;
        background: #0d6efd;
    }

    .kp-btn-outline-danger {
        color: #dc3545;
        background: #fff;
        border: 1px solid #dc3545;
    }

    .kp-btn-outline-danger:hover,
    .kp-btn-outline-danger:focus {
        color: #fff;
        background: #dc3545;
    }

    .kp-alert {
        border-radius: 8px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, .08);
    }

    .kp-card {
        background: #fff;
        border: 1px solid #e7e9ed;
        border-radius: 10px;
        box-shadow: 0 1px 3px rgba(16, 24, 40, .07);
        margin-bottom: 20px;
        overflow: hidden;
    }

    .kp-card-head {
        padding: 14px 18px;
        border-bottom: 1px solid #e7e9ed;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
        min-height: 56px;
    }

    .kp-card-body {
        padding: 18px;
    }

    .kp-card-foot {
        padding: 12px 18px;
        border-top: 1px solid #e7e9ed;
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
    }

    .kp-total {
        font-weight: 600;
        color: #212529;
    }

    .kp-muted {
        color: #6c757d;
    }

    .kp-filter .input-group-addon {
        background: #f8f9fa;
        color: #6c757d;
        border-radius: 6px 0 0 6px;
    }

    .kp-filter .form-control {
        height: 38px;
        border-radius: 0 6px 6px 0;
        box-shadow: none;
    }

    .kp-filter select.form-control {
        border-radius: 6px;
    }

    .kp-filter-row>div {
        margin-bottom: 8px;
    }

    .kp-table {
        width: 100%;
        margin: 0;
        border-collapse: collapse;
    }

    .kp-table th {
        background: #f8f9fa;
        color: #495057;
        font-weight: 600;
        font-size: 13px;
        padding: 12px 14px;
        border-bottom: 1px solid #dee2e6;
        white-space: nowrap;
    }

    .kp-table td {
        padding: 12px 14px;
        border-top: 1px solid #eef0f2;
        vertical-align: middle;
        font-size: 13.5px;
        color: #374151;
    }

    .kp-table tbody tr:first-child td {
        border-top: 0;
    }

    .kp-table tbody tr:hover {
        background: #f6f9ff;
    }

    .kp-no {
        color: #6c757d;
        font-size: 12.5px;
    }

    .kp-name {
        font-weight: 600;
        color: #1f2937;
    }

    .kp-small {
        font-size: 12px;
        color: #6b7280;
    }

    .kp-clip {
        max-width: 180px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .kp-produk {
        max-height: 80px;
        overflow-y: auto;
        white-space: pre-line;
        font-size: 12.5px;
        background: #f8f9fa;
        border: 1px solid #e7e9ed;
        border-radius: 6px;
        padding: 8px;
        color: #6c757d;
    }

    .kp-badge {
        display: inline-block;
        padding: 3px 9px;
        border-radius: 6px;
        font-size: 12px;
        font-weight: 500;
        border: 1px solid #dee2e6;
        background: #f8f9fa;
        color: #6c757d;
        line-height: 1.4;
    }

    .kp-badge-primary {
        color: #0d6efd;
        background: #eef4ff;
        border-color: #cfe0ff;
    }

    .kp-badge-danger {
        color: #dc3545;
        background: #fdecee;
        border-color: #f6c7cc;
    }

    .kp-badge-success {
        color: #198754;
        background: #e9f7ef;
        border-color: #bfe5cf;
    }

    .kp-badge-warning {
        color: #8a6100;
        background: #fff6dd;
        border-color: #f5e0a0;
    }

    .kp-badge-info {
        color: #055160;
        background: #e0f6fb;
        border-color: #a8e3f0;
    }

    a.kp-badge:hover {
        text-decoration: none;
        opacity: .85;
    }

    .kp-links {
        display: flex;
        flex-direction: column;
        gap: 4px;
        align-items: flex-start;
    }

    .kp-actions {
        display: inline-flex;
        gap: 4px;
        align-items: center;
    }

    .kp-icon-btn {
        width: 32px;
        height: 32px;
        padding: 0;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: #fff;
        border: 1px solid #dee2e6;
        border-radius: 6px;
        cursor: pointer;
        color: #6c757d;
    }

    .kp-icon-btn:hover {
        background: #f1f3f5;
        text-decoration: none;
    }

    .kp-icon-btn.is-blue {
        color: #0d6efd;
    }

    .kp-icon-btn.is-green {
        color: #198754;
    }

    .kp-icon-btn.is-red {
        color: #dc3545;
    }

    .kp-icon-btn.is-off {
        color: #adb5bd;
    }

    .kp-empty {
        padding: 44px 10px;
        text-align: center;
        color: #6c757d;
    }

    .kp-empty .fa {
        display: block;
        font-size: 30px;
        margin-bottom: 8px;
    }

    .kp-card-foot .pagination {
        margin: 0;
    }

    .kp-card-foot .pagination>li>a,
    .kp-card-foot .pagination>li>span {
        font-size: 13px;
        padding: 4px 10px;
        color: #0d6efd;
    }

    .kp-card-foot .pagination>.active>a {
        background: #0d6efd;
        border-color: #0d6efd;
        color: #fff;
    }

    .kp-modal .modal-content {
        border: 0;
        border-radius: 10px;
        overflow: hidden;
    }

    .kp-modal .modal-header {
        background: #f8f9fa;
        border-bottom: 1px solid #e7e9ed;
        padding: 14px 18px;
    }

    .kp-modal .modal-title {
        font-weight: 600;
    }

    .kp-modal .modal-footer {
        border-top: 1px solid #e7e9ed;
    }

    .kp-label {
        display: block;
        font-size: 12px;
        color: #6c757d;
        margin-bottom: 2px;
    }

    .kp-box {
        border: 1px solid #e7e9ed;
        border-radius: 8px;
        padding: 12px;
        text-align: center;
        background: #f8f9fa;
        margin-bottom: 10px;
    }

    .kp-box .n {
        font-size: 22px;
        font-weight: 700;
    }

    .kp-info {
        background: #e8f3fe;
        border: 1px solid #cfe2ff;
        color: #084298;
        border-radius: 8px;
        padding: 10px 14px;
        margin-bottom: 14px;
        font-size: 13px;
    }

    .kp-block {
        border: 1px solid #e7e9ed;
        border-radius: 8px;
        padding: 12px;
        margin-bottom: 12px;
    }

    .kp-block h5 {
        margin: 0 0 10px;
        font-weight: 600;
        font-size: 14px;
    }

    .kp-row {
        display: flex;
        justify-content: space-between;
        padding: 3px 0;
        font-size: 13px;
    }

    .kp-head-actions .btn {
        white-space: nowrap;
    }
</style>

<!-- HEADER -->
<div class="kp-head">
    <p class="kp-sub">Daftar kontak perusahaan beserta kategori, status analisis produk AI, dan data penghubungnya.</p>
    <div class="kp-head-actions">
        <button type="button" class="btn kp-btn kp-btn-outline-primary" onclick="openImportAllModal()">
            <i class="fa fa-cubes"></i> Import Semua ke Supplier
        </button>
        <button type="button" class="btn kp-btn kp-btn-outline-success" data-toggle="modal" data-target="#modalImport">
            <i class="fa fa-file-excel-o"></i> Import Excel
        </button>
        <button type="button" class="btn kp-btn kp-btn-primary" data-toggle="modal" data-target="#modalPilihanTambah">
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
                <div class="col-md-4">
                    <div class="input-group">
                        <span class="input-group-addon"><i class="fa fa-search"></i></span>
                        <input type="text" name="cari" value="<?= esc($cari) ?>" class="form-control"
                            placeholder="Cari nama, perusahaan, email, no HP, kota, kategori...">
                    </div>
                </div>
                <div class="col-md-3">
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
                    <select name="status" class="form-control" onchange="this.form.submit()">
                        <option value="">Semua status analisis (<?= $stats['total'] ?>)</option>
                        <option value="belum_dianalisis" <?= $statusDipilih === 'belum_dianalisis' ? 'selected' : '' ?>>Belum dianalisis (<?= $stats['belum'] ?>)</option>
                        <option value="sukses" <?= $statusDipilih === 'sukses' ? 'selected' : '' ?>>Sukses AI (<?= $stats['sukses'] ?>)</option>
                        <option value="manual" <?= $statusDipilih === 'manual' ? 'selected' : '' ?>>Koreksi manual (<?= $stats['manual_edit'] ?>)</option>
                        <option value="gagal" <?= $statusDipilih === 'gagal' ? 'selected' : '' ?>>Gagal dianalisis (<?= $stats['gagal'] ?>)</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn kp-btn kp-btn-secondary" style="width:<?= $adaFilter ? '65' : '100' ?>%">Filter</button>
                    <?php if ($adaFilter): ?>
                        <a href="<?= site_url('kontak') ?>" class="btn kp-btn kp-btn-outline-danger" title="Reset filter"><i class="fa fa-undo"></i></a>
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
        <div id="bulkActionContainer" style="display:none">
            <button type="button" class="btn btn-sm kp-btn kp-btn-danger" onclick="executeBulkDelete()">
                <i class="fa fa-trash"></i> Hapus Terpilih (<span id="selectedCount">0</span>)
            </button>
        </div>
    </div>

    <div class="table-responsive">
        <table class="kp-table">
            <thead>
                <tr>
                    <th style="width:40px"><input type="checkbox" id="selectAll" onclick="toggleSelectAll(this)"></th>
                    <th style="width:55px">No</th>
                    <th>Perusahaan</th>
                    <th>Kontak</th>
                    <th>Tautan</th>
                    <th>Status</th>
                    <th class="text-center">Tidak Terkirim</th>
                    <th style="min-width:200px">Produk Yang Dijual (Auto)</th>
                    <th class="text-center" style="width:150px">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($kontak)): ?>
                    <tr>
                        <td colspan="9">
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
                        $jsonRow   = $k;
                        unset($jsonRow['produk_list']);
                        $lokasi    = trim(($k['kota_kabupaten'] ?? '') . ', ' . ($k['provinsi'] ?? ''), ', ');
                        $web       = trim((string) ($k['website'] ?? ''));
                        $adaWeb    = $web !== '' && $web !== '-';
                        $urlWeb    = $adaWeb ? (strpos($web, 'http') === 0 ? $web : 'https://' . $web) : '';
                        $katalog   = trim((string) ($k['link_catalog'] ?? ''));
                        $adaKat    = $katalog !== '' && $katalog !== '-';
                        $status    = $k['status_analisis'] ?? 'belum_dianalisis';
                        $gagal     = (int) ($k['jumlah_tidak_terkirim'] ?? 0);
                        $prodCount = count($k['produk_list'] ?? []);
                        $canImport = in_array($status, ['sukses', 'manual'], true) && $prodCount > 0;
                        ?>
                        <tr>
                            <td><input type="checkbox" class="row-select" value="<?= $k['id'] ?>" onclick="updateSelectedCount()"></td>
                            <td class="kp-no"><?= $no++ ?></td>
                            <td>
                                <div class="kp-name"><?= esc($k['nama_perusahaan'] ?? '-') ?></div>
                                <?php if (!empty($k['nama'])): ?>
                                    <div class="kp-small"><i class="fa fa-user-o"></i> <?= esc($k['nama']) ?></div>
                                <?php endif; ?>
                                <?php if (!empty($k['kategori_nama'])): ?>
                                    <span class="kp-badge kp-badge-primary" style="margin-top:4px"><?= esc($k['kategori_nama']) ?></span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="kp-line" title="<?= esc($k['email'] ?? '', 'attr') ?>"><i class="fa fa-envelope-o"></i><?= esc($k['email'] ?? '-') ?></div>
                                <div class="kp-line kp-small"><i class="fa fa-phone"></i><?= esc($k['no_hp'] ?: '-') ?></div>
                                <div class="kp-line kp-small"><i class="fa fa-map-marker"></i><?= esc($lokasi ?: '-') ?></div>
                            </td>
                            <td>
                                <div class="kp-links">
                                    <?php if ($adaWeb): ?>
                                        <a href="<?= esc($urlWeb, 'attr') ?>" target="_blank" rel="noopener" class="kp-badge kp-badge-primary" title="<?= esc($web, 'attr') ?>"><i class="fa fa-globe"></i> Web</a>
                                    <?php else: ?>
                                        <span class="kp-badge"><i class="fa fa-globe"></i> -</span>
                                    <?php endif; ?>
                                    <?php if ($adaKat): ?>
                                        <a href="<?= esc($katalog, 'attr') ?>" target="_blank" rel="noopener" class="kp-badge kp-badge-danger" title="<?= esc($katalog, 'attr') ?>"><i class="fa fa-file-pdf-o"></i> Katalog</a>
                                    <?php else: ?>
                                        <span class="kp-badge"><i class="fa fa-file-pdf-o"></i> -</span>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td>
                                <?php if ($status === 'sukses'): ?>
                                    <span class="kp-badge kp-badge-success"><i class="fa fa-check"></i> Sukses</span>
                                <?php elseif ($status === 'manual'): ?>
                                    <span class="kp-badge kp-badge-info"><i class="fa fa-pencil"></i> Koreksi</span>
                                <?php elseif ($status === 'gagal'): ?>
                                    <span class="kp-badge kp-badge-danger"><i class="fa fa-times"></i> Gagal</span>
                                <?php else: ?>
                                    <span class="kp-badge kp-badge-warning"><i class="fa fa-clock-o"></i> Belum</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-center">
                                <span class="kp-badge <?= $gagal > 0 ? 'kp-badge-danger' : '' ?>"><?= $gagal ?></span>
                            </td>
                            <td>
                                <?php if (!empty($k['produk_yang_dijual_auto'])): ?>
                                    <div class="kp-produk"><?= esc($k['produk_yang_dijual_auto']) ?></div>
                                <?php else: ?>
                                    <span class="kp-muted"><em>Belum ada hasil analisis.</em></span>
                                <?php endif; ?>
                            </td>
                            <td class="text-center">
                                <div class="kp-actions">
                                    <button type="button" class="kp-icon-btn is-blue" onclick="showCompanyDetail(<?= $k['id'] ?>)" title="Lihat detail">
                                        <i class="fa fa-eye"></i>
                                    </button>
                                    <button type="button" class="kp-icon-btn is-blue" title="Ubah kontak" data-toggle="modal"
                                        data-target="#modalKontak" data-judul="Ubah Kontak"
                                        data-aksi="<?= site_url('kontak/perbarui/' . $k['id']) ?>"
                                        data-kontak="<?= esc(json_encode($jsonRow), 'attr') ?>">
                                        <i class="fa fa-pencil"></i>
                                    </button>
                                    <button type="button" class="kp-icon-btn btn-import-supplier <?= $canImport ? 'is-green' : 'is-off' ?>"
                                        data-id="<?= $k['id'] ?>"
                                        data-name="<?= esc($k['nama_perusahaan'] ?? '', 'attr') ?>"
                                        data-status="<?= esc($status, 'attr') ?>"
                                        data-products="<?= $prodCount ?>"
                                        title="<?= $canImport ? 'Import ke Supplier (' . $prodCount . ' produk)' : ($prodCount === 0 ? 'Belum ada produk hasil analisis' : 'Status analisis: ' . esc($status, 'attr')) ?>">
                                        <i class="fa fa-download"></i>
                                    </button>
                                    <form action="<?= site_url('kontak/hapus/' . $k['id']) ?>" method="post"
                                        onsubmit="return confirm('Hapus kontak ini?')" style="margin:0">
                                        <?= csrf_field() ?>
                                        <button type="submit" class="kp-icon-btn is-red" title="Hapus kontak"><i class="fa fa-trash"></i></button>
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
                       <a href="<?= site_url('import-excel') ?>" class="btn btn-default btn-block btn-lg">
                            <i class="fa fa-file-excel-o" style="color: #db4437; font-size: 24px;"></i><br>Import Excel
                        </a>
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

<!-- MODAL DETAIL -->
<div class="modal fade kp-modal" id="modalCompanyDetail" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Tutup"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="modalCompanyTitle">Detail Perusahaan</h4>
            </div>
            <div class="modal-body" id="modalCompanyBody"></div>
            <div class="modal-footer">
                <button type="button" class="btn kp-btn kp-btn-secondary" data-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<!-- MODAL IMPORT SEMUA -->
<div class="modal fade kp-modal" id="modalImportAll" tabindex="-1" role="dialog" data-backdrop="static">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Tutup"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title"><i class="fa fa-cubes text-primary"></i> Import Semua ke Supplier</h4>
            </div>
            <div class="modal-body">
                <div class="text-center kp-muted" id="importAllLoading" style="padding:24px">
                    <i class="fa fa-spinner fa-spin fa-2x"></i>
                    <div style="margin-top:8px">Menghitung ringkasan data perusahaan dan produk...</div>
                </div>
                <div id="importAllContent" style="display:none">
                    <div class="kp-info">
                        <i class="fa fa-info-circle"></i>
                        Mengimpor seluruh perusahaan berstatus <strong>Sukses</strong> atau <strong>Koreksi Manual</strong> yang memiliki daftar produk ke Portal Supplier.
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="kp-block">
                                <h5 class="text-success"><i class="fa fa-check-circle"></i> Siap diimpor</h5>
                                <div class="kp-row"><span class="kp-muted">Perusahaan memenuhi syarat</span><strong id="prevEligibleCompany">0</strong></div>
                                <div class="kp-row"><span class="kp-muted">Total baris produk</span><strong id="prevEligibleProduct">0</strong></div>
                                <div class="kp-row"><span class="kp-muted">Supplier baru</span><strong id="prevNewSupplier">0</strong></div>
                                <div class="kp-row"><span class="kp-muted">Supplier sudah ada</span><strong id="prevExistingSupplier">0</strong></div>
                                <div class="kp-small" style="margin-top:6px"><em>Supplier yang sudah ada tidak ditimpa, hanya produk baru yang ditambahkan.</em></div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="kp-block">
                                <h5 class="text-muted"><i class="fa fa-times-circle"></i> Akan dilewati</h5>
                                <div class="kp-row"><span class="kp-muted">Total dilewati</span><strong class="text-danger" id="prevSkippedTotal">0</strong></div>
                                <div class="kp-small" style="margin:6px 0 2px">Rincian alasan:</div>
                                <div id="prevSkippedBreakdown"></div>
                            </div>
                        </div>
                    </div>
                    <div class="alert alert-warning kp-alert" style="margin-bottom:0;font-size:13px">
                        <i class="fa fa-warning"></i> Setiap perusahaan diproses dalam satu transaksi. Jika gagal, hanya data itu yang dibatalkan dan proses lanjut ke perusahaan berikutnya.
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn kp-btn kp-btn-secondary" data-dismiss="modal">Batal</button>
                <button type="button" class="btn kp-btn kp-btn-primary" id="btnExecuteImportAll" onclick="executeImportAll()" disabled>
                    <i class="fa fa-upload"></i> Konfirmasi &amp; Mulai Import Semua
                </button>
            </div>
        </div>
    </div>
</div>

<!-- MODAL HASIL IMPORT -->
<div class="modal fade kp-modal" id="modalImportResult" tabindex="-1" role="dialog" data-backdrop="static">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="modalImportResultTitle">Ringkasan Hasil Import</h4>
            </div>
            <div class="modal-body" id="modalImportResultBody"></div>
            <div class="modal-footer" id="modalImportResultFooter"></div>
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

    var BASE = '<?= site_url('kontak') ?>';
    var csrfTokenName = '<?= csrf_token() ?>';
    var csrfHash = '<?= csrf_hash() ?>';

    function updateCsrf(d) {
        if (d && d.csrf_hash) csrfHash = d.csrf_hash;
    }

    function esc(t) {
        if (t === null || t === undefined) return '';
        return String(t).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;').replace(/'/g, '&#039;');
    }

    function postData(extra) {
        var fd = new FormData();
        fd.append(csrfTokenName, csrfHash);
        return fd;
    }

    // ---------- Pilih & hapus massal ----------
    function toggleSelectAll(master) {
        $('.row-select').prop('checked', master.checked);
        updateSelectedCount();
    }

    function updateSelectedCount() {
        var n = $('.row-select:checked').length;
        $('#bulkActionContainer').toggle(n > 0);
        $('#selectedCount').text(n);
    }

    function executeBulkDelete() {
        var ids = $('.row-select:checked').map(function() {
            return this.value;
        }).get();
        if (!ids.length) return;
        if (!confirm('Hapus ' + ids.length + ' data perusahaan yang dipilih? Tindakan ini tidak dapat dibatalkan.')) return;

        var body = new URLSearchParams();
        ids.forEach(function(id) {
            body.append('ids[]', id);
        });
        body.append(csrfTokenName, csrfHash);

        fetch(BASE + '/bulk-delete', {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: body
            })
            .then(function(r) {
                return r.json();
            })
            .then(function(d) {
                if (d.success) {
                    location.reload();
                } else {
                    alert(d.message || 'Gagal menghapus data');
                }
            })
            .catch(function(e) {
                alert('Kesalahan jaringan: ' + e.message);
            });
    }

    // ---------- Detail ----------
    function parseProduk(text) {
        var out = '<ol style="margin:0;padding-left:18px">';
        text.split('\n').forEach(function(line) {
            line = line.replace(/^\d+[\.\)]\s*/, '').trim();
            if (!line) return;
            var parts = line.split(/\s+[–—\-]\s+/);
            var nama = parts[0].trim();
            var brand = parts.length > 1 ? parts[parts.length - 1].trim() : '';
            var brandOk = brand && brand.length <= 25 && brand.split(/\s+/).length <= 3 && !/[.!?]$/.test(brand);
            out += '<li style="margin-bottom:4px"><strong>' + esc(nama) + '</strong>' +
                (brandOk ? ' <span class="kp-badge kp-badge-primary"><i class="fa fa-tag"></i> ' + esc(brand) + '</span>' : '') + '</li>';
        });
        return out + '</ol>';
    }

    function showCompanyDetail(id) {
        var $body = $('#modalCompanyBody');
        $body.html('<div class="text-center kp-muted" style="padding:24px"><i class="fa fa-spinner fa-spin fa-2x"></i><div style="margin-top:8px">Memuat data...</div></div>');
        $('#modalCompanyDetail').modal('show');

        fetch(BASE + '/detail/' + id)
            .then(function(r) {
                return r.json();
            })
            .then(function(res) {
                if (!res.success) {
                    $body.html('<div class="alert alert-danger">' + esc(res.error || 'Gagal memuat detail') + '</div>');
                    return;
                }
                var c = res.company;
                $('#modalCompanyTitle').text(c.nama_perusahaan);

                var produk = '<span class="kp-muted"><em>Belum ada produk yang dianalisis.</em></span>';
                if (c.produk_yang_dijual_auto && c.produk_yang_dijual_auto.trim()) {
                    produk = parseProduk(c.produk_yang_dijual_auto);
                } else if (res.products && res.products.length) {
                    produk = '<ol style="margin:0;padding-left:18px">' + res.products.map(function(p) {
                        return '<li style="margin-bottom:4px"><strong>' + esc(p.nama_produk_id || p.nama_produk_en) + '</strong></li>';
                    }).join('') + '</ol>';
                }

                var sumber = '<span class="kp-muted kp-small">Belum ada riwayat pengambilan sumber.</span>';
                if (res.sources && res.sources.length) {
                    sumber = res.sources.map(function(s) {
                        var ok = s.status_pengambilan === 'sukses';
                        return '<div style="padding:6px 0;border-top:1px solid #eef0f2;font-size:13px">' +
                            '<div style="display:flex;justify-content:space-between"><strong>' + esc((s.jenis_sumber || '').toUpperCase()) + '</strong>' +
                            '<span class="kp-badge ' + (ok ? 'kp-badge-success' : 'kp-badge-danger') + '">' + (ok ? 'Sukses' : 'Gagal') + '</span></div>' +
                            (s.url_sumber ? '<div class="kp-muted kp-clip" style="max-width:100%">' + esc(s.url_sumber) + '</div>' : '') +
                            (s.pesan_error ? '<div class="text-danger">' + esc(s.pesan_error) + '</div>' : '') + '</div>';
                    }).join('');
                }

                var web = c.website ? '<a href="' + esc(c.website.indexOf('http') === 0 ? c.website : 'https://' + c.website) + '" target="_blank" rel="noopener">' + esc(c.website) + '</a>' : '-';
                var kat = c.link_catalog ? '<a href="' + esc(c.link_catalog) + '" target="_blank" rel="noopener">Buka katalog</a>' : '-';

                $body.html(
                    '<div class="row">' +
                    '<div class="col-md-6"><span class="kp-label">Nomor Excel</span><div><strong>' + esc(c.nomor_excel || '-') + '</strong></div></div>' +
                    '<div class="col-md-6"><span class="kp-label">Status Analisis</span><div><span class="kp-badge kp-badge-primary">' + esc(c.status_analisis) + '</span></div></div>' +
                    '<div class="col-md-6" style="margin-top:12px"><span class="kp-label">Kategori Utama</span><div>' + esc(c.main_categories || '-') + '</div></div>' +
                    '<div class="col-md-6" style="margin-top:12px"><span class="kp-label">Sub Kategori</span><div>' + esc(c.sub_categories || '-') + '</div></div>' +
                    '<div class="col-md-12" style="margin-top:12px"><span class="kp-label">Company Profile</span>' +
                    '<div class="kp-produk" style="max-height:120px">' + (c.company_profile ? esc(c.company_profile) : '<em>Tidak ada company profile.</em>') + '</div></div>' +
                    '<div class="col-md-6" style="margin-top:12px"><span class="kp-label">Website</span><div>' + web + '</div></div>' +
                    '<div class="col-md-6" style="margin-top:12px"><span class="kp-label">Link Catalog</span><div>' + kat + '</div></div>' +
                    '<div class="col-md-12" style="margin-top:16px;border-top:1px solid #e7e9ed;padding-top:12px"><strong>Hasil Produk Yang Dijual (Auto)</strong><div style="margin-top:8px">' + produk + '</div></div>' +
                    '<div class="col-md-12" style="margin-top:16px;border-top:1px solid #e7e9ed;padding-top:12px"><strong>Status Sumber Informasi</strong><div style="margin-top:8px">' + sumber + '</div></div>' +
                    '</div>'
                );
            })
            .catch(function(e) {
                $body.html('<div class="alert alert-danger">Kesalahan jaringan: ' + esc(e.message) + '</div>');
            });
    }

    // ---------- Import semua ----------
    function openImportAllModal() {
        $('#importAllLoading').show();
        $('#importAllContent').hide();
        $('#btnExecuteImportAll').prop('disabled', true);
        $('#modalImportAll').modal('show');

        fetch(BASE + '/import-preview', {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(function(r) {
                return r.json();
            })
            .then(function(res) {
                updateCsrf(res);
                if (!res.success) {
                    alert(res.error || 'Gagal memuat preview data import.');
                    $('#modalImportAll').modal('hide');
                    return;
                }

                $('#prevEligibleCompany').text(res.akan_diimpor.perusahaan);
                $('#prevEligibleProduct').text(res.akan_diimpor.produk);
                $('#prevNewSupplier').text(res.akan_diimpor.supplier_baru);
                $('#prevExistingSupplier').text(res.akan_diimpor.supplier_sudah_ada);
                $('#prevSkippedTotal').text(res.dilewati.total);

                var $b = $('#prevSkippedBreakdown').empty();
                (res.dilewati.breakdown || []).forEach(function(it) {
                    $b.append('<div class="kp-row"><span>' + esc(it.label) + '</span><span class="kp-badge">' + esc(it.count) + '</span></div>');
                });

                $('#importAllLoading').hide();
                $('#importAllContent').show();
                $('#btnExecuteImportAll').prop('disabled', !(res.akan_diimpor.perusahaan > 0));
            })
            .catch(function(e) {
                alert('Kesalahan jaringan: ' + e.message);
                $('#modalImportAll').modal('hide');
            });
    }

    function executeImportAll() {
        var $btn = $('#btnExecuteImportAll');
        if (!confirm('Mulai import keseluruhan ke Portal Supplier sekarang?')) return;
        var label = '<i class="fa fa-upload"></i> Konfirmasi &amp; Mulai Import Semua';
        $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Mengimpor data... harap tunggu');

        fetch(BASE + '/import-all', {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': csrfHash
                },
                body: postData()
            })
            .then(function(r) {
                return r.json();
            })
            .then(function(res) {
                updateCsrf(res);
                $btn.prop('disabled', false).html(label);
                $('#modalImportAll').modal('hide');
                if (!res.success) {
                    alert(res.error || 'Gagal menjalankan import semua');
                    return;
                }
                showImportResult('Ringkasan Hasil Import Semua', res.summary, false);
            })
            .catch(function(e) {
                $btn.prop('disabled', false).html(label);
                alert('Kesalahan jaringan: ' + e.message);
            });
    }

    // ---------- Import satu perusahaan ----------
    $(document).on('click', '.btn-import-supplier', function(e) {
        e.preventDefault();
        var d = this.dataset;
        var n = parseInt(d.products || '0', 10);
        if (n === 0 || ['sukses', 'manual'].indexOf(d.status) === -1) {
            alert('Perusahaan "' + d.name + '" belum memiliki daftar produk hasil analisis yang valid, atau statusnya belum Sukses / Koreksi Manual.');
            return;
        }
        importSingle(d.id, d.name, n);
    });

    function importSingle(id, name, n) {
        if (!confirm('Import perusahaan "' + name + '" (' + n + ' produk) ke direktori Supplier sekarang?')) return;

        $('#modalImportResultTitle').html('<i class="fa fa-truck text-primary"></i> Mengimpor: ' + esc(name));
        $('#modalImportResultBody').html('<div class="text-center kp-muted" style="padding:24px"><i class="fa fa-spinner fa-spin fa-2x"></i><div style="margin-top:8px">Memproses import perusahaan dan produk...</div></div>');
        $('#modalImportResultFooter').html('<button type="button" class="btn kp-btn kp-btn-secondary" disabled>Harap tunggu...</button>');
        $('#modalImportResult').modal('show');

        fetch(BASE + '/import-single/' + id, {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': csrfHash
                },
                body: postData()
            })
            .then(function(r) {
                return r.json();
            })
            .then(function(res) {
                updateCsrf(res);
                showImportResult('Hasil Import: ' + name, res, true);
            })
            .catch(function(e) {
                $('#modalImportResultBody').html('<div class="alert alert-danger">Kesalahan jaringan: ' + esc(e.message) + '</div>');
                $('#modalImportResultFooter').html('<button type="button" class="btn kp-btn kp-btn-secondary" data-dismiss="modal">Tutup</button>');
            });
    }

    function box(label, val, cls) {
        return '<div class="col-md-3 col-xs-6"><div class="kp-box"><div class="kp-small">' + label + '</div><div class="n ' + cls + '">' + esc(val) + '</div></div></div>';
    }

    function tabel(judul, rows, colA, colB, cls) {
        if (!rows || !rows.length) return '';
        return '<h5 class="' + cls + '" style="margin-top:14px"><strong>' + judul + ' (' + rows.length + ')</strong></h5>' +
            '<div class="table-responsive" style="max-height:180px;overflow-y:auto"><table class="table table-bordered table-condensed" style="font-size:12.5px">' +
            '<thead><tr><th>Perusahaan</th><th>' + colA + '</th></tr></thead><tbody>' +
            rows.map(function(r) {
                return '<tr><td>' + esc(r.nama) + '</td><td>' + esc(r[colB]) + '</td></tr>';
            }).join('') +
            '</tbody></table></div>';
    }

    function showImportResult(title, d, single) {
        $('#modalImportResultTitle').html('<i class="fa fa-list-alt text-success"></i> ' + esc(title));
        var closeBtn = '<button type="button" class="btn kp-btn kp-btn-secondary" data-dismiss="modal">Tutup</button>';
        var doneBtn = '<button type="button" class="btn kp-btn kp-btn-primary" onclick="location.reload()"><i class="fa fa-check"></i> Selesai &amp; Refresh</button>';
        var body, footer = closeBtn;

        if (single) {
            if (d.success) {
                body = '<div class="alert alert-success kp-alert"><i class="fa fa-check-circle"></i> ' + esc(d.message) + '</div>' +
                    '<p>' + (d.is_new_supplier ?
                        '<span class="kp-badge kp-badge-success">Supplier baru dibuat</span>' :
                        '<span class="kp-badge kp-badge-info">Supplier sudah terdaftar (data dipertahankan)</span>') + '</p>' +
                    '<div class="row">' + box('Produk ditambahkan', d.products_added, 'text-success').replace('col-md-3', 'col-md-6') +
                    box('Produk dilewati (sudah ada)', d.products_skipped, 'text-muted').replace('col-md-3', 'col-md-6') + '</div>';
                footer = doneBtn;
            } else if (d.is_skipped) {
                body = '<div class="alert alert-warning kp-alert"><strong><i class="fa fa-warning"></i> Perusahaan dilewati</strong><div>' + esc(d.reason || d.message) + '</div></div>' +
                    '<div class="kp-muted kp-small">Hanya perusahaan berstatus Sukses atau Koreksi Manual dengan minimal 1 produk yang dapat diimpor.</div>';
            } else {
                body = '<div class="alert alert-danger kp-alert"><strong><i class="fa fa-times-circle"></i> Import gagal</strong><div>' + esc(d.message || d.error || 'Terjadi kesalahan.') + '</div>' +
                    (d.error ? '<div class="kp-small" style="margin-top:6px">Pesan error: ' + esc(d.error) + '</div>' : '') + '</div>';
            }
        } else {
            body = '<div class="row">' +
                box('Supplier baru', d.supplier_baru, 'text-success') +
                box('Supplier terhubung', d.supplier_sudah_ada, 'text-info') +
                box('Produk ditambahkan', d.produk_ditambahkan, 'text-primary') +
                box('Produk dilewati', d.produk_dilewati, 'text-muted') + '</div>' +
                tabel('Perusahaan dilewati', d.dilewati, 'Alasan', 'alasan', 'text-muted') +
                tabel('Perusahaan gagal', d.gagal, 'Pesan error', 'pesan', 'text-danger');
            footer = doneBtn;
        }

        $('#modalImportResultBody').html(body);
        $('#modalImportResultFooter').html(footer);
        $('#modalImportResult').modal('show');
    }
</script>
<?= $this->endSection() ?>