<?= $this->extend('backend/template/template'); ?>

<?= $this->section('content'); ?>
<div class="row">
    <div class="col-md-6 offset-md-3">
        <div class="card card-warning">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-edit mr-2"></i> Edit Grup Materi
                </h3>
            </div>
            <form action="<?= base_url('backend/grup-materi/update/' . $grup['id']) ?>" method="post">
                <?= csrf_field() ?>
                <div class="card-body">

                    <div class="form-group">
                        <label>ID Grup (Unik)</label>
                        <input type="text" name="id_grup_materi" class="form-control <?= session('errors.id_grup_materi') ? 'is-invalid' : '' ?>" value="<?= old('id_grup_materi', $grup['id_grup_materi']) ?>" required>
                        <div class="invalid-feedback"><?= session('errors.id_grup_materi') ?></div>
                    </div>

                    <div class="form-group">
                        <label>Nama Grup Materi</label>
                        <input type="text" name="nama_grup_materi" class="form-control <?= session('errors.nama_grup_materi') ? 'is-invalid' : '' ?>" value="<?= old('nama_grup_materi', $grup['nama_grup_materi']) ?>" required>
                        <div class="invalid-feedback"><?= session('errors.nama_grup_materi') ?></div>
                    </div>

                    <div class="form-group">
                        <label>Deskripsi</label>
                        <textarea name="deskripsi" class="form-control" rows="3"><?= old('deskripsi', $grup['deskripsi']) ?></textarea>
                    </div>

                    <div class="form-group">
                        <label>Urutan Tampil</label>
                        <input type="number" name="urutan" class="form-control" value="<?= old('urutan', $grup['urutan']) ?>">
                    </div>

                    <div class="form-group">
                        <label>Status</label>
                        <select name="status" class="form-control">
                            <option value="aktif" <?= $grup['status'] == 'aktif' ? 'selected' : '' ?>>Aktif</option>
                            <option value="nonaktif" <?= $grup['status'] == 'nonaktif' ? 'selected' : '' ?>>Non-Aktif</option>
                        </select>
                    </div>

                </div>
                <div class="card-footer d-flex justify-content-between">
                    <a href="<?= base_url('backend/grup-materi') ?>" class="btn btn-secondary">Kembali</a>
                    <button type="submit" class="btn btn-warning"><i class="fas fa-save"></i> Update</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?= $this->endSection(); ?>
