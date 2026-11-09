<?= $this->extend('layouts/main') ?>

<?= $this->section('title') ?><?= esc($title) ?><?= $this->endSection() ?>
<?= $this->section('page_title') ?><?= esc($title) ?><?= $this->endSection() ?>
<?= $this->section('page_subtitle') ?>Form Konfigurasi SMTP<?= $this->endSection() ?>

<?= $this->section('breadcrumb') ?>
<li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
<li><a href="<?= base_url('smtp') ?>">Konfigurasi SMTP</a></li>
<li class="active"><?= $item ? 'Edit' : 'Tambah' ?></li>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<?php /* Flash message ditampilkan lewat SweetAlert2 (lihat section scripts) */ ?>

<?php $errors = session('errors') ?? []; ?>

<div class="box box-primary">
    <div class="box-header with-border">
        <h3 class="box-title"><i class="fa fa-server"></i> <?= esc($title) ?></h3>
        <div class="box-tools pull-right">
            <a href="<?= base_url('smtp') ?>" class="btn btn-default btn-sm"><i class="fa fa-arrow-left"></i> Kembali</a>
        </div>
    </div>
    <form method="post" action="<?= $action ?>" autocomplete="off" id="formSmtp">
        <?= csrf_field() ?>
        <div class="box-body">
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group <?= isset($errors['nama']) ? 'has-error' : '' ?>">
                        <label>Nama Konfigurasi <span class="text-danger">*</span></label>
                        <input type="text" name="nama" class="form-control <?= isset($errors['nama']) ? 'is-invalid' : '' ?>"
                               value="<?= esc(old('nama', $item['nama'] ?? '')) ?>" placeholder="cth: Gmail Utama" required>
                        <?php if (isset($errors['nama'])): ?><small class="text-danger"><?= esc($errors['nama']) ?></small><?php endif; ?>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group <?= isset($errors['host']) ? 'has-error' : '' ?>">
                        <label>Host SMTP <span class="text-danger">*</span></label>
                        <input type="text" name="host" class="form-control <?= isset($errors['host']) ? 'is-invalid' : '' ?>"
                               value="<?= esc(old('host', $item['host'] ?? 'smtp.gmail.com')) ?>" placeholder="smtp.gmail.com" required>
                        <?php if (isset($errors['host'])): ?><small class="text-danger"><?= esc($errors['host']) ?></small><?php endif; ?>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group <?= isset($errors['username']) ? 'has-error' : '' ?>">
                        <label>Username <span class="text-danger">*</span></label>
                        <input type="text" name="username" class="form-control <?= isset($errors['username']) ? 'is-invalid' : '' ?>"
                               value="<?= esc(old('username', $item['username'] ?? '')) ?>" required>
                        <?php if (isset($errors['username'])): ?><small class="text-danger"><?= esc($errors['username']) ?></small><?php endif; ?>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group <?= isset($errors['password']) ? 'has-error' : '' ?>">
                        <label>
                            Password
                            <?php if ($item): ?>
                                <small class="text-muted">(kosongkan bila tidak diubah)</small>
                            <?php else: ?>
                                <span class="text-danger">*</span>
                            <?php endif; ?>
                        </label>
                        <input type="password" name="password" class="form-control"
                               autocomplete="new-password" <?= $item ? '' : 'required' ?>>
                        <?php if (isset($errors['password'])): ?><small class="text-danger"><?= esc($errors['password']) ?></small><?php endif; ?>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group <?= isset($errors['port']) ? 'has-error' : '' ?>">
                        <label>Port <span class="text-danger">*</span></label>
                        <input type="number" name="port" class="form-control"
                               value="<?= esc(old('port', $item['port'] ?? 587)) ?>" required>
                        <?php if (isset($errors['port'])): ?><small class="text-danger"><?= esc($errors['port']) ?></small><?php endif; ?>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label>Crypto <span class="text-danger">*</span></label>
                        <?php $cur = old('crypto', $item['crypto'] ?? 'tls'); ?>
                        <select name="crypto" class="form-control">
                            <option value="tls"  <?= $cur === 'tls'  ? 'selected' : '' ?>>TLS</option>
                            <option value="ssl"  <?= $cur === 'ssl'  ? 'selected' : '' ?>>SSL</option>
                            <option value="none" <?= $cur === 'none' ? 'selected' : '' ?>>None</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group <?= isset($errors['from_email']) ? 'has-error' : '' ?>">
                        <label>From Email <span class="text-danger">*</span></label>
                        <input type="email" name="from_email" class="form-control <?= isset($errors['from_email']) ? 'is-invalid' : '' ?>"
                               value="<?= esc(old('from_email', $item['from_email'] ?? '')) ?>" required>
                        <?php if (isset($errors['from_email'])): ?><small class="text-danger"><?= esc($errors['from_email']) ?></small><?php endif; ?>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group <?= isset($errors['from_name']) ? 'has-error' : '' ?>">
                        <label>From Name <span class="text-danger">*</span></label>
                        <input type="text" name="from_name" class="form-control <?= isset($errors['from_name']) ? 'is-invalid' : '' ?>"
                               value="<?= esc(old('from_name', $item['from_name'] ?? 'email-broadcast')) ?>" required>
                        <?php if (isset($errors['from_name'])): ?><small class="text-danger"><?= esc($errors['from_name']) ?></small><?php endif; ?>
                    </div>
                </div>
                <div class="col-md-12">
                    <div class="checkbox">
                        <label>
                            <input type="checkbox" name="is_active" value="1"
                                   <?= old('is_active', $item['is_active'] ?? 0) ? 'checked' : '' ?>>
                            Jadikan konfigurasi aktif (default untuk kampanye yang tidak memilih SMTP)
                        </label>
                    </div>
                </div>
            </div>
        </div>
        <div class="box-footer">
            <a href="<?= base_url('smtp') ?>" class="btn btn-default">Batal</a>
            <button type="submit" class="btn btn-primary pull-right"><i class="fa fa-save"></i> Simpan</button>
        </div>
    </form>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<?php
    $flashSwal = [
        'success' => session()->getFlashdata('success'),
        'error'   => session()->getFlashdata('error'),
        'errors'  => (array) session()->getFlashdata('errors'),
    ];
