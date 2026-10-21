<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <title><?= esc($title ?? 'Email Blast') ?></title>
  <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">

  <?= $this->include('layouts/header') ?>

  <!-- CSS tambahan per halaman -->
  <?= $this->renderSection('styles') ?>
</head>
<body class="hold-transition skin-blue sidebar-mini">
<div class="wrapper">

  <?= $this->include('layouts/navbar') ?>

  <?= $this->include('layouts/sidebar') ?>

  <!-- Content Wrapper -->
  <div class="content-wrapper">
    <section class="content-header">
      <h1>
        <!-- PERBAIKAN: Gunakan renderSection agar membaca dari View -->
        <?= $this->renderSection('page_title') ?>
        <small><?= $this->renderSection('page_subtitle') ?></small>
      </h1>
      <ol class="breadcrumb">
        <?= $this->renderSection('breadcrumb') ?>
      </ol>
    </section>

    <section class="content">
      <?= $this->renderSection('content') ?>
    </section>
  </div>

  <?= $this->include('layouts/footer') ?>

  <aside class="control-sidebar control-sidebar-dark"></aside>
  <div class="control-sidebar-bg"></div>
</div>

<?= $this->include('layouts/scripts') ?>

<!-- JS tambahan per halaman -->
<?= $this->renderSection('scripts') ?>

</body>
</html>