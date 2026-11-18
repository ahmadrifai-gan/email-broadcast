<?= $this->extend('layouts/main') ?>

<?= $this->section('page_title') ?>Data Perusahaan<?= $this->endSection() ?>
<?= $this->section('page_subtitle') ?>Daftar perusahaan dan status analisis produk<?= $this->endSection() ?>

<?= $this->section('breadcrumb') ?>
<li><a href="<?= site_url('/') ?>"><i class="fa fa-dashboard"></i> Home</a></li>
<li class="active">Data Perusahaan</li>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<div class="app-content pt-3 p-md-3 p-lg-4">
    <div class="container-xl">
        <!-- HEADER -->
        <div class="row g-3 mb-4 align-items-center justify-content-between">
            <div class="col-auto">
                <h1 class="app-page-title mb-0">
                    <i class="fa-solid fa-building text-primary me-2"></i>Data Perusahaan
                </h1>
                <div class="text-muted small mt-1">Daftar seluruh perusahaan dari sheet PRIORITAS beserta status inferensi produk AI.</div>
            </div>
            <div class="col-auto">
                <div class="d-flex flex-wrap gap-2">
                    <a href="<?= base_url('admin/analyzer/import') ?>" class="btn btn-outline-secondary">
                        <i class="fa-solid fa-file-excel me-1 text-success"></i> Import Excel
                    </a>
                    <a href="<?= base_url('admin/analyzer/analisis') ?>" class="btn btn-primary text-white">
                        <i class="fa-solid fa-robot me-1"></i> Mulai Analisis AI
                    </a>
                    <a href="<?= base_url('admin/analyzer/export/download') ?>" class="btn btn-outline-success">
                        <i class="fa-solid fa-download me-1"></i> Export Excel
                    </a>
                    <button type="button" class="btn btn-outline-primary" onclick="openImportAllModal()">
                        <i class="fa-solid fa-boxes-packing me-1"></i> Import Semua ke Supplier
                    </button>
                </div>
            </div>
        </div>

        <!-- FLASH NOTIFICATION -->
        <?php if (session()->getFlashdata('success')): ?>
            <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
                <i class="fa-solid fa-circle-check me-2"></i> <?= session()->getFlashdata('success') ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
        <?php if (session()->getFlashdata('error')): ?>
            <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
                <i class="fa-solid fa-circle-exclamation me-2"></i> <?= session()->getFlashdata('error') ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <!-- FILTER & SEARCH BAR -->
        <div class="app-card shadow-sm mb-4">
            <div class="app-card-body p-3 p-lg-4">
                <form method="GET" action="<?= base_url('admin/analyzer/perusahaan') ?>" class="row g-3 align-items-center">
                    <div class="col-12 col-md-5">
                        <div class="input-group">
                            <span class="input-group-text bg-light"><i class="fa-solid fa-magnifying-glass"></i></span>
                            <input type="text" name="search" class="form-control" placeholder="Cari nama perusahaan, kategori, website..." value="<?= esc($filter['search'] ?? '') ?>">
                        </div>
                    </div>
                    <div class="col-12 col-md-4">
                        <select name="status" class="form-select" onchange="this.form.submit()">
                            <option value="">Semua Status Analisis (<?= $stats['total'] ?>)</option>
                            <option value="belum_dianalisis" <?= ($filter['status'] === 'belum_dianalisis') ? 'selected' : '' ?>>Belum Dianalisis (<?= $stats['belum'] ?>)</option>
                            <option value="sukses" <?= ($filter['status'] === 'sukses') ? 'selected' : '' ?>>Sukses AI (<?= $stats['sukses'] ?>)</option>
                            <option value="manual_edit" <?= ($filter['status'] === 'manual_edit') ? 'selected' : '' ?>>Koreksi Manual (<?= $stats['manual_edit'] ?>)</option>
                            <option value="gagal" <?= ($filter['status'] === 'gagal') ? 'selected' : '' ?>>Gagal Dianalisis (<?= $stats['gagal'] ?>)</option>
                        </select>
                    </div>
                    <div class="col-12 col-md-3 d-flex gap-2">
                        <button type="submit" class="btn btn-secondary flex-grow-1">Filter</button>
                        <?php if (!empty($filter['search']) || !empty($filter['status'])): ?>
                            <a href="<?= base_url('admin/analyzer/perusahaan') ?>" class="btn btn-outline-danger" title="Reset filter"><i class="fa-solid fa-rotate-left"></i></a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>
        </div>

        <!-- TABLE DATA -->
        <div class="app-card shadow-sm mb-4">
            <div class="app-card-header p-3 border-bottom d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center gap-2">
                    <span class="fw-semibold text-dark">Total: <?= number_format($totalRows) ?> Perusahaan</span>
                </div>
                <div id="bulkActionContainer" style="display: none;">
                    <button class="btn btn-sm btn-danger" onclick="executeBulkDelete()">
                        <i class="fa-solid fa-trash me-1"></i> Hapus Terpilih (<span id="selectedCount">0</span>)
                    </button>
                </div>
            </div>
            <div class="app-card-body p-0">
                <div class="table-responsive">
                    <table class="table app-table-hover mb-0 text-start align-middle">
                        <thead class="table-light">
                            <tr>
                                <th class="cell" style="width: 40px;">
                                    <input type="checkbox" id="selectAll" class="form-check-input" onclick="toggleSelectAll(this)">
                                </th>
                                <th class="cell" style="width: 60px;">No</th>
                                <th class="cell">Nama Perusahaan</th>
                                <th class="cell">Kategori</th>
                                <th class="cell">Tautan</th>
                                <th class="cell">Status</th>
                                <th class="cell" style="min-width: 200px;">Produk Yang Dijual (Auto)</th>
                                <th class="cell text-center" style="width: 175px;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($companies)): ?>
                                <tr>
                                    <td colspan="8" class="text-center py-5 text-muted">
                                        <i class="fa-solid fa-circle-info fa-2x mb-2 d-block"></i>
                                        Tidak ada data perusahaan yang ditemukan. Silakan import file Excel terlebih dahulu.
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($companies as $comp): ?>
                                    <tr>
                                        <td class="cell">
                                            <input type="checkbox" class="form-check-input row-select" value="<?= $comp['id'] ?>" onclick="updateSelectedCount()">
                                        </td>
                                        <td class="cell text-muted small">
                                            <?= esc($comp['nomor_excel'] ?: $comp['id']) ?>
                                        </td>
                                        <td class="cell fw-semibold text-dark">
                                            <?= esc($comp['nama_perusahaan']) ?>
                                        </td>
                                        <td class="cell small">
                                            <div class="text-truncate" style="max-width: 180px;" title="<?= esc($comp['main_categories'] ?? '-') ?>">
                                                <strong><?= esc($comp['main_categories'] ?: '-') ?></strong>
                                            </div>
                                            <div class="text-muted text-truncate" style="max-width: 180px;" title="<?= esc($comp['sub_categories'] ?? '-') ?>">
                                                <?= esc($comp['sub_categories'] ?: '-') ?>
                                            </div>
                                        </td>
                                        <td class="cell small">
                                            <div class="d-flex flex-column gap-1">
                                                <?php if (!empty($comp['website']) && $comp['website'] !== '-'): ?>
                                                    <a href="<?= (strpos($comp['website'], 'http') === 0) ? esc($comp['website']) : 'https://' . esc($comp['website']) ?>" target="_blank" class="badge bg-light text-primary border text-truncate text-start" style="max-width: 120px;" title="<?= esc($comp['website']) ?>">
                                                        <i class="fa-solid fa-globe me-1"></i>Web
                                                    </a>
                                                <?php else: ?>
                                                    <span class="badge bg-light text-muted border text-start" style="max-width: 120px;">
                                                        <i class="fa-solid fa-globe me-1"></i>-
                                                    </span>
                                                <?php endif; ?>

                                                <?php if (!empty($comp['link_catalog']) && $comp['link_catalog'] !== '-'): ?>
                                                    <a href="<?= esc($comp['link_catalog']) ?>" target="_blank" class="badge bg-light text-danger border text-truncate text-start" style="max-width: 120px;" title="<?= esc($comp['link_catalog']) ?>">
                                                        <i class="fa-solid fa-file-pdf me-1"></i>Katalog
                                                    </a>
                                                <?php else: ?>
                                                    <span class="badge bg-light text-muted border text-start" style="max-width: 120px;">
                                                        <i class="fa-solid fa-file-pdf me-1"></i>-
                                                    </span>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                        <td class="cell">
                                            <?php if ($comp['status_analisis'] === 'sukses'): ?>
                                                <span class="badge bg-success"><i class="fa-solid fa-check me-1"></i>Sukses</span>
                                            <?php elseif ($comp['status_analisis'] === 'manual_edit'): ?>
                                                <span class="badge bg-info text-dark"><i class="fa-solid fa-pen me-1"></i>Koreksi</span>
                                            <?php elseif ($comp['status_analisis'] === 'gagal'): ?>
                                                <span class="badge bg-danger"><i class="fa-solid fa-xmark me-1"></i>Gagal</span>
                                            <?php else: ?>
                                                <span class="badge bg-warning text-dark"><i class="fa-solid fa-clock me-1"></i>Belum</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="cell small">
                                            <?php if (!empty($comp['produk_yang_dijual_auto'])): ?>
                                                <div class="p-2 bg-light rounded border text-muted" style="max-height: 80px; overflow-y: auto; white-space: pre-line; font-size: 0.82rem;">
                                                    <?= esc($comp['produk_yang_dijual_auto']) ?>
                                                </div>
                                            <?php else: ?>
                                                <span class="text-muted fst-italic">Belum ada hasil analisis.</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="cell text-center">
                                            <div class="btn-group btn-group-sm">
                                                <button type="button" class="btn btn-light border" onclick="showCompanyDetail(<?= $comp['id'] ?>)" title="Lihat Detail">
                                                    <i class="fa-solid fa-eye text-primary"></i>
                                                </button>
                                                <button type="button" class="btn btn-light border" onclick="analyzeSingleCompany(<?= $comp['id'] ?>, '<?= esc($comp['nama_perusahaan'], 'js') ?>')" title="Analisis Ulang AI">
                                                    <i class="fa-solid fa-robot text-success"></i>
                                                </button>
                                                <button type="button" class="btn btn-light border" onclick="openEditProductModal(<?= $comp['id'] ?>)" title="Edit Produk Manual">
                                                    <i class="fa-solid fa-pen-to-square text-info"></i>
                                                </button>
                                                <?php
                                                $prodCount = count($comp['produk_list'] ?? []);
                                                $canImport = in_array($comp['status_analisis'], ['sukses', 'manual_edit'], true) && $prodCount > 0;
                                                ?>
                                                <button type="button" class="btn btn-light border btn-import-supplier"
                                                    data-id="<?= $comp['id'] ?>"
                                                    data-name="<?= esc($comp['nama_perusahaan'], 'attr') ?>"
                                                    data-status="<?= esc($comp['status_analisis'], 'attr') ?>"
                                                    data-products="<?= $prodCount ?>"
                                                    title="<?= $canImport ? 'Import ke Supplier (' . $prodCount . ' produk)' : ($prodCount === 0 ? 'Belum ada produk hasil AI (Klik Analisis AI terlebih dahulu)' : 'Status analisis: ' . $comp['status_analisis']) ?>">
                                                    <i class="fa-solid fa-file-import <?= $canImport ? 'text-success' : 'text-secondary opacity-50' ?>"></i>
                                                </button>
                                                <a href="<?= base_url('admin/analyzer/perusahaan/delete/' . $comp['id']) ?>" class="btn btn-light border" onclick="return confirm('Apakah Anda yakin ingin menghapus perusahaan ini?');" title="Hapus">
                                                    <i class="fa-solid fa-trash text-danger"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- PAGINATION -->
            <?php if ($totalPages > 1): ?>
                <div class="app-card-footer p-3 border-top d-flex justify-content-between align-items-center">
                    <span class="text-muted small">Halaman <?= $currentPage ?> dari <?= $totalPages ?> (Total <?= number_format($totalRows) ?> data)</span>
                    <nav>
                        <ul class="pagination pagination-sm mb-0">
                            <?php if ($currentPage > 1): ?>
                                <li class="page-item">
                                    <a class="page-link" href="<?= base_url('admin/analyzer/perusahaan?page=' . ($currentPage - 1) . '&status=' . urlencode($filter['status']) . '&search=' . urlencode($filter['search'])) ?>">&laquo;</a>
                                </li>
                            <?php endif; ?>

                            <?php
                            $startPage = max(1, $currentPage - 2);
                            $endPage = min($totalPages, $currentPage + 2);
                            for ($i = $startPage; $i <= $endPage; $i++):
                            ?>
                                <li class="page-item <?= ($i == $currentPage) ? 'active' : '' ?>">
                                    <a class="page-link" href="<?= base_url('admin/analyzer/perusahaan?page=' . $i . '&status=' . urlencode($filter['status']) . '&search=' . urlencode($filter['search'])) ?>"><?= $i ?></a>
                                </li>
                            <?php endfor; ?>

                            <?php if ($currentPage < $totalPages): ?>
                                <li class="page-item">
                                    <a class="page-link" href="<?= base_url('admin/analyzer/perusahaan?page=' . ($currentPage + 1) . '&status=' . urlencode($filter['status']) . '&search=' . urlencode($filter['search'])) ?>">&raquo;</a>
                                </li>
                            <?php endif; ?>
                        </ul>
                    </nav>
                </div>
            <?php endif; ?>
        </div>

    </div>
