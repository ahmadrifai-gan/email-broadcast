<?= $this->extend('layouts/main') ?>

<?= $this->section('title') ?>Data No HP<?= $this->endSection() ?>
<?= $this->section('page_title') ?>Data No HP Perusahaan<?= $this->endSection() ?>
<?= $this->section('page_subtitle') ?>Satu perusahaan bisa punya banyak nomor HP / telepon<?= $this->endSection() ?>

<?= $this->section('breadcrumb') ?>
<li><a href="<?= base_url() ?>"><i class="fa fa-dashboard"></i> Home</a></li>
<li><a href="<?= base_url('extractor') ?>">Data Email</a></li>
<li class="active">No HP</li>
<?= $this->endSection() ?>

<?= $this->section('styles') ?>
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
<style>
    .tbl td { vertical-align:middle !important; }
    .url-cell { max-width:280px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; display:inline-block; vertical-align:middle; }
    .btn-aksi { margin-right:2px; }
    .box-tools .form-control { display:inline-block; width:auto; max-width:300px; }
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<ul class="nav nav-pills" style="margin-bottom:12px;">
    <li class=""><a href="<?= base_url('data-kontak/email') ?>"><i class="fa fa-envelope-o"></i> Email</a></li>
    <li class="active"><a href="<?= base_url('data-kontak/telepon') ?>"><i class="fa fa-phone"></i> No HP</a></li>
    <li class=""><a href="<?= base_url('data-kontak/website') ?>"><i class="fa fa-globe"></i> Website</a></li>
</ul>

<div class="box box-primary">
    <div class="box-header with-border">
        <h3 class="box-title"><i class="fa fa-phone"></i> Daftar No HP <span class="badge bg-green" id="cnt">0</span></h3>
        <div class="box-tools">
            <select id="fKontak" class="form-control input-sm">
                <option value="0">Semua perusahaan</option>
                <?php foreach ($opsi as $k): ?>
                    <option value="<?= (int) $k['id'] ?>" <?= (int) $kontakId === (int) $k['id'] ? 'selected' : '' ?>>
                        <?= esc(($k['nama_perusahaan'] ?: $k['nama']) ?: '(tanpa nama)') ?> · #<?= (int) $k['id'] ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <button type="button" class="btn btn-primary btn-sm" id="btnTambah"><i class="fa fa-plus"></i> Tambah No HP</button>
        </div>
    </div>
    <div class="box-body">
        <div class="table-responsive">
            <table id="tbl" class="table table-bordered table-striped table-hover tbl" style="width:100%">
                <thead><tr>
                    <th>Perusahaan</th><th>No HP / Telepon</th><th>Label</th>
                    <th class="text-center" width="130">Terakhir dicek</th>
                    <th class="text-center" width="90">Aksi</th>
                </tr></thead>
            </table>
        </div>
    </div>
</div>

<!-- ===== Modal tambah / ubah ===== -->
<div class="modal fade" id="mdl" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Tutup"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="mdlTitle">Tambah No HP</h4>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label for="mKontak">Perusahaan</label>
                    <select id="mKontak" class="form-control">
                        <?php foreach ($opsi as $k): ?>
                            <option value="<?= (int) $k['id'] ?>"><?= esc(($k['nama_perusahaan'] ?: $k['nama']) ?: '(tanpa nama)') ?> · #<?= (int) $k['id'] ?></option>
                        <?php endforeach; ?>
                    </select>
                    <p class="help-block" id="mKontakHint" style="display:none;">Perusahaan tidak bisa diganti saat mengubah data.</p>
                </div>
                <div class="form-group">
                    <label for="fNomor">No HP / Telepon</label>
                    <input type="text" id="fNomor" class="form-control" placeholder="0812-3456-7890 atau (021) 6914511" maxlength="30" autocomplete="off">
                </div>
                <div class="form-group">
                    <label for="fLabel">Label <small class="text-muted">(opsional, mis. WhatsApp, Kantor)</small></label>
                    <input type="text" id="fLabel" class="form-control" maxlength="100" autocomplete="off">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-primary" id="btnSimpan"><i class="fa fa-save"></i> Simpan</button>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
const API_BASE = '<?= base_url('data-kontak/telepon') ?>';
const CSRF     = { header: '<?= csrf_header() ?>', hash: '<?= csrf_hash() ?>' };
const NAMA     = 'No HP';

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
        { data: 'nomor', render: (d, t, r) => t === 'display' ? `<i class="fa fa-phone text-muted"></i> ${esc(d)}` : d + ' ' + (r.nomor_normal || '') },
        { data: 'label', render: labelCell },
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

/* ---------- form tambah / ubah ---------- */
let editId = 0;   // 0 = tambah

function openForm(row) {
    editId = row ? row.id : 0;
    $('#mdlTitle').text((row ? 'Ubah ' : 'Tambah ') + NAMA);
    $('#mdl input[type=text], #mdl input[type=email]').val('');
    $('#fPrimary').prop('checked', false);

    if (row) {
        $('#mKontak').val(String(row.kontak_id)).prop('disabled', true);
        $('#mKontakHint').show();
        $('#fNomor').val(row.nomor || '');
        $('#fLabel').val(row.label || '');
    } else {
        $('#mKontak').prop('disabled', false);
        $('#mKontakHint').hide();
        const f = Number($('#fKontak').val() || 0);
        if (f > 0) $('#mKontak').val(String(f));
    }
    $('#mdl').modal('show');
    setTimeout(() => $('#fNomor').trigger('focus'), 300);
}

$('#btnTambah').on('click', () => openForm(null));

$('#btnSimpan').on('click', async function () {
    const body = {
        nomor: $('#fNomor').val(),
        label: $('#fLabel').val()
    };
    let path = '/simpan';
    if (editId) { path = '/perbarui/' + editId; }
    else        { body.kontak_id = Number($('#mKontak').val() || 0); }

    const $b = $(this).prop('disabled', true);
    try {
        await api(path, body);
        $('#mdl').modal('hide');
        Toast.fire({ icon: 'success', title: editId ? 'Data diperbarui' : 'Data ditambahkan' });
        await load();
    } catch (e) {
        Swal.fire('Tidak bisa menyimpan', e.message, 'warning');
    } finally {
        $b.prop('disabled', false);
    }
});
$('#mdl').on('keydown', 'input', e => { if (e.key === 'Enter') { e.preventDefault(); $('#btnSimpan').trigger('click'); } });

/* ---------- ubah & hapus ---------- */
$('#tbl').on('click', '[data-act]', async function () {
    const row = dt.row($(this).closest('tr')).data();
    if (!row) return;
    if ($(this).data('act') === 'edit') return openForm(row);

    const ok = await Swal.fire({
        title: 'Hapus data ini?',
        html: `<b>${esc(row.nomor)}</b><br><small class="text-muted">${esc(row.nama_perusahaan || row.nama_kontak || '')}</small>`,
        icon: 'warning', showCancelButton: true, confirmButtonColor: '#dd4b39',
        confirmButtonText: 'Ya, hapus', cancelButtonText: 'Batal'
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

load();
</script>
<?= $this->endSection() ?>