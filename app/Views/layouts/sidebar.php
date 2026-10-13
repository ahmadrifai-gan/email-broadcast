<aside class="main-sidebar">
    <section class="sidebar">

        <!-- Panel user -->
        <div class="user-panel">
            <div class="pull-left image">
                <img src="<?= base_url('assets/dist/img/user2-160x160.png') ?>"
                    class="img-circle"
                    alt="User Image">
            </div>
            <div class="pull-left info">
                <p>Admin</p>
                <a href="#"><i class="fa fa-circle text-success"></i> Online</a>
            </div>
        </div>

        <!-- ============================================== -->
        <!-- SIDEBAR SEARCH (bawaan AdminLTE) -->
        <!-- ============================================== -->
        <form action="#" method="get" class="sidebar-form" id="sidebarSearchForm">
            <div class="input-group">
                <input type="text"
                    name="q"
                    id="sidebarSearchInput"
                    class="form-control"
                    placeholder="Cari menu..."
                    autocomplete="off">
                <span class="input-group-btn">
                    <button type="button" id="sidebarSearchClear" class="btn btn-flat" style="display:none;">
                        <i class="fa fa-times"></i>
                    </button>
                    <button type="submit" name="search" id="search-btn" class="btn btn-flat">
                        <i class="fa fa-search"></i>
                    </button>
                </span>
            </div>
        </form>

        <!-- Menu -->
        <ul class="sidebar-menu" data-widget="tree" id="sidebarMenu">
            <li class="<?= url_is('dashboard*') ? 'active' : '' ?>">
                <a href="<?= site_url('dashboard') ?>">
                    <i class="fa fa-dashboard"></i> <span>Dashboard</span>
                </a>
            </li>

            <li class="header">MANAJEMEN KONTAK</li>
            <li class="<?= url_is('kontak*') ? 'active' : '' ?>">
                <a href="<?= site_url('kontak') ?>">
                    <i class="fa fa-address-book"></i> <span>Data Kontak</span>
                </a>
            </li>

            <li class="header">MANAJEMEN TEMPLATE</li>
            <li class="<?= url_is('kategori-template-email*') ? 'active' : '' ?>">
                <a href="<?= site_url('kategori-template-email') ?>">
                    <i class="fa fa-tags"></i> <span>Kategori Template Email</span>
                </a>
            </li>

            <li class="<?= url_is('template-email*') ? 'active' : '' ?>">
                <a href="<?= site_url('template-email') ?>">
                    <i class="fa fa-file-text-o"></i> <span>Template Email</span>
                </a>
            </li>

            <li class="header">PENGIRIMAN EMAIL</li>
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

        <!-- Pesan kalau tidak ada hasil -->
        <p id="sidebarSearchEmpty"
            class="text-center text-muted"
            style="display:none; padding: 10px; font-size: 12px;">
            <i class="fa fa-search"></i> Menu tidak ditemukan
        </p>

    </section>
</aside>

<script>
    (function() {
        document.addEventListener('DOMContentLoaded', function() {

            var input = document.getElementById('sidebarSearchInput');
            var clearBtn = document.getElementById('sidebarSearchClear');
            var form = document.getElementById('sidebarSearchForm');
            var menu = document.getElementById('sidebarMenu');
            var empty = document.getElementById('sidebarSearchEmpty');

            if (!input || !menu) return;

            // Filter menu berdasarkan keyword
            function filterMenu() {
                var keyword = input.value.toLowerCase().trim();

                // Toggle tombol clear
                clearBtn.style.display = keyword ? 'inline-block' : 'none';

                var items = menu.querySelectorAll('li:not(.header)');
                var headers = menu.querySelectorAll('li.header');
                var found = 0;

                // Filter item menu
                items.forEach(function(li) {
                    var text = (li.textContent || '').toLowerCase();
                    if (text.indexOf(keyword) !== -1) {
                        li.style.display = '';
                        found++;
                    } else {
                        li.style.display = 'none';
                    }
                });

                // Sembunyikan header yang tidak punya item anak yang tampil
                headers.forEach(function(header) {
                    var next = header.nextElementSibling;
                    var hasVisible = false;
                    while (next && !next.classList.contains('header')) {
                        if (next.style.display !== 'none' && !next.classList.contains('header')) {
                            hasVisible = true;
                            break;
                        }
                        next = next.nextElementSibling;
                    }
                    header.style.display = hasVisible ? '' : 'none';
                });

                // Tampilkan pesan "tidak ditemukan"
                empty.style.display = (found === 0 && keyword) ? 'block' : 'none';
            }

            // Event: ketik
            input.addEventListener('input', filterMenu);
            input.addEventListener('keyup', filterMenu);

            // Event: klik tombol clear (X)
            clearBtn.addEventListener('click', function() {
                input.value = '';
                filterMenu();
                input.focus();
            });

            // Cegah form submit (biar tidak reload halaman)
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                filterMenu();
            });

            // Shortcut: tekan "/" untuk fokus ke search box
            document.addEventListener('keydown', function(e) {
                if (e.key === '/' && document.activeElement.tagName !== 'INPUT' && document.activeElement.tagName !== 'TEXTAREA') {
                    e.preventDefault();
                    input.focus();
                    input.select();
                }
            });

        });
    })();
</script>