
<?= $this->extend('layouts/main') ?>

<?= $this->section('page_title') ?>Analisis Produk AI<?= $this->endSection() ?>
<?= $this->section('page_subtitle') ?>Konsol cerdas untuk analisis produk perusahaan berbasis Google Gemini<?= $this->endSection() ?>

<?= $this->section('breadcrumb') ?>
<li><a href="<?= site_url('/') ?>"><i class="fa fa-dashboard"></i> Home</a></li>
<li class="active">Analisis Produk AI</li>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<style>
/* =====================================================
   MODERN AI DASHBOARD
===================================================== */

.ai-dashboard {
    --ai-primary: #635bdb;
    --ai-primary-dark: #5046c7;
    --ai-bg: #f6f7fb;
    --ai-border: #eaecf2;
    --ai-text: #25283b;
    --ai-muted: #85899b;
    color: var(--ai-text);
}

.ai-dashboard .app-card {
    background: #fff;
    border: 1px solid var(--ai-border);
    border-radius: 16px;
    box-shadow: 0 3px 15px rgba(35, 38, 75, .035);
    transition: .25s ease;
    overflow: hidden;
}

.ai-dashboard .app-card:hover {
    box-shadow: 0 8px 25px rgba(35, 38, 75, .065);
}

.ai-dashboard .section-heading {
    font-size: 15px;
    font-weight: 700;
    color: var(--ai-text);
}

.ai-dashboard .section-description {
    font-size: 12px;
    color: var(--ai-muted);
    margin-top: 3px;
}

.ai-dashboard .icon-box {
    width: 42px;
    height: 42px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 12px;
    background: #f0efff;
    color: var(--ai-primary);
    font-size: 17px;
    flex-shrink: 0;
}

.ai-dashboard .ai-label {
    font-size: 11px;
    font-weight: 700;
    color: #85899b;
    text-transform: uppercase;
    letter-spacing: .7px;
}

/* HEADER */

.ai-dashboard .dashboard-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 15px;
    margin-bottom: 25px;
}

.ai-dashboard .dashboard-title {
    font-size: 25px;
    font-weight: 800;
    letter-spacing: -.8px;
    margin: 0;
    color: #25283b;
}

.ai-dashboard .dashboard-subtitle {
    color: var(--ai-muted);
    font-size: 13px;
    margin-top: 7px;
}

.ai-dashboard .system-status {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 9px 14px;
    border-radius: 30px;
    background: #eafaf1;
    color: #16864d;
    font-size: 12px;
    font-weight: 700;
    white-space: nowrap;
}

.ai-dashboard .status-dot {
    width: 8px;
    height: 8px;
    background: #20b66b;
    border-radius: 50%;
    box-shadow: 0 0 0 4px rgba(32,182,107,.12);
}

/* MODE SELECTOR */

.ai-dashboard .mode-selector {
    position: relative;
    display: block;
    padding: 15px;
    border: 1.5px solid var(--ai-border);
    border-radius: 13px;
    cursor: pointer;
    background: #fff;
    transition: all .2s ease;
}

.ai-dashboard .mode-selector:hover {
    border-color: #b6b1f7;
    background: #fafaff;
    transform: translateY(-1px);
}

.ai-dashboard .mode-selector input {
    position: absolute;
    opacity: 0;
    pointer-events: none;
}

.ai-dashboard .mode-selector:has(input:checked) {
    border-color: var(--ai-primary);
    background: #f5f4ff;
    box-shadow: 0 0 0 3px rgba(99,91,219,.08);
}

.ai-dashboard .mode-icon {
    width: 38px;
    height: 38px;
    border-radius: 10px;
    display: flex;
    justify-content: center;
    align-items: center;
    background: #f2f3f8;
    color: #85899b;
    font-size: 15px;
    transition: .2s;
}

.ai-dashboard .mode-selector:has(input:checked) .mode-icon {
    background: var(--ai-primary);
    color: #fff;
}

.ai-dashboard .mode-title {
    font-size: 13px;
    font-weight: 700;
    color: #34364b;
}

.ai-dashboard .mode-description {
    font-size: 11px;
    color: var(--ai-muted);
    margin-top: 4px;
}

.ai-dashboard .mode-check {
    color: var(--ai-primary);
    opacity: 0;
    transition: .2s;
}

.ai-dashboard .mode-selector:has(input:checked) .mode-check {
    opacity: 1;
}

/* COMPANY SELECTION */

.ai-dashboard .selection-panel {
    padding: 16px;
    border: 1px solid var(--ai-border);
    background: #f9faff;
    border-radius: 13px;
}

.ai-dashboard .form-control,
.ai-dashboard .form-select {
    border-color: #e6e8ef;
    border-radius: 9px;
    font-size: 13px;
    min-height: 39px;
}

.ai-dashboard .form-control:focus,
.ai-dashboard .form-select:focus {
    border-color: var(--ai-primary);
    box-shadow: 0 0 0 3px rgba(99,91,219,.10);
}

.ai-dashboard .input-group-text {
    border-color: #e6e8ef;
    border-radius: 9px 0 0 9px;
}

