<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Data Scrape</title>

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome 6 -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet">
    <!-- Google Font Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,400;14..32,500;14..32,600;14..32,700;14..32,800;14..32,900&display=swap" rel="stylesheet">

    <style>
        /* ===== VARIABLES ===== */
        :root {
            --primary: #2563eb;
            --primary-dark: #1d4ed8;
            --primary-light: #dbeafe;
            --primary-soft: #eff6ff;
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

        * { box-sizing: border-box; margin:0; padding:0; }
        html { scroll-behavior: smooth; }
        body {
            min-height: 100vh;
            background: var(--bg);
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            color: var(--ink);
            -webkit-font-smoothing: antialiased;
            background-image: radial-gradient(circle at 0% 0%, rgba(37,99,235,0.05) 0%, transparent 50%),
                              radial-gradient(circle at 100% 100%, rgba(37,99,235,0.05) 0%, transparent 50%);
        }

        /* ===== UTILITY ===== */
        .text-gradient {
            background: linear-gradient(135deg, var(--primary), #60a5fa);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        /* ===== PAGE WRAPPER ===== */
        .page-wrapper {
            max-width: 1280px;
            margin: 0 auto;
            padding: 32px 24px 60px;
        }
        @media (max-width:576px) {
            .page-wrapper { padding: 20px 16px 40px; }
        }

        /* ===== HEADER ===== */
        .header-card {
            background: var(--surface);
            border-radius: var(--radius-lg);
            padding: 32px 36px;
            margin-bottom: 28px;
            position: relative;
            overflow: hidden;
            box-shadow: var(--shadow-md);
            border: 1px solid rgba(226,232,240,0.6);
            transition: var(--transition);
        }
        .header-card::before {
            content: '';
            position: absolute;
            top: 0; left: 0; right: 0;
            height: 4px;
            background: linear-gradient(90deg, var(--primary), #60a5fa, #a78bfa);
            border-radius: var(--radius-lg) var(--radius-lg) 0 0;
        }
        .header-card:hover { box-shadow: var(--shadow-lg); }

        .page-title {
            font-size: 28px;
            font-weight: 800;
            letter-spacing: -0.025em;
            margin: 0;
        }
        .page-title i { color: var(--primary); margin-right: 10px; }
        .page-description {
            color: var(--muted);
            font-size: 15px;
            margin: 4px 0 0 0;
            line-height: 1.6;
        }
        .btn-back {
            background: var(--bg);
            border: 1px solid var(--line);
            color: var(--ink-light);
            font-weight: 600;
            padding: 10px 22px;
            border-radius: var(--radius-sm);
            font-size: 14px;
            transition: var(--transition);
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        .btn-back:hover {
            background: var(--primary-soft);
            border-color: var(--primary);
            color: var(--primary);
            transform: translateY(-1px);
            box-shadow: var(--shadow-sm);
        }

        /* ===== ALERT ===== */
        .alert-custom {
            border: none;
            border-radius: var(--radius-md);
            padding: 16px 24px;
            font-weight: 500;
            box-shadow: var(--shadow-sm);
        }
        .alert-custom.alert-success {
            background: #ecfdf5;
            color: #065f46;
            border-left: 5px solid #10b981;
        }
        .alert-custom.alert-danger {
            background: #fef2f2;
            color: #991b1b;
            border-left: 5px solid #ef4444;
        }

        /* ===== STAT CARDS ===== */
        .stat-card {
            background: var(--surface);
            border-radius: var(--radius-md);
            padding: 22px 24px;
            height: 100%;
            box-shadow: var(--shadow-sm);
            border: 1px solid rgba(226,232,240,0.6);
            transition: var(--transition);
            display: flex;
            align-items: center;
            gap: 18px;
        }
        .stat-card:hover {
            transform: translateY(-3px);
            box-shadow: var(--shadow-md);
            border-color: var(--primary-light);
        }
        .stat-icon {
            width: 52px; height: 52px;
            border-radius: var(--radius-sm);
            display: flex;
            align-items: center;
            justify-content: center;
            background: var(--primary-soft);
            color: var(--primary);
            font-size: 22px;
            flex-shrink: 0;
            transition: var(--transition);
        }
        .stat-card:hover .stat-icon {
            background: var(--primary);
            color: #fff;
            transform: scale(1.05);
        }
        .stat-number { font-size: 26px; font-weight: 800; line-height:1.2; }
        .stat-label { font-size: 13px; font-weight: 600; color: var(--muted); text-transform: uppercase; letter-spacing:0.03em; margin:0; }

        /* ===== KEYWORD CARD ===== */
        .keyword-card {
            background: var(--surface);
            border-radius: var(--radius-md);
            border: 1px solid rgba(226,232,240,0.6);
            box-shadow: var(--shadow-sm);
            transition: var(--transition);
            height: 100%;
            overflow: hidden;
            position: relative;
        }
        .keyword-card::after {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(135deg, rgba(37,99,235,0.02), transparent 60%);
            pointer-events: none;
            opacity: 0;
            transition: var(--transition);
        }
        .keyword-card:hover {
            transform: translateY(-6px);
            box-shadow: var(--shadow-lg);
            border-color: var(--primary-light);
        }
        .keyword-card:hover::after { opacity: 1; }

        .keyword-link {
            text-decoration: none;
            color: inherit;
            display: block;
            height: 100%;
            position: relative;
            z-index: 1;
        }
        .keyword-card-body { padding: 26px 28px 24px; display: flex; flex-direction: column; height: 100%; }

        .keyword-icon-wrap {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 18px;
        }
        .keyword-icon {
            width: 52px; height: 52px;
            border-radius: var(--radius-sm);
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, var(--primary-soft), var(--primary-light));
            color: var(--primary);
            font-size: 20px;
            transition: var(--transition);
        }
        .keyword-card:hover .keyword-icon {
            background: var(--primary);
            color: #fff;
            transform: scale(1.05);
        }
        .keyword-arrow {
            width: 40px; height: 40px;
            border-radius: var(--radius-sm);
            display: flex;
            align-items: center;
            justify-content: center;
            background: var(--primary);
            color: #fff;
            font-size: 16px;
            transition: var(--transition);
            opacity: 0.8;
        }
        .keyword-card:hover .keyword-arrow {
            transform: translateX(5px);
            background: var(--primary-dark);
            opacity: 1;
        }

        .keyword-name {
            font-size: 18px;
            font-weight: 700;
            color: var(--ink);
            line-height: 1.4;
            margin-bottom: 16px;
            word-break: break-word;
            flex: 1;
        }
        .keyword-meta {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            padding-top: 16px;
            border-top: 1px solid var(--line);
        }
        .keyword-count {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-size: 13px;
            font-weight: 700;
            color: var(--ink-light);
            background: var(--bg);
            padding: 6px 14px;
            border-radius: 999px;
            transition: var(--transition);
        }
        .keyword-card:hover .keyword-count {
            background: var(--primary-soft);
            color: var(--primary);
        }
        .keyword-view-label {
            font-size: 13px;
            color: var(--muted-light);
            font-weight: 500;
            transition: var(--transition);
        }
        .keyword-card:hover .keyword-view-label { color: var(--primary); }

        /* ===== EXPORT BUTTON ===== */
        .btn-export {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            width: 100%;
            padding: 10px;
            border-radius: var(--radius-sm);
            font-weight: 600;
            font-size: 14px;
            border: 1px solid var(--line);
            background: var(--surface);
            color: var(--ink-light);
            transition: var(--transition);
            text-decoration: none;
            margin-top: 12px;
        }
        .btn-export:hover {
            background: #ecfdf5;
            border-color: #10b981;
            color: #065f46;
            transform: translateY(-2px);
            box-shadow: var(--shadow-sm);
        }
        .btn-export i { color: #10b981; }

        /* ===== EMPTY STATE ===== */
        .empty-state {
            background: var(--surface);
            border-radius: var(--radius-lg);
            padding: 80px 24px;
            text-align: center;
            box-shadow: var(--shadow-sm);
            border: 1px solid rgba(226,232,240,0.6);
            transition: var(--transition);
        }
        .empty-state:hover { box-shadow: var(--shadow-md); }
        .empty-icon {
            width: 96px; height: 96px;
            margin: 0 auto 24px;
            border-radius: var(--radius-md);
            display: flex;
            align-items: center;
            justify-content: center;
            background: var(--bg);
            color: var(--muted-light);
            font-size: 40px;
            transition: var(--transition);
        }
        .empty-state:hover .empty-icon {
            background: var(--primary-soft);
            color: var(--primary);
            transform: scale(1.05);
        }
        .btn-primary-custom {
            background: var(--primary);
            border: none;
            color: #fff;
            font-weight: 700;
            padding: 12px 32px;
            border-radius: var(--radius-sm);
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            transition: var(--transition);
            font-size: 15px;
        }
        .btn-primary-custom:hover {
            background: var(--primary-dark);
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(37,99,235,0.3);
            color: #fff;
        }

        /* ===== RESPONSIVE ===== */
        @media (max-width:576px) {
            .header-card { padding: 24px 20px; }
            .page-title { font-size: 22px; }
            .keyword-card-body { padding: 20px; }
            .keyword-name { font-size: 16px; }
            .empty-state { padding: 60px 16px; }
        }

        /* ===== ANIMATIONS ===== */
        @keyframes fadeInUp {
            from { opacity:0; transform: translateY(20px); }
            to { opacity:1; transform: translateY(0); }
        }
        .keyword-card { animation: fadeInUp 0.5s ease forwards; }
        .keyword-card:nth-child(1) { animation-delay:0.05s; }
        .keyword-card:nth-child(2) { animation-delay:0.10s; }
        .keyword-card:nth-child(3) { animation-delay:0.15s; }
        .keyword-card:nth-child(4) { animation-delay:0.20s; }
        .keyword-card:nth-child(5) { animation-delay:0.25s; }
        .keyword-card:nth-child(6) { animation-delay:0.30s; }
        .keyword-card:nth-child(7) { animation-delay:0.35s; }
        .keyword-card:nth-child(8) { animation-delay:0.40s; }
        .keyword-card:nth-child(9) { animation-delay:0.45s; }
        .keyword-card:nth-child(10) { animation-delay:0.50s; }
    </style>
</head>
<body>

<div class="page-wrapper">

    <!-- HEADER -->
    <div class="header-card">
        <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">
            <div>
                <div class="d-flex align-items-center gap-2">
                    <i class="fa-solid fa-pen-to-square text-primary fs-3"></i>
                    <h1 class="page-title">Edit Data Scrape</h1>
                </div>
                <p class="page-description">Kelola keyword scraping dan buka kumpulan data yang tersimpan.</p>
            </div>
            <a href="<?= site_url('travel') ?>" class="btn-back">
                <i class="fa-solid fa-arrow-left"></i> Kembali ke Scraper
            </a>
        </div>
    </div>

    <!-- ALERT -->
    <?php if (session()->getFlashdata('success')): ?>
        <div class="alert alert-custom alert-success alert-dismissible fade show mb-4" role="alert">
            <i class="fa-solid fa-circle-check"></i> <?= esc(session()->getFlashdata('success')) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-custom alert-danger alert-dismissible fade show mb-4" role="alert">
            <i class="fa-solid fa-circle-exclamation"></i> <?= esc(session()->getFlashdata('error')) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- STATISTICS -->
    <div class="row g-3 mb-4">
        <div class="col-12 col-sm-6 col-lg-4">
            <div class="stat-card">
                <div class="stat-icon"><i class="fa-solid fa-magnifying-glass"></i></div>
                <div>
                    <div class="stat-number"><?= number_format((int)($totalKeyword ?? 0)) ?></div>
                    <div class="stat-label">Total Keyword</div>
                </div>
            </div>
        </div>
    </div>

    <!-- KEYWORD GRID -->
    <?php if (empty($keywords)): ?>
        <div class="empty-state">
            <div class="empty-icon"><i class="fa-solid fa-database"></i></div>
            <h4 class="fw-bold">Belum Ada Data</h4>
            <p class="text-muted mb-4">Belum ada data scrape yang tersimpan di database.</p>
            <a href="<?= site_url('travel') ?>" class="btn-primary-custom">
                <i class="fa-solid fa-magnifying-glass"></i> Mulai Scraping
            </a>
        </div>
    <?php else: ?>
        <div class="mb-3">
            <h5 class="fw-bold mb-1">Pilih Keyword</h5>
            <p class="text-muted mb-0">Klik card untuk melihat seluruh data.</p>
        </div>
        <div class="row g-4">
            <?php foreach ($keywords as $item):
                $keyword = trim((string)($item['keyword'] ?? ''));
                $total = (int)($item['total_data'] ?? 0);
                if ($keyword === '') continue;
            ?>
                <div class="col-12 col-md-6 col-xl-4">
                    <div class="keyword-card">
                        <a href="<?= site_url('travel/edit-data/' . rawurlencode($keyword)) ?>" class="keyword-link">
                            <div class="keyword-card-body">
                                <div class="keyword-icon-wrap">
                                    <div class="keyword-icon"><i class="fa-solid fa-magnifying-glass"></i></div>
                                    <div class="keyword-arrow"><i class="fa-solid fa-arrow-right"></i></div>
                                </div>
                                <div class="keyword-name"><?= esc($keyword) ?></div>
                                <div class="keyword-meta">
                                    <span class="keyword-count"><i class="fa-solid fa-database"></i> <?= number_format($total) ?> Data</span>
                                    <span class="keyword-view-label">Lihat data</span>
                                </div>
                            </div>
                        </a>
                        <a href="<?= site_url('travel/export-excel/' . rawurlencode($keyword)) ?>" class="btn-export" title="Export data keyword ini ke Excel">
                            <i class="fa-solid fa-file-excel"></i> Export Excel
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>