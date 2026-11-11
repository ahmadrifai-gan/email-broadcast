<?= $this->extend('layouts/main') ?>

<?= $this->section('title') ?>Buat Kampanye Email<?= $this->endSection() ?>
<?= $this->section('page_title') ?>Buat Kampanye Email<?= $this->endSection() ?>
<?= $this->section('page_subtitle') ?>Form Pembuatan Kampanye Email<?= $this->endSection() ?>

<?= $this->section('breadcrumb') ?>
<li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
<li><a href="<?= base_url('kirim-email') ?>">Kirim Email</a></li>
<li class="active">Buat</li>
<?= $this->endSection() ?>

<?= $this->section('styles') ?>
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap.min.css">
<style>
    /* ====== GLOBAL ====== */
    .content-header { margin-bottom: 8px; }
    .box {
        border-radius: 8px;
        border-top-width: 3px;
        box-shadow: 0 2px 10px rgba(0,0,0,.045);
        overflow: hidden;
        margin-bottom: 20px;
    }
    .box-header { padding: 15px 18px; }
    .box-header .box-title { font-size: 16px; font-weight: 600; }
    .box-header .box-title .fa { color: #3c8dbc; margin-right: 4px; }
    .box-body { padding: 18px; }
    .box-footer { padding: 13px 18px; background: #fbfcfd; }

    /* ====== FORM ====== */
    .form-group { margin-bottom: 19px; }
    .form-group label { font-weight: 600; color: #344054; margin-bottom: 7px; display: inline-block; }
    .form-control { border-radius: 5px; box-shadow: none; border-color: #d9e0e7; }
    .form-control:focus { border-color: #3c8dbc; box-shadow: 0 0 0 2px rgba(60,141,188,.10); }
    .input-group-addon { border-color: #d9e0e7; background: #f7f9fb; color: #687787; }
    .btn { border-radius: 5px; transition: all .15s ease; }
    .btn-lg { padding: 10px 17px; }
    .label { border-radius: 20px; padding: 5px 10px; }
    .help-block { font-size: 12px; color: #8a96a3; margin-top: 5px; margin-bottom: 0; }

    /* ====== MODAL KONTAK ====== */
    #modalKontak .modal-dialog { width: 1000px; max-width: 96%; }
    #modalKontak .modal-content, #modalJadwal .modal-content {
        border: 0; border-radius: 9px; overflow: hidden;
        box-shadow: 0 15px 45px rgba(0,0,0,.2);
    }
    #modalKontak .modal-header, #modalJadwal .modal-header {
        padding: 17px 20px; background: #f8fafc; border-bottom: 1px solid #e8edf2;
    }
    #modalKontak .modal-title, #modalJadwal .modal-title { font-weight: 600; }
    #modalKontak .modal-body, #modalJadwal .modal-body { padding: 20px; }
    #modalKontak .modal-footer, #modalJadwal .modal-footer {
        padding: 14px 20px; background: #fbfcfd; border-top: 1px solid #e8edf2;
    }
    #modalKontak .table-responsive { border: 1px solid #e5eaf0 !important; border-radius: 6px; }
    #modalKontak .table, #penerimaTerpilihList .table { margin-bottom: 0; }
    #modalKontak .table > thead > tr > th,
    #penerimaTerpilihList .table > thead > tr > th {
        background: #f5f8fb; color: #475467; font-size: 12px;
        text-transform: uppercase; letter-spacing: .35px;
        border-bottom: 1px solid #e1e7ee; padding: 11px 10px; white-space: nowrap;
    }
    #modalKontak .table > tbody > tr > td,
    #penerimaTerpilihList .table > tbody > tr > td {
        padding: 10px; vertical-align: middle; border-color: #edf0f4;
    }
    #modalKontak .table > tbody > tr.kontak-modal-row { cursor: pointer; }
    #modalKontak .table > tbody > tr.kontak-modal-row:hover > td { background: #f2f8fd; }
    #modalKontak .table > tbody > tr.selected-row > td { background-color: #e7f4fc !important; }
    #modalKontak .check-kontak, #checkAllModal { width: 15px; height: 15px; cursor: pointer; }
    #searchKontakModal { height: 40px; }

    /* ====== PENERIMA ====== */
    .penerima-toolbar {
        display: flex; align-items: center; justify-content: space-between;
        gap: 10px; flex-wrap: wrap; margin: 2px 0 12px;
    }
    .penerima-toolbar .text-muted { font-size: 12px; }
    .penerima-table-wrap { max-height: 300px; overflow: auto; border: 1px solid #e5eaf0; border-radius: 6px; }
    .penerima-empty {
        text-align: center; color: #8a96a3; padding: 30px 12px;
        border: 2px dashed #e5eaf0; border-radius: 8px; background: #fafbfc;
    }
    .penerima-empty .fa { font-size: 34px; display: block; margin-bottom: 10px; color: #bdc7d2; }
    .penerima-name { font-weight: 600; color: #344054; }
    .penerima-company { color: #475467; font-size: 12px; }
    .penerima-email { color: #667085; font-size: 12px; overflow-wrap: anywhere; }
    .btn-hapus-penerima { padding: 4px 8px; border-radius: 4px; }

    /* ====== DATATABLES ====== */
    .dataTables_wrapper { width: 100%; }
    .dataTables_wrapper .dataTables_length,
    .dataTables_wrapper .dataTables_filter { margin: 0 0 12px; color: #667085; }
    .dataTables_wrapper .dataTables_length select,
    .dataTables_wrapper .dataTables_filter input {
        height: 32px; border: 1px solid #d9e0e7; border-radius: 4px;
        padding: 5px 8px; box-shadow: none; outline: none;
    }
    .dataTables_wrapper .dataTables_filter input { margin-left: 6px; }
    .dataTables_wrapper .dataTables_info { color: #667085; font-size: 12px; padding-top: 12px; }
    .dataTables_wrapper .dataTables_paginate { padding-top: 8px; }
    .dataTables_wrapper .pagination { margin: 5px 0 0; }
    .dataTables_wrapper .pagination > li > a { padding: 5px 10px; }
    #tableKontakModal { width: 100% !important; }
    #tableKontakModal_wrapper .table-responsive { border: 0 !important; }
    #penerimaTerpilihList .dataTables_wrapper { padding-top: 8px; }
    #tablePenerimaTerpilih { width: 100% !important; }
    #tablePenerimaTerpilih_wrapper .dataTables_length,
    #tablePenerimaTerpilih_wrapper .dataTables_filter { font-size: 12px; }
    .penerima-table-wrap { max-height: none; overflow: visible; border: 0; border-radius: 0; }
    @media (max-width: 767px) {
        #modalKontak .modal-body { padding: 12px; }
        .dataTables_wrapper .dataTables_length,
        .dataTables_wrapper .dataTables_filter { float: none; text-align: left; }
        .dataTables_wrapper .dataTables_filter input { max-width: 65%; }
        .dataTables_wrapper .dataTables_info { white-space: normal; }
    }

    /* ====== MODE CARD (Waktu & Pola) ====== */
    .mode-card {
        display: flex; align-items: flex-start; width: 100%; min-height: 110px;
        margin: 0 0 15px; padding: 18px 20px; border: 2px solid #e3e3e3; border-radius: 8px;
        background: #fff; cursor: pointer; font-weight: normal; position: relative;
        transition: border-color .15s, box-shadow .15s, background .15s, transform .1s;
    }
    .mode-card:hover { border-color: #9fc5e0; transform: translateY(-1px); }
    .mode-card input[type=radio] { position: absolute; opacity: 0; pointer-events: none; }
    .mode-card.selected {
        border-color: #3c8dbc; background: #f4faff;
        box-shadow: 0 0 0 3px rgba(60,141,188,.18);
    }
    .mode-card.selected:after {
        content: "\f00c"; font-family: FontAwesome;
        position: absolute; top: 10px; right: 14px;
        color: #3c8dbc; font-size: 16px;
    }
    .mode-icon {
        width: 52px; height: 52px; flex: 0 0 52px; margin-right: 16px;
        border-radius: 50%; color: #fff;
        display: flex; align-items: center; justify-content: center; font-size: 24px;
        box-shadow: 0 3px 10px rgba(0,0,0,.15);
    }
    .mode-text { flex: 1; padding-top: 4px; }
    .mode-text strong { display: block; font-size: 16px; margin-bottom: 4px; color: #2c3e50; }
    .mode-text small { display: block; color: #777; font-size: 13px; line-height: 1.5; }
    .mode-text small strong { display: inline; font-size: inherit; color: inherit; margin: 0; }

    /* ====== CARD COLLAPSIBLE (Contoh Isi Email) ====== */
    .box-collapsible .box-header {
        cursor: pointer; user-select: none; transition: background .15s;
        position: relative;
    }
    .box-collapsible .box-header:hover { background: #f8fafc; }
    .box-collapsible .box-header .toggle-icon {
        position: absolute; right: 18px; top: 50%; transform: translateY(-50%);
        font-size: 14px; color: #8a96a3; transition: transform .25s ease;
    }
    .box-collapsible.collapsed .box-header .toggle-icon { transform: translateY(-50%) rotate(-90deg); }
    .box-collapsible.collapsed .box-body,
    .box-collapsible.collapsed .box-footer { display: none; }
    .box-collapsible .box-header .box-tools { padding-right: 30px; }
    .box-collapsible .box-header .box-tools .btn-group { position: relative; z-index: 2; }
    .collapsible-hint {
        font-size: 12px; color: #8a96a3; font-weight: normal; margin-left: 8px;
    }

    /* ====== PREVIEW ENHANCED ====== */
    .preview-info-strip {
        background: linear-gradient(135deg, #f8fafc 0%, #f2f7fb 100%);
        border: 1px solid #d9e6f2;
        border-radius: 8px;
        padding: 14px 16px;
        margin-bottom: 14px;
        font-size: 13px;
    }
    .preview-info-strip .pi-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 12px 20px;
    }
    @media (max-width: 767px) {
        .preview-info-strip .pi-grid { grid-template-columns: 1fr; }
    }
    .preview-info-strip .pi-label {
        color: #667085;
        font-weight: 600;
        font-size: 11px;
        text-transform: uppercase;
        letter-spacing: .4px;
        margin-bottom: 3px;
    }
    .preview-info-strip .pi-value {
        color: #2c3e50;
        font-weight: 500;
        word-break: break-word;
        line-height: 1.45;
    }
    .preview-info-strip .pi-value .pi-email {
        color: #667085; font-weight: 400; font-size: 12px;
    }
    .preview-attachment {
        display: flex;
        align-items: center;
        gap: 12px;
        margin-top: 12px;
        padding: 11px 14px;
        background: #fff9e6;
        border: 1px solid #ffe082;
        border-left: 4px solid #f39c12;
        border-radius: 6px;
        font-size: 13px;
        flex-wrap: wrap;
    }
    .preview-attachment.inline {
        background: #e8f5e9;
        border-color: #a5d6a7;
        border-left-color: #27ae60;
    }
    .preview-attachment .pa-icon {
        font-size: 22px;
        color: #f39c12;
        flex: 0 0 auto;
    }
    .preview-attachment.inline .pa-icon { color: #27ae60; }
    .preview-attachment .pa-body { flex: 1; min-width: 180px; }
    .preview-attachment .pa-title {
        font-weight: 600;
        color: #344054;
        margin-bottom: 2px;
    }
    .preview-attachment .pa-file {
        color: #667085;
        font-size: 12px;
        font-family: Consolas, Menlo, monospace;
        word-break: break-all;
    }
    .preview-attachment .pa-actions {
        display: flex;
        gap: 6px;
        flex: 0 0 auto;
    }
    .preview-attachment .pa-actions .btn { font-size: 12px; }
    /* ====== KATEGORI KONTAK (modal pilih penerima) ====== */
    .kt-toolbar {
        display: flex; flex-wrap: wrap; gap: 8px; align-items: center;
        margin-bottom: 10px; padding: 10px 12px;
        background: #f8fafc; border: 1px solid #e8edf2; border-radius: 6px;
    }
    .kt-toolbar .kt-label { font-size: 12px; font-weight: 600; color: #667085; margin-right: 2px; }
    .kt-toolbar select { width: auto; min-width: 220px; max-width: 100%; }
    .kt-toolbar .kt-spacer { flex: 1 1 auto; }
    #tableKontakModal tr.kt-group-row > td {
        background: #eef4fa !important; border-top: 2px solid #c9dbee !important;
        font-size: 13px; padding: 8px 10px !important; cursor: default;
    }
    #tableKontakModal tr.kt-group-row .check-group { width: 15px; height: 15px; cursor: pointer; margin-right: 8px; vertical-align: middle; }
    #tableKontakModal tr.kt-group-row .kt-group-sel { margin-left: 8px; color: #667085; font-size: 12px; }
    #tableKontakModal tr.kt-group-row .kt-group-lihat { margin-left: 10px; font-size: 12px; cursor: pointer; }
    .kt-badge { display: inline-block; padding: 1px 8px; border-radius: 10px; font-size: 11px; background: #e7f4fc; color: #2b6a94; border: 1px solid #c4e0f2; }
    .kt-badge.none { background: #f2f4f7; color: #667085; border-color: #e0e4ea; }

    .preview-attachment.link {
        background: #e8f1fb;
        border-color: #b9d4f1;
        border-left-color: #3c8dbc;
    }
    .preview-attachment.link .pa-icon { color: #3c8dbc; }
    .preview-attachment .pa-note {
        display: block;
        margin-top: 3px;
        font-size: 11px;
        color: #667085;
    }
    .pdf-viewer-wrap { margin-top: 10px; }
    .pdf-viewer-wrap iframe,
    .pdf-viewer-wrap embed {
        width: 100%;
        height: 520px;
        border: 1px solid #d9e0e7;
        border-radius: 6px;
        background: #f5f6f8;
    }

    #previewHtml {
        width: 100%;
        height: 480px;
        border: 1px solid #d9e0e7;
        border-radius: 6px;
        background: #fff;
        box-shadow: inset 0 0 0 1px rgba(0,0,0,0.02);
    }
    #previewTeks {
        font-family: Consolas, Menlo, monospace;
        font-size: 13px;
        line-height: 1.55;
        background: #fbfcfd;
        border: 1px solid #d9e0e7;
    }
    .preview-empty {
        text-align: center;
        color: #8a96a3;
        padding: 40px 20px;
        border: 2px dashed #e5eaf0;
        border-radius: 8px;
        background: #fafbfc;
    }
    .preview-empty .fa { font-size: 40px; display: block; margin-bottom: 12px; color: #bdc7d2; }

    /* ====== OPSI CARD dalam modal jadwal ====== */
    .opsi-card {
        display: block; margin: 0 0 10px; padding: 12px 14px;
        border: 2px solid #e3e3e3; border-radius: 5px;
        cursor: pointer; font-weight: normal; background: #fff; height: 92px;
    }
    .opsi-card input[type=radio] { position: absolute; opacity: 0; pointer-events: none; }
    .opsi-card.selected { border-color: #00a65a; background: #f3fbf6; }
    .opsi-card strong { display: block; }
    .opsi-card small { display: block; color: #777; }
    .ringkas-jadwal ol { margin: 6px 0 0; padding-left: 22px; }
    .ringkas-jadwal ol li { padding: 2px 0; }

    /* ====== MODAL JADWAL (REDESIGN) ====== */
    #modalJadwal .modal-dialog { width: 1100px; max-width: 96%; }
    #modalJadwal .modal-body { padding: 20px 22px; }

    .mj-step-head {
        display: flex; align-items: flex-start; gap: 14px;
        padding: 16px 18px; margin-bottom: 16px;
        background: linear-gradient(135deg, #eaf4fb 0%, #f4faff 100%);
        border: 1px solid #cfe2f0; border-radius: 8px;
    }
    .mj-step-head .mj-step-icon {
        flex: 0 0 44px; width: 44px; height: 44px; border-radius: 50%;
        background: #3c8dbc; color: #fff;
        display: flex; align-items: center; justify-content: center;
        font-size: 20px; box-shadow: 0 3px 10px rgba(60,141,188,.3);
    }
    .mj-step-head .mj-step-text { flex: 1; }
    .mj-step-head .mj-step-text h4 { margin: 0 0 4px; font-weight: 600; font-size: 15px; color: #2c3e50; }
    .mj-step-head .mj-step-text p { margin: 0; color: #667085; font-size: 13px; line-height: 1.5; }

    .mj-panel {
        border: 1px solid #e5eaf0; border-radius: 8px;
        margin-bottom: 14px; background: #fff; overflow: hidden;
        box-shadow: 0 1px 4px rgba(0,0,0,.04);
    }
    .mj-panel-head {
        padding: 12px 16px; background: #f8fafc; border-bottom: 1px solid #e5eaf0;
        display: flex; align-items: center; gap: 10px;
    }
    .mj-panel-head .mj-num {
        flex: 0 0 26px; width: 26px; height: 26px; border-radius: 50%;
        background: #3c8dbc; color: #fff; font-size: 13px; font-weight: 700;
        display: flex; align-items: center; justify-content: center;
    }
    .mj-panel-head h5 { margin: 0; font-weight: 600; font-size: 14px; color: #2c3e50; }
    .mj-panel-body { padding: 16px; }

    .mj-pill-group { display: flex; gap: 8px; margin-bottom: 14px; }
    .mj-pill {
        flex: 1; margin: 0; cursor: pointer; position: relative;
        padding: 9px 12px; border: 2px solid #e3e3e3; border-radius: 6px;
        text-align: center; font-weight: 600; font-size: 13px; color: #667085;
        background: #fff; transition: all .15s;
        display: flex; align-items: center; justify-content: center; gap: 6px;
    }
    .mj-pill:hover { border-color: #9fc5e0; }
    .mj-pill input[type=radio] { position: absolute; opacity: 0; pointer-events: none; }
    .mj-pill.selected {
        border-color: #3c8dbc; background: #eaf4fb; color: #3c8dbc;
    }
    .mj-pill.selected .fa { color: #3c8dbc; }
    .mj-pill .fa { color: #bdc7d2; }

    .mj-field { margin-bottom: 10px; }
    .mj-field:last-child { margin-bottom: 0; }
    .mj-field label {
        display: block; font-size: 12px; font-weight: 600;
        color: #667085; margin-bottom: 5px; text-transform: uppercase;
        letter-spacing: .3px;
    }
    .mj-field .input-group-addon { font-size: 12px; }
    .mj-field .form-control { height: 38px; }

    .mj-help {
        font-size: 12px; color: #8a96a3; margin-top: 10px;
        padding: 8px 10px; background: #fbfcfd;
        border-left: 3px solid #d9e0e7; border-radius: 0 4px 4px 0;
        line-height: 1.5;
    }
    .mj-help .fa { color: #3c8dbc; margin-right: 4px; }

    .mj-actions {
        display: flex; flex-wrap: wrap; gap: 8px; margin-bottom: 14px;
        padding: 12px 14px; background: #f8fafc;
        border: 1px solid #e5eaf0; border-radius: 8px;
    }
    .mj-actions .btn { font-weight: 600; font-size: 13px; }

    .mj-plan-wrap {
        border: 1px solid #e5eaf0; border-radius: 8px; overflow: hidden;
    }
    .mj-plan-wrap .table { margin: 0; }
    .mj-plan-wrap .table > thead > tr > th {
        background: #f5f8fb; color: #475467; font-size: 12px;
        text-transform: uppercase; letter-spacing: .3px;
        border-bottom: 1px solid #e1e7ee; padding: 11px 10px;
        font-weight: 700; white-space: nowrap;
    }
    .mj-plan-wrap .table > tbody > tr > td {
        padding: 8px 10px; vertical-align: middle; border-color: #edf0f4;
    }
    .mj-plan-wrap .table > tbody > tr:nth-child(even) > td { background: #fafbfc; }
    .mj-plan-wrap .plan-jumlah { max-width: 80px; }
    .mj-plan-wrap .akun-prev {
        display: inline-block; max-width: 340px;
        overflow: hidden; text-overflow: ellipsis; white-space: nowrap;
        vertical-align: middle; color: #475467; font-size: 13px;
    }
    .mj-plan-wrap .plan-detail > td { background: #f8fafc !important; }
    .mj-plan-wrap .pick-list {
        max-height: 220px; overflow-y: auto; border: 1px solid #e5eaf0;
        background: #fff; padding: 8px 12px; margin-top: 8px; border-radius: 6px;
    }
    .mj-plan-wrap .pick-item {
        display: block; font-weight: normal; margin: 0; padding: 5px 0;
        cursor: pointer; border-bottom: 1px dashed #f0f2f5;
    }
    .mj-plan-wrap .pick-item:last-child { border-bottom: 0; }
    .mj-plan-wrap .pick-item.dipakai { color: #aaa; cursor: not-allowed; }
    .mj-plan-wrap .pick-cari { margin-bottom: 4px; }

    #planRingkas { margin-bottom: 14px; }
    #planRingkas .callout { border-radius: 8px; margin-bottom: 0 !important; }
    #planRingkas .callout p { margin: 0 0 4px; }
    #planRingkas .callout p:last-child { margin-bottom: 0; }

    .kc-card { min-height: 96px; }
    .kc-card .mode-text strong { font-size: 15px; }

    @media (max-width: 767px) {
        .mode-card { min-height: auto; padding: 14px; }
        .mode-icon { width: 44px; height: 44px; flex: 0 0 44px; font-size: 20px; }
        .mode-text strong { font-size: 14px; }
        #modalJadwal .modal-body { padding: 14px; }
        .mj-actions { flex-direction: column; }
        .mj-actions .btn { width: 100%; }
    }
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<?php /* Flash message & error validasi ditampilkan lewat SweetAlert2 (lihat section scripts) */ ?>

<form action="<?= base_url('kirim-email/simpan') ?>" method="post" id="formKampanye">
    <?= csrf_field() ?>

    <div class="row">
        <div class="col-lg-6">
            <div class="box box-primary">
                <div class="box-header with-border">
                    <h3 class="box-title"><i class="fa fa-cog"></i> Pengaturan Kampanye</h3>
                </div>
                <div class="box-body">
                    <div class="form-group">
                        <label for="kategori_id">1. Kategori</label>
                        <div class="input-group">
                            <span class="input-group-addon"><i class="fa fa-tags"></i></span>
                            <select name="kategori_id" id="kategori_id" class="form-control">
                                <option value="">-- Pilih Kategori --</option>
                                <?php foreach ($kategori as $k): ?>
                                    <option value="<?= $k['id'] ?>" <?= old('kategori_id') == $k['id'] ? 'selected' : '' ?>>
                                        <?= esc($k['nama_kategori']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="template_email_id">2. Template</label>
                        <div class="input-group">
                            <span class="input-group-addon"><i class="fa fa-file-text-o"></i></span>
                            <select name="template_email_id" id="template_email_id" class="form-control" required disabled>
                                <option value="">-- Pilih kategori dulu --</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="smtp_setting_id">3. SMTP Pengirim</label>
                        <div class="input-group">
                            <span class="input-group-addon"><i class="fa fa-server"></i></span>
                            <select name="smtp_setting_id" id="smtp_setting_id" class="form-control">
                                <option value="">— Pakai SMTP aktif default —</option>
                                <?php foreach (($smtpList ?? []) as $s): ?>
                                    <option value="<?= (int) $s['id'] ?>"
                                        <?= (string) old('smtp_setting_id') === (string) $s['id'] ? 'selected' : '' ?>>
                                        <?= esc($s['nama']) ?> (<?= esc($s['from_email']) ?>)
                                        <?= (int) $s['is_active'] === 1 ? ' • AKTIF' : '' ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <p class="help-block">
                            Kosongkan untuk memakai SMTP yang aktif. Pilih satu untuk menimpa SMTP default pada kampanye ini.
                        </p>
                    </div>

                    <div class="form-group">
                        <label>Nama Kampanye</label>
                        <div class="input-group">
                            <span class="input-group-addon"><i class="fa fa-bullhorn"></i></span>
                            <input type="text" name="nama_kampanye" class="form-control"
                                   placeholder="Contoh: Promo Akhir Tahun" value="<?= esc(old('nama_kampanye')) ?>" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Subjek Email</label>
                        <div class="input-group">
                            <span class="input-group-addon"><i class="fa fa-envelope"></i></span>
                            <input type="text" name="subjek_email" id="subjek_email" class="form-control"
                                   value="<?= esc(old('subjek_email')) ?>" required>
                        </div>
                        <p class="help-block">Terisi otomatis dari template, boleh diubah.</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="box box-success">
                <div class="box-header with-border">
                    <h3 class="box-title"><i class="fa fa-users"></i> 4. Penerima (dari Kontak)</h3>
                    <div class="box-tools pull-right">
                        <span class="label label-success"><span id="jumlahDipilih">0</span> dipilih</span>
                    </div>
                </div>
                <div class="box-body">
                    <div class="penerima-toolbar">
                        <span class="text-muted"><i class="fa fa-info-circle"></i> Pilih kontak yang akan menerima kampanye ini.</span>
                        <button type="button" class="btn btn-success" id="btnBukaModalKontak">
                            <i class="fa fa-user-plus"></i> Pilih Penerima
                        </button>
                    </div>

                    <div id="penerimaTerpilihList">
                        <div class="penerima-empty">
                            <i class="fa fa-users"></i>
                            Belum ada penerima dipilih.
                            <div><small>Penerima yang dipilih akan tampil dalam tabel ini.</small></div>
                        </div>
                    </div>

                    <div id="hiddenPenerimaWrap"></div>
                </div>
            </div>
        </div>
    </div>

    <div class="box box-info box-collapsible collapsed" id="boxPreview">
        <div class="box-header with-border" id="boxPreviewHeader">
            <h3 class="box-title">
                <i class="fa fa-eye"></i> Contoh Isi Email
                <small class="text-muted collapsible-hint" id="infoContoh"></small>
            </h3>
            <div class="box-tools pull-right">
                <div class="btn-group btn-group-sm" role="group" id="previewTabs">
                    <button type="button" class="btn btn-default active" id="tabTeks"><i class="fa fa-text-width"></i> Teks</button>
                    <button type="button" class="btn btn-default" id="tabHtml"><i class="fa fa-code"></i> Tampilan HTML</button>
                </div>
            </div>
            <span class="toggle-icon"><i class="fa fa-chevron-down"></i></span>
        </div>
        <div class="box-body">
            <div id="previewEmpty" class="preview-empty">
                <i class="fa fa-envelope-o"></i>
                Pilih kategori &amp; template untuk melihat contoh isi email.
                <div><small>Tautan, gambar, dan lampiran PDF akan ditampilkan di sini.</small></div>
            </div>

            <div id="previewBody" class="hidden">
                <div class="preview-info-strip" id="previewInfoStrip"></div>
                <textarea id="previewTeks" class="form-control" rows="14" readonly
                          placeholder="Pilih kategori & template untuk melihat contoh isi email..."></textarea>
                <iframe id="previewHtml"
                        sandbox="allow-popups allow-popups-to-escape-sandbox"
                        class="hidden"></iframe>
            </div>
        </div>
        <div class="box-footer text-muted small">
            <i class="fa fa-info-circle"></i>
            Placeholder <code>{nama}</code>, <code>{nama_perusahaan}</code>, <code>{no_hp}</code>,
            <code>{email}</code>, <code>{domisili}</code>
            diganti dengan data penerima pertama yang dipilih. Isi ini hanya untuk dibaca (read-only).
        </div>
    </div>

    <?php $modeLama = old('mode_kirim') ?: 'sekarang'; ?>
    <div class="box box-warning">
        <div class="box-header with-border">
            <h3 class="box-title"><i class="fa fa-calendar-check-o"></i> 5. Waktu &amp; Pola Pengiriman</h3>
        </div>
        <div class="box-body">
            <p class="text-muted" style="margin-bottom:15px;">
                Pilih salah satu. Untuk mode terjadwal / bertahap, tanggal &amp; jam diatur lewat jendela pengaturan.
            </p>

            <div class="row">
                <div class="col-md-6">
                    <label class="mode-card" data-mode="sekarang">
                        <input type="radio" name="mode_kirim" value="sekarang" <?= $modeLama === 'sekarang' ? 'checked' : '' ?>>
                        <span class="mode-icon bg-yellow"><i class="fa fa-bolt"></i></span>
                        <span class="mode-text"><strong>Kirim langsung (tidak bertahap)</strong>
                            <small>Dikirim satu kali, segera setelah disimpan.</small></span>
                    </label>
                </div>
                <div class="col-md-6">
                    <label class="mode-card" data-mode="sekarang_berulang">
                        <input type="radio" name="mode_kirim" value="sekarang_berulang" <?= $modeLama === 'sekarang_berulang' ? 'checked' : '' ?>>
                        <span class="mode-icon bg-red"><i class="fa fa-repeat"></i></span>
                        <span class="mode-text"><strong>Kirim langsung + bertahap</strong>
                            <small>Mulai sekarang, penerima <strong>dibagi</strong> ke beberapa pengiriman. Jumlah, akun &amp; jarak waktunya diatur sendiri / acak.</small></span>
                    </label>
                </div>
                <div class="col-md-6">
                    <label class="mode-card" data-mode="jadwal">
                        <input type="radio" name="mode_kirim" value="jadwal" <?= $modeLama === 'jadwal' ? 'checked' : '' ?>>
                        <span class="mode-icon bg-aqua"><i class="fa fa-clock-o"></i></span>
                        <span class="mode-text"><strong>Terjadwal (tidak bertahap)</strong>
                            <small>Dikirim otomatis satu kali pada tanggal &amp; jam yang dipilih.</small></span>
                    </label>
                </div>
                <div class="col-md-6">
                    <label class="mode-card" data-mode="jadwal_berulang">
                        <input type="radio" name="mode_kirim" value="jadwal_berulang" <?= $modeLama === 'jadwal_berulang' ? 'checked' : '' ?>>
                        <span class="mode-icon bg-green"><i class="fa fa-calendar-plus-o"></i></span>
                        <span class="mode-text"><strong>Terjadwal + bertahap</strong>
                            <small>Mulai pada waktu tertentu, penerima <strong>dibagi</strong> ke beberapa pengiriman. Jumlah, akun &amp; jarak waktunya diatur sendiri / acak.</small></span>
                    </label>
                </div>
            </div>

            <div id="ringkasJadwal" class="ringkas-jadwal"></div>
            <div id="hiddenJadwalWrap"></div>
        </div>
    </div>

    <div class="box box-danger hidden" id="kartuKecepatan">
        <div class="box-header with-border">
            <h3 class="box-title"><i class="fa fa-tachometer"></i> 6. Kecepatan Pengiriman <small>— penerima banyak</small></h3>
            <div class="box-tools pull-right">
                <span class="label label-danger"><span id="kcJumlah">0</span> penerima</span>
            </div>
        </div>
        <div class="box-body">
            <div class="callout callout-warning">
                <p><i class="fa fa-exclamation-triangle"></i>
                    Anda memilih <strong>banyak penerima</strong> (minimal <?= (int) $ambangBanyak ?>). Mengirim sekaligus membuat akun SMTP mudah
                    dibatasi/ditandai spam. Atur jumlah email per menit &mdash; <strong>tetap</strong> atau <strong>acak</strong> dalam rentang.
                    (Ingin membagi penerima ke beberapa pengiriman? Pilih mode <em>berulang</em> di kartu 4.)</p>
            </div>

            <div class="row">
                <div class="col-md-4">
                    <label class="mode-card kc-card" data-kc="bertahap">
                        <input type="radio" name="kecepatan_mode" value="bertahap" <?= in_array(old('kecepatan_mode'), ['acak', 'cepat'], true) ? '' : 'checked' ?>>
                        <span class="mode-icon bg-red"><i class="fa fa-tachometer"></i></span>
                        <span class="mode-text"><strong>Tetap per menit</strong>
                            <small>Jumlah email per menit sama setiap menit.</small></span>
                    </label>
                </div>
                <div class="col-md-4">
                    <label class="mode-card kc-card" data-kc="acak">
                        <input type="radio" name="kecepatan_mode" value="acak" <?= old('kecepatan_mode') === 'acak' ? 'checked' : '' ?>>
                        <span class="mode-icon bg-purple"><i class="fa fa-random"></i></span>
                        <span class="mode-text"><strong>Acak per menit</strong>
                            <small>Jumlah email tiap menit diacak dalam rentang (mis. 5&ndash;10).</small></span>
                    </label>
                </div>
                <div class="col-md-4">
                    <label class="mode-card kc-card" data-kc="cepat">
                        <input type="radio" name="kecepatan_mode" value="cepat" <?= old('kecepatan_mode') === 'cepat' ? 'checked' : '' ?>>
                        <span class="mode-icon bg-gray"><i class="fa fa-fighter-jet"></i></span>
                        <span class="mode-text"><strong>Secepatnya</strong>
                            <small>Tanpa batas per menit (tetap mengikuti batas harian).</small></span>
                    </label>
                </div>
            </div>

            <div id="kcBoxTetap">
                <div class="row">
                    <div class="col-sm-3">
                        <div class="form-group">
                            <label for="kc_rate">Email per menit</label>
                            <input type="number" name="kecepatan_per_menit" id="kc_rate" class="form-control input-lg"
                                   min="1" max="<?= (int) $maksKecepatan ?>" value="<?= esc(old('kecepatan_per_menit') ?: 10) ?>">
                        </div>
                    </div>
                    <div class="col-sm-9">
                        <label>Pilihan cepat</label><br>
                        <div class="btn-group">
                            <?php foreach ([5, 10, 20, 30, 60] as $r): ?>
                                <button type="button" class="btn btn-default kc-preset" data-rate="<?= $r ?>"><?= $r ?> / menit</button>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>

            <div id="kcBoxAcak" class="hidden">
                <div class="row">
                    <div class="col-sm-3">
                        <div class="form-group">
                            <label for="kc_min">Minimal / menit</label>
                            <input type="number" name="kecepatan_min" id="kc_min" class="form-control input-lg"
                                   min="1" max="<?= (int) $maksKecepatan ?>" value="<?= esc(old('kecepatan_min') ?: 5) ?>">
                        </div>
                    </div>
                    <div class="col-sm-3">
                        <div class="form-group">
                            <label for="kc_maks">Maksimal / menit</label>
                            <input type="number" name="kecepatan_maks" id="kc_maks" class="form-control input-lg"
                                   min="1" max="<?= (int) $maksKecepatan ?>" value="<?= esc(old('kecepatan_maks') ?: 10) ?>">
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <label>Pilihan cepat</label><br>
                        <div class="btn-group">
                            <?php foreach ([[5, 10], [10, 20], [20, 30], [30, 60]] as [$mn, $mx]): ?>
                                <button type="button" class="btn btn-default kc-preset-acak" data-min="<?= $mn ?>" data-max="<?= $mx ?>"><?= $mn ?>&ndash;<?= $mx ?></button>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>

            <div id="kcEstimasi"></div>
        </div>
    </div>

    <div class="box box-solid">
        <div class="box-body">
            <a href="<?= base_url('kirim-email') ?>" class="btn btn-default btn-lg"><i class="fa fa-arrow-left"></i> Batal</a>
            <button type="submit" class="btn btn-primary btn-lg pull-right" id="btnSimpan">
                <i class="fa fa-send"></i> <span id="labelSimpan">Simpan &amp; Kirim Sekarang</span>
            </button>
        </div>
    </div>

</form>

<div class="modal fade" id="modalJadwal" tabindex="-1" role="dialog" aria-labelledby="judulModalJadwal">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title"><i class="fa fa-calendar"></i> <span id="judulModalJadwal">Atur Jadwal</span></h4>
            </div>
            <div class="modal-body">
                <div id="mjError" class="alert alert-danger hidden"></div>

                <div class="mj-step-head" id="mjStepHead">
                    <div class="mj-step-icon"><i class="fa fa-cogs"></i></div>
                    <div class="mj-step-text">
                        <h4 id="mjStepTitle">Atur Rencana Pengiriman</h4>
                        <p id="mjStepDesc">Tentukan bagaimana penerima akan dibagi ke beberapa pengiriman.</p>
                    </div>
                </div>

                <div id="mjBoxMulai" class="hidden">
                    <div class="mj-panel">
                        <div class="mj-panel-head">
                            <span class="mj-num"><i class="fa fa-clock-o"></i></span>
                            <h5><span id="mjLabelMulai">Waktu Pengiriman Pertama</span></h5>
                        </div>
                        <div class="mj-panel-body">
                            <div class="row">
                                <div class="col-sm-6">
                                    <div class="mj-field">
                                        <label><i class="fa fa-calendar"></i> Tanggal &amp; Jam</label>
                                        <input type="text" class="hidden">
                                        <input type="datetime-local" id="mj_mulai" class="form-control input-lg">
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="mj-help" style="margin-top: 22px;">
                                        <i class="fa fa-info-circle"></i>
                                        Pilih tanggal &amp; jam kapan pengiriman <strong>pertama</strong> akan dilakukan.
                                        Pengiriman berikutnya (jika berulang) dihitung otomatis dari waktu ini.
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div id="mjBoxRencana" class="hidden">
                    <div class="callout callout-info" style="margin-bottom:16px; border-radius:8px;">
                        <h4 style="margin-top:0;"><i class="fa fa-info-circle"></i> Cara Kerja Pembagian</h4>
                        <p style="margin-bottom:6px;">
                            Penerima akan <strong>dibagi</strong> ke beberapa pengiriman &mdash; setiap akun hanya menerima <strong>satu kali</strong>
                            (pesan tidak diulang ke orang yang sama).
                        </p>
                        <p style="margin-bottom:0;">
                            <i class="fa fa-users"></i> Total penerima terpilih: <strong id="mjPool">0</strong> akun
                        </p>
                    </div>

                    <div class="row">
                        <div class="col-md-4">
                            <div class="mj-panel">
                                <div class="mj-panel-head">
                                    <span class="mj-num">1</span>
                                    <h5>Total Pengiriman</h5>
                                </div>
                                <div class="mj-panel-body">
                                    <div class="mj-pill-group">
                                        <label class="mj-pill selected" data-pill="mj_total_mode" data-value="manual">
                                            <input type="radio" name="mj_total_mode" value="manual" checked>
                                            <i class="fa fa-pencil"></i> Atur sendiri
                                        </label>
                                        <label class="mj-pill" data-pill="mj_total_mode" data-value="acak">
                                            <input type="radio" name="mj_total_mode" value="acak">
                                            <i class="fa fa-random"></i> Acak
                                        </label>
                                    </div>

                                    <div id="mjTotalManual" class="mj-field">
                                        <label>Jumlah pengiriman</label>
                                        <div class="input-group">
                                            <input type="number" id="mj_total" class="form-control" min="2" max="<?= (int) $maksPengiriman ?>" value="11">
                                            <span class="input-group-addon">pengiriman</span>
                                        </div>
                                    </div>
                                    <div id="mjTotalAcak" class="mj-field hidden">
                                        <label>Rentang pengiriman</label>
                                        <div class="input-group">
                                            <span class="input-group-addon">antara</span>
                                            <input type="number" id="mj_total_min" class="form-control" min="2" value="5">
                                            <span class="input-group-addon">s/d</span>
                                            <input type="number" id="mj_total_max" class="form-control" min="2" value="10">
                                        </div>
                                    </div>

                                    <div class="mj-help">
                                        <i class="fa fa-lightbulb-o"></i>
                                        Berapa kali pengiriman akan dilakukan? Minimal 2 kali.
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="mj-panel">
                                <div class="mj-panel-head">
                                    <span class="mj-num">2</span>
                                    <h5>Akun Tiap Pengiriman</h5>
                                </div>
                                <div class="mj-panel-body">
                                    <div class="mj-pill-group">
                                        <label class="mj-pill" data-pill="mj_akun_mode" data-value="tetap">
                                            <input type="radio" name="mj_akun_mode" value="tetap">
                                            <i class="fa fa-pencil"></i> Atur sendiri
                                        </label>
                                        <label class="mj-pill selected" data-pill="mj_akun_mode" data-value="acak">
                                            <input type="radio" name="mj_akun_mode" value="acak" checked>
                                            <i class="fa fa-random"></i> Acak
                                        </label>
                                    </div>

                                    <div id="mjAkunTetap" class="mj-field hidden">
                                        <label>Jumlah akun per pengiriman</label>
                                        <div class="input-group">
                                            <input type="number" id="mj_akun" class="form-control" min="1" value="5">
                                            <span class="input-group-addon">akun</span>
                                        </div>
                                    </div>
                                    <div id="mjAkunAcak" class="mj-field">
                                        <label>Rentang akun</label>
                                        <div class="input-group">
                                            <span class="input-group-addon">antara</span>
                                            <input type="number" id="mj_akun_min" class="form-control" min="1" value="5">
                                            <span class="input-group-addon">s/d</span>
                                            <input type="number" id="mj_akun_max" class="form-control" min="1" value="10">
                                            <span class="input-group-addon">akun</span>
                                        </div>
                                    </div>

                                    <div class="mj-help">
                                        <i class="fa fa-lightbulb-o"></i>
                                        Akun dipilih acak dari penerima; bisa dipilih manual per baris setelah rencana dibuat.
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="mj-panel">
                                <div class="mj-panel-head">
                                    <span class="mj-num">3</span>
                                    <h5>Jarak Waktu</h5>
                                </div>
                                <div class="mj-panel-body">
                                    <div class="mj-pill-group">
                                        <label class="mj-pill selected" data-pill="mj_waktu_mode" data-value="tetap">
                                            <input type="radio" name="mj_waktu_mode" value="tetap" checked>
                                            <i class="fa fa-pencil"></i> Atur sendiri
                                        </label>
                                        <label class="mj-pill" data-pill="mj_waktu_mode" data-value="acak">
                                            <input type="radio" name="mj_waktu_mode" value="acak">
                                            <i class="fa fa-random"></i> Acak
                                        </label>
                                    </div>

                                    <div id="mjGapTetap" class="mj-field">
                                        <label>Jarak antar pengiriman</label>
                                        <div class="input-group">
                                            <span class="input-group-addon">setiap</span>
                                            <input type="number" id="mj_gap" class="form-control" min="1" value="1">
                                            <select id="mj_gap_satuan" class="form-control" style="max-width:90px;">
                                                <option value="menit">menit</option>
                                                <option value="jam">jam</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div id="mjGapAcak" class="mj-field hidden">
                                        <label>Rentang jarak waktu</label>
                                        <div class="input-group">
                                            <input type="number" id="mj_gap_min" class="form-control" min="1" value="5" title="minimal">
                                            <span class="input-group-addon">s/d</span>
                                            <input type="number" id="mj_gap_max" class="form-control" min="1" value="10" title="maksimal">
                                            <select id="mj_gap_satuan" class="form-control" style="max-width:90px;">
                                                <option value="menit">menit</option>
                                                <option value="jam">jam</option>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="mj-help">
                                        <i class="fa fa-lightbulb-o"></i>
                                        Minimal <?= (int) $minGap ?> menit. Jam pengiriman tiap baris juga bisa diatur manual.
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mj-actions">
                        <button type="button" class="btn btn-primary" id="btnBuatRencana">
                            <i class="fa fa-magic"></i> Buat Rencana
                        </button>
                        <button type="button" class="btn btn-default" id="btnAcakUlang">
                            <i class="fa fa-random"></i> Acak Ulang
                        </button>
                        <button type="button" class="btn btn-warning" id="btnLengkapi">
                            <i class="fa fa-plus-circle"></i> Lengkapi Semua Penerima
                        </button>
                        <button type="button" class="btn btn-default" id="btnTambahBaris">
                            <i class="fa fa-plus"></i> Tambah Pengiriman
                        </button>
                    </div>

                    <div id="planRingkas"></div>

                    <div class="mj-plan-wrap" style="max-height:380px; overflow-y:auto;">
                        <table class="table table-bordered plan-table">
                            <thead>
                                <tr>
                                    <th width="50">Ke-</th>
                                    <th width="220" id="thWaktu">Waktu kirim</th>
                                    <th width="100">Akun</th>
                                    <th>Penerima</th>
                                    <th width="150">Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="planBody"></tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal"><i class="fa fa-times"></i> Batal</button>
                <button type="button" class="btn btn-primary" id="btnTerapkanJadwal"><i class="fa fa-check"></i> Terapkan</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalKontak" tabindex="-1" role="dialog" aria-labelledby="modalKontakLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title" id="modalKontakLabel">
                    <i class="fa fa-users"></i> Pilih Kontak Penerima
                </h4>
            </div>
            <div class="modal-body">
                <div class="form-group" style="margin-bottom:10px;">
                    <div class="input-group">
                        <span class="input-group-addon"><i class="fa fa-search"></i></span>
                        <input type="text" id="searchKontakModal" class="form-control" autocomplete="off"
                               placeholder="Cari nama / perusahaan / email / domisili...">
                        <span class="input-group-btn">
                            <button type="button" class="btn btn-default" id="btnResetSearch" title="Bersihkan pencarian">
                                <i class="fa fa-times"></i>
                            </button>
                        </span>
                    </div>
                </div>

                <?php
                    $katNama = [];
                    foreach (($kategoriKontak ?? []) as $kt) { $katNama[(int) $kt['id']] = $kt['nama']; }
                    $totalKontak = !empty($kontak) ? count($kontak) : 0;
                ?>
                <div class="kt-toolbar">
                    <span class="kt-label"><i class="fa fa-eye"></i> Tampilan:</span>
                    <div class="btn-group btn-group-sm" id="ktViewMode">
                        <button type="button" class="btn btn-default active" data-mode="semua" title="Semua kontak dalam satu daftar, tanpa pengelompokan">
                            <i class="fa fa-list"></i> Semua Kontak
                        </button>
                        <button type="button" class="btn btn-default" data-mode="kategori" title="Kontak dikelompokkan per kategori">
                            <i class="fa fa-folder-open"></i> Per Kategori
                        </button>
                    </div>

                    <span class="kt-label" style="margin-left:6px;"><i class="fa fa-filter"></i> Kategori:</span>
                    <select id="ktFilterKategori" class="form-control input-sm">
                        <option value="">Semua kategori (<?= (int) $totalKontak ?>)</option>
                        <?php foreach (($kategoriKontak ?? []) as $kt): ?>
                            <option value="<?= (int) $kt['id'] ?>"><?= esc($kt['nama']) ?> (<?= (int) $kt['jumlah'] ?>)</option>
                        <?php endforeach; ?>
                        <?php if (!empty($tanpaKategori)): ?>
                            <option value="0">Tanpa kategori (<?= (int) $tanpaKategori ?>)</option>
                        <?php endif; ?>
                    </select>

                    <span class="kt-spacer"></span>
                    <button type="button" class="btn btn-info btn-sm" id="btnPilihSemuaTampil" title="Pilih semua kontak yang sedang tampil (sesuai kategori &amp; pencarian)">
                        <i class="fa fa-check-square-o"></i> Pilih Semua yang Tampil <span id="ktJumlahTampil"></span>
                    </button>
                    <button type="button" class="btn btn-default btn-sm" id="btnKosongkanPilihan">
                        <i class="fa fa-square-o"></i> Kosongkan
                    </button>
                </div>

                <div class="table-responsive" style="max-height: 430px; overflow-y: auto;">
                    <table id="tableKontakModal" class="table table-bordered table-striped table-hover" style="width:100%; margin-bottom:0;">
                        <thead>
                            <tr>
                                <th width="40" class="text-center">
                                    <input type="checkbox" id="checkAllModal" title="Pilih semua yang tampil">
                                </th>
                                <th>Nama</th>
                                <th>Nama Perusahaan</th>
                                <th>Email</th>
                                <th>Domisili</th>
                                <th>Kategori</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($kontak)): foreach ($kontak as $k): ?>
                                <?php
                                    $kid      = (int) ($k['kategori_id'] ?? 0);
                                    $namaKat  = $katNama[$kid] ?? '';
                                    $kidEfektif = ($namaKat !== '') ? $kid : 0;
                                ?>
                                <tr class="kontak-modal-row"
                                    data-id="<?= (int) $k['id'] ?>"
                                    data-kategori="<?= $kidEfektif ?>"
                                    data-kategori-nama="<?= esc($namaKat !== '' ? $namaKat : 'Tanpa kategori') ?>"
                                    data-nama="<?= esc($k['nama'] ?? '') ?>"
                                    data-nama-perusahaan="<?= esc($k['nama_perusahaan'] ?? '') ?>"
                                    data-no-hp="<?= esc($k['no_hp'] ?? '') ?>"
                                    data-email="<?= esc($k['email'] ?? '') ?>"
                                    data-kota="<?= esc($k['kota_kabupaten'] ?? '') ?>">
                                    <td class="text-center">
                                        <input type="checkbox" class="check-kontak" value="<?= (int) $k['id'] ?>">
                                    </td>
                                    <td><?= esc($k['nama'] ?? '-') ?></td>
                                    <td><?= esc($k['nama_perusahaan'] ?? '-') ?></td>
                                    <td><?= esc($k['email'] ?? '-') ?></td>
                                    <td><?= esc($k['kota_kabupaten'] ?? '-') ?></td>
                                    <td>
                                        <?php if ($namaKat !== ''): ?>
                                            <span class="kt-badge"><?= esc($namaKat) ?></span>
                                        <?php else: ?>
                                            <span class="kt-badge none">Tanpa kategori</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; endif; ?>
                        </tbody>
                    </table>
                </div>

                <div class="text-muted small" style="margin-top:12px; padding:10px 12px; background:#f8fafc; border:1px solid #e8edf2; border-radius:5px;">
                    <i class="fa fa-info-circle"></i>
                    Klik baris atau checkbox untuk memilih. Gunakan <strong>Per Kategori</strong> atau filter kategori untuk memilih seluruh kontak dalam satu kategori sekaligus. Pilihan diterapkan saat Anda klik <strong>Tambahkan</strong>.
                    <span class="pull-right">Terpilih: <strong id="countModalSelected">0</strong></span>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">
                    <i class="fa fa-times"></i> Batal
                </button>
                <button type="button" class="btn btn-primary" id="btnTambahkanPenerima" disabled>
                    <i class="fa fa-check"></i> Tambahkan <span id="labelTambahkan"></span>
                </button>
            </div>
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
<script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.8/js/dataTables.bootstrap.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.5.0/js/responsive.bootstrap.min.js"></script>
<script>
(function () {
'use strict';

function mulai() {

const $ = id => document.getElementById(id);

AppSwal.flash(<?= json_encode($flashSwal, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE) ?>);

/* ==========================================================
   DATA DARI SERVER
   ========================================================== */
const TEMPLATES    = <?= json_encode($templateJs, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE) ?>;
const OLD_TEMPLATE = <?= (int) old('template_email_id') ?>;
const OLD_PENERIMA = <?= json_encode(array_map('strval', (array) old('penerima')), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>;
const BASE_URL     = '<?= base_url() ?>';

/* Debug: cek field template yang tersedia */
if (TEMPLATES && TEMPLATES.length) {
    const sample = TEMPLATES[0];
    console.log('[TEMPLATES] Field yang tersedia pada template pertama:', Object.keys(sample));
}

/* ==========================================================
   STATE
   ========================================================== */
const penerimaMap   = new Map();
const kontakDataMap = new Map();
let   workingSet    = new Set();

/* ==========================================================
   UTIL
   ========================================================== */
function ganti(teks, s) {
    return (teks || '')
        .split('{nama}').join(s.nama || '')
        .split('{nama_perusahaan}').join(s.nama_perusahaan || '')
        .split('{perusahaan}').join(s.nama_perusahaan || '')
        .split('{no_hp}').join(s.no_hp || '')
        .split('{email}').join(s.email || '')
        .split('{domisili}').join(s.domisili || '');
}

function escapeHtml(s) {
    return String(s == null ? '' : s)
        .replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;').replace(/'/g, '&#39;');
}

/* ==========================================================
   NORMALISASI URL PDF (relatif → absolut)
   ========================================================== */
function normalizePreviewUrl(url) {
    url = String(url || '').trim();
    if (!url) return '';
    // Sudah absolut atau data URI
    if (/^(https?:)?\/\//i.test(url) || /^data:/i.test(url) || /^blob:/i.test(url)) return url;
    // Relatif → prepend BASE_URL
    const base = BASE_URL.replace(/\/+$/, '');
    const path = url.replace(/^\/+/, '');
    return base + '/' + path;
}

/* ==========================================================
   DETEKSI FIELD PDF DARI BANYAK VARIAN NAMA
   ========================================================== */
function getPdfInfo(t) {
    if (!t) return { url: '', tipe: '', nama: '', hasData: false };

    // Cari field URL dengan banyak kemungkinan nama
    const rawUrl = t.attachment_pdf_url
                || t.pdf_url
                || t.pdf_path
                || t.pdf_attachment_url
                || t.lampiran_pdf
                || t.attachment
                || '';

    const url = normalizePreviewUrl(rawUrl);

    // Tipe PDF — normalisasi + default
    //    Harus sama dengan EmailSender: HANYA 'attachment' yang benar-benar dilampirkan,
    //    selain itu ('link' / kosong) PDF hanya berupa tautan di isi email.
    let tipe = String(t.tipe_pdf || t.pdf_tipe || t.pdf_type || '').toLowerCase().trim();
    if (url) {
        if (['attachment', 'attachments', 'lampiran', 'attach'].includes(tipe)) {
            tipe = 'attachment';
        } else if (['inline', 'embed', 'embedded'].includes(tipe)) {
            tipe = 'inline';
        } else {
            tipe = 'link';
        }
    }

    // Nama file
    let nama = '';
    if (url) {
        try {
            const u = new URL(url);
            nama = decodeURIComponent(u.pathname.split('/').pop() || '');
        } catch (e) {
            nama = url.split('/').pop().split('?')[0] || '';
        }
    }

    return {
        url: url,
        tipe: tipe,
        nama: nama || 'file.pdf',
        hasData: !!url,
        rawUrl: rawUrl
    };
}

/* ==========================================================
   PREVIEW — TEXT EXTRACTION
   ========================================================== */
function htmlKeTeks(html, s) {
    if (!html) return '';
    const rendered = s ? ganti(html, s) : html;

    const work = rendered
        .replace(/<br\s*\/?>/gi, '\n')
        .replace(/<\/(p|div|h[1-6]|tr|li|table|thead|tbody|blockquote)>/gi, '\n');

    const doc = new DOMParser().parseFromString(work, 'text/html');
    doc.querySelectorAll('style,script,head,title,noscript').forEach(n => n.remove());

    const links = [];
    doc.querySelectorAll('a[href]').forEach(a => {
        const href = (a.getAttribute('href') || '').trim();
        if (!href) return;
        if (/^#/i.test(href) || /^mailto:/i.test(href) || /^tel:/i.test(href)) return;
        const text = (a.textContent || '').trim().replace(/\s+/g, ' ');
        links.push({ text, href });
    });

    const images = [];
    doc.querySelectorAll('img[src]').forEach(img => {
        const src = (img.getAttribute('src') || '').trim();
        if (!src || /^cid:/i.test(src) || /^data:/i.test(src)) return;
        const alt = (img.getAttribute('alt') || '').trim();
        images.push({ alt, src });
    });

    let text = (doc.body.textContent || '')
        .replace(/[ \t]+\n/g, '\n')
        .replace(/\n{3,}/g, '\n\n')
        .trim();

    if (links.length) {
        text += '\n\n─────────────────────────\nTAUTAN DALAM EMAIL:\n';
        links.forEach((l, i) => {
            text += `${i + 1}. ${l.text ? l.text + '\n   → ' : ''}${l.href}\n`;
        });
    }
    if (images.length) {
        text += '\n─────────────────────────\nGAMBAR DALAM EMAIL:\n';
        images.forEach((img, i) => {
            text += `${i + 1}. ${img.alt ? img.alt + '\n   → ' : ''}${img.src}\n`;
        });
    }

    return text;
}

/* ==========================================================
   PREVIEW — HTML BUILDER
   ========================================================== */
function buildHtmlPreview(html, s) {
    if (!html) return '';
    let rendered = ganti(html, s);

    if (!/<base\b/i.test(rendered)) {
        const baseTag = '<base href="' + BASE_URL + '">';
        if (/<head\b[^>]*>/i.test(rendered)) {
            rendered = rendered.replace(/(<head\b[^>]*>)/i, '$1' + baseTag);
        } else if (/<html\b[^>]*>/i.test(rendered)) {
            rendered = rendered.replace(/(<html\b[^>]*>)/i, '$1<head>' + baseTag + '</head>');
        } else {
            rendered = baseTag + rendered;
        }
    }

    rendered = rendered.replace(/<a\b([^>]*)>/gi, function (m, attrs) {
        if (/\btarget\s*=/i.test(attrs)) return m;
        return '<a' + attrs + ' target="_blank" rel="noopener noreferrer">';
    });

    return rendered;
}

/* ==========================================================
   PREVIEW — INFO STRIP + LAMPIRAN PDF (FIXED)
   ========================================================== */
function renderInfoStrip(t, s, subjek) {
    const strip = $('previewInfoStrip');
    if (!strip) return;

    const pdf = getPdfInfo(t);

    // Debug log
    console.log('[Preview PDF]', {
        hasUrl: !!pdf.url,
        url: pdf.url,
        rawUrl: pdf.rawUrl,
        tipe: pdf.tipe,
        nama: pdf.nama,
        templateFields: t ? Object.keys(t) : []
    });

    let html = '<div class="pi-grid">';
    html += '<div>' +
        '<div class="pi-label"><i class="fa fa-envelope-o"></i> Subjek</div>' +
        '<div class="pi-value">' + escapeHtml(subjek || '-') + '</div>' +
    '</div>';
    html += '<div>' +
        '<div class="pi-label"><i class="fa fa-user"></i> Kepada</div>' +
        '<div class="pi-value">' + escapeHtml(s.nama || '-') +
        (s.email ? ' <span class="pi-email">&lt;' + escapeHtml(s.email) + '&gt;</span>' : '') +
        '</div>' +
    '</div>';
    html += '</div>';

    // Kartu PDF sesuai pengaturan template (lampiran / link / inline)
    if (pdf.url) {
        const isInline = (pdf.tipe === 'inline');
        const isLink   = (pdf.tipe === 'link');
        const cls      = isInline ? ' inline' : (isLink ? ' link' : '');
        const icon     = isInline ? 'fa-file-pdf-o' : (isLink ? 'fa-link' : 'fa-paperclip');
        const judul    = isInline ? 'PDF ditampilkan inline di badan email'
                       : (isLink  ? 'PDF disisipkan sebagai link (tidak dilampirkan)'
                                  : 'Lampiran PDF');
        const catatan  = isLink
            ? 'Penerima hanya mendapat tautan, file PDF tidak ikut terlampir pada email.'
            : (isInline ? '' : 'File PDF ini akan terlampir pada email yang dikirim.');

        html += '<div class="preview-attachment' + cls + '">' +
            '<i class="fa ' + icon + ' pa-icon"></i>' +
            '<div class="pa-body">' +
                '<div class="pa-title"><i class="fa fa-file-pdf-o"></i> ' + judul + '</div>' +
                '<div class="pa-file">' + escapeHtml(pdf.nama) + '</div>' +
                (catatan ? '<span class="pa-note">' + catatan + '</span>' : '') +
            '</div>' +
            '<div class="pa-actions">' +
                '<button type="button" class="btn btn-default btn-xs" id="btnTogglePdf" title="Tampilkan isi PDF di sini">' +
                    '<i class="fa fa-eye"></i> Pratinjau PDF' +
                '</button>' +
                '<a href="' + escapeHtml(pdf.url) + '" target="_blank" rel="noopener" class="btn btn-default btn-xs" title="Buka PDF di tab baru">' +
                    '<i class="fa fa-external-link"></i> Buka' +
                '</a>' +
                '<a href="' + escapeHtml(pdf.url) + '" download class="btn btn-default btn-xs" title="Unduh PDF">' +
                    '<i class="fa fa-download"></i> Unduh' +
                '</a>' +
            '</div>' +
        '</div>' +
        '<div class="pdf-viewer-wrap hidden" id="pdfViewerWrap"></div>';
    } else if (t && (t.attachment_pdf_url || t.pdf_url || t.pdf_path || t.lampiran_pdf)) {
        // Ada data mentah tapi URL kosong setelah normalize (mungkin whitespace)
        console.warn('[Preview PDF] Field PDF ada tapi kosong setelah normalize:', t);
    }

    strip.innerHTML = html;

    const btnPdf  = document.getElementById('btnTogglePdf');
    const wrapPdf = document.getElementById('pdfViewerWrap');
    if (btnPdf && wrapPdf && pdf.url) {
        btnPdf.addEventListener('click', function () {
            if (wrapPdf.classList.contains('hidden')) {
                // iframe dibuat saat diklik saja (hemat bandwidth)
                wrapPdf.innerHTML = '<iframe src="' + escapeHtml(pdf.url) + '#view=FitH" title="Pratinjau PDF"></iframe>';
                wrapPdf.classList.remove('hidden');
                btnPdf.innerHTML = '<i class="fa fa-eye-slash"></i> Tutup Pratinjau';
            } else {
                wrapPdf.innerHTML = '';
                wrapPdf.classList.add('hidden');
                btnPdf.innerHTML = '<i class="fa fa-eye"></i> Pratinjau PDF';
            }
        });
    }
}

/* ==========================================================
   PREVIEW — RENDER UTAMA
   ========================================================== */
function contohPenerima() {
    if (penerimaMap.size === 0) {
        return {
            nama:            'Nama Penerima',
            nama_perusahaan: 'PT Contoh Perusahaan',
            no_hp:           '081234567890',
            email:           'email@contoh.com',
            domisili:        'Kota',
            nyata:           false
        };
    }
    const first = penerimaMap.values().next().value;
    return {
        nama:            first.nama || '-',
        nama_perusahaan: first.nama_perusahaan || '-',
        no_hp:           first.no_hp || '-',
        email:           first.email || '-',
        domisili:        first.domisili || '-',
        nyata:           true
    };
}

function renderPreview() {
    const t = TEMPLATES.find(x => x.id === Number($('template_email_id').value));
    const emptyEl = $('previewEmpty');
    const bodyEl  = $('previewBody');

    if (!t) {
        if (emptyEl) emptyEl.classList.remove('hidden');
        if (bodyEl)  bodyEl.classList.add('hidden');
        $('infoContoh').textContent = '';
        return;
    }

    if (emptyEl) emptyEl.classList.add('hidden');
    if (bodyEl)  bodyEl.classList.remove('hidden');

    const s = contohPenerima();
    const subjRaw      = $('subjek_email').value || t.subjek || '';
    const subjRendered = ganti(subjRaw, s);
    const htmlRendered = buildHtmlPreview(t.html || '', s);

    let teksRendered;
    if (t.teks && t.teks.trim()) {
        teksRendered = ganti(t.teks, s);
    } else {
        teksRendered = htmlKeTeks(t.html || '', s);
    }

    renderInfoStrip(t, s, subjRendered);

    const pdf = getPdfInfo(t);
    let pdfLine = '';
    if (pdf.url) {
        pdfLine = (pdf.tipe === 'attachment' ? 'Lampiran   : ' : 'PDF (link) : ') + pdf.nama + '\n' +
                  'URL        : ' + pdf.url + '\n';
    }

    const header =
        'Kepada     : ' + (s.nama || '-') + ' <' + (s.email || '-') + '>\n' +
        'Perusahaan : ' + (s.nama_perusahaan || '-') + '\n' +
        'No HP      : ' + (s.no_hp || '-') + '\n' +
        'Domisili   : ' + (s.domisili || '-') + '\n' +
        'Subjek     : ' + subjRendered + '\n' +
        pdfLine +
        '─────────────────────────────────────────────────────────────\n\n';

    $('previewTeks').value = header + teksRendered;
    $('previewHtml').srcdoc = htmlRendered;

    $('infoContoh').textContent = s.nyata
        ? '(contoh: ' + (s.nama || 'penerima pertama') + ')' +
          (pdf.url ? (pdf.tipe === 'link' ? ' • 🔗 ' : ' • 📎 ') + pdf.nama : '')
        : '(data dummy)' + (pdf.url ? (pdf.tipe === 'link' ? ' • 🔗 ' : ' • 📎 ') + pdf.nama : '');
}

$('boxPreviewHeader').addEventListener('click', function (e) {
    if (e.target.closest('#previewTabs') || e.target.closest('button')) return;
    $('boxPreview').classList.toggle('collapsed');
});

/* ==========================================================
   TEMPLATE DROPDOWN
   ========================================================== */
function isiTemplate(kategoriId, pilihId) {
    const sel  = $('template_email_id');
    const list = TEMPLATES.filter(t => t.kategori_id === Number(kategoriId));
    sel.innerHTML = '';

    if (!kategoriId) {
        sel.innerHTML = '<option value="">-- Pilih kategori dulu --</option>';
        sel.disabled = true;
    } else if (list.length === 0) {
        sel.innerHTML = '<option value="">(Belum ada template di kategori ini)</option>';
        sel.disabled = true;
    } else {
        sel.innerHTML = '<option value="">-- Pilih Template --</option>';
        list.forEach(t => {
            const o = document.createElement('option');
            o.value = t.id;
            const pdf = getPdfInfo(t);
            o.textContent = t.nama + (pdf.url ? (pdf.tipe === 'link' ? ' 🔗' : ' 📎') : '');
            if (t.id === pilihId) o.selected = true;
            sel.appendChild(o);
        });
        sel.disabled = false;
    }
    renderPreview();
}

$('kategori_id').addEventListener('change', function () {
    $('subjek_email').value = '';
    isiTemplate(this.value, 0);
});

$('template_email_id').addEventListener('change', function () {
    const t = TEMPLATES.find(x => x.id === Number(this.value));
    $('subjek_email').value = t ? t.subjek : '';
    renderPreview();
    if (t) $('boxPreview').classList.remove('collapsed');
});

$('subjek_email').addEventListener('input', renderPreview);

/* ==========================================================
   TAB PREVIEW
   ========================================================== */
$('tabTeks').addEventListener('click', (e) => {
    e.stopPropagation();
    $('previewTeks').classList.remove('hidden'); $('previewHtml').classList.add('hidden');
    $('tabTeks').classList.add('active'); $('tabHtml').classList.remove('active');
});
$('tabHtml').addEventListener('click', (e) => {
    e.stopPropagation();
    $('previewHtml').classList.remove('hidden'); $('previewTeks').classList.add('hidden');
    $('tabHtml').classList.add('active'); $('tabTeks').classList.remove('active');
});

/* ==========================================================
   RENDER TABEL PENERIMA TERPILIH
   ========================================================== */
function renderPenerimaTerpilih() {
    const wrap = $('penerimaTerpilihList');
    const hiddenWrap = $('hiddenPenerimaWrap');

    $('jumlahDipilih').textContent = penerimaMap.size;
    cekCfgMasihValid();
    kcUpdate();

    hiddenWrap.innerHTML = '';
    penerimaMap.forEach((p) => {
        const inp = document.createElement('input');
        inp.type = 'hidden';
        inp.name = 'penerima[]';
        inp.value = p.id;
        hiddenWrap.appendChild(inp);
    });

    if (penerimaMap.size === 0) {
        wrap.innerHTML =
            '<div class="penerima-empty">' +
                '<i class="fa fa-users"></i>' +
                'Belum ada penerima dipilih.' +
                '<div><small>Penerima yang dipilih akan tampil dalam tabel ini.</small></div>' +
            '</div>';
        renderPreview();
        return;
    }

    let html =
        '<div class="penerima-table-wrap">' +
        '<table id="tablePenerimaTerpilih" class="table table-bordered table-striped table-hover">' +
        '<thead><tr>' +
        '<th style="width:48px" class="text-center">No.</th>' +
        '<th>Nama</th>' +
        '<th>Nama Perusahaan</th>' +
        '<th>Email</th>' +
        '<th>Domisili</th>' +
        '<th style="width:75px" class="text-center">Aksi</th>' +
        '</tr></thead><tbody>';

    let no = 1;
    penerimaMap.forEach((p) => {
        html += '<tr>' +
            '<td class="text-center text-muted">' + no++ + '</td>' +
            '<td><span class="penerima-name">' + escapeHtml(p.nama || '-') + '</span></td>' +
            '<td><span class="penerima-company">' + escapeHtml(p.nama_perusahaan || '-') + '</span></td>' +
            '<td><span class="penerima-email">' + escapeHtml(p.email || '-') + '</span></td>' +
            '<td>' + escapeHtml(p.domisili || '-') + '</td>' +
            '<td class="text-center"><button type="button" class="btn btn-danger btn-xs btn-hapus-penerima" title="Hapus penerima" data-id="' + escapeHtml(p.id) + '"><i class="fa fa-trash"></i></button></td>' +
            '</tr>';
    });

    html += '</tbody></table></div>';
    wrap.innerHTML = html;

    if (window.jQuery && window.jQuery.fn && window.jQuery.fn.DataTable) {
        window.jQuery('#tablePenerimaTerpilih').DataTable({
            pageLength: 5,
            lengthMenu: [[5, 10, 25, 50, 100, -1], [5, 10, 25, 50, 100, 'Semua']],
            searching: true,
            ordering: true,
            info: true,
            autoWidth: false,
            responsive: true,
            language: {
                search: 'Cari:',
                lengthMenu: 'Tampilkan _MENU_ data',
                info: 'Menampilkan _START_–_END_ dari _TOTAL_ penerima',
                infoEmpty: 'Belum ada penerima',
                zeroRecords: 'Data penerima tidak ditemukan',
                emptyTable: 'Belum ada penerima dipilih',
                paginate: { first: 'Awal', last: 'Akhir', next: '›', previous: '‹' }
            },
            columnDefs: [{ targets: [0, 5], orderable: false }]
        });
    }

    wrap.querySelectorAll('.btn-hapus-penerima').forEach(el => {
        el.addEventListener('click', function () {
            penerimaMap.delete(String(this.dataset.id));
            renderPenerimaTerpilih();
        });
    });

    renderPreview();
}

/* ==========================================================
   MODAL PILIH KONTAK
   ========================================================== */
let kontakDataTable = null;

function getModalRows() {
    return Array.from(document.querySelectorAll('#tableKontakModal tbody tr.kontak-modal-row'));
}

/* ---- KATEGORI KONTAK: state filter & mode tampilan ---- */
let ktFilter = '';          // '' = semua kategori, '0' = tanpa kategori, '<id>' = kategori tertentu
let ktMode   = 'semua';     // 'semua' (tanpa pengelompokan) | 'kategori' (dikelompokkan)
const KT_COL = 5;           // indeks kolom "Kategori" di tabel modal

function lolosKategori(tr) {
    return ktFilter === '' || String(tr.dataset.kategori || '0') === ktFilter;
}

/** ID kontak yang sedang "tampil": lolos pencarian teks DAN filter kategori (lintas semua halaman). */
function getAllFilteredIds() {
    if (kontakDataTable) {
        const ids = [];
        kontakDataTable.rows({ search: 'applied' }).nodes().each(function (tr) {
            ids.push(String(tr.dataset.id));
        });
        return ids;
    }
    // Fallback tanpa DataTables
    const q = ((document.getElementById('searchKontakModal') || {}).value || '').trim().toLowerCase();
    return getModalRows().filter(tr => {
        if (!lolosKategori(tr)) return false;
        if (!q) return true;
        const hay = ((tr.dataset.nama || '') + ' ' + (tr.dataset.namaPerusahaan || '') + ' ' +
                     (tr.dataset.noHp || '') + ' ' + (tr.dataset.email || '') + ' ' +
                     (tr.dataset.kota || '') + ' ' + (tr.dataset.kategoriNama || '')).toLowerCase();
        return hay.indexOf(q) !== -1;
    }).map(tr => String(tr.dataset.id));
}

/** Kelompokkan ID yang tampil per kategori: { '<idKategori>': [ids...] } */
function idsTampilPerKategori() {
    const out = {};
    const ids = getAllFilteredIds();
    ids.forEach(id => {
        const d   = kontakDataMap.get(String(id));
        const kat = d ? String(d.kategori_id || '0') : '0';
        (out[kat] = out[kat] || []).push(id);
    });
    return out;
}

function sisipkanBarisGrup(api) {
    if (ktMode !== 'kategori') return;
    const rows = api.rows({ page: 'current' }).nodes();
    const perKat = idsTampilPerKategori();
    let last = null;
    rows.each(function (tr) {
        const kat = String(tr.dataset.kategori || '0');
        if (kat === last) return;
        last = kat;
        const total = (perKat[kat] || []).length;
        const g = document.createElement('tr');
        g.className = 'kt-group-row';
        g.dataset.kategori = kat;
        g.innerHTML =
            '<td colspan="6">' +
                '<input type="checkbox" class="check-group" title="Pilih / batalkan semua kontak di kategori ini">' +
                '<i class="fa fa-folder-open text-primary"></i> ' +
                '<strong>' + escapeHtml(tr.dataset.kategoriNama || 'Tanpa kategori') + '</strong> ' +
                '<span class="kt-badge">' + total + ' kontak</span>' +
                '<span class="kt-group-sel"></span>' +
                (ktFilter === '' ? '<a class="kt-group-lihat" data-kat="' + kat + '"><i class="fa fa-search"></i> lihat kategori ini saja</a>' : '') +
            '</td>';
        tr.parentNode.insertBefore(g, tr);
    });
}

function updateStatusGrup() {
    const grupRows = document.querySelectorAll('#tableKontakModal tbody tr.kt-group-row');
    if (!grupRows.length) return;
    const perKat = idsTampilPerKategori();
    grupRows.forEach(g => {
        const ids = perKat[g.dataset.kategori] || [];
        let sel = 0;
        ids.forEach(id => { if (workingSet.has(id)) sel++; });
        const cb = g.querySelector('.check-group');
        cb.checked = ids.length > 0 && sel === ids.length;
        cb.indeterminate = sel > 0 && sel < ids.length;
        const lbl = g.querySelector('.kt-group-sel');
        if (lbl) lbl.textContent = sel > 0 ? '(' + sel + ' dari ' + ids.length + ' terpilih)' : '';
    });
}

function updateJumlahTampil() {
    const el = document.getElementById('ktJumlahTampil');
    if (el) el.textContent = '(' + getAllFilteredIds().length + ')';
}

function updateCountModal() {
    const n = workingSet.size;
    $('countModalSelected').textContent = n;
    $('labelTambahkan').textContent = n > 0 ? '(' + n + ')' : '';
    $('btnTambahkanPenerima').disabled = n === 0;
}

function syncCheckAllState() {
    const ids = getAllFilteredIds();
    let checkedCount = 0;
    ids.forEach(id => { if (workingSet.has(id)) checkedCount++; });
    const ca = $('checkAllModal');
    ca.checked = ids.length > 0 && checkedCount === ids.length;
    ca.indeterminate = checkedCount > 0 && checkedCount < ids.length;
    updateStatusGrup();
    updateJumlahTampil();
}

function syncCheckboxFromWorkingSet() {
    getModalRows().forEach(tr => {
        const cb = tr.querySelector('.check-kontak');
        const id = String(cb.value);
        cb.checked = workingSet.has(id);
        tr.classList.toggle('selected-row', cb.checked);
    });
    syncCheckAllState();
    updateCountModal();
}

function filterRows(q) {
    q = q || '';
    if (kontakDataTable) {
        kontakDataTable.search(q).draw();
        syncCheckboxFromWorkingSet();
    } else {
        const rows = getModalRows();
        rows.forEach(tr => {
            const hay = (
                (tr.dataset.nama || '') + ' ' +
                (tr.dataset.namaPerusahaan || '') + ' ' +
                (tr.dataset.noHp || '') + ' ' +
                (tr.dataset.email || '') + ' ' +
                (tr.dataset.kota || '') + ' ' +
                (tr.dataset.kategoriNama || '')
            ).toLowerCase();
            const cocokTeks = !q || hay.indexOf(q.toLowerCase().trim()) !== -1;
            tr.style.display = (cocokTeks && lolosKategori(tr)) ? '' : 'none';
        });
        syncCheckAllState();
    }
}

function setRowChecked(tr, checked) {
    const cb = tr.querySelector('.check-kontak');
    cb.checked = checked;
    const id = String(cb.value);
    if (checked) workingSet.add(id); else workingSet.delete(id);
    tr.classList.toggle('selected-row', checked);
}

(function initModalTable() {
    const tbody = document.querySelector('#tableKontakModal tbody');

    document.querySelectorAll('#tableKontakModal tbody tr.kontak-modal-row').forEach(tr => {
        const id = String(tr.dataset.id);
        kontakDataMap.set(id, {
            id:              id,
            nama:            tr.dataset.nama           || '',
            nama_perusahaan: tr.dataset.namaPerusahaan || '',
            no_hp:           tr.dataset.noHp           || '',
            email:           tr.dataset.email          || '',
            domisili:        tr.dataset.kota           || '',
            kategori_id:     tr.dataset.kategori       || '0',
            kategori_nama:   tr.dataset.kategoriNama   || 'Tanpa kategori',
        });
    });

    if (window.jQuery && window.jQuery.fn && window.jQuery.fn.DataTable) {
        // Filter kategori (dipakai bersama pencarian teks bawaan DataTables)
        window.jQuery.fn.dataTable.ext.search.push(function (settings, data, dataIndex) {
            if (settings.nTable.id !== 'tableKontakModal' || ktFilter === '') return true;
            const tr = settings.aoData[dataIndex] && settings.aoData[dataIndex].nTr;
            return !tr || String(tr.dataset.kategori || '0') === ktFilter;
        });

        kontakDataTable = window.jQuery('#tableKontakModal').DataTable({
            pageLength: 25,
            lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, 'Semua']],
            searching: true,
            ordering: true,
            order: [[1, 'asc']],
            info: true,
            autoWidth: false,
            responsive: true,
            dom: 'rt<"row"<"col-sm-5"i><"col-sm-7"p>>',
            language: {
                search: 'Cari:',
                lengthMenu: 'Tampilkan _MENU_ data',
                info: 'Menampilkan _START_–_END_ dari _TOTAL_ kontak',
                infoEmpty: 'Tidak ada kontak tersedia',
                infoFiltered: '(disaring dari _MAX_ kontak)',
                zeroRecords: 'Tidak ada kontak yang cocok dengan pencarian',
                emptyTable: 'Belum ada kontak dengan email',
                paginate: { first: 'Awal', last: 'Akhir', next: '›', previous: '‹' }
            },
            columnDefs: [{ targets: 0, orderable: false, searchable: false }],
            drawCallback: function () {
                sisipkanBarisGrup(this.api());
                syncCheckboxFromWorkingSet();
            }
        });
    }

    tbody.addEventListener('click', function (e) {
        const tr = e.target.closest('tr.kontak-modal-row');
        if (!tr || e.target.tagName === 'INPUT') return;
        setRowChecked(tr, !tr.querySelector('.check-kontak').checked);
        syncCheckAllState();
        updateCountModal();
    });

    tbody.addEventListener('change', function (e) {
        if (!e.target.classList.contains('check-kontak')) return;
        const tr = e.target.closest('tr');
        const id = String(e.target.value);
        if (e.target.checked) workingSet.add(id); else workingSet.delete(id);
        tr.classList.toggle('selected-row', e.target.checked);
        syncCheckAllState();
        updateCountModal();
    });

    $('searchKontakModal').addEventListener('input', function () {
        filterRows(this.value);
    });

    $('btnResetSearch').addEventListener('click', function () {
        $('searchKontakModal').value = '';
        filterRows('');
        $('searchKontakModal').focus();
    });

    $('checkAllModal').addEventListener('change', function () {
        const checked = this.checked;
        const ids = getAllFilteredIds();
        if (checked) {
            ids.forEach(id => workingSet.add(id));
        } else {
            ids.forEach(id => workingSet.delete(id));
        }
        syncCheckboxFromWorkingSet();
        updateCountModal();
    });

    /* ---- Kontrol kategori ---- */
    function terapkanFilterDanMode() {
        if (kontakDataTable) {
            // Mode "Per Kategori": urut kategori dulu agar kontak sekategori berkumpul
            kontakDataTable.order(ktMode === 'kategori' ? [[KT_COL, 'asc'], [1, 'asc']] : [[1, 'asc']]);
            kontakDataTable.page(0).draw();
        } else {
            filterRows($('searchKontakModal').value);
        }
        syncCheckboxFromWorkingSet();
    }

    $('ktFilterKategori').addEventListener('change', function () {
        ktFilter = this.value;
        terapkanFilterDanMode();
    });

    document.querySelectorAll('#ktViewMode button').forEach(btn => {
        btn.addEventListener('click', function () {
            ktMode = this.dataset.mode;
            document.querySelectorAll('#ktViewMode button').forEach(b => b.classList.toggle('active', b === this));
            terapkanFilterDanMode();
        });
    });

    // Pilih / kosongkan semua yang sedang tampil (sesuai kategori + pencarian)
    $('btnPilihSemuaTampil').addEventListener('click', function () {
        getAllFilteredIds().forEach(id => workingSet.add(id));
        syncCheckboxFromWorkingSet();
    });
    $('btnKosongkanPilihan').addEventListener('click', function () {
        workingSet.clear();
        syncCheckboxFromWorkingSet();
    });

    // Header grup kategori: centang = pilih semua kontak kategori tsb
    tbody.addEventListener('change', function (e) {
        if (!e.target.classList.contains('check-group')) return;
        const g   = e.target.closest('tr.kt-group-row');
        const ids = idsTampilPerKategori()[g.dataset.kategori] || [];
        if (e.target.checked) ids.forEach(id => workingSet.add(id));
        else ids.forEach(id => workingSet.delete(id));
        syncCheckboxFromWorkingSet();
    });

    // "lihat kategori ini saja" → set filter dropdown
    tbody.addEventListener('click', function (e) {
        const a = e.target.closest('.kt-group-lihat');
        if (!a) return;
        $('ktFilterKategori').value = a.dataset.kat;
        ktFilter = a.dataset.kat;
        terapkanFilterDanMode();
    });
})();

/* ---- Kontrol modal ---- */
const modalKontakEl = $('modalKontak');
const modalJadwalEl = $('modalJadwal');
const backdrops = new Map();

const adaBs3 = () => !!(window.jQuery && window.jQuery.fn && window.jQuery.fn.modal);
const adaBs5 = () => !!(window.bootstrap && window.bootstrap.Modal);

function modalTampil(el, fokusId) {
    if (adaBs3()) {
        window.jQuery(el).modal('show');
    } else if (adaBs5()) {
        window.bootstrap.Modal.getOrCreateInstance(el).show();
    } else {
        el.style.display = 'block';
        el.classList.add('in', 'show');
        el.removeAttribute('aria-hidden');
        document.body.classList.add('modal-open');
        if (!backdrops.has(el)) {
            const bd = document.createElement('div');
            bd.className = 'modal-backdrop fade in show';
            document.body.appendChild(bd);
            backdrops.set(el, bd);
        }
    }
    if (fokusId) setTimeout(() => { const f = $(fokusId); if (f) f.focus(); }, 300);
}

function modalTutup(el) {
    if (adaBs3()) {
        window.jQuery(el).modal('hide');
    } else if (adaBs5()) {
        const inst = window.bootstrap.Modal.getInstance(el);
        if (inst) inst.hide();
    } else {
        el.style.display = 'none';
        el.classList.remove('in', 'show');
        el.setAttribute('aria-hidden', 'true');
        if (backdrops.has(el)) { backdrops.get(el).remove(); backdrops.delete(el); }
        if (backdrops.size === 0) document.body.classList.remove('modal-open');
    }
}

[modalKontakEl, modalJadwalEl].forEach(el => {
    document.body.appendChild(el);
    if (!adaBs3() && !adaBs5()) {
        el.querySelectorAll('[data-dismiss="modal"]').forEach(btn => btn.addEventListener('click', () => modalTutup(el)));
        el.addEventListener('click', e => { if (e.target === el) modalTutup(el); });
    }
});
if (!adaBs3() && !adaBs5()) {
    document.addEventListener('keydown', e => {
        if (e.key !== 'Escape') return;
        [modalJadwalEl, modalKontakEl].forEach(el => { if (el.style.display === 'block') modalTutup(el); });
    });
}

function bukaModalKontak() {
    workingSet = new Set(penerimaMap.keys());
    $('searchKontakModal').value = '';
    // reset filter kategori & mode tampilan ke "Semua Kontak"
    ktFilter = ''; ktMode = 'semua';
    $('ktFilterKategori').value = '';
    document.querySelectorAll('#ktViewMode button').forEach(b => b.classList.toggle('active', b.dataset.mode === 'semua'));
    if (kontakDataTable) kontakDataTable.order([[1, 'asc']]);
    filterRows('');
    syncCheckboxFromWorkingSet();
    modalTampil(modalKontakEl, 'searchKontakModal');
}

$('btnBukaModalKontak').addEventListener('click', bukaModalKontak);

$('btnTambahkanPenerima').addEventListener('click', function () {
    penerimaMap.clear();
    workingSet.forEach(id => {
        const data = kontakDataMap.get(String(id));
        if (!data) return;
        penerimaMap.set(String(id), { ...data });
    });
    modalTutup(modalKontakEl);
    renderPenerimaTerpilih();
    AppSwal.toast('success', penerimaMap.size + ' penerima dipilih', 2200);
});

/* ==========================================================
   MODE KIRIM
   ========================================================== */
const MIN_GAP_MENIT   = <?= (int) $minGap ?>;
const MAKS_PENGIRIMAN = <?= (int) $maksPengiriman ?>;
const OLD_JADWAL      = <?= json_encode([
    'mode'    => old('mode_kirim') ?: 'sekarang',
    'mulai'   => (string) old('jadwal_pengiriman'),
    'offsets' => array_values((array) old('plan_offset')),
    'akun'    => array_values((array) old('plan_akun')),
], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>;

const MODE_INFO = {
    sekarang:          { butuhWaktu: false, berulang: false, label: 'Kirim langsung',            simpan: 'Simpan & Kirim Sekarang' },
    sekarang_berulang: { butuhWaktu: false, berulang: true,  label: 'Kirim langsung + berulang', simpan: 'Simpan & Mulai Kirim Bertahap' },
    jadwal:            { butuhWaktu: true,  berulang: false, label: 'Terjadwal',                 simpan: 'Simpan & Jadwalkan' },
    jadwal_berulang:   { butuhWaktu: true,  berulang: true,  label: 'Terjadwal + berulang',      simpan: 'Simpan & Jadwalkan Bertahap' }
};

let cfg = null;
let plan = [];
let barisTerbuka = -1;
let totalDiisiUser = false;

const modeRadio  = name => (document.querySelector('input[name="' + name + '"]:checked') || {}).value;
const modeDipilih = () => modeRadio('mode_kirim') || 'sekarang';
const pad     = n => String(n).padStart(2, '0');
const keLocal = d => d.getFullYear() + '-' + pad(d.getMonth() + 1) + '-' + pad(d.getDate()) + 'T' + pad(d.getHours()) + ':' + pad(d.getMinutes());
const fmt     = d => d.toLocaleString('id-ID', { weekday: 'short', day: '2-digit', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' });
const rand    = (a, b) => Math.floor(Math.random() * (b - a + 1)) + a;
const bacaInt = (id, def) => { const v = parseInt($(id).value, 10); return isNaN(v) ? def : v; };
const poolIds = () => Array.from(penerimaMap.keys());

function acakUrut(arr) {
    const a = arr.slice();
    for (let i = a.length - 1; i > 0; i--) {
        const j = Math.floor(Math.random() * (i + 1));
        [a[i], a[j]] = [a[j], a[i]];
    }
    return a;
}

function fmtDurasi(menit) {
    if (menit < 60) return menit + ' menit';
    if (menit >= 1440 && menit % 1440 === 0) return (menit / 1440) + ' hari';
    const j = Math.floor(menit / 60), m = menit % 60;
    return j + ' jam' + (m ? ' ' + m + ' menit' : '');
}

function tampilError(msg) { const e = $('mjError'); e.textContent = msg; e.classList.remove('hidden'); }
function sembunyiError()  { $('mjError').classList.add('hidden'); }

const dipakaiSemua = () => { const set = new Set(); plan.forEach(r => r.akun.forEach(id => set.add(id))); return set; };
const bebasIds     = () => { const d = dipakaiSemua(); return poolIds().filter(id => !d.has(id)); };

function namaAkun(id) {
    const p = penerimaMap.get(id);
    if (!p) return ('#' + id);
    const nm = p.nama || '';
    const np = p.nama_perusahaan || '';
    return nm && np ? (nm + ' (' + np + ')') : (nm || np || p.email || ('#' + id));
}

function mulaiWaktu() {
    if (!MODE_INFO[modeDipilih()].butuhWaktu) return new Date();
    const v = $('mj_mulai').value;
    const d = v ? new Date(v) : null;
    return (d && !isNaN(d)) ? d : null;
}

function teksWaktu(i, offset) {
    if (MODE_INFO[modeDipilih()].butuhWaktu) {
        const m = mulaiWaktu();
        return m ? fmt(new Date(m.getTime() + offset * 60000)) : '?';
    }
    return i === 0 ? 'Sekarang' : 'Sekarang + ' + fmtDurasi(offset);
}

function ambilTotal() {
    let t;
    if (modeRadio('mj_total_mode') === 'acak') {
        const a = bacaInt('mj_total_min', 0), b = bacaInt('mj_total_max', 0);
        if (!(a >= 2 && b >= a)) throw new Error('Rentang total pengiriman tidak valid (minimal 2, maksimal ≥ minimal).');
        t = rand(a, b);
    } else {
        t = bacaInt('mj_total', 0);
        if (!(t >= 2)) throw new Error('Total pengiriman minimal 2.');
    }
    if (t > MAKS_PENGIRIMAN) throw new Error('Total pengiriman maksimal ' + MAKS_PENGIRIMAN + '.');
    return t;
}

function ambilUkuran() {
    if (modeRadio('mj_akun_mode') === 'acak') {
        const a = bacaInt('mj_akun_min', 0), b = bacaInt('mj_akun_max', 0);
        if (!(a >= 1 && b >= a)) throw new Error('Rentang jumlah akun tidak valid (minimal 1, maksimal ≥ minimal).');
        return rand(a, b);
    }
    const k = bacaInt('mj_akun', 0);
    if (!(k >= 1)) throw new Error('Isi jumlah akun per pengiriman (minimal 1).');
    return k;
}

function ambilGap() {
    const unit = $('mj_gap_satuan').value === 'jam' ? 60 : 1;
    let g;
    if (modeRadio('mj_waktu_mode') === 'acak') {
        const a = bacaInt('mj_gap_min', 0), b = bacaInt('mj_gap_max', 0);
        if (!(a >= 1 && b >= a)) throw new Error('Rentang jarak waktu tidak valid (minimal 1, maksimal ≥ minimal).');
        g = rand(a, b);
    } else {
        g = bacaInt('mj_gap', 0);
        if (!(g >= 1)) throw new Error('Isi jarak waktu antar pengiriman (minimal 1).');
    }
    const menit = g * unit;
    if (menit < MIN_GAP_MENIT) throw new Error('Jarak antar pengiriman minimal ' + MIN_GAP_MENIT + ' menit.');
    return menit;
}

function buatRencana() {
    try {
        const pool = acakUrut(poolIds());
        if (pool.length < 2) throw new Error('Pilih minimal 2 penerima terlebih dahulu.');
        const total = ambilTotal();
        const baru = [];
        let p = 0, offset = 0;
        for (let i = 0; i < total && p < pool.length; i++) {
            const k = ambilUkuran();
            if (i > 0) offset += ambilGap();
            baru.push({ offset: offset, akun: pool.slice(p, p + k) });
            p += k;
        }
        if (baru.length < 2) throw new Error('Penerima terlalu sedikit untuk dibagi ke minimal 2 pengiriman. Kecilkan jumlah akun per pengiriman.');
        plan = baru;
        barisTerbuka = -1;
        sembunyiError();
        renderPlan();
        if (baru.length < total) {
            tampilError('Penerima habis di pengiriman ke-' + baru.length + ', jadi rencana hanya berisi ' + baru.length +
                        ' pengiriman (dari ' + total + ' yang diminta).');
        }
    } catch (e) { tampilError(e.message); }
}

function lengkapiRencana() {
    try {
        const sisa = acakUrut(bebasIds());
        if (!sisa.length) return;
        let p = 0;
        while (p < sisa.length) {
            if (plan.length >= MAKS_PENGIRIMAN) throw new Error('Sudah mencapai batas ' + MAKS_PENGIRIMAN + ' pengiriman.');
            const k = ambilUkuran();
            const offset = plan.length ? plan[plan.length - 1].offset + ambilGap() : 0;
            plan.push({ offset: offset, akun: sisa.slice(p, p + k) });
            p += k;
        }
        sembunyiError();
        renderPlan();
    } catch (e) { tampilError(e.message); }
}

function tambahBaris() {
    try {
        const gap = plan.length ? ambilGap() : 0;
        const bebas = acakUrut(bebasIds());
        const k = bebas.length ? Math.min(ambilUkuran(), bebas.length) : 0;
        plan.push({ offset: (plan.length ? plan[plan.length - 1].offset : 0) + gap, akun: bebas.slice(0, k) });
        barisTerbuka = plan.length - 1;
        sembunyiError();
        renderPlan();
    } catch (e) { tampilError(e.message); }
}

function setJumlah(i, k) {
    const r = plan[i];
    k = Math.max(0, k);
    if (k < r.akun.length) {
        r.akun = r.akun.slice(0, k);
    } else if (k > r.akun.length) {
        r.akun = r.akun.concat(acakUrut(bebasIds()).slice(0, k - r.akun.length));
    }
}

function acakAkunBaris(i) {
    const r = plan[i];
    const n = r.akun.length || 1;
    const milikSendiri = new Set(r.akun);
    const dipakai = dipakaiSemua();
    r.akun = acakUrut(poolIds().filter(id => !dipakai.has(id) || milikSendiri.has(id))).slice(0, n);
}

function hapusBaris(i) {
    plan.splice(i, 1);
    if (plan.length && plan[0].offset !== 0) {
        const dasar = plan[0].offset;
        plan.forEach(r => { r.offset -= dasar; });
    }
    barisTerbuka = -1;
}

function ringkasAkun(r) {
    const nama = r.akun.map(namaAkun);
    const teks = nama.slice(0, 3).join(', ') + (nama.length > 3 ? ' +' + (nama.length - 3) + ' lainnya' : '');
    return { teks: teks || '(belum ada akun)', penuh: nama.join(', ') };
}

function buatPanelAkun(i) {
    const tr = document.createElement('tr');
    tr.className = 'plan-detail';
    const td = document.createElement('td');
    td.colSpan = 5;

    const cari = document.createElement('input');
    cari.type = 'text';
    cari.className = 'form-control input-sm pick-cari';
    cari.placeholder = 'Cari akun...';
    td.appendChild(cari);

    const list = document.createElement('div');
    list.className = 'pick-list';
    const lain = {};
    plan.forEach((r, j) => { if (j !== i) r.akun.forEach(id => { lain[id] = j + 1; }); });

    penerimaMap.forEach((p, id) => {
        const lbl = document.createElement('label');
        lbl.className = 'pick-item' + (lain[id] ? ' dipakai' : '');
        lbl.dataset.search = ((p.nama || '') + ' ' + (p.nama_perusahaan || '') + ' ' + p.email).toLowerCase();

        const cb = document.createElement('input');
        cb.type = 'checkbox';
        cb.className = 'pick-akun';
        cb.dataset.i = i;
        cb.value = id;
        cb.checked = plan[i].akun.includes(id);
        cb.disabled = !!lain[id];
        lbl.appendChild(cb);

        const nama = p.nama || '-';
        const perusahaan = p.nama_perusahaan ? (' — ' + p.nama_perusahaan) : '';
        lbl.appendChild(document.createTextNode(' ' + nama + perusahaan + ' <' + p.email + '>'));
        if (lain[id]) {
            const sm = document.createElement('small');
            sm.textContent = '  — sudah di pengiriman ke-' + lain[id];
            lbl.appendChild(sm);
        }
        list.appendChild(lbl);
    });
    td.appendChild(list);
    tr.appendChild(td);
    return tr;
}

function refreshBaris(i) {
    const tr = $('planBody').querySelector('tr[data-row="' + i + '"]');
    if (!tr) return;
    tr.querySelector('.plan-jumlah').value = plan[i].akun.length;
    const rk = ringkasAkun(plan[i]);
    const sp = tr.querySelector('.akun-prev');
    sp.textContent = rk.teks;
    sp.title = rk.penuh;
}

function renderPlan() {
    const info = MODE_INFO[modeDipilih()];
    $('thWaktu').textContent = info.butuhWaktu ? 'Waktu kirim' : 'Jarak dari pertama';
    const tb = $('planBody');
    tb.innerHTML = '';

    plan.forEach((r, i) => {
        const tr = document.createElement('tr');
        tr.dataset.row = i;
        tr.innerHTML =
            '<td><strong>' + (i + 1) + '</strong></td>' +
            '<td class="td-waktu"></td>' +
            '<td><input type="number" class="form-control input-sm plan-jumlah" min="1" data-i="' + i + '"></td>' +
            '<td><span class="akun-prev"></span></td>' +
            '<td>' +
                '<button type="button" class="btn btn-default btn-xs" data-aksi="pilih" data-i="' + i + '" title="Pilih akun manual"><i class="fa fa-users"></i></button> ' +
                '<button type="button" class="btn btn-default btn-xs" data-aksi="acak" data-i="' + i + '" title="Acak ulang akun baris ini"><i class="fa fa-random"></i></button> ' +
                '<button type="button" class="btn btn-danger btn-xs" data-aksi="hapus" data-i="' + i + '" title="Hapus pengiriman"><i class="fa fa-trash"></i></button>' +
            '</td>';

        const tdW = tr.querySelector('.td-waktu');
        if (i === 0) {
            tdW.textContent = info.butuhWaktu ? (mulaiWaktu() ? fmt(mulaiWaktu()) : '— isi waktu mulai —') : 'Sekarang';
        } else if (info.butuhWaktu) {
            const m = mulaiWaktu();
            const inp = document.createElement('input');
            inp.type = 'datetime-local';
            inp.className = 'form-control input-sm plan-waktu';
            inp.dataset.i = i;
            if (m) inp.value = keLocal(new Date(m.getTime() + r.offset * 60000));
            tdW.appendChild(inp);
        } else {
            const g = document.createElement('div');
            g.className = 'input-group input-group-sm';
            const inp = document.createElement('input');
            inp.type = 'number';
            inp.min = 1;
            inp.className = 'form-control plan-offset';
            inp.dataset.i = i;
            inp.value = r.offset;
            const ad = document.createElement('span');
            ad.className = 'input-group-addon';
            ad.textContent = 'menit';
            g.appendChild(inp);
            g.appendChild(ad);
            tdW.appendChild(g);
        }

        tr.querySelector('.plan-jumlah').value = r.akun.length;
        const rk = ringkasAkun(r);
        const sp = tr.querySelector('.akun-prev');
        sp.textContent = rk.teks;
        sp.title = rk.penuh;
        tb.appendChild(tr);

        if (i === barisTerbuka) tb.appendChild(buatPanelAkun(i));
    });

    renderRingkasanPlan();
}

function renderRingkasanPlan() {
    const box = $('planRingkas');
    box.innerHTML = '';
    const M = penerimaMap.size, dipakai = dipakaiSemua().size, sisa = M - dipakai;
    $('btnLengkapi').disabled = sisa <= 0 || plan.length === 0;
    if (!plan.length) return;

    const err = validasiJadwal();
    const c = document.createElement('div');
    c.className = 'callout ' + (err ? 'callout-danger' : (sisa > 0 ? 'callout-warning' : 'callout-success'));

    const baris = (teks, ikon) => {
        const p = document.createElement('p');
        p.style.margin = '0 0 4px';
        const i = document.createElement('i');
        i.className = 'fa ' + ikon;
        p.appendChild(i);
        p.appendChild(document.createTextNode(' ' + teks));
        c.appendChild(p);
    };

    baris(plan.length + ' pengiriman • ' + dipakai + ' dari ' + M + ' penerima kebagian • pengiriman terakhir ± ' +
          fmtDurasi(plan[plan.length - 1].offset) + ' setelah yang pertama.', 'fa-list-ol');
    if (sisa > 0) baris(sisa + ' penerima belum kebagian dan TIDAK akan dikirimi. Klik "Lengkapi semua penerima" untuk menambah pengiriman.', 'fa-exclamation-triangle');
    if (err) baris(err, 'fa-times-circle');
    box.appendChild(c);
}

function validasiJadwal() {
    const m = modeDipilih(), info = MODE_INFO[m];
    if (m === 'sekarang') return '';

    if (info.butuhWaktu) {
        const t = mulaiWaktu();
        if (!t) return 'Tanggal & jam pengiriman pertama wajib diisi.';
        if (t <= new Date()) return 'Waktu pengiriman pertama harus di masa depan.';
    }
    if (!info.berulang) return '';

    if (plan.length < 2) return 'Rencana minimal 2 pengiriman.';
    if (plan.length > MAKS_PENGIRIMAN) return 'Maksimal ' + MAKS_PENGIRIMAN + ' pengiriman.';
    const dipakai = new Set();
    for (let i = 0; i < plan.length; i++) {
        const r = plan[i];
        if (!r.akun.length) return 'Pengiriman ke-' + (i + 1) + ' belum punya akun.';
        if (i > 0 && (r.offset - plan[i - 1].offset) < MIN_GAP_MENIT) {
            return 'Pengiriman ke-' + (i + 1) + ' harus minimal ' + MIN_GAP_MENIT + ' menit setelah pengiriman ke-' + i + '.';
        }
        for (const id of r.akun) {
            if (!penerimaMap.has(id)) return 'Ada akun di rencana yang sudah tidak ada di daftar penerima. Buat ulang rencana.';
            if (dipakai.has(id)) return 'Satu akun hanya boleh ada di satu pengiriman.';
            dipakai.add(id);
        }
    }
    return '';
}

function buatCfg() {
    const m = modeDipilih(), info = MODE_INFO[m];
    const mulai = info.butuhWaktu ? $('mj_mulai').value : '';
    if (!info.berulang) {
        return { mode: m, mulai: mulai, total: 1, offsets: [0], akun: null,
                 tampil: [info.butuhWaktu ? fmt(new Date(mulai)) : 'Sekarang'] };
    }
    return {
        mode: m, mulai: mulai, total: plan.length,
        offsets: plan.map(r => r.offset),
        akun: plan.map(r => r.akun.slice()),
        tampil: plan.map((r, i) => (i + 1) + '. ' + teksWaktu(i, r.offset) + ' — ' + r.akun.length + ' akun')
    };
}

function terapkanJadwal() {
    const err = validasiJadwal();
    if (err) { tampilError(err); return; }
    cfg = buatCfg();
    tulisHiddenJadwal();
    modalTutup(modalJadwalEl);
    renderRingkasan();
    kcUpdate();
}

function tulisHiddenJadwal() {
    const wrap = $('hiddenJadwalWrap');
    wrap.innerHTML = '';
    if (!cfg) return;
    const tambah = (nama, nilai) => {
        const i = document.createElement('input');
        i.type = 'hidden'; i.name = nama; i.value = nilai;
        wrap.appendChild(i);
    };
    const info = MODE_INFO[cfg.mode];
    if (info.butuhWaktu) tambah('jadwal_pengiriman', cfg.mulai);
    if (info.berulang) {
        tambah('total_putaran', cfg.total);
        cfg.offsets.forEach(o => tambah('plan_offset[]', o));
        cfg.akun.forEach(a => tambah('plan_akun[]', a.join(',')));
    }
}

function cekCfgMasihValid() {
    if (!cfg || !cfg.akun) return;
    if (cfg.akun.some(list => list.some(id => !penerimaMap.has(id)))) {
        cfg = null;
        tulisHiddenJadwal();
        renderRingkasan();
    }
}

function renderRingkasan() {
    const m = modeDipilih(), info = MODE_INFO[m], box = $('ringkasJadwal');
    box.innerHTML = '';

    const callout = document.createElement('div');
    callout.className = 'callout';
    callout.style.marginBottom = '0';

    if (m === 'sekarang') {
        callout.className += ' callout-info';
        callout.innerHTML = '<p><i class="fa fa-bolt"></i> Email dikirim <strong>langsung</strong> ke <strong id="rjJumlah"></strong> penerima setelah disimpan.</p>';
        box.appendChild(callout);
        $('rjJumlah').textContent = penerimaMap.size;
        return;
    }

    if (!cfg || cfg.mode !== m) {
        callout.className += ' callout-warning';
        callout.innerHTML = '<h4><i class="fa fa-exclamation-triangle"></i> Jadwal belum diatur</h4>' +
            '<p>Klik tombol di bawah untuk mengatur ' + (info.berulang ? 'rencana pengiriman' : 'tanggal &amp; jam pengiriman') + '.</p>';
        const btn = document.createElement('button');
        btn.type = 'button'; btn.className = 'btn btn-warning';
        btn.innerHTML = '<i class="fa fa-cog"></i> Atur Jadwal';
        btn.addEventListener('click', bukaModalJadwal);
        callout.appendChild(btn);
        box.appendChild(callout);
        return;
    }

    callout.className += ' callout-success';
    const h = document.createElement('h4');
    h.innerHTML = '<i class="fa fa-check-circle"></i> ';
    h.appendChild(document.createTextNode(info.label + ' — ' + cfg.total + ' pengiriman'));
    callout.appendChild(h);

    if (info.berulang) {
        const jml = cfg.akun.reduce((t, l) => t + l.length, 0);
        const p = document.createElement('p');
        p.textContent = jml + ' akun dibagi ke ' + cfg.total + ' pengiriman (setiap akun menerima 1 kali).' +
            (jml < penerimaMap.size ? ' ' + (penerimaMap.size - jml) + ' penerima terpilih tidak ikut dikirimi.' : '');
        callout.appendChild(p);
    }

    const ol = document.createElement('ol');
    const batas = 6;
    cfg.tampil.slice(0, batas).forEach(t => {
        const li = document.createElement('li');
        li.textContent = t.replace(/^\d+\.\s/, '');
        ol.appendChild(li);
    });
    if (cfg.tampil.length > batas) {
        const li = document.createElement('li');
        li.style.listStyle = 'none';
        li.textContent = '… dan ' + (cfg.tampil.length - batas) + ' pengiriman lainnya';
        ol.appendChild(li);
    }
    callout.appendChild(ol);

    const btn = document.createElement('button');
    btn.type = 'button'; btn.className = 'btn btn-default btn-sm'; btn.style.marginTop = '8px';
    btn.innerHTML = '<i class="fa fa-pencil"></i> Ubah jadwal';
    btn.addEventListener('click', bukaModalJadwal);
    callout.appendChild(btn);
    box.appendChild(callout);
}

function updateMode() {
    const m = modeDipilih();
    document.querySelectorAll('.mode-card[data-mode]').forEach(c => c.classList.toggle('selected', c.dataset.mode === m));
    $('labelSimpan').textContent = MODE_INFO[m].simpan;
    renderRingkasan();
    kcUpdate();
}

function formUpdate() {
    const tm = modeRadio('mj_total_mode'), am = modeRadio('mj_akun_mode'), wm = modeRadio('mj_waktu_mode');
    $('mjTotalManual').classList.toggle('hidden', tm === 'acak');
    $('mjTotalAcak').classList.toggle('hidden', tm !== 'acak');
    $('mjAkunTetap').classList.toggle('hidden', am === 'acak');
    $('mjAkunAcak').classList.toggle('hidden', am !== 'acak');
    $('mjGapTetap').classList.toggle('hidden', wm === 'acak');
    $('mjGapAcak').classList.toggle('hidden', wm !== 'acak');

    document.querySelectorAll('.mj-pill').forEach(pill => {
        const name = pill.dataset.pill;
        const val = pill.dataset.value;
        const checked = document.querySelector('input[name="' + name + '"][value="' + val + '"]').checked;
        pill.classList.toggle('selected', checked);
    });
}

function bukaModalJadwal() {
    const m = modeDipilih(), info = MODE_INFO[m];
    if (m === 'sekarang') return;

    $('judulModalJadwal').textContent = 'Atur Jadwal — ' + info.label;
    $('mjStepTitle').textContent = info.butuhWaktu ? 'Atur Waktu & Rencana Pengiriman' : 'Atur Rencana Pengiriman Bertahap';
    $('mjStepDesc').textContent = info.berulang
        ? 'Tentukan kapan mulai, lalu bagi penerima ke beberapa pengiriman dengan waktu yang diatur.'
        : 'Pilih tanggal & jam kapan email akan dikirim otomatis.';

    $('mjBoxMulai').classList.toggle('hidden', !info.butuhWaktu);
    $('mjBoxRencana').classList.toggle('hidden', !info.berulang);
    $('mjLabelMulai').textContent = info.berulang ? 'Mulai pada (pengiriman pertama)' : 'Kirim pada';

    $('mj_mulai').min = keLocal(new Date());
    if (info.butuhWaktu && !$('mj_mulai').value) {
        $('mj_mulai').value = keLocal(new Date(Date.now() + 10 * 60000));
    }
    sembunyiError();

    if (info.berulang) {
        $('mjPool').textContent = penerimaMap.size;
        formUpdate();
        plan.forEach(r => { r.akun = r.akun.filter(id => penerimaMap.has(id)); });
        if (!totalDiisiUser) $('mj_total').value = Math.max(2, Math.ceil(penerimaMap.size / 7.5));
        if (!plan.length && penerimaMap.size >= 2) buatRencana(); else renderPlan();
        if (penerimaMap.size < 2) tampilError('Pilih minimal 2 penerima dulu untuk dibagi ke beberapa pengiriman.');
    }
    modalTampil(modalJadwalEl, info.butuhWaktu ? 'mj_mulai' : 'mj_total');
}

document.querySelectorAll('input[name="mode_kirim"]').forEach(r => r.addEventListener('change', function () {
    if (cfg && cfg.mode !== modeDipilih()) { cfg = null; tulisHiddenJadwal(); }
    updateMode();
    if (modeDipilih() !== 'sekarang') bukaModalJadwal();
}));

['mj_total_mode', 'mj_akun_mode', 'mj_waktu_mode'].forEach(n =>
    document.querySelectorAll('input[name="' + n + '"]').forEach(r => r.addEventListener('change', formUpdate)));
$('mj_total').addEventListener('input', () => { totalDiisiUser = true; });
$('mj_mulai').addEventListener('change', () => { if (plan.length) renderPlan(); });
$('btnBuatRencana').addEventListener('click', buatRencana);
$('btnAcakUlang').addEventListener('click', buatRencana);
$('btnLengkapi').addEventListener('click', lengkapiRencana);
$('btnTambahBaris').addEventListener('click', tambahBaris);
$('btnTerapkanJadwal').addEventListener('click', terapkanJadwal);

$('planBody').addEventListener('change', function (e) {
    const t = e.target, i = parseInt(t.dataset.i, 10);
    if (isNaN(i) || !plan[i]) return;
    if (t.classList.contains('plan-jumlah')) {
        setJumlah(i, parseInt(t.value, 10) || 0);
        renderPlan();
    } else if (t.classList.contains('plan-offset')) {
        plan[i].offset = Math.max(0, parseInt(t.value, 10) || 0);
        renderPlan();
    } else if (t.classList.contains('plan-waktu')) {
        const m = mulaiWaktu(), d = t.value ? new Date(t.value) : null;
        if (m && d && !isNaN(d)) plan[i].offset = Math.round((d - m) / 60000);
        renderPlan();
    } else if (t.classList.contains('pick-akun')) {
        const id = t.value;
        if (t.checked) { if (!plan[i].akun.includes(id)) plan[i].akun.push(id); }
        else plan[i].akun = plan[i].akun.filter(x => x !== id);
        refreshBaris(i);
        renderRingkasanPlan();
    }
});

$('planBody').addEventListener('click', function (e) {
    const b = e.target.closest('button[data-aksi]');
    if (!b) return;
    const i = parseInt(b.dataset.i, 10);
    if (b.dataset.aksi === 'pilih')      barisTerbuka = (barisTerbuka === i) ? -1 : i;
    else if (b.dataset.aksi === 'acak')  acakAkunBaris(i);
    else if (b.dataset.aksi === 'hapus') hapusBaris(i);
    renderPlan();
});

$('planBody').addEventListener('input', function (e) {
    if (!e.target.classList.contains('pick-cari')) return;
    const q = e.target.value.toLowerCase();
    e.target.closest('td').querySelectorAll('.pick-item').forEach(el => {
        el.style.display = el.dataset.search.includes(q) ? '' : 'none';
    });
});

/* ==========================================================
   KECEPATAN PENGIRIMAN
   ========================================================== */
const AMBANG_BANYAK  = <?= (int) $ambangBanyak ?>;
const BATAS_HARIAN   = <?= (int) $batasHarian ?>;
const MAKS_KECEPATAN = <?= (int) $maksKecepatan ?>;
const OLD_KEC = <?= json_encode([
    'mode' => (string) old('kecepatan_mode'),
    'rate' => (int) old('kecepatan_per_menit'),
    'min'  => (int) old('kecepatan_min'),
    'maks' => (int) old('kecepatan_maks'),
], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>;

const kcMode   = () => modeRadio('kecepatan_mode') || 'bertahap';
const kcTampil = () => penerimaMap.size >= AMBANG_BANYAK && !MODE_INFO[modeDipilih()].berulang;

function kcLaju() {
    if (kcMode() === 'acak') {
        const a = bacaInt('kc_min', 0), b = bacaInt('kc_maks', 0);
        if (!(a >= 1 && b >= a && b <= MAKS_KECEPATAN)) return null;
        return { min: a, max: b, avg: (a + b) / 2 };
    }
    const r = bacaInt('kc_rate', 0);
    if (!(r >= 1 && r <= MAKS_KECEPATAN)) return null;
    return { min: r, max: r, avg: r };
}

function kcBaris(box, teks, ikon) {
    const p = document.createElement('p');
    p.style.margin = '0 0 4px';
    const i = document.createElement('i');
    i.className = 'fa ' + ikon;
    p.appendChild(i);
    p.appendChild(document.createTextNode(' ' + teks));
    box.appendChild(p);
}

function kcEstimasi() {
    const box = $('kcEstimasi');
    box.innerHTML = '';
    box.className = 'callout callout-info';
    box.style.marginTop = '8px';

    const n = penerimaMap.size, l = kcLaju();
    if (!l) {
        box.className = 'callout callout-danger';
        kcBaris(box, kcMode() === 'acak'
            ? 'Isi rentang 1 – ' + MAKS_KECEPATAN + ' email per menit (maksimal ≥ minimal).'
            : 'Isi jumlah email per menit antara 1 dan ' + MAKS_KECEPATAN + '.', 'fa-times-circle');
        return;
    }

    const menit = Math.ceil(n / l.avg);
    kcBaris(box, l.min === l.max
        ? n + ' penerima ÷ ' + l.min + ' email/menit = ± ' + fmtDurasi(menit) + '.'
        : n + ' penerima ÷ rata-rata ' + l.avg + ' email/menit (acak ' + l.min + '–' + l.max + ') = ± ' + fmtDurasi(menit) + '.', 'fa-clock-o');

    if (BATAS_HARIAN > 0 && n > BATAS_HARIAN) {
        box.className = 'callout callout-warning';
        kcBaris(box, n + ' email melebihi batas harian ' + BATAS_HARIAN + ' email / 24 jam. Sisanya otomatis ditunda sampai jatah tersedia.', 'fa-exclamation-triangle');
    }
}

function kcUpdate() {
    const tampil = kcTampil();
    $('kartuKecepatan').classList.toggle('hidden', !tampil);
    document.querySelectorAll('#kartuKecepatan input').forEach(i => { i.disabled = !tampil; });
    if (!tampil) return;

    $('kcJumlah').textContent = penerimaMap.size;
    const mode = kcMode();
    document.querySelectorAll('.kc-card').forEach(c => c.classList.toggle('selected', c.dataset.kc === mode));
    $('kcBoxTetap').classList.toggle('hidden', mode !== 'bertahap');
    $('kcBoxAcak').classList.toggle('hidden', mode !== 'acak');
    $('kc_rate').disabled = mode !== 'bertahap';
    $('kc_min').disabled  = mode !== 'acak';
    $('kc_maks').disabled = mode !== 'acak';
    if (mode === 'cepat') { $('kcEstimasi').innerHTML = ''; $('kcEstimasi').className = ''; }
    else kcEstimasi();
}

document.querySelectorAll('input[name="kecepatan_mode"]').forEach(r => r.addEventListener('change', kcUpdate));
['kc_rate', 'kc_min', 'kc_maks'].forEach(id => $(id).addEventListener('input', kcEstimasi));
document.querySelectorAll('.kc-preset').forEach(b => b.addEventListener('click', function () {
    $('kc_rate').value = this.dataset.rate;
    kcEstimasi();
}));
document.querySelectorAll('.kc-preset-acak').forEach(b => b.addEventListener('click', function () {
    $('kc_min').value = this.dataset.min;
    $('kc_maks').value = this.dataset.max;
    kcEstimasi();
}));

/* ==========================================================
   VALIDASI SUBMIT
   ========================================================== */
let sedangSimpan = false;

function ringkasanKonfirmasi() {
    const t    = TEMPLATES.find(x => x.id === Number($('template_email_id').value));
    const m    = modeDipilih();
    const mi   = MODE_INFO[m];
    const inp  = document.querySelector('input[name="nama_kampanye"]');
    const baris = [
        ['Kampanye',  (inp && inp.value.trim()) || '-'],
        ['Template',  t ? t.nama : '-'],
        ['Penerima',  penerimaMap.size + ' kontak'],
        ['Mode kirim', mi.label]
    ];
    if (m !== 'sekarang' && cfg) {
        if (mi.berulang) {
            baris.push(['Jumlah pengiriman', cfg.total + ' kali (penerima dibagi)']);
            baris.push(['Pengiriman pertama', String(cfg.tampil[0] || '-')]);
        } else {
            baris.push(['Waktu kirim', String(cfg.tampil[0] || '-')]);
        }
    }
    if (kcTampil()) {
        if (kcMode() === 'cepat') {
            baris.push(['Kecepatan', 'Tanpa pembatasan']);
        } else {
            const l = kcLaju();
            if (l) baris.push(['Kecepatan', (l.min === l.max ? l.min : l.min + '–' + l.max) + ' email/menit']);
        }
    }
    return '<table class="swal-ringkasan">' +
        baris.map(r => '<tr><th>' + AppSwal.esc(r[0]) + '</th><td>' + AppSwal.esc(r[1]) + '</td></tr>').join('') +
        '</table>';
}

$('formKampanye').addEventListener('submit', async function (e) {
    const form = this;
    if (form.dataset.sudahDikonfirmasi === '1') return;   // submit native setelah semua dialog disetujui
    e.preventDefault();
    if (sedangSimpan) return;
    sedangSimpan = true;
    let lanjut = false;

    try {
        if (!$('template_email_id').value) {
            await AppSwal.info({ ikon: 'warning', judul: 'Template belum dipilih', teks: 'Pilih kategori dan template terlebih dahulu.' });
            const k = $('kategori_id'); if (k) k.focus();
            return;
        }
        if (penerimaMap.size === 0) {
            await AppSwal.info({ ikon: 'warning', judul: 'Belum ada penerima', teks: 'Pilih minimal 1 penerima untuk kampanye ini.', tombol: 'Pilih Penerima' });
            bukaModalKontak();
            return;
        }
        if (kcTampil() && kcMode() !== 'cepat' && !kcLaju()) {
            await AppSwal.info({ ikon: 'warning', judul: 'Pengaturan kecepatan belum lengkap', teks: 'Lengkapi pengaturan email per menit terlebih dahulu.' });
            const f = $('kc_rate'); if (f && !f.disabled) f.focus();
            return;
        }

        const m = modeDipilih();
        if (m !== 'sekarang') {
            if (!cfg || cfg.mode !== m) {
                await AppSwal.info({ ikon: 'warning', judul: 'Jadwal belum diatur', teks: 'Atur jadwal pengiriman terlebih dahulu.', tombol: 'Atur Jadwal' });
                bukaModalJadwal();
                return;
            }
            const err = validasiJadwal();
            if (err) {
                cfg = null; tulisHiddenJadwal(); renderRingkasan();
                await AppSwal.info({ ikon: 'error', judul: 'Jadwal tidak valid', teks: err, tombol: 'Perbaiki Jadwal' });
                bukaModalJadwal();
                return;
            }
        }

        // Konfirmasi akhir (ringkasan kampanye)
        const tanpaBatas = kcTampil() && kcMode() === 'cepat';
        let html = ringkasanKonfirmasi();
        if (tanpaBatas) {
            html += '<p style="margin:14px 0 0;color:#c0392b;font-size:13px;"><i class="fa fa-exclamation-triangle"></i> ' +
                    'Anda akan mengirim ke <b>' + penerimaMap.size + '</b> penerima <b>tanpa pembatasan per menit</b>. ' +
                    'Akun SMTP bisa terkena limit.</p>';
        }
        const ok = await AppSwal.konfirmasi({
            judul:  tanpaBatas ? 'Kirim tanpa pembatasan?' : (m === 'sekarang' ? 'Kirim kampanye sekarang?' : 'Simpan & jadwalkan kampanye?'),
            html:   html,
            ikon:   tanpaBatas ? 'warning' : 'question',
            ya:     MODE_INFO[m].simpan,
            bahaya: tanpaBatas
        });
        if (!ok) return;

        AppSwal.loading('Menyimpan kampanye...', 'Jangan tutup halaman ini.');
        lanjut = true;
        form.dataset.sudahDikonfirmasi = '1';
        HTMLFormElement.prototype.submit.call(form);
    } finally {
        if (!lanjut) sedangSimpan = false;
    }
});

// Tombol "Kembali" browser (bfcache) -> pulihkan form agar bisa dikirim lagi
window.addEventListener('pageshow', function (ev) {
    if (!ev.persisted) return;
    AppSwal.tutup();
    $('formKampanye').dataset.sudahDikonfirmasi = '';
    sedangSimpan = false;
});

/* ==========================================================
   INIT
   ========================================================== */
(function init() {
    let kat = $('kategori_id').value;
    if (OLD_TEMPLATE) {
        const t = TEMPLATES.find(x => x.id === OLD_TEMPLATE);
        if (t) { kat = String(t.kategori_id); $('kategori_id').value = kat; }
    }
    isiTemplate(kat, OLD_TEMPLATE);

    if (Array.isArray(OLD_PENERIMA) && OLD_PENERIMA.length > 0) {
        OLD_PENERIMA.forEach(id => {
            const idStr = String(id);
            const data = kontakDataMap.get(idStr);
            if (data) {
                penerimaMap.set(idStr, { ...data });
            }
        });
    }

    if (OLD_KEC.mode === 'acak' || OLD_KEC.mode === 'cepat') {
        document.querySelector('input[name="kecepatan_mode"][value="' + OLD_KEC.mode + '"]').checked = true;
    }
    if (OLD_KEC.rate > 0) $('kc_rate').value = OLD_KEC.rate;
    if (OLD_KEC.min > 0)  $('kc_min').value  = OLD_KEC.min;
    if (OLD_KEC.maks > 0) $('kc_maks').value = OLD_KEC.maks;

    if (OLD_JADWAL.mode && OLD_JADWAL.mode !== 'sekarang' && MODE_INFO[OLD_JADWAL.mode]) {
        const info = MODE_INFO[OLD_JADWAL.mode];
        if (OLD_JADWAL.mulai) $('mj_mulai').value = OLD_JADWAL.mulai;
        if (info.berulang && OLD_JADWAL.offsets.length >= 2 && OLD_JADWAL.offsets.length === OLD_JADWAL.akun.length) {
            plan = OLD_JADWAL.offsets.map((o, i) => ({
                offset: parseInt(o, 10) || 0,
                akun: String(OLD_JADWAL.akun[i] || '').split(',').filter(x => x && penerimaMap.has(x))
            }));
            totalDiisiUser = true;
        }
        if (!validasiJadwal()) { cfg = buatCfg(); tulisHiddenJadwal(); }
    }

    updateMode();
    renderPenerimaTerpilih();
})();

}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', mulai);
} else {
    mulai();
}

})();
</script>
<?= $this->endSection() ?>