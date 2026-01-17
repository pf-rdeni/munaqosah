<?= $this->extend('backend/template/template'); ?>

<?= $this->section('content'); ?>
<div class="row">
    <div class="col-md-6 offset-md-3">
        <div class="card card-warning">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-edit mr-2"></i> Edit Data Juri
                </h3>
            </div>
            <form action="<?= base_url('backend/juri/update/' . $juri['id']) ?>" method="post">
                <?= csrf_field() ?>
                <div class="card-body">
                    
                    <div class="callout callout-warning">
                        <h5><i class="fas fa-exclamation-triangle"></i> Perhatian</h5>
                        <p>Mengubah Grup Materi <b>tidak akan</b> mengubah Username/ID Juri yang sudah ada (untuk menjaga konsistensi login).</p>
                    </div>

                    <div class="form-group">
                        <label>Grup Materi Ujian</label>
                        <select name="id_grup_materi" class="form-control <?= session('errors.id_grup_materi') ? 'is-invalid' : '' ?>" required>
                            <option value="">-- Pilih Grup Materi --</option>
                            <?php foreach ($materiList as $grup): ?>
                                <option value="<?= $grup['id'] ?>" <?= (old('id_grup_materi') ?? $juri['id_grup_materi']) == $grup['id'] ? 'selected' : '' ?>>
                                    <?= $grup['nama_grup_materi'] ?> (<?= $grup['id_grup_materi'] ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <div class="invalid-feedback"><?= session('errors.id_grup_materi') ?></div>
                    </div>

                    <div class="form-group">
                        <label>Nama Lengkap Juri</label>
                        <input type="text" name="nama_juri" class="form-control <?= session('errors.nama_juri') ? 'is-invalid' : '' ?>" value="<?= old('nama_juri') ?? $juri['nama_juri'] ?>" placeholder="Nama Juri" required>
                        <div class="invalid-feedback"><?= session('errors.nama_juri') ?></div>
                    </div>

                    <div class="form-group">
                        <label>Username (Read Only)</label>
                        <input type="text" class="form-control" value="<?= esc($juri['username']) ?>" readonly>
                    </div>

                </div>
                <div class="card-footer d-flex justify-content-between">
                    <a href="<?= base_url('backend/juri') ?>" class="btn btn-secondary">Kembali</a>
                    <button type="submit" class="btn btn-warning"><i class="fas fa-save"></i> Update</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?= $this->endSection(); ?>
