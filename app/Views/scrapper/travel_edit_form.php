<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Data - <?= esc($data['nama'] ?? 'Data') ?></title>

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
            --ink: #0f172a;
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

        * {
            box-sizing: border-box;
        }

        html {
            scroll-behavior: smooth;
        }

        body {
            min-height: 100vh;
            background: var(--bg);
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            color: var(--ink);
            -webkit-font-smoothing: antialiased;
            background-image: radial-gradient(circle at 10% 0%, rgba(37, 99, 235, 0.05) 0%, transparent 50%),
                radial-gradient(circle at 100% 10%, rgba(37, 99, 235, 0.05) 0%, transparent 50%);
        }

        .page-wrapper {
            max-width: 1120px;
            margin: 0 auto;
            padding: 32px 24px 60px;
        }

        @media (max-width:576px) {
            .page-wrapper {
                padding: 20px 16px 40px;
            }
        }

        /* HEADER */
        .header-card {
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

        .header-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, var(--primary), #60a5fa, #a78bfa);
        }

        .header-card:hover {
            box-shadow: var(--shadow-lg);
        }

        .header-title {
            display: flex;
            align-items: center;
            gap: 12px;
            font-size: 27px;
            font-weight: 800;
            margin: 0;
        }

        .header-title i {
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

        .header-description {
            color: var(--muted);
            margin: 8px 0 0 0;
            line-height: 1.6;
        }

        /* FORM CARD */
        .form-card {
            background: var(--surface);
            border-radius: var(--radius-lg);
            padding: 36px 40px;
            box-shadow: var(--shadow-md);
            border: 1px solid rgba(226, 232, 240, 0.6);
            transition: var(--transition);
        }

        .form-card:hover {
            box-shadow: var(--shadow-lg);
        }

        .form-section-title {
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

        .form-section-title i {
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

        .form-label {
            font-weight: 700;
            font-size: 14px;
            color: var(--ink-light);
            margin-bottom: 8px;
        }

        .required {
            color: var(--danger);
        }

        .form-control {
            min-height: 48px;
            border-radius: var(--radius-sm);
            border: 1px solid #d7dee8;
            padding: 11px 16px;
            color: var(--ink);
            background: #fff;
            transition: var(--transition);
        }

        .form-control:hover {
            border-color: #b9c4d2;
        }

        .form-control:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 0.22rem rgba(37, 99, 235, 0.12);
            background: #fff;
        }

        textarea.form-control {
            min-height: 120px;
            resize: vertical;
        }

        .input-group-text {
            min-width: 48px;
            justify-content: center;
            border-radius: var(--radius-sm) 0 0 var(--radius-sm);
            background: #f8fafc;
            border-color: #d7dee8;
            color: var(--muted);
        }

        .input-group .form-control {
            border-radius: 0 var(--radius-sm) var(--radius-sm) 0;
        }

        .help-text {
            color: var(--muted-light);
            font-size: 12px;
            margin-top: 6px;
            line-height: 1.5;
        }

        .form-actions {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 12px;
            margin-top: 32px;
            padding-top: 24px;
            border-top: 1px solid var(--line);
        }

        .btn-custom {
            min-height: 48px;
            padding: 11px 24px;
            border-radius: var(--radius-sm);
            font-weight: 700;
            border: 0;
            transition: var(--transition);
        }

        .btn-custom:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-sm);
        }

        .btn-primary-custom {
            background: var(--primary);
            color: #fff;
        }

        .btn-primary-custom:hover {
            background: var(--primary-dark);
        }

        .btn-outline-secondary-custom {
            background: transparent;
            border: 1px solid var(--line);
            color: var(--muted);
        }

        .btn-outline-secondary-custom:hover {
            background: var(--bg);
            border-color: #b9c4d2;
        }

        @media (max-width:768px) {
            .header-card {
                padding: 24px 20px;
            }

            .form-card {
                padding: 24px 20px;
            }

            .header-title {
                font-size: 22px;
            }

            .form-actions {
                flex-direction: column-reverse;
                align-items: stretch;
            }

            .form-actions .btn {
                width: 100%;
            }
        }
    </style>
</head>

<body>

    <div class="page-wrapper">

        <!-- HEADER -->
        <div class="header-card">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
                <div>
                    <h1 class="header-title"><i class="fa-solid fa-pen-to-square"></i> Edit Data Scraping</h1>
                    <p class="header-description">Perbarui informasi tempat dengan rapi, lalu simpan perubahan.</p>
                </div>
                <a href="<?= site_url('travel/edit-data/' . rawurlencode($data['keyword'] ?? '')) ?>" class="btn btn-secondary btn-custom"><i class="fa-solid fa-arrow-left me-2"></i> Kembali</a>
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

            <form action="<?= site_url('travel/update/' . (int)($data['id'] ?? 0)) ?>" method="post">
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

                <!-- Nama -->
                <div class="mb-4">
                    <label for="nama" class="form-label">Nama Tempat <span class="required">*</span></label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fa-solid fa-building"></i></span>
                        <input type="text" name="nama" id="nama" class="form-control" value="<?= esc(old('nama', $data['nama'] ?? '')) ?>" maxlength="255" required>
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
                    <a href="<?= site_url('travel/edit-data/' . rawurlencode($data['keyword'] ?? '')) ?>" class="btn btn-outline-secondary btn-custom"><i class="fa-solid fa-xmark me-2"></i> Batal</a>
                    <button type="submit" class="btn btn-primary btn-custom"><i class="fa-solid fa-floppy-disk me-2"></i> Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>