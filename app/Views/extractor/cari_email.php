<?= $this->extend('layouts/main') ?>

<?= $this->section('title') ?>Cari Email<?= $this->endSection() ?>
<?= $this->section('page_title') ?>Pencarian Email Kontak<?= $this->endSection() ?>
<?= $this->section('page_subtitle') ?>Lengkapi data email kontak secara otomatis dari website perusahaan<?= $this->endSection() ?>

<?= $this->section('breadcrumb') ?>
<li><a href="<?= base_url() ?>"><i class="fa fa-dashboard"></i> Home</a></li>
<li class="active">Cari Email</li>
<?= $this->endSection() ?>

<?= $this->section('styles') ?>
<!-- DataTables -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap.min.css">
<!-- SweetAlert2 -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
<style>
    /* ========== KARTU RINGKASAN (Sama seperti Laporan) ========== */
    .km-cards {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 12px;
        margin-bottom: 15px;
    }
    @media (max-width: 1199px) { .km-cards { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
    @media (max-width: 479px) { .km-cards { grid-template-columns: 1fr; } }

    .km-card {
        display: flex; align-items: center; background: #fff;
        border-radius: 8px; border: 1px solid #e9ecef;
        box-shadow: 0 1px 3px rgba(0,0,0,.06); padding: 12px 14px;
        min-height: 84px; overflow: hidden; transition: all .2s ease;
    }
    .km-card:hover { transform: translateY(-2px); box-shadow: 0 6px 18px rgba(0,0,0,.08); }
    .km-card-icon {
        flex-shrink: 0; width: 46px; height: 46px; border-radius: 10px;
        display: flex; align-items: center; justify-content: center;
        color: #fff; font-size: 20px; margin-right: 12px;
    }
    .km-card-body { min-width: 0; flex: 1; overflow: hidden; }
    .km-card-label {
        font-size: 11px; text-transform: uppercase; letter-spacing: .4px;
        color: #6c757d; font-weight: 600; white-space: nowrap;
        overflow: hidden; text-overflow: ellipsis; display: block; margin-bottom: 2px;
    }
    .km-card-value { font-size: 22px; font-weight: 700; color: #212529; line-height: 1.2; }
    .km-card-sub { font-size: 11px; color: #6c757d; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; margin-top: 2px; }
    
    .km-card.aqua   .km-card-icon { background: linear-gradient(135deg,#00c0ef,#4dd0e1); }
    .km-card.blue   .km-card-icon { background: linear-gradient(135deg,#3c8dbc,#5fa8dc); }
    .km-card.green  .km-card-icon { background: linear-gradient(135deg,#00a65a,#4caf50); }
    .km-card.red    .km-card-icon { background: linear-gradient(135deg,#dd4b39,#e57373); }

    /* ========== STYLING LAINNYA ========== */
    .url-cell { max-width: 250px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; display: inline-block; vertical-align: middle; }
    .note-cell { max-width: 300px; font-size: 12px; line-height: 1.3; color: #555; }
    #tblKontak td, #tblHasil td { vertical-align: middle !important; }
    
    .stat-label { font-size: 11px; color: #777; text-transform: uppercase; font-weight: 600; letter-spacing: 0.5px; }
    .stat-value { font-size: 22px; font-weight: 700; color: #333; margin-bottom: 2px; }
    
    .progress-bar-striped { background-image: linear-gradient(45deg,rgba(255,255,255,.15) 25%,transparent 25%,transparent 50%,rgba(255,255,255,.15) 50%,rgba(255,255,255,.15) 75%,transparent 75%,transparent); background-size: 40px 40px; }
    .active-progress { animation: progress-bar-stripes 2s linear infinite; }
    
    .box-header.with-border { border-bottom: 1px solid #f4f4f4; }
    .box-title { font-weight: 600; font-size: 15px; }
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<!-- Row 1: KARTU RINGKASAN (Custom Grid) -->
<div class="km-cards">
    <div class="km-card aqua">
        <div class="km-card-icon"><i class="fa fa-user-times"></i></div>
        <div class="km-card-body">
            <span class="km-card-label">Kontak Tanpa Email</span>
            <div class="km-card-value" id="stTotal">0</div>
            <div class="km-card-sub">Total data</div>
        </div>
    </div>
    <div class="km-card blue">
        <div class="km-card-icon"><i class="fa fa-globe"></i></div>
        <div class="km-card-body">
            <span class="km-card-label">Website Valid</span>
            <div class="km-card-value" id="stWeb">0</div>
            <div class="km-card-sub">Siap diproses</div>
        </div>
    </div>
    <div class="km-card green">
        <div class="km-card-icon"><i class="fa fa-envelope-open-o"></i></div>
        <div class="km-card-body">
            <span class="km-card-label">Email Ditemukan</span>
            <div class="km-card-value" id="stFound">0</div>
            <div class="km-card-sub">Berhasil diekstrak</div>
        </div>
    </div>
    <div class="km-card red">
        <div class="km-card-icon"><i class="fa fa-exclamation-triangle"></i></div>
        <div class="km-card-body">
            <span class="km-card-label">Gagal / Kosong</span>
            <div class="km-card-value" id="stFail">0</div>
            <div class="km-card-sub">Perlu dicek ulang</div>
        </div>
    </div>
</div>

<!-- Row 2: Pengaturan & Status -->
<div class="row">
    <!-- Panel Pengaturan -->
    <div class="col-md-5">
        <div class="box box-warning">
            <div class="box-header with-border">
                <h3 class="box-title"><i class="fa fa-sliders text-yellow"></i> Pengaturan Proses</h3>
                <div class="box-tools pull-right">
                    <span class="text-muted" style="font-size:12px;"><i class="fa fa-check text-success"></i> Tersimpan otomatis</span>
                </div>
            </div>
            <div class="box-body">
                <div class="form-group">
                    <label for="key">Gemini API Key</label>
                    <div class="input-group">
                        <input id="key" type="password" class="form-control" placeholder="AIza... (kosong = pakai .env)" autocomplete="off">
                        <span class="input-group-btn">
                            <button class="btn btn-default" type="button" id="btnEye" title="Tampilkan / sembunyikan"><i class="fa fa-eye"></i></button>
                        </span>
                    </div>
                    <span class="help-block" style="font-size:12px;">Dapatkan API Key di <a href="https://aistudio.google.com/app/apikey" target="_blank">Google AI Studio</a>.</span>
                </div>

                <div class="form-group">
                    <label for="model">Model Gemini</label>
                    <input id="model" class="form-control" list="modelList" autocomplete="off">
                    <datalist id="modelList">
                        <option value="gemini-3.5-flash-lite">
                        <option value="gemini-3.8-flash">
                        <option value="gemini-3.1-flash-lite">
                    </datalist>
                    <div style="margin-top: 8px;">
                        <button class="btn btn-sm btn-default text-blue" type="button" id="btnModels"><i class="fa fa-refresh"></i> Muat Model</button>
                        <button class="btn btn-sm btn-default text-green" type="button" id="btnTest"><i class="fa fa-plug"></i> Uji Koneksi</button>
                    </div>
                </div>

                <div class="row">
                    <div class="col-sm-6">
                        <div class="form-group">
                            <label for="rate">Kontak / menit</label>
                            <input id="rate" type="number" step="0.1" min="0.05" class="form-control">
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="form-group">
                            <label for="hours">Durasi (jam)</label>
                            <input id="hours" type="number" step="0.5" min="0.01" max="24" class="form-control">
                        </div>
                    </div>
                </div>

                <!-- TOKEN READ-ONLY -->
                <div class="form-group">
                    <label for="token_sisa">Sisa Kuota Token (Read-Only)</label>
                    <div class="input-group">
                        <span class="input-group-addon"><i class="fa fa-key"></i></span>
                        <input id="token_sisa" type="text" class="form-control" value="0" readonly style="background-color:#f9f9f9; font-weight:bold; color:#d9534f;">
                        <span class="input-group-addon">Token</span>
                    </div>
                    <span class="help-block" style="font-size:12px;">Batas maksimal 1.000.000 token per proses.</span>
                </div>

                <div class="checkbox" style="margin-top:0; margin-bottom:15px;">
                    <label>
                        <input type="checkbox" id="auto_fill"> <b>Isi email otomatis</b>
                    </label>
                    <p class="help-block" style="font-size:12px; margin-bottom:0;">Email terbaik (domain sama) disimpan ke kontak. Jika dimatikan, hasil hanya masuk tab <b>Hasil Email</b>.</p>
                </div>

                <div class="callout callout-info" style="margin-bottom: 0; padding: 10px;">
                    <p style="font-size: 12px; margin:0;">Satu kontak = satu permintaan AI. Proses berhenti otomatis bila kuota, durasi, atau batas token habis.</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Panel Status -->
    <div class="col-md-7">
        <div class="box box-primary">
            <div class="box-header with-border">
                <h3 class="box-title"><i class="fa fa-tasks text-blue"></i> Status Proses</h3>
                <div class="box-tools pull-right">
                    <span id="stBadge" class="label label-default" style="font-size:12px; padding: 5px 10px;">Siap</span>
                </div>
            </div>
            <div class="box-body">
                <p class="font-weight-bold" id="stMsg" style="margin-bottom:15px; font-size:14px; color:#333;">Memuat status...</p>
                
                <div class="progress" style="height:20px; margin-bottom:5px; border-radius:4px;">
                    <div id="stBar" class="progress-bar progress-bar-primary progress-bar-striped active-progress" role="progressbar" style="width:0%; border-radius:4px;"></div>
                </div>
                <div class="clearfix" style="font-size:12px; color:#777; margin-bottom:25px;">
                    <span class="pull-left" id="stPct">0 dari 0 kontak</span>
                    <span class="pull-right" id="stDeadline"></span>
                </div>

                <div class="row text-center" style="margin-bottom:20px;">
                    <div class="col-xs-4" style="border-right:1px solid #f4f4f4;">
                        <div class="stat-value" id="stProc">0</div>
                        <div class="stat-label">Diproses</div>
                    </div>
                    <div class="col-xs-4" style="border-right:1px solid #f4f4f4;">
                        <div class="stat-value text-green" id="stNew">0</div>
                        <div class="stat-label">Email Baru</div>
                    </div>
                    <div class="col-xs-4">
                        <div class="stat-value text-aqua" id="stTok">0</div>
                        <div class="stat-label">Token Terpakai</div>
                    </div>
                </div>
                
                <div class="progress progress-xs" style="margin-bottom:5px; border-radius:2px;">
                    <div id="stTokBar" class="progress-bar progress-bar-aqua" style="width:0%; border-radius:2px;"></div>
                </div>
                <div class="text-muted text-truncate" style="font-size:12px;" id="stCurrent"></div>

                <div id="workerWarn" class="alert alert-warning" style="margin-top:25px; margin-bottom:0; display:none; border-radius:4px;">
                    <h4 style="margin-top:0;"><i class="icon fa fa-warning"></i> Worker Belum Aktif!</h4>
                    <p style="margin:0; font-size:13px;">Buka terminal/CMD di folder proyek, lalu jalankan perintah: <br><code>php spark emails:work</code></p>
                </div>
            </div>
            <div class="box-footer">
                <button id="btnStart" class="btn btn-success"><i class="fa fa-play"></i> Mulai Proses <span class="badge bg-light text-dark ml-1" id="selCount">0</span></button>
                <button id="btnStop" class="btn btn-danger" disabled><i class="fa fa-stop"></i> Hentikan</button>
                <span class="text-muted" style="font-size:12px; margin-left:10px;">Centang kontak di tab bawah, lalu klik Mulai.</span>
            </div>
        </div>
    </div>
</div>

<!-- Row 3: Tabel Data -->
<div class="nav-tabs-custom">
    <ul class="nav nav-tabs">
        <li class="active">
            <a href="#paneKontak" data-toggle="tab" style="font-weight:bold;"><i class="fa fa-user-times text-teal"></i> Kontak Tanpa Email <span class="badge bg-teal" id="cntKontak">0</span></a>
        </li>
        <li>
            <a href="#paneHasil" data-toggle="tab" style="font-weight:bold;"><i class="fa fa-envelope-open-o text-green"></i> Hasil Email <span class="badge bg-green" id="cntHasil">0</span></a>
        </li>
        <li class="pull-right" style="padding: 8px 10px;">
            <a class="btn btn-sm btn-default" href="<?= base_url('extractor/export/csv') ?>"><i class="fa fa-file-text-o text-green"></i> CSV</a>
            <a class="btn btn-sm btn-default" href="<?= base_url('extractor/export/xlsx') ?>"><i class="fa fa-file-excel-o text-green"></i> Excel</a>
        </li>
    </ul>
    
    <div class="tab-content">
        <!-- Tab Kontak Tanpa Email -->
        <div class="tab-pane active" id="paneKontak">
            <div class="box-tools clearfix" style="margin-bottom:15px;">
                <div class="btn-group">
                    <button class="btn btn-default btn-sm" data-sel="all"><i class="fa fa-check-square-o"></i> Pilih Semua Berwebsite</button>
                    <button class="btn btn-default btn-sm" data-sel="pending"><i class="fa fa-clock-o"></i> Belum Diproses</button>
                    <button class="btn btn-default btn-sm" data-sel="retry"><i class="fa fa-refresh"></i> Gagal / Kosong</button>
                    <button class="btn btn-default btn-sm" data-sel="none"><i class="fa fa-square-o"></i> Kosongkan</button>
                </div>
                <span class="text-muted pull-right" style="font-size:12px; margin-top:8px;">Hanya kontak dengan website valid yang bisa dipilih.</span>
            </div>
            <div class="table-responsive">
                <table id="tblKontak" class="table table-bordered table-striped table-hover" style="width:100%">
                    <thead>
                        <tr>
                            <th width="30" class="text-center"><input type="checkbox" id="chkAll"></th>
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
        <div class="tab-pane" id="paneHasil">
            <div class="table-responsive">
                <table id="tblHasil" class="table table-bordered table-striped table-hover" style="width:100%">
                    <thead>
                        <tr>
                            <th>Email</th>
                            <th>Keterangan</th>
                            <th>Kontak</th>
                            <th>Website</th>
                            <th class="text-center">Status</th>
                            <th class="text-center" width="80">Aksi</th>
                        </tr>
                    </thead>
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
    const esc = (s) => String(s ?? '').replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
    const num = (n) => Number(n || 0).toLocaleString('id-ID');
    
    const Toast = Swal.mixin({
        toast: true, position: 'top-end', showConfirmButton: false, timer: 3000,
        timerProgressBar: true
    });

    const XT = {
        async api(path, body) {
            const isPost = body !== undefined;
            const res = await fetch(API_BASE + path, {
                method: isPost ? 'POST' : 'GET',
                headers: isPost ? { 'Content-Type': 'application/json' } : {},
                body: isPost ? JSON.stringify(body) : undefined
            });

            if (!res.ok) {
                let errorMsg = 'HTTP ' + res.status;
                try {
                    const data = await res.json();
                    errorMsg = data.error || errorMsg;
                } catch (e) {
                    const text = await res.text();
                    errorMsg += ': ' + text.substring(0, 200);
                }
                throw new Error(errorMsg);
            }
            return res.json();
        },
        async run(fn) {
            try { return await fn(); }
            catch (e) { 
                Swal.fire({
                    icon: 'error',
                    title: 'Terjadi Kesalahan',
                    html: `<div style="text-align:left; font-size:14px; word-wrap:break-word;">${esc(e.message)}</div>`
                }); 
            }
        },
        setWorker(ok) {
            $('#workerWarn').toggle(!ok);
        }
    };

    // Default Model & Config
    const DEF = { key: '', model: 'gemini-3.5-flash-lite', rate: '5', hours: '24', auto_fill: '1' };
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
    $('#key,#model,#rate,#hours,#auto_fill').on('input change', function() {
        const c = cfg(); FIELDS.forEach(k => store(k, c[k]));
    });
    $('#btnEye').on('click', function() {
        const i = $('#key'), show = i.attr('type') === 'password';
        i.attr('type', show ? 'text' : 'password');
        $(this).find('i').toggleClass('fa-eye', !show).toggleClass('fa-eye-slash', show);
    });

    $('#btnModels').on('click', () => XT.run(async () => {
        const r = await XT.api('/models', { key: cfg().key });
        $('#modelList').html(r.models.map(m => `<option value="${esc(m.id)}">${esc(m.name)}</option>`).join(''));
        Toast.fire({ icon: 'success', title: r.models.length + ' model tersedia.' });
    }));

    $('#btnTest').on('click', () => XT.run(async () => {
        const c = cfg();
        Swal.fire({ title: 'Menguji koneksi...', allowOutsideClick: false, onBeforeOpen: () => Swal.showLoading() });
        const r = await XT.api('/test', { key: c.key, model: c.model });
        Swal.fire('Koneksi Berhasil', `Model ${r.model || c.model || '(default)'} merespons.`, 'success');
    }));

    const ST = {
        pending:    ['Belum diproses', 'default'],
        processing: ['Diproses', 'warning'],
        done:       ['Selesai', 'success'],
        empty:      ['Tidak ada email', 'warning'],
        error:      ['Gagal', 'danger']
    };
    const badge = (s) => {
        const m = ST[s] || [s, 'default'];
        return `<span class="label label-${m[1]}">${esc(m[0])}</span>`;
    };
    const LANG = {
        search: 'Cari:', lengthMenu: 'Tampilkan _MENU_', info: '_START_–_END_ dari _TOTAL_',
        infoEmpty: '0 data', infoFiltered: '(disaring dari _MAX_)', zeroRecords: 'Tidak ada data',
        paginate: { previous: '‹', next: '›' }
    };
    
    let kontak = [], hasil = [], checked = new Set();

    const dtK = $('#tblKontak').DataTable({
        data: [], deferRender: true, autoWidth: false, order: [], pageLength: 10,
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
                    return `<strong>${esc(main)}</strong>` + (sub ? `<br><small class="text-muted">${esc(sub)}</small>` : '');
                }
            },
            {
                data: 'website',
                render: (d, t, r) => {
                    if (t !== 'display') return d || '';
                    if (!d) return '<span class="text-muted"><i>—</i></span>';
                    if (!r.has_web) return `<span class="url-cell text-muted" title="${esc(d)}"><i class="fa fa-times-circle text-red"></i> ${esc(d)}</span>`;
                    return `<a class="url-cell" href="${esc(r.url)}" target="_blank" rel="noopener" title="${esc(r.url)}"><i class="fa fa-external-link"></i> ${esc(d)}</a>`;
                }
            },
            {
                data: 'status', className: 'text-center',
                render: (d, t, r) => t === 'display'
                    ? (r.has_web || d !== 'pending' ? badge(d) : '<span class="label label-default">Tanpa web</span>')
                    : d
            },
            { data: 'n', className: 'text-center text-green' },
            {
                data: 'note', className: 'note-cell',
                render: (d, t) => t === 'display' ? `<span title="${esc(d)}">${esc(d || '')}</span>` : (d || '')
            }
        ]
    });

    const dtH = $('#tblHasil').DataTable({
        data: [], deferRender: true, autoWidth: false, order: [], pageLength: 10,
        language: { ...LANG, emptyTable: 'Belum ada email yang ditemukan.' },
        columns: [
            { data: 'email', render: (d, t) => t === 'display' ? `<i class="fa fa-envelope text-aqua"></i> <b>${esc(d)}</b>` : d },
            { data: 'label', render: (d, t) => t === 'display' ? esc(d || '') : (d || '') },
            { data: 'nama_perusahaan', render: (d, t, r) => esc(d || r.nama || '') },
            { data: 'website', render: (d, t) => t === 'display' ? `<span class="url-cell text-muted">${esc(d || '')}</span>` : (d || '') },
            {
                data: 'dipakai', className: 'text-center',
                render: (d, t) => t === 'display'
                    ? (Number(d) ? '<span class="label label-success"><i class="fa fa-check"></i> Dipakai</span>' : '<span class="label label-default">Alternatif</span>')
                    : (Number(d) || 0)
            },
            {
                data: null, orderable: false, className: 'text-center',
                render: (d, t, r) => t === 'display'
                    ? (Number(r.dipakai) ? '' : `<button class="btn btn-xs btn-primary btn-pakai" data-id="${r.id}">Pakai</button>`)
                    : ''
            }
        ]
    });

    $('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
        dtK.columns.adjust().draw();
        dtH.columns.adjust().draw();
    });

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

    $('#tblHasil tbody').on('click', '.btn-pakai', function() {
        const id = Number(this.dataset.id), row = hasil.find(r => r.id === id);
        XT.run(async () => {
            const ok = await Swal.fire({
                title: 'Jadikan email kontak?',
                text: `${row.email} akan disimpan sebagai email ${row.nama_perusahaan || row.nama || 'kontak'}.`,
                icon: 'question', showCancelButton: true,
                confirmButtonText: 'Ya, Pakai', cancelButtonText: 'Batal',
                confirmButtonColor: '#00a65a'
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

    async function doStart() {
        if (!checked.size) return Swal.fire('Pilih Kontak', 'Centang minimal satu kontak yang punya website.', 'warning');
        
        const c = cfg(), rate = parseFloat(c.rate), hours = parseFloat(c.hours);
        if (!(rate > 0)) return Swal.fire('Periksa Pengaturan', 'Kontak per menit harus > 0.', 'warning');
        if (!(hours > 0 && hours <= 24)) return Swal.fire('Periksa Pengaturan', 'Durasi 0.01 - 24 jam.', 'warning');

        // Kita hardcode token limit 1jt untuk backend, karena user tidak input lagi
        const r = await XT.api('/start', {
            ids: [...checked], key: c.key, model: c.model, rate, hours,
            token_limit: 1000000, auto_fill: c.auto_fill === '1' ? 1 : 0
        });
        Toast.fire({ icon: 'success', title: r.queued + ' kontak masuk antrean' });
        if (r.skipped) Toast.fire({ icon: 'warning', title: `${r.skipped} kontak dilewati` });
        if (!r.ai) Toast.fire({ icon: 'info', title: 'API key kosong: hanya regex.' });
        
        await poll();
    }
    $('#btnStart').on('click', () => XT.run(doStart));
    $('#btnStop').on('click', () => XT.run(async () => {
        await XT.api('/stop', {});
        await poll();
    }));

    const JOB = {
        queued:   ['Menunggu worker', 'warning'],
        running:  ['Berjalan', 'primary'],
        finished: ['Selesai', 'success'],
        stopped:  ['Dihentikan', 'default'],
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
                $('#stBadge').attr('class', 'label label-default').text('Siap');
                
                // Jika tidak ada job, set token kembali ke 1jt
                $('#token_sisa').val('1.000.000');
            } else {
                const m = JOB[j.status] || [j.status, 'default'];
                $('#stBadge').attr('class', 'label label-' + m[1]).text(m[0]);
                $('#stMsg').text(j.message || '');
                
                const total = Number(j.total || 0), done = Number(j.processed_count || 0);
                const pct = total ? Math.min(100, Math.round(done / total * 100)) : 0;
                $('#stBar').css('width', pct + '%');
                $('#stPct').text(`${num(done)} dari ${num(total)} kontak (${pct}%)`);
                $('#stDeadline').text(j.status === 'running' && j.deadline_at ? 'Batas: ' + j.deadline_at : '');
                
                $('#stProc').text(num(done));
                $('#stNew').text(num(j.found_count));
                $('#stTok').text(num(j.tokens_used));
                
                // Tampilkan sisa token secara realtime (Read-Only)
                const tokenSisa = Number(j.token_limit) - Number(j.tokens_used);
                $('#token_sisa').val(num(tokenSisa));

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
    
    poll();
    setInterval(poll, 3000);
</script>
<?= $this->endSection() ?>