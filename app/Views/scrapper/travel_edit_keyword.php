<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data - <?= esc($keyword ?? 'Keyword') ?></title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
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
        * { box-sizing: border-box; }
        html { scroll-behavior: smooth; }
        body {
            min-height: 100vh;
            background: var(--bg);
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            color: var(--ink);
            -webkit-font-smoothing: antialiased;
            background-image: radial-gradient(circle at 5% 0%, rgba(37,99,235,0.05) 0%, transparent 50%),
                              radial-gradient(circle at 100% 10%, rgba(37,99,235,0.05) 0%, transparent 50%);
        }
        .page-wrapper { max-width: 1500px; margin:0 auto; padding:32px 24px 60px; }
        @media (max-width:576px) { .page-wrapper { padding:20px 16px 40px; } }

        /* HEADER */
        .header-card {
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
        .header-card::before {
            content: '';
            position: absolute;
            top: 0; left: 0; right: 0;
            height: 4px;
            background: linear-gradient(90deg, var(--primary), #60a5fa, #a78bfa);
        }
        .header-card:hover { box-shadow: var(--shadow-lg); }

        .header-label {
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
        .header-label i { color: var(--primary); }

        .keyword-title {
            display: flex;
            align-items: center;
            gap: 12px;
            font-size: 28px;
            font-weight: 800;
            margin: 0 0 12px 0;
            word-break: break-word;
        }
        .keyword-title i { color: var(--primary); font-size: 26px; }

        .data-badge {
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

        .header-actions .btn {
            border-radius: var(--radius-sm);
            font-weight: 600;
            padding: 10px 18px;
            transition: var(--transition);
        }
        .header-actions .btn:hover { transform: translateY(-2px); box-shadow: var(--shadow-sm); }

        /* TABLE CARD */
        .table-card {
            background: var(--surface);
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-md);
            border: 1px solid rgba(226,232,240,0.6);
            overflow: hidden;
            transition: var(--transition);
        }
        .table-card:hover { box-shadow: var(--shadow-lg); }

        .table-header {
            padding: 22px 26px;
            border-bottom: 1px solid var(--line);
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 12px;
        }
        .table-header-title {
            font-size: 18px;
            font-weight: 800;
            margin:0;
        }
        .table-header-title i { color: var(--primary); margin-right: 8px; }
        .table-header-description {
            color: var(--muted);
            font-size: 14px;
            margin:0;
        }

        .table-responsive { overflow-x: auto; -webkit-overflow-scrolling: touch; }
        .data-table {
            width: 100%;
            min-width: 1150px;
            border-collapse: separate;
            border-spacing: 0;
            margin:0;
        }
        .data-table thead th {
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
            border-bottom: 2px solid var(--line);
            white-space: nowrap;
        }
        .data-table tbody td {
            padding: 16px 16px;
            vertical-align: middle;
            border-bottom: 1px solid #edf1f5;
            color: var(--ink-light);
        }
        .data-table tbody tr { transition: var(--transition); }
        .data-table tbody tr:nth-child(even) { background: #fcfdff; }
        .data-table tbody tr:hover { background: var(--primary-soft); }
        .data-table tbody tr:last-child td { border-bottom: 0; }

        .number-cell { width: 60px; color: var(--muted-light); font-weight: 800; text-align: center; }
        .name-cell { min-width: 200px; max-width: 300px; font-weight: 700; color: var(--ink); }
        .address-cell { min-width: 260px; max-width: 400px; line-height: 1.5; word-break: break-word; }
        .phone-cell { min-width: 180px; }
        .phone-link { text-decoration: none; color: var(--ink-light); font-weight: 600; }
        .phone-link:hover { color: var(--primary); }
        .email-cell { min-width: 200px; max-width: 280px; word-break: break-word; }
        .email-link { color: var(--primary); text-decoration: none; font-weight: 600; }
        .email-link:hover { text-decoration: underline; }
        .website-cell { min-width: 180px; max-width: 240px; }
        .website-link {
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
        .website-link:hover { background: var(--primary-light); color: var(--primary-dark); }
        .website-link-text { overflow: hidden; text-overflow: ellipsis; white-space: nowrap; max-width: 160px; }

        .wa-badge, .web-badge {
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
        .wa-badge-active, .web-badge-active {
            background: #f0fdf4;
            color: #15803d;
            border-color: #bbf7d0;
        }
        .wa-badge-inactive, .web-badge-inactive {
            background: #fffbeb;
            color: #92400e;
            border-color: #fde68a;
        }
        .wa-badge-unknown, .web-badge-unknown {
            background: var(--bg);
            color: var(--muted);
            border-color: var(--line);
        }

        .web-check-btn {
            font-size: 11px !important;
            padding: 4px 12px !important;
            border-radius: 999px !important;
            font-weight: 700;
            transition: var(--transition);
        }
        .web-check-btn:hover { transform: scale(1.03); }

        .maps-button {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            border-radius: var(--radius-sm);
            font-weight: 600;
            font-size: 13px;
            padding: 6px 14px;
        }

        .action-cell { min-width: 140px; white-space: nowrap; }
        .action-buttons { display: flex; align-items: center; gap: 6px; }
        .edit-button, .delete-button { border-radius: var(--radius-sm); font-weight: 600; padding: 6px 12px; }

        .empty-state {
            padding: 80px 20px;
            text-align: center;
        }
        .empty-icon {
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
        .empty-state:hover .empty-icon {
            background: var(--primary-soft);
            color: var(--primary);
            transform: scale(1.05);
        }
        .empty-title { font-size: 20px; font-weight: 800; margin-bottom: 8px; }
        .empty-description { color: var(--muted); margin-bottom: 20px; }
        .text-empty { color: var(--muted-light); font-style: italic; }

        @media (max-width:768px) {
            .header-card { padding: 24px 20px; }
            .keyword-title { font-size: 22px; }
            .table-header { flex-direction: column; align-items: stretch; gap: 8px; }
            .header-actions { flex-wrap: wrap; }
            .header-actions .btn { flex:1; min-width:120px; }
        }
    </style>
</head>
<body>

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
                <a href="<?= site_url('travel/export-excel/' . rawurlencode($keyword ?? '')) ?>" class="btn btn-outline-success" title="Export data keyword ini ke Excel"><i class="fa-solid fa-file-excel"></i> Export Excel</a>
                <a href="<?= site_url('travel/edit-data') ?>" class="btn btn-secondary"><i class="fa-solid fa-arrow-left"></i> Kembali</a>
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
                <a href="<?= site_url('travel/edit-data') ?>" class="btn btn-primary"><i class="fa-solid fa-arrow-left me-2"></i> Kembali ke Daftar Keyword</a>
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
                        $nama = trim((string)($item['nama'] ?? ''));
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
                            <td class="name-cell"><?= $nama !== '' ? esc($nama) : '<span class="text-empty">-</span>' ?></td>
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
                                        <a href="<?= site_url('travel/edit/' . $id) ?>" class="btn btn-primary edit-button"><i class="fa-solid fa-pen"></i> Edit</a>
                                        <form action="<?= site_url('travel/delete/' . $id) ?>" method="post" class="d-inline" onsubmit="return confirm('Yakin ingin menghapus data ini?');">
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
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Sama seperti sebelumnya, fungsi untuk cek WA dan Website (tidak diubah)
(function(){
    let csrfInput = document.querySelector('input[name^="csrf"]');
    let csrfName = csrfInput ? csrfInput.getAttribute('name') : null;
    let csrfHash = csrfInput ? csrfInput.value : null;

    function updateCsrf(csrf) { if (csrf && csrf.hash && csrfInput) { csrfHash = csrf.hash; csrfInput.value = csrf.hash; } }

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

    async function checkOne(id, btn, type) {
        const cell = document.querySelector((type === 'wa' ? '.phone-cell' : '.website-cell') + '[data-item-id="' + id + '"]');
        const originalHtml = btn ? btn.innerHTML : '';
        if (btn) { btn.disabled = true; btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Mengecek...'; }
        try {
            const formData = new FormData();
            if (csrfName && csrfHash) formData.append(csrfName, csrfHash);
            const url = type === 'wa' ? '<?= site_url("travel/check-wa") ?>/' + id : '<?= site_url("travel/check-website") ?>/' + id;
            const response = await fetch(url, { method: 'POST', body: formData, headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' } });
            const data = await response.json();
            updateCsrf(data.csrf);
            if (data.success && cell) {
                applyBadge(cell, type === 'wa' ? data.wa_status : data.website_status, type);
            } else if (!data.success) { console.warn('Gagal:', data.message); }
            return data;
        } catch (e) { console.error('Error:', e); return { success: false, message: e.message }; }
        finally { if (btn) { btn.disabled = false; btn.innerHTML = originalHtml; } }
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
                if (id) await checkOne(id, buttons[i], 'wa');
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
                if (id) await checkOne(id, buttons[i], 'web');
            }
            checkAllWeb.disabled = false;
            checkAllWeb.innerHTML = originalHtml;
        });
    }
})();
</script>
</body>
</html>