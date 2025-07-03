<?= $this->extend('layout/main_layout') ?>
<?= $this->section('content') ?>

<div class="container py-4">
    <h2 class="mb-4">Konfigurasi Jenis & Kode Dokumen</h2>

    <!-- Tabel Jenis Dokumen -->
    <div class="form-section-divider">
        <h4>Daftar Jenis Dokumen</h4>
    </div>

    <table class="table table-bordered table-striped mb-5">
        <thead class="table-dark">
            <tr>
                <th>ID</th>
                <th>Nama Jenis</th>
                <th>Kode</th>
                <th>Gunakan Kode Predefined</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($kategori_dokumen as $kategori): ?>
                <tr>
                    <td><?= $kategori['id'] ?></td>
                    <td><?= esc($kategori['nama']) ?></td>
                    <td><?= esc($kategori['kode']) ?></td>
                    <td><?= $kategori['use_predefined_codes'] ? 'Ya' : 'Tidak' ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <!-- Tabel Kode-Nama Dokumen -->
    <div class="form-section-divider">
        <h4>Daftar Kode-Nama Dokumen Predefined</h4>
    </div>

    <?php if (!empty($kode_dokumen)): ?>
        <?php foreach ($kode_dokumen as $jenis => $list): ?>
            <div class="mb-3">
                <h5 class="text-primary mt-4"><?= strtoupper($jenis) ?></h5>
                <table class="table table-sm table-bordered">
                    <thead class="table-light">
                        <tr>
                            <th>Kode</th>
                            <th>Nama Dokumen</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($list as $item): ?>
                            <tr>
                                <td><?= esc($item['kode']) ?></td>
                                <td><?= esc($item['nama']) ?></td>
                            </tr>
                        <?php endforeach ?>
                    </tbody>
                </table>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <p class="text-muted">Belum ada kode dokumen yang tersedia.</p>
    <?php endif; ?>
</div>

<?= $this->endSection() ?>