.ai-dashboard .multi-company-item {
    padding: 9px 10px;
    border-radius: 8px;
    transition: .15s;
}

.ai-dashboard .multi-company-item:hover {
    background: #f3f2ff;
}

.ai-dashboard .multi-company-item .form-check-input {
    cursor: pointer;
}

.ai-dashboard .multi-company-item .form-check-input:checked {
    background-color: var(--ai-primary);
    border-color: var(--ai-primary);
}

/* BUTTONS */

.ai-dashboard .btn-primary {
    background: var(--ai-primary);
    border-color: var(--ai-primary);
}

.ai-dashboard .btn-primary:hover {
    background: var(--ai-primary-dark);
    border-color: var(--ai-primary-dark);
}

.ai-dashboard .btn-start {
    min-height: 48px;
    border-radius: 11px;
    font-size: 14px;
    font-weight: 700;
    box-shadow: 0 5px 12px rgba(99,91,219,.2);
    transition: .2s;
}

.ai-dashboard .btn-start:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 17px rgba(99,91,219,.25);
}

.ai-dashboard .btn-action {
    border-radius: 10px;
    font-size: 13px;
    font-weight: 700;
    min-height: 40px;
}

/* STATISTICS */

.ai-dashboard .stat-card {
    padding: 17px 15px;
    height: 100%;
    position: relative;
}

.ai-dashboard .stat-icon {
    width: 35px;
    height: 35px;
    display: flex;
    justify-content: center;
    align-items: center;
    border-radius: 10px;
    font-size: 14px;
    margin-bottom: 13px;
}

.ai-dashboard .stat-number {
    font-size: 27px;
    line-height: 1.2;
    font-weight: 800;
    color: #292b40;
    margin: 0;
}

.ai-dashboard .stat-label {
    font-size: 11px;
    font-weight: 600;
    color: var(--ai-muted);
}

/* PROGRESS */

.ai-dashboard .progress-card {
    position: relative;
    overflow: hidden;
    background: linear-gradient(120deg, #5148c8, #7069ed 65%, #5589e9);
    border: none;
    color: white;
}

.ai-dashboard .progress-card::after {
    content: "";
    position: absolute;
    width: 200px;
    height: 200px;
    right: -75px;
    top: -100px;
    border: 30px solid rgba(255,255,255,.07);
    border-radius: 50%;
    pointer-events: none;
}

.ai-dashboard .progress-inner {
    position: relative;
    z-index: 1;
    padding: 23px;
}

.ai-dashboard .progress-title {
    font-size: 17px;
    font-weight: 750;
}

.ai-dashboard .progress-status {
    font-size: 12px;
    color: rgba(255,255,255,.75);
}

.ai-dashboard .progress-badge {
    background: rgba(255,255,255,.17);
    color: white;
    border: 1px solid rgba(255,255,255,.2);
    border-radius: 30px;
    padding: 8px 13px;
    font-size: 12px;
    font-weight: 700;
}

.ai-dashboard .progress {
    height: 10px;
    background: rgba(255,255,255,.2);
    border-radius: 30px;
}

.ai-dashboard .progress-bar {
    border-radius: 30px;
    transition: width .4s ease;
}

/* TERMINAL */

.ai-dashboard .terminal-card {
    background: #171a2b;
    border-radius: 15px;
    overflow: hidden;
    border: 1px solid #25283b;
}

.ai-dashboard .terminal-header {
    background: #202438;
    padding: 14px 17px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    border-bottom: 1px solid #303449;
}

.ai-dashboard .terminal-dots {
    display: flex;
    gap: 5px;
}

.ai-dashboard .terminal-dots span {
    height: 9px;
    width: 9px;
    border-radius: 50%;
}

.ai-dashboard #terminalConsole {
    height: 335px;
    overflow-y: auto;
    padding: 17px;
    background: #171a2b;
    color: #d8dbea;
    font-family: 'Fira Code', Consolas, monospace;
    font-size: 11px;
    line-height: 1.8;
}

.ai-dashboard #terminalConsole::-webkit-scrollbar {
    width: 6px;
}

.ai-dashboard #terminalConsole::-webkit-scrollbar-thumb {
    background: #42475d;
    border-radius: 10px;
}

.ai-dashboard .terminal-welcome {
    color: #8e96b2;
}

/* RESULT */

.ai-dashboard .result-content {
    background: #f8f9fd;
    border: 1px solid var(--ai-border);
    border-radius: 12px;
    padding: 18px;
}

.ai-dashboard #latestProductText {
    white-space: pre-wrap;
    overflow-wrap: anywhere;
    font-family: inherit;
    font-size: 12px;
    line-height: 1.8;
    color: #41445a;
}

/* ALERT */

.ai-dashboard .api-alert {
    border-radius: 12px;
    border: 1px solid #f5caca;
    background: #fff6f6;
}

/* RESPONSIVE */

