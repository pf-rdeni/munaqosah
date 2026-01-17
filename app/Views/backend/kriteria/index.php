<?= $this->extend('backend/template/template'); ?>

<?= $this->section('content'); ?>
<div class="row">
    <div class="col-12">
        <?php 
            $alertColor = ($totalBobot == 100) ? 'success' : 'warning'; 
            $alertIcon = ($totalBobot == 100) ? 'check-circle' : 'exclamation-triangle';
            
            // Format angka tanpa desimal .00 jika bulat
            $fmtTotal = (float)$totalBobot; 
        ?>
        <div class="callout callout-<?= $alertColor ?>">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h5><i class="fas fa-<?= $alertIcon ?> mr-2"></i> Materi: <?= esc($materi['nama_materi']) ?> (<?= esc($materi['id_materi']) ?>)</h5>
                    <p>Total Bobot Penilaian: <strong><?= $fmtTotal ?> / 100</strong></p>
                    
                    <?php if ($totalBobot < 100): ?>
                        <small>Anda masih perlu menambahkan <strong><?= 100 - $totalBobot ?></strong> poin lagi agar total menjadi 100.</small>
                    <?php elseif ($totalBobot > 100): ?>
                        <small class="text-danger">Total melebihi batas! Harap kurangi bobot.</small>
                    <?php else: ?>
                        <small>Total bobot sudah pas (100).</small>
                    <?php endif; ?>
                </div>
                <!-- Progress Bar visual -->
                <div class="col-4">
                     <div class="progress progress-sm">
                        <div class="progress-bar bg-<?= $alertColor ?>" role="progressbar" aria-valuenow="<?= $totalBobot ?>" aria-valuemin="0" aria-valuemax="100" style="width: <?= min($totalBobot, 100) ?>%">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-list-ul mr-2"></i>
                    Daftar Kriteria
                </h3>
                <div class="card-tools">
                     <a href="<?= base_url('backend/materi') ?>" class="btn btn-secondary btn-sm mr-2">
                        <i class="fas fa-arrow-left mr-1"></i> Kembali
                    </a>
                    <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#modalTambah">
                        <i class="fas fa-plus mr-1"></i> Tambah Kriteria
                    </button>
                </div>
            </div>
            <div class="card-body p-0">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th style="width: 10px">#</th>
                            <th>ID</th>
                            <th>Nama Kriteria</th>
                            <th>Deskripsi</th>
                            <th>Bobot</th>
                            <th>Urutan</th>
                            <th style="width: 150px">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($kriteriaList)): ?>
                            <tr><td colspan="7" class="text-center">Belum ada kriteria.</td></tr>
                        <?php else: ?>
                            <?php $no = 1; foreach ($kriteriaList as $k): ?>
                            <tr>
                                <td><?= $no++ ?></td>
                                <td><code><?= esc($k['id_kriteria']) ?></code></td>
                                <td><?= esc($k['nama_kriteria']) ?></td>
                                <td><?= esc($k['deskripsi']) ?></td>
                                <td><?= esc($k['bobot']) ?></td>
                                <td><?= esc($k['urutan']) ?></td>
                                <td>
                                    <button class="btn btn-warning btn-xs btn-edit" 
                                            data-id="<?= $k['id'] ?>"
                                            data-nama="<?= esc($k['nama_kriteria']) ?>"
                                            data-deskripsi="<?= esc($k['deskripsi']) ?>"
                                            data-bobot="<?= $k['bobot'] ?>"
                                            data-urutan="<?= $k['urutan'] ?>"
                                            data-toggle="modal" data-target="#modalEdit">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="btn btn-danger btn-xs" onclick="confirmDelete('<?= base_url('backend/kriteria/delete/' . $k['id']) ?>')">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal Tambah -->
