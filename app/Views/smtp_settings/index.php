<?= $this->extend('layouts/main') ?>

<?= $this->section('title') ?>Konfigurasi SMTP<?= $this->endSection() ?>
<?= $this->section('page_title') ?>Konfigurasi SMTP<?= $this->endSection() ?>
<?= $this->section('page_subtitle') ?>Manajemen Akun SMTP Pengirim<?= $this->endSection() ?>

<?= $this->section('breadcrumb') ?>
<li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
<li class="active">Konfigurasi SMTP</li>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<?php if (session()->getFlashdata('success')): ?>
    <div class="alert alert-success alert-dismissible">
        <button type="button" class="close" data-dismiss="alert">&times;</button>
        <i class="icon fa fa-check"></i> <?= esc(session()->getFlashdata('success')) ?>
    </div>
<?php endif; ?>
<?php if (session()->getFlashdata('error')): ?>
    <div class="alert alert-danger alert-dismissible">
        <button type="button" class="close" data-dismiss="alert">&times;</button>
        <i class="icon fa fa-ban"></i> <?= esc(session()->getFlashdata('error')) ?>
    </div>
<?php endif; ?>

<div class="box box-primary">
    <div class="box-header with-border">
        <h3 class="box-title"><i class="fa fa-server"></i> Daftar Konfigurasi SMTP</h3>
        <div class="box-tools pull-right">
            <a href="<?= base_url('smtp/create') ?>" class="btn btn-primary btn-sm">
                <i class="fa fa-plus"></i> Tambah Konfigurasi
            </a>
        </div>
    </div>
    <div class="box-body">
        <div class="table-responsive">
            <table id="smtpTable" class="table table-bordered table-striped table-hover" style="width:100%;">
                <thead>
                    <tr>
                        <th width="50">No</th>
                        <th>Nama</th>
                        <th>Host</th>
                        <th>Username</th>
                        <th width="100">Port</th>
                        <th width="80">Crypto</th>
                        <th>Pengirim</th>
                        <th width="100">Status</th>
                        <th width="220">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($items as $i => $row): ?>
                    <tr>
                        <td><?= $i + 1 ?></td>
                        <td><strong><?= esc($row['nama']) ?></strong></td>
                        <td><code><?= esc($row['host']) ?></code></td>
                        <td><?= esc($row['username']) ?></td>
                        <td><?= esc($row['port']) ?></td>
                        <td><span class="label label-default"><?= esc(strtoupper($row['crypto'])) ?></span></td>
                        <td>
                            <?= esc($row['from_email']) ?><br>
                            <small class="text-muted"><?= esc($row['from_name']) ?></small>
                        </td>
                        <td>
                            <?php if ((int) $row['is_active'] === 1): ?>
                                <span class="label label-success"><i class="fa fa-check"></i> Aktif</span>
                            <?php else: ?>
                                <span class="label label-default">Nonaktif</span>
                            <?php endif; ?>
                        </td>
                        <td style="white-space:nowrap;">
                            <?php if ((int) $row['is_active'] !== 1): ?>
                                <a href="<?= base_url('smtp/' . $row['id'] . '/activate') ?>"
                                   class="btn btn-success btn-xs"
                                   onclick="return confirm('Aktifkan konfigurasi ini?')">
                                    <i class="fa fa-power-off"></i> Aktifkan
                                </a>
                            <?php endif; ?>
                            <a href="<?= base_url('smtp/' . $row['id'] . '/edit') ?>" class="btn btn-warning btn-xs">
                                <i class="fa fa-pencil"></i> Edit
                            </a>
                            <button type="button" class="btn btn-info btn-xs btn-test"
                                    data-id="<?= $row['id'] ?>"
                                    data-nama="<?= esc($row['nama']) ?>"
                                    data-email="<?= esc($row['from_email']) ?>">
                                <i class="fa fa-paper-plane"></i> Test
                            </button>
                            <?php if ((int) $row['is_active'] !== 1): ?>
                                <a href="<?= base_url('smtp/' . $row['id'] . '/delete') ?>"
                                   class="btn btn-danger btn-xs"
                                   onclick="return confirm('Hapus konfigurasi ini?')">
                                    <i class="fa fa-trash"></i>
                                </a>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <p class="help-block">
            <i class="fa fa-info-circle"></i>
            Hanya <strong>satu</strong> konfigurasi yang boleh aktif. Kampanye yang tidak memilih SMTP sendiri
            akan memakai konfigurasi aktif ini.
        </p>
    </div>
</div>

<!-- Modal Test -->
<div class="modal fade" id="modalTest" tabindex="-1" role="dialog">
    <div class="modal-dialog">
        <form method="post" id="formTest">
            <?= csrf_field() ?>
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title"><i class="fa fa-paper-plane"></i> Test Kirim Email</h4>
                </div>
                <div class="modal-body">
                    <p>Konfigurasi: <strong id="testNama"></strong></p>
                    <div class="form-group">
                        <label>Email Tujuan</label>
                        <input type="email" name="test_email" id="testEmail" class="form-control" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary"><i class="fa fa-send"></i> Kirim Test</button>
                </div>
            </div>
        </form>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
(function () {
'use strict';

function mulai() {
    // DataTables
    if (window.jQuery && window.jQuery.fn.DataTable) {
        window.jQuery('#smtpTable').DataTable({
            autoWidth: false,
            pageLength: 10,
            columnDefs: [
                { targets: 0, orderable: false, searchable: false },
                { targets: -1, orderable: false, searchable: false }
            ],
            drawCallback: function () {
                const api = this.api();
                api.column(0, { page: 'current' }).nodes().each(function (cell, i) {
                    cell.innerHTML = api.page.info().start + i + 1;
                });
            },
            language: {
                search: 'Cari:', lengthMenu: 'Tampilkan _MENU_ data',
                info: 'Menampilkan _START_ - _END_ dari _TOTAL_ data',
                infoEmpty: 'Tidak ada data', zeroRecords: 'Tidak ada data yang cocok',
                emptyTable: 'Belum ada konfigurasi SMTP',
                paginate: { first: 'Pertama', last: 'Terakhir', next: 'Selanjutnya', previous: 'Sebelumnya' }
            }
        });
    }

    // Modal Test
    const modal = document.getElementById('modalTest');
    const form  = document.getElementById('formTest');

    document.querySelectorAll('.btn-test').forEach(function (btn) {
        btn.addEventListener('click', function () {
            const id = this.dataset.id;
            form.action = '<?= base_url('smtp') ?>/' + id + '/test';
            document.getElementById('testNama').textContent = this.dataset.nama;
            document.getElementById('testEmail').value = this.dataset.email;

            if (window.jQuery && window.jQuery.fn.modal) {
                window.jQuery(modal).modal('show');
            } else {
                modal.style.display = 'block';
                modal.classList.add('in');
            }
        });
    });
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', mulai);
} else { mulai(); }

})();
</script>
<?= $this->endSection() ?>