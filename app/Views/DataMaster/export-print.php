<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size:12px; }
        table { width:100%; border-collapse:collapse; }
        th,td { border:1px solid #000; padding:4px; }
        th { background:#eee; }

        /* ── footer di pojok kiri bawah ── */
        .footer-pdf    {
            position: fixed;
            bottom: 0;
            left:   0;
            right:  0;
            text-align: center;
            font-size: 10px;
            border-top:1px solid #000;
            padding:4px 0;
        }

        /* nomor halaman otomatis (khusus Dompdf ≥1.2) */
        .pagenum:before {
            content: counter(page);
        }
    </style>
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
<!-- FOOTER-pdf -->
<div class="footer-pdf">
    © <?= date('Y') ?> Nama Institusi · Halaman <span class="pagenum"></span>
</div>

<!-- auto‑print untuk versi print -->
<?php if (!isset($noAutoPrint)) : ?>
<script>
  window.addEventListener('load', () => window.print());
</script>
<?php endif; ?>
</body>
</html>