@media(max-width: 767px) {
    .ai-dashboard .dashboard-header {
        align-items: flex-start;
        flex-direction: column;
    }

    .ai-dashboard .dashboard-title {
        font-size: 21px;
    }

    .ai-dashboard .stat-card {
        padding: 13px 10px;
    }

    .ai-dashboard .stat-number {
        font-size: 22px;
    }

    .ai-dashboard .progress-inner {
        padding: 18px;
    }

    .ai-dashboard #terminalConsole {
        height: 280px;
    }
}
</style>

<div class="ai-dashboard">
<div class="app-content pt-3 p-md-3 p-lg-4">
<div class="container-xl">

    <!-- HEADER -->
    <div class="dashboard-header">
        <div>
            <div class="d-flex align-items-center gap-2 mb-2">
                <div class="icon-box">
                    <i class="fa-solid fa-wand-magic-sparkles"></i>
                </div>
                <span class="ai-label">AI Intelligence Center</span>
            </div>

            <h1 class="dashboard-title">Analisis Produk AI</h1>
            <p class="dashboard-subtitle">
                Kelola dan analisis informasi produk perusahaan secara otomatis menggunakan Google Gemini.
            </p>
        </div>

        <div class="system-status" id="systemStatus">
            <span class="status-dot"></span>
            Sistem Siap
        </div>
    </div>

    <!-- API ALERT -->
    <?php if (!$hasApiKey): ?>
    <div class="api-alert p-3 mb-4 d-flex align-items-center gap-3">
        <div class="icon-box" style="background:#ffe5e5;color:#dc3545;">
            <i class="fa-solid fa-triangle-exclamation"></i>
        </div>
        <div>
            <strong class="text-danger d-block">Google Gemini API Key belum dikonfigurasi</strong>
            <small class="text-muted">
                Lengkapi variabel <code>gemini.apiKey</code> di file .env sebelum menjalankan analisis.
            </small>
        </div>
    </div>
    <?php endif; ?>

    <div class="row g-4">

        <!-- LEFT: CONFIGURATION -->
        <div class="col-12 col-lg-5">

            <div class="app-card mb-4">

                <div class="p-3 p-lg-4 border-bottom d-flex align-items-center gap-3">
                    <div class="icon-box">
                        <i class="fa-solid fa-sliders"></i>
                    </div>
                    <div>
                        <h5 class="section-heading mb-0">Konfigurasi Analisis</h5>
                        <div class="section-description">Atur target dan metode pemrosesan AI.</div>
                    </div>
                </div>

                <div class="p-3 p-lg-4">

                    <!-- MODE -->
                    <div class="mb-4">
                        <label class="ai-label mb-3 d-block">01. Pilih Mode Analisis</label>

                        <div class="d-flex flex-column gap-2">

                            <label class="mode-selector mb-0">
                                <input type="radio" name="analysis_mode" id="modePending" value="pending" checked onchange="toggleModeControls()">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="mode-icon">
                                        <i class="fa-solid fa-clock-rotate-left"></i>
                                    </div>
                                    <div class="flex-grow-1">
                                        <div class="mode-title">Belum Dianalisis / Gagal</div>
                                        <div class="mode-description">
                                            Proses antrean yang belum dianalisis dan gagal.
                                            (<?= (int)($stats['belum'] ?? 0) ?> belum, <?= (int)($stats['gagal'] ?? 0) ?> gagal)
                                        </div>
                                    </div>
                                    <i class="fa-solid fa-circle-check mode-check"></i>
                                </div>
                            </label>

                            <label class="mode-selector mb-0">
                                <input type="radio" name="analysis_mode" id="modeSingle" value="single" onchange="toggleModeControls()">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="mode-icon">
                                        <i class="fa-solid fa-crosshairs"></i>
                                    </div>
                                    <div class="flex-grow-1">
                                        <div class="mode-title">Satu Perusahaan</div>
                                        <div class="mode-description">Analisis satu perusahaan secara spesifik.</div>
                                    </div>
                                    <i class="fa-solid fa-circle-check mode-check"></i>
                                </div>
                            </label>

                            <label class="mode-selector mb-0">
                                <input type="radio" name="analysis_mode" id="modeSelected" value="selected" onchange="toggleModeControls()">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="mode-icon">
                                        <i class="fa-solid fa-list-check"></i>
                                    </div>
                                    <div class="flex-grow-1">
                                        <div class="mode-title">Multi-Pilih</div>
                                        <div class="mode-description">Pilih beberapa perusahaan sesuai kebutuhan.</div>
                                    </div>
                                    <i class="fa-solid fa-circle-check mode-check"></i>
                                </div>
                            </label>

                            <label class="mode-selector mb-0">
                                <input type="radio" name="analysis_mode" id="modeAll" value="all" onchange="toggleModeControls()">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="mode-icon">
                                        <i class="fa-solid fa-globe"></i>
                                    </div>
                                    <div class="flex-grow-1">
                                        <div class="mode-title">Seluruh Perusahaan</div>
                                        <div class="mode-description">
                                            Analisis seluruh <?= (int)($stats['total'] ?? count($companies)) ?> data perusahaan.
                                        </div>
                                    </div>
                                    <i class="fa-solid fa-circle-check mode-check"></i>
                                </div>
                            </label>

                        </div>
                    </div>

                    <!-- SINGLE SELECT -->
                    <div class="selection-panel mb-4" id="singleSelectContainer" style="display:none;">
                        <label class="ai-label mb-2 d-block">02. Pilih Perusahaan</label>

                        <div class="input-group input-group-sm mb-2">
                            <span class="input-group-text bg-white">
                                <i class="fa-solid fa-magnifying-glass text-muted"></i>
                            </span>
                            <input type="text" class="form-control" id="searchSingleCompany"
                                placeholder="Cari nama perusahaan..."
                                onkeyup="filterSingleCompanyDropdown(this.value)">
                            <button class="btn btn-light border" type="button" onclick="clearSingleCompanySearch()">
                                <i class="fa-solid fa-xmark"></i>
                            </button>
                        </div>

                        <select class="form-select" id="singleCompanySelect">
                            <option value="">-- Pilih Perusahaan --</option>
                            <?php foreach ($companies as $c): ?>
                                <option value="<?= (int)$c['id'] ?>"
                                    <?= ((string)$c['id'] === (string)($selectedId ?? '')) ? 'selected' : '' ?>
                                    data-nama="<?= esc(strtolower($c['nama_perusahaan']), 'attr') ?>">
                                    <?= esc($c['nama_perusahaan']) ?> (<?= esc($c['status_analisis']) ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>

                        <div id="searchSingleFeedback" class="small text-primary mt-2" style="display:none;"></div>
                    </div>

                    <!-- MULTI SELECT -->
                    <div class="selection-panel mb-4" id="multiSelectContainer" style="display:none;">

                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <label class="ai-label mb-0">02. Pilih Perusahaan</label>
                            <span class="badge rounded-pill bg-primary bg-opacity-10 text-primary" id="selectedMultiCount">0 dipilih</span>
                        </div>

                        <div class="input-group input-group-sm mb-2">
                            <span class="input-group-text bg-white">
                                <i class="fa-solid fa-magnifying-glass text-muted"></i>
                            </span>
                            <input type="text" class="form-control" id="searchMultiCompany"
                                placeholder="Cari perusahaan..."
                                onkeyup="filterMultiCompanyList(this.value)">
                            <button class="btn btn-light border" type="button" onclick="clearMultiCompanySearch()">
                                <i class="fa-solid fa-xmark"></i>
                            </button>
                        </div>

                        <div class="d-flex justify-content-between mb-2">
                            <button type="button" class="btn btn-sm btn-link text-primary text-decoration-none p-0" onclick="toggleSelectAllMulti(true)">
                                <i class="fa-solid fa-check-double me-1"></i>Pilih Semua
                            </button>
                            <button type="button" class="btn btn-sm btn-link text-danger text-decoration-none p-0" onclick="toggleSelectAllMulti(false)">
                                Batal Semua
                            </button>
                        </div>

                        <div class="border rounded-3 bg-white p-2"
                            id="multiCompanyListWrap"
                            style="max-height:230px;overflow-y:auto;">

                            <?php foreach ($companies as $c): ?>
                            <div class="form-check multi-company-item mb-0"
                                data-nama="<?= esc(strtolower($c['nama_perusahaan']), 'attr') ?>">

                                <input class="form-check-input multi-check"
                                    type="checkbox"
                                    value="<?= (int)$c['id'] ?>"
                                    id="multi_<?= (int)$c['id'] ?>">

                                <label class="form-check-label small d-flex justify-content-between align-items-center w-100"
                                    for="multi_<?= (int)$c['id'] ?>">
                                    <span><?= esc($c['nama_perusahaan']) ?></span>
                                    <span class="badge bg-light text-secondary border ms-2">
                                        <?= esc($c['status_analisis']) ?>
                                    </span>
                                </label>
                            </div>
                            <?php endforeach; ?>

                        </div>
                    </div>

                    <!-- ACTION -->
                    <div class="pt-2">
                        <button type="button" class="btn btn-primary btn-start w-100 text-white"
                            id="btnStart" onclick="startAnalysis()" <?= !$hasApiKey ? 'disabled' : '' ?>>
                            <i class="fa-solid fa-play me-2"></i> Mulai Analisis AI
                        </button>

                        <div class="row g-2 mt-1">
                            <div class="col-6">
                                <button type="button" class="btn btn-warning btn-action w-100"
                                    id="btnPause" onclick="togglePause()" style="display:none;">
                                    <i class="fa-solid fa-pause me-1"></i> Jeda
                                </button>
                            </div>
                            <div class="col-6">
                                <button type="button" class="btn btn-danger btn-action w-100"
                                    id="btnStop" onclick="stopAnalysis()" style="display:none;">
                                    <i class="fa-solid fa-stop me-1"></i> Hentikan
                                </button>
                            </div>
                        </div>
                    </div>

                </div>
            </div>

            <!-- STATISTICS -->
            <div class="row g-3">

                <div class="col-4">
                    <div class="app-card stat-card">
                        <div class="stat-icon" style="background:#f0efff;color:#635bdb;">
                            <i class="fa-solid fa-layer-group"></i>
                        </div>
                        <div class="stat-label">Antrean</div>
                        <h3 class="stat-number" id="statQueue">0</h3>
                    </div>
                </div>

                <div class="col-4">
                    <div class="app-card stat-card">
                        <div class="stat-icon" style="background:#e9f9f0;color:#1b9a59;">
                            <i class="fa-solid fa-circle-check"></i>
                        </div>
                        <div class="stat-label">Sukses</div>
                        <h3 class="stat-number text-success" id="statSuccess">0</h3>
                    </div>
                </div>

                <div class="col-4">
                    <div class="app-card stat-card">
                        <div class="stat-icon" style="background:#fff0f0;color:#dc4545;">
                            <i class="fa-solid fa-circle-xmark"></i>
                        </div>
                        <div class="stat-label">Gagal</div>
                        <h3 class="stat-number text-danger" id="statFailed">0</h3>
                    </div>
                </div>

            </div>

        </div>

        <!-- RIGHT: MONITORING -->
        <div class="col-12 col-lg-7">

            <!-- PROGRESS -->
            <div class="app-card progress-card mb-4">
                <div class="progress-inner">

                    <div class="d-flex justify-content-between align-items-start gap-3 mb-4">
                        <div>
                            <div class="d-flex align-items-center gap-2 mb-2">
                                <i class="fa-solid fa-microchip"></i>
                                <span class="small fw-bold text-white-50">AI ENGINE MONITOR</span>
                            </div>
                            <div class="progress-title" id="progressStatusText">
                                Menunggu perintah...
                            </div>
                            <div class="progress-status mt-1">
                                Status pemrosesan analisis perusahaan.
                            </div>
                        </div>

                        <span class="progress-badge" id="progressBadge">0% (0 / 0)</span>
                    </div>

                    <div class="progress">
                        <div class="progress-bar progress-bar-striped progress-bar-animated bg-warning"
                            id="analysisProgressBar"
                            role="progressbar"
                            style="width:0%;"
                            aria-valuenow="0"
                            aria-valuemin="0"
                            aria-valuemax="100">
                        </div>
                    </div>

                    <div class="d-flex justify-content-between mt-2">
                        <small class="text-white-50">Progress analisis</small>
                        <small class="text-white-50">Google Gemini AI</small>
                    </div>

                </div>
            </div>

            <!-- TERMINAL -->
            <div class="terminal-card mb-4">

                <div class="terminal-header">
                    <div class="d-flex align-items-center gap-3">
                        <div class="terminal-dots">
                            <span style="background:#ff5f57;"></span>
                            <span style="background:#febc2e;"></span>
                            <span style="background:#28c840;"></span>
                        </div>

                        <div class="text-white small fw-bold">
                            <i class="fa-solid fa-terminal me-1 text-success"></i>
                            Live Terminal
                        </div>

                        <span class="badge bg-secondary bg-opacity-25 text-white-50"
                            style="font-size:9px;">SERVER LOG</span>
                    </div>

                    <button type="button"
                        class="btn btn-sm btn-outline-light border-0"
                        onclick="clearConsoleLog()">
                        <i class="fa-solid fa-trash-can me-1"></i>
                        Bersihkan
                    </button>
                </div>

                <div id="terminalConsole">
                    <div class="terminal-welcome">
                        <span class="text-success">➜</span>
                        Konsol siap digunakan.
                    </div>
                    <div class="terminal-welcome">
                        Pilih mode analisis dan klik Mulai Analisis AI untuk memulai.
                    </div>
                    <div class="text-success mt-2">
                        ● SYSTEM READY <span class="blink">_</span>
                    </div>
                </div>

            </div>

            <!-- LATEST RESULT -->
            <div class="app-card" id="latestResultCard" style="display:none;">

                <div class="p-3 border-bottom d-flex align-items-center gap-3">
                    <div class="icon-box" style="background:#e9f9f0;color:#1b9a59;">
                        <i class="fa-solid fa-wand-magic-sparkles"></i>
                    </div>

                    <div class="flex-grow-1">
                        <h6 class="section-heading mb-1">Hasil Analisis Terakhir</h6>
                        <span class="small text-primary fw-bold" id="latestCompanyName">-</span>
                    </div>

                    <span class="badge bg-success bg-opacity-10 text-success rounded-pill">
                        <i class="fa-solid fa-check me-1"></i> Selesai
                    </span>
                </div>

                <div class="p-3">
                    <div class="result-content">
                        <div class="d-flex align-items-center gap-2 mb-3">
                            <i class="fa-solid fa-file-lines text-primary"></i>
                            <span class="small fw-bold text-dark">Informasi Produk</span>
                        </div>
                        <pre class="mb-0" id="latestProductText"></pre>
                    </div>
                </div>

            </div>

        </div>

    </div>

</div>
</div>
</div>

<script>
// =====================================================
// ENDPOINT
// =====================================================

const URL_QUEUE = '<?= base_url('analisis-ai/queue') ?>';
const URL_PROCESS = '<?= base_url('analisis-ai/process-one') ?>';

// =====================================================
// STATE
// =====================================================

let isRunning = false;
let isPaused = false;
let isProcessing = false;

let queueList = [];
let currentIndex = 0;
let countSuccess = 0;
let countFailed = 0;
let processTimer = null;

// =====================================================
// HELPER
// =====================================================

function escapeHtml(s) {
    return String(s ?? '').replace(/[&<>"']/g, c => ({
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#39;'
    }[c]));
}

