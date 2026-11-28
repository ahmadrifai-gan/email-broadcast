<?= $this->extend('layouts/main') ?>

<?= $this->section('title') ?>Data Email<?= $this->endSection() ?>
<?= $this->section('page_title') ?>Data &amp; Hasil Pencarian Email<?= $this->endSection() ?>
<?= $this->section('page_subtitle') ?>Email kontak yang ditemukan otomatis dari website perusahaan<?= $this->endSection() ?>

<?= $this->section('breadcrumb') ?>
<li><a href="<?= base_url() ?>"><i class="fa fa-dashboard"></i> Home</a></li>
<li class="active">Data Email</li>
<?= $this->endSection() ?>

<?= $this->section('styles') ?>
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
<style>
    .url-cell { max-width:220px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; display:inline-block; vertical-align:middle; }
    .note-cell { max-width:360px; }
    .tbl td { vertical-align:middle !important; }
    .filter-bar { margin-bottom:12px; }
    /* 5 kartu ringkasan sebaris di layar lebar */
    @media (min-width:992px) { .col-md-5ths { width:20%; float:left; position:relative; min-height:1px; padding-left:15px; padding-right:15px; } }
    .info-box-text { white-space:normal; font-size:12px; line-height:1.25; }
    .info-box .progress-description { font-size:11px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<!-- ===== Ringkasan ===== -->
<div class="row">
    <div class="col-md-5ths col-sm-6 col-xs-12">
        <div class="info-box"><span class="info-box-icon bg-aqua"><i class="fa fa-envelope-o"></i></span>
            <div class="info-box-content"><span class="info-box-text">Kontak Tanpa Email</span><span class="info-box-number" id="sTanpa">0</span></div></div>
    </div>
    <div class="col-md-5ths col-sm-6 col-xs-12">
        <div class="info-box"><span class="info-box-icon bg-green"><i class="fa fa-check-circle"></i></span>
            <div class="info-box-content"><span class="info-box-text">Kontak Sudah Ber-email</span><span class="info-box-number" id="sPunya">0</span></div></div>
    </div>
    <div class="col-md-5ths col-sm-6 col-xs-12">
        <div class="info-box"><span class="info-box-icon bg-yellow"><i class="fa fa-search"></i></span>
            <div class="info-box-content"><span class="info-box-text">Email Ditemukan</span><span class="info-box-number" id="sHasil">0</span></div></div>
    </div>
    <div class="col-md-5ths col-sm-6 col-xs-12">
        <div class="info-box"><span class="info-box-icon bg-red"><i class="fa fa-hourglass-half"></i></span>
            <div class="info-box-content"><span class="info-box-text">Belum Dipakai</span><span class="info-box-number" id="sBelum">0</span></div></div>
    </div>
    <div class="col-md-5ths col-sm-6 col-xs-12">
        <div class="info-box"><span class="info-box-icon bg-purple"><i class="fa fa-key"></i></span>
            <div class="info-box-content"><span class="info-box-text">Token Hari Ini</span><span class="info-box-number" id="sToken">0</span>
                <span class="progress-description text-muted" id="sTokenSub">Reset otomatis pukul 00.00</span></div></div>
    </div>
</div>

<div id="workerWarn" class="callout callout-warning" style="display:none;">
    <p><i class="fa fa-warning"></i> Worker belum aktif. Jalankan <code>php spark emails:work</code> agar antrean diproses.</p>
</div>

<p>
    <a href="<?= base_url('extractor/cari') ?>" class="btn btn-primary"><i class="fa fa-search"></i> Cari Email</a>
    <span id="loadErr" class="text-red" style="margin-left:10px;"></span>
</p>

<!-- ===== Tabel ===== -->
<div class="nav-tabs-custom">
    <ul class="nav nav-tabs">
        <li class="active"><a href="#tabJobs" data-toggle="tab"><i class="fa fa-tasks"></i> Riwayat Proses</a></li>
        <li><a href="#tabHasil" data-toggle="tab"><i class="fa fa-envelope"></i> Hasil Email</a></li>
        <li><a href="#tabRiwayat" data-toggle="tab"><i class="fa fa-history"></i> Riwayat per Kontak</a></li>
        <li class="pull-right"><a href="<?= base_url('extractor/cari') ?>" class="text-primary"><i class="fa fa-search"></i> Cari Email</a></li>
    </ul>
    <div class="tab-content">
        <div class="tab-pane" id="tabHasil">
            <div class="filter-bar form-inline">
                <label>Status:&nbsp;</label>
                <select id="fHasil" class="form-control input-sm">
                    <option value="">Semua</option>
                    <option value="Belum dipakai">Belum dipakai</option>
                    <option value="Dipakai">Dipakai</option>
                    <option value="Kontak sudah ber-email">Kontak sudah ber-email</option>
                </select>
            </div>
            <div class="table-responsive">
                <table id="tblHasil" class="table table-bordered table-striped table-hover tbl" style="width:100%">
                    <thead><tr><th>Kontak / Perusahaan</th><th>Website</th><th>Email</th><th>Label</th><th class="text-center" width="130">Status</th><th class="text-center" width="150">Aksi</th></tr></thead>
                </table>
            </div>
        </div>
        <div class="tab-pane" id="tabRiwayat">
            <div class="filter-bar form-inline">
                <label>Status:&nbsp;</label>
                <select id="fRiwayat" class="form-control input-sm">
                    <option value="">Semua</option>
                    <option value="done">Selesai</option>
                    <option value="empty">Kosong</option>
                    <option value="error">Gagal</option>
                    <option value="pending">Antre</option>
                    <option value="processing">Proses</option>
                </select>
            </div>
            <div class="table-responsive">
                <table id="tblRiwayat" class="table table-bordered table-striped table-hover tbl" style="width:100%">
                    <thead><tr><th>Kontak / Perusahaan</th><th>Website</th><th class="text-center" width="90">Status</th><th class="text-center" width="90">Email</th><th>Catatan</th><th width="140">Diproses</th><th class="text-center" width="90">Aksi</th></tr></thead>
                </table>
            </div>
        </div>
        <div class="tab-pane active" id="tabJobs">
            <p class="text-muted" style="margin-bottom:10px;">Satu baris = satu kali proses <b>Cari Email</b> (berisi banyak kontak). Klik <b>Detail</b> untuk melihat progres, token per batch, dan hasil tiap kontak.</p>
            <div class="table-responsive">
                <table id="tblJobs" class="table table-bordered table-striped table-hover tbl" style="width:100%">
                    <thead><tr><th width="50" class="text-center">#</th><th>Dibuat</th><th class="text-center">Status</th><th class="text-center">Kontak</th><th class="text-center">Batch</th><th class="text-center">Email</th><th class="text-right">Token Terpakai</th><th>Mode Token</th><th>Model</th><th class="text-center" width="90">Aksi</th></tr></thead>
                </table>
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
const API_BASE = '<?= base_url('extractor/api') ?>';
const DETAIL_BASE = '<?= base_url('extractor/detail') ?>';
const esc = (s) => String(s ?? '').replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
const num = (n) => Number(n || 0).toLocaleString('id-ID');
const Toast = Swal.mixin({ toast:true, position:'top-end', showConfirmButton:false, timer:3000, timerProgressBar:true });

async function api(path, body) {
    const post = body !== undefined;
    const res = await fetch(API_BASE + path, {
        method: post ? 'POST' : 'GET',
        headers: post ? { 'Content-Type':'application/json', 'X-Requested-With':'XMLHttpRequest' } : { 'X-Requested-With':'XMLHttpRequest' },
        body: post ? JSON.stringify(body) : undefined
    });
    if (!res.ok) {
        let m = 'HTTP ' + res.status;
        try { m = (await res.json()).error || m; } catch { /* abaikan */ }
        throw new Error(m);
    }
    return res.json();
}

const LANG = { search:'Cari:', lengthMenu:'Tampilkan _MENU_', info:'_START_–_END_ dari _TOTAL_', infoEmpty:'0 data', infoFiltered:'(dari _MAX_)', zeroRecords:'Tidak ada data', paginate:{ previous:'‹', next:'›' } };

const nameCell = (d, t, r) => {
    const main = d || r.nama || '(tanpa nama)';
    if (t !== 'display') return main + ' ' + (r.nama || '') + ' ' + (r.kota_kabupaten || '');
    const sub = [d && r.nama ? r.nama : '', r.kota_kabupaten || ''].filter(Boolean).join(' · ');
    return `<strong>${esc(main)}</strong>` + (sub ? `<br><small class="text-muted">${esc(sub)}</small>` : '');
};
const webCell = (d, t) => {
    if (t !== 'display') return d || '';
    if (!d) return '<span class="text-muted">—</span>';
    const url = /^https?:\/\//i.test(d) ? d : 'https://' + d;
    return `<a class="url-cell" href="${esc(url)}" target="_blank" rel="noopener"><i class="fa fa-external-link"></i> ${esc(d)}</a>`;
};

// tombol Detail -> halaman detail proses pencarian asal data ini (menyorot kontaknya)
const detailBtn = (jobId, kontakId, full = false) => jobId
    ? `<a class="btn btn-xs btn-primary" href="${DETAIL_BASE}/${jobId}?kontak=${kontakId}" title="Lihat detail proses #${jobId}"><i class="fa fa-list-alt"></i> Detail${full ? ' #' + jobId : ''}</a>`
    : '<span class="text-muted" title="Data ini belum tercatat di proses mana pun">-</span>';

// status teks (dipakai untuk filter) -> label
function hasilStatus(r) {
    if (Number(r.dipakai) === 1) return 'Dipakai';
    if (String(r.email_kontak || '').trim() !== '') return 'Kontak sudah ber-email';
    return 'Belum dipakai';
}
const HS_CLASS = { 'Dipakai':'success', 'Kontak sudah ber-email':'default', 'Belum dipakai':'warning' };

const dtH = $('#tblHasil').DataTable({
    data: [], deferRender: true, autoWidth: false, order: [], pageLength: 10,
    language: { ...LANG, emptyTable:'Belum ada hasil pencarian.' },
    columns: [
        { data:'nama_perusahaan', render:nameCell },
        { data:'website', render:webCell },
        { data:'email', render:(d,t) => t === 'display' ? `<code>${esc(d)}</code>` : d },
        { data:'label', render:(d) => esc(d || '-') },
        { data:null, className:'text-center', render:(d,t,r) => { const s = hasilStatus(r); return t === 'display' ? `<span class="label label-${HS_CLASS[s]}">${esc(s)}</span>` : s; } },
        { data:null, orderable:false, className:'text-center', render:(d,t,r) => (hasilStatus(r) === 'Belum dipakai'
            ? `<button class="btn btn-xs btn-success btn-pakai" data-id="${r.id}"><i class="fa fa-check"></i> Pakai</button> ` : '') + detailBtn(r.job_id, r.kontak_id) }
    ]
});

const ST = { pending:['Antre','default'], processing:['Proses','warning'], done:['Selesai','success'], empty:['Kosong','warning'], error:['Gagal','danger'] };
const dtR = $('#tblRiwayat').DataTable({
    data: [], deferRender: true, autoWidth: false, order: [], pageLength: 10,
    language: { ...LANG, emptyTable:'Belum ada riwayat pencarian.' },
    columns: [
        { data:'nama_perusahaan', render:nameCell },
        { data:'website', render:webCell },
        { data:'status', className:'text-center', render:(d,t) => { const m = ST[d] || [d,'default']; return t === 'display' ? `<span class="label label-${m[1]}">${esc(m[0])}</span>` : d; } },
        { data:'found_count', className:'text-center', render:(d) => num(d) },
        { data:'note', className:'note-cell', render:(d,t) => t === 'display' ? (d ? `<span title="${esc(d)}">${esc(d)}</span>` : '<span class="text-muted">-</span>') : (d || '') },
        { data:'processed_at', render:(d) => esc(d || '-') },
        { data:null, orderable:false, className:'text-center', render:(d,t,r) => detailBtn(r.job_id, r.kontak_id) }
    ]
});

const dtJ = $('#tblJobs').DataTable({
    data: [], deferRender: true, autoWidth: false, order: [], pageLength: 10,
    language: { ...LANG, emptyTable:'Belum ada proses pencarian.' },
    columns: [
        { data:'id', className:'text-center', render:(d) => '#' + d },
        { data:'created_at', render:(d) => esc(d || '-') },
        { data:'status', className:'text-center', render:(d, t) => { const m = JOB_MAP[d] || [d, 'default']; return t === 'display' ? `<span class="label label-${m[1]}">${esc(m[0])}</span>` : d; } },
        { data:'processed_count', className:'text-center', render:(d, t, r) => `${num(d)} / ${num(r.total)}` },
        { data:'batches', className:'text-center', render:(d) => num(d) },
        { data:'found_count', className:'text-center', render:(d) => num(d) },
        { data:'tokens_used', className:'text-right', render:(d) => num(d) },
        { data:'token_mode', render:(d, t, r) => (d === 'per_batch' ? `Reset tiap batch (${num(r.token_limit)}/batch)` : `Akumulatif (maks ${num(r.token_limit)})`) + (Number(r.daily_limit) > 0 ? `<br><small class="text-muted">Harian: ${num(r.daily_limit)}/hari</small>` : '') },
        { data:'model', render:(d) => esc(d || '-') },
        { data:null, orderable:false, className:'text-center', render:(d, t, r) => `<a class="btn btn-xs btn-primary" href="${DETAIL_BASE}/${r.id}"><i class="fa fa-list-alt"></i> Lihat Detail</a>` }
    ]
});

$('a[data-toggle="tab"]').on('shown.bs.tab', function () { $.fn.dataTable.tables({ visible:true, api:true }).columns.adjust(); });

$('#fHasil').on('change', function () { dtH.column(4).search(this.value ? '^' + $.fn.dataTable.util.escapeRegex(this.value) + '$' : '', true, false).draw(); });
$('#fRiwayat').on('change', function () { dtR.column(2).search(this.value ? '^' + this.value + '$' : '', true, false).draw(); });

$('#tblHasil tbody').on('click', '.btn-pakai', async function () {
    const id = Number(this.dataset.id);
    try {
        await api('/pakai', { id });
        Toast.fire({ icon:'success', title:'Email dipakai' });
        sig = '';
        tick();
    } catch (e) {
        Swal.fire({ icon:'error', title:'Gagal', text:e.message });
    }
});

const JOB_MAP = {
    queued:['Menunggu worker','warning'], running:['Berjalan','primary'], finished:['Selesai','success'],
    stopped:['Dihentikan','default'], expired:['Waktu habis','warning'], quota:['Berhenti (kuota/token)','danger']
};
let sig = '';

// kartu "Token Hari Ini": total token semua proses sejak 00.00
function fillToken(used, limit) {
    used = Number(used || 0); limit = Number(limit || 0);
    $('#sToken').text(num(used));
    $('#sTokenSub').text(limit > 0
        ? `Batas ${num(limit)} · sisa ${num(Math.max(0, limit - used))}`
        : 'Reset otomatis pukul 00.00');
}

let ticking = false;
async function tick() {
    if (ticking) return;
    ticking = true;
    try {
        const r = await api('/ringkasan');
        const st = r.stats || {};
        $('#sTanpa').text(num(st.tanpa_email));
        $('#sPunya').text(num(st.punya_email));
        $('#sHasil').text(num(st.hasil_total));
        $('#sBelum').text(num(st.hasil_belum_dipakai));
        fillToken(r.token_hari_ini, r.daily_limit);

        const j = r.job || {};
        const busy = ['queued', 'running'].includes(j.status);
        $('#workerWarn').toggle(busy && !r.worker);
        $('#loadErr').text('');

        const s = [j.id, j.status, j.processed_count, j.found_count, st.hasil_total, st.hasil_belum_dipakai].join(':');
        if (s !== sig) {
            sig = s;
            const [h, rw, jb] = await Promise.all([api('/hasil'), api('/riwayat'), api('/jobs')]);
            dtH.clear().rows.add(h).draw(false);
            dtR.clear().rows.add(rw).draw(false);
            dtJ.clear().rows.add(jb).draw(false);
        }
    } catch (e) {
        $('#loadErr').text('Gagal memuat data: ' + e.message);
    } finally {
        ticking = false;
    }
}

$(function () { tick(); setInterval(tick, 5000); });
</script>
<?= $this->endSection() ?>