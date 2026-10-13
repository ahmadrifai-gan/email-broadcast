<?= $this->extend('layouts/main') ?>

<?= $this->section('title') ?>Data Kategori Template Email<?= $this->endSection() ?>
<?= $this->section('page_title') ?>Kategori Template Email<?= $this->endSection() ?>
<?= $this->section('page_subtitle') ?>Manajemen Data Kategori Template Email<?= $this->endSection() ?>

<?= $this->section('breadcrumb') ?>
<li>
    <a href="#">
        <i class="fa fa-dashboard"></i> Home
    </a>
</li>
<li class="active">Kategori Template Email</li>
<?= $this->endSection() ?>

<!-- ============================================== -->
<!-- CSS DataTables (Bootstrap 3 - cocok AdminLTE) -->
<!-- ============================================== -->
<?= $this->section('styles') ?>
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap.min.css">
<?= $this->endSection() ?>

<!-- ============================================== -->
<!-- CONTENT -->
<!-- ============================================== -->
<?= $this->section('content') ?>

<div class="box box-success">

    <div class="box-header with-border">

        <h3 class="box-title">
            <i class="fa fa-envelope-o"></i>
            Data Kategori Template Email
        </h3>

        <div class="pull-right">
            <button type="button"
                    class="btn btn-success btn-sm"
                    data-toggle="modal"
                    data-target="#modalTambah">
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
                <tr>
                    <td>1</td>
                    <td>
                        <span class="label label-primary" style="font-size:13px;">
                            Contoh Kategori
                        </span>
                    </td>
                    <td>Deskripsi contoh kategori template email.</td>
                    <td class="text-center">
                        <span class="label label-success">Aktif</span>
                    </td>
                    <td>2025-01-01 10:00:00</td>
                    <td>2025-01-01 10:00:00</td>
                    <td class="text-center">
                        <button type="button"
                                class="btn btn-warning btn-xs btn-edit"
                                data-toggle="modal"
                                data-target="#editModal">
                            <i class="fa fa-edit"></i>
                        </button>
                        <button type="button" class="btn btn-danger btn-xs">
                            <i class="fa fa-trash"></i>
                        </button>
                    </td>
                </tr>
                <tr>
                    <td>2</td>
                    <td>
                        <span class="label label-primary" style="font-size:13px;">
                            Promosi
                        </span>
                    </td>
                    <td>Kategori untuk email promosi produk.</td>
                    <td class="text-center">
                        <span class="label label-success">Aktif</span>
                    </td>
                    <td>2025-01-02 09:15:00</td>
                    <td>2025-01-02 09:15:00</td>
                    <td class="text-center">
                        <button type="button"
                                class="btn btn-warning btn-xs btn-edit"
                                data-toggle="modal"
                                data-target="#editModal">
                            <i class="fa fa-edit"></i>
                        </button>
                        <button type="button" class="btn btn-danger btn-xs">
                            <i class="fa fa-trash"></i>
                        </button>
                    </td>
                </tr>
                <tr>
                    <td>3</td>
                    <td>
                        <span class="label label-primary" style="font-size:13px;">
                            Notifikasi
                        </span>
                    </td>
                    <td>Kategori untuk email notifikasi sistem.</td>
                    <td class="text-center">
                        <span class="label label-default">Nonaktif</span>
                    </td>
                    <td>2025-01-03 14:20:00</td>
                    <td>2025-01-03 14:20:00</td>
                    <td class="text-center">
                        <button type="button"
                                class="btn btn-warning btn-xs btn-edit"
                                data-toggle="modal"
                                data-target="#editModal">
                            <i class="fa fa-edit"></i>
                        </button>
                        <button type="button" class="btn btn-danger btn-xs">
                            <i class="fa fa-trash"></i>
                        </button>
                    </td>
                </tr>
                </tbody>

            </table>
        </div>

    </div>
</div>

<!-- MODAL TAMBAH -->
<div class="modal fade" id="modalTambah" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">

            <div class="modal-header bg-green">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">Tambah Kategori Template Email</h4>
            </div>

            <div class="modal-body">

                <div class="form-group">
                    <label>Nama Kategori</label>
                    <input type="text"
                           class="form-control"
                           placeholder="Masukkan nama kategori"
                           maxlength="100">
                </div>

                <div class="form-group">
                    <label>Deskripsi</label>
                    <textarea class="form-control"
                              rows="3"
                              placeholder="Masukkan deskripsi kategori"></textarea>
                </div>

                <div class="form-group">
                    <label>Status</label>
                    <select class="form-control">
                        <option value="aktif">Aktif</option>
                        <option value="nonaktif">Nonaktif</option>
                    </select>
                </div>

            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-success">Simpan</button>
            </div>

        </div>
    </div>
</div>

<!-- MODAL EDIT -->
<div class="modal fade" id="editModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">

            <div class="modal-header bg-yellow">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">Edit Kategori Template Email</h4>
            </div>

            <div class="modal-body">

                <div class="form-group">
                    <label>Nama Kategori</label>
                    <input type="text"
                           class="form-control"
                           placeholder="Masukkan nama kategori"
                           maxlength="100">
                </div>

                <div class="form-group">
                    <label>Deskripsi</label>
                    <textarea class="form-control"
                              rows="3"
                              placeholder="Masukkan deskripsi kategori"></textarea>
                </div>

                <div class="form-group">
                    <label>Status</label>
                    <select class="form-control">
                        <option value="aktif">Aktif</option>
                        <option value="nonaktif">Nonaktif</option>
                    </select>
                </div>

            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-warning">Update</button>
            </div>

        </div>
    </div>
</div>

<?= $this->endSection() ?>

<!-- ============================================== -->
<!-- JS DataTables + Inisialisasi -->
<!-- ============================================== -->
<?= $this->section('scripts') ?>
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap.min.js"></script>

<script>
$(document).ready(function () {

    // Inisialisasi DataTables
    $('#kategoriTable').DataTable({
        autoWidth: false,
        pageLength: 10,
        lengthMenu: [[5, 10, 25, 50, -1], [5, 10, 25, 50, 'Semua']],
        columnDefs: [
            {
                targets: 0,        // kolom "No"
                orderable: false,
                searchable: false
            },
            {
                targets: -1,       // kolom "Aksi" (paling kanan)
                orderable: false,
                searchable: false
            }
        ],
        language: {
            url: '//cdn.datatables.net/plug-ins/1.13.4/i18n/id.json'
        }
    });

    // Event delegation untuk tombol Edit (tetap jalan walau di-render ulang DataTables)
    $(document).on('click', '.btn-edit', function () {
        // let id   = $(this).data('id');
        // let nama = $(this).data('nama');
        // $('#edit_nama').val(nama);
        // $('#editForm').attr('action', '...');
        console.log('Tombol edit diklik');
    });

});
</script>
<?= $this->endSection() ?>