function updateSystemStatus(status) {
    const el = document.getElementById('systemStatus');

    const states = {
        ready: {
            text: 'Sistem Siap',
            color: '#20b66b',
            bg: '#eafaf1'
        },
        running: {
            text: 'AI Sedang Bekerja',
            color: '#635bdb',
            bg: '#f0efff'
        },
        paused: {
            text: 'Analisis Dijeda',
            color: '#d18a18',
            bg: '#fff7e7'
        },
        stopped: {
            text: 'Analisis Berhenti',
            color: '#dc4545',
            bg: '#fff0f0'
        }
    };

    const s = states[status] || states.ready;

    el.style.color = s.color;
    el.style.background = s.bg;
    el.innerHTML = `<span class="status-dot" style="background:${s.color};box-shadow:0 0 0 4px ${s.color}20;"></span>${s.text}`;
}

// =====================================================
// INITIALIZATION
// =====================================================

document.addEventListener('DOMContentLoaded', () => {

    const urlParams = new URLSearchParams(window.location.search);

    if (urlParams.get('mode') === 'single' && urlParams.get('id')) {
        document.getElementById('modeSingle').checked = true;
        toggleModeControls();
        document.getElementById('singleCompanySelect').value = urlParams.get('id');
    }

    document.querySelectorAll('.multi-check').forEach(cb => {
        cb.addEventListener('change', updateSelectedCount);
    });

    updateSelectedCount();
    toggleModeControls();
});

