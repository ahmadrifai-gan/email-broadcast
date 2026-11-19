<?= $this->extend('layouts/main') ?>

<?= $this->section('title') ?>Edit Data - <?= esc($data['nama_perusahaan'] ?? 'Data') ?><?= $this->endSection() ?>
<?= $this->section('page_title') ?>Edit Data Scraping<?= $this->endSection() ?>

<?= $this->section('breadcrumb') ?>
<li><a href="<?= site_url('/') ?>"><i class="fa fa-dashboard"></i> Home</a></li>
<li><a href="<?= site_url('scrapper/edit-data') ?>">Edit Data Scrape</a></li>
<li class="active">Edit Data</li>
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
        --ink: #0f172a;
        --ink-light: #1e293b;
        --muted: #64748b;
        --muted-light: #94a3b8;
        --line: #e2e8f0;
        --bg: #f1f5f9;
        --surface: #ffffff;
        --shadow-sm: 0 2px 8px rgba(15, 23, 42, 0.06);
        --shadow-md: 0 8px 30px rgba(15, 23, 42, 0.08);
        --shadow-lg: 0 20px 50px rgba(15, 23, 42, 0.12);
        --radius-sm: 10px;
        --radius-md: 16px;
        --radius-lg: 24px;
        --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }

    /* ===== SCOPED UTILITIES ===== */
    .page-wrapper * { box-sizing: border-box; }
    .page-wrapper a { text-decoration: none; }
    .page-wrapper .d-flex { display: flex !important; }
    .page-wrapper .flex-column { flex-direction: column !important; }
    .page-wrapper .justify-content-between { justify-content: space-between !important; }
    @media (min-width: 768px) {
        .page-wrapper .flex-md-row { flex-direction: row !important; }
        .page-wrapper .align-items-md-center { align-items: center !important; }
    }
    .page-wrapper .gap-3 { gap: 16px !important; }
    .page-wrapper .mb-0 { margin-bottom: 0 !important; }
    .page-wrapper .mb-4 { margin-bottom: 24px !important; }
    .page-wrapper .mt-2 { margin-top: 8px !important; }
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
    .page-wrapper .btn-outline-secondary { background: transparent; border: 1px solid var(--line); color: var(--muted); }
    .page-wrapper .btn-outline-secondary:hover { background: var(--bg); border-color: #b9c4d2; color: var(--ink); }
    .page-wrapper .alert.fade.show { opacity: 1; }
    .page-wrapper .alert-dismissible { position: relative; padding-right: 44px; }
    .page-wrapper .btn-close {
        position: absolute; top: 50%; right: 16px; transform: translateY(-50%);
        background: transparent; border: 0; font-size: 20px; line-height: 1;
        color: inherit; opacity: 0.6; cursor: pointer;
    }
    .page-wrapper .btn-close::before { content: "\00d7"; }
    .page-wrapper .btn-close:hover { opacity: 1; }
    .page-wrapper .input-group { display: flex; align-items: stretch; width: 100%; }
    .page-wrapper .input-group-text {
        display: flex; align-items: center; padding: 6px 12px;
        font-size: 14px; border: 1px solid #d7dee8; border-right: 0;
    }
    .page-wrapper .input-group > .form-control { flex: 1 1 auto; width: 1%; min-width: 0; }

    .page-wrapper {
        max-width: 1120px;
        margin: 0 auto;
        padding: 32px 24px 60px;
        min-height: 100vh;
        background: var(--bg);
        font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
        font-size: 14px;
        color: var(--ink);
        -webkit-font-smoothing: antialiased;
        background-image: radial-gradient(circle at 10% 0%, rgba(37, 99, 235, 0.05) 0%, transparent 50%),
            radial-gradient(circle at 100% 10%, rgba(37, 99, 235, 0.05) 0%, transparent 50%);
        border-radius: 16px;
    }

    @media (max-width:576px) {
        .page-wrapper {
            padding: 20px 16px 40px;
        }
    }

    /* HEADER */
    .page-wrapper .header-card {
        background: var(--surface);
        border-radius: var(--radius-lg);
        padding: 32px 36px;
        margin-bottom: 28px;
        box-shadow: var(--shadow-md);
        border: 1px solid rgba(226, 232, 240, 0.6);
        transition: var(--transition);
        position: relative;
        overflow: hidden;
    }

    .page-wrapper .header-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
        background: linear-gradient(90deg, var(--primary), #60a5fa, #a78bfa);
    }

    .page-wrapper .header-card:hover {
        box-shadow: var(--shadow-lg);
    }

    .page-wrapper .header-title {
        display: flex;
        align-items: center;
        gap: 12px;
        font-size: 27px;
        font-weight: 800;
        margin: 0;
    }

    .page-wrapper .header-title i {
        width: 44px;
        height: 44px;
        border-radius: var(--radius-sm);
        display: flex;
        align-items: center;
        justify-content: center;
        color: var(--primary);
        background: var(--primary-soft);
        font-size: 18px;
    }

    .page-wrapper .header-description {
        color: var(--muted);
        margin: 8px 0 0 0;
        line-height: 1.6;
    }

    /* FORM CARD */
    .page-wrapper .form-card {
        background: var(--surface);
        border-radius: var(--radius-lg);
        padding: 36px 40px;
        box-shadow: var(--shadow-md);
        border: 1px solid rgba(226, 232, 240, 0.6);
        transition: var(--transition);
    }

    .page-wrapper .form-card:hover {
        box-shadow: var(--shadow-lg);
    }

    .page-wrapper .form-section-title {
        display: flex;
        align-items: center;
        gap: 10px;
        font-size: 13px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.08em;
        color: #475569;
        margin-bottom: 28px;
        padding-bottom: 16px;
        border-bottom: 1px solid var(--line);
    }

    .page-wrapper .form-section-title i {
        width: 34px;
        height: 34px;
        border-radius: var(--radius-sm);
        display: flex;
        align-items: center;
        justify-content: center;
        background: var(--primary-soft);
        color: var(--primary);
        font-size: 14px;
    }

    .page-wrapper .form-label {
        font-weight: 700;
        font-size: 14px;
        color: var(--ink-light);
        margin-bottom: 8px;
        display: block;
    }

    .page-wrapper .required {
        color: var(--danger);
    }

    .page-wrapper .form-control {
        height: auto;
        min-height: 48px;
        font-size: 14px;
        border-radius: var(--radius-sm);
        border: 1px solid #d7dee8;
        padding: 11px 16px;
        color: var(--ink);
        background: #fff;
        box-shadow: none;
        transition: var(--transition);
    }

    .page-wrapper .form-control:hover {
        border-color: #b9c4d2;
    }

    .page-wrapper .form-control:focus {
        border-color: var(--primary);
        box-shadow: 0 0 0 0.22rem rgba(37, 99, 235, 0.12);
        background: #fff;
    }

    .page-wrapper textarea.form-control {
        min-height: 120px;
        resize: vertical;
    }

    .page-wrapper .input-group-text {
        min-width: 48px;
        justify-content: center;
        border-radius: var(--radius-sm) 0 0 var(--radius-sm);
        background: #f8fafc;
        border-color: #d7dee8;
        color: var(--muted);
    }

    .page-wrapper .input-group .form-control {
        border-radius: 0 var(--radius-sm) var(--radius-sm) 0;
    }

    .page-wrapper .help-text {
        color: var(--muted-light);
        font-size: 12px;
        margin-top: 6px;
        line-height: 1.5;
    }

    .page-wrapper .form-actions {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 12px;
        margin-top: 32px;
        padding-top: 24px;
        border-top: 1px solid var(--line);
    }

    .page-wrapper .btn-custom {
        min-height: 48px;
        padding: 11px 24px;
        border-radius: var(--radius-sm);
        font-weight: 700;
        border: 0;
        transition: var(--transition);
    }

    .page-wrapper .btn-custom:hover {
        transform: translateY(-2px);
        box-shadow: var(--shadow-sm);
    }

    .page-wrapper .btn-primary-custom {
        background: var(--primary);
        color: #fff;
    }

    .page-wrapper .btn-primary-custom:hover {
        background: var(--primary-dark);
    }

    .page-wrapper .btn-outline-secondary-custom {
        background: transparent;
        border: 1px solid var(--line);
        color: var(--muted);
    }

    .page-wrapper .btn-outline-secondary-custom:hover {
        background: var(--bg);
        border-color: #b9c4d2;
    }

    @media (max-width:768px) {
        .page-wrapper .header-card {
            padding: 24px 20px;
        }

        .page-wrapper .form-card {
            padding: 24px 20px;
        }

        .page-wrapper .header-title {
            font-size: 22px;
        }

        .page-wrapper .form-actions {
            flex-direction: column-reverse;
            align-items: stretch;
        }

        .page-wrapper .form-actions .btn {
            width: 100%;
        }
    }
</style>

    <div class="page-wrapper">

        <!-- HEADER -->
        <div class="header-card">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
                <div>
                    <h1 class="header-title"><i class="fa-solid fa-pen-to-square"></i> Edit Data Scraping</h1>
                    <p class="header-description">Perbarui informasi tempat dengan rapi, lalu simpan perubahan.</p>
                </div>
                <a href="<?= site_url('scrapper/edit-data/' . rawurlencode($data['keyword'] ?? '')) ?>" class="btn btn-secondary btn-custom"><i class="fa-solid fa-arrow-left me-2"></i> Kembali</a>
            </div>
        </div>

        <!-- ALERT -->
        <?php if (session()->getFlashdata('error')): ?>
            <div class="alert alert-danger alert-dismissible fade show mb-4 border-0 shadow-sm">
                <i class="fa-solid fa-circle-exclamation me-2"></i> <?= esc(session()->getFlashdata('error')) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php $errors = session()->getFlashdata('errors'); ?>
        <?php if (!empty($errors)): ?>
            <div class="alert alert-warning mb-4 border-0 shadow-sm">
                <strong>Periksa data berikut:</strong>
                <ul class="mb-0 mt-2">
                    <?php foreach ($errors as $error): ?>
                        <li><?= esc($error) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <!-- FORM -->
        <div class="form-card">
            <div class="form-section-title">
                <i class="fa-solid fa-circle-info"></i> Informasi Data
            </div>

            <form action="<?= site_url('scrapper/update/' . (int)($data['id'] ?? 0)) ?>" method="post">
                <?= csrf_field() ?>

                <!-- Keyword -->
                <div class="mb-4">
                    <label for="keyword" class="form-label">Keyword <span class="required">*</span></label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fa-solid fa-magnifying-glass"></i></span>
                        <input type="text" name="keyword" id="keyword" class="form-control" value="<?= esc(old('keyword', $data['keyword'] ?? '')) ?>" maxlength="255" required>
                    </div>
                    <div class="help-text">Keyword yang digunakan saat melakukan scraping.</div>
                </div>

                <!-- Nama Perusahaan -->
                <div class="mb-4">
                    <label for="nama_perusahaan" class="form-label">Nama Tempat <span class="required">*</span></label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fa-solid fa-building"></i></span>
                        <input type="text" name="nama_perusahaan" id="nama_perusahaan" class="form-control" value="<?= esc(old('nama_perusahaan', $data['nama_perusahaan'] ?? '')) ?>" maxlength="255" required>
                    </div>
                </div>

                <!-- Alamat -->
                <div class="mb-4">
                    <label for="alamat" class="form-label">Alamat</label>
                    <textarea name="alamat" id="alamat" class="form-control" maxlength="500" rows="4"><?= esc(old('alamat', $data['alamat'] ?? '')) ?></textarea>
                </div>

                <!-- Nomor -->
                <div class="mb-4">
                    <label for="nomor" class="form-label">Nomor HP / Telepon</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fa-solid fa-phone"></i></span>
                        <input type="text" name="nomor" id="nomor" class="form-control" value="<?= esc(old('nomor', $data['nomor'] ?? '')) ?>" maxlength="50">
                    </div>
                </div>

                <!-- Email -->
                <div class="mb-4">
                    <label for="email" class="form-label">Email</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fa-solid fa-envelope"></i></span>
                        <input type="email" name="email" id="email" class="form-control" value="<?= esc(old('email', $data['email'] ?? '')) ?>" maxlength="255">
                    </div>
                </div>

                <!-- Website -->
                <div class="mb-4">
                    <label for="website" class="form-label">Website</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fa-solid fa-globe"></i></span>
                        <input type="url" name="website" id="website" class="form-control" value="<?= esc(old('website', $data['website'] ?? '')) ?>" maxlength="500" placeholder="https://contoh.com">
                    </div>
                </div>

                <!-- Google Maps -->
                <div class="mb-4">
                    <label for="maps_url" class="form-label">Google Maps</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fa-solid fa-map-location-dot"></i></span>
                        <input type="url" name="maps_url" id="maps_url" class="form-control" value="<?= esc(old('maps_url', $data['maps_url'] ?? '')) ?>" maxlength="1000" placeholder="https://www.google.com/maps/...">
                    </div>
                    <div class="help-text">Masukkan URL Google Maps jika ingin mengubah lokasi.</div>
                </div>

                <!-- Actions -->
                <div class="form-actions">
                    <a href="<?= site_url('scrapper/edit-data/' . rawurlencode($data['keyword'] ?? '')) ?>" class="btn btn-outline-secondary btn-custom"><i class="fa-solid fa-xmark me-2"></i> Batal</a>
                    <button type="submit" class="btn btn-primary btn-custom"><i class="fa-solid fa-floppy-disk me-2"></i> Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
document.querySelectorAll('.page-wrapper [data-bs-dismiss="alert"]').forEach(btn => {
    btn.addEventListener('click', () => {
        const alert = btn.closest('.alert');
        if (alert) alert.remove();
    });
});
</script>
<?= $this->endSection() ?>