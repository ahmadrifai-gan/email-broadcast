<?= $this->extend('layouts/main') ?>

<?= $this->section('title') ?>Cari Email<?= $this->endSection() ?>
<?= $this->section('page_title') ?>Cari Email Kontak<?= $this->endSection() ?>
<?= $this->section('page_subtitle') ?>Hubungkan AI, pilih kontak, atur pengambilan data, lalu jalankan<?= $this->endSection() ?>

<?= $this->section('breadcrumb') ?>
<li><a href="<?= base_url() ?>"><i class="fa fa-dashboard"></i> Home</a></li>
<li><a href="<?= base_url('extractor') ?>">Data Email</a></li>
<li class="active">Cari Email</li>
<?= $this->endSection() ?>

<?= $this->section('styles') ?>
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
<style>
    .box.locked { opacity:.55; pointer-events:none; }
    .url-cell { max-width:220px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; display:inline-block; vertical-align:middle; }
    .note-cell { max-width:340px; }
    .tbl td { vertical-align:middle !important; }
    .spec-label { display:block; margin-bottom:5px; }
    .spec-label .mode-toggle { margin-top:-2px; }
    .input-group .form-control.text-center { min-width:60px; }
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<p>
    <a href="<?= base_url('extractor') ?>" class="btn btn-default btn-sm"><i class="fa fa-arrow-left"></i> Kembali ke Data Email</a>
    <a href="#" id="lnkLast" class="btn btn-default btn-sm" style="display:none;"><i class="fa fa-list-alt"></i> Detail proses terakhir</a>
</p>

<div class="callout callout-info" id="activeJob" style="display:none;">
    <h4><i class="fa fa-spinner fa-spin"></i> Ada proses pencarian yang sedang berjalan</h4>
    <p id="activeMsg">-</p>
    <p><a href="#" id="lnkActive" class="btn btn-primary btn-sm"><i class="fa fa-external-link"></i> Buka Halaman Detail</a></p>
</div>

<div class="row">
    <!-- ===== 1. Koneksi AI ===== -->
    <div class="col-md-6">
        <div class="box box-primary" id="cardApi">
            <div class="box-header with-border">
                <h3 class="box-title"><i class="fa fa-plug"></i> 1. Koneksi AI</h3>
                <div class="box-tools pull-right"><span class="label label-warning" id="apiBadge">Belum terhubung</span></div>
            </div>
            <div class="box-body">
                <div class="form-group">
                    <label>Gemini API Key</label>
                    <div class="input-group">
                        <input id="key" type="password" class="form-control" placeholder="AIza... (kosong = pakai .env)" autocomplete="off">
                        <span class="input-group-btn"><button class="btn btn-default" type="button" id="btnEye"><i class="fa fa-eye"></i></button></span>
                    </div>
                </div>
                <div class="form-group">
                    <label>Model Gemini</label>
                    <input id="model" list="modelList" class="form-control" autocomplete="off" placeholder="Pilih model...">
                    <datalist id="modelList">
                        <option value="gemini-3.5-flash-lite">
                        <option value="gemini-3.8-flash">
                        <option value="gemini-3.1-flash-lite">
                    </datalist>
                </div>
                <button class="btn btn-default" type="button" id="btnModels"><i class="fa fa-refresh"></i> Muat Model</button>
                <button class="btn btn-primary pull-right" type="button" id="btnTest"><i class="fa fa-plug"></i> Hubungkan &amp; Lanjut</button>
                <div class="callout callout-warning" id="apiHint" style="margin:15px 0 0;">
                    <p>Isi API key lalu klik <b>Hubungkan &amp; Lanjut</b> untuk membuka langkah berikutnya.</p>
                </div>
            </div>
        </div>
    </div>

    <!-- ===== 2. Pilih Kontak ===== -->
    <div class="col-md-6">
        <div class="box box-info locked" id="cardPick">
            <div class="box-header with-border">
                <h3 class="box-title"><i class="fa fa-users"></i> 2. Pilih Kontak</h3>
                <div class="box-tools pull-right"><span class="label label-default" id="pickBadge">Terkunci</span></div>
            </div>
            <div class="box-body">
                <div class="callout callout-info" style="margin-top:0;">
                    <p>Klik tombol di bawah. Akan muncul jendela berisi <b>hanya kontak yang belum punya email</b>. Centang kontak yang ingin dicarikan emailnya.</p>
                </div>
                <table class="table table-condensed">
                    <tr><td>Kontak tanpa email</td><td class="text-right"><b id="pickAvail">0</b></td></tr>
                    <tr><td>Punya website</td><td class="text-right"><b id="pickWeb">0</b></td></tr>
                    <tr><td>Dipilih</td><td class="text-right"><b class="text-green" id="pickSel">0</b></td></tr>
                </table>
                <button class="btn btn-info btn-block" type="button" id="btnPick"><i class="fa fa-list-ul"></i> Pilih Data Kontak</button>
            </div>
        </div>
    </div>