<div class="modal fade" id="modalTambah">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Tambah Kriteria</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="<?= base_url('backend/kriteria/store/' . $materi['id']) ?>" method="post">
                <?= csrf_field() ?>
                <div class="modal-body">
                    <?php 
                        $sisa = 100 - $totalBobot; 
                        $alertStyle = ($sisa > 0) ? 'info' : 'danger';
                    ?>
                    <div class="alert alert-<?= $alertStyle ?> p-2">
                        <small>
                            <i class="fas fa-info-circle mr-1"></i> 
                            Total Bobot saat ini: <strong><?= (float)$totalBobot ?></strong>. 
                            <?php if ($sisa > 0): ?>
                                Sisa Bobot tersedia: <strong><?= (float)$sisa ?></strong>.
                            <?php else: ?>
                                <br>Silahkan ubah bobot kriteria lain jika ingin menambah kriteria baru.
                            <?php endif; ?>
                        </small>
                    </div>

                    <div class="form-group">
                        <label>Nama Kriteria</label>
                        <input type="text" name="nama_kriteria" class="form-control" required placeholder="Contoh: Kelancaran">
                    </div>
                    <div class="form-group">
                        <label>Deskripsi</label>
                        <textarea name="deskripsi" class="form-control" rows="2"></textarea>
                    </div>
                     <div class="row">
                        <div class="col-6">
                            <div class="form-group">
                                <label>Bobot</label>
                                <input type="number" step="0.01" name="bobot" class="form-control" value="0" max="<?= $sisa ?>" min="0">
                                <small class="text-muted">Max: <?= (float)$sisa ?></small>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="form-group">
                                <label>Urutan</label>
                                <input type="number" name="urutan" class="form-control" value="0">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer justify-content-between">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary" <?= ($sisa <= 0) ? 'disabled' : '' ?>><i class="fas fa-save"></i> Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Edit -->
<div class="modal fade" id="modalEdit">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Edit Kriteria</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="formEdit" action="" method="post">
                <?= csrf_field() ?>
                <div class="modal-body">
                    <!-- Info Alert Dynamic -->
                    <div class="alert alert-info p-2" id="editAlert">
                        <small>
                            <i class="fas fa-info-circle mr-1"></i> 
                            <span id="editAlertText">Checking...</span>
                        </small>
                    </div>

                    <div class="form-group">
                        <label>Nama Kriteria</label>
                        <input type="text" id="editNama" name="nama_kriteria" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Deskripsi</label>
                        <textarea id="editDeskripsi" name="deskripsi" class="form-control" rows="2"></textarea>
                    </div>
                     <div class="row">
                        <div class="col-6">
                            <div class="form-group">
                                <label>Bobot</label>
                                <input type="number" step="0.01" id="editBobot" name="bobot" class="form-control">
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="form-group">
                                <label>Urutan</label>
                                <input type="number" id="editUrutan" name="urutan" class="form-control">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer justify-content-between">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-warning">Update</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?= $this->endSection(); ?>

<?= $this->section('scripts'); ?>
<script>
    // Simpan Total Bobot Global dari PHP ke JS
    const globalTotalBobot = <?= (float)$totalBobot ?>;

    $(document).on('click', '.btn-edit', function() {
        let id = $(this).data('id');
        let nama = $(this).data('nama');
        let deskripsi = $(this).data('deskripsi');
        let bobot = parseFloat($(this).data('bobot'));
        let urutan = $(this).data('urutan');

        // Hitung Limit untuk Item ini
        // Limit = (100 - TotalGlobal) + BobotItemIni
        let sisaGlobal = 100 - globalTotalBobot;
        let maxAllowed = sisaGlobal + bobot;
        
        // Update Form
        $('#formEdit').attr('action', '<?= base_url('backend/kriteria/update') ?>/' + id);
        $('#editNama').val(nama);
        $('#editDeskripsi').val(deskripsi);
        $('#editBobot').val(bobot);
        $('#editUrutan').val(urutan);
        
        // Update Constraint
        $('#editBobot').attr('max', maxAllowed);
        
        // Update Alert Text
        let alertHtml = 'Bobot saat ini: <strong>' + bobot + '</strong>. Maksimal yang bisa diupdate: <strong>' + maxAllowed.toFixed(2) + '</strong>';
        $('#editAlertText').html(alertHtml);
        
        // Visual Color
        // Jika maxAllowed < bobot (kasus aneh/over) -> Warning, else Info
        if (maxAllowed <= 0) {
             $('#editAlert').removeClass('alert-info').addClass('alert-danger');
             $('#editBobot').attr('readonly', true); // Lock if full? No, user might want to reduce it.
        } else {
             $('#editAlert').removeClass('alert-danger').addClass('alert-info');
             $('#editBobot').attr('readonly', false);
        }
    });
</script>
<?= $this->endSection(); ?>
