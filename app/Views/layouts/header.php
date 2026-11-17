<!-- Bootstrap 3.3.7 -->
<link rel="stylesheet" href="<?= base_url('assets/bower_components/bootstrap/dist/css/bootstrap.min.css') ?>">
<!-- Font Awesome -->
<link rel="stylesheet" href="<?= base_url('assets/bower_components/font-awesome/css/font-awesome.min.css') ?>">
<!-- Ionicons -->
<link rel="stylesheet" href="<?= base_url('assets/bower_components/Ionicons/css/ionicons.min.css') ?>">
<!-- AdminLTE -->
<link rel="stylesheet" href="<?= base_url('assets/dist/css/AdminLTE.min.css') ?>">
<link rel="stylesheet" href="<?= base_url('assets/dist/css/skins/_all-skins.min.css') ?>">

<!-- DataTables -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.bootstrap.min.css">

<!-- Google Font -->
<link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,600,700,300italic,400italic,600italic">
<style>
    /* 1. Memperbaiki teks sidebar utama agar bisa turun baris */
    .sidebar-menu>li>a {
        white-space: normal !important;
        word-wrap: break-word !important;
        overflow: visible !important;
        padding-right: 10px !important;
    }

    /* 2. Memperbaiki submenu (treeview-menu) agar rapi saat turun baris */
    .treeview-menu>li>a {
        white-space: normal !important;
        word-wrap: break-word !important;
        display: flex !important;
        align-items: flex-start !important;
        padding: 8px 5px 8px 15px !important;
    }

    /* 3. Memberi jarak pada ikon lingkaran agar tidak menempel ke teks */
    .treeview-menu>li>a>.fa {
        margin-right: 8px !important;
        margin-top: 3px !important;
        /* Menyesuaikan posisi ikon jika teks 2 baris */
        flex-shrink: 0 !important;
        /* Mencegah ikon mengecil */
    }

    /* 4. Memastikan teks yang aktif (berwarna putih) tetap terbaca jelas */
    .treeview-menu>li.active>a {
        color: #fff !important;
    }
</style>
<!--[if lt IE 9]>
<script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
<script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
<![endif]-->