</div>

<!-- MODAL DETAIL PERUSAHAAN -->
<div class="modal fade" id="modalCompanyDetail" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalCompanyTitle">Detail Perusahaan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="modalCompanyBody">
                <div class="text-center py-4">
                    <div class="spinner-border text-primary" role="status"></div>
                    <div class="mt-2 text-muted">Memuat data...</div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<!-- MODAL EDIT PRODUK MANUAL -->
<div class="modal fade" id="modalEditProduct" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalEditTitle">Edit Produk Manual</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="formEditProduct">
                    <input type="hidden" id="editCompanyId" name="company_id">
                    <p class="text-muted small">Tambahkan atau sesuaikan daftar produk yang benar-benar dijual perusahaan dalam Bahasa Indonesia (ID) dan Bahasa Inggris (EN).</p>
                    
                    <div class="table-responsive mb-3">
                        <table class="table table-bordered table-sm align-middle" id="tableEditItems">
                            <thead class="table-light">
                                <tr>
                                    <th style="width: 45%;">Nama Produk (Bahasa Indonesia)</th>
                                    <th style="width: 45%;">Nama Produk (English)</th>
                                    <th style="width: 10%;" class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="productRowsContainer">
                                <!-- Dinamis via JavaScript -->
                            </tbody>
                        </table>
                    </div>

                    <button type="button" class="btn btn-sm btn-outline-primary" onclick="addProductRow('', '')">
                        <i class="fa-solid fa-plus me-1"></i> Tambah Baris Produk
                    </button>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-primary text-white" id="btnSaveProduct" onclick="saveProductEdits()">
                    <i class="fa-solid fa-floppy-disk me-1"></i> Simpan Koreksi
                </button>
            </div>
        </div>
    </div>