</div>

<!-- ===== 3. Pengaturan ===== -->
<div class="box box-success locked" id="cardSet">
    <div class="box-header with-border">
        <h3 class="box-title"><i class="fa fa-sliders"></i> 3. Pengaturan Pengambilan Data</h3>
        <div class="box-tools pull-right"><span class="label label-default" id="setBadge">Terkunci</span></div>
    </div>
    <div class="box-body">
        <div class="row">
            <div class="col-md-8">
                <p class="text-muted">Setiap pengaturan bisa <b>Tetap</b> (satu nilai) atau <b>Acak</b> (nilai dipilih acak di antara batas bawah dan atas).</p>
                <div class="row" id="specs"></div>
                <div class="row">
                    <div class="col-sm-6">
                        <div class="form-group">
                            <label>Batas token</label>
                            <div class="input-group">
                                <input type="number" class="form-control text-center" id="token_limit" min="1000" step="1000">
                                <span class="input-group-addon">token</span>
                            </div>
                            <div style="margin-top:8px;">
                                <label class="radio-inline"><input type="radio" name="token_mode" value="total"> Akumulatif (tidak reset)</label><br>
                                <label class="radio-inline"><input type="radio" name="token_mode" value="per_batch"> Reset tiap batch</label>
                            </div>
                            <p class="help-block" id="tokenModeHelp"></p>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="form-group">
                            <label>Batas token harian (semua proses)</label>
                            <div class="input-group">
                                <input type="number" class="form-control text-center" id="daily_limit" min="0" step="1000">
                                <span class="input-group-addon">token/hari</span>
                            </div>
                            <p class="help-block">
                                Terpakai hari ini: <b id="dailyUsed">0</b> &middot; sisa: <b id="dailySisa">-</b>.
                                Reset otomatis pukul 00.00. Bila tercapai, proses <b>menunggu sampai besok</b> lalu lanjut sendiri. Isi <b>0</b> = tanpa batas harian.
                            </p>
                        </div>
                        <div class="checkbox">
                            <label><input type="checkbox" id="auto_fill"> Isi email otomatis ke data kontak</label>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="box box-solid" style="border:1px solid #d2d6de;">
                    <div class="box-header with-border"><h3 class="box-title">Perkiraan</h3></div>
                    <div class="box-body no-padding">
                        <table class="table table-condensed">
                            <tr><td>Akan diproses</td><td class="text-right"><b id="estN">0</b></td></tr>
                            <tr><td>Jumlah batch</td><td class="text-right"><b id="estBatch">0</b></td></tr>
                            <tr><td>Perkiraan durasi</td><td class="text-right"><b id="estDur">-</b></td></tr>
                            <tr><td>Muat dalam batas durasi</td><td class="text-right"><b id="estCap">-</b></td></tr>
                        </table>
                    </div>
                </div>
                <div class="callout callout-info" id="estNote"><p>Proses berjalan terus sampai semua kontak selesai, token habis, kuota Gemini habis, atau batas durasi tercapai.</p></div>
            </div>
        </div>
    </div>
</div>

<!-- ===== Tombol aksi ===== -->
<div class="box box-default locked" id="actionBar">
    <div class="box-body">
        <button id="btnStart" class="btn btn-success"><i class="fa fa-play"></i> Mulai Proses <span class="badge bg-green" id="selCount">0</span></button>
        <button id="btnStop" class="btn btn-danger" disabled><i class="fa fa-stop"></i> Hentikan</button>
        <span class="pull-right">
            <span id="workerWarn" style="display:none;margin-right:10px;" class="text-orange"><i class="fa fa-warning"></i> Worker belum aktif — jalankan <code>php spark emails:work</code></span>
            <span id="stBadge" class="label label-default" style="font-size:13px;">Siap</span>
        </span>
    </div>
</div>

<!-- ===== Antrean / log ===== -->
<div class="box box-default">
    <div class="box-header with-border">
        <h3 class="box-title" id="queueTitle">Kontak Terpilih (pratinjau antrean)</h3>
        <div class="box-tools pull-right"><span class="text-muted" style="font-size:12px;">Setelah dimulai, progres &amp; hasil tampil di halaman Detail</span></div>
    </div>
    <div class="box-body">
        <p id="batchSummary" class="text-muted"></p>
        <div class="table-responsive">
            <table id="tblQueue" class="table table-bordered table-striped table-hover tbl" style="width:100%">
                <thead><tr><th width="40" class="text-center">No</th><th>Kontak / Perusahaan</th><th>Website</th><th class="text-center" width="100">Batch</th><th class="text-center" width="90">Status</th><th>Catatan</th></tr></thead>
            </table>
        </div>
    </div>
