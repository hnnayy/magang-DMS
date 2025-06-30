<?= $this->include('layout/header') ?>

<?= $this->include('layout/sidebar') ?>

    <div class="main-content">
        <?= $this->renderSection('content') ?>
    </div>

<?= $this->include('layout/footer') ?>