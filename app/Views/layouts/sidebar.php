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
        <!-- SIDEBAR SEARCH -->
        <!-- ============================================== -->
        <form action="#" method="get" class="sidebar-form" id="sidebarSearchForm" autocomplete="off">
            <div class="input-group">
                <input type="text"
                    name="q"
                    id="sidebarSearchInput"
                    class="form-control"
                    placeholder="Cari menu..."
                    autocomplete="off">
                <span class="input-group-btn">
                    <button type="button" id="sidebarSearchClear" class="btn btn-flat" style="display:none;" title="Bersihkan">
                        <i class="fa fa-times"></i>
                    </button>
                    <button type="submit" name="search" id="search-btn" class="btn btn-flat" title="Cari">
                        <i class="fa fa-search"></i>
                    </button>
                </span>
            </div>
        </form>

        <!-- Menu -->
        <ul class="sidebar-menu" data-widget="tree" id="sidebarMenu">
            <li class="header">MENU UTAMA</li>
            <li class="<?= url_is('') || url_is('/') ? 'active' : '' ?>">
                <a href="<?= site_url('') ?>">
                    <i class="fa fa-dashboard"></i> <span>Dashboard</span>
                </a>
            </li>

            <li class="header">KATEGORI KONTAK</li>
            <li class="<?= url_is('kategori') || url_is('kategori/*') ? 'active' : '' ?>">
                <a href="<?= site_url('kategori') ?>">
                    <i class="fa fa-address-book"></i> <span>Kategori Kontak</span>
                </a>
            </li>
            <li class="<?= url_is('kontak*') ? 'active' : '' ?>">
                <a href="<?= site_url('kontak') ?>">
                    <i class="fa fa-address-book-o"></i> <span>Data Kontak</span>
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

            <!-- LAPORAN (DROPDOWN) -->
            <li class="header">LAPORAN</li>
            <li class="treeview <?= url_is('laporan*') ? 'active' : '' ?>">
                <a href="#">
                    <i class="fa fa-bar-chart"></i> <span>Laporan Aktivitas</span>
                    <i class="fa fa-angle-left pull-right"></i>
                </a>
                <ul class="treeview-menu">
                    <li class="<?= url_is('laporan/ringkasan-kampanye*') ? 'active' : '' ?>">
                        <a href="<?= site_url('laporan/ringkasan-kampanye') ?>">
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
                    <li class="<?= url_is('laporan/penerima-kontak*') ? 'active' : '' ?>">
                        <a href="<?= site_url('laporan/penerima-kontak') ?>">
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
    'use strict';

    document.addEventListener('DOMContentLoaded', function() {
        var input     = document.getElementById('sidebarSearchInput');
        var clearBtn  = document.getElementById('sidebarSearchClear');
        var form      = document.getElementById('sidebarSearchForm');
        var menu      = document.getElementById('sidebarMenu');
        var emptyMsg  = document.getElementById('sidebarSearchEmpty');

        if (!input || !menu) return;

        // Simpan state awal treeview yang terbuka (aktif) agar bisa dikembalikan
        var openState = {};
        menu.querySelectorAll('li.treeview').forEach(function(tv, i) {
            openState[i] = tv.classList.contains('active') || tv.classList.contains('menu-open');
        });

        function resetTreeview() {
            menu.querySelectorAll('li.treeview').forEach(function(tv, i) {
                var submenu = tv.querySelector(':scope > ul.treeview-menu');
                if (!submenu) return;
                if (openState[i]) {
                    tv.classList.add('menu-open');
                    submenu.style.display = 'block';
                } else {
                    tv.classList.remove('menu-open');
                    submenu.style.display = '';
                }
            });
        }

        function filterMenu() {
            var keyword = input.value.toLowerCase().trim();
            clearBtn.style.display = keyword ? 'inline-block' : 'none';

            var headers   = menu.querySelectorAll('li.header');
            var treeviews = menu.querySelectorAll('li.treeview');
            var allItems  = menu.querySelectorAll('li:not(.header)');

            // --- Reset ---
            if (!keyword) {
                allItems.forEach(function(li) {
                    li.style.display = '';
                    li.dataset.matched = '';
                });
                headers.forEach(function(h) { h.style.display = ''; });
                emptyMsg.style.display = 'none';
                resetTreeview();
                return;
            }

            // --- 1. Tandai item yang cocok ---
            allItems.forEach(function(li) {
                var link = li.querySelector(':scope > a');
                var label = link ? link.textContent.toLowerCase() : '';
                li.dataset.matched = (label.indexOf(keyword) !== -1) ? '1' : '0';
            });

            // --- 2. Item biasa (non-treeview) ---
            allItems.forEach(function(li) {
                var hasSubmenu = li.querySelector(':scope > ul.treeview-menu');
                if (hasSubmenu) return; // skip parent treeview
                li.style.display = (li.dataset.matched === '1') ? '' : 'none';
            });

            // --- 3. Treeview (dari bawah ke atas) ---
            var revTreeviews = Array.prototype.slice.call(treeviews).reverse();
            revTreeviews.forEach(function(tv) {
                var submenu = tv.querySelector(':scope > ul.treeview-menu');
                if (!submenu) return;

                var childItems = submenu.querySelectorAll(':scope > li');
                var anyChildVisible = false;

                childItems.forEach(function(child) {
                    if (child.style.display !== 'none') anyChildVisible = true;
                });

                var parentMatch = tv.dataset.matched === '1';
                var shouldShow  = parentMatch || anyChildVisible;

                tv.style.display = shouldShow ? '' : 'none';

                if (shouldShow) {
                    submenu.style.display = 'block';
                    tv.classList.add('menu-open');
                } else {
                    submenu.style.display = 'none';
                    tv.classList.remove('menu-open');
                }
            });

            // --- 4. Sembunyikan header yang kosong ---
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

            // --- 5. Pesan "tidak ditemukan" ---
            var anyVisible = false;
            menu.querySelectorAll(':scope > li:not(.header)').forEach(function(li) {
                if (li.style.display !== 'none') anyVisible = true;
            });
            emptyMsg.style.display = anyVisible ? 'none' : 'block';
        }

        // Event: input dengan debounce ringan
        var debounceTimer = null;
        input.addEventListener('input', function() {
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(filterMenu, 120);
        });

        // Tombol clear
        clearBtn.addEventListener('click', function() {
            input.value = '';
            filterMenu();
            input.focus();
        });

        // Form submit (cegah reload)
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            filterMenu();
        });

        // Shortcut "/" untuk fokus pencarian
        document.addEventListener('keydown', function(e) {
            var el = document.activeElement;
            var tag = (el && el.tagName) || '';
            if (e.key === '/' &&
                tag !== 'INPUT' &&
                tag !== 'TEXTAREA' &&
                tag !== 'SELECT' &&
                !(el && el.isContentEditable)) {
                e.preventDefault();
                input.focus();
                input.select();
            }
        });
    });
})();
</script>