</div>

<!-- ===== Modal pilih kontak ===== -->
<div class="modal fade" id="modalKontak" tabindex="-1" role="dialog" data-backdrop="static">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Tutup"><span>&times;</span></button>
                <h4 class="modal-title"><i class="fa fa-envelope-o"></i> Pilih Kontak Tanpa Email</h4>
            </div>
            <div class="modal-body">
                <p>
                    <span class="label label-info">Tanpa email: <span id="mAvail">0</span></span>
                    <span class="label label-primary">Punya website: <span id="mWeb">0</span></span>
                    <span class="label label-success">Dipilih: <span id="mSel">0</span></span>
                </p>
                <p>
                    <button type="button" class="btn btn-default btn-sm" id="mSelAll"><i class="fa fa-check-square-o"></i> Pilih semua (berwebsite)</button>
                    <button type="button" class="btn btn-default btn-sm" id="mSelNone"><i class="fa fa-square-o"></i> Kosongkan</button>
                    <span class="text-muted" style="margin-left:8px;">Kontak tanpa website valid tidak bisa dipilih.</span>
                    <select id="fCheck" class="form-control input-sm" style="display:inline-block;width:auto;margin-left:8px;">
                        <option value="all">Semua kontak</option>
                        <option value="fresh">Dicek &lt; 30 hari lalu</option>
                        <option value="stale">Belum dicek / &gt; 30 hari</option>
                    </select>
                </p>
                <div class="table-responsive">
                    <table id="tblPick" class="table table-bordered table-striped table-hover tbl" style="width:100%">
                        <thead><tr>
                            <th width="30" class="text-center"><input type="checkbox" id="chkAll" title="Pilih semua hasil pencarian"></th>
                            <th>Kontak / Perusahaan</th><th>Website</th><th class="text-center" width="130">Terakhir dicek</th><th class="text-center" width="90">Status</th>
                        </tr></thead>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-success" id="btnPickOk"><i class="fa fa-check"></i> Pakai <span id="mSel2">0</span> Kontak</button>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
/* ==================================================================
   KONFIG & STATE
   ================================================================== */
const API_BASE = '<?= base_url('extractor/api') ?>';
const DETAIL_BASE = '<?= base_url('extractor/detail') ?>';
const esc = (s) => String(s ?? '').replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
const num = (n) => Number(n || 0).toLocaleString('id-ID');
const Toast = Swal.mixin({ toast:true, position:'top-end', showConfirmButton:false, timer:3000, timerProgressBar:true });
const EST_SEC_PER_CONTACT = 12;   // perkiraan lama proses 1 kontak (unduh website + AI)

const STATE = {
    apiValidated: false,
    kontak: [], checked: new Set(), draft: new Set(),
    batchOf: new Map(), taken: 0, totalBatches: 0,
    lastJobId: 0
};

const XT = {
    async api(path, body) {
        const isPost = body !== undefined;
        const res = await fetch(API_BASE + path, {
            method: isPost ? 'POST' : 'GET',
            headers: isPost ? { 'Content-Type':'application/json', 'X-Requested-With':'XMLHttpRequest' } : { 'X-Requested-With':'XMLHttpRequest' },
            body: isPost ? JSON.stringify(body) : undefined
        });
        if (!res.ok) {
            let m = 'HTTP ' + res.status;
            try { const d = await res.json(); m = d.error || m; }
            catch { const t = await res.text(); m += ': ' + t.substring(0, 200); }
            throw new Error(m);
        }
        return res.json();
    },
    async run(fn) {
        try { return await fn(); }
        catch (e) { Swal.close(); Swal.fire({ icon:'error', title:'Kesalahan', html:`<div style="text-align:left;">${esc(e.message)}</div>` }); }
    }
};

/* ==================================================================
   PENGATURAN: semuanya punya mode TETAP / ACAK
   ================================================================== */