</div>

<!-- MODAL PREVIEW & KONFIRMASI IMPORT SEMUA KE SUPPLIER -->
<div class="modal fade" id="modalImportAll" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-light">
                <h5 class="modal-title">
                    <i class="fa-solid fa-boxes-packing text-primary me-2"></i>Import Semua ke Supplier
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="modalImportAllBody">
                <div class="text-center py-4" id="importAllLoading">
                    <div class="spinner-border text-primary" role="status"></div>
                    <div class="mt-2 text-muted">Menghitung ringkasan data perusahaan dan produk...</div>
                </div>
                <div id="importAllContent" style="display: none;">
                    <div class="alert alert-info d-flex align-items-center mb-3">
                        <i class="fa-solid fa-circle-info fa-lg me-2"></i>
                        <div>
                            Fitur ini akan mengimpor seluruh perusahaan yang <strong>sudah dianalisis sukses</strong> atau <strong>koreksi manual</strong> dan memiliki daftar produk ke Portal Supplier.
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <div class="card border-success h-100 shadow-sm">
                                <div class="card-header bg-success text-white py-2 fw-semibold">
                                    <i class="fa-solid fa-check-circle me-1"></i> Siap Diimpor
                                </div>
                                <div class="card-body">
                                    <div class="d-flex justify-content-between mb-2">
                                        <span class="text-muted">Perusahaan Memenuhi Syarat:</span>
                                        <span class="fw-bold fs-6 text-success" id="prevEligibleCompany">0</span>
                                    </div>
                                    <div class="d-flex justify-content-between mb-2">
                                        <span class="text-muted">Total Baris Produk:</span>
                                        <span class="fw-bold fs-6 text-dark" id="prevEligibleProduct">0</span>
                                    </div>
                                    <hr class="my-2">
                                    <div class="d-flex justify-content-between small mb-1">
                                        <span class="text-muted"><i class="fa-solid fa-plus-circle text-success me-1"></i> Supplier Baru:</span>
                                        <span class="fw-semibold" id="prevNewSupplier">0</span>
                                    </div>
                                    <div class="d-flex justify-content-between small">
                                        <span class="text-muted"><i class="fa-solid fa-link text-info me-1"></i> Supplier Sudah Ada:</span>
                                        <span class="fw-semibold" id="prevExistingSupplier">0</span>
                                    </div>
                                    <div class="text-muted small mt-2 fst-italic">
                                        *Supplier yang sudah ada tidak akan ditimpa, hanya produk baru yang akan ditambahkan.
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="card border-secondary h-100 shadow-sm">
                                <div class="card-header bg-secondary text-white py-2 fw-semibold">
                                    <i class="fa-solid fa-circle-xmark me-1"></i> Akan Dilewati
                                </div>
                                <div class="card-body">
                                    <div class="d-flex justify-content-between mb-2">
                                        <span class="text-muted">Total Dilewati:</span>
                                        <span class="fw-bold fs-6 text-danger" id="prevSkippedTotal">0</span>
                                    </div>
                                    <div class="small text-muted mb-2">Rincian Alasan:</div>
                                    <ul class="list-group list-group-flush small" id="prevSkippedBreakdown">
                                        <!-- Rincian diisi via JS -->
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="alert alert-warning small mb-0">
                        <i class="fa-solid fa-triangle-exclamation me-1"></i>
                        Setiap perusahaan akan diproses dalam satu transaksi. Jika gagal, hanya data tersebut yang dibatalkan dan proses tetap berlanjut ke perusahaan berikutnya.
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-primary text-white" id="btnExecuteImportAll" onclick="executeImportAll()" disabled>
                    <i class="fa-solid fa-upload me-1"></i> Konfirmasi & Mulai Import Semua
                </button>
            </div>
        </div>
    </div>