// =====================================================
// MODE CONTROLS
// =====================================================

function toggleModeControls() {

    const selected = document.querySelector('input[name="analysis_mode"]:checked');

    if (!selected) return;

    const mode = selected.value;

    document.getElementById('singleSelectContainer').style.display =
        mode === 'single' ? 'block' : 'none';

    document.getElementById('multiSelectContainer').style.display =
        mode === 'selected' ? 'block' : 'none';
}

// =====================================================
// MULTI SELECT
// =====================================================

function updateSelectedCount() {

    const count = document.querySelectorAll('.multi-check:checked').length;

    const badge = document.getElementById('selectedMultiCount');

    if (badge) {
        badge.textContent = `${count} dipilih`;
    }
}

function toggleSelectAllMulti(val) {

    document.querySelectorAll('.multi-check').forEach(cb => {

        const parent = cb.closest('.multi-company-item');

        if (!parent || parent.style.display !== 'none' || !val) {
            cb.checked = val;
        }

    });

    updateSelectedCount();
}

function filterMultiCompanyList(keyword) {

    const filter = (keyword || '').toLowerCase().trim();

    document.querySelectorAll('#multiSelectContainer .multi-company-item').forEach(item => {

        const text = (item.getAttribute('data-nama') || item.textContent).toLowerCase();

        item.style.display = text.includes(filter) ? '' : 'none';

    });
}

