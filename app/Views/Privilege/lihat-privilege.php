<?= $this->extend('layout/main_layout') ?>
<?= $this->section('content') ?>

<div class="px-4 py-3">
    <h4>Lihat Privilege</h4>
    <hr>

    <div class="table-responsive bg-white p-3 rounded shadow-sm">
        <table class="table table-bordered table-hover align-middle" id="privilegeTable">
            <thead class="table-light">
                <tr>
                    <th class="text-center">No</th>
                    <th>Role</th>
                    <th>Submenu</th>
                    <th>Privilege</th>
                    <th class="text-center">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($privileges as $i => $p): ?>
                    <tr>
                        <td class="text-center"><?= $i + 1 ?></td>
                        <td><?= esc($p['role']) ?></td>
                        <td><?= esc(implode(', ', $p['submenu'])) ?></td>
                        <td>
                            <?php foreach ($p['actions'] as $act): ?>
                                <?php
                                    $badge = match($act) {
                                        'create' => 'bg-success',
                                        'read'   => 'bg-primary',
                                        'update' => 'bg-warning text-dark',
                                        'delete' => 'bg-danger',
                                        default  => 'bg-secondary'
                                    };
                                ?>
                                <span class="badge <?= $badge ?>"><?= ucfirst($act) ?></span>
                            <?php endforeach ?>
                        </td>
                        <td class="text-center">
                            <button class="btn btn-sm btn-outline-primary">Edit</button>
                            <button class="btn btn-sm btn-outline-danger">Hapus</button>
                        </td>
                    </tr>
                <?php endforeach ?>
            </tbody>
        </table>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/jquery.dataTables.min.css">
<script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
<script>
    $(document).ready(function () {
        $('#privilegeTable').DataTable();
    });
</script>

<?= $this->endSection() ?>
