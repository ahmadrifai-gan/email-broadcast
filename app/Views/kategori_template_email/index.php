<?= $this->extend('layouts/main') ?>

<?= $this->section('title') ?>Data Kategori Template Email<?= $this->endSection() ?>
<?= $this->section('page_title') ?>Kategori Template Email<?= $this->endSection() ?>
<?= $this->section('page_subtitle') ?>Manajemen Data Kategori Template Email<?= $this->endSection() ?>

<?= $this->section('breadcrumb') ?>
<li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
<li class="active">Kategori Template Email</li>
<?= $this->endSection() ?>

<?= $this->section('styles') ?>
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap.min.css">
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<div class="box box-primary">
    <div class="box-header with-border">
        <h3 class="box-title"><i class="fa fa-envelope-o"></i> Data Kategori Template Email</h3>
        <div class="pull-right">
            <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#modalTambah">
                <i class="fa fa-plus"></i> Tambah Kategori
            </button>
        </div>
    </div>

    <div class="box-body">

        <div class="table-responsive">
            <table id="kategoriTable" class="table table-bordered table-striped table-hover">
                <thead>
                    <tr>
                        <th width="50">No</th>
                        <th>Nama Kategori</th>
                        <th>Deskripsi</th>
                        <th width="90">Status</th>
                        <th width="140">Dibuat Pada</th>
                        <th width="140">Diperbarui Pada</th>
                        <th width="120">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $no = 1; foreach ($kategori as $k) : ?>
                        <tr>
                            <td><?= $no++ ?></td>
                            <td>
                                <span class="label label-primary" style="font-size:13px;">
                                    <?= esc($k['nama_kategori']) ?>
                                </span>
                            </td>
                            <td><?= esc($k['deskripsi']) ?></td>
                            <td class="text-center">
                                <?php if ($k['status'] === 'aktif') : ?>
                                    <span class="label label-primary">Aktif</span>
                                <?php else : ?>
                                    <span class="label label-default">Nonaktif</span>
                                <?php endif; ?>
                            </td>
                            <td><?= esc($k['dibuat_pada']) ?></td>
                            <td><?= esc($k['diperbarui_pada']) ?></td>
                            <td class="text-center">
                                <button type="button"
                                        class="btn btn-warning btn-xs btn-edit"
                                        data-toggle="modal"
                                        data-target="#editModal"
                                        data-id="<?= $k['id'] ?>"
                                        data-nama="<?= esc($k['nama_kategori']) ?>"
                                        data-deskripsi="<?= esc($k['deskripsi']) ?>"
                                        data-status="<?= $k['status'] ?>">
                                    <i class="fa fa-edit"></i>
                                </button>

                                <form action="<?= base_url('kategori-template-email/delete/' . $k['id']) ?>"
                                      method="post"
                                      class="form-delete"
                                      style="display:inline">
                                    <?= csrf_field() ?>
                                    <button type="submit" class="btn btn-danger btn-xs">
                                        <i class="fa fa-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- ================= MODAL TAMBAH ================= -->
<div class="modal fade" id="modalTambah" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog">
        <form action="<?= base_url('kategori-template-email/store') ?>" method="post" id="formTambah">
            <?= csrf_field() ?>
            <div class="modal-content">
                <div class="modal-header bg-blue">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title">Tambah Kategori Template Email</h4>
                </div>

                <div class="modal-body">
                    <div class="form-group">
                        <label>Nama Kategori <span class="text-danger">*</span></label>
                        <input type="text" name="nama_kategori" class="form-control"
                               placeholder="Masukkan nama kategori" maxlength="100" required>
                    </div>
                    <div class="form-group">
                        <label>Deskripsi</label>
                        <textarea name="deskripsi" class="form-control" rows="3"
                                  placeholder="Masukkan deskripsi kategori"></textarea>
                    </div>
                    <div class="form-group">
                        <label>Status</label>
                        <select name="status" class="form-control">
                            <option value="aktif">Aktif</option>
                            <option value="nonaktif">Nonaktif</option>
                        </select>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> Simpan</button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- ================= MODAL EDIT ================= -->
<div class="modal fade" id="editModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog">
        <form id="editForm" action="" method="post">
            <?= csrf_field() ?>
            <div class="modal-content">
                <div class="modal-header bg-yellow">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title">Edit Kategori Template Email</h4>
                </div>

                <div class="modal-body">
                    <div class="form-group">
                        <label>Nama Kategori <span class="text-danger">*</span></label>
                        <input type="text" id="edit_nama" name="nama_kategori" class="form-control"
                               placeholder="Masukkan nama kategori" maxlength="100" required>
                    </div>
                    <div class="form-group">
                        <label>Deskripsi</label>
                        <textarea id="edit_deskripsi" name="deskripsi" class="form-control" rows="3"
                                  placeholder="Masukkan deskripsi kategori"></textarea>
                    </div>
                    <div class="form-group">
                        <label>Status</label>
                        <select id="edit_status" name="status" class="form-control">
                            <option value="aktif">Aktif</option>
                            <option value="nonaktif">Nonaktif</option>
                        </select>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-warning"><i class="fa fa-save"></i> Update</button>
                </div>
            </div>
        </form>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap.min.js"></script>
<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
$(document).ready(function () {

    /* ===============================
     * DataTables
     * =============================== */
    $('#kategoriTable').DataTable({
        autoWidth: false,
        pageLength: 10,
        lengthMenu: [[5, 10, 25, 50, -1], [5, 10, 25, 50, 'Semua']],
        columnDefs: [
            { targets: 0,  orderable: false, searchable: false },  // No
            { targets: -1, orderable: false, searchable: false }   // Aksi
        ],
        language: {
            url: '//cdn.datatables.net/plug-ins/1.13.4/i18n/id.json'
        }
    });

    /* ===============================
     * Populate modal edit
     * =============================== */
    $(document).on('click', '.btn-edit', function () {
        let id        = $(this).data('id');
        let nama      = $(this).data('nama');
        let deskripsi = $(this).data('deskripsi');
        let status    = $(this).data('status');

        $('#edit_nama').val(nama);
        $('#edit_deskripsi').val(deskripsi);
        $('#edit_status').val(status);

        $('#editForm').attr('action', '<?= base_url('kategori-template-email/update') ?>/' + id);
    });

    /* ===============================
     * Flash message → SweetAlert2
     * =============================== */
    <?php if (session()->getFlashdata('success')) : ?>
        Swal.fire({
            icon: 'success',
            title: 'Berhasil!',
            text: <?= json_encode(session()->getFlashdata('success')) ?>,
            timer: 2000,
            showConfirmButton: false
        });
    <?php endif; ?>

    <?php if (session()->getFlashdata('error')) : ?>
        Swal.fire({
            icon: 'error',
            title: 'Gagal!',
            text: <?= json_encode(session()->getFlashdata('error')) ?>
        });
    <?php endif; ?>

    <?php if (session()->getFlashdata('errors')) : ?>
        Swal.fire({
            icon: 'warning',
            title: 'Validasi Gagal',
            html: `<?= implode('<br>', array_map('esc', session()->getFlashdata('errors'))) ?>`
        });
    <?php endif; ?>

    /* ===============================
     * Konfirmasi Hapus (SweetAlert2)
     * =============================== */
    $(document).on('submit', '.form-delete', function (e) {
        e.preventDefault();
        let form = this;

        Swal.fire({
            title: 'Yakin ingin menghapus?',
            text: 'Data yang dihapus tidak dapat dikembalikan!',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Ya, Hapus!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                form.submit();
            }
        });
    });

});
</script>
<?= $this->endSection() ?>