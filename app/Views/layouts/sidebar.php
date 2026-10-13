<aside class="main-sidebar">
    <section class="sidebar">
        <!-- Panel user -->
        <div class="user-panel">
            <div class="pull-left image">
                <img src="<?= base_url('assets/dist/img/user2-160x160.png') ?>" class="img-circle" alt="User Image">
            </div>
            <div class="pull-left info">
                <p>Admin</p>
                <a href="#"><i class="fa fa-circle text-success"></i> Online</a>
            </div>
        </div>

        <!-- Menu -->
        <ul class="sidebar-menu" data-widget="tree">
            <li class="header">MENU UTAMA</li>

            <li class="<?= url_is('dashboard*') ? 'active' : '' ?>">
                <a href="<?= site_url('dashboard') ?>">
                    <i class="fa fa-dashboard"></i> <span>Dashboard</span>
                </a>
            </li>

            <li class="<?= url_is('kontak*') ? 'active' : '' ?>">
                <a href="<?= site_url('kontak') ?>">
                    <i class="fa fa-address-book"></i> <span>Data Kontak</span>
                </a>
            </li>

            <li class="<?= url_is('kategori-template*') ? 'active' : '' ?>">
                <a href="<?= site_url('kategori-template') ?>">
                    <i class="fa fa-tags"></i> <span>Kategori Template Email</span>
                </a>
            </li>

            <li class="<?= url_is('template-email*') ? 'active' : '' ?>">
                <a href="<?= site_url('template-email') ?>">
                    <i class="fa fa-file-text-o"></i> <span>Template Email</span>
                </a>
            </li>

            <li class="<?= url_is('kirim-email*') ? 'active' : '' ?>">
                <a href="<?= site_url('kirim-email') ?>">
                    <i class="fa fa-paper-plane"></i> <span>Kirim Email</span>
                </a>
            </li>

            <li class="<?= url_is('riwayat-pengiriman*') ? 'active' : '' ?>">
                <a href="<?= site_url('riwayat-pengiriman') ?>">
                    <i class="fa fa-history"></i> <span>Riwayat Pengiriman</span>
                </a>
            </li>
        </ul>
    </section>
</aside>