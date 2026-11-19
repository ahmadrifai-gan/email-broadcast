<?= $this->extend('layouts/main') ?>

<?= $this->section('page_title') ?>Import Data Perusahaan<?= $this->endSection() ?>
<?= $this->section('page_subtitle') ?>Unggah Excel berisi profil dan produk perusahaan<?= $this->endSection() ?>

<?= $this->section('breadcrumb') ?>
<li><a href="<?= site_url('/') ?>"><i class="fa fa-dashboard"></i> Home</a></li>
<li class="active">Import Data Perusahaan</li>
<?= $this->endSection() ?>

<?= $this->section('styles') ?>
<style>
    .ix-page { --ix-primary: #4f46e5; --ix-primary-soft: #eef2ff; --ix-success: #059669; --ix-border: #e5e7eb; --ix-text: #111827; --ix-muted: #6b7280; }
    .ix-header { display: flex; align-items: center; justify-content: space-between; gap: 16px; margin-bottom: 18px; }
    .ix-title-wrap { display: flex; align-items: center; gap: 12px; }
    .ix-title-icon { width: 46px; height: 46px; border-radius: 12px; background: var(--ix-primary-soft); color: var(--ix-primary); display: inline-flex; align-items: center; justify-content: center; font-size: 21px; }
    .ix-title-wrap h2 { margin: 0 0 3px; font-size: 20px; font-weight: 700; color: var(--ix-text); }
    .ix-title-wrap p { margin: 0; color: var(--ix-muted); }
    .ix-data-count { padding: 9px 13px; border: 1px solid var(--ix-border); border-radius: 10px; background: #fff; color: var(--ix-muted); white-space: nowrap; }
    .ix-data-count strong { color: var(--ix-primary); font-size: 17px; margin-right: 3px; }
    .ix-steps { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 10px; margin-bottom: 16px; }
    .ix-step { display: flex; align-items: center; gap: 10px; padding: 11px 13px; border: 1px solid var(--ix-border); border-radius: 11px; background: #fff; color: var(--ix-muted); }
    .ix-step.active { border-color: #c7d2fe; background: #f8faff; color: var(--ix-text); }
    .ix-step.done { border-color: #bbf7d0; background: #f0fdf4; color: #166534; }
    .ix-step-num { width: 28px; height: 28px; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; flex: 0 0 28px; background: #f3f4f6; font-weight: 700; }
    .ix-step.active .ix-step-num { background: var(--ix-primary); color: #fff; }
    .ix-step.done .ix-step-num { background: var(--ix-success); color: #fff; }
    .ix-card { border: 1px solid var(--ix-border); border-radius: 14px; background: #fff; box-shadow: 0 8px 24px rgba(15, 23, 42, .06); overflow: hidden; margin-bottom: 16px; }
    .ix-card-head { padding: 16px 18px; border-bottom: 1px solid var(--ix-border); display: flex; align-items: center; justify-content: space-between; gap: 10px; }
    .ix-card-head h3 { margin: 0; font-size: 15px; font-weight: 700; color: var(--ix-text); }
    .ix-card-head h3 i { color: var(--ix-primary); margin-right: 7px; }
    .ix-card-body { padding: 18px; }
    .ix-card-foot { padding: 13px 18px; border-top: 1px solid var(--ix-border); background: #fafafa; display: flex; align-items: center; justify-content: flex-end; gap: 8px; }
    .ix-dropzone { position: relative; min-height: 190px; border: 2px dashed #cbd5e1; border-radius: 12px; background: #f8fafc; display: flex; flex-direction: column; align-items: center; justify-content: center; text-align: center; padding: 22px; cursor: pointer; transition: .2s ease; }
    .ix-dropzone:hover, .ix-dropzone.dragover { border-color: var(--ix-primary); background: #f5f7ff; }
    .ix-dropzone-icon { width: 54px; height: 54px; border-radius: 14px; background: #dcfce7; color: #15803d; display: inline-flex; align-items: center; justify-content: center; font-size: 24px; margin-bottom: 10px; }
    .ix-dropzone strong { color: var(--ix-text); }
    .ix-dropzone small { display: block; color: var(--ix-muted); margin-top: 5px; }
    #fileExcel { position: absolute; inset: 0; width: 100%; height: 100%; opacity: 0; cursor: pointer; }
    .ix-file-name { display: none; margin-top: 12px; padding: 9px 12px; border: 1px solid #bbf7d0; border-radius: 8px; background: #f0fdf4; color: #166534; font-size: 13px; }
    .ix-mode-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 12px; }
    .ix-mode { position: relative; }
    .ix-mode input { position: absolute; opacity: 0; }
    .ix-mode label { display: block; min-height: 82px; border: 1px solid var(--ix-border); border-radius: 10px; background: #fff; padding: 13px 14px 12px 42px; cursor: pointer; margin: 0; transition: .18s ease; }
    .ix-mode label:before { content: ""; position: absolute; left: 15px; top: 17px; width: 17px; height: 17px; border: 2px solid #cbd5e1; border-radius: 50%; background: #fff; }
    .ix-mode input:checked + label { border-color: var(--ix-primary); background: var(--ix-primary-soft); box-shadow: 0 0 0 1px var(--ix-primary); }
    .ix-mode input:checked + label:before { border: 5px solid var(--ix-primary); }
    .ix-mode label strong { display: block; color: var(--ix-text); margin-bottom: 3px; }
    .ix-mode label span { display: block; color: var(--ix-muted); line-height: 1.45; font-size: 12.5px; }
    .ix-action { border: 0; border-radius: 8px; padding: 9px 15px; font-weight: 600; transition: .18s ease; }
    .ix-action-primary { background: var(--ix-primary); color: #fff; box-shadow: 0 5px 14px rgba(79, 70, 229, .18); }
    .ix-action-primary:hover, .ix-action-primary:focus { background: #4338ca; color: #fff; }
    .ix-action-primary:disabled { opacity: .65; }
    .ix-action-success { background: var(--ix-success); color: #fff; }
    .ix-action-success:hover, .ix-action-success:focus { background: #047857; color: #fff; }
    .ix-action-outline { border: 1px solid var(--ix-border); background: #fff; color: #374151; text-decoration: none; }
    .ix-action-outline:hover { border-color: #c7d2fe; color: var(--ix-primary); text-decoration: none; }
    #kotakPratinjau { display: none; }
    .ix-preview-summary { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 10px; margin-bottom: 14px; }
    .ix-stat { padding: 12px; border: 1px solid var(--ix-border); border-radius: 9px; background: #fafafa; }
    .ix-stat span { display: block; color: var(--ix-muted); font-size: 12px; margin-bottom: 3px; }
    .ix-stat strong { color: var(--ix-text); font-size: 15px; }
    .ix-tags { display: flex; flex-wrap: wrap; gap: 5px; margin-top: 8px; }
    .ix-tag { display: inline-flex; align-items: center; max-width: 180px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; padding: 4px 8px; border-radius: 999px; background: #ecfdf5; color: #047857; font-size: 11.5px; }
    .ix-tag-muted { background: #f3f4f6; color: #6b7280; }
    .ix-preview-table { max-height: 390px; overflow: auto; border: 1px solid var(--ix-border); border-radius: 9px; }
    #tabelPratinjau { margin: 0; min-width: 820px; }
    #tabelPratinjau thead th { position: sticky; top: 0; z-index: 2; background: #f8fafc; color: #475569; border-bottom: 1px solid var(--ix-border); white-space: nowrap; }
    #tabelPratinjau td { max-width: 230px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; color: #374151; }
    .ix-result { border: 1px solid var(--ix-border); border-left-width: 4px; border-radius: 10px; padding: 14px 16px; margin-top: 16px; background: #fff; }
    .ix-result-success { border-left-color: var(--ix-success); background: #f0fdf4; color: #166534; }
    .ix-result-danger { border-left-color: #dc2626; background: #fef2f2; color: #991b1b; }
    .ix-result-warning { border-left-color: #d97706; background: #fffbeb; color: #92400e; margin-top: 10px; }
    @media (max-width: 767px) { .ix-header { align-items: flex-start; } .ix-steps { grid-template-columns: 1fr; } .ix-mode-grid, .ix-preview-summary { grid-template-columns: 1fr; } .ix-data-count { display: none; } }
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<div class="ix-page">
    <div class="ix-header">
        <div class="ix-title-wrap">
            <span class="ix-title-icon"><i class="fa fa-file-excel-o"></i></span>
            <div>
                <h2>Import Data Perusahaan</h2>
                <p>Unggah, pratinjau, lalu impor data Excel ke sistem.</p>
            </div>
        </div>
        <div class="ix-data-count"><strong><?= number_format((int) $jumlahSekarang) ?></strong> data tersimpan</div>
    </div>

    <div class="ix-steps">
        <div class="ix-step active" id="stepUpload"><span class="ix-step-num">1</span><strong>Pilih & unggah berkas</strong></div>
        <div class="ix-step" id="stepPreview"><span class="ix-step-num">2</span><strong>Pratinjau data</strong></div>
        <div class="ix-step" id="stepResult"><span class="ix-step-num">3</span><strong>Selesai import</strong></div>
    </div>

    <div class="ix-card">
        <div class="ix-card-head">
            <h3><i class="fa fa-cloud-upload"></i> Unggah berkas Excel</h3>
            <a href="<?= site_url('import-excel/template') ?>" class="ix-action ix-action-outline">
                <i class="fa fa-download"></i> Unduh template
            </a>
        </div>
        <div class="ix-card-body">
            <form id="formUnggah">
                <div class="ix-dropzone" id="dropzone">
                    <span class="ix-dropzone-icon"><i class="fa fa-file-excel-o"></i></span>
                    <strong id="dropzoneTitle">Tarik berkas Excel ke sini</strong>
                    <small id="dropzoneDesc">atau klik untuk memilih .xlsx / .xls (maks. 20 MB)</small>
                    <input type="file" name="file_excel" id="fileExcel" accept=".xlsx,.xls">
                </div>
                <div class="ix-file-name" id="namaFile"><i class="fa fa-check-circle"></i> <span></span></div>

                <div style="margin-top:18px">
                    <label style="font-weight:700;margin-bottom:8px">Jika data sudah ada</label>
                    <div class="ix-mode-grid">
                        <div class="ix-mode">
                            <input type="radio" name="mode" id="modeAppend" value="append" checked>
                            <label for="modeAppend">
                                <strong><i class="fa fa-plus-circle"></i> Lewati data lama</strong>
                                <span>Tambahkan hanya data baru dan lewati data yang sama.</span>
                            </label>
                        </div>
                        <div class="ix-mode">
                            <input type="radio" name="mode" id="modeUpdate" value="update">
                            <label for="modeUpdate">
                                <strong><i class="fa fa-refresh"></i> Perbarui data lama</strong>
                                <span>Kolom yang terisi pada berkas akan menimpa data lama.</span>
                            </label>
                        </div>
                    </div>
                    <p class="text-muted" style="font-size:12px;margin:8px 0 0">Pencocokan dilakukan berdasarkan nomor atau nama perusahaan.</p>
                </div>
            </form>
        </div>
        <div class="ix-card-foot">
            <button type="button" class="ix-action ix-action-primary" id="tombolPratinjau">
                <i class="fa fa-eye"></i> Pratinjau data
            </button>
        </div>
    </div>

    <div class="ix-card" id="kotakPratinjau">
        <div class="ix-card-head">
            <h3><i class="fa fa-table"></i> Pratinjau data</h3>
            <span class="label label-primary">Maks. 10 baris ditampilkan</span>
        </div>
        <div class="ix-card-body">
            <div id="infoPratinjau"></div>
            <div class="ix-preview-table">
                <table class="table table-bordered table-striped table-hover" id="tabelPratinjau"></table>
            </div>
        </div>
        <div class="ix-card-foot">
            <button type="button" class="ix-action ix-action-success" id="tombolImport">
                <i class="fa fa-upload"></i> Mulai import
            </button>
        </div>
    </div>
</div>

<div id="hasilImport" class="ix-page"></div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
(function () {
    'use strict';

    var URL_PRATINJAU = '<?= site_url('import-excel/preview') ?>';
    var URL_PROSES    = '<?= site_url('import-excel/process') ?>';
    var csrfNama      = '<?= csrf_token() ?>';
    var csrfHash      = '<?= csrf_hash() ?>';
    var tempFile      = '';

    var inputFile = document.getElementById('fileExcel');
    var dropzone  = document.getElementById('dropzone');
    var namaFile  = document.getElementById('namaFile');

    function aturNamaFile(file) {
        if (!file) {
            namaFile.style.display = 'none';
            return;
        }
        namaFile.querySelector('span').textContent = file.name + ' (' + formatUkuran(file.size) + ')';
        namaFile.style.display = 'inline-block';
    }

    function formatUkuran(bytes) {
        if (!bytes) { return '0 B'; }
        var satuan = ['B', 'KB', 'MB', 'GB'];
        var indeks = Math.floor(Math.log(bytes) / Math.log(1024));
        return (bytes / Math.pow(1024, indeks)).toFixed(indeks === 0 ? 0 : 1) + ' ' + satuan[indeks];
    }

    inputFile.addEventListener('change', function () {
        aturNamaFile(this.files && this.files[0] ? this.files[0] : null);
    });

    ['dragenter', 'dragover'].forEach(function (eventName) {
        dropzone.addEventListener(eventName, function (event) {
            event.preventDefault();
            dropzone.classList.add('dragover');
        });
    });

    ['dragleave', 'drop'].forEach(function (eventName) {
        dropzone.addEventListener(eventName, function () {
            dropzone.classList.remove('dragover');
        });
    });

    dropzone.addEventListener('drop', function (event) {
        event.preventDefault();
        var files = event.dataTransfer.files;
        if (!files || !files.length) { return; }
        if (typeof DataTransfer !== 'undefined') {
            var transfer = new DataTransfer();
            transfer.items.add(files[0]);
            inputFile.files = transfer.files;
            aturNamaFile(files[0]);
        }
    });

    function aturLangkah(langkah) {
        var stepUpload = document.getElementById('stepUpload');
        var stepPreview = document.getElementById('stepPreview');
        var stepResult = document.getElementById('stepResult');
        [stepUpload, stepPreview, stepResult].forEach(function (step) {
            step.classList.remove('active', 'done');
        });

        if (langkah === 1) {
            stepUpload.classList.add('active');
        } else if (langkah === 2) {
            stepUpload.classList.add('done');
            stepPreview.classList.add('active');
        } else {
            stepUpload.classList.add('done');
            stepPreview.classList.add('done');
            stepResult.classList.add('active');
        }
    }

    function esc(t) {
        if (t === null || t === undefined) { return ''; }
        return String(t).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;').replace(/'/g, '&#039;');
    }

    function kirim(url, formData) {
        formData.append(csrfNama, csrfHash);
        return fetch(url, {
            method: 'POST',
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': csrfHash },
            body: formData
        }).then(function (r) { return r.json(); }).then(function (d) {
            if (d.csrf_hash)  { csrfHash = d.csrf_hash; }
            if (d.csrf_token) { csrfNama = d.csrf_token; }
            return d;
        });
    }

    function pilihMode() {
        return document.querySelector('input[name="mode"]:checked').value;
    }

    // ---------- Langkah 1: pratinjau ----------
    document.getElementById('tombolPratinjau').addEventListener('click', function () {
        var input = document.getElementById('fileExcel');
        if (!input.files || input.files.length === 0) {
            alert('Pilih berkas Excel terlebih dahulu.');
            return;
        }

        var tombol = this;
        tombol.disabled = true;
        tombol.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Membaca berkas...';
        document.getElementById('hasilImport').innerHTML = '';

        var fd = new FormData();
        fd.append('file_excel', input.files[0]);

        kirim(URL_PRATINJAU, fd).then(function (res) {
            if (!res.success) {
                tempFile = '';
                document.getElementById('kotakPratinjau').style.display = 'none';
                alert(res.message || 'Gagal membaca berkas.');
                return;
            }
            tempFile = res.temp_file;
            tampilPratinjau(res);
            aturLangkah(2);
            document.getElementById('kotakPratinjau').scrollIntoView({ behavior: 'smooth', block: 'start' });
        }).catch(function () {
            alert('Terjadi kesalahan. Muat ulang halaman lalu coba lagi.');
        }).then(function () {
            tombol.disabled = false;
            tombol.innerHTML = '<i class="fa fa-eye"></i> Pratinjau';
        });
    });

    function tampilPratinjau(res) {
        var dikenali = Array.isArray(res.kolom_dikenali) ? res.kolom_dikenali : [];
        var diabaikan = Array.isArray(res.kolom_diabaikan) ? res.kolom_diabaikan : [];
        var info = '<div class="ix-preview-summary">'
            + '<div class="ix-stat"><span>Sheet aktif</span><strong>' + esc(res.sheet_name) + '</strong></div>'
            + '<div class="ix-stat"><span>Jumlah baris data</span><strong>' + esc(res.total_rows) + '</strong></div>'
            + '<div class="ix-stat"><span>Kolom dikenali</span><strong>' + dikenali.length + ' kolom</strong></div>'
            + '</div>';

        info += '<div style="margin-bottom:10px"><span class="text-muted" style="font-size:12px">Kolom dikenali</span><div class="ix-tags">'
            + dikenali.map(function (k) { return '<span class="ix-tag">' + esc(k) + '</span>'; }).join('')
            + '</div></div>';

        if (res.kolom_diabaikan && res.kolom_diabaikan.length > 0) {
            info += '<div><span class="text-muted" style="font-size:12px">Kolom diabaikan</span><div class="ix-tags">'
                + diabaikan.map(function (k) { return '<span class="ix-tag ix-tag-muted">' + esc(k) + '</span>'; }).join('')
                + '</div></div>';
        }
        document.getElementById('infoPratinjau').innerHTML = info;

        var html = '<thead><tr>' + res.header.map(function (h) { return '<th>' + esc(h) + '</th>'; }).join('') + '</tr></thead><tbody>';
        res.preview_rows.forEach(function (row) {
            html += '<tr>' + row.map(function (c) { return '<td>' + esc(c) + '</td>'; }).join('') + '</tr>';
        });
        html += '</tbody>';
        document.getElementById('tabelPratinjau').innerHTML = html;

        document.getElementById('kotakPratinjau').style.display = 'block';
    }

    // ---------- Langkah 2: import ----------
    document.getElementById('tombolImport').addEventListener('click', function () {
        if (!tempFile) {
            alert('Lakukan pratinjau terlebih dahulu.');
            return;
        }
        if (!confirm('Mulai import sekarang?')) { return; }

        var tombol = this;
        tombol.disabled = true;
        tombol.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Mengimpor... harap tunggu';

        var fd = new FormData();
        fd.append('temp_file', tempFile);
        fd.append('mode', pilihMode());

        kirim(URL_PROSES, fd).then(function (res) {
            tampilHasil(res);
            if (res.success) {
                tempFile = '';
                document.getElementById('kotakPratinjau').style.display = 'none';
                document.getElementById('fileExcel').value = '';
                namaFile.style.display = 'none';
                aturLangkah(3);
            }
        }).catch(function () {
            alert('Terjadi kesalahan. Muat ulang halaman lalu coba lagi.');
        }).then(function () {
            tombol.disabled = false;
            tombol.innerHTML = '<i class="fa fa-upload"></i> Mulai import';
        });
    });

    function tampilHasil(res) {
        var el = document.getElementById('hasilImport');

        if (!res.success) {
            el.innerHTML = '<div class="ix-result ix-result-danger"><i class="fa fa-times-circle"></i> <strong>' + esc(res.message || 'Import gagal.') + '</strong></div>';
            aturLangkah(2);
            return;
        }

        var html = '<div class="ix-result ix-result-success"><i class="fa fa-check-circle"></i> <strong>' + esc(res.message) + '</strong></div>';
        if (res.galat && res.galat.length > 0) {
            html += '<div class="ix-result ix-result-warning"><strong><i class="fa fa-warning"></i> Baris yang dilewati (maks. 30 ditampilkan)</strong><ul style="margin:8px 0 0 15px">'
                + res.galat.map(function (g) { return '<li>' + esc(g) + '</li>'; }).join('') + '</ul></div>';
        }
        el.innerHTML = html;
    }
}());
</script>
<?= $this->endSection() ?>