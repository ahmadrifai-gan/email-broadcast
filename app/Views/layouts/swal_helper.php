<?php
/**
 * app/Views/layouts/swal_helper.php
 * Helper SweetAlert2 bersama (kirim_email: index/detail/buat, smtp_settings: index/form).
 * Dipanggil di section 'scripts':  <?= $this->include('layouts/swal_helper') ?>
 *
 * SweetAlert2 dimuat dengan cara & versi yang SAMA seperti halaman Kategori Template Email:
 *     <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
 *
 * API (window.AppSwal):
 *   konfirmasi({judul, teks|html, ikon, ya, batal, bahaya})  -> Promise<boolean>
 *   info({ikon, judul, teks|html, tombol, timer})             -> Promise
 *   toast(ikon, judul, ms) | loading(judul, teks) | tutup() | progressToast(judul)
 *   flash({success, error, errors})
 * <form> / <a> dengan atribut data-swal-konfirmasi otomatis meminta konfirmasi dulu.
 */
?>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<style>
    .swal2-container { z-index: 20000 !important; }
    .swal-ringkasan { width: 100%; text-align: left; font-size: 13.5px; margin: 6px 0 0; border-collapse: collapse; }
    .swal-ringkasan th { width: 36%; color: #667085; font-weight: 600; padding: 7px 10px 7px 0; vertical-align: top; }
    .swal-ringkasan td { padding: 7px 0; color: #2c3e50; font-weight: 500; word-break: break-word; }
    .swal-ringkasan tr + tr th, .swal-ringkasan tr + tr td { border-top: 1px solid #eef1f5; }
    .swal-daftar { text-align: left; margin: 6px 0 0; padding-left: 18px; }
    .swal-daftar li { margin-bottom: 3px; }
    .swal-fallback-alert { margin-bottom: 15px; }
</style>
<script>
(function () {
'use strict';
if (window.AppSwal) return;

var BIRU = '#3085d6', MERAH = '#d33', ABU = '#6e7881';

function esc(s) {
    return String(s == null ? '' : s).replace(/[&<>"']/g, function (c) {
        return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
    });
}

/* Swal dimuat sinkron lewat <script> di atas; fungsi ini dipertahankan agar kode lama tetap jalan */
function muat() { return Promise.resolve(window.Swal || null); }

function teksPolos(o) {
    var t = o.teks || '';
    if (!t && o.html) { var d = document.createElement('div'); d.innerHTML = o.html; t = d.textContent; }
    return [o.judul, t].filter(Boolean).join('\n\n');
}

/* ---------- Konfirmasi (gaya sama seperti hapus di Kategori Template) ---------- */
async function konfirmasi(o) {
    o = o || {};
    if (!window.Swal) return window.confirm(teksPolos(o));
    var r = await Swal.fire({
        title: o.judul || 'Anda yakin?',
        text: o.html ? undefined : o.teks,
        html: o.html,
        icon: o.ikon || 'warning',
        showCancelButton: true,
        confirmButtonColor: o.bahaya ? MERAH : BIRU,
        cancelButtonColor:  o.bahaya ? BIRU  : ABU,
        confirmButtonText: o.ya || 'Ya, lanjutkan',
        cancelButtonText:  o.batal || 'Batal',
        focusCancel: !!o.bahaya
    });
    return !!r.isConfirmed;
}

/* ---------- Pesan info / peringatan / error ---------- */
async function info(o) {
    o = o || {};
    if (!window.Swal) { window.alert(teksPolos(o)); return; }
    return Swal.fire({
        icon: o.ikon || 'info',
        title: o.judul,
        text: o.html ? undefined : o.teks,
        html: o.html,
        confirmButtonColor: BIRU,
        confirmButtonText: o.tombol || 'OK',
        showConfirmButton: o.tombol !== false,
        timer: o.timer || undefined,
        timerProgressBar: !!o.timer
    });
}

/* ---------- Toast ---------- */
async function toast(ikon, judul, ms) {
    if (!window.Swal) { bannerFallback(ikon === 'error' ? 'danger' : 'success', judul); return; }
    return Swal.fire({
        toast: true, position: 'top-end', icon: ikon || 'success', title: judul,
        showConfirmButton: false, timer: ms || 3200, timerProgressBar: true,
        didOpen: function (t) {
            t.addEventListener('mouseenter', Swal.stopTimer);
            t.addEventListener('mouseleave', Swal.resumeTimer);
        }
    });
}

/* ---------- Loading (modal) ---------- */
function loading(judul, teks) {
    if (!window.Swal) return;
    Swal.fire({
        title: judul || 'Memproses...',
        text: teks || 'Mohon tunggu sebentar.',
        allowOutsideClick: false, allowEscapeKey: false, showConfirmButton: false,
        didOpen: function () { Swal.showLoading(); }
    });
}

/* ---------- Toast progres ---------- */
function progressToast(judul) {
    if (window.Swal) {
        Swal.fire({
            toast: true, position: 'top-end', title: judul,
            showConfirmButton: false, allowOutsideClick: false,
            didOpen: function () { Swal.showLoading(); }
        });
    }
    return function tutupToast() { if (window.Swal) Swal.close(); };
}

function tutup() { if (window.Swal) Swal.close(); }

function bannerFallback(tipe, pesan) {
    var host = document.querySelector('section.content') || document.querySelector('.content-wrapper') || document.body;
    var d = document.createElement('div');
    d.className = 'alert alert-' + tipe + ' alert-dismissible swal-fallback-alert';
    d.setAttribute('role', 'alert');
    d.textContent = pesan;
    host.insertBefore(d, host.firstChild);
}

/* ---------- Flash message (gaya sama seperti Kategori Template) ---------- */
async function flash(d) {
    d = d || {};
    var errs = [].concat(d.errors || []).filter(Boolean);

    if (!window.Swal) {
        if (d.error) bannerFallback('danger', d.error);
        errs.forEach(function (m) { bannerFallback('danger', m); });
        if (d.success) bannerFallback('success', d.success);
        return;
    }

    if (d.error) {
        await Swal.fire({ icon: 'error', title: 'Gagal!', text: d.error, confirmButtonColor: BIRU });
    }
    if (errs.length) {
        await Swal.fire({
            icon: 'warning', title: 'Validasi Gagal', confirmButtonColor: BIRU,
            html: errs.length > 1
                ? '<ul class="swal-daftar">' + errs.map(function (m) { return '<li>' + esc(m) + '</li>'; }).join('') + '</ul>'
                : esc(errs[0])
        });
    }
    if (d.success) {
        Swal.fire({ icon: 'success', title: 'Berhasil!', text: d.success, timer: 2200, showConfirmButton: false });
    }
}

/* ---------- Form / link dengan data-swal-konfirmasi ---------- */
async function tanyaDariDataset(el) {
    var d = el.dataset;
    var html = esc(d.teks || '').replace(/\{nama\}/g, '<b>' + esc(d.nama || '') + '</b>');
    return konfirmasi({
        judul: d.judul || 'Anda yakin?',
        html: html || undefined,
        ikon: d.ikon || 'warning',
        ya: d.ya || 'Ya, lanjutkan',
        batal: d.batal || 'Batal',
        bahaya: d.bahaya === '1'
    });
}

// <form data-swal-konfirmasi ...>
document.addEventListener('submit', async function (e) {
    var f = e.target;
    if (!(f instanceof HTMLFormElement) || !f.hasAttribute('data-swal-konfirmasi')) return;
    if (f.dataset.swalLolos === '1') return;          // sudah dikonfirmasi -> biarkan submit native
    e.preventDefault();
    if (!(await tanyaDariDataset(f))) return;
    loading(f.dataset.loading || 'Memproses...');
    f.dataset.swalLolos = '1';
    HTMLFormElement.prototype.submit.call(f);
}, true);

// <a href="..." data-swal-konfirmasi ...>
document.addEventListener('click', async function (e) {
    var a = e.target.closest ? e.target.closest('a[data-swal-konfirmasi]') : null;
    if (!a || e.defaultPrevented) return;
    if (e.ctrlKey || e.metaKey || e.shiftKey || e.button === 1) return;
    e.preventDefault();
    if (!(await tanyaDariDataset(a))) return;
    loading(a.dataset.loading || 'Memproses...');
    window.location.href = a.href;
}, true);

window.AppSwal = {
    esc: esc, muat: muat, konfirmasi: konfirmasi, info: info, toast: toast,
    loading: loading, tutup: tutup, progressToast: progressToast, flash: flash
};

})();
</script>