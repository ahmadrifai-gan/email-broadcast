<?= $this->extend('layouts/main') ?>

<?= $this->section('title') ?>Scrapper Gmaps<?= $this->endSection() ?>
<?= $this->section('page_title') ?>Scrapper Gmaps<?= $this->endSection() ?>

<?= $this->section('breadcrumb') ?>
<li><a href="<?= site_url('/') ?>"><i class="fa fa-dashboard"></i> Home</a></li>
<li class="active">Scrapper Gmaps</li>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

<style>
    :root{
        --primary:#2563eb;
        --primary-dark:#1d4ed8;
        --secondary:#4f46e5;
        --text:#172033;
        --muted:#64748b;
        --border:#e2e8f0;
        --bg:#f4f7fb;
        --card:#fff;
        --success:#16a34a;
        --danger:#dc2626;
        --radius:22px;
        --shadow:0 12px 40px rgba(15,23,42,.07);
    }

    /* ===== SCOPED UTILITIES (KHUSUS DI DALAM .scrapper-container) ===== */
    .scrapper-container * { box-sizing: border-box; }
    .scrapper-container a { text-decoration: none; }

    .scrapper-container .d-flex { display: flex !important; }
    .scrapper-container .flex-column { flex-direction: column !important; }
    .scrapper-container .gap-1 { gap: 4px !important; }
    .scrapper-container .mb-0 { margin-bottom: 0 !important; }
    .scrapper-container .mb-3 { margin-bottom: 16px !important; }
    .scrapper-container .mt-3 { margin-top: 16px !important; }
    .scrapper-container .me-1 { margin-right: 4px !important; }
    .scrapper-container .me-2 { margin-right: 8px !important; }
    .scrapper-container .small { font-size: 12px !important; }
    .scrapper-container .text-primary { color: var(--primary) !important; }
    .scrapper-container .text-muted { color: var(--muted) !important; }

    .scrapper-container .btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        padding: 8px 16px;
        border-radius: 10px;
        font-size: 13px;
        font-weight: 600;
        line-height: 1.4;
        cursor: pointer;
        transition: .2s;
    }
    .scrapper-container .btn-success {
        background: var(--success);
        border: 1px solid var(--success);
        color: #fff;
    }
    .scrapper-container .btn-success:hover {
        background: #15803d;
        border-color: #15803d;
        color: #fff;
    }

    .scrapper-container .alert {
        border: 0;
        border-radius: 14px;
        font-size: 12px;
        padding: 12px 16px;
    }
    .scrapper-container .alert.fade.show { opacity: 1; }
    .scrapper-container .alert-dismissible { position: relative; padding-right: 40px; }
    .scrapper-container .alert-primary {
        background: #eff6ff;
        color: #1e40af;
        border: 1px solid #dbeafe;
    }
    .scrapper-container .btn-close {
        position: absolute;
        top: 50%;
        right: 14px;
        transform: translateY(-50%);
        background: transparent;
        border: 0;
        font-size: 18px;
        line-height: 1;
        color: inherit;
        opacity: .65;
        cursor: pointer;
    }
    .scrapper-container .btn-close::before { content: "\00d7"; }
    .scrapper-container .btn-close:hover { opacity: 1; }

    @keyframes scrapper-spin {
        to { transform: rotate(360deg); }
    }
    .scrapper-container .spinner-border {
        display: inline-block;
        width: 24px;
        height: 24px;
        vertical-align: -0.125em;
        border: 3px solid currentColor;
        border-right-color: transparent;
        border-radius: 50%;
        animation: scrapper-spin .75s linear infinite;
    }
    .scrapper-container .spinner-border-sm {
        width: 14px;
        height: 14px;
        border-width: 2px;
    }

    /* ===== STYLE UTAMA SCRAPPER ===== */
    .scrapper-container {
        font-family:'Inter',system-ui,sans-serif;
        color:var(--text);
        background:
            radial-gradient(circle at 0 0,rgba(37,99,235,.08),transparent 28%),
            radial-gradient(circle at 100% 0,rgba(79,70,229,.07),transparent 25%),
            var(--bg);
        border-radius: 15px;
        padding: 20px;
    }

    .scrapper-container .page{
        width:100%;
        max-width:1450px;
        margin:auto;
    }

    .scrapper-container .topbar{
        display:flex;
        align-items:center;
        justify-content:space-between;
        gap:16px;
        margin-bottom:18px;
    }

    .scrapper-container .brand{
        display:flex;
        align-items:center;
        gap:12px;
    }

    .scrapper-container .brand-icon{
        width:44px;height:44px;
        display:grid;place-items:center;
        border-radius:14px;
        color:#fff;
        background:linear-gradient(135deg,var(--primary),var(--secondary));
        box-shadow:0 10px 24px rgba(37,99,235,.2);
    }

    .scrapper-container .brand-title{font-weight:800;font-size:15px}
    .scrapper-container .brand-subtitle{font-size:10px;color:#94a3b8;margin-top:2px}

    .scrapper-container .status{
        display:inline-flex;
        align-items:center;
        gap:7px;
        padding:8px 12px;
        border-radius:999px;
        background:#f0fdf4;
        color:#15803d;
        border:1px solid #dcfce7;
        font-size:10px;
        font-weight:700;
    }

    .scrapper-container .status-dot{
        width:7px;height:7px;border-radius:50%;
        background:#22c55e;
        box-shadow:0 0 0 4px rgba(34,197,94,.1);
    }

    .scrapper-container .hero{
        position:relative;
        overflow:hidden;
        padding:38px;
        border-radius:var(--radius);
        color:#fff;
        background:linear-gradient(135deg,#1e3a8a,#2563eb 48%,#4f46e5);
        box-shadow:0 25px 65px rgba(37,99,235,.18);
        margin-bottom:18px;
    }

    .scrapper-container .hero:before,
    .scrapper-container .hero:after{
        content:"";
        position:absolute;
        border-radius:50%;
        background:rgba(255,255,255,.07);
        pointer-events:none;
    }

    .scrapper-container .hero:before{width:450px;height:450px;right:-150px;top:-280px}
    .scrapper-container .hero:after{width:300px;height:300px;right:180px;bottom:-250px}

    .scrapper-container .hero-content{position:relative;z-index:1}
    .scrapper-container .hero-icon{
        width:58px;height:58px;
        display:grid;place-items:center;
        border-radius:17px;
        background:rgba(255,255,255,.13);
        border:1px solid rgba(255,255,255,.16);
        font-size:22px;
        margin-bottom:16px;
    }

    .scrapper-container .hero h1{
        margin:0 0 10px;
        font-size:34px;
        line-height:1.15;
        font-weight:800;
        letter-spacing:-.8px;
    }

    .scrapper-container .hero p{
        max-width:820px;
        margin:0;
        color:rgba(255,255,255,.84);
        font-size:12px;
        line-height:1.8;
    }

    .scrapper-container .chips{
        display:flex;
        flex-wrap:wrap;
        gap:8px;
        margin-top:20px;
    }

    .scrapper-container .chip{
        display:inline-flex;
        align-items:center;
        gap:7px;
        padding:8px 11px;
        border-radius:999px;
        background:rgba(255,255,255,.1);
        border:1px solid rgba(255,255,255,.14);
        font-size:9px;
        font-weight:600;
    }

    .scrapper-container .card{
        background:rgba(255,255,255,.97);
        border:1px solid var(--border);
        border-radius:var(--radius);
        box-shadow:var(--shadow);
        overflow:hidden;
    }

    .scrapper-container .card + .card{margin-top:18px}

    .scrapper-container .card-head{
        padding:20px 22px;
        border-bottom:1px solid var(--border);
    }

    .scrapper-container .section-title{
        margin:0;
        font-size:15px;
        font-weight:800;
    }

    .scrapper-container .section-subtitle{
        margin:4px 0 0;
        color:var(--muted);
        font-size:10px;
    }

    .scrapper-container .search-body{padding:24px}

    .scrapper-container .search-row{
        display:flex;
        gap:10px;
    }

    .scrapper-container .input-wrap{position:relative;flex:1}
    .scrapper-container .input-icon{
        position:absolute;
        left:16px;top:50%;
        transform:translateY(-50%);
        color:#94a3b8;
        z-index:2;
    }

    .scrapper-container .search-input{
        width:100%;
        height:54px;
        padding:0 16px 0 44px;
        border:1px solid #dbe3ee;
        border-radius:14px;
        outline:none;
        font-size:12px;
        transition:.2s;
    }

    .scrapper-container .search-input:focus{
        border-color:#60a5fa;
        box-shadow:0 0 0 4px rgba(37,99,235,.08);
    }

    .scrapper-container .search-input.is-invalid{
        border-color:var(--danger);
        box-shadow:0 0 0 4px rgba(220,38,38,.12);
    }

    .scrapper-container .search-button{
        min-width:150px;
        height:54px;
        border:0;
        border-radius:14px;
        color:#fff;
        font-weight:700;
        font-size:11px;
        background:linear-gradient(135deg,var(--primary),var(--secondary));
        box-shadow:0 10px 24px rgba(37,99,235,.18);
        cursor:pointer;
    }

    .scrapper-container .search-button:disabled{opacity:.7;cursor:wait}

    .scrapper-container .hint{
        margin-top:11px;
        padding:10px 12px;
        border-radius:10px;
        background:#f8fafc;
        border:1px solid #e2e8f0;
        color:#7b8798;
        font-size:9px;
        line-height:1.6;
    }

    .scrapper-container .history-list{
        display:grid;
        grid-template-columns:repeat(3,minmax(0,1fr));
        gap:10px;
        padding:16px;
    }

    .scrapper-container .history-item{
        display:flex;
        align-items:center;
        gap:11px;
        min-width:0;
        padding:13px;
        border:1px solid #e8edf4;
        border-radius:14px;
        background:#fff;
        transition:.18s;
    }

    .scrapper-container .history-item:hover{
        transform:translateY(-2px);
        border-color:#bfdbfe;
        box-shadow:0 10px 25px rgba(15,23,42,.06);
    }

    .scrapper-container .history-icon{
        width:40px;height:40px;
        flex:0 0 40px;
        display:grid;place-items:center;
        border-radius:12px;
        color:var(--primary);
        background:#eff6ff;
    }

    .scrapper-container .history-info{min-width:0;flex:1}
    .scrapper-container .history-keyword{
        overflow:hidden;
        text-overflow:ellipsis;
        white-space:nowrap;
        font-size:11px;
        font-weight:700;
    }

    .scrapper-container .history-count{font-size:9px;color:#94a3b8;margin-top:3px}

    .scrapper-container .history-link{
        width:34px;height:34px;
        flex:0 0 34px;
        display:grid;place-items:center;
        border-radius:10px;
        background:#f8fafc;
        color:#64748b;
    }

    .scrapper-container .history-link:hover{background:#eff6ff;color:var(--primary)}

    .scrapper-container .result-head{
        display:flex;
        align-items:center;
        justify-content:space-between;
        gap:12px;
    }

    .scrapper-container .result-badge{
        padding:7px 10px;
        border-radius:999px;
        background:#eff6ff;
        color:var(--primary);
        border:1px solid #dbeafe;
        font-size:9px;
        font-weight:700;
    }

    .scrapper-container .toolbar{
        display:none;
        align-items:center;
        gap:10px;
        padding:14px 18px;
        border-bottom:1px solid var(--border);
    }

    .scrapper-container .toolbar input{
        width:100%;
        max-width:360px;
        height:38px;
        padding:0 12px;
        border:1px solid #dbe3ee;
        border-radius:10px;
        outline:none;
        font-size:10px;
    }

    .scrapper-container .toolbar input:focus{
        border-color:#60a5fa;
        box-shadow:0 0 0 3px rgba(37,99,235,.07);
    }

    .scrapper-container .table-wrap{width:100%;overflow:auto}
    .scrapper-container table{width:100%;min-width:1050px;border-collapse:collapse}
    .scrapper-container th{
        padding:13px 14px;
        background:#f8fafc;
        color:#64748b;
        border-bottom:1px solid var(--border);
        text-align:left;
        font-size:9px;
        text-transform:uppercase;
        letter-spacing:.3px;
    }
    .scrapper-container td{
        padding:14px;
        border-bottom:1px solid #eef2f7;
        vertical-align:middle;
        font-size:10px;
    }
    .scrapper-container tr:hover td{background:#fbfdff}

    .scrapper-container .number{
        width:55px;
        text-align:center;
        color:#94a3b8;
        font-weight:800;
    }

    .scrapper-container .place-name{font-weight:800;color:#111827}
    .scrapper-container .address{max-width:320px;line-height:1.55;color:#475569}
    .scrapper-container .phone a{color:#475569;font-weight:700}
    .scrapper-container .email a{font-weight:700;color:var(--primary)}
    .scrapper-container .website a{font-weight:700;color:var(--primary)}
    .scrapper-container .map-btn{
        display:inline-flex;
        align-items:center;
        justify-content:center;
        width:34px;height:34px;
        border-radius:10px;
        color:#15803d;
        background:#f0fdf4;
        border:1px solid #dcfce7;
    }

    .scrapper-container .wa-badge{
        display:inline-flex;
        align-items:center;
        gap:6px;
        padding:6px 10px;
        border-radius:999px;
        font-weight:700;
        font-size:9.5px;
        white-space:nowrap;
        border:1px solid transparent;
        width:fit-content;
    }

    .scrapper-container .wa-badge i{font-size:11px}

    .scrapper-container .wa-badge-active{
        background:#f0fdf4;
        color:#15803d;
        border-color:#bbf7d0;
    }

    .scrapper-container .wa-badge-inactive{
        background:#fffbeb;
        color:#92400e;
        border-color:#fde68a;
    }

    .scrapper-container .wa-badge-unknown{
        background:#f1f5f9;
        color:#64748b;
        border-color:#e2e8f0;
    }

    .scrapper-container .empty{
        padding:55px 20px;
        text-align:center;
        color:#64748b;
    }

    .scrapper-container .empty-icon{
        width:70px;height:70px;
        margin:0 auto 15px;
        display:grid;place-items:center;
        border-radius:20px;
        background:#f1f5f9;
        color:#94a3b8;
        font-size:28px;
    }

    .scrapper-container .empty-title{font-weight:800;color:#334155;margin-bottom:6px}
    .scrapper-container .empty-text{font-size:10px}

    @media(max-width:991px){
        .scrapper-container .history-list{grid-template-columns:repeat(2,minmax(0,1fr))}
    }

    @media(max-width:767px){
        .scrapper-container .page{padding:16px 11px 35px}
        .scrapper-container .topbar{align-items:flex-start}
        .scrapper-container .status{font-size:9px}
        .scrapper-container .hero{padding:25px 20px;border-radius:18px}
        .scrapper-container .hero h1{font-size:27px}
        .scrapper-container .hero p{font-size:10px}
        .scrapper-container .search-body{padding:18px}
        .scrapper-container .search-row{flex-direction:column}
        .scrapper-container .search-button{width:100%}
        .scrapper-container .history-list{grid-template-columns:1fr;padding:12px}
        .scrapper-container .card-head{padding:17px}
        .scrapper-container .result-head{align-items:flex-start;flex-direction:column}
        .scrapper-container .toolbar{align-items:stretch;flex-direction:column}
        .scrapper-container .toolbar input{max-width:none}
    }
</style>

<div class="scrapper-container">
    <div class="page">
        <div class="topbar">
            <div class="brand">
                <div class="brand-icon"><i class="fa-solid fa-location-dot"></i></div>
                <div>
                    <div class="brand-title">Google Maps Data Scraper</div>
                    <div class="brand-subtitle">Pencarian data tempat berbasis keyword</div>
                </div>
            </div>

            <div class="status">
                <span class="status-dot"></span>
                Sistem siap digunakan
            </div>
        </div>

        <section class="hero">
            <div class="hero-content">
                <div class="hero-icon">
                    <i class="fa-solid fa-map-location-dot"></i>
                </div>

                <h1>Ambil Data Google Maps<br>dengan Lebih Mudah</h1>

                <p>
                    Masukkan keyword atau URL pencarian Google Maps.
                    Sistem akan mengambil nama tempat, alamat, nomor telepon,
                    email (jika tersedia), website, dan tautan Google Maps
                    tanpa mengambil rating.
                </p>

                <div class="chips">
                    <span class="chip"><i class="fa-solid fa-magnifying-glass"></i> Keyword / URL</span>
                    <span class="chip"><i class="fa-solid fa-phone"></i> Nomor HP</span>
                    <span class="chip"><i class="fa-solid fa-envelope"></i> Email</span>
                    <span class="chip"><i class="fa-solid fa-globe"></i> Website</span>
                    <span class="chip"><i class="fa-solid fa-location-dot"></i> Google Maps</span>
                    <span class="chip"><i class="fa-solid fa-shield-halved"></i> Anti Duplikat</span>
                </div>
            </div>
        </section>

        <?php if (session()->getFlashdata('success')): ?>
            <div class="alert alert-success alert-dismissible fade show mb-3" role="alert">
                <i class="fa-solid fa-circle-check me-2"></i>
                <?= esc(session()->getFlashdata('success')) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if (session()->getFlashdata('error')): ?>
            <div class="alert alert-danger alert-dismissible fade show mb-3" role="alert">
                <i class="fa-solid fa-circle-exclamation me-2"></i>
                <?= esc(session()->getFlashdata('error')) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <section class="card">
            <div class="card-head">
                <h2 class="section-title">
                    <i class="fa-solid fa-magnifying-glass text-primary me-2"></i>
                    Pencarian Data
                </h2>
                <p class="section-subtitle">
                    Gunakan kata pencarian biasa atau URL Google Maps.
                </p>
            </div>

            <div class="search-body">
                <div class="search-row">
                    <div class="input-wrap">
                        <i class="fa-solid fa-location-dot input-icon"></i>
                        <input
                            type="text"
                            id="gmapsUrl"
                            class="search-input"
                            placeholder="Contoh: travel haji umroh Malang, restoran Jakarta..."
                            autocomplete="off"
                        >
                    </div>

                    <button type="button" id="btnAmbil" class="search-button" onclick="ambilData()">
                        <i class="fa-solid fa-cloud-arrow-down me-2"></i>
                        Ambil Data
                    </button>
                </div>

                <div class="hint">
                    <i class="fa-solid fa-circle-info me-1 text-primary"></i>
                    Contoh:
                    <code>travel haji umroh Malang</code>
                    atau
                    <code>https://www.google.com/maps/search/travel+haji+umroh+Malang/</code>
                </div>

                <div id="loadingStatus" class="mt-3" style="display:none;">
                    <div class="alert alert-primary mb-0">
                        <span class="spinner-border spinner-border-sm me-2"></span>
                        Sedang mengambil data dari Google Maps. Mohon tunggu...
                    </div>
                </div>
            </div>
        </section>

        <section class="card">
            <div class="card-head result-head">
                <div>
                    <h2 class="section-title">
                        <i class="fa-solid fa-clock-rotate-left text-primary me-2"></i>
                        Riwayat Pencarian
                    </h2>
                    <p class="section-subtitle">Keyword yang pernah tersimpan di database.</p>
                </div>
                <span class="result-badge">
                    <?= number_format(is_array($history ?? []) ? count($history) : 0) ?> keyword
                </span>
            </div>

            <div class="history-list">
                <?php if (empty($history)): ?>
                    <div class="empty" style="grid-column:1/-1;padding:35px 15px;">
                        <div class="empty-icon"><i class="fa-solid fa-clock-rotate-left"></i></div>
                        <div class="empty-title">Belum ada riwayat</div>
                        <div class="empty-text">Lakukan pencarian pertama untuk membuat riwayat.</div>
                    </div>
                <?php else: ?>
                    <?php foreach ($history as $item): ?>
                        <?php
                            $keyword = trim((string)($item['keyword'] ?? ''));
                            $total = (int)($item['total_data'] ?? 0);
                        ?>
                        <?php if ($keyword === '') continue; ?>

                        <div class="history-item">
                            <div class="history-icon">
                                <i class="fa-solid fa-magnifying-glass"></i>
                            </div>

                            <div class="history-info">
                                <div class="history-keyword" title="<?= esc($keyword) ?>">
                                    <?= esc($keyword) ?>
                                </div>
                                <div class="history-count">
                                    <?= number_format($total) ?> data tersimpan
                                </div>
                            </div>

                            <a
                                class="history-link"
                                href="<?= site_url('scrapper/edit-data/' . rawurlencode($keyword)) ?>"
                                title="Kelola data"
                            >
                                <i class="fa-solid fa-arrow-right"></i>
                            </a>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </section>

        <section class="card">
            <div class="card-head result-head">
                <div>
                    <h2 class="section-title">
                        <i class="fa-solid fa-table-list text-primary me-2"></i>
                        Hasil Pencarian
                    </h2>
                    <p class="section-subtitle" id="resultSubtitle">
                        Belum ada pencarian.
                    </p>
                </div>

                <span id="totalBadge" class="result-badge">0 data</span>
            </div>

            <div id="tableToolbar" class="toolbar">
                <input
                    type="search"
                    id="tableSearch"
                    placeholder="Filter hasil berdasarkan nama, alamat, nomor, email, atau website..."
                >
                <span class="small text-muted">
                    Menampilkan <strong id="visibleTotal">0</strong> data
                </span>
            </div>

            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th class="number">#</th>
                            <th>Nama</th>
                            <th>Alamat</th>
                            <th>Nomor HP</th>
                            <th>Email</th>
                            <th>Website</th>
                            <th>Google Maps</th>
                        </tr>
                    </thead>

                    <tbody id="tableBody">
                        <tr>
                            <td colspan="7">
                                <div class="empty">
                                    <div class="empty-icon">
                                        <i class="fa-solid fa-database"></i>
                                    </div>
                                    <div class="empty-title">Belum ada data</div>
                                    <div class="empty-text">
                                        Hasil scraping akan tampil di sini.
                                    </div>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </section>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<?= csrf_field() ?>

<script>
'use strict';

document.querySelectorAll('.scrapper-container [data-bs-dismiss="alert"]').forEach(btn => {
    btn.addEventListener('click', () => {
        const alert = btn.closest('.alert');
        if (alert) alert.remove();
    });
});

let scrapedData = [];

let csrfInput =
    document.querySelector(
        'input[name^="csrf"]'
    );

let csrfName =
    csrfInput
        ? csrfInput.name
        : '';

let csrfHash =
    csrfInput
        ? csrfInput.value
        : '';


const input =
    document.getElementById(
        'gmapsUrl'
    );

const btn =
    document.getElementById(
        'btnAmbil'
    );

const loading =
    document.getElementById(
        'loadingStatus'
    );

const tableBody =
    document.getElementById(
        'tableBody'
    );

const tableToolbar =
    document.getElementById(
        'tableToolbar'
    );

const tableSearch =
    document.getElementById(
        'tableSearch'
    );

const visibleTotal =
    document.getElementById(
        'visibleTotal'
    );

const totalBadge =
    document.getElementById(
        'totalBadge'
    );

const resultSubtitle =
    document.getElementById(
        'resultSubtitle'
    );


function escapeHtml(value)
{
    return String(value ?? '')
        .replace(
            /&/g,
            '&amp;'
        )
        .replace(
            /</g,
            '&lt;'
        )
        .replace(
            />/g,
            '&gt;'
        )
        .replace(
            /"/g,
            '&quot;'
        )
        .replace(
            /'/g,
            '&#039;'
        );
}


function normalizeWebsite(url)
{
    if (
        !url ||
        url === '-'
    ) {
        return '';
    }

    url =
        String(url)
            .trim();


    if (
        !/^https?:\/\//i.test(url)
    ) {

        url =
            'https://' +
            url;
    }


    try {

        const parsed =
            new URL(url);


        if (
            ![
                'http:',
                'https:'
            ].includes(
                parsed.protocol
            )
        ) {

            return '';
        }


        return parsed.href;

    } catch (e) {

        return '';
    }
}


function normalizePhone(phone)
{
    if (
        !phone ||
        phone === '-'
    ) {

        return '';
    }


    return String(phone)
        .replace(
            /[^\d+]/g,
            ''
        );
}


function normalizeEmail(email)
{
    if (
        !email ||
        email === '-'
    ) {

        return '';
    }


    email =
        String(email)
            .trim();


    const isValid =
        /^[^\s@]+@[^\s@]+\.[^\s@]+$/
            .test(email);


    return isValid
        ? email
        : '';
}


function waBadgeInfo(
    waStatus
) {

    if (
        waStatus === 'active'
    ) {

        return {

            className:
                'wa-badge-active',

            icon:
                'fa-solid fa-circle-check',

            label:
                'Aktif WA'
        };
    }


    if (
        waStatus === 'inactive'
    ) {

        return {

            className:
                'wa-badge-inactive',

            icon:
                'fa-solid fa-triangle-exclamation',

            label:
                'Tidak Aktif WA'
        };
    }


    return {

        className:
            'wa-badge-unknown',

        icon:
            'fa-regular fa-clock',

        label:
            'Belum Dicek'
    };
}


function renderPhone(
    phone,
    waStatus
) {

    if (
        !phone ||
        phone === '-'
    ) {

        return `
            <span class="text-muted">
                -
            </span>
        `;
    }


    const clean =
        normalizePhone(phone);


    const badge =
        waBadgeInfo(
            waStatus
        );


    const numberHtml =
        clean

            ? `
                <a
                    href="tel:${escapeHtml(clean)}"
                >
                    ${escapeHtml(phone)}
                </a>
            `

            : escapeHtml(phone);


    return `
        <div class="phone d-flex flex-column gap-1">

            ${numberHtml}

            <span
                class="wa-badge ${badge.className}"
                title="Status WhatsApp: ${badge.label}"
            >

                <i class="${badge.icon}"></i>

                ${badge.label}

            </span>

        </div>
    `;
}


function renderEmail(email)
{
    const clean =
        normalizeEmail(email);


    if (!clean) {

        return `
            <span class="text-muted">
                -
            </span>
        `;
    }


    return `
        <a
            href="mailto:${escapeHtml(clean)}"
        >

            <i
                class="fa-solid fa-envelope me-1"
            ></i>

            ${escapeHtml(clean)}

        </a>
    `;
}


function renderWebsite(website)
{
    const url =
        normalizeWebsite(
            website
        );


    if (!url) {

        return `
            <span class="text-muted">
                -
            </span>
        `;
    }


    let label =
        'Website';


    try {

        label =
            new URL(url)
                .hostname
                .replace(
                    /^www\./i,
                    ''
                );

    } catch (e) {}


    return `
        <a
            href="${escapeHtml(url)}"
            target="_blank"
            rel="noopener noreferrer"
        >

            <i
                class="fa-solid fa-globe me-1"
            ></i>

            ${escapeHtml(label)}

        </a>
    `;
}


function renderMaps(url)
{
    if (
        !url ||
        url === '-'
    ) {

        return `
            <span class="text-muted">
                -
            </span>
        `;
    }


    return `
        <a
            href="${escapeHtml(url)}"
            target="_blank"
            rel="noopener noreferrer"
            class="map-btn"
            title="Buka Google Maps"
        >

            <i
                class="fa-solid fa-location-dot"
            ></i>

        </a>
    `;
}


function renderFilteredTable(
    data
) {

    if (
        !Array.isArray(data)
    ) {

        data = [];
    }


    visibleTotal.textContent =
        data.length;


    if (
        data.length === 0
    ) {

        tableBody.innerHTML = `

            <tr>

                <td colspan="7">

                    <div class="empty">

                        <div class="empty-icon">

                            <i
                                class="fa-solid fa-filter"
                            ></i>

                        </div>

                        <div class="empty-title">
                            Data tidak ditemukan
                        </div>

                        <div class="empty-text">
                            Tidak ada hasil yang sesuai dengan filter.
                        </div>

                    </div>

                </td>

            </tr>

        `;

        return;
    }


    tableBody.innerHTML =
        data
            .map(
                (
                    item,
                    index
                ) => `

                    <tr>

                        <td class="number">
                            ${String(index + 1).padStart(2, '0')}
                        </td>


                        <td>

                            <div class="place-name">
                                ${escapeHtml(
                                    item.nama || '-'
                                )}
                            </div>

                        </td>


                        <td>

                            <div class="address">

                                <i
                                    class="fa-solid fa-location-dot text-muted me-1"
                                ></i>

                                ${escapeHtml(
                                    item.alamat || '-'
                                )}

                            </div>

                        </td>


                        <td class="phone">

                            ${renderPhone(
                                item.nomor || '',
                                item.wa_status
                            )}

                        </td>


                        <td class="email">

                            ${renderEmail(
                                item.email || ''
                            )}

                        </td>


                        <td class="website">

                            ${renderWebsite(
                                item.website || ''
                            )}

                        </td>


                        <td>

                            ${renderMaps(
                                item.maps_url || ''
                            )}

                        </td>

                    </tr>

                `
            )
            .join('');
}


function renderResultTable(
    data
) {

    scrapedData =
        Array.isArray(data)
            ? data
            : [];


    tableToolbar.style.display =
        scrapedData.length
            ? 'flex'
            : 'none';


    if (tableSearch) {

        tableSearch.value = '';
    }


    renderFilteredTable(
        scrapedData
    );
}


function showLoadingTable()
{
    tableToolbar.style.display =
        'none';


    tableBody.innerHTML = `

        <tr>

            <td colspan="7">

                <div class="empty">

                    <div
                        class="spinner-border text-primary mb-3"
                        role="status"
                    ></div>

                    <div class="empty-title">
                        Sedang mengambil data...
                    </div>

                    <div class="empty-text">
                        Sistem sedang memproses hasil Google Maps.
                    </div>

                </div>

            </td>

        </tr>

    `;
}


function showTableError(
    message
) {

    tableToolbar.style.display =
        'none';


    tableBody.innerHTML = `

        <tr>

            <td colspan="7">

                <div class="empty">

                    <div
                        class="empty-icon"
                        style="background:#fef2f2;color:#dc2626;"
                    >

                        <i
                            class="fa-solid fa-triangle-exclamation"
                        ></i>

                    </div>

                    <div class="empty-title">
                        Gagal mengambil data
                    </div>

                    <div class="empty-text">
                        ${escapeHtml(message)}
                    </div>

                </div>

            </td>

        </tr>

    `;
}


async function ambilData()
{
    const value =
        input.value.trim();


    if (!value) {

        input.focus();

        input.classList.add(
            'is-invalid'
        );


        setTimeout(
            () => {
                input.classList.remove(
                    'is-invalid'
                );
            },
            2000
        );


        showTableError(
            'Masukkan keyword atau URL Google Maps terlebih dahulu.'
        );

        return;
    }


    btn.disabled =
        true;


    btn.innerHTML = `

        <span
            class="spinner-border spinner-border-sm me-2"
        ></span>

        Memproses...

    `;


    loading.style.display =
        'block';


    totalBadge.textContent =
        'Memproses...';


    resultSubtitle.textContent =
        'Sedang mengambil data...';


    showLoadingTable();


    const formData =
        new FormData();


    formData.append(
        'url',
        value
    );


    if (
        csrfName &&
        csrfHash
    ) {

        formData.append(
            csrfName,
            csrfHash
        );
    }


    try {

        const response =
            await fetch(

                '<?= site_url("scrapper/scrape") ?>',

                {

                    method:
                        'POST',

                    body:
                        formData,

                    headers: {

                        'X-Requested-With':
                            'XMLHttpRequest',

                        'Accept':
                            'application/json'
                    }

                }
            );


        const text =
            await response.text();


        console.log(
            'SERVER RESPONSE:',
            text
        );


        let data;


        try {

            data =
                JSON.parse(text);

        } catch (e) {

            console.error(
                'INVALID JSON:',
                text
            );

            throw new Error(
                'Server tidak mengembalikan JSON valid. Periksa log aplikasi.'
            );
        }


        if (
            data.csrf &&
            data.csrf.hash
        ) {

            csrfHash =
                data.csrf.hash;


            if (csrfInput) {

                csrfInput.value =
                    csrfHash;
            }
        }


        if (
            data.status !==
            'success'
        ) {

            const serverMessage =
                data.message ||
                'Gagal mengambil data.';


            console.warn(
                'SCRAPE ERROR:',
                serverMessage
            );


            totalBadge.textContent =
                'Pencarian gagal';


            resultSubtitle.textContent =
                'Permintaan tidak dapat diproses.';


            showTableError(
                serverMessage
            );


            return;
        }


        const dataArray =
            Array.isArray(
                data.data
            )
                ? data.data
                : [];


        const total =
            Number(
                data.total ??
                dataArray.length
            );


        renderResultTable(
            dataArray
        );


        const keyword =
            data.keyword ||
            value;


        resultSubtitle.textContent =
            'Keyword: ' +
            keyword;


        totalBadge.textContent =
            total +
            ' data ditemukan';


        const newTotal =
            Number(
                data.new_total || 0
            );


        const existingTotal =
            Number(
                data.existing_total || 0
            );


        if (
            newTotal > 0 ||
            existingTotal > 0
        ) {

            resultSubtitle.textContent =

                `Keyword: ${keyword}` +
                ` • Baru: ${newTotal}` +
                ` • Sudah ada: ${existingTotal}`;
        }


        if (
            Array.isArray(
                data.history
            )
        ) {

            updateHistory(
                data.history
            );
        }


    } catch (error) {

        console.error(
            'SCRAPE ERROR:',
            error
        );


        totalBadge.textContent =
            'Terjadi kesalahan';


        resultSubtitle.textContent =
            'Pencarian gagal diproses.';


        showTableError(
            error.message ||
            'Terjadi kesalahan saat mengambil data.'
        );

    } finally {

        loading.style.display =
            'none';


        btn.disabled =
            false;


        btn.innerHTML = `

            <i
                class="fa-solid fa-cloud-arrow-down me-2"
            ></i>

            Ambil Data

        `;
    }
}


function updateHistory(
    history
) {

    const list =
        document.querySelector(
            '.history-list'
        );


    if (
        !list ||
        !Array.isArray(history)
    ) {

        return;
    }


    if (
        history.length === 0
    ) {

        list.innerHTML = `

            <div
                class="empty"
                style="grid-column:1/-1;padding:35px 15px;"
            >

                <div class="empty-icon">

                    <i
                        class="fa-solid fa-clock-rotate-left"
                    ></i>

                </div>

                <div class="empty-title">
                    Belum ada riwayat
                </div>

            </div>

        `;

        return;
    }


    list.innerHTML =
        history
            .map(
                item => {

                    const keyword =
                        String(
                            item.keyword || ''
                        ).trim();


                    const total =
                        Number(
                            item.total_data || 0
                        );


                    if (!keyword) {
                        return '';
                    }


                    return `

                        <div class="history-item">

                            <div class="history-icon">

                                <i
                                    class="fa-solid fa-magnifying-glass"
                                ></i>

                            </div>


                            <div class="history-info">

                                <div
                                    class="history-keyword"
                                    title="${escapeHtml(keyword)}"
                                >
                                    ${escapeHtml(keyword)}
                                </div>


                                <div class="history-count">

                                    ${total.toLocaleString('id-ID')}

                                    data tersimpan

                                </div>

                            </div>


                            <a
                                class="history-link"
                                href="<?= site_url('scrapper/edit-data') ?>/${encodeURIComponent(keyword)}"
                                title="Kelola data"
                            >

                                <i
                                    class="fa-solid fa-arrow-right"
                                ></i>

                            </a>

                        </div>

                    `;
                }
            )
            .join('');
}


if (tableSearch) {

    tableSearch.addEventListener(
        'input',
        function () {

            const q =
                this.value
                    .trim()
                    .toLowerCase();


            if (!q) {

                renderFilteredTable(
                    scrapedData
                );

                return;
            }


            const filtered =
                scrapedData.filter(
                    item => {

                        const haystack = [

                            item.nama,

                            item.alamat,

                            item.nomor,

                            item.email,

                            item.website,

                            item.maps_url

                        ]
                            .join(' ')
                            .toLowerCase();


                        return haystack.includes(
                            q
                        );
                    }
                );


            renderFilteredTable(
                filtered
            );
        }
    );
}


input.addEventListener(
    'keydown',
    function (event) {

        if (
            event.key ===
            'Enter'
        ) {

            event.preventDefault();

            ambilData();
        }
    }
);

</script>
<?= $this->endSection() ?>