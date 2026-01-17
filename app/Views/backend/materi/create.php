<?= $this->extend('backend/template/template'); ?>

<?= $this->section('content'); ?>
<div class="row">
    <div class="col-md-6 offset-md-3">
        <div class="card card-primary">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-plus mr-2"></i> Tambah Materi Ujian
                </h3>
            </div>
            <form action="<?= base_url('backend/materi/store') ?>" method="post">
                <?= csrf_field() ?>
                <div class="card-body">

                    <div class="form-group">
                        <label>Grup Materi</label>
                        <select name="id_grup_materi" class="form-control select2 <?= session('errors.id_grup_materi') ? 'is-invalid' : '' ?>" style="width: 100%;" required>
                            <option value="">-- Pilih Grup --</option>
                            <?php foreach ($grupMateri as $g): ?>
                                <option value="<?= $g['id'] ?>" <?= old('id_grup_materi') == $g['id'] ? 'selected' : '' ?>>
                                    <?= esc($g['nama_grup_materi']) ?> (<?= esc($g['id_grup_materi']) ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <div class="invalid-feedback"><?= session('errors.id_grup_materi') ?></div>
                    </div>

                    <div class="form-group">
                        <label>Nama Materi</label>
                        <input type="text" id="inputNama" name="nama_materi" class="form-control <?= session('errors.nama_materi') ? 'is-invalid' : '' ?>" value="<?= old('nama_materi') ?>" placeholder="Contoh: Hafalan Juz 30" required>
                        <div class="invalid-feedback"><?= session('errors.nama_materi') ?></div>
                    </div>

                    <div class="form-group">
                        <label>ID Materi (Preview)</label>
                        <input type="text" id="inputIdMateri" class="form-control" placeholder="Otomatis 3 Huruf" readonly>
                        <small class="text-muted">Kode akan diambil dari 3 huruf pertama.</small>
                    </div>

                    <div class="form-group">
                        <label>Deskripsi</label>
                        <textarea name="deskripsi" class="form-control" rows="3"><?= old('deskripsi') ?></textarea>
                    </div>

                    <div class="form-group">
                        <label>Nilai Maksimal</label>
                        <input type="number" name="nilai_maksimal" class="form-control" value="<?= old('nilai_maksimal', '0') ?>" min="0">
                        <small class="text-muted">Nilai default 0.</small>
                    </div>

                </div>
                <div class="card-footer d-flex justify-content-between">
                    <a href="<?= base_url('backend/materi') ?>" class="btn btn-secondary">Kembali</a>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?= $this->endSection(); ?>

<?= $this->section('scripts'); ?>
<script>
    $('#inputNama').on('input', function() {
        let text = $(this).val();
        let words = text.split(' ').filter(word => word.length > 0);
        let initials = '';

        if (words.length >= 2) {
            initials = words[0][0] + words[1][0];
        } else if (words.length === 1) {
            initials = words[0].substring(0, 2);
        }

        let code = 'M' + initials.toUpperCase() + '01';
        $('#inputIdMateri').val(code);
    });
</script>
<?= $this->endSection(); ?>