const SPECS = [
    { id:'hours',    label:'Batas durasi proses',            unit:'jam',        min:0.1,  step:0.5, def:{ val:24, min:12, max:24 }, hint:'Mode acak: durasi dipilih sekali saat proses dimulai.' },
    { id:'total',    label:'Total pengambilan maksimal',     unit:'kontak',     min:1,    step:1,   def:{ val:100, min:50, max:100 }, hint:'Mode acak: jumlah dipilih sekali saat proses dimulai.' },
    { id:'batch',    label:'Kontak per batch',               unit:'kontak',     min:1,    step:1,   def:{ val:10, min:5, max:15 }, hint:'Mode acak: tiap batch ukurannya berbeda.' },
    { id:'perhour',  label:'Maks. pengambilan per jam',      unit:'kontak/jam', min:0,    step:1,   def:{ val:0, min:20, max:40 }, hint:'Isi 0 (mode tetap) = tanpa batas. Mode acak: batas dipilih ulang tiap jam.' },
    { id:'delay',    label:'Jeda antar kontak (dalam batch)',unit:'detik',      min:2,    step:1,   def:{ val:5, min:3, max:10 }, hint:'Mode acak: jeda dipilih acak sebelum tiap kontak.' },
    { id:'interval', label:'Jeda antar batch',               unit:'menit',      min:0.05, step:0.1, def:{ val:5, min:2, max:8 }, hint:'Mode acak: jeda dipilih acak setiap akhir batch.' }
];
const SPEC_MODE = {};
const PFX = 'ext4_';

function store(k, v) {
    try {
        if (v === undefined) return localStorage.getItem(PFX + k);
        localStorage.setItem(PFX + k, v);
    } catch { return null; }
}

function renderSpecs() {
    $('#specs').html(SPECS.map(sp => `
        <div class="col-sm-6">
          <div class="form-group">
            <label class="spec-label">${esc(sp.label)}
              <span class="btn-group btn-group-xs pull-right mode-toggle" data-spec="${sp.id}">
                <button type="button" class="btn btn-default" data-m="tetap">Tetap</button>
                <button type="button" class="btn btn-default" data-m="acak">Acak</button>
              </span>
            </label>
            <div class="input-group" id="${sp.id}_tetap">
              <input type="number" class="form-control text-center" id="${sp.id}_val" min="${sp.min}" step="${sp.step}">
              <span class="input-group-addon">${esc(sp.unit)}</span>
            </div>
            <div class="input-group" id="${sp.id}_acak" style="display:none;">
              <input type="number" class="form-control text-center" id="${sp.id}_min" min="${sp.min}" step="${sp.step}" title="Batas bawah">
              <span class="input-group-addon">s/d</span>
              <input type="number" class="form-control text-center" id="${sp.id}_max" min="${sp.min}" step="${sp.step}" title="Batas atas">
              <span class="input-group-addon">${esc(sp.unit)}</span>
            </div>
            <p class="help-block">${esc(sp.hint)}</p>
          </div>
        </div>`).join(''));

    SPECS.forEach(sp => {
        ['val', 'min', 'max'].forEach(f => $('#' + sp.id + '_' + f).val(store(sp.id + '_' + f) ?? sp.def[f]));
        applyMode(sp.id, store(sp.id + '_mode') === 'acak' ? 'acak' : 'tetap', false);
    });
}
function applyMode(id, mode, save = true) {
    SPEC_MODE[id] = mode;
    $(`.mode-toggle[data-spec="${id}"] button`).each(function () {
        const on = this.dataset.m === mode;
        $(this).toggleClass('btn-primary active', on).toggleClass('btn-default', !on);
    });
    $('#' + id + '_tetap').toggle(mode === 'tetap');
    $('#' + id + '_acak').toggle(mode === 'acak');
    if (save) { store(id + '_mode', mode); settingsChanged(); }
}
function getSpec(sp) {
    const f = (k) => { const v = parseFloat($('#' + sp.id + '_' + k).val()); return Number.isFinite(v) ? Math.max(sp.min, v) : sp.def[k]; };
    let val = f('val'), min = f('min'), max = f('max');
    if (min > max) [min, max] = [max, min];
    return { mode: SPEC_MODE[sp.id] || 'tetap', val, min, max };
}
const specOf = (id) => getSpec(SPECS.find(s => s.id === id));
const avg = (s) => s.mode === 'acak' ? (s.min + s.max) / 2 : s.val;
const perHourAvg = (s) => (s.mode === 'acak' ? s.max <= 0 : s.val <= 0) ? 0 : avg(s);
const allSpecs = () => Object.fromEntries(SPECS.map(sp => [sp.id, getSpec(sp)]));

renderSpecs();
$('#specs').on('click', '.mode-toggle button', function () { applyMode($(this).closest('.mode-toggle').data('spec'), this.dataset.m); });
$('#specs').on('input change', 'input', function () { store(this.id, this.value); settingsChanged(); });