</div>

<!-- MODAL HASIL IMPORT -->
<div class="modal fade" id="modalImportResult" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header bg-light">
                <h5 class="modal-title" id="modalImportResultTitle">
                    <i class="fa-solid fa-square-poll-vertical text-primary me-2"></i>Ringkasan Hasil Import
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="modalImportResultBody">
                <!-- Konten dinamis via JS -->
            </div>
            <div class="modal-footer" id="modalImportResultFooter">
                <button type="button" class="btn btn-primary text-white" onclick="closeImportResultAndRefresh()">
                    <i class="fa-solid fa-check me-1"></i> Selesai
                </button>
            </div>
        </div>
    </div>
</div>

<script>
// CSRF Security Variables
let csrfTokenName = '<?= csrf_token() ?>';
let csrfHash = '<?= csrf_hash() ?>';

function updateCsrf(data) {
    if (data && data.csrf_hash) {
        csrfHash = data.csrf_hash;
    }
}

// Select All & Bulk Action
function toggleSelectAll(master) {
    const checkboxes = document.querySelectorAll('.row-select');
    checkboxes.forEach(cb => cb.checked = master.checked);
    updateSelectedCount();
}

function updateSelectedCount() {
    const selected = document.querySelectorAll('.row-select:checked');
    const container = document.getElementById('bulkActionContainer');
    const countSpan = document.getElementById('selectedCount');
    if (selected.length > 0) {
        container.style.display = 'block';
        countSpan.textContent = selected.length;
    } else {
        container.style.display = 'none';
        countSpan.textContent = '0';
    }
}

function executeBulkDelete() {
    const selected = Array.from(document.querySelectorAll('.row-select:checked')).map(cb => cb.value);
    if (selected.length === 0) return;
    if (!confirm(`Apakah Anda yakin ingin menghapus ${selected.length} data perusahaan yang dipilih? Tindakan ini tidak dapat dibatalkan.`)) return;

    fetch('<?= base_url('admin/analyzer/perusahaan/bulk-delete') ?>', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: 'ids[]=' + selected.join('&ids[]=')
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert(data.message || 'Gagal menghapus data');
        }
    })
    .catch(err => alert('Terjadi kesalahan jaringan: ' + err.message));
}