?>
<?= $this->include('layouts/swal_helper') ?>
<script>
(function () {
'use strict';

function mulai() {
    AppSwal.flash(<?= json_encode($flashSwal, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE) ?>);

    const form       = document.getElementById('formSmtp');
    const WAS_ACTIVE = <?= !empty($item['is_active']) ? 'true' : 'false' ?>;
    const NAMA_AWAL  = <?= json_encode((string) ($item['nama'] ?? ''), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE) ?>;
    let sedang = false;

    form.addEventListener('submit', async function (e) {
        if (form.dataset.lolos === '1') return;      // submit native setelah dikonfirmasi
        e.preventDefault();
        if (sedang) return;
        sedang = true;
        let lanjut = false;

        try {
            const cb    = form.querySelector('input[name="is_active"]');
            const aktif = !!(cb && cb.checked);
            const nama  = (form.querySelector('input[name="nama"]').value || '').trim();

            if (aktif && !WAS_ACTIVE) {
                const ok = await AppSwal.konfirmasi({
                    judul: 'Jadikan konfigurasi aktif?',
                    html:  'Konfigurasi <b>' + AppSwal.esc(nama || 'ini') + '</b> akan menjadi SMTP aktif. ' +
                           'Konfigurasi aktif lainnya dinonaktifkan, dan kampanye yang tidak memilih SMTP sendiri akan memakai konfigurasi ini.',
                    ikon:  'question',
                    ya:    'Ya, simpan & aktifkan'
                });
                if (!ok) return;
            } else if (!aktif && WAS_ACTIVE) {
                const ok = await AppSwal.konfirmasi({
                    judul: 'Nonaktifkan konfigurasi ini?',
                    html:  'Konfigurasi <b>' + AppSwal.esc(NAMA_AWAL || nama) + '</b> sedang aktif. ' +
                           'Jika tidak ada konfigurasi aktif, kampanye yang tidak memilih SMTP sendiri akan memakai pengaturan di <code>.env</code>.',
                    ikon:  'warning',
                    ya:    'Ya, nonaktifkan',
                    bahaya: true
                });
                if (!ok) return;
            }

            AppSwal.loading('Menyimpan konfigurasi...', 'Mohon tunggu sebentar.');
            lanjut = true;
            form.dataset.lolos = '1';
            HTMLFormElement.prototype.submit.call(form);
        } finally {
            if (!lanjut) sedang = false;
        }
    });

    // Tombol "Kembali" browser (bfcache) -> pulihkan form
    window.addEventListener('pageshow', function (ev) {
        if (!ev.persisted) return;
        AppSwal.tutup();
        form.dataset.lolos = '';
        sedang = false;
    });
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', mulai);
} else { mulai(); }

})();
</script>
<?= $this->endSection() ?>