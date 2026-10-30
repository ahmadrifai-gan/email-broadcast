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

<?php if (session()->getFlashdata('error')): ?>
    <div class="alert alert-danger alert-dismissible">
        <button type="button" class="close" data-dismiss="alert">&times;</button>
        <i class="icon fa fa-ban"></i> <?= esc(session()->getFlashdata('error')) ?>
    </div>
<?php endif; ?>

<?php $errors = session('errors') ?? []; ?>

<div class="box box-primary">
    <div class="box-header with-border">
        <h3 class="box-title"><i class="fa fa-server"></i> <?= esc($title) ?></h3>
        <div class="box-tools pull-right">
            <a href="<?= base_url('smtp') ?>" class="btn btn-default btn-sm"><i class="fa fa-arrow-left"></i> Kembali</a>
        </div>
    </div>
    <form method="post" action="<?= $action ?>" autocomplete="off">
        <?= csrf_field() ?>
        <div class="box-body">
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Nama Konfigurasi <span class="text-danger">*</span></label>
                        <input type="text" name="nama" class="form-control <?= isset($errors['nama']) ? 'is-invalid' : '' ?>"
                               value="<?= esc(old('nama', $item['nama'] ?? '')) ?>" placeholder="cth: Gmail Utama" required>
                        <?php if (isset($errors['nama'])): ?><small class="text-danger"><?= esc($errors['nama']) ?></small><?php endif; ?>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Host SMTP <span class="text-danger">*</span></label>
                        <input type="text" name="host" class="form-control <?= isset($errors['host']) ? 'is-invalid' : '' ?>"
                               value="<?= esc(old('host', $item['host'] ?? 'smtp.gmail.com')) ?>" placeholder="smtp.gmail.com" required>
                        <?php if (isset($errors['host'])): ?><small class="text-danger"><?= esc($errors['host']) ?></small><?php endif; ?>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Username <span class="text-danger">*</span></label>
                        <input type="text" name="username" class="form-control <?= isset($errors['username']) ? 'is-invalid' : '' ?>"
                               value="<?= esc(old('username', $item['username'] ?? '')) ?>" required>
                        <?php if (isset($errors['username'])): ?><small class="text-danger"><?= esc($errors['username']) ?></small><?php endif; ?>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
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
                    <div class="form-group">
                        <label>Port <span class="text-danger">*</span></label>
                        <input type="number" name="port" class="form-control"
                               value="<?= esc(old('port', $item['port'] ?? 587)) ?>" required>
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
                    <div class="form-group">
                        <label>From Email <span class="text-danger">*</span></label>
                        <input type="email" name="from_email" class="form-control <?= isset($errors['from_email']) ? 'is-invalid' : '' ?>"
                               value="<?= esc(old('from_email', $item['from_email'] ?? '')) ?>" required>
                        <?php if (isset($errors['from_email'])): ?><small class="text-danger"><?= esc($errors['from_email']) ?></small><?php endif; ?>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
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