// Show Company Detail Modal
function showCompanyDetail(id) {
    const modalEl = document.getElementById('modalCompanyDetail');
    const modal = new bootstrap.Modal(modalEl);
    const body = document.getElementById('modalCompanyBody');
    const title = document.getElementById('modalCompanyTitle');

    body.innerHTML = `
        <div class="text-center py-4">
            <div class="spinner-border text-primary" role="status"></div>
            <div class="mt-2 text-muted">Memuat data...</div>
        </div>
    `;
    modal.show();

    fetch('<?= base_url('admin/analyzer/perusahaan/detail') ?>/' + id)
    .then(r => r.json())
    .then(res => {
        if (!res.success) {
            body.innerHTML = `<div class="alert alert-danger">${res.error || 'Gagal memuat detail'}</div>`;
            return;
        }

        const comp = res.company;
        title.textContent = comp.nama_perusahaan;

        function isValidBrand(text) {
            if (!text) return false;
            text = text.trim();
            if (text.length === 0 || text.length > 25) return false;
            if (text.split(/\s+/).length > 3) return false;
            if (/[.!?]$/.test(text)) return false;
            if (isDescription(text)) return false;
            if (/\b(yang|untuk|dengan|adalah|sebagai|mewah|tahan|membantu|beraroma|aroma|berbahan|pria|wanita|hidangan|masakan|bergizi|dan|atau|of|for|with|is|in|on|at|biasa|digunakan|dijadikan|penambah|serpihan|lembaran)\b/i.test(text)) {
                return false;
            }
            return true;
        }

        function isDescription(text) {
            if (!text) return false;
            text = text.trim();
            if (/[.!?]$/.test(text) && text.length > 25) return true;
            if (/\b(yang\s+(?:biasa\s+)?(?:digunakan|dijadikan|membantu|berfungsi|dibungkus)|berbahan\s+dasar|untuk\s+(?:membuat|penambah|mencerahkan\s+wajah|pria\s+dan\s+wanita)|beraroma\s+tahan\s+lama|cita\s*rasa\s+gurih|makanan\s+ringan\s+berbahan|serpihan\s+rumput\s+laut|lembaran\s+rumput\s+laut|adalah\s+(?:sebuah|produk|salah\s+satu)|diformulasikan\s+untuk|cocok\s+untuk|berkhasiat\s+untuk|aroma\s+tahan\s+lama)\b/i.test(text)) {
                return true;
            }
            if (text.length > 75 && text.split(/\s+/).length > 8) return true;
            return false;
        }

        let productsHtml = '';
        if (comp.produk_yang_dijual_auto && comp.produk_yang_dijual_auto.trim().length > 0) {
            const lines = comp.produk_yang_dijual_auto.split('\n').filter(l => l.trim().length > 0);
            productsHtml = '<ol class="mb-0 ps-3">';
            lines.forEach(line => {
                let cleanLine = line.replace(/^\d+[\.\)]\s*/, '').trim();
                let parts = cleanLine.split(/\s+[–—\-]\s+/);
                let prodName = parts[0].trim();
                let brand = '';

                if (parts.length >= 3) {
                    if (isValidBrand(parts[2])) {
                        brand = parts[2].trim();
                    }
                    if (!isDescription(parts[1])) {
                        prodName = parts[1].trim();
                    }
                } else if (parts.length === 2) {
                    if (isValidBrand(parts[1])) {
                        brand = parts[1].trim();
                    }
                    // Jika parts[1] adalah kalimat deskripsi, abaikan sepenuhnya
                }

                // Jika prodName berupa deskripsi, bersihkan
                if (isDescription(prodName) && parts.length > 1 && !isDescription(parts[0])) {
                    prodName = parts[0].trim();
                }

                const brandBadge = brand ? ` <span class="badge bg-light text-primary border ms-1" style="font-size: 0.68rem; font-weight: normal;"><i class="fa-solid fa-tag me-1"></i>${escapeHtml(brand)}</span>` : '';
                productsHtml += `<li class="mb-1 text-dark"><strong>${escapeHtml(prodName)}</strong>${brandBadge}</li>`;
            });
            productsHtml += '</ol>';
        } else if (res.products && res.products.length > 0) {
            productsHtml = '<ol class="mb-0 ps-3">';
            res.products.forEach(p => {
                const id = (p.nama_produk_id || '').trim();
                const en = (p.nama_produk_en || '').trim();
                let text = (!isDescription(id) && id) ? id : en;
                const brand = (p.brand || '').trim();
                const brandBadge = isValidBrand(brand) 
                    ? ` <span class="badge bg-light text-primary border ms-1" style="font-size: 0.68rem; font-weight: normal;"><i class="fa-solid fa-tag me-1"></i>${escapeHtml(brand)}</span>` 
                    : '';
                productsHtml += `<li class="mb-1 text-dark"><strong>${escapeHtml(text)}</strong>${brandBadge}</li>`;
            });
            productsHtml += '</ol>';
        } else {
            productsHtml = '<span class="text-muted fst-italic">Belum ada produk yang dianalisis.</span>';
        }

        let sourcesHtml = '';
        if (res.sources && res.sources.length > 0) {
            sourcesHtml = '<ul class="list-group list-group-flush small">';
            res.sources.forEach(s => {
                const badge = s.status_pengambilan === 'sukses' ? '<span class="badge bg-success">Sukses</span>' : '<span class="badge bg-danger">Gagal</span>';
                sourcesHtml += `
                    <li class="list-group-item px-0">
                        <div class="d-flex justify-content-between">
                            <strong>${s.jenis_sumber.toUpperCase()}</strong>
                            ${badge}
                        </div>
                        ${s.url_sumber ? `<div class="text-truncate text-muted">${s.url_sumber}</div>` : ''}
                        ${s.pesan_error ? `<div class="text-danger">${s.pesan_error}</div>` : ''}
                    </li>
                `;
            });
            sourcesHtml += '</ul>';
        } else {
            sourcesHtml = '<span class="text-muted small">Belum ada riwayat pengambilan sumber.</span>';
        }

        body.innerHTML = `
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label text-muted small mb-1">Nomor Excel</label>
                    <div class="fw-semibold">${comp.nomor_excel || '-'}</div>
                </div>
                <div class="col-md-6">
                    <label class="form-label text-muted small mb-1">Status Analisis</label>
                    <div><span class="badge bg-primary">${comp.status_analisis}</span></div>
                </div>
                <div class="col-md-6">
                    <label class="form-label text-muted small mb-1">Kategori Utama</label>
                    <div>${comp.main_categories || '-'}</div>
                </div>
                <div class="col-md-6">
                    <label class="form-label text-muted small mb-1">Sub Kategori</label>
                    <div>${comp.sub_categories || '-'}</div>
                </div>
                <div class="col-12">
                    <label class="form-label text-muted small mb-1">Company Profile</label>
                    <div class="p-2 bg-light rounded small" style="max-height: 120px; overflow-y: auto;">
                        ${comp.company_profile || '<span class="text-muted fst-italic">Tidak ada company profile.</span>'}
                    </div>
                </div>
                <div class="col-md-6">
                    <label class="form-label text-muted small mb-1">Website</label>
                    <div>${comp.website ? `<a href="${comp.website.startsWith('http') ? comp.website : 'https://' + comp.website}" target="_blank">${comp.website}</a>` : '-'}</div>
                </div>
                <div class="col-md-6">
                    <label class="form-label text-muted small mb-1">Link Catalog</label>
                    <div>${comp.link_catalog ? `<a href="${comp.link_catalog}" target="_blank" class="text-truncate d-inline-block" style="max-width: 250px;">Buka Katalog</a>` : '-'}</div>
                </div>
                <div class="col-12 border-top pt-3">
                    <label class="form-label text-dark fw-bold mb-2">Hasil Produk Yang Dijual (Auto)</label>
                    ${productsHtml}
                </div>
                <div class="col-12 border-top pt-3">
                    <label class="form-label text-dark fw-bold mb-2">Status Sumber Informasi</label>
                    ${sourcesHtml}
                </div>
            </div>
        `;
    })
    .catch(err => {
        body.innerHTML = `<div class="alert alert-danger">Kesalahan jaringan: ${err.message}</div>`;
    });
}

// Single company quick analyze
function analyzeSingleCompany(id, name) {
    if (!confirm(`Jalankan analisis AI untuk perusahaan "${name}" sekarang?`)) return;

    // Arahkan ke halaman analisis dengan parameter single ID
    window.location.href = `<?= base_url('admin/analyzer/analisis') ?>?mode=single&id=` + id;
}

// Edit product manual modal
function openEditProductModal(id) {
    const modalEl = document.getElementById('modalEditProduct');
    const modal = new bootstrap.Modal(modalEl);
    const container = document.getElementById('productRowsContainer');
    const compIdInput = document.getElementById('editCompanyId');

    compIdInput.value = id;
    container.innerHTML = `<tr><td colspan="3" class="text-center py-3 text-muted">Memuat produk...</td></tr>`;
    modal.show();

    fetch('<?= base_url('admin/analyzer/produk/edit') ?>/' + id)
    .then(r => r.json())
    .then(res => {
        if (!res.success) {
            alert('Gagal memuat data produk');
            modal.hide();
            return;
        }

        document.getElementById('modalEditTitle').textContent = 'Edit Produk: ' + res.company.nama_perusahaan;
        container.innerHTML = '';

        if (res.products && res.products.length > 0) {
            res.products.forEach(p => {
                addProductRow(p.nama_produk_id, p.nama_produk_en);
            });
        } else {
            addProductRow('', '');
        }
    })
    .catch(err => alert('Terjadi kesalahan: ' + err.message));
}