function clearMultiCompanySearch() {

    const input = document.getElementById('searchMultiCompany');

    if (input) {
        input.value = '';
        filterMultiCompanyList('');
        input.focus();
    }
}

// =====================================================
// SINGLE SELECT SEARCH
// =====================================================

function filterSingleCompanyDropdown(keyword) {

    const filter = (keyword || '').toLowerCase().trim();

    const select = document.getElementById('singleCompanySelect');
    const feedback = document.getElementById('searchSingleFeedback');

    if (!select) return;

    const options = select.options;

    let matchedCount = 0;

    for (let i = 1; i < options.length; i++) {

        const opt = options[i];

        const text = (opt.getAttribute('data-nama') || opt.text).toLowerCase();

        if (text.includes(filter)) {
            opt.hidden = false;
            opt.style.display = '';
            matchedCount++;
        } else {
            opt.hidden = true;
            opt.style.display = 'none';
        }
    }

    if (filter !== '') {

        feedback.style.display = 'block';

        feedback.innerHTML = `<i class="fa-solid fa-circle-info me-1"></i> Ditemukan <strong>${matchedCount}</strong> perusahaan.`;

        if (matchedCount > 0 && select.selectedIndex > 0 && options[select.selectedIndex].hidden) {

            for (let i = 1; i < options.length; i++) {

                if (!options[i].hidden) {
                    select.selectedIndex = i;
                    break;
                }

            }
        }

    } else {

        feedback.style.display = 'none';

    }
}

function clearSingleCompanySearch() {

    const input = document.getElementById('searchSingleCompany');

    if (input) {
        input.value = '';
        filterSingleCompanyDropdown('');
        input.focus();
    }
}

// =====================================================
// TERMINAL
// =====================================================

