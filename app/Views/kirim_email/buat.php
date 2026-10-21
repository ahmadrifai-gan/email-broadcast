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
<style>
    /* Style khusus modal pilih kontak */
    #modalKontak .modal-dialog { width: 900px; max-width: 95%; }
    #modalKontak .table > tbody > tr.kontak-modal-row { cursor: pointer; }
    #modalKontak .table > tbody > tr.kontak-modal-row:hover { background-color: #f5f5f5; }
    #modalKontak .table > tbody > tr.selected-row,
    #modalKontak .table > tbody > tr.selected-row td { background-color: #d9edf7 !important; }

    /* Daftar penerima terpilih */
    .penerima-terpilih-list {
        max-height: 260px;
        overflow-y: auto;
        border: 1px solid #ddd;
        border-radius: 3px;
        padding: 8px;
        background: #fafafa;
    }
    .penerima-chip {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 6px 10px;
        margin-bottom: 5px;
        background: #fff;
        border: 1px solid #e0e0e0;
        border-radius: 3px;
        font-size: 13px;
    }
    .penerima-chip .info { flex: 1; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
    .penerima-chip .info strong { color: #3c8dbc; }
    .penerima-chip .info small { color: #777; }
    .penerima-chip .hapus { color: #dd4b39; cursor: pointer; padding: 2px 6px; }
    .penerima-chip .hapus:hover { background: #f2dede; border-radius: 3px; }
    .penerima-empty { text-align: center; color: #999; padding: 30px 0; }
    .penerima-empty .fa { font-size: 40px; display: block; margin-bottom: 10px; }
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<?php if (session()->getFlashdata('error')): ?>
    <div class="alert alert-danger alert-dismissible">
        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
        <i class="icon fa fa-ban"></i> <?= esc(session()->getFlashdata('error')) ?>
    </div>
<?php endif; ?>
<?php if (session()->getFlashdata('errors')): ?>
    <div class="alert alert-danger alert-dismissible">
        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
        <i class="icon fa fa-ban"></i>
        <?php foreach ((array) session()->getFlashdata('errors') as $e): ?>
            <div><?= esc($e) ?></div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<form action="<?= base_url('kirim-email/simpan') ?>" method="post" id="formKampanye">
    <?= csrf_field() ?>

    <div class="row">
        <!-- ============ KARTU 1: PENGATURAN ============ -->
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

        <!-- ============ KARTU 2: PENERIMA ============ -->
        <div class="col-lg-6">
            <div class="box box-success">
                <div class="box-header with-border">
                    <h3 class="box-title"><i class="fa fa-users"></i> 3. Penerima (dari Kontak)</h3>
                    <div class="box-tools pull-right">
                        <span class="label label-success"><span id="jumlahDipilih">0</span> dipilih</span>
                    </div>
                </div>
                <div class="box-body">
                    <button type="button" class="btn btn-success btn-block" id="btnBukaModalKontak" style="margin-bottom:10px;">
                        <i class="fa fa-search"></i> Cari &amp; Pilih Kontak Penerima
                    </button>

                    <div id="penerimaTerpilihList" class="penerima-terpilih-list">
                        <div class="penerima-empty">
                            <i class="fa fa-inbox"></i>
                            Belum ada penerima dipilih.<br>
                            <small>Klik tombol di atas untuk memilih kontak.</small>
                        </div>
                    </div>

                    <div id="hiddenPenerimaWrap"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- ============ KARTU 3: PREVIEW ============ -->
    <div class="box box-info">
        <div class="box-header with-border">
            <h3 class="box-title">
                <i class="fa fa-eye"></i> Contoh Isi Email
                <small class="text-muted" id="infoContoh"></small>
            </h3>
            <div class="box-tools pull-right">
                <div class="btn-group btn-group-sm" role="group">
                    <button type="button" class="btn btn-default active" id="tabTeks"><i class="fa fa-text-width"></i> Teks</button>
                    <button type="button" class="btn btn-default" id="tabHtml"><i class="fa fa-code"></i> Tampilan HTML</button>
                </div>
            </div>
        </div>
        <div class="box-body">
            <textarea id="previewTeks" class="form-control" rows="14" readonly
                      placeholder="Pilih kategori & template untuk melihat contoh isi email..."
                      style="background:#f8f9fa; font-family: Consolas, monospace; font-size:13px;"></textarea>
            <!-- Bootstrap 3: pakai class "hidden", bukan "d-none" -->
            <iframe id="previewHtml" sandbox class="hidden"
                    style="width:100%;height:380px;border:1px solid #ddd;border-radius:3px;background:#fff;"></iframe>
        </div>
        <div class="box-footer text-muted small">
            <i class="fa fa-info-circle"></i>
            Placeholder <code>{nama}</code>, <code>{perusahaan}</code>, <code>{email}</code>, <code>{domisili}</code>
            diganti dengan data penerima pertama yang dipilih. Isi ini hanya untuk dibaca (read-only).
        </div>
    </div>

    <!-- ============ KARTU 4: WAKTU PENGIRIMAN ============ -->
    <div class="box box-warning">
        <div class="box-header with-border">
            <h3 class="box-title"><i class="fa fa-calendar-check-o"></i> 4. Waktu Pengiriman</h3>
        </div>
        <div class="box-body">
            <div class="row">
                <div class="col-md-6">
                    <div class="box box-default" style="margin-bottom:0;">
                        <div class="box-body">
                            <div class="radio">
                                <label>
                                    <input type="radio" name="mode_kirim" id="modeSekarang" value="sekarang"
                                           <?= old('mode_kirim') === 'jadwal' ? '' : 'checked' ?>>
                                    <i class="fa fa-bolt text-yellow"></i> <strong>Kirim langsung sekarang</strong>
                                </label>
                                <p class="help-block">Pengiriman dimulai segera setelah kampanye disimpan.</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="box box-default" style="margin-bottom:0;">
                        <div class="box-body">
                            <div class="radio">
                                <label>
                                    <input type="radio" name="mode_kirim" id="modeJadwal" value="jadwal"
                                           <?= old('mode_kirim') === 'jadwal' ? 'checked' : '' ?>>
                                    <i class="fa fa-clock-o text-aqua"></i> <strong>Jadwalkan (dikirim otomatis)</strong>
                                </label>
                                <p class="help-block">Email dikirim otomatis saat waktu yang dipilih tiba.</p>
                            </div>
                            <div id="boxJadwal" class="hidden">
                                <input type="datetime-local" name="jadwal_pengiriman" id="jadwal_pengiriman" class="form-control"
                                       value="<?= esc(old('jadwal_pengiriman')) ?>">
                                <p class="help-block">Berjalan otomatis lewat cron / saat halaman Kirim Email terbuka.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="box-footer">
            <a href="<?= base_url('kirim-email') ?>" class="btn btn-default"><i class="fa fa-arrow-left"></i> Batal</a>
            <button type="submit" class="btn btn-primary pull-right" id="btnSimpan">
                <i class="fa fa-send"></i> <span id="labelSimpan">Simpan &amp; Kirim Sekarang</span>
            </button>
        </div>
    </div>

</form>

<!-- ============ MODAL PILIH KONTAK ============ -->
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
                               placeholder="Cari nama / email / domisili...">
                        <span class="input-group-btn">
                            <button type="button" class="btn btn-default" id="btnResetSearch" title="Bersihkan pencarian">
                                <i class="fa fa-times"></i>
                            </button>
                        </span>
                    </div>
                </div>

                <div class="table-responsive" style="max-height: 400px; overflow-y: auto; border:1px solid #eee;">
                    <table id="tableKontakModal" class="table table-bordered table-striped table-hover" style="width:100%; margin-bottom:0;">
                        <thead>
                            <tr>
                                <th width="40" class="text-center">
                                    <input type="checkbox" id="checkAllModal" title="Pilih semua yang tampil">
                                </th>
                                <th>Nama Perusahaan</th>
                                <th>Email</th>
                                <th>Domisili</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($kontak)): foreach ($kontak as $k): ?>
                                <tr class="kontak-modal-row"
                                    data-id="<?= $k['id'] ?>"
                                    data-nama="<?= esc($k['nama_perusahaan'] ?? '') ?>"
                                    data-email="<?= esc($k['email']) ?>"
                                    data-domisili="<?= esc($k['domisili'] ?? '') ?>">
                                    <td class="text-center">
                                        <input type="checkbox" class="check-kontak" value="<?= $k['id'] ?>">
                                    </td>
                                    <td><?= esc($k['nama_perusahaan'] ?? '-') ?></td>
                                    <td><?= esc($k['email']) ?></td>
                                    <td><?= esc($k['domisili'] ?? '-') ?></td>
                                </tr>
                            <?php endforeach; else: ?>
                                <tr>
                                    <td colspan="4" class="text-center text-muted">
                                        <i class="fa fa-inbox fa-3x"></i><br>
                                        Belum ada kontak dengan email.
                                    </td>
                                </tr>
                            <?php endif; ?>
                            <tr id="rowNoResult" style="display:none;">
                                <td colspan="4" class="text-center text-muted">
                                    <i class="fa fa-search fa-3x"></i><br>
                                    Tidak ada kontak yang cocok dengan pencarian.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="text-muted small" style="margin-top:8px;">
                    <i class="fa fa-info-circle"></i>
                    Klik baris atau checkbox untuk memilih. Pilihan diterapkan saat Anda klik <strong>Tambahkan</strong>.
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
<script>
/* Dibungkus IIFE + dijalankan setelah DOM siap, supaya:
   - "$" lokal TIDAK menimpa jQuery milik AdminLTE/Bootstrap
   - tidak peduli @section scripts dirender sebelum/sesudah jQuery & bootstrap.js */
(function () {
'use strict';

function mulai() {

const $ = id => document.getElementById(id);

/* ==========================================================
   DATA DARI SERVER
   ========================================================== */
const TEMPLATES    = <?= json_encode($templateJs, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE) ?>;
const OLD_TEMPLATE = <?= (int) old('template_email_id') ?>;
const OLD_PENERIMA = <?= json_encode(array_map('strval', (array) old('penerima')), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>;

/* ==========================================================
   STATE
   ========================================================== */
const penerimaMap = new Map();     // id -> {id, nama, email, domisili}
let   workingSet  = new Set();     // sementara saat modal dibuka

/* ==========================================================
   UTIL
   ========================================================== */
function ganti(teks, s) {
    return (teks || '')
        .split('{nama}').join(s.nama)
        .split('{perusahaan}').join(s.nama)
        .split('{email}').join(s.email)
        .split('{domisili}').join(s.domisili);
}

function htmlKeTeks(html) {
    const dulu = (html || '')
        .replace(/<br\s*\/?>/gi, '\n')
        .replace(/<\/(p|div|h[1-6]|tr|li|table)>/gi, '\n');
    const doc = new DOMParser().parseFromString(dulu, 'text/html');
    doc.querySelectorAll('style,script,head,title').forEach(n => n.remove());
    return (doc.body.textContent || '').replace(/[ \t]+\n/g, '\n').replace(/\n{3,}/g, '\n\n').trim();
}

function escapeHtml(s) {
    return String(s == null ? '' : s)
        .replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;').replace(/'/g, '&#39;');
}

/* ==========================================================
   PREVIEW
   ========================================================== */
function contohPenerima() {
    if (penerimaMap.size === 0) {
        return { nama: 'Nama Penerima', email: 'email@contoh.com', domisili: 'Kota', nyata: false };
    }
    const first = penerimaMap.values().next().value;
    return { nama: first.nama || '-', email: first.email, domisili: first.domisili || '', nyata: true };
}

function renderPreview() {
    const t = TEMPLATES.find(x => x.id === Number($('template_email_id').value));
    if (!t) {
        $('previewTeks').value = '';
        $('previewHtml').srcdoc = '';
        $('infoContoh').textContent = '';
        return;
    }
    const s    = contohPenerima();
    const teks = (t.teks && t.teks.trim()) ? t.teks : htmlKeTeks(t.html);
    const subj = $('subjek_email').value || t.subjek;

    $('previewTeks').value = 'Kepada : ' + s.nama + ' <' + s.email + '>\n'
        + 'Subjek : ' + ganti(subj, s) + '\n'
        + '------------------------------------------------------------\n\n'
        + ganti(teks, s);
    $('previewHtml').srcdoc = ganti(t.html, s);
    $('infoContoh').textContent = s.nyata ? '(contoh untuk: ' + s.nama + ')' : '(contoh dengan data dummy)';
}

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
            o.textContent = t.nama;
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
});

$('subjek_email').addEventListener('input', renderPreview);

/* ==========================================================
   TAB PREVIEW  (Bootstrap 3 -> class "hidden")
   ========================================================== */
$('tabTeks').addEventListener('click', () => {
    $('previewTeks').classList.remove('hidden'); $('previewHtml').classList.add('hidden');
    $('tabTeks').classList.add('active'); $('tabHtml').classList.remove('active');
});
$('tabHtml').addEventListener('click', () => {
    $('previewHtml').classList.remove('hidden'); $('previewTeks').classList.add('hidden');
    $('tabHtml').classList.add('active'); $('tabTeks').classList.remove('active');
});

/* ==========================================================
   RENDER DAFTAR PENERIMA TERPILIH (chip)
   ========================================================== */
function renderPenerimaTerpilih() {
    const wrap = $('penerimaTerpilihList');
    const hiddenWrap = $('hiddenPenerimaWrap');

    $('jumlahDipilih').textContent = penerimaMap.size;

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
                '<i class="fa fa-inbox"></i>' +
                'Belum ada penerima dipilih.<br>' +
                '<small>Klik tombol di atas untuk memilih kontak.</small>' +
            '</div>';
        renderPreview();
        return;
    }

    wrap.innerHTML = '';
    penerimaMap.forEach((p) => {
        const chip = document.createElement('div');
        chip.className = 'penerima-chip';
        chip.innerHTML =
            '<div class="info">' +
                '<strong>' + escapeHtml(p.nama || '-') + '</strong> ' +
                '<small>&lt;' + escapeHtml(p.email) + '&gt;</small>' +
                (p.domisili ? ' <small class="text-muted">• ' + escapeHtml(p.domisili) + '</small>' : '') +
            '</div>' +
            '<span class="hapus" title="Hapus" data-id="' + escapeHtml(p.id) + '">' +
                '<i class="fa fa-times"></i>' +
            '</span>';
        wrap.appendChild(chip);
    });

    wrap.querySelectorAll('.hapus').forEach(el => {
        el.addEventListener('click', function () {
            penerimaMap.delete(this.dataset.id);
            renderPenerimaTerpilih();
        });
    });

    renderPreview();
}

/* ==========================================================
   MODAL PILIH KONTAK  (manual filter, tanpa DataTables)
   ========================================================== */
function getModalRows() {
    return Array.from(document.querySelectorAll('#tableKontakModal tbody tr.kontak-modal-row'));
}

function updateCountModal() {
    const n = workingSet.size;
    $('countModalSelected').textContent = n;
    $('labelTambahkan').textContent = n > 0 ? '(' + n + ')' : '';
    $('btnTambahkanPenerima').disabled = n === 0;
}

function syncCheckAllState() {
    const rows = getModalRows().filter(tr => tr.style.display !== 'none');
    const checked = rows.filter(tr => tr.querySelector('.check-kontak').checked);
    const ca = $('checkAllModal');
    ca.checked       = rows.length > 0 && checked.length === rows.length;
    ca.indeterminate = checked.length > 0 && checked.length < rows.length;
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
    q = (q || '').toLowerCase().trim();
    const rows = getModalRows();
    let visible = 0;

    rows.forEach(tr => {
        const hay = (
            (tr.dataset.nama || '') + ' ' +
            (tr.dataset.email || '') + ' ' +
            (tr.dataset.domisili || '')
        ).toLowerCase();
        const match = !q || hay.indexOf(q) !== -1;
        tr.style.display = match ? '' : 'none';
        if (match) visible++;
    });

    $('rowNoResult').style.display = (rows.length > 0 && visible === 0) ? '' : 'none';
    syncCheckAllState();
}

function setRowChecked(tr, checked) {
    const cb = tr.querySelector('.check-kontak');
    cb.checked = checked;
    const id = String(cb.value);
    if (checked) workingSet.add(id); else workingSet.delete(id);
    tr.classList.toggle('selected-row', checked);
}

/* Listener tabel modal (sekali saja) */
(function initModalTable() {
    const tbody = document.querySelector('#tableKontakModal tbody');

    tbody.addEventListener('click', function (e) {
        const tr = e.target.closest('tr.kontak-modal-row');
        if (!tr) return;
        if (e.target.tagName === 'INPUT') return;
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

    $('searchKontakModal').addEventListener('input', function () { filterRows(this.value); });
    $('btnResetSearch').addEventListener('click', function () {
        $('searchKontakModal').value = '';
        filterRows('');
        $('searchKontakModal').focus();
    });

    $('checkAllModal').addEventListener('change', function () {
        const checked = this.checked;
        getModalRows().forEach(tr => {
            if (tr.style.display === 'none') return;
            setRowChecked(tr, checked);
        });
        updateCountModal();
    });
})();

/* ---- Kontrol modal: Bootstrap 3 (jQuery) -> Bootstrap 5 -> fallback manual ---- */
const modalEl = $('modalKontak');
let backdropEl = null;

const adaBs3 = () => !!(window.jQuery && window.jQuery.fn && window.jQuery.fn.modal);
const adaBs5 = () => !!(window.bootstrap && window.bootstrap.Modal);

// Pindahkan modal ke <body>: di AdminLTE 2, modal di dalam .content-wrapper
// sering tertutup backdrop (stacking context) sehingga tampak "tidak muncul".
document.body.appendChild(modalEl);

function modalTampil() {
    if (adaBs3()) {
        window.jQuery(modalEl).modal('show');
    } else if (adaBs5()) {
        window.bootstrap.Modal.getOrCreateInstance(modalEl).show();
    } else {
        // Fallback manual (jika bootstrap.js tidak termuat sama sekali)
        modalEl.style.display = 'block';
        modalEl.classList.add('in', 'show');
        modalEl.removeAttribute('aria-hidden');
        document.body.classList.add('modal-open');
        if (!backdropEl) {
            backdropEl = document.createElement('div');
            backdropEl.className = 'modal-backdrop fade in show';
            document.body.appendChild(backdropEl);
        }
    }
    setTimeout(() => $('searchKontakModal').focus(), 300);
}

function modalTutup() {
    if (adaBs3()) {
        window.jQuery(modalEl).modal('hide');
    } else if (adaBs5()) {
        const inst = window.bootstrap.Modal.getInstance(modalEl);
        if (inst) inst.hide();
    } else {
        modalEl.style.display = 'none';
        modalEl.classList.remove('in', 'show');
        modalEl.setAttribute('aria-hidden', 'true');
        document.body.classList.remove('modal-open');
        if (backdropEl) { backdropEl.remove(); backdropEl = null; }
    }
}

// Tombol X / Batal & klik di luar dialog: hanya perlu ditangani manual pada mode fallback
if (!adaBs3() && !adaBs5()) {
    modalEl.querySelectorAll('[data-dismiss="modal"]').forEach(btn => btn.addEventListener('click', modalTutup));
    modalEl.addEventListener('click', e => { if (e.target === modalEl) modalTutup(); });
    document.addEventListener('keydown', e => { if (e.key === 'Escape' && modalEl.style.display === 'block') modalTutup(); });
}

function bukaModalKontak() {
    workingSet = new Set(penerimaMap.keys());
    $('searchKontakModal').value = '';
    filterRows('');
    syncCheckboxFromWorkingSet();
    modalTampil();
}

$('btnBukaModalKontak').addEventListener('click', bukaModalKontak);

$('btnTambahkanPenerima').addEventListener('click', function () {
    penerimaMap.clear();
    workingSet.forEach(id => {
        const tr = document.querySelector('#tableKontakModal tbody tr[data-id="' + id + '"]');
        if (!tr) return;
        penerimaMap.set(id, {
            id:       id,
            nama:     tr.dataset.nama     || '',
            email:    tr.dataset.email    || '',
            domisili: tr.dataset.domisili || ''
        });
    });
    modalTutup();
    renderPenerimaTerpilih();
});

/* ==========================================================
   MODE KIRIM
   ========================================================== */
function updateMode() {
    const jadwal = $('modeJadwal').checked;
    $('boxJadwal').classList.toggle('hidden', !jadwal);
    $('jadwal_pengiriman').required = jadwal;
    $('labelSimpan').textContent = jadwal ? 'Simpan & Jadwalkan' : 'Simpan & Kirim Sekarang';
}
$('modeSekarang').addEventListener('change', updateMode);
$('modeJadwal').addEventListener('change', updateMode);

$('jadwal_pengiriman').min = new Date(Date.now() - new Date().getTimezoneOffset() * 60000).toISOString().slice(0, 16);

/* ==========================================================
   VALIDASI SUBMIT
   ========================================================== */
$('formKampanye').addEventListener('submit', function (e) {
    if (!$('template_email_id').value) {
        e.preventDefault();
        alert('Pilih kategori dan template terlebih dahulu.');
        return;
    }
    if (penerimaMap.size === 0) {
        e.preventDefault();
        alert('Pilih minimal 1 penerima.');
    }
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
            const tr = document.querySelector('#tableKontakModal tbody tr[data-id="' + idStr + '"]');
            if (tr) {
                penerimaMap.set(idStr, {
                    id:       idStr,
                    nama:     tr.dataset.nama     || '',
                    email:    tr.dataset.email    || '',
                    domisili: tr.dataset.domisili || ''
                });
            }
        });
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