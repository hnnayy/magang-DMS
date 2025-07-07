<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <link rel="stylesheet" href="<?= base_url('assets/css/daftar-unit.css') ?>">

</head>
<body>
<h3>Daftar Unit</h3>
<table>
    <thead>
        <tr>
            <th>No</th><th>Fakultas/Direktorat</th><th>Unit</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($units as $i => $u): ?>
        <tr>
            <td><?= $i+1 ?></td>
            <td><?= esc($u['parent_name']) ?></td>
            <td><?= esc($u['name']) ?></td>
        </tr>
        <?php endforeach ?>
    </tbody>
</table>
<!-- FOOTER -->
<div class="footer-pdf">
    © <?= date('Y') ?> Nama Institusi · Halaman <span class="pagenum"></span>
</div>
</body>
</html>
