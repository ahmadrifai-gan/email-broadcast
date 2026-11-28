<?= $this->extend('layouts/main') ?>

<?= $this->section('title') ?>Detail Pencarian Email<?= $this->endSection() ?>
<?= $this->section('page_title') ?>Detail Pencarian Email #<?= (int) $jobId ?><?= $this->endSection() ?>
<?= $this->section('page_subtitle') ?>Progres, pemakaian token per batch, dan hasil email tiap kontak<?= $this->endSection() ?>

<?= $this->section('breadcrumb') ?>
<li><a href="<?= base_url() ?>"><i class="fa fa-dashboard"></i> Home</a></li>
<li><a href="<?= base_url('extractor') ?>">Data Email</a></li>
<li class="active">Detail #<?= (int) $jobId ?></li>
<?= $this->endSection() ?>

<?= $this->section('styles') ?>
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
<style>
    .url-cell { max-width:200px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; display:inline-block; vertical-align:middle; }
    .note-cell { max-width:300px; }
    .tbl td { vertical-align:middle !important; }
    .filter-bar { margin-bottom:12px; }
    .filter-bar .form-control { margin-right:10px; }
    .mail-line { display:block; margin-bottom:2px; white-space:nowrap; }
    .mail-line small { color:#999; }
    #tblBatch td, #tblBatch th { white-space:nowrap; }
    #tblBatch tr.is-current td { background:#f0f7ff; }
    .tok-bar { height:6px; background:#eee; border-radius:3px; margin-top:3px; min-width:90px; }
    .tok-bar > span { display:block; height:100%; background:#00c0ef; border-radius:3px; }
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<p>
    <a href="<?= base_url('extractor') ?>" class="btn btn-default btn-sm"><i class="fa fa-arrow-left"></i> Data Email</a>
    <a href="<?= base_url('extractor/cari') ?>" class="btn btn-primary btn-sm"><i class="fa fa-search"></i> Cari Email Baru</a>
</p>

<!-- ===== Status proses ===== -->
<div class="box box-primary">
    <div class="box-header with-border">
        <h3 class="box-title">Status Proses</h3>
        <div class="box-tools pull-right">
            <button id="btnStop" class="btn btn-danger btn-sm" disabled><i class="fa fa-stop"></i> Hentikan</button>
        </div>
    </div>
    <div class="box-body">
        <div id="workerWarn" class="callout callout-warning" style="display:none;margin-bottom:12px;">
            <p><i class="fa fa-warning"></i> Worker belum aktif. Jalankan <code>php spark emails:work</code> agar antrean diproses.</p>
        </div>
        <p style="margin-bottom:8px;">
            <span id="jStatus" class="label label-default" style="font-size:13px;">-</span>
            <span id="jBatch" class="label label-primary" style="display:none;"></span>
            <span id="jHour" class="label label-info" style="display:none;"></span>
            <span id="jWait" class="label label-warning" style="display:none;"></span>
            <span id="jDeadline" class="label label-danger" style="display:none;"></span>
            <span id="jMode" class="label label-default"></span>
            <span class="text-muted" style="margin-left:6px;">Model: <b id="jModel">-</b></span>
        </p>
        <p><strong id="jMsg">Memuat...</strong></p>
        <p class="text-muted" id="dailyLine" style="margin-top:-4px;"></p>
        <div class="progress progress-sm active" style="margin-bottom:6px;"><div id="jBar" class="progress-bar progress-bar-primary progress-bar-striped" style="width:0%"></div></div>
        <div class="clearfix text-muted" style="font-size:12px;margin-bottom:14px;">
            <span class="pull-left" id="jPct"></span><span class="pull-right" id="jElapsed"></span>
        </div>
        <div class="row text-center">
            <div class="col-sm-3 col-xs-6"><div class="description-block"><h4 class="description-header" id="jProc">0</h4><span class="description-text">Diproses</span></div></div>
            <div class="col-sm-3 col-xs-6"><div class="description-block"><h4 class="description-header text-green" id="jFound">0</h4><span class="description-text">Email Ditemukan</span></div></div>
            <div class="col-sm-3 col-xs-6"><div class="description-block"><h4 class="description-header text-aqua" id="jTok">0</h4><span class="description-text">Total Token Terpakai</span></div></div>
            <div class="col-sm-3 col-xs-6"><div class="description-block"><h4 class="description-header text-red" id="jSisa">0</h4><span class="description-text" id="jSisaLbl">Sisa Token</span></div></div>
        </div>
    </div>
</div>

<!-- ===== Token per batch ===== -->
<div class="box box-info">
    <div class="box-header with-border">
        <h3 class="box-title"><i class="fa fa-key"></i> Token per Batch</h3>
    </div>
    <div class="box-body">
        <div class="callout callout-info" id="tokenNote" style="margin-bottom:12px;"><p>Memuat...</p></div>
        <div class="table-responsive" style="max-height:420px;overflow-y:auto;">
            <table id="tblBatch" class="table table-bordered table-condensed table-hover">
                <thead>
                    <tr>
                        <th class="text-center" width="70">Batch</th>
                        <th class="text-center">Kontak</th>
                        <th class="text-center">Email</th>
                        <th class="text-right">Token Awal</th>
                        <th class="text-right">Token Terpakai</th>
                        <th class="text-right">Token Sisa</th>
                        <th class="text-center">Status</th>
                        <th>Mulai</th>
                        <th>Selesai</th>
                    </tr>
                </thead>
                <tbody id="batchBody"><tr><td colspan="9" class="text-center text-muted">Memuat...</td></tr></tbody>
                <tfoot id="batchFoot"></tfoot>
            </table>
        </div>
    </div>
</div>

<!-- ===== Hasil per kontak ===== -->
<div class="box box-success">
    <div class="box-header with-border">
        <h3 class="box-title"><i class="fa fa-envelope"></i> Hasil per Kontak</h3>
        <div class="box-tools pull-right"><a href="<?= base_url('extractor') ?>" class="btn btn-box-tool"><i class="fa fa-list"></i> Semua hasil email (untuk "Pakai")</a></div>
    </div>
    <div class="box-body">
        <div class="callout callout-info" id="focusNote" style="display:none;">
            <p>Menampilkan kontak <b></b> dari proses ini. <a href="#" id="btnShowAll">Tampilkan semua kontak</a></p>
        </div>
        <div class="filter-bar form-inline">
            <label>Batch:&nbsp;</label>
            <select id="fBatch" class="form-control input-sm"><option value="">Semua</option></select>
            <label>Status:&nbsp;</label>
            <select id="fStatus" class="form-control input-sm">
                <option value="">Semua</option>
                <option value="done">Selesai</option>
                <option value="empty">Kosong</option>
                <option value="error">Gagal</option>
                <option value="skipped">Dilewati</option>
                <option value="processing">Proses</option>
                <option value="pending">Antre</option>
            </select>
        </div>
        <div class="table-responsive">
            <table id="tblItems" class="table table-bordered table-striped table-hover tbl" style="width:100%">
                <thead><tr>
                    <th width="40" class="text-center">No</th>
                    <th>Kontak / Perusahaan</th>
                    <th>Website</th>
                    <th class="text-center" width="70">Batch</th>
                    <th class="text-center" width="85">Status</th>
                    <th>Email Ditemukan</th>
                    <th class="text-right" width="80">Token</th>
                    <th>Catatan</th>
                </tr></thead>
            </table>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
const JOB_ID   = <?= (int) $jobId ?>;
const FOCUS_ID = <?= (int) ($kontakId ?? 0) ?>;   // kontak yang disorot (dari tombol Detail di halaman data)
const API_BASE = '<?= base_url('extractor/api') ?>';
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

const JOB_MAP = {
    queued:['Menunggu worker','warning'], running:['Berjalan','primary'], finished:['Selesai','success'],
    stopped:['Dihentikan','default'], expired:['Waktu habis','warning'], quota:['Berhenti (kuota/token)','danger']
};
const BATCH_MAP = { pending:['Antre','default'], running:['Berjalan','primary'], done:['Selesai','success'], stopped:['Terhenti','warning'] };
const ST = {
    pending:['Antre','default'], processing:['Proses','warning'], done:['Selesai','success'],
    empty:['Kosong','warning'], error:['Gagal','danger'], skipped:['Dilewati','default']
};
const WAIT_TXT = { batch:'Jeda antar batch', hour:'Batas per jam tercapai', rate:'Menunggu limit API', daily:'Batas token harian tercapai' };
const LANG = { search:'Cari:', lengthMenu:'Tampilkan _MENU_', info:'_START_–_END_ dari _TOTAL_', infoEmpty:'0 data', infoFiltered:'(dari _MAX_)', zeroRecords:'Tidak ada data', paginate:{ previous:'‹', next:'›' } };

const dt = (s) => s ? String(s).substring(5, 19).replace('T', ' ') : '-';

/* ---------- tabel kontak ---------- */
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

const dtI = $('#tblItems').DataTable({
    data: [], deferRender: true, autoWidth: false, order: [], pageLength: 25,
    language: { ...LANG, emptyTable:'Belum ada data.' },
    columns: [
        { data:null, orderable:false, className:'text-center', render:() => '' },
        { data:'nama_perusahaan', render:nameCell },
        { data:'website', render:webCell },
        { data:'batch', className:'text-center', render:(d, t) => t === 'display' ? `<span class="label label-primary">${d}</span>` : d },
        { data:'status', className:'text-center', render:(d, t) => { const m = ST[d] || [d, 'default']; return t === 'display' ? `<span class="label label-${m[1]}">${esc(m[0])}</span>` : d; } },
        { data:'emails', render:(d, t, r) => {
            const list = d || [];
            if (t !== 'display') return list.map(x => x.email).join(' ');
            if (!list.length) return r.found_count > 0 ? `<span class="text-muted">${num(r.found_count)} email</span>` : '<span class="text-muted">-</span>';
            return list.map(x => {
                const used = r.email_kontak && String(r.email_kontak).toLowerCase() === String(x.email).toLowerCase();
                return `<span class="mail-line"><code>${esc(x.email)}</code>${x.label ? ` <small>${esc(x.label)}</small>` : ''}${used ? ' <i class="fa fa-check-circle text-green" title="Sudah diisi ke kontak"></i>' : ''}</span>`;
            }).join('');
        } },
        { data:'tokens_used', className:'text-right', render:(d, t) => t === 'display' ? (Number(d) ? num(d) : '<span class="text-muted">0</span>') : Number(d || 0) },
        { data:'note', className:'note-cell', render:(d, t) => t === 'display' ? (d ? `<span title="${esc(d)}">${esc(d)}</span>` : '<span class="text-muted">-</span>') : (d || '') }
    ],
    drawCallback: function () {
        const api = this.api(), start = api.page.info().start;
        api.column(0, { page:'current' }).nodes().each((c, i) => c.innerHTML = start + i + 1);
    }
});
$('#fBatch').on('change', function () { dtI.column(3).search(this.value ? '^' + this.value + '$' : '', true, false).draw(); });
$('#fStatus').on('change', function () { dtI.column(4).search(this.value ? '^' + this.value + '$' : '', true, false).draw(); });

/* ---------- render ---------- */
let TOKEN_LIMIT = 0, TOKEN_MODE = 'total';

function renderJob(d) {
    const j = d.job;
    const busy = ['queued', 'running'].includes(j.status);
    TOKEN_LIMIT = Number(j.token_limit || 0);
    TOKEN_MODE  = j.token_mode === 'per_batch' ? 'per_batch' : 'total';

    $('#workerWarn').toggle(busy && !d.worker);
    $('#btnStop').prop('disabled', !busy);

    const m = JOB_MAP[j.status] || [j.status, 'default'];
    $('#jStatus').attr('class', 'label label-' + m[1]).text(m[0]);
    $('#jMsg').text(j.message || m[0]);
    $('#jModel').text(j.model || '-');
    $('#jBar').toggleClass('active', j.status === 'running');

    const total = Number(j.total || 0), done = Number(j.processed_count || 0);
    const pct = total ? Math.min(100, Math.round(done / total * 100)) : 0;
    $('#jBar').css('width', pct + '%');
    $('#jPct').text(`${num(done)} dari ${num(total)} kontak (${pct}%)`);

    if (total) $('#jBatch').show().text(`Batch ${num(j.current_batch)}/${num(j.total_batches)}`); else $('#jBatch').hide();
    if (Number(j.hour_limit) > 0 && j.status === 'running') $('#jHour').show().text(`Jam ini: ${num(j.hour_count)}/${num(j.hour_limit)} kontak`); else $('#jHour').hide();
    if (j.status === 'running' && j.waiting_next && j.next_batch_at)
        $('#jWait').show().text(`${WAIT_TXT[j.wait_reason] || 'Menunggu'} · lanjut ${String(j.next_batch_at).substring(11)}`);
    else $('#jWait').hide();
    if (j.deadline_at && j.status === 'running') $('#jDeadline').show().text('Batas: ' + j.deadline_at); else $('#jDeadline').hide();

    $('#jMode').attr('class', 'label label-' + (TOKEN_MODE === 'per_batch' ? 'success' : 'default'))
        .text(TOKEN_MODE === 'per_batch' ? 'Token: reset tiap batch' : 'Token: akumulatif');

    $('#jProc').text(num(done));
    $('#jFound').text(num(j.found_count));
    $('#jTok').text(num(j.tokens_used));
    $('#jSisa').text(num(j.token_sisa));
    const dl = Number(j.daily_limit || 0), du = Number(j.token_hari_ini || 0);
    $('#dailyLine').html(dl > 0
        ? `<i class="fa fa-calendar"></i> Token harian (semua proses): <b>${num(du)}</b> / ${num(dl)} &middot; sisa <b>${num(Math.max(0, dl - du))}</b> &middot; reset pukul 00.00`
        : `<i class="fa fa-calendar"></i> Token terpakai hari ini (semua proses): <b>${num(du)}</b> &middot; batas harian tidak aktif`);
    $('#jSisaLbl').text(TOKEN_MODE === 'per_batch' ? 'Sisa Token (batch ini)' : 'Sisa Token (total)');

    if (j.elapsed_seconds) {
        const s = Number(j.elapsed_seconds);
        $('#jElapsed').text(`Berjalan: ${Math.floor(s / 3600)}j ${Math.floor((s % 3600) / 60)}m`);
    } else $('#jElapsed').text('');

    $('#tokenNote').attr('class', 'callout ' + (TOKEN_MODE === 'per_batch' ? 'callout-success' : 'callout-info')).find('p').html(
        TOKEN_MODE === 'per_batch'
            ? `<b>Token direset tiap batch.</b> Setiap batch mulai lagi dengan jatah <b>${num(TOKEN_LIMIT)}</b> token. Jika jatah batch habis, sisa kontak di batch itu dilewati dan proses lanjut ke batch berikutnya.`
            : `<b>Token tidak direset.</b> Batas <b>${num(TOKEN_LIMIT)}</b> token berlaku untuk seluruh proses; sisa token terus berkurang dari batch ke batch dan proses berhenti saat habis.`
    );
}

function renderBatches(batches, current) {
    if (!batches.length) { $('#batchBody').html('<tr><td colspan="9" class="text-center text-muted">Belum ada batch.</td></tr>'); $('#batchFoot').empty(); return; }

    let sumFound = 0, sumTok = 0, sumProc = 0, sumSize = 0;
    const rows = batches.map(b => {
        sumFound += b.found_count; sumTok += b.tokens_used; sumProc += b.processed; sumSize += b.size;
        const m = BATCH_MAP[b.status] || [b.status, 'default'];
        const started = b.status !== 'pending';
        const awal = Number(b.token_awal), pakai = Number(b.tokens_used);
        const pctUsed = awal > 0 ? Math.min(100, Math.round(pakai / awal * 100)) : 0;
        return `<tr class="${b.batch_no === current ? 'is-current' : ''}">
            <td class="text-center"><b>${b.batch_no}</b></td>
            <td class="text-center">${num(b.processed)} / ${num(b.size)}</td>
            <td class="text-center">${num(b.found_count)}</td>
            <td class="text-right">${started ? num(awal) : '<span class="text-muted">-</span>'}</td>
            <td class="text-right">${started ? num(pakai) + `<div class="tok-bar"><span style="width:${pctUsed}%"></span></div>` : '<span class="text-muted">-</span>'}</td>
            <td class="text-right">${started ? num(b.token_sisa) : '<span class="text-muted">-</span>'}</td>
            <td class="text-center"><span class="label label-${m[1]}">${esc(m[0])}</span></td>
            <td>${dt(b.started_at)}</td><td>${dt(b.finished_at)}</td></tr>`;
    });
    $('#batchBody').html(rows.join(''));
    $('#batchFoot').html(`<tr style="font-weight:bold;background:#f9f9f9;">
        <td class="text-center">Total</td><td class="text-center">${num(sumProc)} / ${num(sumSize)}</td><td class="text-center">${num(sumFound)}</td>
        <td class="text-right">${TOKEN_MODE === 'per_batch' ? '<span class="text-muted">reset/batch</span>' : ''}</td><td class="text-right">${num(sumTok)}</td><td></td><td></td><td></td><td></td></tr>`);

    // isi pilihan batch sekali
    const sel = $('#fBatch');
    if (sel.find('option').length - 1 !== batches.length) {
        const cur = sel.val();
        sel.html('<option value="">Semua</option>' + batches.map(b => `<option value="${b.batch_no}">Batch ${b.batch_no}</option>`).join('')).val(cur);
    }
}

let focusDone = false;
function applyFocus(items) {
    if (!FOCUS_ID || focusDone) return;
    focusDone = true;
    const it = items.find(x => Number(x.id) === FOCUS_ID);
    if (!it) return;
    const name = it.nama_perusahaan || it.nama || '';
    dtI.search(name).draw();
    $('#focusNote').show().find('b').text(name);
    const el = document.getElementById('tblItems');
    if (el) setTimeout(() => el.scrollIntoView({ behavior:'smooth', block:'start' }), 300);
}
$('#btnShowAll').on('click', function (e) {
    e.preventDefault();
    dtI.search('').draw();
    $('#focusNote').hide();
});

function renderItems(items) {
    dtI.clear().rows.add(items).draw(false);
    applyFocus(items);
}

/* ---------- polling ---------- */
let sig = '', busyPoll = false, finalDone = false;

async function refresh(force) {
    if (busyPoll) return;
    busyPoll = true;
    try {
        let d = await api('/detail/' + JOB_ID);
        const j = d.job;
        const s = [j.status, j.processed_count, j.found_count, j.tokens_used, d.batches.map(b => b.status + b.processed).join(',')].join('|');
        if (force || s !== sig) {
            d = await api('/detail/' + JOB_ID + '?items=1');
            sig = s;
            renderItems(d.items || []);
        }
        renderJob(d);
        renderBatches(d.batches, Number(d.job.current_batch || 0));
        finalDone = !['queued', 'running'].includes(d.job.status);
    } catch (e) {
        $('#jMsg').text('Gagal memuat data: ' + e.message);
    } finally {
        busyPoll = false;
    }
}

$('#btnStop').on('click', async function () {
    const ok = await Swal.fire({ icon:'question', title:'Hentikan proses?', text:'Kontak yang sudah diproses tetap tersimpan.', showCancelButton:true, confirmButtonText:'Hentikan', cancelButtonText:'Batal' });
    if (!ok.isConfirmed) return;
    try { await api('/stop', {}); Toast.fire({ icon:'info', title:'Permintaan berhenti dikirim' }); refresh(true); }
    catch (e) { Swal.fire({ icon:'error', title:'Gagal', text:e.message }); }
});

$(function () {
    refresh(true);
    setInterval(() => { if (!finalDone || !sig) refresh(false); }, 3000);
});
</script>
<?= $this->endSection() ?>