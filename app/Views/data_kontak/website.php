<?= $this->extend('layouts/main') ?>

<?= $this->section('title') ?>Data Website<?= $this->endSection() ?>
<?= $this->section('page_title') ?>Data Website Perusahaan<?= $this->endSection() ?>
<?= $this->section('page_subtitle') ?>Satu perusahaan bisa punya banyak website; satu di antaranya menjadi website utama<?= $this->endSection() ?>

<?= $this->section('breadcrumb') ?>
<li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
<li><a href="<?= base_url('extractor') ?>">Data Email</a></li>
<li class="active">Website</li>
<?= $this->endSection() ?>

<?= $this->section('styles') ?>
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
<style>
    .tbl td { vertical-align: middle !important; }
    .url-cell { max-width: 320px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; display: inline-block; vertical-align: middle; }
    .btn-aksi { margin-right: 2px; }
    .box-tools .form-control { display: inline-block; width: auto; max-width: 300px; }
    .modal-header.bg-blue { background-color: #3c8dbc !important; color: #fff; border-radius: 4px 4px 0 0; }
    .modal-header.bg-yellow { background-color: #f39c12 !important; color: #fff; border-radius: 4px 4px 0 0; }
    .modal-header .close { color: #fff; opacity: .8; }
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<ul class="nav nav-pills" style="margin-bottom:12px;">
    <li class=""><a href="<?= base_url('data-kontak/email') ?>"><i class="fa fa-envelope-o"></i> Email</a></li>
    <li class=""><a href="<?= base_url('data-kontak/telepon') ?>"><i class="fa fa-phone"></i> No HP</a></li>
    <li class="active"><a href="<?= base_url('data-kontak/website') ?>"><i class="fa fa-globe"></i> Website</a></li>
</ul>

<!-- LANGSUNG KE BOX, TANPA CONTENT-HEADER MANUAL -->
<div class="box box-primary">
    <div class="box-header with-border">
        <h3 class="box-title"><i class="fa fa-globe"></i> Data Website <span class="badge bg-yellow" id="cnt">0</span></h3>
        <div class="box-tools">
            <select id="fKontak" class="form-control input-sm">
                <option value="0">Semua perusahaan</option>
                <?php foreach ($opsi as $k): ?>
                    <option value="<?= (int) $k['id'] ?>" <?= (int) $kontakId === (int) $k['id'] ? 'selected' : '' ?>>
                        <?= esc(($k['nama_perusahaan'] ?: $k['nama']) ?: '(tanpa nama)') ?> · #<?= (int) $k['id'] ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <button type="button" class="btn btn-primary btn-sm" id="btnTambah">
                <i class="fa fa-plus"></i> Tambah Website
            </button>
        </div>
    </div>

    <div class="box-body">
        <div class="table-responsive">
            <table id="tbl" class="table table-bordered table-striped table-hover tbl" style="width:100%">
                <thead>
                    <tr>
                        <th>Perusahaan</th>
                        <th>Website</th>
                        <th>Label</th>
                        <th class="text-center" width="80">Utama</th>
                        <th class="text-center" width="130">Terakhir dicek</th>
                        <th class="text-center" width="90">Aksi</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
</div>

<!-- ================= MODAL TAMBAH ================= -->
<div class="modal fade" id="modalTambah" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog">
        <form id="formTambah">
            <?= csrf_field() ?>
            <div class="modal-content">
                <div class="modal-header bg-blue">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title">Tambah Website</h4>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label>Perusahaan</label>
                        <select id="mKontak" class="form-control">
                            <?php foreach ($opsi as $k): ?>
                                <option value="<?= (int) $k['id'] ?>"><?= esc(($k['nama_perusahaan'] ?: $k['nama']) ?: '(tanpa nama)') ?> · #<?= (int) $k['id'] ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Website <span class="text-danger">*</span></label>
                        <input type="text" id="fUrl" class="form-control" placeholder="https://perusahaan.co.id" maxlength="500" autocomplete="off" required>
                    </div>
                    <div class="form-group">
                        <label>Label <small class="text-muted">(opsional, mis. Toko online, Katalog)</small></label>
                        <input type="text" id="fLabel" class="form-control" maxlength="100" autocomplete="off">
                    </div>
                    <div class="checkbox">
                        <label><input type="checkbox" id="fPrimary"> Jadikan website utama perusahaan</label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-primary" id="btnSimpanTambah"><i class="fa fa-save"></i> Simpan</button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- ================= MODAL EDIT ================= -->
<div class="modal fade" id="editModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog">
        <form id="editForm">
            <?= csrf_field() ?>
            <div class="modal-content">
                <div class="modal-header bg-yellow">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title">Edit Website</h4>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label>Perusahaan</label>
                        <select id="edit_mKontak" class="form-control" disabled>
                            <?php foreach ($opsi as $k): ?>
                                <option value="<?= (int) $k['id'] ?>"><?= esc(($k['nama_perusahaan'] ?: $k['nama']) ?: '(tanpa nama)') ?> · #<?= (int) $k['id'] ?></option>
                            <?php endforeach; ?>
                        </select>
                        <p class="help-block">Perusahaan tidak bisa diganti saat mengubah data.</p>
                    </div>
                    <div class="form-group">
                        <label>Website <span class="text-danger">*</span></label>
                        <input type="text" id="edit_url" class="form-control" placeholder="https://perusahaan.co.id" maxlength="500" autocomplete="off" required>
                    </div>
                    <div class="form-group">
                        <label>Label <small class="text-muted">(opsional, mis. Toko online, Katalog)</small></label>
                        <input type="text" id="edit_label" class="form-control" maxlength="100" autocomplete="off">
                    </div>
                    <div class="checkbox">
                        <label><input type="checkbox" id="edit_primary"> Jadikan website utama perusahaan</label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-warning" id="btnSimpanEdit"><i class="fa fa-save"></i> Update</button>
                </div>
            </div>
        </form>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
const API_BASE = '<?= base_url('data-kontak/website') ?>';
const CSRF     = { header: '<?= csrf_header() ?>', hash: '<?= csrf_hash() ?>' };
const NAMA     = 'Website';

const esc = (s) => String(s ?? '').replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
const Toast = Swal.mixin({ toast:true, position:'top-end', showConfirmButton:false, timer:3000, timerProgressBar:true });
const LANG = { search:'Cari:', lengthMenu:'Tampilkan _MENU_', info:'_START_–_END_ dari _TOTAL_', infoEmpty:'0 data', infoFiltered:'(dari _MAX_)', zeroRecords:'Tidak ada data', emptyTable:'Belum ada data', paginate:{ previous:'‹', next:'›' } };

async function api(path, body) {
    const post = body !== undefined;
    const headers = { 'X-Requested-With': 'XMLHttpRequest' };
    if (post) {
        headers['Content-Type'] = 'application/json';
        if (CSRF.header && CSRF.hash) headers[CSRF.header] = CSRF.hash;
    }
    const res = await fetch(API_BASE + path, { method: post ? 'POST' : 'GET', headers, body: post ? JSON.stringify(body) : undefined });
    if (!res.ok) {
        let m = 'HTTP ' + res.status;
        try { m = (await res.json()).error || m; } catch { /* abaikan */ }
        throw new Error(m);
    }
    return res.json();
}

/* ---------- sel tabel ---------- */
const kontakCell = (d, t, r) => {
    const main = r.nama_perusahaan || r.nama_kontak || '(tanpa nama)';
    if (t !== 'display') return main + ' ' + (r.nama_kontak || '');
    const sub = r.nama_perusahaan && r.nama_kontak ? `<br><small class="text-muted">${esc(r.nama_kontak)}</small>` : '';
    return `<strong>${esc(main)}</strong>${sub}`;
};
const urlCell = (d, t) => t === 'display'
    ? `<a class="url-cell" href="${esc(d)}" target="_blank" rel="noopener" title="${esc(d)}"><i class="fa fa-external-link"></i> ${esc(d)}</a>`
    : d;
const labelCell = (d, t) => t === 'display' ? (d ? esc(d) : '<span class="text-muted">—</span>') : (d || '');
const checkCell = (d, t) => {
    if (t !== 'display') return d || '';
    if (!d) return '<span class="text-muted">Manual / belum dicek</span>';
    const days = Math.floor((Date.now() - new Date(String(d).replace(' ', 'T')).getTime()) / 864e5);
    return `<span class="label label-${days < 30 ? 'success' : 'default'}" title="${esc(d)}">${days <= 0 ? 'Hari ini' : days + ' hari lalu'}</span>`;
};
const yesCell = (d, t) => t === 'display'
    ? (Number(d) === 1 ? '<span class="label label-success">Ya</span>' : '<span class="text-muted">—</span>')
    : Number(d || 0);
const aksiCell = () => `<button type="button" class="btn btn-xs btn-warning btn-aksi" data-act="edit" title="Ubah"><i class="fa fa-pencil"></i></button>`
                     + `<button type="button" class="btn btn-xs btn-danger btn-aksi" data-act="hapus" title="Hapus"><i class="fa fa-trash"></i></button>`;

const dt = $('#tbl').DataTable({
    data: [], deferRender: true, autoWidth: false, order: [], pageLength: 10, language: LANG,
    columns: [
        { data: 'nama_perusahaan', render: kontakCell },
        { data: 'url', render: urlCell },
        { data: 'label', render: labelCell },
        { data: 'is_primary', className: 'text-center', render: yesCell },
        { data: 'last_check', className: 'text-center', render: checkCell },
        { data: null, orderable: false, className: 'text-center', render: aksiCell }
    ]
});

/* ---------- muat data ---------- */
async function load() {
    try {
        const id   = Number($('#fKontak').val() || 0);
        const rows = await api('/data' + (id > 0 ? '?kontak_id=' + id : ''));
        dt.clear().rows.add(rows).draw(false);
        $('#cnt').text(rows.length.toLocaleString('id-ID'));
    } catch (e) {
        Swal.fire('Gagal memuat data', e.message, 'error');
    }
}
$('#fKontak').on('change', load);

/* ---------- TAMBAH ---------- */
$('#btnTambah').on('click', function () {
    $('#formTambah')[0].reset();
    $('#fPrimary').prop('checked', false);
    const f = Number($('#fKontak').val() || 0);
    if (f > 0) $('#mKontak').val(String(f));
    $('#modalTambah').modal('show');
    setTimeout(() => $('#fUrl').trigger('focus'), 300);
});

$('#btnSimpanTambah').on('click', async function () {
    const body = {
        kontak_id: Number($('#mKontak').val() || 0),
        url: $('#fUrl').val(),
        label: $('#fLabel').val(),
        is_primary: $('#fPrimary').is(':checked') ? 1 : 0
    };
    const $b = $(this).prop('disabled', true);
    try {
        await api('/simpan', body);
        $('#modalTambah').modal('hide');
        Toast.fire({ icon: 'success', title: 'Data ditambahkan' });
        await load();
    } catch (e) {
        Swal.fire('Tidak bisa menyimpan', e.message, 'warning');
    } finally {
        $b.prop('disabled', false);
    }
});

/* ---------- EDIT ---------- */
$(document).on('click', '.btn-aksi[data-act="edit"]', function () {
    const row = dt.row($(this).closest('tr')).data();
    if (!row) return;
    $('#edit_mKontak').val(String(row.kontak_id));
    $('#edit_url').val(row.url || '');
    $('#edit_label').val(row.label || '');
    $('#edit_primary').prop('checked', Number(row.is_primary) === 1);
    $('#editForm').data('id', row.id);
    $('#editModal').modal('show');
    setTimeout(() => $('#edit_url').trigger('focus'), 300);
});

$('#btnSimpanEdit').on('click', async function () {
    const id = $('#editForm').data('id');
    const body = {
        url: $('#edit_url').val(),
        label: $('#edit_label').val(),
        is_primary: $('#edit_primary').is(':checked') ? 1 : 0
    };
    const $b = $(this).prop('disabled', true);
    try {
        await api('/perbarui/' + id, body);
        $('#editModal').modal('hide');
        Toast.fire({ icon: 'success', title: 'Data diperbarui' });
        await load();
    } catch (e) {
        Swal.fire('Tidak bisa menyimpan', e.message, 'warning');
    } finally {
        $b.prop('disabled', false);
    }
});

/* ---------- Enter = simpan ---------- */
$('#modalTambah, #editModal').on('keydown', 'input', function (e) {
    if (e.key === 'Enter') {
        e.preventDefault();
        const isEdit = $(this).closest('#editModal').length > 0;
        $(isEdit ? '#btnSimpanEdit' : '#btnSimpanTambah').trigger('click');
    }
});

/* ---------- HAPUS ---------- */
$('#tbl').on('click', '.btn-aksi[data-act="hapus"]', async function () {
    const row = dt.row($(this).closest('tr')).data();
    if (!row) return;

    const ok = await Swal.fire({
        title: 'Yakin ingin menghapus?',
        html: `<b>${esc(row.url)}</b><br><small class="text-muted">${esc(row.nama_perusahaan || row.nama_kontak || '')}</small><br><small class="text-muted">Data yang dihapus tidak dapat dikembalikan!</small>`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Ya, Hapus!',
        cancelButtonText: 'Batal'
    });
    if (!ok.isConfirmed) return;

    try {
        await api('/hapus/' + row.id, {});
        Toast.fire({ icon: 'success', title: 'Data dihapus' });
        await load();
    } catch (e) {
        Swal.fire('Gagal menghapus', e.message, 'error');
    }
});

/* ---------- flash data (opsional jika ada redirect server-side) ---------- */
<?php if (session()->getFlashdata('success')) : ?>
    Swal.fire({ icon: 'success', title: 'Berhasil!', text: <?= json_encode(session()->getFlashdata('success')) ?>, timer: 2000, showConfirmButton: false });
<?php endif; ?>
<?php if (session()->getFlashdata('error')) : ?>
    Swal.fire({ icon: 'error', title: 'Gagal!', text: <?= json_encode(session()->getFlashdata('error')) ?> });
<?php endif; ?>

load();
</script>
<?= $this->endSection() ?>