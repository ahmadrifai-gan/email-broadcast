<?= $this->extend('layouts/main') ?>

<?= $this->section('page_title') ?>Pengaturan AI<?= $this->endSection() ?>
<?= $this->section('page_subtitle') ?>Konfigurasi mesin inferensi dan instruksi produk<?= $this->endSection() ?>

<?= $this->section('breadcrumb') ?>
<li><a href="<?= site_url('/') ?>"><i class="fa fa-dashboard"></i> Home</a></li>
<li class="active">Pengaturan AI</li>
<?= $this->endSection() ?>

<?= $this->section('styles') ?>
<style>
    .ai-settings {
        --ai-primary: #4f46e5;
        --ai-primary-soft: #eef2ff;
        --ai-border: #e5e7eb;
        --ai-text: #111827;
        --ai-muted: #6b7280;
        --ai-success: #059669;
        --ai-danger: #dc2626;
        max-width: 1180px;
    }

    .ai-settings-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        margin-bottom: 18px;
    }

    .ai-settings-title {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .ai-settings-title-icon {
        width: 46px;
        height: 46px;
        border-radius: 12px;
        background: var(--ai-primary-soft);
        color: var(--ai-primary);
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 20px;
    }

    .ai-settings-title h2 {
        margin: 0 0 3px;
        font-size: 20px;
        font-weight: 700;
        color: var(--ai-text);
    }

    .ai-settings-title p {
        margin: 0;
        color: var(--ai-muted);
        font-size: 13px;
    }

    .ai-link-button,
    .ai-primary-button,
    .ai-test-button,
    .ai-reset-button {
        border: 0;
        border-radius: 8px;
        font-weight: 600;
        transition: .18s ease;
        text-decoration: none !important;
    }

    .ai-link-button {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        padding: 9px 13px;
        border: 1px solid var(--ai-border);
        background: #fff;
        color: #374151;
        white-space: nowrap;
    }

    .ai-link-button:hover {
        border-color: #c7d2fe;
        color: var(--ai-primary);
        background: #fafaff;
    }

    .ai-status-banner {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 14px;
        border: 1px solid;
        border-radius: 12px;
        padding: 14px 16px;
        margin-bottom: 16px;
        box-shadow: 0 6px 18px rgba(15, 23, 42, .05);
    }

    .ai-status-banner.success {
        border-color: #bbf7d0;
        background: #f0fdf4;
        color: #166534;
    }

    .ai-status-banner.danger {
        border-color: #fecaca;
        background: #fef2f2;
        color: #991b1b;
    }

    .ai-status-main {
        display: flex;
        align-items: center;
        gap: 12px;
        min-width: 0;
    }

    .ai-status-icon {
        width: 38px;
        height: 38px;
        border-radius: 50%;
        background: rgba(255, 255, 255, .75);
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex: 0 0 38px;
        font-size: 17px;
    }

    .ai-status-copy strong {
        display: block;
        margin-bottom: 3px;
    }

    .ai-status-copy span {
        color: #6b7280;
        font-size: 12.5px;
        line-height: 1.45;
    }

    .ai-status-copy code {
        background: rgba(255, 255, 255, .8);
        color: inherit;
    }

    .ai-test-button {
        padding: 9px 12px;
        border: 1px solid currentColor;
        background: #fff;
        color: #374151;
        white-space: nowrap;
    }

    .ai-test-button:hover {
        background: #f8fafc;
    }

    .ai-layout {
        display: grid;
        grid-template-columns: minmax(0, 2fr) minmax(260px, .85fr);
        gap: 16px;
        align-items: start;
    }

    .ai-card {
        border: 1px solid var(--ai-border);
        border-radius: 12px;
        background: #fff;
        box-shadow: 0 8px 24px rgba(15, 23, 42, .06);
        overflow: hidden;
        margin-bottom: 16px;
    }

    .ai-card-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
        min-height: 54px;
        padding: 14px 17px;
        border-bottom: 1px solid var(--ai-border);
        background: #fff;
    }

    .ai-card-header h3 {
        margin: 0;
        font-size: 15px;
        font-weight: 700;
        color: var(--ai-text);
    }

    .ai-card-header h3 i {
        margin-right: 7px;
        color: var(--ai-primary);
    }

    .ai-card-body {
        padding: 18px;
    }

    .ai-form-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 16px;
        margin-bottom: 18px;
    }

    .ai-field label,
    .ai-field-label {
        display: block;
        margin-bottom: 7px;
        color: #374151;
        font-size: 13px;
        font-weight: 700;
    }

    .ai-field .form-control {
        height: 40px;
        border: 1px solid #d1d5db;
        border-radius: 8px;
        box-shadow: none;
        color: var(--ai-text);
    }

    .ai-field textarea.form-control {
        height: auto;
        min-height: 280px;
        padding: 13px;
        border-radius: 9px;
        line-height: 1.55;
        resize: vertical;
        font-size: 12.5px;
    }

    .ai-field .form-control:focus {
        border-color: #818cf8;
        box-shadow: 0 0 0 3px rgba(99, 102, 241, .10);
    }

    .ai-help {
        display: block;
        margin-top: 6px;
        color: var(--ai-muted);
        font-size: 12px;
        line-height: 1.45;
    }

    .ai-temp-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
        margin-bottom: 7px;
    }

    .ai-temp-value {
        min-width: 42px;
        text-align: center;
        border-radius: 999px;
        background: var(--ai-primary);
        color: #fff;
        padding: 3px 8px;
        font-size: 12px;
        font-weight: 700;
    }

    .ai-range {
        width: 100%;
        accent-color: var(--ai-primary);
    }

    .ai-reset-button {
        background: transparent;
        color: var(--ai-primary);
        padding: 4px 0;
    }

    .ai-reset-button:hover {
        color: #3730a3;
    }

    .ai-format-box {
        border: 1px solid var(--ai-border);
        border-radius: 8px;
        background: #f8fafc;
        padding: 10px 11px;
        color: #475569;
        font-size: 11.5px;
        line-height: 1.55;
        overflow-x: auto;
    }

    .ai-status-control {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        border: 1px solid var(--ai-border);
        border-radius: 9px;
        background: #fafafa;
        padding: 12px 13px;
    }

    .ai-status-control strong {
        display: block;
        color: var(--ai-text);
        font-size: 13px;
    }

    .ai-status-control span {
        color: var(--ai-muted);
        font-size: 12px;
    }

    .ai-switch {
        position: relative;
        display: inline-block;
        width: 44px;
        height: 24px;
        flex: 0 0 44px;
    }

    .ai-switch input {
        opacity: 0;
        width: 0;
        height: 0;
    }

    .ai-switch-slider {
        position: absolute;
        inset: 0;
        border-radius: 999px;
        background: #cbd5e1;
        cursor: pointer;
        transition: .2s;
    }

    .ai-switch-slider:before {
        content: "";
        position: absolute;
        width: 18px;
        height: 18px;
        left: 3px;
        top: 3px;
        border-radius: 50%;
        background: #fff;
        box-shadow: 0 1px 4px rgba(15, 23, 42, .25);
        transition: .2s;
    }

    .ai-switch input:checked + .ai-switch-slider {
        background: var(--ai-success);
    }

    .ai-switch input:checked + .ai-switch-slider:before {
        transform: translateX(20px);
    }

    .ai-card-footer {
        display: flex;
        justify-content: flex-end;
        border-top: 1px solid var(--ai-border);
        background: #fafafa;
        padding: 13px 17px;
    }

    .ai-primary-button {
        padding: 10px 16px;
        background: var(--ai-primary);
        color: #fff;
        box-shadow: 0 5px 14px rgba(79, 70, 229, .18);
    }

    .ai-primary-button:hover,
    .ai-primary-button:focus {
        background: #4338ca;
        color: #fff;
    }

    .ai-guide {
        color: #4b5563;
        font-size: 12.5px;
        line-height: 1.55;
    }

    .ai-guide-section {
        padding: 13px 0;
        border-bottom: 1px solid #eef0f2;
    }

    .ai-guide-section:first-child {
        padding-top: 0;
    }

    .ai-guide-section:last-child {
        border-bottom: 0;
        padding-bottom: 0;
    }

    .ai-guide-section h4 {
        margin: 0 0 7px;
        color: var(--ai-text);
        font-size: 13px;
        font-weight: 700;
    }

    .ai-guide-section p {
        margin: 0;
    }

    .ai-guide-list {
        margin: 8px 0 0;
        padding-left: 18px;
    }

    .ai-guide-example {
        border: 1px solid var(--ai-border);
        border-radius: 8px;
        background: #f8fafc;
        padding: 10px;
        margin: 8px 0;
        color: #374151;
        font-family: monospace;
        font-size: 11.5px;
    }

    #testResultBox {
        display: none;
    }

    .ai-alert {
        border: 1px solid;
        border-radius: 8px;
        padding: 11px 13px;
        margin-bottom: 16px;
        font-size: 13px;
    }

    .ai-alert.success {
        border-color: #bbf7d0;
        background: #f0fdf4;
        color: #166534;
    }

    .ai-result {
        border: 1px solid;
        border-radius: 8px;
        padding: 11px 13px;
        margin-bottom: 16px;
        font-size: 13px;
    }

    .ai-result.success {
        border-color: #bbf7d0;
        background: #f0fdf4;
        color: #166534;
    }

    .ai-result.danger {
        border-color: #fecaca;
        background: #fef2f2;
        color: #991b1b;
    }

    @media (max-width: 991px) {
        .ai-layout {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 767px) {
        .ai-settings-header,
        .ai-status-banner {
            align-items: flex-start;
        }

        .ai-settings-header,
        .ai-status-banner {
            flex-direction: column;
        }

        .ai-test-button,
        .ai-link-button {
            width: 100%;
            justify-content: center;
        }

        .ai-form-grid {
            grid-template-columns: 1fr;
        }
    }
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="ai-settings">
    <div class="ai-settings-header">
        <div class="ai-settings-title">
            <span class="ai-settings-title-icon"><i class="fa fa-sliders"></i></span>
            <div>
                <h2>Pengaturan Mesin AI</h2>
                <p>Kelola provider, model, parameter inferensi, dan instruksi prompt.</p>
            </div>
        </div>
        <a href="<?= base_url('/analisis') ?>" class="ai-link-button">
            <i class="fa fa-terminal"></i> Konsol Analisis
        </a>
    </div>

    <?php if (session()->getFlashdata('success')): ?>
        <div class="ai-alert success">
            <i class="fa fa-check-circle"></i>
            <?= esc(session()->getFlashdata('success')) ?>
        </div>
    <?php endif; ?>

    <div class="ai-status-banner <?= $hasApiKey ? 'success' : 'danger' ?>">
        <div class="ai-status-main">
            <span class="ai-status-icon">
                <i class="fa <?= $hasApiKey ? 'fa-shield' : 'fa-exclamation-triangle' ?>"></i>
            </span>
            <div class="ai-status-copy">
                <strong><?= $hasApiKey ? 'Kredensial API Terhubung' : 'API Key Belum Terkonfigurasi' ?></strong>
                <span>
                    <?= $hasApiKey
                        ? 'API Key dibaca aman dari berkas <code>.env</code> melalui <code>gemini.apiKey</code>.'
                        : 'Isi variabel <code>gemini.apiKey</code> pada berkas <code>.env</code> untuk mengaktifkan koneksi.' ?>
                </span>
            </div>
        </div>
        <button type="button" class="ai-test-button" id="btnTestConn" onclick="testConnection()">
            <i class="fa fa-plug"></i> Uji Koneksi API
        </button>
    </div>

    <div id="testResultBox"></div>

    <div class="ai-layout">
        <div class="ai-card">
            <div class="ai-card-header">
                <h3><i class="fa fa-cogs"></i> Parameter Inferensi AI</h3>
            </div>

            <form method="POST" action="<?= base_url('pengaturan/save') ?>" id="formPengaturanAi">
                <?= csrf_field() ?>

                <div class="ai-card-body">
                    <div class="ai-form-grid">
                        <div class="ai-field">
                            <label for="providerInput">Provider AI</label>
                            <input type="text" class="form-control" id="providerInput" name="provider"
                                value="<?= esc($config['provider'] ?? 'gemini') ?>" required placeholder="contoh: gemini">
                            <span class="ai-help">Penyedia AI aktif, misalnya <code>gemini</code>.</span>
                        </div>

                        <div class="ai-field">
                            <label for="namaModelInput">Nama Model</label>
                            <input type="text" class="form-control" id="namaModelInput" name="nama_model"
                                value="<?= esc($config['nama_model'] ?? 'gemini-3.5-flash-lite') ?>" required
                                placeholder="contoh: gemini-3.5-flash-lite">
                            <span class="ai-help">Model yang digunakan untuk inferensi produk.</span>
                        </div>
                    </div>

                    <div class="ai-form-grid">
                        <div class="ai-field">
                            <div class="ai-temp-head">
                                <label for="temperature" style="margin:0">Temperature</label>
                                <span class="ai-temp-value" id="tempVal"><?= esc($config['temperature'] ?? '0') ?></span>
                            </div>
                            <input type="range" class="ai-range" id="temperature" name="temperature"
                                min="0" max="1" step="0.05"
                                value="<?= esc($config['temperature'] ?? '0') ?>"
                                oninput="document.getElementById('tempVal').textContent = this.value">
                            <span class="ai-help">Nilai 0 menghasilkan respons paling konsisten dan faktual.</span>
                        </div>

                        <div class="ai-field">
                            <label for="maxTokens">Max Output Tokens</label>
                            <input type="number" class="form-control" id="maxTokens" name="max_tokens"
                                value="<?= esc($config['max_tokens'] ?? '8192') ?>" min="200" max="8192" required>
                            <span class="ai-help">Batas maksimum respons output, maksimal 8192 token.</span>
                        </div>
                    </div>

                    <div class="ai-field" style="margin-bottom:18px">
                        <div class="ai-temp-head">
                            <label for="instruksiAi" style="margin:0">Instruksi AI & Format Hasil</label>
                            <button type="button" class="ai-reset-button" onclick="resetPromptToDefault()">
                                <i class="fa fa-rotate-left"></i> Pulihkan Template
                            </button>
                        </div>
                        <textarea class="form-control" id="instruksiAi" name="instruksi_ai" rows="18" required><?= esc($config['instruksi_ai'] ?? $defaultPrompt) ?></textarea>
                        <div class="ai-format-box" style="margin-top:8px">
                            Format: <code>JSON [{"name_source":"...","brand":"...","name_en":"...","name_id":"...","source":"...","evidence":"..."}]</code>
                        </div>
                        <span class="ai-help">
                            Placeholder: <code>{nama}</code>, <code>{kategori}</code>, <code>{sub}</code>,
                            <code>{nomor_potongan}</code>, <code>{total_potongan}</code>, <code>{potongan}</code>.
                        </span>
                    </div>

                    <div class="ai-status-control">
                        <div>
                            <strong>Status Layanan AI</strong>
                            <span>Aktifkan atau nonaktifkan proses inferensi.</span>
                        </div>
                        <label class="ai-switch" title="Status layanan AI">
                            <input type="checkbox" id="statusSwitch" name="status" value="aktif"
                                <?= (($config['status'] ?? 'aktif') === 'aktif') ? 'checked' : '' ?>>
                            <span class="ai-switch-slider"></span>
                        </label>
                    </div>
                </div>

                <div class="ai-card-footer">
                    <button type="submit" class="ai-primary-button">
                        <i class="fa fa-floppy-o"></i> Simpan Pengaturan AI
                    </button>
                </div>
            </form>
        </div>

        <aside class="ai-card">
            <div class="ai-card-header">
                <h3><i class="fa fa-info-circle"></i> Panduan</h3>
            </div>
            <div class="ai-card-body ai-guide">
                <div class="ai-guide-section">
                    <h4>Keamanan API Key</h4>
                    <p>API Key hanya dibaca dari file <code>.env</code>. Form ini tidak menampilkan atau menyimpan kunci ke database.</p>
                </div>

                <div class="ai-guide-section">
                    <h4>Format Hasil Produk</h4>
                    <div class="ai-guide-example">
                        1. Baby Walker – Alat Bantu Jalan Bayi.<br>
                        2. Baby Stroller – Kereta Dorong Bayi.
                    </div>
                    <ul class="ai-guide-list">
                        <li>Pisahkan nama dengan en-dash (<code>–</code>)</li>
                        <li>Akhiri setiap baris dengan tanda titik</li>
                        <li>Gunakan nama Indonesia yang ringkas</li>
                    </ul>
                </div>

                <div class="ai-guide-section">
                    <h4>Jeda Laju</h4>
                    <p>Sistem menerapkan jeda 2 detik antarperusahaan dan menangani respons HTTP 429 tanpa menandai perusahaan sebagai gagal.</p>
                </div>
            </div>
        </aside>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
const defaultPromptText = <?= json_encode($defaultPrompt) ?>;

function resetPromptToDefault() {
    if (confirm('Pulihkan teks instruksi AI ke template standar?')) {
        document.getElementById('instruksiAi').value = defaultPromptText;
    }
}

function testConnection() {
    const btn = document.getElementById('btnTestConn');
    const resultBox = document.getElementById('testResultBox');

    btn.disabled = true;
    btn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Menguji...';
    resultBox.style.display = 'none';

    fetch('<?= base_url('/pengaturan/test-connection') ?>', {
        method: 'POST',
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(r => r.json())
    .then(res => {
        btn.disabled = false;
        btn.innerHTML = '<i class="fa fa-plug"></i> Uji Koneksi API';
        resultBox.style.display = 'block';

        if (res.success) {
            resultBox.className = 'ai-result success';
            resultBox.innerHTML = '<i class="fa fa-check-circle"></i> <strong>' + res.message + '</strong>';
        } else {
            resultBox.className = 'ai-result danger';
            resultBox.innerHTML = '<i class="fa fa-times-circle"></i> <strong>Koneksi Gagal:</strong> ' + res.message;
        }
    })
    .catch(err => {
        btn.disabled = false;
        btn.innerHTML = '<i class="fa fa-plug"></i> Uji Koneksi API';
        resultBox.style.display = 'block';
        resultBox.className = 'ai-result danger';
        resultBox.innerHTML = '<i class="fa fa-times-circle"></i> <strong>Kesalahan Jaringan:</strong> ' + err.message;
    });
}
</script>
<?= $this->endSection() ?>
