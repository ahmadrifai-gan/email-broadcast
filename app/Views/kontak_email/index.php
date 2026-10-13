<?= $this->extend('layouts/main') ?>

<?= $this->section('title') ?>Data Kontak<?= $this->endSection() ?>
<?= $this->section('page_title') ?>Data Kontak<?= $this->endSection() ?>
<?= $this->section('page_subtitle') ?>Manajemen Data Kontak<?= $this->endSection() ?>

<?= $this->section('breadcrumb') ?>
<li>
    <a href="#">
        <i class="fa fa-dashboard"></i> Home
    </a>
</li>
<li class="active">Data Kontak</li>
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

<div class="box box-primary">

    <div class="box-header with-border">

        <h3 class="box-title">
            <i class="fa fa-address-book"></i>
            Data Kontak
        </h3>

        <div class="pull-right">
            <button type="button"
                    class="btn btn-primary btn-sm"
                    data-toggle="modal"
                    data-target="#modalTambah">
                <i class="fa fa-plus"></i> Tambah Kontak
            </button>
        </div>

    </div>

    <div class="box-body">

        <div class="table-responsive">
            <table id="kontakTable" class="table table-bordered table-striped table-hover">

                <thead>
                <tr>
                    <th width="50">No</th>
                    <th width="60">ID</th>
                    <th>Nama Perusahaan</th>
                    <th>Email</th>
                    <th>Domisili</th>
                    <th width="120">Aksi</th>
                </tr>
                </thead>

                <tbody>
                <tr>
                    <td>1</td>
                    <td>1</td>
                    <td>
                        <span class="label label-primary" style="font-size:13px;">
                            PT Contoh Sejahtera
                        </span>
                    </td>
                    <td>info@contoh.co.id</td>
                    <td>Jakarta</td>
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
                    <td>2</td>
                    <td>
                        <span class="label label-primary" style="font-size:13px;">
                            CV Maju Bersama
                        </span>
                    </td>
                    <td>contact@majubersama.com</td>
                    <td>Bandung</td>
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
                    <td>3</td>
                    <td>
                        <span class="label label-primary" style="font-size:13px;">
                            PT Digital Nusantara
                        </span>
                    </td>
                    <td>halo@digitalnusantara.id</td>
                    <td>Surabaya</td>
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

<!-- ============================================== -->
<!-- MODAL TAMBAH -->
<!-- ============================================== -->
<div class="modal fade" id="modalTambah" tabindex="-1" role="dialog" aria-labelledby="modalTambahLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">

            <div class="modal-header bg-primary">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title" id="modalTambahLabel">
                    <i class="fa fa-plus-circle"></i> Tambah Kontak
                </h4>
            </div>

            <div class="modal-body">

                <div class="form-group">
                    <label for="tambah_nama">
                        <i class="fa fa-building-o"></i> Nama Perusahaan
                    </label>
                    <input type="text"
                           id="tambah_nama"
                           class="form-control"
                           placeholder="Masukkan nama perusahaan"
                           autocomplete="off">
                </div>

                <div class="form-group">
                    <label for="tambah_email">
                        <i class="fa fa-envelope-o"></i> Email
                    </label>
                    <input type="email"
                           id="tambah_email"
                           class="form-control"
                           placeholder="Masukkan email"
                           autocomplete="off">
                </div>

                <div class="form-group">
                    <label for="tambah_domisili">
                        <i class="fa fa-map-marker"></i> Domisili
                    </label>
                    <input type="text"
                           id="tambah_domisili"
                           class="form-control"
                           placeholder="Masukkan domisili"
                           autocomplete="off">
                </div>

            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">
                    <i class="fa fa-times"></i> Batal
                </button>
                <button type="button" class="btn btn-primary">
                    <i class="fa fa-save"></i> Simpan
                </button>
            </div>

        </div>
    </div>
</div>

<!-- ============================================== -->
<!-- MODAL EDIT -->
<!-- ============================================== -->
<div class="modal fade" id="editModal" tabindex="-1" role="dialog" aria-labelledby="editModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">

            <div class="modal-header bg-yellow">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title" id="editModalLabel">
                    <i class="fa fa-pencil-square-o"></i> Edit Kontak
                </h4>
            </div>

            <div class="modal-body">

                <div class="form-group">
                    <label for="edit_nama">
                        <i class="fa fa-building-o"></i> Nama Perusahaan
                    </label>
                    <input type="text"
                           id="edit_nama"
                           class="form-control"
                           placeholder="Masukkan nama perusahaan"
                           autocomplete="off">
                </div>

                <div class="form-group">
                    <label for="edit_email">
                        <i class="fa fa-envelope-o"></i> Email
                    </label>
                    <input type="email"
                           id="edit_email"
                           class="form-control"
                           placeholder="Masukkan email"
                           autocomplete="off">
                </div>

                <div class="form-group">
                    <label for="edit_domisili">
                        <i class="fa fa-map-marker"></i> Domisili
                    </label>
                    <input type="text"
                           id="edit_domisili"
                           class="form-control"
                           placeholder="Masukkan domisili"
                           autocomplete="off">
                </div>

            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">
                    <i class="fa fa-times"></i> Batal
                </button>
                <button type="button" class="btn btn-warning">
                    <i class="fa fa-refresh"></i> Update
                </button>
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

    $('#kontakTable').DataTable({
        autoWidth: false,
        pageLength: 10,
        lengthMenu: [[5, 10, 25, 50, -1], [5, 10, 25, 50, 'Semua']],
        columnDefs: [
            {
                targets: [0, 1],   // kolom "No" dan "ID"
                orderable: false,
                searchable: false
            },
            {
                targets: -1,       // kolom "Aksi"
                orderable: false,
                searchable: false
            }
        ],
        language: {
            url: '//cdn.datatables.net/plug-ins/1.13.4/i18n/id.json'
        }
    });

    // Event delegation tombol Edit
    $(document).on('click', '.btn-edit', function () {
        console.log('Tombol edit diklik');
    });

});
</script>
<?= $this->endSection() ?>