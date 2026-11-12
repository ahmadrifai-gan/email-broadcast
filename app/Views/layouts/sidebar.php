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
            <li class="<?= url_is('*') && !url_is('kategori*') && !url_is('kontak*') && !url_is('kategori-template-email*') && !url_is('template-email*') && !url_is('kirim-email*') && !url_is('riwayat-pengiriman*') && !url_is('smtp*') && !url_is('laporan-*') ? 'active' : '' ?>">
                <a href="<?= site_url('') ?>">
                    <i class="fa fa-dashboard"></i> <span>Dashboard</span>
                </a>
            </li>

            <li class="header">Kategori KONTAK</li>
            <li class="<?= url_is('kategori*') && !url_is('kategori-template-email*') ? 'active' : '' ?>">
                <a href="<?= site_url('kategori') ?>">
                    <i class="fa fa-address-book"></i> <span>Kategori Kontak</span>
                </a>
            </li>
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

            <!-- ============================================== -->
            <!-- MENU BARU: LAPORAN AKTIVITAS (DROPDOWN) -->
            <!-- ============================================== -->
            <li class="header">LAPORAN</li>
            <li class="treeview <?= url_is('laporan-*') ? 'active' : '' ?>">
                <a href="#">
                    <i class="fa fa-bar-chart"></i> <span>Laporan Aktivitas</span>
                    <i class="fa fa-angle-left pull-right"></i>
                </a>
                <ul class="treeview-menu">
                    <li class="<?= url_is('laporan-ringkasan-kampanye*') ? 'active' : '' ?>">
                        <a href="<?= site_url('laporan-ringkasan-kampanye') ?>">
                            <i class="fa fa-circle-o"></i> Ringkasan Kampanye
                        </a>
                    </li>
                     <li class="<?= url_is('laporan-smtp-kuota') ? 'active' : '' ?>">
    <a href="<?= site_url('laporan-smtp-kuota') ?>">
        <i class="fa fa-circle-o"></i> Laporan SMTP dan Kuota
    </a>
</li>
                    <li class="<?= url_is('laporan-per-kampanye*') ? 'active' : '' ?>">
                        <a href="<?= site_url('laporan-per-kampanye') ?>">
                            <i class="fa fa-circle-o"></i> Laporan per Kampanye
                        </a>
                    </li>
                    <li class="<?= url_is('laporan-template*') ? 'active' : '' ?>">
                        <a href="<?= site_url('laporan-template') ?>">
                            <i class="fa fa-circle-o"></i> Laporan Template
                        </a>
                    </li>
                    <li class="<?= url_is('laporan-penerima-kontak*') ? 'active' : '' ?>">
                        <a href="<?= site_url('laporan-penerima-kontak') ?>">
                            <i class="fa fa-circle-o"></i> Laporan Penerima dan Kontak
                        </a>
                    </li>
                </ul>
            </li>

            <li class="header">KONFIGURASI</li>
            <li class="<?= url_is('smtp*') && !url_is('laporan-smtp-kuota*') ? 'active' : '' ?>">
                <a href="<?= site_url('smtp') ?>">
                    <i class="fa fa-server"></i> <span>Konfigurasi SMTP</span>
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

            var input    = document.getElementById('sidebarSearchInput');
            var clearBtn = document.getElementById('sidebarSearchClear');
            var form     = document.getElementById('sidebarSearchForm');
            var menu     = document.getElementById('sidebarMenu');
            var empty    = document.getElementById('sidebarSearchEmpty');

            if (!input || !menu) return;

            // Filter menu berdasarkan keyword (mendukung submenu treeview)
            function filterMenu() {
                var keyword = input.value.toLowerCase().trim();

                // Toggle tombol clear
                clearBtn.style.display = keyword ? 'inline-block' : 'none';

                var allItems = menu.querySelectorAll('li:not(.header)');
                var headers  = menu.querySelectorAll('li.header');

                // 1) Tandai tiap item apakah labelnya cocok dengan keyword
                allItems.forEach(function(li) {
                    var link  = li.querySelector(':scope > a');
                    var label = link ? link.textContent : li.textContent;
                    li.dataset.matched = (!keyword || label.toLowerCase().indexOf(keyword) !== -1) ? '1' : '0';
                });

                // 2) Sembunyikan dulu leaf (item tanpa submenu) berdasarkan kecocokan label
                allItems.forEach(function(li) {
                    var hasChildren = li.querySelector(':scope > ul.treeview-menu');
                    if (!hasChildren) {
                        li.style.display = li.dataset.matched === '1' ? '' : 'none';
                    }
                });

                // 3) Proses treeview dari yang paling dalam ke luar
                //    Parent tampil jika dirinya cocok ATAU ada anak yang cocok
                var treeviews = Array.prototype.slice
                    .call(menu.querySelectorAll('li.treeview'))
                    .reverse();

                treeviews.forEach(function(tv) {
                    var submenu = tv.querySelector(':scope > ul.treeview-menu');
                    var anyChildVisible = false;

                    Array.prototype.forEach.call(
                        tv.querySelectorAll(':scope > ul.treeview-menu > li'),
                        function(child) {
                            if (child.style.display !== 'none') anyChildVisible = true;
                        }
                    );

                    var shouldShow = (tv.dataset.matched === '1' || anyChildVisible);

                    tv.style.display = shouldShow ? '' : 'none';

                    if (keyword && shouldShow && submenu) {
                        // Paksa buka dropdown saat pencarian
                        submenu.style.display = 'block';
                        tv.classList.add('search-expanded');
                    } else if (tv.classList.contains('search-expanded')) {
                        // Reset ke kondisi semula (mengikuti CSS AdminLTE)
                        submenu.style.display = '';
                        tv.classList.remove('search-expanded');
                    }
                });

                // 4) Sembunyikan header yang tidak punya item anak yang tampil
                headers.forEach(function(header) {
                    var next = header.nextElementSibling;
                    var hasVisible = false;
                    while (next && !next.classList.contains('header')) {
                        if (next.style.display !== 'none') {
                            hasVisible = true;
                            break;
                        }
                        next = next.nextElementSibling;
                    }
                    header.style.display = hasVisible ? '' : 'none';
                });

                // 5) Tampilkan pesan "tidak ditemukan" jika tidak ada yang visible
                var anyVisible = false;
                menu.querySelectorAll(':scope > li:not(.header)').forEach(function(li) {
                    if (li.style.display !== 'none') anyVisible = true;
                });

                empty.style.display = (!anyVisible && keyword) ? 'block' : 'none';
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
                if (e.key === '/' &&
                    document.activeElement.tagName !== 'INPUT' &&
                    document.activeElement.tagName !== 'TEXTAREA') {
                    e.preventDefault();
                    input.focus();
                    input.select();
                }
            });

        });
    })();
</script>