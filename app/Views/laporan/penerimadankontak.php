<?= $this->extend('layouts/main') ?>

<?= $this->section('title') ?>Cari Email<?= $this->endSection() ?>
<?= $this->section('page_title') ?>Pencarian Email Kontak<?= $this->endSection() ?>
<?= $this->section('page_subtitle') ?>Lengkapi data email kontak secara otomatis dari website perusahaan<?= $this->endSection() ?>

<?= $this->section('breadcrumb') ?>
<li class="breadcrumb-item"><a href="<?= base_url() ?>"><i class="fas fa-home"></i> Home</a></li>
<li class="breadcrumb-item active">Cari Email</li>
<?= $this->endSection() ?>

<?= $this->section('styles') ?>
<!-- DataTables -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap4.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap4.min.css">
<!-- SweetAlert2 -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
<style>
    /* Custom Styling */
    .url-cell { max-width: 250px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; display: inline-block; vertical-align: bottom; }
    .note-cell { max-width: 300px; font-size: .85rem; line-height: 1.2; }
    #tblKontak td, #tblHasil td { vertical-align: middle; }
    .stat-label { font-size: .8rem; color: #6c757d; text-transform: uppercase; letter-spacing: 0.5px; }
    .stat-value { font-size: 1.4rem; font-weight: 700; color: #343a40; }
    .card-outline-tabs .card-header { border-bottom: 1px solid #dee2e6; }
    .table th { background-color: #f8f9fa; font-size: 0.85rem; text-transform: uppercase; letter-spacing: 0.5px; }
    .badge-soft-warning { background-color: #fff3cd; color: #856404; }
    .badge-soft-danger { background-color: #f8d7da; color: #721c24; }
    .progress-bar-animated { animation: progress-bar-stripes 1s linear infinite; }
    .bg-gradient-primary { background: linear-gradient(45deg, #007bff, #0056b3); }
    .bg-gradient-success { background: linear-gradient(45deg, #28a745, #1e7e34); }
    .bg-gradient-info { background: linear-gradient(45deg, #17a2b8, #117a8b); }
    .bg-gradient-warning { background: linear-gradient(45deg, #ffc107, #d39e00); }
    .bg-gradient-danger { background: linear-gradient(45deg, #dc3545, #bd2130); }
    
    /* Perbaikan tampilan Info Box agar sejajar */
    .info-box { box-shadow: 0 1px 3px rgba(0,0,0,.1); border-radius: 8px; min-height: 80px; }
    .info-box-icon { border-radius: 8px 0 0 8px; font-size: 1.8rem; width: 80px; line-height: 80px; }
    .info-box-content { padding: 10px 15px; line-height: 1.2; display: flex; flex-direction: column; justify-content: center; }
    .info-box-text { font-size: 0.85rem; text-transform: uppercase; font-weight: 600; color: #6c757d; }
    .info-box-number { font-size: 1.5rem; font-weight: 700; display: block; }
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<!-- Row 1: Info Boxes -->
<div class="row">
    <div class="col-12 col-sm-6 col-lg-3">
        <div class="info-box shadow-sm">
            <span class="info-box-icon bg-gradient-info elevation-1"><i class="fas fa-user-slash"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Kontak Tanpa Email</span>
                <span class="info-box-number" id="stTotal">0</span>
            </div>
        </div>
    </div>
    <div class="col-12 col-sm-6 col-lg-3">
        <div class="info-box shadow-sm">
            <span class="info-box-icon bg-gradient-primary elevation-1"><i class="fas fa-globe"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Punya Website Valid</span>
                <span class="info-box-number" id="stWeb">0</span>
            </div>
        </div>
    </div>
    <div class="col-12 col-sm-6 col-lg-3">
        <div class="info-box shadow-sm">
            <span class="info-box-icon bg-gradient-success elevation-1"><i class="fas fa-envelope-open-text"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Email Ditemukan</span>
                <span class="info-box-number" id="stFound">0</span>
            </div>
        </div>
    </div>
    <div class="col-12 col-sm-6 col-lg-3">
        <div class="info-box shadow-sm">
            <span class="info-box-icon bg-gradient-danger elevation-1"><i class="fas fa-exclamation-triangle"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Gagal / Kosong</span>
                <span class="info-box-number" id="stFail">0</span>
            </div>
        </div>
    </div>
</div>

<!-- Row 2: Pengaturan & Status -->
<div class="row">
    <!-- Panel Pengaturan -->
    <div class="col-lg-5">
        <div class="card card-warning card-outline shadow-sm">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-sliders-h mr-1"></i> Pengaturan Proses</h3>
                <div class="card-tools">
                    <span class="text-muted text-sm"><i class="fas fa-check text-success"></i> Tersimpan otomatis</span>
                </div>
            </div>
            <div class="card-body">
                <div class="form-group">
                    <label for="key">Gemini API Key</label>
                    <div class="input-group">
                        <input id="key" type="password" class="form-control" placeholder="AIza... (kosong = pakai .env)" autocomplete="off">
                        <div class="input-group-append">
                            <button class="btn btn-outline-secondary" type="button" id="btnEye" title="Tampilkan / sembunyikan"><i class="fas fa-eye"></i></button>
                        </div>
                    </div>
                    <small class="form-text text-muted">Buat gratis di <a href="https://aistudio.google.com/app/apikey" target="_blank" rel="noopener">Google AI Studio</a>.</small>
                </div>

                <div class="form-group">
                    <label for="model">Model Gemini</label>
                    <input id="model" class="form-control" list="modelList" autocomplete="off">
                    <datalist id="modelList">
                        <option value="gemini-2.0-flash-lite">
                        <option value="gemini-2.0-flash">
                        <option value="gemini-1.5-flash">
                    </datalist>
                    <div class="mt-2">
                        <button class="btn btn-sm btn-outline-primary" type="button" id="btnModels"><i class="fas fa-sync-alt"></i> Muat Model</button>
                        <button class="btn btn-sm btn-outline-success" type="button" id="btnTest"><i class="fas fa-plug"></i> Uji Koneksi</button>
                    </div>
                </div>

                <div class="row">
                    <div class="col-sm-4">
                        <div class="form-group">
                            <label for="rate">Kontak / menit</label>
                            <input id="rate" type="number" step="0.1" min="0.05" class="form-control">
                        </div>
                    </div>
                    <div class="col-sm-4">
                        <div class="form-group">
                            <label for="hours">Durasi maks (jam)</label>
                            <input id="hours" type="number" step="0.5" min="0.01" max="24" class="form-control">
                        </div>
                    </div>
                    <div class="col-sm-4">
                        <div class="form-group">
                            <label for="token_limit">Batas token</label>
                            <input id="token_limit" type="number" step="10000" min="1000" class="form-control">
                        </div>
                    </div>
                </div>

                <div class="custom-control custom-switch mb-3">
                    <input type="checkbox" class="custom-control-input" id="auto_fill">
                    <label class="custom-control-label" for="auto_fill">Isi kolom email kontak otomatis</label>
                    <small class="form-text text-muted d-block">Email terbaik (domain sama) disimpan ke kontak. Jika dimatikan, hasil hanya masuk tab <b>Hasil Email</b>.</small>
                </div>

                <div class="callout callout-info mb-0 py-2">
                    <small>Satu kontak = satu permintaan AI. Proses berhenti otomatis bila kuota, durasi, atau batas token habis.</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Panel Status -->
    <div class="col-lg-7">
        <div class="card card-primary card-outline shadow-sm h-100">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-tasks mr-1"></i> Status Proses</h3>
                <div class="card-tools">
                    <span id="stBadge" class="badge badge-secondary px-3 py-2">Siap</span>
                </div>
            </div>
            <div class="card-body d-flex flex-column">
                <p class="mb-2 font-weight-bold" id="stMsg">Memuat status...</p>
                
                <div class="progress mb-1" style="height:20px;">
                    <div id="stBar" class="progress-bar bg-primary progress-bar-striped progress-bar-animated" style="width:0%"></div>
                </div>
                <div class="d-flex justify-content-between text-sm text-muted mb-4">
                    <span id="stPct">0 dari 0 kontak</span>
                    <span id="stDeadline"></span>
                </div>

                <div class="row text-center mb-3 mt-auto">
                    <div class="col-4 border-right">
                        <div class="stat-value" id="stProc">0</div>
                        <div class="stat-label">Diproses</div>
                    </div>
                    <div class="col-4 border-right">
                        <div class="stat-value text-success" id="stNew">0</div>
                        <div class="stat-label">Email Baru</div>
                    </div>
                    <div class="col-4">
                        <div class="stat-value text-info" id="stTok">0</div>
                        <div class="stat-label">Token Terpakai</div>
                    </div>
                </div>
                
                <div class="progress mt-2" style="height:8px;">
                    <div id="stTokBar" class="progress-bar bg-info" style="width:0%"></div>
                </div>
                <div class="text-sm text-muted mt-1 text-truncate" id="stCurrent"></div>

                <div id="workerWarn" class="alert alert-warning mt-3 mb-0 d-none shadow-sm">
                    <i class="fas fa-exclamation-triangle"></i> Worker belum aktif! Buka terminal di folder proyek lalu jalankan: <br>
                    <code class="mt-1 d-inline-block">php spark emails:work</code>
                </div>
            </div>
            <div class="card-footer bg-white">
                <button id="btnStart" class="btn btn-success shadow-sm"><i class="fas fa-play"></i> Mulai Proses <span class="badge badge-light ml-1" id="selCount">0</span></button>
                <button id="btnStop" class="btn btn-danger shadow-sm" disabled><i class="fas fa-stop"></i> Hentikan</button>
                <span class="text-muted text-sm ml-2 d-none d-md-inline">Centang kontak di tab bawah, lalu klik Mulai.</span>
            </div>
        </div>
    </div>
</div>

<!-- Row 3: Tabel Data -->
<div class="card card-primary card-outline card-outline-tabs shadow-sm mt-3">
    <div class="card-header p-0 pt-1 border-bottom-0">
        <ul class="nav nav-tabs" id="custom-tabs-two-tab" role="tablist">
            <li class="nav-item">
                <a class="nav-link active font-weight-bold" data-toggle="pill" href="#paneKontak" role="tab">
                    <i class="fas fa-user-slash"></i> Kontak Tanpa Email <span class="badge badge-info ml-1" id="cntKontak">0</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link font-weight-bold" data-toggle="pill" href="#paneHasil" role="tab">
                    <i class="fas fa-envelope-open-text"></i> Hasil Email <span class="badge badge-success ml-1" id="cntHasil">0</span>
                </a>
            </li>
            <li class="nav-item ml-auto p-2 d-none d-md-block">
                <a class="btn btn-sm btn-default shadow-sm" href="<?= base_url('extractor/export/csv') ?>"><i class="fas fa-file-csv text-success"></i> Export CSV</a>
                <a class="btn btn-sm btn-default shadow-sm" href="<?= base_url('extractor/export/xlsx') ?>"><i class="fas fa-file-excel text-success"></i> Export Excel</a>
            </li>
        </ul>
    </div>
    <div class="card-body">
        <div class="tab-content" id="custom-tabs-two-tabContent">
            <!-- Tab Kontak Tanpa Email -->
            <div class="tab-pane fade show active" id="paneKontak" role="tabpanel">
                <div class="mb-3 d-flex justify-content-between align-items-center">
                    <div class="btn-group btn-group-sm shadow-sm">
                        <button class="btn btn-default" data-sel="all"><i class="fas fa-check-square"></i> Pilih Semua Berwebsite</button>
                        <button class="btn btn-default" data-sel="pending"><i class="fas fa-clock"></i> Belum Diproses</button>
                        <button class="btn btn-default" data-sel="retry"><i class="fas fa-redo"></i> Gagal / Kosong</button>
                        <button class="btn btn-default" data-sel="none"><i class="fas fa-square"></i> Kosongkan</button>
                    </div>
                    <span class="text-muted text-sm d-none d-lg-block">Hanya kontak dengan website valid yang bisa dipilih.</span>
                </div>
                <div class="table-responsive">
                    <table id="tblKontak" class="table table-bordered table-striped table-hover table-sm w-100">
                        <thead>
                            <tr>
                                <th style="width:30px" class="text-center"><input type="checkbox" id="chkAll" title="Pilih semua hasil pencarian"></th>
                                <th>Kontak / Perusahaan</th>
                                <th>Website</th>
                                <th class="text-center">Status</th>
                                <th class="text-center">Hasil</th>
                                <th>Catatan</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>

            <!-- Tab Hasil Email -->
            <div class="tab-pane fade" id="paneHasil" role="tabpanel">
                <div class="table-responsive">
                    <table id="tblHasil" class="table table-bordered table-striped table-hover table-sm w-100">
                        <thead>
                            <tr>
                                <th>Email</th>
                                <th>Keterangan</th>
                                <th>Kontak</th>
                                <th>Website</th>
                                <th class="text-center">Status</th>
                                <th class="text-center" style="width:80px">Aksi</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<!-- DataTables -->
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap4.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.5.0/js/responsive.bootstrap4.min.js"></script>
<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    // --- Base URL & Helper ---
    const API_BASE = '<?= base_url('extractor/api') ?>';
    const esc = (s) => String(s ?? '').replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
    const num = (n) => Number(n || 0).toLocaleString('id-ID');
    
    // Toast helper
    const Toast = Swal.mixin({
        toast: true, position: 'top-end', showConfirmButton: false, timer: 3000,
        timerProgressBar: true, didOpen: (toast) => { toast.addEventListener('mouseenter', Swal.stopTimer); toast.addEventListener('mouseleave', Swal.resumeTimer); }
    });

    const XT = {
        async api(path, body) {
            const isPost = body !== undefined;
            const res = await fetch(API_BASE + path, {
                method: isPost ? 'POST' : 'GET',
                headers: isPost ? { 'Content-Type': 'application/json' } : {},
                body: isPost ? JSON.stringify(body) : undefined
            });
            const data = await res.json().catch(() => ({}));
            if (!res.ok) throw new Error(data.error || ('HTTP ' + res.status));
            return data;
        },
        async run(fn) {
            try { return await fn(); }
            catch (e) { Swal.fire('Error', e.message, 'error'); }
        },
        setWorker(ok) {
            $('#workerWarn').toggleClass('d-none', ok);
        }
    };

    // --- Pengaturan (LocalStorage) ---
    const DEF = { key: '', model: 'gemini-2.0-flash-lite', rate: '5', hours: '24', token_limit: '1000000', auto_fill: '1' };
    const FIELDS = Object.keys(DEF);

    function store(k, v) {
        try {
            if (v === undefined) return localStorage.getItem('ext_' + k);
            localStorage.setItem('ext_' + k, v);
        } catch (e) { return null; }
    }
    function cfg() {
        const o = {};
        FIELDS.forEach(k => {
            if (k === 'auto_fill') o[k] = $('#auto_fill').is(':checked') ? '1' : '0';
            else o[k] = ($('#' + k).val() || '').trim();
        });
        return o;
    }
    FIELDS.forEach(k => {
        const v = store(k) ?? DEF[k];
        if (k === 'auto_fill') $('#auto_fill').prop('checked', v === '1');
        else $('#' + k).val(v);
    });
    $('#key,#model,#rate,#hours,#token_limit,#auto_fill').on('input change', function() {
        const c = cfg(); FIELDS.forEach(k => store(k, c[k]));
    });
    $('#btnEye').on('click', function() {
        const i = $('#key'), show = i.attr('type') === 'password';
        i.attr('type', show ? 'text' : 'password');
        $(this).find('i').toggleClass('fa-eye', !show).toggleClass('fa-eye-slash', show);
    });

    // --- API Actions ---
    $('#btnModels').on('click', () => XT.run(async () => {
        const r = await XT.api('/models', { key: cfg().key });
        $('#modelList').html(r.models.map(m => `<option value="${esc(m.id)}">${esc(m.name)}</option>`).join(''));
        Toast.fire({ icon: 'success', title: r.models.length + ' model tersedia.' });
    }));

    $('#btnTest').on('click', () => XT.run(async () => {
        const c = cfg();
        Swal.fire({ title: 'Menguji koneksi...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });
        const r = await XT.api('/test', { key: c.key, model: c.model });
        Swal.fire('Koneksi Berhasil', `Model ${c.model || '(default)'} merespons (${r.tokens} token).`, 'success');
    }));

    // --- DataTables Setup ---
    const ST = {
        pending:    ['Belum diproses', 'secondary'],
        processing: ['Diproses', 'warning'],
        done:       ['Selesai', 'success'],
        empty:      ['Tidak ada email', 'warning'],
        error:      ['Gagal', 'danger']
    };
    const badge = (s) => {
        const m = ST[s] || [s, 'secondary'];
        return `<span class="badge badge-${m[1]}">${esc(m[0])}</span>`;
    };
    const LANG = {
        search: 'Cari:', lengthMenu: 'Tampilkan _MENU_', info: '_START_–_END_ dari _TOTAL_',
        infoEmpty: '0 data', infoFiltered: '(disaring dari _MAX_)', zeroRecords: 'Tidak ada data',
        paginate: { previous: '‹', next: '›' }
    };
    
    let kontak = [], hasil = [], checked = new Set();

    const dtK = $('#tblKontak').DataTable({
        data: [], deferRender: true, autoWidth: false, order: [], pageLength: 10,
        lengthMenu: [10, 25, 50, 100],
        language: { ...LANG, emptyTable: 'Semua kontak sudah memiliki email.' },
        columns: [
            {
                data: null, orderable: false, className: 'text-center',
                render: (d, t, r) => t === 'display'
                    ? `<input type="checkbox" class="chk" data-id="${r.id}" ${checked.has(r.id) ? 'checked' : ''} ${r.has_web ? '' : 'disabled'}>`
                    : ''
            },
            {
                data: 'nama_perusahaan',
                render: (d, t, r) => {
                    const main = d || r.nama || '(tanpa nama)';
                    if (t !== 'display') return main + ' ' + (r.nama || '');
                    const sub = [d && r.nama ? r.nama : '', r.kota_kabupaten || ''].filter(Boolean).join(' · ');
                    return `<div class="font-weight-bold text-dark">${esc(main)}</div>` + (sub ? `<small class="text-muted">${esc(sub)}</small>` : '');
                }
            },
            {
                data: 'website',
                render: (d, t, r) => {
                    if (t !== 'display') return d || '';
                    if (!d) return '<span class="text-muted font-italic">—</span>';
                    if (!r.has_web) return `<span class="url-cell text-muted" title="${esc(d)}"><i class="fas fa-times-circle text-danger"></i> ${esc(d)}</span>`;
                    return `<a class="url-cell text-primary" href="${esc(r.url)}" target="_blank" rel="noopener" title="${esc(r.url)}"><i class="fas fa-external-link-alt"></i> ${esc(d)}</a>`;
                }
            },
            {
                data: 'status', className: 'text-center',
                render: (d, t, r) => t === 'display'
                    ? (r.has_web || d !== 'pending' ? badge(d) : '<span class="badge badge-light">Tanpa web</span>')
                    : d
            },
            { data: 'n', className: 'text-center font-weight-bold text-success' },
            {
                data: 'note', className: 'note-cell',
                render: (d, t) => t === 'display' ? `<span title="${esc(d)}">${esc(d || '')}</span>` : (d || '')
            }
        ]
    });

    const dtH = $('#tblHasil').DataTable({
        data: [], deferRender: true, autoWidth: false, order: [], pageLength: 10,
        lengthMenu: [10, 25, 50, 100],
        language: { ...LANG, emptyTable: 'Belum ada email yang ditemukan.' },
        columns: [
            { data: 'email', render: (d, t) => t === 'display' ? `<i class="fas fa-envelope text-primary mr-1"></i> <b>${esc(d)}</b>` : d },
            { data: 'label', render: (d, t) => t === 'display' ? esc(d || '') : (d || '') },
            { data: 'nama_perusahaan', render: (d, t, r) => esc(d || r.nama || '') },
            { data: 'website', render: (d, t) => t === 'display' ? `<span class="url-cell text-muted" title="${esc(d || '')}">${esc(d || '')}</span>` : (d || '') },
            {
                data: 'dipakai', className: 'text-center',
                render: (d, t) => t === 'display'
                    ? (Number(d) ? '<span class="badge badge-success"><i class="fas fa-check"></i> Dipakai</span>' : '<span class="badge badge-light">Alternatif</span>')
                    : (Number(d) || 0)
            },
            {
                data: null, orderable: false, className: 'text-center',
                render: (d, t, r) => t === 'display'
                    ? (Number(r.dipakai) ? '' : `<button class="btn btn-xs btn-outline-primary btn-pakai" data-id="${r.id}">Pakai</button>`)
                    : ''
            }
        ]
    });

    $('a[data-toggle="pill"]').on('shown.bs.tab', () => { dtK.columns.adjust(); dtH.columns.adjust(); });

    // --- Selection Logic ---
    function updateSel() { $('#selCount').text(checked.size); }
    function redrawK() { dtK.rows().invalidate('data').draw(false); updateSel(); }
    function selectBy(fn) {
        checked = new Set(kontak.filter(r => r.has_web && fn(r)).map(r => r.id));
        redrawK();
    }
    $('[data-sel]').on('click', function() {
        const m = this.dataset.sel;
        if (m === 'none') selectBy(() => false);
        else if (m === 'all') selectBy(() => true);
        else if (m === 'pending') selectBy(r => r.status === 'pending');
        else selectBy(r => r.status === 'error' || r.status === 'empty');
    });
    $('#chkAll').on('change', function() {
        const on = this.checked;
        dtK.rows({ search: 'applied' }).data().toArray().forEach(r => {
            if (r.has_web) on ? checked.add(r.id) : checked.delete(r.id);
        });
        redrawK();
    });
    $('#tblKontak tbody').on('change', '.chk', function() {
        const id = Number(this.dataset.id);
        this.checked ? checked.add(id) : checked.delete(id);
        updateSel();
    });

    // --- Actions ---
    $('#tblHasil tbody').on('click', '.btn-pakai', function() {
        const id = Number(this.dataset.id), row = hasil.find(r => r.id === id);
        XT.run(async () => {
            const ok = await Swal.fire({
                title: 'Jadikan email kontak?',
                text: `${row.email} akan disimpan sebagai email ${row.nama_perusahaan || row.nama || 'kontak'}.`,
                icon: 'question', showCancelButton: true,
                confirmButtonText: 'Ya, Pakai', cancelButtonText: 'Batal',
                confirmButtonColor: '#007bff'
            });
            if (!ok.isConfirmed) return;
            await XT.api('/pakai', { id });
            Toast.fire({ icon: 'success', title: 'Email kontak diperbarui' });
            await load();
        });
    });

    async function load() {
        const [k, h] = await Promise.all([XT.api('/kontak'), XT.api('/hasil')]);
        kontak = k.map(r => ({ ...r, id: Number(r.id), n: Number(r.n), has_web: Number(r.has_web) === 1 }));
        hasil  = h.map(r => ({ ...r, id: Number(r.id), kontak_id: Number(r.kontak_id), dipakai: Number(r.dipakai) === 1 }));

        const ids = new Set(kontak.map(r => r.id));
        [...checked].forEach(i => { if (!ids.has(i)) checked.delete(i); });

        dtK.clear().rows.add(kontak).draw(false);
        dtH.clear().rows.add(hasil).draw(false);
        updateSel();

        $('#stTotal').text(num(kontak.length));
        $('#stWeb').text(num(kontak.filter(r => r.has_web).length));
        $('#stFound').text(num(hasil.length));
        $('#stFail').text(num(kontak.filter(r => r.status === 'error' || r.status === 'empty').length));
        $('#cntKontak').text(kontak.length);
        $('#cntHasil').text(hasil.length);
    }

    // --- Start / Stop Process ---
    async function doStart() {
        if (!checked.size) return Swal.fire('Pilih Kontak', 'Centang minimal satu kontak yang punya website.', 'warning');
        
        const c = cfg(), rate = parseFloat(c.rate), hours = parseFloat(c.hours), tl = parseInt(c.token_limit, 10);
        if (!(rate > 0)) return Swal.fire('Periksa Pengaturan', 'Kontak per menit harus > 0.', 'warning');
        if (!(hours > 0 && hours <= 24)) return Swal.fire('Periksa Pengaturan', 'Durasi 0.01 - 24 jam.', 'warning');
        if (!(tl >= 1000)) return Swal.fire('Periksa Pengaturan', 'Batas token minimal 1.000.', 'warning');

        const r = await XT.api('/start', {
            ids: [...checked], key: c.key, model: c.model, rate, hours,
            token_limit: tl, auto_fill: c.auto_fill === '1' ? 1 : 0
        });
        Toast.fire({ icon: 'success', title: r.queued + ' kontak masuk antrean' });
        if (r.skipped) Toast.fire({ icon: 'warning', title: `${r.skipped} kontak dilewati (sudah ada email / web invalid)` });
        if (!r.ai) Toast.fire({ icon: 'info', title: 'API key kosong: hanya regex.' });
        
        await poll();
    }
    $('#btnStart').on('click', () => XT.run(doStart));
    $('#btnStop').on('click', () => XT.run(async () => {
        await XT.api('/stop', {});
        await poll();
    }));

    // --- Status Polling ---
    const JOB = {
        queued:   ['Menunggu worker', 'warning'],
        running:  ['Berjalan', 'primary'],
        finished: ['Selesai', 'success'],
        stopped:  ['Dihentikan', 'secondary'],
        expired:  ['Waktu habis', 'warning'],
        quota:    ['Berhenti: kuota / limit', 'danger']
    };
    let polling = false, lastSig = '';

    async function poll() {
        if (polling) return;
        polling = true;
        try {
            const s = await XT.api('/status'), j = s.job;
            XT.setWorker(s.worker);

            const busy = !!j && ['queued', 'running'].includes(j.status);
            $('#btnStart').prop('disabled', busy);
            $('#btnStop').prop('disabled', !busy);

            if (!j) {
                $('#stMsg').text('Siap. Pilih kontak lalu klik Mulai Proses.');
                $('#stBadge').attr('class', 'badge badge-secondary px-3 py-2').text('Siap');
            } else {
                const m = JOB[j.status] || [j.status, 'secondary'];
                $('#stBadge').attr('class', 'badge badge-' + m[1] + ' px-3 py-2').text(m[0]);
                $('#stMsg').text(j.message || '');
                
                const total = Number(j.total || 0), done = Number(j.processed_count || 0);
                const pct = total ? Math.min(100, Math.round(done / total * 100)) : 0;
                $('#stBar').css('width', pct + '%').toggleClass('progress-bar-animated', j.status === 'running');
                $('#stPct').text(`${num(done)} dari ${num(total)} kontak (${pct}%)`);
                $('#stDeadline').text(j.status === 'running' && j.deadline_at ? 'Batas: ' + j.deadline_at : '');
                
                $('#stProc').text(num(done));
                $('#stNew').text(num(j.found_count));
                $('#stTok').text(num(j.tokens_used));
                
                const tp = Number(j.token_limit) ? Math.min(100, Number(j.tokens_used) / Number(j.token_limit) * 100) : 0;
                $('#stTokBar').css('width', tp + '%');
                $('#stCurrent').text(j.status === 'running' && j.current_url ? 'Sedang memproses: ' + j.current_url : '');
            }

            const sig = j ? [j.id, j.status, j.processed_count, j.found_count].join(':') : 'none';
            if (sig !== lastSig) { lastSig = sig; await load(); }
        } catch (e) {
            $('#stMsg').text('Gagal memuat: ' + e.message);
        } finally {
            polling = false;
        }
    }
    
    // Mulai Polling
    poll();
    setInterval(poll, 3000);
</script>
<?= $this->endSection() ?>