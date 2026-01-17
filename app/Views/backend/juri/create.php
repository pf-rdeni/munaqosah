<?= $this->extend('backend/template/template'); ?>

<?= $this->section('content'); ?>
<div class="row">
    <div class="col-md-6 offset-md-3">
        <div class="card card-primary">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-user-plus mr-2"></i> Tambah Juri Baru
                </h3>
            </div>
            <form action="<?= base_url('backend/juri/store') ?>" method="post">
                <?= csrf_field() ?>
                <div class="card-body">
                    
                    <div class="alert alert-info">
                        <h5><i class="icon fas fa-info"></i> Info Akun Otomatis</h5>
                        <ul>
                            <li>Username akan digenerate otomatis berdasarkan materi (cth: <b>juri_tahfidz_1</b>).</li>
                            <li>Password default: <b>JuriMunaqosah123</b></li>
                        </ul>
                    </div>

                    <?php if (session()->getFlashdata('errors')): ?>
                        <div class="alert alert-danger">
                            <ul>
                            <?php foreach (session()->getFlashdata('errors') as $error): ?>
                                <li><?= esc($error) ?></li>
                            <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>

                    <div class="form-group">
                        <label>Grup Materi Ujian</label>
                        <select id="selectGrupMateri" name="id_grup_materi" class="form-control <?= session('errors.id_grup_materi') ? 'is-invalid' : '' ?>" required>
                            <option value="">-- Pilih Grup Materi --</option>
                            <?php foreach ($materiList as $grup): ?>
                                <option value="<?= $grup['id'] ?>" <?= old('id_grup_materi') == $grup['id'] ? 'selected' : '' ?>>
                                    <?= $grup['nama_grup_materi'] ?> (<?= $grup['id_grup_materi'] ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <div class="invalid-feedback"><?= session('errors.id_grup_materi') ?></div>
                    </div>

                    <div class="form-group">
                        <label>Nama Lengkap Juri (Otomatis)</label>
                        <input type="text" id="inputNamaJuri" name="nama_juri" class="form-control <?= session('errors.nama_juri') ? 'is-invalid' : '' ?>" value="<?= old('nama_juri') ?>" placeholder="Akan terisi otomatis" required>
                        <div class="invalid-feedback"><?= session('errors.nama_juri') ?></div>
                    </div>

                    <div class="form-group">
                        <label>Username (Preview)</label>
                        <input type="text" id="inputUsername" class="form-control" placeholder="juri_..." readonly>
                        <small class="text-muted">Username login akan digenerate dari format ini (sistem akan menambahkan angka jika sudah ada).</small>
                    </div>

                </div>
                <div class="card-footer d-flex justify-content-between">
                    <a href="<?= base_url('backend/juri') ?>" class="btn btn-secondary">Kembali</a>
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
        $('#selectGrupMateri').change(function() {
            var grupId = $(this).val();
            
            if (grupId && grupId !== '') {
                // Show loading state
                $('#inputUsername').val('Memuat...');
                $('#inputNamaJuri').val('Memuat...');
                
                // Call AJAX to get next available username
                $.ajax({
                    url: '<?= base_url('backend/juri/generateUsername') ?>/' + grupId,
                    type: 'GET',
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            $('#inputUsername').val(response.username);
                            $('#inputNamaJuri').val(response.nama_juri);
                        } else {
                            $('#inputUsername').val('Error: ' + response.message);
                            $('#inputNamaJuri').val('');
                        }
                    },
                    error: function() {
                        $('#inputUsername').val('Gagal memuat');
                        $('#inputNamaJuri').val('');
                    }
                });
            } else {
                $('#inputNamaJuri').val('');
                $('#inputUsername').val('');
            }
        });
    });
</script>
<?= $this->endSection(); ?>