// field lain
['key', 'model', 'token_limit', 'daily_limit'].forEach(k => {
    const def = { key:'', model:'gemini-3.5-flash-lite', token_limit:'1000000', daily_limit:'1000000' }[k];
    $('#' + k).val(store(k) ?? def).on('input change', function () { store(k, this.value); });
});
const TOKEN_HELP = {
    total: 'Satu batas untuk SELURUH proses. Sisa token terus berkurang dari batch ke batch, proses berhenti saat habis.',
    per_batch: 'Setiap batch mulai lagi dengan jatah token penuh (reset). Jika jatah batch habis, sisa kontak batch itu dilewati.'
};
function setTokenMode(m, save = true) {
    m = m === 'per_batch' ? 'per_batch' : 'total';
    $('input[name="token_mode"][value="' + m + '"]').prop('checked', true);
    $('#tokenModeHelp').text(TOKEN_HELP[m]);
    if (save) store('token_mode', m);
}
const getTokenMode = () => $('input[name="token_mode"]:checked').val() === 'per_batch' ? 'per_batch' : 'total';
setTokenMode(store('token_mode'), false);
$('input[name="token_mode"]').on('change', function () { setTokenMode(this.value); });
$('#auto_fill').prop('checked', (store('auto_fill') ?? '1') === '1').on('change', function () { store('auto_fill', this.checked ? '1' : '0'); });

// simpan nilai spec memakai kunci "<id>_<val|min|max>" (id elemen sudah sama dengan kunci)
$('#btnEye').on('click', function () {
    const i = $('#key'), show = i.attr('type') === 'password';
    i.attr('type', show ? 'text' : 'password');
    $(this).find('i').toggleClass('fa-eye', !show).toggleClass('fa-eye-slash', show);
});

/* ==================================================================
   LANGKAH 1: KONEKSI AI
   ================================================================== */
function setApiValidated(ok) {
    STATE.apiValidated = ok;
    $('#apiBadge').attr('class', 'label ' + (ok ? 'label-success' : 'label-warning')).text(ok ? 'Terhubung' : 'Belum terhubung');
    $('#pickBadge, #setBadge').attr('class', 'label ' + (ok ? 'label-success' : 'label-default')).text(ok ? 'Aktif' : 'Terkunci');
    $('#cardPick, #cardSet, #actionBar').toggleClass('locked', !ok);
    $('#apiHint').toggle(!ok);
    if (ok) load();
}

$('#btnTest').on('click', () => XT.run(async () => {
    const key = ($('#key').val() || '').trim(), model = ($('#model').val() || '').trim();
    if (!key) {
        const ok = await Swal.fire({ title:'API Key kosong', text:'Pakai API key dari file .env server?', icon:'question', showCancelButton:true, confirmButtonText:'Ya, lanjut', cancelButtonText:'Batal' });
        if (!ok.isConfirmed) return;
    }
    Swal.fire({ title:'Menghubungkan...', text:'Menguji koneksi ke Gemini', allowOutsideClick:false, didOpen:() => Swal.showLoading() });
    const r = await XT.api('/test', { key, model });
    Swal.close();
    if (r.model && r.model !== model) $('#model').val(r.model).trigger('change');
    setApiValidated(true);
    Toast.fire({ icon:'success', title:'Terhubung ke ' + (r.model || model) });
}));

$('#btnModels').on('click', () => XT.run(async () => {
    const r = await XT.api('/models', { key: ($('#key').val() || '').trim() });
    $('#modelList').html(r.models.map(m => `<option value="${esc(m.id)}">${esc(m.name)}</option>`).join(''));
    Toast.fire({ icon:'success', title: r.models.length + ' model tersedia' });
}));

/* ==================================================================
   TABEL
   ================================================================== */
const ST = { pending:['Antre','default'], processing:['Proses','warning'], done:['Selesai','success'], empty:['Kosong','warning'], error:['Gagal','danger'] };
const badge = (s) => { const m = ST[s] || [s, 'default']; return `<span class="label label-${m[1]}">${esc(m[0])}</span>`; };
const LANG = { search:'Cari:', lengthMenu:'Tampilkan _MENU_', info:'_START_–_END_ dari _TOTAL_', infoEmpty:'0 data', infoFiltered:'(dari _MAX_)', zeroRecords:'Tidak ada data', paginate:{ previous:'‹', next:'›' } };

