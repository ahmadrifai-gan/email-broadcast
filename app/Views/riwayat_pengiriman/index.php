<?= $this->extend('layouts/main') ?>

<?= $this->section('title') ?>Riwayat Pengiriman<?= $this->endSection() ?>
<?= $this->section('page_title') ?>Riwayat Pengiriman<?= $this->endSection() ?>
<?= $this->section('page_subtitle') ?>Log semua email yang dikirim<?= $this->endSection() ?>

<?= $this->section('breadcrumb') ?>
<li><a href="<?= base_url() ?>"><i class="fa fa-dashboard"></i> Home</a></li>
<li class="active">Riwayat Pengiriman</li>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="box box-primary">
    <div class="box-header with-border">
        <h3 class="box-title"><i class="fa fa-history"></i> Riwayat Pengiriman</h3>
    </div>

    <div class="box-body">
        <form method="get" action="<?= site_url('riwayat-pengiriman') ?>" class="form-inline" style="margin-bottom:15px;">
            <select name="kampanye" class="form-control input-sm">
                <option value="">Semua kampanye</option>
                <?php foreach ($kampanye as $k): ?>
                    <option value="<?= (int) $k['id'] ?>" <?= $filterId === (int) $k['id'] ? 'selected' : '' ?>>
                        <?= esc($k['nama_kampanye']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <select name="jenis" class="form-control input-sm">
                <option value="">Semua status</option>
                <option value="terkirim" <?= $jenis === 'terkirim' ? 'selected' : '' ?>>Terkirim</option>
                <option value="gagal" <?= $jenis === 'gagal' ? 'selected' : '' ?>>Gagal</option>
            </select>
            <button type="submit" class="btn btn-primary btn-sm"><i class="fa fa-search"></i> Filter</button>
            <a href="<?= site_url('riwayat-pengiriman') ?>" class="btn btn-default btn-sm">Reset</a>
        </form>

        <div class="table-responsive">
            <table class="table table-bordered table-striped table-hover">
                <thead>
                <tr>
                    <th width="50">No</th>
                    <th width="150">Waktu</th>
                    <th>Kampanye</th>
                    <th>Penerima</th>
                    <th width="90">Status</th>
                    <th>Keterangan</th>
                </tr>
                </thead>
                <tbody>
                <?php if (empty($riwayat)): ?>
                    <tr><td colspan="6" class="text-center text-muted">Belum ada riwayat pengiriman.</td></tr>
                <?php endif; ?>

                <?php $no = 1 + (($pager->getCurrentPage() - 1) * $pager->getPerPage()); ?>
                <?php foreach ($riwayat as $r): ?>
                    <tr>
                        <td><?= $no++ ?></td>
                        <td><?= esc(date('d-m-Y H:i', strtotime($r['terjadi_pada']))) ?></td>
                        <td>
                            <a href="<?= site_url('kirim-email/detail/' . $r['kampanye_email_id']) ?>">
                                <?= esc($r['nama_kampanye']) ?>
                            </a>
                        </td>
                        <td>
                            <?= esc($r['nama_penerima']) ?><br>
                            <small class="text-muted"><?= esc($r['alamat_email_penerima']) ?></small>
                        </td>
                        <td>
                            <?php if ($r['jenis_aktivitas'] === 'terkirim'): ?>
                                <span class="label label-success">Terkirim</span>
                            <?php else: ?>
                                <span class="label label-danger"><?= esc(ucfirst($r['jenis_aktivitas'])) ?></span>
                            <?php endif; ?>
                        </td>
                        <td><small class="text-muted"><?= esc($r['detail_aktivitas']) ?></small></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <?= $pager->links() ?>
    </div>
</div>
<?= $this->endSection() ?>