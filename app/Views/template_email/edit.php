<?= $this->extend('layouts/main') ?>

<?= $this->section('title') ?>Edit Template Email<?= $this->endSection() ?>
<?= $this->section('page_title') ?>Edit Template Email<?= $this->endSection() ?>
<?= $this->section('page_subtitle') ?>Formulir Edit Data Template Email<?= $this->endSection() ?>

<?= $this->section('breadcrumb') ?>
<li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
<li><a href="<?= base_url('template-email') ?>">Template Email</a></li>
<li class="active">Edit</li>
<?= $this->endSection() ?>

<?= $this->section('styles') ?>
<style>
    .placeholder-box {
        background: #f9f9f9;
        border: 1px dashed #ccc;
        border-radius: 6px;
        padding: 15px;
        margin-top: 10px;
    }
    .placeholder-box .box-title {
        font-weight: 600;
        color: #555;
        margin-bottom: 10px;
        font-size: 13px;
    }
    .placeholder-badge {
        display: inline-block;
        background: #ffffff;
        border: 1px solid #f39c12;
        color: #f39c12;
        padding: 6px 12px;
        border-radius: 4px;
        font-family: 'Courier New', monospace;
        font-size: 13px;
        font-weight: 600;
        cursor: pointer;
        margin: 4px;
        transition: all 0.2s ease;
        user-select: none;
    }
    .placeholder-badge:hover {
        background: #f39c12;
        color: #ffffff;
        transform: translateY(-2px);
        box-shadow: 0 3px 6px rgba(243, 156, 18, 0.3);
    }
    .placeholder-badge:active {
        transform: translateY(0);
    }
    .placeholder-desc {
        font-size: 12px;
        color: #888;
        margin-top: 10px;
        padding-top: 10px;
        border-top: 1px solid #e0e0e0;
    }
    .placeholder-desc li {
        margin-bottom: 4px;
    }
    .placeholder-desc code {
        background: #f0f0f0;
        padding: 2px 6px;
        border-radius: 3px;
        color: #c7254e;
    }
    .toast-notif {
        position: fixed;
        bottom: 30px;
        right: 30px;
        background: #28a745;
        color: white;
        padding: 12px 20px;
        border-radius: 6px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.2);
        opacity: 0;
        transform: translateY(20px);
        transition: all 0.3s ease;
        z-index: 9999;
        font-size: 14px;
    }
    .toast-notif.show {
        opacity: 1;
        transform: translateY(0);
    }

    /* ============ SPAM CHECK STYLE ============ */
    .spam-check-wrapper {
        margin-top: 15px;
        padding-top: 15px;
        border-top: 1px dashed #ddd;
    }
    .spam-result {
        padding: 20px;
        border-radius: 8px;
        margin-top: 10px;
        animation: fadeInSpam 0.3s ease;
    }
    .spam-result.spam {
        background: #fdecea;
        border-left: 5px solid #e74c3c;
    }
    .spam-result.inbox {
        background: #eafaf1;
        border-left: 5px solid #27ae60;
    }
    .spam-result.error {
        background: #fef5e7;
        border-left: 5px solid #f39c12;
    }
    .spam-result h4 {
        margin: 0 0 10px 0;
        font-size: 18px;
    }
    .spam-result.spam h4 { color: #c0392b; }
    .spam-result.inbox h4 { color: #1e8449; }
    .spam-result.error h4 { color: #b9770e; }
    .spam-result .alasan-list {
        margin: 10px 0 0 0;
        padding-left: 20px;
    }
    .spam-result .alasan-list li {
        margin-bottom: 6px;
        color: #555;
    }
    .spam-loading {
        display: inline-block;
        animation: spinSpam 1s linear infinite;
    }
    @keyframes fadeInSpam {
        from { opacity: 0; transform: translateY(-10px); }
        to   { opacity: 1; transform: translateY(0); }
    }
    @keyframes spinSpam {
        from { transform: rotate(0deg); }
        to   { transform: rotate(360deg); }
    }
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<div class="row">
    <div class="col-md-12">

        <div class="box box-warning">
            <div class="box-header with-border">
                <h3 class="box-title"><i class="fa fa-edit"></i> Formulir Edit Template Email</h3>
            </div>

            <form action="<?= base_url('template-email/update/' . $template['id']) ?>" method="post" id="formEdit">
                <?= csrf_field() ?>
                <div class="box-body">

                    <!-- Error Validasi -->
                    <?php if (session()->getFlashdata('errors')) : ?>
                        <div class="alert alert-danger">
                            <i class="icon fa fa-ban"></i> Terdapat kesalahan:
                            <ul>
                                <?php foreach (session()->getFlashdata('errors') as $error) : ?>
                                    <li><?= esc($error) ?></li>
                                <?php endforeach ?>
                            </ul>
                        </div>
                    <?php endif ?>

                    <div class="form-group">
                        <label>Nama Template <span class="text-danger">*</span></label>
                        <input type="text" name="nama_template" class="form-control"
                               value="<?= old('nama_template', esc($template['nama_template'])) ?>"
                               placeholder="Masukkan nama template" maxlength="150" required>
                    </div>

                    <div class="form-group">
                        <label>Kategori Template <span class="text-danger">*</span></label>
                        <select name="kategori_template_id" class="form-control" required>
                            <option value="">-- Pilih Kategori --</option>
                            <?php foreach ($kategori as $k) : ?>
                                <option value="<?= $k['id'] ?>"
                                    <?= old('kategori_template_id', $template['kategori_template_id']) == $k['id'] ? 'selected' : '' ?>>
                                    <?= esc($k['nama_kategori']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Subjek Email <span class="text-danger">*</span></label>
                        <input type="text" name="subjek_email" class="form-control"
                               value="<?= old('subjek_email', esc($template['subjek_email'])) ?>"
                               placeholder="Masukkan subjek email" maxlength="255" required>
                    </div>

                    <!-- ISI HTML -->
                    <div class="form-group">
                        <label>Isi HTML <span class="text-danger">*</span></label>
                        <textarea name="isi_html" id="isiHtml" class="form-control" rows="10"
                                  placeholder="Tulis kode HTML template di sini..." required><?= old('isi_html', esc($template['isi_html'])) ?></textarea>

                        <!-- PLACEHOLDER BOX -->
                        <div class="placeholder-box">
                            <div class="box-title">
                                💡 Placeholder Tersedia <small class="text-muted">(klik untuk menyalin)</small>
                            </div>

                            <div>
                                <span class="placeholder-badge" 
                                      data-placeholder="{{nama}}"
                                      title="Nama lengkap penerima">
                                    {{nama}}
                                </span>
                                <span class="placeholder-badge" 
                                      data-placeholder="{{nama_perusahaan}}"
                                      title="Nama perusahaan penerima">
                                    {{nama_perusahaan}}
                                </span>
                                <span class="placeholder-badge" 
                                      data-placeholder="{{no_hp}}"
                                      title="Nomor HP penerima">
                                    {{no_hp}}
                                </span>
                                <span class="placeholder-badge" 
                                      data-placeholder="{{email}}"
                                      title="Email penerima">
                                    {{email}}
                                </span>
                            </div>

                            <div class="placeholder-desc">
                                <strong>Keterangan:</strong>
                                <ul class="list-unstyled" style="margin-top: 8px; margin-bottom: 0;">
                                    <li><code>{{nama}}</code> → Nama lengkap penerima</li>
                                    <li><code>{{nama_perusahaan}}</code> → Nama perusahaan penerima</li>
                                    <li><code>{{no_hp}}</code> → Nomor HP penerima</li>
                                    <li><code>{{email}}</code> → Email penerima</li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Isi Teks <small class="text-muted">(opsional — fallback untuk email client yang tidak support HTML)</small></label>
                        <textarea name="isi_teks" class="form-control" rows="4"
                                  placeholder="Masukkan isi teks (opsional)"><?= old('isi_teks', esc($template['isi_teks'])) ?></textarea>
                    </div>

                    <!-- TOMBOL CEK SPAM (POSISI BARU) -->
                    <div class="spam-check-wrapper">
                        <button type="button" class="btn btn-info" id="btnCekSpam">
                            <i class="fa fa-search"></i> Cek Spam (AI)
                        </button>
                        <small class="text-muted" style="margin-left: 10px;">
                            Analisis apakah email ini masuk <strong>Spam</strong> atau <strong>Inbox</strong>
                        </small>
                    </div>

                    <!-- AREA HASIL CEK SPAM (POSISI BARU) -->
                    <div id="hasilCekSpam" style="display:none;"></div>

                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Status</label>
                                <select name="status" class="form-control">
                                    <option value="draf" <?= old('status', $template['status']) === 'draf' ? 'selected' : '' ?>>Draf</option>
                                    <option value="aktif" <?= old('status', $template['status']) === 'aktif' ? 'selected' : '' ?>>Aktif</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Versi</label>
                                <input type="number" name="versi" class="form-control"
                                       value="<?= old('versi', esc($template['versi'])) ?>" min="1">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Status Pemeriksaan</label>
                                <select name="status_pemeriksaan" class="form-control">
                                    <option value="belum_diperiksa" <?= old('status_pemeriksaan', $template['status_pemeriksaan']) === 'belum_diperiksa' ? 'selected' : '' ?>>Belum Diperiksa</option>
                                    <option value="sudah_diperiksa" <?= old('status_pemeriksaan', $template['status_pemeriksaan']) === 'sudah_diperiksa' ? 'selected' : '' ?>>Sudah Diperiksa</option>
                                    <option value="perlu_perbaikan" <?= old('status_pemeriksaan', $template['status_pemeriksaan']) === 'perlu_perbaikan' ? 'selected' : '' ?>>Perlu Perbaikan</option>
                                </select>
                            </div>
                        </div>
                    </div>

                </div>

                <div class="box-footer">
                    <a href="<?= base_url('template-email') ?>" class="btn btn-default">
                        <i class="fa fa-arrow-left"></i> Kembali
                    </a>
                    <button type="submit" class="btn btn-warning pull-right">
                        <i class="fa fa-save"></i> Update
                    </button>
                </div>
            </form>
        </div>

    </div>
</div>

<!-- Toast Notifikasi -->
<div class="toast-notif" id="toastNotif">✅ Placeholder tersalin!</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
$(document).ready(function() {

    // ==========================================
    // KLIK PLACEHOLDER → COPY KE CLIPBOARD
    // ==========================================
    $(document).on('click', '.placeholder-badge', function() {
        const placeholder = $(this).data('placeholder');
        const $textarea = $('#isiHtml');

        if (navigator.clipboard && navigator.clipboard.writeText) {
            navigator.clipboard.writeText(placeholder).then(function() {
                showToast('✅ "' + placeholder + '" tersalin ke clipboard!');
            }).catch(function() {
                fallbackCopy($textarea, placeholder);
            });
        } else {
            fallbackCopy($textarea, placeholder);
        }
    });

    function fallbackCopy($textarea, placeholder) {
        const textarea = $textarea[0];
        const start = textarea.selectionStart;
        const end = textarea.selectionEnd;
        const currentValue = textarea.value;

        textarea.value = currentValue.substring(0, start) + placeholder + currentValue.substring(end);
        textarea.selectionStart = textarea.selectionEnd = start + placeholder.length;
        textarea.focus();

        showToast('✅ "' + placeholder + '" dimasukkan ke editor!');
    }

    function showToast(message) {
        const $toast = $('#toastNotif');
        $toast.text(message).addClass('show');
        setTimeout(function() {
            $toast.removeClass('show');
        }, 2000);
    }

    // ==========================================
    // CEK SPAM AI
    // ==========================================
    $('#btnCekSpam').on('click', function() {
        const $btn = $(this);
        const subjek  = $('input[name="subjek_email"]').val();
        const isiHtml = $('textarea[name="isi_html"]').val();
        const isiTeks = $('textarea[name="isi_teks"]').val();
        const $hasil  = $('#hasilCekSpam');

        if (!subjek || !isiHtml) {
            Swal.fire({
                icon: 'warning',
                title: 'Belum Lengkap',
                text: 'Isi Subjek Email dan Isi HTML terlebih dahulu sebelum cek spam.',
            });
            return;
        }

        $btn.prop('disabled', true).html('<i class="fa fa-spinner spam-loading"></i> Menganalisis...');
        $hasil.hide().empty();

        $.ajax({
            url: '<?= base_url("template-email/cek-spam") ?>',
            method: 'POST',
            data: {
                '<?= csrf_token() ?>': '<?= csrf_hash() ?>',
                subjek_email: subjek,
                isi_html: isiHtml,
                isi_teks: isiTeks,
            },
            dataType: 'json',
            success: function(res) {
                $btn.prop('disabled', false).html('<i class="fa fa-search"></i> Cek Spam (AI)');

                if (!res.success) {
                    $hasil.show().html(
                        '<div class="spam-result error">' +
                        '<h4><i class="fa fa-exclamation-triangle"></i> Gagal Menganalisis</h4>' +
                        '<p style="margin:0;">' + (res.message || 'Terjadi kesalahan.') + '</p>' +
                        '</div>'
                    );
                    return;
                }

                if (res.status === 'spam') {
                    let alasanHtml = '';
                    if (res.alasan && res.alasan.length > 0) {
                        alasanHtml = '<ul class="alasan-list">';
                        res.alasan.forEach(function(a) {
                            alasanHtml += '<li>' + a + '</li>';
                        });
                        alasanHtml += '</ul>';
                    }
                    $hasil.show().html(
                        '<div class="spam-result spam">' +
                        '<h4><i class="fa fa-times-circle"></i> Berisiko Masuk SPAM</h4>' +
                        '<p style="margin:0 0 5px 0;">Email ini kemungkinan masuk ke folder <strong>Spam</strong> penerima.</p>' +
                        '<p style="margin:8px 0 0 0; font-weight:600;">Alasan:</p>' +
                        alasanHtml +
                        '</div>'
                    );
                } else {
                    $hasil.show().html(
                        '<div class="spam-result inbox">' +
                        '<h4><i class="fa fa-check-circle"></i> Aman — Masuk INBOX</h4>' +
                        '<p style="margin:0;">Email ini kemungkinan masuk ke <strong>Inbox</strong> penerima. Tidak ada masalah signifikan yang terdeteksi.</p>' +
                        '</div>'
                    );
                }
            },
            error: function(xhr) {
                $btn.prop('disabled', false).html('<i class="fa fa-search"></i> Cek Spam (AI)');
                $hasil.show().html(
                    '<div class="spam-result error">' +
                    '<h4><i class="fa fa-exclamation-triangle"></i> Gagal Menganalisis</h4>' +
                    '<p style="margin:0;">Terjadi kesalahan pada server: ' + (xhr.statusText || 'Unknown error') + '</p>' +
                    '</div>'
                );
            }
        });
    });

});
</script>
<?= $this->endSection() ?>