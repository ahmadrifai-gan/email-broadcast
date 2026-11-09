<?= $this->extend('layouts/main') ?>

<?= $this->section('title') ?>Konfigurasi SMTP<?= $this->endSection() ?>
<?= $this->section('page_title') ?>Konfigurasi SMTP<?= $this->endSection() ?>
<?= $this->section('page_subtitle') ?>Manajemen Akun SMTP Pengirim<?= $this->endSection() ?>

<?= $this->section('breadcrumb') ?>
<li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
<li class="active">Konfigurasi SMTP</li>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<?php /* Flash message ditampilkan lewat SweetAlert2 (lihat section scripts) */ ?>

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
                                   data-swal-konfirmasi data-ikon="question"
                                   data-judul="Aktifkan konfigurasi SMTP?"
                                   data-nama="<?= esc($row['nama'], 'attr') ?>"
                                   data-teks="Konfigurasi {nama} akan menjadi SMTP aktif. Konfigurasi aktif lainnya otomatis dinonaktifkan, dan kampanye yang tidak memilih SMTP sendiri akan memakai konfigurasi ini."
                                   data-ya="Ya, aktifkan" data-loading="Mengaktifkan konfigurasi...">
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
                                   class="btn btn-danger btn-xs" title="Hapus"
                                   data-swal-konfirmasi data-ikon="warning" data-bahaya="1"
                                   data-judul="Hapus konfigurasi SMTP?"
                                   data-nama="<?= esc($row['nama'], 'attr') ?>"
                                   data-teks="Konfigurasi {nama} akan dihapus. Kampanye yang memakai SMTP ini akan kembali memakai SMTP aktif. Tindakan ini tidak dapat dibatalkan."
                                   data-ya="Ya, Hapus!" data-loading="Menghapus konfigurasi...">
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

/* DataTables dimuat otomatis bila layout belum menyediakannya */
const DT_CSS = 'https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap.min.css';
const DT_JS  = 'https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js';
const DT_BS  = 'https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap.min.js';

function muatScript(src) {
    return new Promise((ok, gagal) => {
        const s = document.createElement('script');
        s.src = src; s.onload = ok;
        s.onerror = () => gagal(new Error('Gagal memuat ' + src));
        document.head.appendChild(s);
    });
}
function muatCss(href) {
    if (document.querySelector('link[href="' + href + '"]')) return;
    const l = document.createElement('link');
    l.rel = 'stylesheet'; l.href = href;
    document.head.appendChild(l);
}

async function siapkanDataTables() {
    const $ = window.jQuery;
    if (!$) return false;
    if (!$.fn.DataTable) {
        try {
            muatCss(DT_CSS);
            await muatScript(DT_JS);
            await muatScript(DT_BS);
        } catch (e) {
            console.warn('[SMTP] DataTables gagal dimuat:', e);
            return false;
        }
    }
    return true;
}

async function mulai() {
    AppSwal.flash(<?= json_encode($flashSwal, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE) ?>);

    // DataTables
    if (await siapkanDataTables()) {
        window.jQuery('#smtpTable').DataTable({
            autoWidth: false,
            pageLength: 10,
            order: [],
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

    // Test kirim email: popup SweetAlert2 (modal Bootstrap dipakai hanya jika SweetAlert2 gagal dimuat)
    const modal = document.getElementById('modalTest');
    const form  = document.getElementById('formTest');

    form.addEventListener('submit', function () {
        AppSwal.loading('Mengirim email test...', 'Menghubungi server SMTP, bisa memakan waktu beberapa detik.');
    });

    function modalBootstrap(btn) {
        form.action = '<?= base_url('smtp') ?>/' + btn.dataset.id + '/test';
        document.getElementById('testNama').textContent = btn.dataset.nama;
        document.getElementById('testEmail').value = btn.dataset.email;
        if (window.jQuery && window.jQuery.fn.modal) {
            window.jQuery(modal).modal('show');
        } else {
            modal.style.display = 'block';
            modal.classList.add('in');
        }
    }

    document.querySelectorAll('.btn-test').forEach(function (btn) {
        btn.addEventListener('click', async function () {
            const S = await AppSwal.muat();
            if (!S) { modalBootstrap(btn); return; }

            const r = await S.fire({
                icon: 'info',
                title: 'Test Kirim Email',
                html: 'Konfigurasi: <b>' + AppSwal.esc(btn.dataset.nama) + '</b><br>' +
                      '<small style="color:#667085;">Email percobaan akan dikirim memakai SMTP ini.</small>',
                input: 'email',
                inputLabel: 'Email tujuan',
                inputValue: btn.dataset.email || '',
                inputPlaceholder: 'nama@contoh.com',
                validationMessage: 'Masukkan alamat email yang valid',
                inputValidator: function (v) { return !v ? 'Email tujuan wajib diisi' : undefined; },
                showCancelButton: true,
                confirmButtonText: '<i class="fa fa-send"></i> Kirim Test',
                cancelButtonText: 'Batal',
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#6e7881'
            });
            if (!r.isConfirmed) return;

            form.action = '<?= base_url('smtp') ?>/' + btn.dataset.id + '/test';
            document.getElementById('testEmail').value = r.value;
            AppSwal.loading('Mengirim email test...', 'Menghubungi server SMTP, bisa memakan waktu beberapa detik.');
            HTMLFormElement.prototype.submit.call(form);
        });
    });
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', mulai);
} else { mulai(); }

})();
</script>
<?= $this->endSection() ?>