<?= $this->extend('layouts/main') ?>

<?= $this->section('title') ?>Data - <?= esc($keyword ?? 'Keyword') ?><?= $this->endSection() ?>
<?= $this->section('page_title') ?>Data Keyword<?= $this->endSection() ?>

<?= $this->section('breadcrumb') ?>
<li><a href="<?= site_url('/') ?>"><i class="fa fa-dashboard"></i> Home</a></li>
<li><a href="<?= site_url('scrapper/edit-data') ?>">Edit Data Scrape</a></li>
<li class="active"><?= esc($keyword ?? 'Keyword') ?></li>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,400;14..32,500;14..32,600;14..32,700;14..32,800;14..32,900&display=swap" rel="stylesheet">

<style>
    :root {
        --primary: #2563eb;
        --primary-dark: #1d4ed8;
        --primary-soft: #eff6ff;
        --primary-light: #dbeafe;
        --success: #16a34a;
        --danger: #dc2626;
        --warning: #d97706;
        --ink: #0f172a;
        --ink-light: #1e293b;
        --muted: #64748b;
        --muted-light: #94a3b8;
        --line: #e2e8f0;
        --bg: #f1f5f9;
        --surface: #ffffff;
        --shadow-sm: 0 2px 8px rgba(15,23,42,0.06);
        --shadow-md: 0 8px 30px rgba(15,23,42,0.08);
        --shadow-lg: 0 20px 50px rgba(15,23,42,0.12);
        --radius-sm: 10px;
        --radius-md: 16px;
        --radius-lg: 24px;
        --transition: all 0.3s cubic-bezier(0.4,0,0.2,1);
    }

    /* ===== SCOPED UTILITIES ===== */
    .page-wrapper * { box-sizing: border-box; }
    .page-wrapper a { text-decoration: none; }
    .page-wrapper .d-flex { display: flex !important; }
    .page-wrapper .d-inline { display: inline !important; }
    .page-wrapper .flex-column { flex-direction: column !important; }
    .page-wrapper .flex-wrap { flex-wrap: wrap !important; }
    .page-wrapper .justify-content-between { justify-content: space-between !important; }
    @media (min-width: 768px) {
        .page-wrapper .flex-md-row { flex-direction: row !important; }
        .page-wrapper .align-items-md-center { align-items: center !important; }
    }
    .page-wrapper .gap-1 { gap: 4px !important; }
    .page-wrapper .gap-2 { gap: 8px !important; }
    .page-wrapper .gap-4 { gap: 24px !important; }
    .page-wrapper .mb-4 { margin-bottom: 24px !important; }
    .page-wrapper .me-1 { margin-right: 4px !important; }
    .page-wrapper .me-2 { margin-right: 8px !important; }
    .page-wrapper .border-0 { border: 0 !important; }
    .page-wrapper .shadow-sm { box-shadow: var(--shadow-sm) !important; }
    .page-wrapper .btn {
        display: inline-flex; align-items: center; justify-content: center; gap: 6px;
        font-size: 14px; line-height: 1.5; text-decoration: none; cursor: pointer;
    }
    .page-wrapper .btn-primary { background: var(--primary); border: 1px solid var(--primary); color: #fff; }
    .page-wrapper .btn-primary:hover { background: var(--primary-dark); border-color: var(--primary-dark); color: #fff; }
    .page-wrapper .btn-secondary { background: #64748b; border: 1px solid #64748b; color: #fff; }
    .page-wrapper .btn-secondary:hover { background: #475569; border-color: #475569; color: #fff; }
    .page-wrapper .btn-outline-success { background: transparent; border: 1px solid #16a34a; color: #16a34a; }
    .page-wrapper .btn-outline-success:hover { background: #16a34a; color: #fff; }
    .page-wrapper .btn-outline-primary { background: transparent; border: 1px solid var(--primary); color: var(--primary); }
    .page-wrapper .btn-outline-primary:hover { background: var(--primary); color: #fff; }
    .page-wrapper .btn-outline-secondary { background: transparent; border: 1px solid #cbd5e1; color: #475569; }
    .page-wrapper .btn-outline-secondary:hover { background: #64748b; border-color: #64748b; color: #fff; }
    .page-wrapper .btn-outline-danger { background: transparent; border: 1px solid #dc2626; color: #dc2626; }
    .page-wrapper .btn-outline-danger:hover { background: #dc2626; color: #fff; }
    .page-wrapper .alert.fade.show { opacity: 1; }
    .page-wrapper .alert-dismissible { position: relative; padding-right: 44px; }
    .page-wrapper .btn-close {
        position: absolute; top: 50%; right: 16px; transform: translateY(-50%);
        background: transparent; border: 0; font-size: 20px; line-height: 1;
        color: inherit; opacity: 0.6; cursor: pointer;
    }
    .page-wrapper .btn-close::before { content: "\00d7"; }
    .page-wrapper .btn-close:hover { opacity: 1; }

    .page-wrapper {
        max-width: 1500px;
        margin:0 auto;
        padding:32px 24px 60px;
        min-height: 100vh;
        background: var(--bg);
        font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
        font-size: 14px;
        color: var(--ink);
        -webkit-font-smoothing: antialiased;
        background-image: radial-gradient(circle at 5% 0%, rgba(37,99,235,0.05) 0%, transparent 50%),
                          radial-gradient(circle at 100% 10%, rgba(37,99,235,0.05) 0%, transparent 50%);
        border-radius: 16px;
    }
    @media (max-width:576px) { .page-wrapper { padding:20px 16px 40px; } }

    /* HEADER */
    .page-wrapper .header-card {
        background: var(--surface);
        border-radius: var(--radius-lg);
        padding: 32px 36px;
        margin-bottom: 28px;
        box-shadow: var(--shadow-md);
        border: 1px solid rgba(226,232,240,0.6);
        transition: var(--transition);
        position: relative;
        overflow: hidden;
    }
    .page-wrapper .header-card::before {
        content: '';
        position: absolute;
        top: 0; left: 0; right: 0;
        height: 4px;
        background: linear-gradient(90deg, var(--primary), #60a5fa, #a78bfa);
    }
    .page-wrapper .header-card:hover { box-shadow: var(--shadow-lg); }

    .page-wrapper .header-label {
        display: flex;
        align-items: center;
        gap: 8px;
        color: var(--muted);
        font-size: 12px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.08em;
        margin-bottom: 6px;
    }
    .page-wrapper .header-label i { color: var(--primary); }

    .page-wrapper .keyword-title {
        display: flex;
        align-items: center;
        gap: 12px;
        font-size: 28px;
        font-weight: 800;
        margin: 0 0 12px 0;
        word-break: break-word;
    }
    .page-wrapper .keyword-title i { color: var(--primary); font-size: 26px; }

    .page-wrapper .data-badge {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        background: var(--primary-soft);
        color: var(--primary);
        border: 1px solid var(--primary-light);
        border-radius: 999px;
        padding: 8px 16px;
        font-size: 13px;
        font-weight: 700;
    }

    .page-wrapper .header-actions .btn {
        border-radius: var(--radius-sm);
        font-weight: 600;
        padding: 10px 18px;
        transition: var(--transition);
    }
    .page-wrapper .header-actions .btn:hover { transform: translateY(-2px); box-shadow: var(--shadow-sm); }

    /* TABLE CARD */
    .page-wrapper .table-card {
        background: var(--surface);
        border-radius: var(--radius-lg);
        box-shadow: var(--shadow-md);
        border: 1px solid rgba(226,232,240,0.6);
        overflow: hidden;
        transition: var(--transition);
    }
    .page-wrapper .table-card:hover { box-shadow: var(--shadow-lg); }

    .page-wrapper .table-header {
        padding: 22px 26px;
        border-bottom: 1px solid var(--line);
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 12px;
    }
    .page-wrapper .table-header-title {
        font-size: 18px;
        font-weight: 800;
        margin:0;
    }
    .page-wrapper .table-header-title i { color: var(--primary); margin-right: 8px; }
    .page-wrapper .table-header-description {
        color: var(--muted);
        font-size: 14px;
        margin:0;
    }

    .page-wrapper .table-responsive { overflow-x: auto; -webkit-overflow-scrolling: touch; border: 0; }
    .page-wrapper .data-table {
        width: 100%;
        min-width: 1150px;
        border-collapse: separate;
        border-spacing: 0;
        margin:0;
    }
    .page-wrapper .data-table > thead > tr > th {
        position: sticky;
        top: 0;
        z-index: 2;
        background: #f8fafc;
        color: #475569;
        font-size: 11px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.06em;
        padding: 14px 16px;
        border-top: 0;
        border-bottom: 2px solid var(--line);
        white-space: nowrap;
    }
    .page-wrapper .data-table > tbody > tr > td {
        padding: 16px 16px;
        vertical-align: middle;
        border-top: 0;
        border-bottom: 1px solid #edf1f5;
        color: var(--ink-light);
    }
    .page-wrapper .data-table tbody tr { transition: var(--transition); }
    .page-wrapper .data-table tbody tr:nth-child(even) { background: #fcfdff; }
    .page-wrapper .data-table tbody tr:hover { background: var(--primary-soft); }
    .page-wrapper .data-table tbody tr:last-child td { border-bottom: 0; }

    .page-wrapper .number-cell { width: 60px; color: var(--muted-light); font-weight: 800; text-align: center; }
    .page-wrapper .name-cell { min-width: 200px; max-width: 300px; font-weight: 700; color: var(--ink); }
    .page-wrapper .address-cell { min-width: 260px; max-width: 400px; line-height: 1.5; word-break: break-word; }
    .page-wrapper .phone-cell { min-width: 180px; }
    .page-wrapper .phone-link { text-decoration: none; color: var(--ink-light); font-weight: 600; }
    .page-wrapper .phone-link:hover { color: var(--primary); }
    .page-wrapper .email-cell { min-width: 200px; max-width: 280px; word-break: break-word; }
    .page-wrapper .email-link { color: var(--primary); text-decoration: none; font-weight: 600; }
    .page-wrapper .email-link:hover { text-decoration: underline; }
    .page-wrapper .website-cell { min-width: 180px; max-width: 240px; }
    .page-wrapper .website-link {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        max-width: 100%;
        text-decoration: none;
        font-weight: 600;
        color: var(--primary);
        background: var(--primary-soft);
        border: 1px solid var(--primary-light);
        border-radius: var(--radius-sm);
        padding: 6px 12px;
        transition: var(--transition);
    }
    .page-wrapper .website-link:hover { background: var(--primary-light); color: var(--primary-dark); }
    .page-wrapper .website-link-text { overflow: hidden; text-overflow: ellipsis; white-space: nowrap; max-width: 160px; }

    .page-wrapper .wa-badge, .page-wrapper .web-badge {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 4px 12px;
        border-radius: 999px;
        font-weight: 700;
        font-size: 11px;
        border: 1px solid transparent;
        width: fit-content;
        max-width: 100%;
        transition: var(--transition);
    }
    .page-wrapper .wa-badge-active, .page-wrapper .web-badge-active {
        background: #f0fdf4;
        color: #15803d;
        border-color: #bbf7d0;
    }
    .page-wrapper .wa-badge-inactive, .page-wrapper .web-badge-inactive {
        background: #fffbeb;
        color: #92400e;
        border-color: #fde68a;
    }
    .page-wrapper .wa-badge-unknown, .page-wrapper .web-badge-unknown {
        background: var(--bg);
        color: var(--muted);
        border-color: var(--line);
    }

    .page-wrapper .web-check-btn {
        font-size: 11px !important;
        padding: 4px 12px !important;
        border-radius: 999px !important;
        font-weight: 700;
        transition: var(--transition);
    }
    .page-wrapper .web-check-btn:hover { transform: scale(1.03); }

    .page-wrapper .maps-button {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        border-radius: var(--radius-sm);
        font-weight: 600;
        font-size: 13px;
        padding: 6px 14px;
    }

    .page-wrapper .action-cell { min-width: 140px; white-space: nowrap; }
    .page-wrapper .action-buttons { display: flex; align-items: center; gap: 6px; }
    .page-wrapper .edit-button, .page-wrapper .delete-button { border-radius: var(--radius-sm); font-weight: 600; padding: 6px 12px; }

    .page-wrapper .empty-state {
        padding: 80px 20px;
        text-align: center;
    }
    .page-wrapper .empty-icon {
        width: 88px; height: 88px;
        margin: 0 auto 20px;
        border-radius: var(--radius-md);
        display: flex;
        align-items: center;
        justify-content: center;
        background: var(--bg);
        color: var(--muted-light);
        font-size: 34px;
        transition: var(--transition);
    }
    .page-wrapper .empty-state:hover .empty-icon {
        background: var(--primary-soft);
        color: var(--primary);
        transform: scale(1.05);
    }
    .page-wrapper .empty-title { font-size: 20px; font-weight: 800; margin-bottom: 8px; }
    .page-wrapper .empty-description { color: var(--muted); margin-bottom: 20px; }
    .page-wrapper .text-empty { color: var(--muted-light); font-style: italic; }

    @media (max-width:768px) {
        .page-wrapper .header-card { padding: 24px 20px; }
        .page-wrapper .keyword-title { font-size: 22px; }
        .page-wrapper .table-header { flex-direction: column; align-items: stretch; gap: 8px; }
        .page-wrapper .header-actions { flex-wrap: wrap; }
        .page-wrapper .header-actions .btn { flex:1; min-width:120px; }
    }
</style>

<div class="page-wrapper">

    <!-- HEADER -->
    <div class="header-card">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-4">
            <div>
                <div class="header-label"><i class="fa-solid fa-folder-open"></i> Data Keyword</div>
                <h1 class="keyword-title"><i class="fa-solid fa-magnifying-glass"></i> <span><?= esc($keyword ?? 'Keyword tidak tersedia') ?></span></h1>
                <div class="data-badge"><i class="fa-solid fa-database"></i> <?= number_format((int)($totalData ?? 0)) ?> Data</div>
            </div>
            <div class="header-actions d-flex flex-wrap gap-2">
                <button type="button" id="checkAllWaButton" class="btn btn-outline-success"><i class="fa-brands fa-whatsapp"></i> Cek Semua WA</button>
                <button type="button" id="checkAllWebsiteButton" class="btn btn-outline-primary"><i class="fa-solid fa-globe"></i> Cek Semua Website</button>
                <a href="<?= site_url('scrapper/export-excel/' . rawurlencode($keyword ?? '')) ?>" class="btn btn-outline-success" title="Export data keyword ini ke Excel"><i class="fa-solid fa-file-excel"></i> Export Excel</a>
                <a href="<?= site_url('scrapper/edit-data') ?>" class="btn btn-secondary"><i class="fa-solid fa-arrow-left"></i> Kembali</a>
            </div>
        </div>
    </div>

    <!-- ALERT -->
    <?php if (session()->getFlashdata('success')): ?>
        <div class="alert alert-success alert-dismissible fade show mb-4 border-0 shadow-sm">
            <i class="fa-solid fa-circle-check me-2"></i> <?= esc(session()->getFlashdata('success')) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show mb-4 border-0 shadow-sm">
            <i class="fa-solid fa-circle-exclamation me-2"></i> <?= esc(session()->getFlashdata('error')) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- TABLE -->
    <div class="table-card">
        <div class="table-header">
            <h2 class="table-header-title"><i class="fa-solid fa-table"></i> Daftar Data Tempat</h2>
            <p class="table-header-description">Seluruh data hasil scraping untuk keyword: <strong><?= esc($keyword ?? '-') ?></strong></p>
        </div>

        <?php if (empty($items)): ?>
            <div class="empty-state">
                <div class="empty-icon"><i class="fa-solid fa-database"></i></div>
                <h3 class="empty-title">Data Tidak Ditemukan</h3>
                <p class="empty-description">Tidak ada data yang tersimpan untuk keyword ini.</p>
                <a href="<?= site_url('scrapper/edit-data') ?>" class="btn btn-primary"><i class="fa-solid fa-arrow-left me-2"></i> Kembali ke Daftar Keyword</a>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover data-table">
                    <thead>
                        <tr>
                            <th class="number-cell">#</th>
                            <th>Nama</th>
                            <th>Alamat</th>
                            <th>Nomor HP</th>
                            <th>Email</th>
                            <th>Website</th>
                            <th>Google Maps</th>
                            <th class="action-cell">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($items as $index => $item):
                        $id = (int)($item['id'] ?? 0);
                        $namaPerusahaan = trim((string)($item['nama_perusahaan'] ?? ''));
                        $alamat = trim((string)($item['alamat'] ?? ''));
                        $nomor = trim((string)($item['nomor'] ?? ''));
                        $email = trim((string)($item['email'] ?? ''));
                        $website = trim((string)($item['website'] ?? ''));
                        $mapsUrl = trim((string)($item['maps_url'] ?? ''));
                        $waStatus = trim((string)($item['wa_status'] ?? ''));
                        $websiteStatus = trim((string)($item['website_status'] ?? ''));
                    ?>
                        <tr>
                            <td class="number-cell"><?= $index + 1 ?></td>
                            <td class="name-cell"><?= $namaPerusahaan !== '' ? esc($namaPerusahaan) : '<span class="text-empty">-</span>' ?></td>
                            <td class="address-cell"><?= $alamat !== '' ? esc($alamat) : '<span class="text-empty">-</span>' ?></td>
                            <td class="phone-cell" data-item-id="<?= $id ?>">
                                <?php if ($nomor !== ''): ?>
                                    <div class="d-flex flex-column gap-1">
                                        <a href="tel:<?= esc($nomor) ?>" class="phone-link"><i class="fa-solid fa-phone me-1"></i> <?= esc($nomor) ?></a>
                                        <span class="wa-badge wa-status-badge <?= $waStatus === 'active' ? 'wa-badge-active' : ($waStatus === 'inactive' ? 'wa-badge-inactive' : 'wa-badge-unknown') ?>" title="<?= $waStatus === 'active' ? 'Nomor terdaftar di WhatsApp' : ($waStatus === 'inactive' ? 'Nomor tidak terdaftar di WhatsApp' : 'Status WhatsApp belum dicek') ?>">
                                            <i class="<?= $waStatus === 'active' ? 'fa-solid fa-circle-check' : ($waStatus === 'inactive' ? 'fa-solid fa-triangle-exclamation' : 'fa-regular fa-clock') ?>"></i>
                                            <span class="wa-status-label"><?= $waStatus === 'active' ? 'Nomor Terdaftar di WA' : ($waStatus === 'inactive' ? 'Nomor Tidak Terdaftar di WA' : 'Belum Dicek') ?></span>
                                        </span>
                                        <button type="button" class="btn btn-outline-secondary web-check-btn check-wa-button" data-id="<?= $id ?>"><i class="fa-solid fa-rotate"></i> Cek WA</button>
                                    </div>
                                <?php else: ?>
                                    <span class="text-empty">-</span>
                                <?php endif; ?>
                            </td>
                            <td class="email-cell">
                                <?php if ($email !== ''): ?>
                                    <a href="mailto:<?= esc($email) ?>" class="email-link"><i class="fa-solid fa-envelope me-1"></i> <?= esc($email) ?></a>
                                <?php else: ?>
                                    <span class="text-empty">-</span>
                                <?php endif; ?>
                            </td>
                            <td class="website-cell" data-item-id="<?= $id ?>">
                                <?php if ($website !== ''): ?>
                                    <div class="d-flex flex-column gap-1">
                                        <a href="<?= esc($website) ?>" target="_blank" rel="noopener noreferrer" class="website-link" title="<?= esc($website) ?>">
                                            <i class="fa-solid fa-globe"></i>
                                            <span class="website-link-text"><?= esc(preg_replace('#^https?://(www\.)?#i', '', rtrim($website, '/'))) ?></span>
                                        </a>
                                        <span class="web-badge web-status-badge <?= $websiteStatus === 'active' ? 'web-badge-active' : ($websiteStatus === 'inactive' ? 'web-badge-inactive' : 'web-badge-unknown') ?>" title="<?= $websiteStatus === 'active' ? 'Website dapat diakses' : ($websiteStatus === 'inactive' ? 'Website tidak dapat diakses' : 'Status website belum dicek') ?>">
                                            <i class="<?= $websiteStatus === 'active' ? 'fa-solid fa-circle-check' : ($websiteStatus === 'inactive' ? 'fa-solid fa-triangle-exclamation' : 'fa-regular fa-clock') ?>"></i>
                                            <span class="web-status-label"><?= $websiteStatus === 'active' ? 'Bisa Diakses' : ($websiteStatus === 'inactive' ? 'Tidak Bisa Diakses' : 'Belum Dicek') ?></span>
                                        </span>
                                        <button type="button" class="btn btn-outline-secondary web-check-btn check-website-button" data-id="<?= $id ?>"><i class="fa-solid fa-rotate"></i> Cek Website</button>
                                    </div>
                                <?php else: ?>
                                    <span class="text-empty">-</span>
                                <?php endif; ?>
                            </td>
                            <td class="maps-cell">
                                <?php if ($mapsUrl !== ''): ?>
                                    <a href="<?= esc($mapsUrl) ?>" target="_blank" rel="noopener noreferrer" class="btn btn-outline-success maps-button"><i class="fa-solid fa-map-location-dot"></i> Maps</a>
                                <?php else: ?>
                                    <span class="text-empty">-</span>
                                <?php endif; ?>
                            </td>
                            <td class="action-cell">
                                <?php if ($id > 0): ?>
                                    <div class="action-buttons">
                                        <a href="<?= site_url('scrapper/edit/' . $id) ?>" class="btn btn-primary edit-button"><i class="fa-solid fa-pen"></i> Edit</a>
                                        <form action="<?= site_url('scrapper/delete/' . $id) ?>" method="post" class="d-inline" onsubmit="return confirm('Yakin ingin menghapus data ini?');">
                                            <?= csrf_field() ?>
                                            <button type="submit" class="btn btn-outline-danger delete-button"><i class="fa-solid fa-trash"></i></button>
                                        </form>
                                    </div>
                                <?php else: ?>
                                    <span class="text-empty">-</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<?= csrf_field() ?>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
document.querySelectorAll('.page-wrapper [data-bs-dismiss="alert"]').forEach(btn => {
    btn.addEventListener('click', () => {
        const alert = btn.closest('.alert');
        if (alert) alert.remove();
    });
});

(function(){
    let csrfInput = document.querySelector('input[name^="csrf"]');
    let csrfName = csrfInput ? csrfInput.getAttribute('name') : null;
    let csrfHash = csrfInput ? csrfInput.value : null;

    function updateCsrf(data) {
        const newHash = (data && data.csrf && data.csrf.hash) ? data.csrf.hash : (data && data.csrf_hash ? data.csrf_hash : null);
        if (newHash && csrfInput) {
            csrfHash = newHash;
            csrfInput.value = newHash;
        }
    }

    function badgeMarkup(status, type) {
        const isActive = status === 'active';
        const isInactive = status === 'inactive';
        const cls = isActive ? (type === 'wa' ? 'wa-badge-active' : 'web-badge-active') : (isInactive ? (type === 'wa' ? 'wa-badge-inactive' : 'web-badge-inactive') : (type === 'wa' ? 'wa-badge-unknown' : 'web-badge-unknown'));
        const icon = isActive ? 'fa-solid fa-circle-check' : (isInactive ? 'fa-solid fa-triangle-exclamation' : 'fa-regular fa-clock');
        const label = isActive ? (type === 'wa' ? 'Nomor Terdaftar di WA' : 'Bisa Diakses') : (isInactive ? (type === 'wa' ? 'Nomor Tidak Terdaftar di WA' : 'Tidak Bisa Diakses') : 'Belum Dicek');
        const title = isActive ? (type === 'wa' ? 'Nomor terdaftar di WhatsApp' : 'Website dapat diakses') : (isInactive ? (type === 'wa' ? 'Nomor tidak terdaftar di WhatsApp' : 'Website tidak dapat diakses') : (type === 'wa' ? 'Status WhatsApp belum dicek' : 'Status website belum dicek'));
        return { cls, icon, label, title };
    }

    function applyBadge(cell, status, type) {
        const badge = cell.querySelector(type === 'wa' ? '.wa-status-badge' : '.web-status-badge');
        if (!badge) return;
        const info = badgeMarkup(status, type);
        badge.className = (type === 'wa' ? 'wa-badge wa-status-badge ' : 'web-badge web-status-badge ') + info.cls;
        badge.title = info.title;
        badge.innerHTML = '<i class="' + info.icon + '"></i><span class="' + (type === 'wa' ? 'wa-status-label' : 'web-status-label') + '">' + info.label + '</span>';
    }

    async function checkOne(id, btn, type, silent = false) {
        const cell = document.querySelector((type === 'wa' ? '.phone-cell' : '.website-cell') + '[data-item-id="' + id + '"]');
        const originalHtml = btn ? btn.innerHTML : '';
        if (btn) { btn.disabled = true; btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Mengecek...'; }
        try {
            const formData = new FormData();
            if (csrfName && csrfHash) formData.append(csrfName, csrfHash);
            const url = type === 'wa'
                ? '<?= site_url("scrapper/check-wa") ?>/' + id
                : '<?= site_url("scrapper/check-website") ?>/' + id;
            const headers = { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' };
            if (csrfHash) headers['X-CSRF-TOKEN'] = csrfHash;

            const response = await fetch(url, { method: 'POST', body: formData, headers: headers });
            const text = await response.text();
            let data;
            try {
                data = JSON.parse(text);
            } catch (err) {
                throw new Error('Route atau respon server tidak valid (Status: ' + response.status + ')');
            }

            updateCsrf(data);
            if (data.success && cell) {
                applyBadge(cell, type === 'wa' ? data.wa_status : data.website_status, type);
            } else if (!data.success && !silent) {
                alert(data.message || 'Gagal melakukan pengecekan.');
            }
            return data;
        } catch (e) {
            console.error('Error:', e);
            if (!silent) alert('Gagal mengecek: ' + e.message);
            return { success: false, message: e.message };
        } finally {
            if (btn) { btn.disabled = false; btn.innerHTML = originalHtml; }
        }
    }

    // Per-baris
    document.querySelectorAll('.check-wa-button').forEach(btn => btn.addEventListener('click', () => { const id = btn.getAttribute('data-id'); if (id) checkOne(id, btn, 'wa'); }));
    document.querySelectorAll('.check-website-button').forEach(btn => btn.addEventListener('click', () => { const id = btn.getAttribute('data-id'); if (id) checkOne(id, btn, 'web'); }));

    // Cek Semua WA
    const checkAllWa = document.getElementById('checkAllWaButton');
    if (checkAllWa) {
        checkAllWa.addEventListener('click', async () => {
            const buttons = Array.from(document.querySelectorAll('.check-wa-button'));
            if (!buttons.length) return;
            const originalHtml = checkAllWa.innerHTML;
            checkAllWa.disabled = true;
            for (let i = 0; i < buttons.length; i++) {
                checkAllWa.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Mengecek (' + (i+1) + '/' + buttons.length + ')...';
                const id = buttons[i].getAttribute('data-id');
                if (id) await checkOne(id, buttons[i], 'wa', true);
            }
            checkAllWa.disabled = false;
            checkAllWa.innerHTML = originalHtml;
        });
    }

    // Cek Semua Website
    const checkAllWeb = document.getElementById('checkAllWebsiteButton');
    if (checkAllWeb) {
        checkAllWeb.addEventListener('click', async () => {
            const buttons = Array.from(document.querySelectorAll('.check-website-button'));
            if (!buttons.length) return;
            const originalHtml = checkAllWeb.innerHTML;
            checkAllWeb.disabled = true;
            for (let i = 0; i < buttons.length; i++) {
                checkAllWeb.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Mengecek (' + (i+1) + '/' + buttons.length + ')...';
                const id = buttons[i].getAttribute('data-id');
                if (id) await checkOne(id, buttons[i], 'web', true);
            }
            checkAllWeb.disabled = false;
            checkAllWeb.innerHTML = originalHtml;
        });
    }
})();
</script>
<?= $this->endSection() ?>