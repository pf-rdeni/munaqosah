<?= $this->extend('backend/template/template'); ?>

<?= $this->section('content'); ?>
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-layer-group mr-2"></i>
                    Data Grup Materi
                </h3>
                <div class="card-tools">
                    <a href="<?= base_url('backend/grup-materi/create') ?>" class="btn btn-primary btn-sm">
                        <i class="fas fa-plus mr-1"></i> Tambah Grup
                    </a>
                </div>
            </div>
            <div class="card-body">
                <table id="tabelGrup" class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th width="5%" class="text-center">No</th>
                            <th>ID Grup</th>
                            <th>Nama Grup Materi</th>
                            <th>Urutan</th>
                            <th>Status</th>
                            <th width="15%" class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $no = 1; foreach ($grupList as $grup): ?>
                            <tr>
                                <td class="text-center"><?= $no++ ?></td>
                                <td><code><?= esc($grup['id_grup_materi']) ?></code></td>
                                <td><?= esc($grup['nama_grup_materi']) ?></td>
                                <td><?= esc($grup['urutan']) ?></td>
                                <td class="text-center">
                                    <?php if ($grup['status'] == 'aktif'): ?>
                                        <span class="badge badge-success">Aktif</span>
                                    <?php else: ?>
                                        <span class="badge badge-danger">Non-Aktif</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <a href="<?= base_url('backend/grup-materi/edit/' . $grup['id']) ?>" class="btn btn-warning btn-xs">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    
                                    <button type="button" class="btn btn-danger btn-xs" onclick="confirmDelete('<?= base_url('backend/grup-materi/delete/' . $grup['id']) ?>')">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection(); ?>

<?= $this->section('scripts'); ?>
<script>
    $(document).ready(function() {
        $('#tabelGrup').DataTable({
            responsive: true,
            autoWidth: false,
        });
    });
</script>
<?= $this->endSection(); ?>
