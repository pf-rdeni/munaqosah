<?= $this->extend('backend/template/template'); ?>

<?= $this->section('content'); ?>
<div class="row">
    <div class="col-md-6 offset-md-3">
        <div class="card card-primary">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-plus mr-2"></i> Tambah Grup Materi
                </h3>
            </div>
            <form action="<?= base_url('backend/grup-materi/store') ?>" method="post">
                <?= csrf_field() ?>
                <div class="card-body">

                    <div class="form-group">
                        <label>Nama Grup Materi</label>
                        <input type="text" id="inputNama" name="nama_grup_materi" class="form-control <?= session('errors.nama_grup_materi') ? 'is-invalid' : '' ?>" value="<?= old('nama_grup_materi') ?>" placeholder="Contoh: Praktek Sholat" required>
                        <div class="invalid-feedback"><?= session('errors.nama_grup_materi') ?></div>
                    </div>

                    <div class="form-group">
                        <label>ID Grup (Otomatis)</label>
                        <input type="text" id="inputID" name="id_grup_materi" class="form-control <?= session('errors.id_grup_materi') ? 'is-invalid' : '' ?>" value="<?= old('id_grup_materi') ?>" placeholder="Generated ID">
                        <small class="text-muted">Dapat diedit manual. Jika ID sudah ada, sistem akan otomatis mencari nomor urut berikutnya (misal PS02).</small>
                        <div class="invalid-feedback"><?= session('errors.id_grup_materi') ?></div>
                    </div>

                    <div class="form-group">
                        <label>Deskripsi</label>
                        <textarea name="deskripsi" class="form-control" rows="3"><?= old('deskripsi') ?></textarea>
                    </div>

                    <div class="form-group">
                        <label>Urutan Tampil</label>
                        <input type="number" name="urutan" class="form-control" value="<?= old('urutan', 0) ?>">
                    </div>

                </div>
                <div class="card-footer d-flex justify-content-between">
                    <a href="<?= base_url('backend/grup-materi') ?>" class="btn btn-secondary">Kembali</a>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?= $this->endSection(); ?>

<?= $this->section('scripts'); ?>
<script>
$(document).ready(function() {
    $('#inputNama').on('input', function() {
        let name = $(this).val().trim();
        if (name.length === 0) {
            $('#inputID').val('');
            return;
        }

        let words = name.split(/\s+/);
        let code = '';

        if (words.length >= 2) {
            // First letter of first two words
            let c1 = words[0].charAt(0);
            let c2 = words[1].charAt(0);
            code = (c1 + c2).toUpperCase();
        } else if (words.length === 1) {
            // First and Last letter of the word
            let word = words[0];
            let c1 = word.charAt(0);
            let c2 = word.charAt(word.length - 1);
            code = (c1 + c2).toUpperCase();
        }

        // Auto append 01
        if (code.length === 2) {
            $('#inputID').val(code + '01');
        }
    });
});
</script>
<?= $this->endSection(); ?>