const nameCell = (d, t, r) => {
    const main = d || r.nama || '(tanpa nama)';
    if (t !== 'display') return main + ' ' + (r.nama || '') + ' ' + (r.kota_kabupaten || '');
    const sub = [d && r.nama ? r.nama : '', r.kota_kabupaten || ''].filter(Boolean).join(' · ');
    return `<strong>${esc(main)}</strong>` + (sub ? `<br><small class="text-muted">${esc(sub)}</small>` : '');
};
const webCell = (d, t, r) => {
    if (t !== 'display') return d || '';
    if (!d) return '<span class="text-muted">—</span>';
    const url = r.url || (/^https?:\/\//i.test(d) ? d : 'https://' + d);
    if (r.has_web === false) return `<span class="url-cell text-muted" title="${esc(d)}"><i class="fa fa-times-circle text-red"></i> ${esc(d)}</span>`;
    return `<a class="url-cell" href="${esc(url)}" target="_blank" rel="noopener"><i class="fa fa-external-link"></i> ${esc(d)}</a>`;
};

const dtQ = $('#tblQueue').DataTable({
    data: [], deferRender: true, autoWidth: false, order: [], pageLength: 10,
    language: { ...LANG, emptyTable:'Belum ada kontak dipilih.' },
    columns: [
        { data:null, orderable:false, className:'text-center', render:() => '' },
        { data:'nama_perusahaan', render:nameCell },
        { data:'website', render:webCell },
        { data:'batch', className:'text-center', render:(d, t, r) => t === 'display'
            ? (d ? `<span class="label label-primary">${r.approx ? '≈ ' : ''}Batch ${d}</span>` : '<span class="text-muted">—</span>') : (d || 0) },
        { data:'status', className:'text-center', render:(d, t) => t === 'display' ? badge(d) : d },
        { data:'note', className:'note-cell', render:(d, t) => t === 'display' ? (d ? `<span title="${esc(d)}">${esc(d)}</span>` : '<span class="text-muted">-</span>') : (d || '') }
    ],
    drawCallback: function () {
        const api = this.api(), start = api.page.info().start;
        api.column(0, { page:'current' }).nodes().each((c, i) => c.innerHTML = start + i + 1);
    }
});

const dtP = $('#tblPick').DataTable({
    data: [], deferRender: true, autoWidth: false, order: [], pageLength: 10,
    language: { ...LANG, emptyTable:'Tidak ada kontak tanpa email.' },
    columns: [
        { data:null, orderable:false, className:'text-center', render:(d, t, r) => t === 'display'
            ? `<input type="checkbox" class="chk" data-id="${r.id}" ${STATE.draft.has(r.id) ? 'checked' : ''} ${r.has_web ? '' : 'disabled'}>` : '' },
        { data:'nama_perusahaan', render:nameCell },
        { data:'website', render:webCell },
        { data:'last_check', className:'text-center', render:(d, t, r) => {
            if (t !== 'display') return d || '';
            if (!d) return '<span class="text-muted">Belum pernah</span>';
            const cls = Number(r.fresh) === 1 ? 'success' : 'default';
            return `<span class="label label-${cls}" title="${esc(d)}">${r.hari_lalu === 0 ? 'Hari ini' : r.hari_lalu + ' hari lalu'}</span>`
                + `<br><small class="text-muted">${r.n} email · ${r.n_telepon} HP · ${r.n_website} web</small>`;
        } },
        { data:'status', className:'text-center', render:(d, t) => t === 'display' ? badge(d) : d }
    ]
});

/* ==================================================================
   DATA KONTAK + MODAL
   ================================================================== */
async function load() {
    const k = await XT.api('/kontak');
    STATE.kontak = k.map(r => ({ ...r, id:Number(r.id), has_web:Number(r.has_web) === 1 }));

    const ids = new Set(STATE.kontak.map(r => r.id));
    [...STATE.checked].forEach(i => { if (!ids.has(i)) STATE.checked.delete(i); });

    const web = STATE.kontak.filter(r => r.has_web).length;
    $('#pickAvail, #mAvail').text(num(STATE.kontak.length));
    $('#pickWeb, #mWeb').text(num(web));
    dtP.clear().rows.add(STATE.kontak).draw(false);
    afterSelection();
}
function refreshDraft() {
    $('#mSel, #mSel2').text(num(STATE.draft.size));
    dtP.rows().invalidate('data').draw(false);
}

$('#btnPick').on('click', () => XT.run(async () => {
    await load();
    STATE.draft = new Set(STATE.checked);
    refreshDraft();
    $('#modalKontak').modal('show');
}));
$('#modalKontak').on('shown.bs.modal', () => dtP.columns.adjust().draw(false));
// Filter "dicek < 30 hari" dijalankan di tabel (data tetap lengkap, jadi pilihan & antrean tidak terpengaruh)
$.fn.dataTable.ext.search.push((settings, data, idx) => {
    if (settings.nTable.id !== 'tblPick') return true;
    const f = $('#fCheck').val() || 'all';
    if (f === 'all') return true;
    const r = dtP.row(idx).data();
    if (!r) return true;
    return f === 'fresh' ? Number(r.fresh) === 1 : Number(r.fresh) !== 1;
});
$('#fCheck').on('change', () => dtP.draw());
$('#mSelAll').on('click', () => { STATE.draft = new Set(STATE.kontak.filter(r => r.has_web).map(r => r.id)); refreshDraft(); });
$('#mSelNone').on('click', () => { STATE.draft = new Set(); refreshDraft(); });
$('#chkAll').on('change', function () {
    const on = this.checked;
    dtP.rows({ search:'applied' }).data().toArray().forEach(r => { if (r.has_web) on ? STATE.draft.add(r.id) : STATE.draft.delete(r.id); });
    refreshDraft();
});
$('#tblPick tbody').on('change', '.chk', function () {
    const id = Number(this.dataset.id);
    this.checked ? STATE.draft.add(id) : STATE.draft.delete(id);
    $('#mSel, #mSel2').text(num(STATE.draft.size));
});
$('#btnPickOk').on('click', () => {
    STATE.checked = new Set(STATE.draft);
    $('#modalKontak').modal('hide');
    afterSelection();
    if (STATE.checked.size) Toast.fire({ icon:'success', title: num(STATE.checked.size) + ' kontak dipilih' });
});

/* ==================================================================
   BATCH & PERKIRAAN (memakai nilai rata-rata untuk mode acak)
   ================================================================== */
function recalcBatch() {
    const S = allSpecs();
    const size  = Math.max(1, Math.round(avg(S.batch)));
    const limit = Math.max(1, Math.round(avg(S.total)));
    const approx = S.batch.mode === 'acak';

    STATE.batchOf = new Map();
    let n = 0;
    STATE.kontak.forEach(r => {
        if (STATE.checked.has(r.id) && n < limit) { STATE.batchOf.set(r.id, Math.floor(n / size) + 1); n++; }
    });
    STATE.taken = n;
    STATE.totalBatches = Math.ceil(n / size);
    STATE.approx = approx || S.total.mode === 'acak';

    const outside = Math.max(0, STATE.checked.size - n);
    let t;
    if (!STATE.checked.size) t = 'Belum ada kontak dipilih. Klik “Pilih Data Kontak”.';
    else {
        t = `${STATE.approx ? '≈ ' : ''}${num(n)} kontak akan diproses dalam ${STATE.approx ? '≈ ' : ''}${num(STATE.totalBatches)} batch.`;
        if (outside > 0) t += ` ${num(outside)} kontak berada di luar batas Total Pengambilan.`;
        if (STATE.approx) t += ' (Mode acak: angka pasti ditentukan saat proses dimulai.)';
    }
    $('#batchSummary').text(t);
}

function fmtDur(sec) {
    sec = Math.round(sec);
    if (sec < 60) return sec + ' dtk';
    const h = Math.floor(sec / 3600), m = Math.floor((sec % 3600) / 60);
    return (h ? h + ' j ' : '') + m + ' mnt';
}
function calc() {
    const S = allSpecs();
    const n = STATE.taken;
    const size = Math.max(1, Math.round(avg(S.batch)));
    const perAcc = EST_SEC_PER_CONTACT + avg(S.delay);
    const interval = avg(S.interval) * 60;
    const ph = perHourAvg(S.perhour);
    const hours = avg(S.hours);
    const batches = Math.ceil(n / size);

    let total = n * perAcc + Math.max(0, batches - 1) * interval;
    if (ph > 0) total = Math.max(total, (n / ph) * 3600);

    const cycle = size * perAcc + interval;
    let cap = Math.floor(hours * 3600 / cycle) * size;
    cap += Math.min(size, Math.floor(((hours * 3600) % cycle) / perAcc));
    if (ph > 0) cap = Math.min(cap, Math.floor(ph * hours));
    return { n, batches, total, cap, hours };
}
function estimate() {
    const e = calc(), p = STATE.approx ? '≈ ' : '';
    $('#estN').text(p + num(e.n));
    $('#estBatch').text(p + num(e.batches));
    $('#estDur').text(e.n ? p + fmtDur(e.total) : '-');
    $('#estCap').text('±' + num(e.cap) + ' kontak');

    const over = e.n > 0 && e.total > e.hours * 3600;
    $('#estNote').attr('class', 'callout ' + (over ? 'callout-warning' : 'callout-info')).find('p').html(over
        ? `Perkiraan durasi <b>${fmtDur(e.total)}</b> melebihi batas <b>${Math.round(e.hours * 10) / 10} jam</b>. Hanya sekitar <b>${num(e.cap)}</b> kontak yang sempat diproses. Naikkan durasi, perbesar batch, atau perpendek jeda.`
        : 'Proses berjalan terus sampai semua kontak selesai, token habis, kuota Gemini habis, atau batas durasi tercapai. Angka di atas hanya perkiraan.');
}
function settingsChanged() { recalcBatch(); estimate(); renderQueue(); }
function afterSelection() {
    $('#selCount, #pickSel').text(num(STATE.checked.size));
    settingsChanged();
}

function renderQueue() {
    const approx = !!STATE.approx;
    dtQ.clear().rows.add(STATE.kontak.filter(r => STATE.batchOf.has(r.id))
        .map(r => ({ ...r, batch: STATE.batchOf.get(r.id), approx, status:'pending', note:'' }))).draw(false);
}

/* ==================================================================
   MULAI / HENTIKAN
   ================================================================== */
async function doStart() {
    if (!STATE.apiValidated) return Swal.fire('Hubungkan Dulu', 'Klik “Hubungkan & Lanjut” pada langkah 1.', 'warning');
    if (!STATE.checked.size) return Swal.fire('Pilih Kontak', 'Klik “Pilih Data Kontak” lalu centang minimal satu kontak.', 'warning');

    const e = calc();
    if (e.total > e.hours * 3600) {
        const ok = await Swal.fire({ icon:'question', title:'Mungkin tidak semua selesai', text:`Perkiraan ${fmtDur(e.total)} melebihi batas durasi. Lanjutkan?`, showCancelButton:true, confirmButtonText:'Lanjutkan', cancelButtonText:'Batal' });
        if (!ok.isConfirmed) return;
    }

    // kirim SEMUA kontak terpilih (urut sesuai tabel); server menentukan total & batch final
    const ids = STATE.kontak.filter(r => STATE.checked.has(r.id)).map(r => r.id);
    const S = allSpecs();

    Swal.fire({ title:'Menyiapkan antrean...', allowOutsideClick:false, didOpen:() => Swal.showLoading() });
    const r = await XT.api('/start', {
        ids,
        key: ($('#key').val() || '').trim(),
        model: ($('#model').val() || '').trim(),
        token_limit: Math.max(1000, Math.floor(parseFloat($('#token_limit').val()) || 1000000)),
        token_mode: getTokenMode(),
        daily_limit: Math.max(0, Math.floor(parseFloat($('#daily_limit').val()) || 0)),
        auto_fill: $('#auto_fill').is(':checked') ? 1 : 0,
        settings: { hours:S.hours, total:S.total, batch:S.batch, perhour:S.perhour, delay:S.delay, interval:S.interval }
    });
    Swal.close();
    Swal.fire({ icon:'success', title:'Proses dimulai', text:`${r.queued} kontak masuk antrean (${r.batches} batch). Membuka halaman detail...`, timer:1500, showConfirmButton:false });
    // progres, token per batch, dan hasil email ada di halaman detail
    window.location.href = DETAIL_BASE + '/' + r.job_id;
}
$('#btnStart').on('click', () => XT.run(doStart));
$('#btnStop').on('click', () => XT.run(async () => { await XT.api('/stop', {}); await poll(); }));

/* ==================================================================
   POLLING STATUS (ringan: hanya tombol, banner & angka ringkas)
   ================================================================== */
let polling = false;

function updDaily(used) {
    if (used !== undefined) STATE.dailyUsed = Number(used || 0);
    const used2 = Number(STATE.dailyUsed || 0), lim = Math.max(0, Math.floor(parseFloat($('#daily_limit').val()) || 0));
    $('#dailyUsed').text(num(used2));
    $('#dailySisa').text(lim > 0 ? num(Math.max(0, lim - used2)) : 'tanpa batas');
}
$('#daily_limit').on('input change', () => updDaily());

async function poll() {
    if (polling) return;
    polling = true;
    try {
        const s = await XT.api('/status');
        const j = s.job || null;
        const busy = !!j && ['queued', 'running'].includes(j.status);
        updDaily(s.token_hari_ini);

        $('#btnStart').prop('disabled', busy);
        $('#btnStop').prop('disabled', !busy);
        $('#workerWarn').toggle(!s.worker && busy);
        if (busy && !STATE.apiValidated) setApiValidated(true);

        if (j) {
            const url = DETAIL_BASE + '/' + j.id;
            $('#lnkLast').attr('href', url).show();
            $('#lnkActive').attr('href', url);
            $('#activeJob').toggle(busy);
            $('#activeMsg').text(`${j.message || 'Berjalan'} — ${num(j.processed_count)}/${num(j.total)} kontak, batch ${num(j.current_batch)}/${num(j.total_batches)}`);
            $('#stBadge').attr('class', 'label label-' + (busy ? 'primary' : 'default')).text(busy ? 'Berjalan' : 'Siap');

            // daftar kontak tanpa email berubah saat proses jalan (auto-fill)
            const sig = [j.id, j.status, j.processed_count, j.found_count].join(':');
            if (sig !== poll.sig) { poll.sig = sig; if (STATE.apiValidated) await load(); }
        } else {
            $('#activeJob, #lnkLast').hide();
            $('#stBadge').attr('class', 'label label-default').text('Siap');
        }
    } catch (e) {
        /* abaikan, coba lagi pada polling berikutnya */
    } finally {
        polling = false;
    }
}

$(function () {
    setApiValidated(false);
    afterSelection();
    poll();
    setInterval(poll, 3000);
});
</script>
<?= $this->endSection() ?>