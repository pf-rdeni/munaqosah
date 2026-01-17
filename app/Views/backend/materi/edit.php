<?= $this->extend('backend/template/template'); ?>

<?= $this->section('content'); ?>
<div class="row">
    <div class="col-md-6 offset-md-3">
        <div class="card card-warning">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-edit mr-2"></i> Edit Materi Ujian
                </h3>
            </div>
            <form action="<?= base_url('backend/materi/update/' . $materi['id']) ?>" method="post">
                <?= csrf_field() ?>
                <div class="card-body">

                    <div class="form-group">
                        <label>Grup Materi</label>
                        <select name="id_grup_materi" class="form-control select2 <?= session('errors.id_grup_materi') ? 'is-invalid' : '' ?>" style="width: 100%;" required>
                            <option value="">-- Pilih Grup --</option>
                            <?php foreach ($grupMateri as $g): ?>
                                <option value="<?= $g['id'] ?>" <?= (old('id_grup_materi', $materi['id_grup_materi']) == $g['id']) ? 'selected' : '' ?>>
                                    <?= esc($g['nama_grup_materi']) ?> (<?= esc($g['id_grup_materi']) ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <div class="invalid-feedback"><?= session('errors.id_grup_materi') ?></div>
                    </div>

                    <div class="form-group">
                        <label>Nama Materi</label>
                        <input type="text" name="nama_materi" class="form-control <?= session('errors.nama_materi') ? 'is-invalid' : '' ?>" value="<?= old('nama_materi', $materi['nama_materi']) ?>" required>
                        <div class="invalid-feedback"><?= session('errors.nama_materi') ?></div>
                    </div>
                    
                    <div class="form-group">
                        <label>ID Materi (Read Only)</label>
                        <input type="text" class="form-control" value="<?= esc($materi['id_materi']) ?>" readonly>
                    </div>

                    <div class="form-group">
                        <label>Deskripsi</label>
                        <textarea name="deskripsi" class="form-control" rows="3"><?= old('deskripsi', $materi['deskripsi']) ?></textarea>
                    </div>

                    <div class="form-group">
                        <label>Nilai Maksimal</label>
                        <input type="number" name="nilai_maksimal" class="form-control" value="<?= old('nilai_maksimal', $materi['nilai_maksimal']) ?>" min="0">
                    </div>

                </div>
                <div class="card-footer d-flex justify-content-between">
                    <a href="<?= base_url('backend/materi') ?>" class="btn btn-secondary">Kembali</a>
                    <button type="submit" class="btn btn-warning"><i class="fas fa-save"></i> Update</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?= $this->endSection(); ?>
