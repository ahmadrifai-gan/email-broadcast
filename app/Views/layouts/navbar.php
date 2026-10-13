<header class="main-header">
    <!-- Logo -->
    <a href="<?= site_url('dashboard') ?>" class="logo">
        <span class="logo-mini"><b>E</b>B</span>
        <span class="logo-lg"><b>Email</b> Blast</span>
    </a>

    <nav class="navbar navbar-static-top">
        <!-- Tombol toggle sidebar -->
        <a href="#" class="sidebar-toggle" data-toggle="push-menu" role="button">
            <span class="sr-only">Toggle navigation</span>
        </a>

        <div class="navbar-custom-menu">
            <ul class="nav navbar-nav">
                <!-- Menu user -->
                <li class="dropdown user user-menu">
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                        <img src="<?= base_url('assets/dist/img/user2-160x160.png') ?>" class="user-image" alt="User Image">
                        <span class="hidden-xs">Admin</span>
                    </a>
                    <ul class="dropdown-menu">
                        <li class="user-header">
                            <img src="<?= base_url('assets/dist/img/user2-160x160.png') ?>" class="img-circle" alt="User Image">
                            <p>
                                Admin
                                <small>Administrator</small>
                            </p>
                        </li>
                        <li class="user-footer">
                            <div class="pull-left">
                                <a href="#" class="btn btn-default btn-flat">Profil</a>
                            </div>
                            <div class="pull-right">
                                <a href="#" class="btn btn-default btn-flat">Keluar</a>
                            </div>
                        </li>
                    </ul>
                </li>
            </ul>
        </div>
    </nav>
</header>