function addProductRow(valId, valEn) {
    const container = document.getElementById('productRowsContainer');
    const tr = document.createElement('tr');
    tr.innerHTML = `
        <td><input type="text" name="product_id[]" class="form-control form-control-sm" value="${escapeHtml(valId)}" placeholder="Nama produk (Indonesia)"></td>
        <td><input type="text" name="product_en[]" class="form-control form-control-sm" value="${escapeHtml(valEn)}" placeholder="Product name (English)"></td>
        <td class="text-center">
            <button type="button" class="btn btn-sm btn-outline-danger" onclick="this.closest('tr').remove()"><i class="fa-solid fa-trash"></i></button>
        </td>
    `;
    container.appendChild(tr);
}

function saveProductEdits() {
    const id = document.getElementById('editCompanyId').value;
    const form = document.getElementById('formEditProduct');
    const formData = new FormData(form);
    const btn = document.getElementById('btnSaveProduct');

    btn.disabled = true;
    btn.innerHTML = `<span class="spinner-border spinner-border-sm me-1"></span> Menyimpan...`;

    fetch('<?= base_url('admin/analyzer/produk/save') ?>/' + id, {
        method: 'POST',
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: formData
    })
    .then(r => r.json())
    .then(data => {
        btn.disabled = false;
        btn.innerHTML = `<i class="fa-solid fa-floppy-disk me-1"></i> Simpan Koreksi`;

        if (data.success) {
            bootstrap.Modal.getInstance(document.getElementById('modalEditProduct')).hide();
            location.reload();
        } else {
            alert(data.error || 'Gagal menyimpan perubahan');
        }
    })
    .catch(err => {
        btn.disabled = false;
        btn.innerHTML = `<i class="fa-solid fa-floppy-disk me-1"></i> Simpan Koreksi`;
        alert('Kesalahan jaringan: ' + err.message);
    });
}

function escapeHtml(text) {
    if (text === null || text === undefined) return '';
    return String(text)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
}

// Helper aman untuk inisialisasi Modal Bootstrap 5
function getBootstrapModal(el) {
    if (!el) return null;
    if (typeof bootstrap !== 'undefined' && bootstrap && bootstrap.Modal) {
        if (typeof bootstrap.Modal.getOrCreateInstance === 'function') {
            return bootstrap.Modal.getOrCreateInstance(el);
        }
        return new bootstrap.Modal(el);
    }
    // Fallback vanilla JS jika bootstrap.Modal tidak tersedia
    return {
        show: function() {
            el.classList.add('show');
            el.style.display = 'block';
            document.body.classList.add('modal-open');
            let backdrop = document.querySelector('.modal-backdrop');
            if (!backdrop) {
                backdrop = document.createElement('div');
                backdrop.className = 'modal-backdrop fade show';
                document.body.appendChild(backdrop);
            }
        },
        hide: function() {
            el.classList.remove('show');
            el.style.display = 'none';
            document.body.classList.remove('modal-open');
            const backdrop = document.querySelector('.modal-backdrop');
            if (backdrop) backdrop.remove();
        }
    };
}

