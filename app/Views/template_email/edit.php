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
<link href="https://cdn.jsdelivr.net/npm/summernote@0.8.20/dist/summernote-bs4.min.css" rel="stylesheet">
<style>
    .placeholder-box { background: #f9f9f9; border: 1px dashed #ccc; border-radius: 6px; padding: 15px; margin-top: 10px; }
    .placeholder-box .box-title { font-weight: 600; color: #555; margin-bottom: 10px; font-size: 13px; }
    .placeholder-badge { display: inline-block; background: #ffffff; border: 1px solid #f39c12; color: #f39c12; padding: 6px 12px; border-radius: 4px; font-family: 'Courier New', monospace; font-size: 13px; font-weight: 600; cursor: pointer; margin: 4px; transition: all 0.2s ease; user-select: none; }
    .placeholder-badge:hover { background: #f39c12; color: #ffffff; transform: translateY(-2px); box-shadow: 0 3px 6px rgba(243, 156, 18, 0.3); }
    .placeholder-desc { font-size: 12px; color: #888; margin-top: 10px; padding-top: 10px; border-top: 1px solid #e0e0e0; }
    .placeholder-desc code { background: #f0f0f0; padding: 2px 6px; border-radius: 3px; color: #c7254e; }
    .toast-notif { position: fixed; bottom: 30px; right: 30px; background: #28a745; color: white; padding: 12px 20px; border-radius: 6px; box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2); opacity: 0; transform: translateY(20px); transition: all 0.3s ease; z-index: 9999; font-size: 14px; }
    .toast-notif.show { opacity: 1; transform: translateY(0); }
    .spam-check-wrapper { margin-top: 15px; padding-top: 15px; border-top: 1px dashed #ddd; }
    .spam-result { padding: 20px; border-radius: 8px; margin-top: 10px; animation: fadeInSpam 0.3s ease; }
    .spam-result.spam { background: #fdecea; border-left: 5px solid #e74c3c; }
    .spam-result.inbox { background: #eafaf1; border-left: 5px solid #27ae60; }
    .spam-result.error { background: #fef5e7; border-left: 5px solid #f39c12; }
    .spam-result h4 { margin: 0 0 10px 0; font-size: 18px; }
    .spam-result.spam h4 { color: #c0392b; }
    .spam-result.inbox h4 { color: #1e8449; }
    .spam-result.error h4 { color: #b9770e; }
    .spam-result .alasan-list { margin: 10px 0 0 0; padding-left: 20px; }
    .spam-result .alasan-list li { margin-bottom: 6px; color: #555; }
    .spam-loading { display: inline-block; animation: spinSpam 1s linear infinite; }
    @keyframes fadeInSpam { from { opacity: 0; transform: translateY(-10px); } to { opacity: 1; transform: translateY(0); } }
    @keyframes spinSpam { from { transform: rotate(0deg); } to { transform: rotate(360deg); } }
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
                    <?php if (session()->getFlashdata('errors')) : ?>
                        <div class="alert alert-danger"><i class="icon fa fa-ban"></i> Terdapat kesalahan:<ul><?php foreach (session()->getFlashdata('errors') as $error) : ?><li><?= esc($error) ?></li><?php endforeach ?></ul></div>
                    <?php endif ?>

                    <div class="form-group"><label>Nama Template <span class="text-danger">*</span></label><input type="text" name="nama_template" class="form-control" value="<?= old('nama_template', esc($template['nama_template'])) ?>" placeholder="Masukkan nama template" maxlength="150" required></div>
                    <div class="form-group"><label>Kategori Template <span class="text-danger">*</span></label><select name="kategori_template_id" class="form-control" required><option value="">-- Pilih Kategori --</option><?php foreach ($kategori as $k) : ?><option value="<?= $k['id'] ?>" <?= old('kategori_template_id', $template['kategori_template_id']) == $k['id'] ? 'selected' : '' ?>><?= esc($k['nama_kategori']) ?></option><?php endforeach; ?></select></div>
                    <div class="form-group"><label>Subjek Email <span class="text-danger">*</span></label><input type="text" name="subjek_email" class="form-control" value="<?= old('subjek_email', esc($template['subjek_email'])) ?>" placeholder="Masukkan subjek email" maxlength="255" required></div>

                    <!-- EDITOR SUMMERNOTE (Pakai data-html) -->
                    <div class="form-group">
                        <label>Desain Email <span class="text-danger">*</span></label>
                        <div id="editorIsiHtml" data-html="<?= old('isi_html', esc($template['isi_html'])) ?>" style="background-color: white;"></div>
                        <input type="hidden" name="isi_html" id="isiHtml" value="<?= old('isi_html', esc($template['isi_html'])) ?>" required>

                        <div class="placeholder-box">
                            <div class="box-title">💡 Placeholder Tersedia <small class="text-muted">(klik untuk memasukkan)</small></div>
                            <div>
                                <span class="placeholder-badge" data-placeholder="{{nama}}" title="Nama lengkap penerima">{{nama}}</span>
                                <span class="placeholder-badge" data-placeholder="{{nama_perusahaan}}" title="Nama perusahaan penerima">{{nama_perusahaan}}</span>
                                <span class="placeholder-badge" data-placeholder="{{no_hp}}" title="Nomor HP penerima">{{no_hp}}</span>
                                <span class="placeholder-badge" data-placeholder="{{email}}" title="Email penerima">{{email}}</span>
                            </div>
                        </div>
                    </div>

                    <!-- CARD KHUSUS PDF -->
                    <div class="box box-default" style="border-top: 3px solid #f39c12; margin-top: 20px;">
                        <div class="box-header with-border"><h3 class="box-title"><i class="fa fa-file-pdf-o"></i> Pengaturan Dokumen PDF (Opsional)</h3></div>
                        <div class="box-body">
                            <div class="row">
                                <div class="col-md-5">
                                    <div class="form-group">
                                        <label>Upload Dokumen PDF</label>
                                        <input type="file" id="uploadPdfFile" class="form-control" accept="application/pdf">
                                        <small class="text-muted">Ukuran maksimal 5MB.</small>
                                    </div>
                                </div>
                                <div class="col-md-7">
                                    <div class="form-group">
                                        <label>Cara Menampilkan PDF di Email:</label>
                                        <div class="radio"><label><input type="radio" name="tipe_pdf" value="link" checked> <strong>Sisipkan sebagai Link</strong> (Teks di dalam desain)</label></div>
                                        <div class="radio"><label><input type="radio" name="tipe_pdf" value="attachment"> <strong>Sebagai Lampiran (Attachment)</strong> (Menempel di bawah)</label></div>
                                    </div>
                                </div>
                            </div>
                            <div id="pdfResultContainer" style="display: none; margin-top: 15px; padding: 15px; background: #f9f9f9; border: 1px dashed #ccc; border-radius: 5px;">
                                <p style="margin: 0 0 10px 0;"><strong>File PDF Terpilih:</strong> <a href="#" id="linkPdfResult" target="_blank" class="text-success">[Cek File PDF]</a></p>
                                <button type="button" class="btn btn-sm btn-primary" id="btnInsertPdfText"><i class="fa fa-plus"></i> Sisipkan PDF ke Body Email</button>
                                <button type="button" class="btn btn-sm btn-danger" id="btnResetPdf"><i class="fa fa-trash"></i> Batal</button>
                            </div>
                            <input type="hidden" name="attachment_pdf_url" id="attachmentPdfUrl" value="">
                        </div>
                    </div>

                    <div class="form-group" style="margin-top: 20px;">
                        <label>Isi Teks <small class="text-muted">(opsional — fallback)</small></label>
                        <textarea name="isi_teks" class="form-control" rows="4" placeholder="Masukkan isi teks (opsional)"><?= old('isi_teks', esc($template['isi_teks'])) ?></textarea>
                    </div>

                    <!-- TOMBOL PREVIEW & SPAM -->
                    <div class="spam-check-wrapper">
                        <button type="button" class="btn btn-default" id="btnPreview"><i class="fa fa-eye"></i> Preview Email</button>
                        <button type="button" class="btn btn-info" id="btnCekSpam" style="margin-left: 5px;"><i class="fa fa-search"></i> Cek Spam (AI)</button>
                    </div>
                    <div id="hasilCekSpam" style="display:none;"></div>

                    <div class="row">
                        <div class="col-md-4"><div class="form-group"><label>Status</label><select name="status" class="form-control"><option value="draf" <?= old('status', $template['status']) === 'draf' ? 'selected' : '' ?>>Draf</option><option value="aktif" <?= old('status', $template['status']) === 'aktif' ? 'selected' : '' ?>>Aktif</option></select></div></div>
                        <div class="col-md-4"><div class="form-group"><label>Versi</label><input type="number" name="versi" class="form-control" value="<?= old('versi', esc($template['versi'])) ?>" min="1"></div></div>
                        <div class="col-md-4"><div class="form-group"><label>Status Pemeriksaan</label><select name="status_pemeriksaan" class="form-control"><option value="belum_diperiksa" <?= old('status_pemeriksaan', $template['status_pemeriksaan']) === 'belum_diperiksa' ? 'selected' : '' ?>>Belum Diperiksa</option><option value="sudah_diperiksa" <?= old('status_pemeriksaan', $template['status_pemeriksaan']) === 'sudah_diperiksa' ? 'selected' : '' ?>>Sudah Diperiksa</option><option value="perlu_perbaikan" <?= old('status_pemeriksaan', $template['status_pemeriksaan']) === 'perlu_perbaikan' ? 'selected' : '' ?>>Perlu Perbaikan</option></select></div></div>
                    </div>
                </div>

                <div class="box-footer">
                    <a href="<?= base_url('template-email') ?>" class="btn btn-default"><i class="fa fa-arrow-left"></i> Kembali</a>
                    <button type="submit" class="btn btn-warning pull-right"><i class="fa fa-save"></i> Update</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- MODAL PREVIEW -->
<div class="modal fade" id="modalPreview" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title"><i class="fa fa-envelope"></i> Preview Email</h4>
            </div>
            <div class="modal-body" style="background-color: #f0f2f5; padding: 20px;">
                <div style="max-width: 600px; margin: 0 auto; background: white; border: 1px solid #ddd; box-shadow: 0 2px 5px rgba(0,0,0,0.1); border-radius: 8px; overflow: hidden;">
                    <div style="padding: 15px 20px; border-bottom: 1px solid #eee; background: #f9f9f9;">
                        <p style="margin: 0 0 5px 0; font-size: 14px; color: #333;"><strong>Subjek:</strong> <span id="previewSubjek">-</span></p>
                        <p style="margin: 0; font-size: 12px; color: #888;">Dari: Pengirim Email &lt;pengirim@domain.com&gt;</p>
                    </div>
                    <div id="previewBody" style="padding: 20px; min-height: 200px;"></div>
                    <div id="previewAttachment" style="display: none; padding: 15px 20px; border-top: 1px solid #eee; background: #f9f9f9;">
                        <p style="margin: 0; font-size: 13px; color: #555;"><i class="fa fa-paperclip"></i> <strong>1 Lampiran:</strong></p>
                        <a href="#" id="previewAttachmentUrl" target="_blank" style="font-size: 13px; color: #0056b3; text-decoration: underline;">Dokumen_PDF.pdf</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="toast-notif" id="toastNotif">✅ Placeholder tersalin!</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdn.jsdelivr.net/npm/summernote@0.8.20/dist/summernote-bs4.min.js"></script>
<script>
    $(document).ready(function() {
        let isiHtmlAwal = $('#editorIsiHtml').data('html');

        // 1. INISIALISASI SUMMERNOTE
        $('#editorIsiHtml').summernote({
            placeholder: 'Desain isi email di sini...',
            tabsize: 2,
            height: 300,
            toolbar: [
                ['style', ['style']],
                ['font', ['bold', 'underline', 'italic', 'clear']],
                ['color', ['color']],
                ['para', ['ul', 'ol', 'paragraph']],
                ['table', ['table']],
                ['insert', ['link', 'picture']],
                ['view', ['codeview']]
            ],
            callbacks: {
                onChange: function(contents) { $('#isiHtml').val(contents); },
                onImageUpload: function(files) {
                    for (let i = 0; i < files.length; i++) uploadImage(files[i], this);
                }
            }
        });

        // Masukkan HTML Lama (Edit)
        setTimeout(function() {
            if(isiHtmlAwal && isiHtmlAwal !== 'null' && isiHtmlAwal !== '') {
                $('#editorIsiHtml').summernote('code', isiHtmlAwal);
            }
        }, 200);

        function uploadImage(file, editor) {
            let data = new FormData();
            data.append("file", file);
            data.append("<?= csrf_token() ?>", "<?= csrf_hash() ?>");
            Swal.fire({ title: 'Mengunggah...', didOpen: () => { Swal.showLoading(); } });
            $.ajax({
                url: '<?= base_url("template-email/upload-gambar") ?>',
                method: 'POST', data: data, contentType: false, processData: false,
                success: function(res) {
                    Swal.close();
                    if (res.success) { $(editor).summernote('insertImage', res.url); } 
                    else { Swal.fire({ icon: 'error', title: 'Gagal', text: res.message }); }
                }
            });
        }

        // 2. FITUR CARD UPLOAD PDF
        let uploadedPdfUrl = '';
        $('#uploadPdfFile').on('change', function() {
            let file = this.files[0];
            if (!file) return;
            let data = new FormData();
            data.append("file", file);
            data.append("<?= csrf_token() ?>", "<?= csrf_hash() ?>");
            Swal.fire({ title: 'Mengunggah PDF...', allowOutsideClick: false, didOpen: () => { Swal.showLoading(); } });

            $.ajax({
                url: '<?= base_url("template-email/upload-gambar") ?>',
                method: 'POST', data: data, contentType: false, processData: false,
                success: function(res) {
                    Swal.close();
                    if (res.success) {
                        uploadedPdfUrl = res.url;
                        $('#attachmentPdfUrl').val(res.url);
                        $('#linkPdfResult').attr('href', res.url);
                        $('#pdfResultContainer').slideDown();
                        Swal.fire({ icon: 'success', title: 'Berhasil', text: 'PDF berhasil di-upload!' });
                    } else {
                        Swal.fire({ icon: 'error', title: 'Gagal', text: res.message });
                        $('#uploadPdfFile').val('');
                    }
                }
            });
        });

        $('#btnInsertPdfText').on('click', function() {
            if (!uploadedPdfUrl) return;
            let tipe = $('input[name="tipe_pdf"]:checked').val();
            let insertHtml = '';

            if (tipe === 'link') {
                insertHtml = `<p style="margin-top:10px;"><a href="${uploadedPdfUrl}" target="_blank" style="padding:10px 15px; background-color:#007bff; color:#fff; text-decoration:none; border-radius:5px; font-weight:bold;">📄 Lihat Dokumen PDF</a></p><br>`;
            } else {
                insertHtml = `<p style="padding:15px; border:2px dashed #f39c12; background:#fffdf5; margin-top:10px;"><strong>📁 FILE PDF TERLAMPIR:</strong> <a href="${uploadedPdfUrl}" target="_blank" style="color:#3498db;">[Cek File PDF]</a></p><br>`;
            }

            // Jurus Tembak Langsung DOM (Bebas Bug)
            var $editable = $('#editorIsiHtml').next('.note-editor').find('.note-editable');
            if ($editable.length > 0) {
                $editable.append(insertHtml);
            } else {
                $('#editorIsiHtml').append(insertHtml);
            }
            $('#editorIsiHtml').summernote('editor.saveRange');
            showToast('✅ Teks PDF disisipkan ke Editor!');
        });

        $('#btnResetPdf').on('click', function() {
            uploadedPdfUrl = '';
            $('#attachmentPdfUrl').val('');
            $('#uploadPdfFile').val('');
            $('#pdfResultContainer').slideUp();
        });

        // 3. FITUR PREVIEW EMAIL
        $('#btnPreview').on('click', function() {
            let subjek = $('input[name="subjek_email"]').val() || '(Tanpa Subjek)';
            let isiHtml = $('#editorIsiHtml').summernote('code');
            let pdfUrl = $('#attachmentPdfUrl').val();
            let tipePdf = $('input[name="tipe_pdf"]:checked').val();

            $('#previewSubjek').text(subjek);
            isiHtml = isiHtml.replace(/\{\{nama\}\}/g, 'Ackmal');
            isiHtml = isiHtml.replace(/\{\{nama_perusahaan\}\}/g, 'PT Binco');
            isiHtml = isiHtml.replace(/\{\{no_hp\}\}/g, '081234567890');
            isiHtml = isiHtml.replace(/\{\{email\}\}/g, 'ackmal@binco.com');
            $('#previewBody').html(isiHtml);

            if (pdfUrl && tipePdf === 'attachment') {
                $('#previewAttachment').show();
                $('#previewAttachmentUrl').attr('href', pdfUrl);
            } else {
                $('#previewAttachment').hide();
            }
            $('#modalPreview').modal('show');
        });

        // 4. PLACEHOLDER & SPAM AI
        $(document).on('click', '.placeholder-badge', function() {
            $('#editorIsiHtml').summernote('insertText', $(this).data('placeholder'));
            showToast('✅ Dimasukkan ke editor!');
        });

        function showToast(msg) {
            const $t = $('#toastNotif');
            $t.text(msg).addClass('show');
            setTimeout(() => $t.removeClass('show'), 2000);
        }

        $('#formEdit').on('submit', function() { $('#isiHtml').val($('#editorIsiHtml').summernote('code')); });

        const csrfName = '<?= csrf_token() ?>';
        let csrfHash = '<?= csrf_hash() ?>';
        $('#btnCekSpam').on('click', function() {
            const $btn = $(this), $hasil = $('#hasilCekSpam');
            const subjek = $('input[name="subjek_email"]').val().trim();
            const isiHtml = $('#editorIsiHtml').summernote('code').trim();
            const isiTeks = $('textarea[name="isi_teks"]').val() || '';
            const tombolAwal = '<i class="fa fa-search"></i> Cek Spam (AI)';

            if (!subjek || !isiHtml || isiHtml === '<p><br></p>') {
                Swal.fire({ icon: 'warning', title: 'Belum Lengkap', text: 'Isi Subjek Email dan Desain Email terlebih dahulu.' });
                return;
            }

            $btn.prop('disabled', true).html('<i class="fa fa-spinner spam-loading"></i> Menganalisis...');
            $hasil.hide().empty();

            $.ajax({
                url: '<?= base_url("template-email/cek-spam") ?>',
                method: 'POST',
                dataType: 'json',
                timeout: 120000,
                data: { [csrfName]: csrfHash, subjek_email: subjek, isi_html: isiHtml, isi_teks: isiTeks },
                success: function(res) {
                    if (res.csrfHash) csrfHash = res.csrfHash;
                    if (!res.success) { showError(res.message); return; }

                    if (res.status === 'spam') {
                        let $card = $('<div class="spam-result spam">');
                        $card.append('<h4><i class="fa fa-times-circle"></i> Berisiko Masuk SPAM</h4>');
                        $card.append('<p>Email ini memiliki indikasi risiko masuk ke folder Spam.</p>');
                        let $list = $('<ul class="alasan-list">');
                        if (res.alasan.length > 0) { res.alasan.forEach(a => $list.append($('<li>').text(a))); } else { $list.append('<li>Tidak ada alasan spesifik.</li>'); }
                        $card.append($list);
                        $hasil.append($card).show();
                    } else {
                        let $card = $('<div class="spam-result inbox">');
                        $card.append('<h4><i class="fa fa-check-circle"></i> Tidak Ada Indikasi Spam</h4>');
                        $card.append('<p>Tidak ditemukan indikasi spam yang signifikan.</p>');
                        $hasil.append($card).show();
                    }
                },
                error: function(xhr, textStatus) {
                    if (xhr.responseJSON && xhr.responseJSON.csrfHash) csrfHash = xhr.responseJSON.csrfHash;
                    let msg = textStatus === 'timeout' ? 'Koneksi ke server Google terlalu lama (Timeout).' : (xhr.responseJSON?.message || 'Terjadi kesalahan.');
                    showError(msg);
                },
                complete: function() { $btn.prop('disabled', false).html(tombolAwal); }
            });

            function showError(msg) {
                $hasil.html(`<div class="spam-result error"><h4><i class="fa fa-exclamation-triangle"></i> Gagal Menganalisis</h4><p>${msg}</p></div>`).show();
            }
        });
    });
</script>
<?= $this->endSection() ?>