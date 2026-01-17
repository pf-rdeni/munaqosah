<?= $this->extend('backend/template/template'); ?>

<?= $this->section('content'); ?>
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-book mr-2"></i>
                    Data Materi Ujian
                </h3>
                <div class="card-tools">
                    <a href="<?= base_url('backend/materi/create') ?>" class="btn btn-primary btn-sm">
                        <i class="fas fa-plus mr-1"></i> Tambah Materi
                    </a>
                </div>
            </div>
            <div class="card-body">
                <table id="tabelMateri" class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th width="5%">No</th>
                            <th>ID Code</th>
                            <th>Nama Materi</th>
                            <th>Grup Materi</th>
                            <th>Kriteria Penilaian</th>
                            <th>Nilai Max</th>
                            <th>Deskripsi</th>
                            <th width="15%" class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $no = 1; foreach ($materiList as $materi): ?>
                            <tr>
                                <td class="text-center"><?= $no++ ?></td>
                                <td><code><?= esc($materi['id_materi']) ?></code></td>
                                <td><?= esc($materi['nama_materi']) ?></td>
                                <td>
                                    <span class="badge badge-info"><?= esc($materi['nama_grup_materi'] ?? '-') ?></span>
                                </td>
                                <td>
                                    <small class="d-block text-left" style="line-height: 1.4;">
                                        <?= nl2br(esc($materi['list_kriteria'] ?? '-')) ?>
                                    </small>
                                </td>
                                <td class="text-center"><?= esc($materi['nilai_maksimal']) ?></td>
                                <td><?= esc($materi['deskripsi']) ?></td>
                                <td class="text-center">
                                    <div class="btn-group">
                                        <a href="<?= base_url('backend/materi/kriteria/' . $materi['id']) ?>" class="btn btn-info btn-sm" title="Kelola Kriteria">
                                            <i class="fas fa-list-ul"></i>
                                        </a>
                                        <a href="<?= base_url('backend/materi/edit/' . $materi['id']) ?>" class="btn btn-warning btn-sm" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <button type="button" class="btn btn-danger btn-sm" onclick="confirmDelete('<?= base_url('backend/materi/delete/' . $materi['id']) ?>')">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
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
        $('#tabelMateri').DataTable({
            responsive: true,
            autoWidth: false,
        });
    });
</script>
<?= $this->endSection(); ?>