// Buka Modal Import Semua (Preview ringkasan sebelum import)
function openImportAllModal() {
    const modalEl = document.getElementById('modalImportAll');
    const modal = new bootstrap.Modal(modalEl);
    const loadingEl = document.getElementById('importAllLoading');
    const contentEl = document.getElementById('importAllContent');
    const btnExecute = document.getElementById('btnExecuteImportAll');

    loadingEl.style.display = 'block';
    contentEl.style.display = 'none';
    btnExecute.disabled = true;
    modal.show();

    fetch('<?= base_url('admin/analyzer/perusahaan/import-preview') ?>', {
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(r => {
        if (r.status === 401) {
            alert('Sesi admin telah berakhir. Silakan login kembali.');
            window.location.href = '<?= base_url('login') ?>';
            throw new Error('Unauthorized');
        }
        return r.json();
    })
    .then(res => {
        updateCsrf(res);
        if (!res.success) {
            alert(res.error || 'Gagal memuat preview data import.');
            modal.hide();
            return;
        }

        document.getElementById('prevEligibleCompany').textContent = res.akan_diimpor.perusahaan + ' Perusahaan';
        document.getElementById('prevEligibleProduct').textContent = res.akan_diimpor.produk + ' Produk';
        document.getElementById('prevNewSupplier').textContent = res.akan_diimpor.supplier_baru + ' Perusahaan';
        document.getElementById('prevExistingSupplier').textContent = res.akan_diimpor.supplier_sudah_ada + ' Perusahaan';
        document.getElementById('prevSkippedTotal').textContent = res.dilewati.total + ' Perusahaan';

        const breakdownList = document.getElementById('prevSkippedBreakdown');
        breakdownList.innerHTML = '';
        if (res.dilewati.breakdown && res.dilewati.breakdown.length > 0) {
            res.dilewati.breakdown.forEach(item => {
                const li = document.createElement('li');
                li.className = 'list-group-item d-flex justify-content-between align-items-center px-0 py-1';
                li.innerHTML = `<span>${item.label}</span><span class="badge bg-light text-dark border">${item.count}</span>`;
                breakdownList.appendChild(li);
            });
        }

        loadingEl.style.display = 'none';
        contentEl.style.display = 'block';
        if (res.akan_diimpor.perusahaan > 0) {
            btnExecute.disabled = false;
        } else {
            btnExecute.disabled = true;
            btnExecute.title = 'Tidak ada data valid yang dapat diimpor';
        }
    })
    .catch(err => {
        if (err.message !== 'Unauthorized') {
            alert('Terjadi kesalahan jaringan: ' + err.message);
            modal.hide();
        }
    });
}

// Jalankan Import Semua ke Supplier
function executeImportAll() {
    const btnExecute = document.getElementById('btnExecuteImportAll');
    if (!confirm('Apakah Anda yakin ingin memulai proses import keseluruhan ke Portal Supplier sekarang?')) return;

    btnExecute.disabled = true;
    btnExecute.innerHTML = `<span class="spinner-border spinner-border-sm me-1"></span> Mengimpor data... Harap tunggu...`;

    const formData = new FormData();
    formData.append(csrfTokenName, csrfHash);

    fetch('<?= base_url('admin/analyzer/perusahaan/import-all') ?>', {
        method: 'POST',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': csrfHash
        },
        body: formData
    })
    .then(r => {
        if (r.status === 401) {
            alert('Sesi admin telah berakhir. Silakan login kembali.');
            window.location.href = '<?= base_url('login') ?>';
            throw new Error('Unauthorized');
        }
        return r.json();
    })
    .then(res => {
        updateCsrf(res);
        btnExecute.disabled = false;
        btnExecute.innerHTML = `<i class="fa-solid fa-upload me-1"></i> Konfirmasi & Mulai Import Semua`;

        bootstrap.Modal.getInstance(document.getElementById('modalImportAll')).hide();

        if (!res.success) {
            alert(res.error || 'Gagal menjalankan import semua');
            return;
        }

        showImportResultModal('Ringkasan Hasil Import Semua', res.summary, false);
    })
    .catch(err => {
        btnExecute.disabled = false;
        btnExecute.innerHTML = `<i class="fa-solid fa-upload me-1"></i> Konfirmasi & Mulai Import Semua`;
        if (err.message !== 'Unauthorized') {
            alert('Terjadi kesalahan jaringan: ' + err.message);
        }
    });
}

// Delegated click handler untuk tombol Import ke Supplier
document.addEventListener('click', function(e) {
    const btn = e.target.closest('.btn-import-supplier');
    if (!btn) return;

    e.preventDefault();
    const id = btn.dataset.id;
    const name = btn.dataset.name;
    const status = btn.dataset.status;
    const productCount = parseInt(btn.dataset.products || '0', 10);

    if (productCount === 0 || !['sukses', 'manual_edit'].includes(status)) {
        alert(`Perusahaan "${name}" belum memiliki daftar produk hasil analisis AI yang valid.\n\nHarap klik tombol "Analisis Ulang AI" (ikon robot hijau) pada baris perusahaan ini terlebih dahulu untuk mendeteksi produknya.`);
        return;
    }

    importSingleSupplier(id, name, productCount);
});

// Import Manual Satu Perusahaan
function importSingleSupplier(id, name, productCount) {
    const countInfo = productCount ? ` (${productCount} produk)` : '';
    if (!confirm(`Import perusahaan "${name}"${countInfo} ke direktori Supplier sekarang?`)) return;

    const modalResultEl = document.getElementById('modalImportResult');
    const bodyResult = document.getElementById('modalImportResultBody');
    const titleResult = document.getElementById('modalImportResultTitle');
    const footerResult = document.getElementById('modalImportResultFooter');

    if (titleResult) {
        titleResult.innerHTML = `<i class="fa-solid fa-truck-ramp-box text-primary me-2"></i> Mengimpor: ${escapeHtml(name)}`;
    }
    if (bodyResult) {
        bodyResult.innerHTML = `
            <div class="text-center py-4">
                <div class="spinner-border text-primary" role="status"></div>
                <div class="mt-2 text-muted">Memproses import perusahaan dan produk ke database Supplier...</div>
            </div>
        `;
    }
    if (footerResult) {
        footerResult.innerHTML = `
            <button type="button" class="btn btn-secondary" disabled>
                <span class="spinner-border spinner-border-sm me-1"></span> Harap tunggu...
            </button>
        `;
    }

    try {
        const modalResult = getBootstrapModal(modalResultEl);
        if (modalResult) modalResult.show();
    } catch (e) {
        console.warn('Modal show warning:', e);
    }

    const formData = new FormData();
    formData.append(csrfTokenName, csrfHash);

    fetch('<?= base_url('admin/analyzer/perusahaan/import-single') ?>/' + id, {
        method: 'POST',
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: formData
    })
    .then(r => {
        if (r.status === 401) {
            alert('Sesi admin telah berakhir. Silakan login kembali.');
            window.location.href = '<?= base_url('login') ?>';
            throw new Error('Unauthorized');
        }
        return r.json();
    })
    .then(res => {
        updateCsrf(res);
        showImportResultModal(`Hasil Import: ${escapeHtml(name)}`, res, true);
    })
    .catch(err => {
        if (err.message !== 'Unauthorized') {
            if (bodyResult) {
                bodyResult.innerHTML = `<div class="alert alert-danger"><i class="fa-solid fa-circle-exclamation me-1"></i> Terjadi kesalahan jaringan: ${escapeHtml(err.message)}</div>`;
            } else {
                alert('Terjadi kesalahan: ' + err.message);
            }
            if (footerResult) {
                footerResult.innerHTML = `
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                `;
            }
        }
    });
}

// Tampilkan Modal Hasil Import
function showImportResultModal(title, data, isSingle) {
    const modalResultEl = document.getElementById('modalImportResult');
    const bodyResult = document.getElementById('modalImportResultBody');
    const titleResult = document.getElementById('modalImportResultTitle');
    const footerResult = document.getElementById('modalImportResultFooter');

    if (titleResult) {
        titleResult.innerHTML = `<i class="fa-solid fa-square-poll-vertical text-success me-2"></i> ${title}`;
    }

    if (isSingle) {
        if (data.success) {
            const supplierBadge = data.is_new_supplier
                ? '<span class="badge bg-success fs-6"><i class="fa-solid fa-plus-circle me-1"></i>Supplier Baru Dibuat</span>'
                : '<span class="badge bg-info text-dark fs-6"><i class="fa-solid fa-link me-1"></i>Supplier Sudah Terdaftar (Data Dipertahankan)</span>';

            bodyResult.innerHTML = `
                <div class="alert alert-success d-flex align-items-center mb-3">
                    <i class="fa-solid fa-circle-check fa-xl me-2"></i>
                    <div>${escapeHtml(data.message)}</div>
                </div>

                <div class="card shadow-sm mb-3">
                    <div class="card-body">
                        <div class="mb-3 d-flex justify-content-between align-items-center">
                            <span class="text-muted fw-semibold">Status Supplier:</span>
                            <div>${supplierBadge}</div>
                        </div>
                        <div class="row g-2 text-center">
                            <div class="col-6">
                                <div class="p-3 bg-light rounded border">
                                    <div class="text-muted small">Produk Ditambahkan</div>
                                    <div class="fs-4 fw-bold text-success">${data.products_added}</div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="p-3 bg-light rounded border">
                                    <div class="text-muted small">Produk Dilewati (Sudah Ada)</div>
                                    <div class="fs-4 fw-bold text-secondary">${data.products_skipped}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            `;
            if (footerResult) {
                footerResult.innerHTML = `
                    <a href="<?= base_url('admin/produk_sales/index') ?>" class="btn btn-primary text-white">
                        <i class="fa-solid fa-arrow-up-right-from-square me-1"></i> Buka Halaman Produk Supplier
                    </a>
                    <button type="button" class="btn btn-secondary" onclick="closeImportResultAndRefresh()">
                        <i class="fa-solid fa-check me-1"></i> Selesai & Refresh
                    </button>
                `;
            }
        } else if (data.is_skipped) {
            bodyResult.innerHTML = `
                <div class="alert alert-warning mb-3">
                    <h6 class="alert-heading fw-bold"><i class="fa-solid fa-triangle-exclamation me-1"></i> Perusahaan Dilewati</h6>
                    <div>${escapeHtml(data.reason || data.message)}</div>
                </div>
                <div class="text-muted small">
                    Hanya perusahaan yang memiliki status analisis <strong>Sukses</strong> atau <strong>Koreksi Manual</strong> dan memiliki minimal 1 produk terdeteksi yang dapat diimpor ke Portal Supplier.
                </div>
            `;
            if (footerResult) {
                footerResult.innerHTML = `
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fa-solid fa-xmark me-1"></i> Tutup
                    </button>
                `;
            }
        } else {
            bodyResult.innerHTML = `
                <div class="alert alert-danger mb-3">
                    <h6 class="alert-heading fw-bold"><i class="fa-solid fa-circle-xmark me-1"></i> Import Gagal</h6>
                    <div>${escapeHtml(data.message || data.error || 'Terjadi kesalahan.')}</div>
                    ${data.error ? `<div class="mt-2 text-monospace small bg-white p-2 rounded border text-danger">Pesan Error: ${escapeHtml(data.error)}</div>` : ''}
                </div>
            `;
            if (footerResult) {
                footerResult.innerHTML = `
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fa-solid fa-xmark me-1"></i> Tutup
                    </button>
                `;
            }
        }
    } else {
        // Bulk import summary
        let skippedListHtml = '';
        if (data.dilewati && data.dilewati.length > 0) {
            skippedListHtml = `
                <div class="mt-3">
                    <h6 class="fw-bold text-secondary"><i class="fa-solid fa-triangle-exclamation me-1"></i> Daftar Perusahaan Dilewati (${data.dilewati.length}):</h6>
                    <div class="table-responsive" style="max-height: 180px; overflow-y: auto;">
                        <table class="table table-sm table-bordered mb-0 small">
                            <thead class="table-light">
                                <tr><th>Perusahaan</th><th>Alasan</th></tr>
                            </thead>
                            <tbody>
                                ${data.dilewati.map(d => `<tr><td>${escapeHtml(d.nama)}</td><td class="text-muted">${escapeHtml(d.alasan)}</td></tr>`).join('')}
                            </tbody>
                        </table>
                    </div>
                </div>
            `;
        }

        let failedListHtml = '';
        if (data.gagal && data.gagal.length > 0) {
            failedListHtml = `
                <div class="mt-3">
                    <h6 class="fw-bold text-danger"><i class="fa-solid fa-circle-xmark me-1"></i> Daftar Perusahaan Gagal (${data.gagal.length}):</h6>
                    <div class="table-responsive" style="max-height: 180px; overflow-y: auto;">
                        <table class="table table-danger">
                            <thead class="table-danger">
                                <tr><th>Perusahaan</th><th>Pesan Error Asli</th></tr>
                            </thead>
                            <tbody>
                                ${data.gagal.map(g => `<tr><td>${escapeHtml(g.nama)}</td><td class="text-danger">${escapeHtml(g.pesan)}</td></tr>`).join('')}
                            </tbody>
                        </table>
                    </div>
                </div>
            `;
        }

        bodyResult.innerHTML = `
            <div class="row g-2 mb-3 text-center">
                <div class="col-md-3 col-6">
                    <div class="p-2 border rounded bg-light">
                        <div class="text-muted small">Supplier Baru</div>
                        <div class="fs-4 fw-bold text-success">${data.supplier_baru}</div>
                    </div>
                </div>
                <div class="col-md-3 col-6">
                    <div class="p-2 border rounded bg-light">
                        <div class="text-muted small">Supplier Terhubung</div>
                        <div class="fs-4 fw-bold text-info">${data.supplier_sudah_ada}</div>
                    </div>
                </div>
                <div class="col-md-3 col-6">
                    <div class="p-2 border rounded bg-light">
                        <div class="text-muted small">Produk Ditambahkan</div>
                        <div class="fs-4 fw-bold text-primary">${data.produk_ditambahkan}</div>
                    </div>
                </div>
                <div class="col-md-3 col-6">
                    <div class="p-2 border rounded bg-light">
                        <div class="text-muted small">Produk Dilewati</div>
                        <div class="fs-4 fw-bold text-secondary">${data.produk_dilewati}</div>
                    </div>
                </div>
            </div>

            ${skippedListHtml}
            ${failedListHtml}
        `;

        if (footerResult) {
            footerResult.innerHTML = `
                <a href="<?= base_url('admin/produk_sales/index') ?>" class="btn btn-primary text-white">
                    <i class="fa-solid fa-arrow-up-right-from-square me-1"></i> Buka Halaman Produk Supplier
                </a>
                <button type="button" class="btn btn-secondary" onclick="closeImportResultAndRefresh()">
                    <i class="fa-solid fa-check me-1"></i> Selesai & Refresh
                </button>
            `;
        }
    }

    try {
        const modalResult = getBootstrapModal(modalResultEl);
        if (modalResult) modalResult.show();
    } catch (e) {
        console.warn('Modal show warning:', e);
    }
}

function closeImportResultAndRefresh() {
    const modalEl = document.getElementById('modalImportResult');
    if (modalEl) {
        try {
            const instance = getBootstrapModal(modalEl);
            if (instance) instance.hide();
        } catch (e) {
            modalEl.classList.remove('show');
            modalEl.style.display = 'none';
        }
    }
    location.reload();
}
</script>

<?= $this->endSection(); ?>