function appendLog(msg, type = 'info') {

    const consoleEl = document.getElementById('terminalConsole');

    const time = new Date().toLocaleTimeString('en-GB');

    const colors = {
        success: 'text-success',
        error: 'text-danger',
        warning: 'text-warning',
        info: 'text-info',
        muted: 'text-secondary'
    };

    let prefix = '<span class="text-primary">➜</span>';

    if (type === 'success') prefix = '<span class="text-success">✔</span>';
    if (type === 'error') prefix = '<span class="text-danger">✘</span>';
    if (type === 'warning') prefix = '<span class="text-warning">⚠</span>';

    const div = document.createElement('div');

    div.className = `${colors[type] || 'text-light'} mb-1`;

    div.innerHTML = `
        <span class="text-secondary">[${time}]</span>
        ${prefix}
        ${msg}
    `;

    consoleEl.appendChild(div);

    consoleEl.scrollTop = consoleEl.scrollHeight;
}

function clearConsoleLog() {

    document.getElementById('terminalConsole').innerHTML = `
        <div class="terminal-welcome">
            <span class="text-success">➜</span> Konsol dibersihkan.
        </div>
    `;
}

// =====================================================
// START ANALYSIS
// =====================================================

function startAnalysis() {

    if (isRunning) return;

    const mode = document.querySelector('input[name="analysis_mode"]:checked').value;

    let queryParams = '?mode=' + encodeURIComponent(mode);

    if (mode === 'single') {

        const id = document.getElementById('singleCompanySelect').value;

        if (!id) {
            alert('Silakan pilih salah satu perusahaan terlebih dahulu.');
            return;
        }

        queryParams += '&id=' + encodeURIComponent(id);

    } else if (mode === 'selected') {

        const selected = Array.from(
            document.querySelectorAll('.multi-check:checked')
        ).map(cb => cb.value);

        if (selected.length === 0) {
            alert('Silakan centang setidaknya satu perusahaan.');
            return;
        }

        queryParams += '&ids=' + selected.join(',');

    }

    appendLog('Mengambil daftar antrean perusahaan dari server...', 'info');

    const startButton = document.getElementById('btnStart');

    startButton.disabled = true;

    fetch(URL_QUEUE + queryParams, {
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(r => {

        if (!r.ok) throw new Error(`HTTP ${r.status}`);

        return r.json();

    })
    .then(res => {

        if (!res.success || !res.queue || res.queue.length === 0) {

            appendLog('Tidak ada perusahaan yang memenuhi kriteria antrean.', 'warning');

            alert(res.message || 'Tidak ada perusahaan yang perlu dianalisis.');

            startButton.disabled = false;

            return;
        }

        queueList = res.queue;

        currentIndex = 0;
        countSuccess = 0;
        countFailed = 0;

        isRunning = true;
        isPaused = false;
        isProcessing = false;

        document.getElementById('statQueue').textContent = queueList.length;
        document.getElementById('statSuccess').textContent = '0';
        document.getElementById('statFailed').textContent = '0';

        const bar = document.getElementById('analysisProgressBar');

        bar.className = 'progress-bar progress-bar-striped progress-bar-animated bg-warning';
        bar.style.width = '0%';

        startButton.style.display = 'none';

        document.getElementById('btnPause').style.display = 'block';

        document.getElementById('btnPause').innerHTML =
            '<i class="fa-solid fa-pause me-1"></i> Jeda';

        document.getElementById('btnPause').className =
            'btn btn-warning btn-action w-100';

        document.getElementById('btnStop').style.display = 'block';

        updateSystemStatus('running');

        appendLog(
            `Memulai analisis untuk total <strong>${queueList.length}</strong> perusahaan.`,
            'success'
        );

        processNext();

    })
    .catch(err => {

        appendLog('Gagal mengambil antrean: ' + escapeHtml(err.message), 'error');

        startButton.disabled = false;

    });
}

// =====================================================
// PROCESS QUEUE
// =====================================================

function processNext() {

    if (!isRunning) return;

    if (isPaused) return;

    if (isProcessing) return;

    if (currentIndex >= queueList.length) {
        finishAnalysis();
        return;
    }

    const item = queueList[currentIndex];

    const total = queueList.length;

    const currentNum = currentIndex + 1;

    const pct = Math.round((currentNum / total) * 100);

    const namaAman = escapeHtml(item.nama_perusahaan);

    const bar = document.getElementById('analysisProgressBar');

    bar.style.width = pct + '%';

    bar.setAttribute('aria-valuenow', pct);

    document.getElementById('progressBadge').textContent =
        `${pct}% (${currentNum} / ${total})`;

    document.getElementById('progressStatusText').textContent =
        `Memproses (${currentNum}/${total}): ${item.nama_perusahaan}...`;

    appendLog(
        `<strong>[${currentNum}/${total}]</strong> Menganalisis: <strong>${namaAman}</strong> (ID: ${escapeHtml(item.id)})...`,
        'info'
    );

    const formData = new FormData();

    formData.append('id', item.id);

    isProcessing = true;

    fetch(URL_PROCESS, {
        method: 'POST',
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: formData
    })
    .then(r => {

        if (!r.ok) throw new Error(`HTTP ${r.status}`);

        return r.json();

    })
    .then(res => {

        isProcessing = false;

        if (res.logs && res.logs.length > 0) {

            res.logs.forEach(l => {

                appendLog('&nbsp;&nbsp;&gt; ' + escapeHtml(l), 'muted');

            });

        }

        if (res.quota_exceeded) {

            appendLog(
                escapeHtml(res.error || 'Kuota AI habis, coba lagi nanti'),
                'error'
            );

            alert('Kuota AI habis, coba lagi nanti.');

            finishAnalysis(true);

            return;
        }

        if (res.skipped) {

            appendLog(
                'Dilewati: tidak ada sumber informasi (AI tidak dipanggil).',
                'warning'
            );

        } else if (res.no_product) {

            countSuccess++;

            document.getElementById('statSuccess').textContent = countSuccess;

            appendLog(
                'SELESAI: AI menyimpulkan tanpa produk fisik.',
                'success'
            );

            showLatestResult(res.nama_perusahaan, '(Tanpa produk)');

        } else if (res.success) {

            countSuccess++;

            document.getElementById('statSuccess').textContent = countSuccess;

            appendLog(
                `SUKSES: Ditemukan ${(res.products || []).length} produk.`,
                'success'
            );

            showLatestResult(
                res.nama_perusahaan,
                res.formatted_text || ''
            );

        } else {

            countFailed++;

            document.getElementById('statFailed').textContent = countFailed;

            appendLog(
                'GAGAL: ' + escapeHtml(res.error || 'Terjadi kesalahan'),
                'error'
            );

        }

        currentIndex++;

        processTimer = setTimeout(processNext, 2000);

    })
    .catch(err => {

        isProcessing = false;

        countFailed++;

        document.getElementById('statFailed').textContent = countFailed;

        appendLog(
            'Jaringan/Server error: ' + escapeHtml(err.message),
            'error'
        );

        currentIndex++;

        processTimer = setTimeout(processNext, 2000);

    });
}

// =====================================================
// LATEST RESULT
// =====================================================

function showLatestResult(companyName, productText) {

    document.getElementById('latestResultCard').style.display = 'block';

    document.getElementById('latestCompanyName').textContent =
        companyName || '-';

    document.getElementById('latestProductText').textContent =
        productText || '';

}

// =====================================================
// PAUSE / RESUME
// =====================================================

function togglePause() {

    if (!isRunning) return;

    isPaused = !isPaused;

    const btn = document.getElementById('btnPause');

    if (isPaused) {

        btn.innerHTML = '<i class="fa-solid fa-play me-1"></i> Lanjutkan';

        btn.className = 'btn btn-success btn-action w-100';

        document.getElementById('progressStatusText').textContent =
            'Analisis sedang dijeda.';

        updateSystemStatus('paused');

        appendLog('Analisis dijeda oleh pengguna.', 'warning');

    } else {

        btn.innerHTML = '<i class="fa-solid fa-pause me-1"></i> Jeda';

        btn.className = 'btn btn-warning btn-action w-100';

        updateSystemStatus('running');

        appendLog('Melanjutkan pemrosesan antrean...', 'info');

        processNext();

    }
}

// =====================================================
// STOP
// =====================================================

function stopAnalysis() {

    if (!isRunning) return;

    if (!confirm('Apakah Anda yakin ingin menghentikan proses analisis?')) {
        return;
    }

    isRunning = false;

    isPaused = false;

    if (processTimer) {
        clearTimeout(processTimer);
        processTimer = null;
    }

    appendLog('Proses analisis dihentikan oleh pengguna.', 'warning');

    finishAnalysis(true);

}

// =====================================================
// FINISH
// =====================================================

function finishAnalysis(stopped = false) {

    isRunning = false;

    isPaused = false;

    isProcessing = false;

    if (processTimer) {
        clearTimeout(processTimer);
        processTimer = null;
    }

    const startButton = document.getElementById('btnStart');

    startButton.style.display = 'block';
    startButton.disabled = false;

    document.getElementById('btnPause').style.display = 'none';

    document.getElementById('btnStop').style.display = 'none';

    const bar = document.getElementById('analysisProgressBar');

    bar.classList.remove('progress-bar-animated');

    if (!stopped) {

        bar.className = 'progress-bar bg-success';

        bar.style.width = '100%';

        bar.setAttribute('aria-valuenow', 100);

        document.getElementById('progressBadge').textContent =
            `100% (${queueList.length} / ${queueList.length})`;

        document.getElementById('progressStatusText').textContent =
            'Seluruh antrean analisis selesai!';

        updateSystemStatus('ready');

        appendLog(
            `=== ANALISIS SELESAI === Sukses: ${countSuccess}, Gagal: ${countFailed}`,
            'success'
        );

        setTimeout(() => {

            alert(
                `Analisis selesai!\nSukses: ${countSuccess}\nGagal: ${countFailed}`
            );

        }, 500);

    } else {

        document.getElementById('progressStatusText').textContent =
            'Analisis dihentikan.';

        updateSystemStatus('stopped');

    }

}
</script>

<?= $this->endSection(); ?>
