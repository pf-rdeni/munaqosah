<?= $this->extend('backend/template/template'); ?>

<?= $this->section('content'); ?>
<div class="row">
    <div class="col-md-6">
        <div class="card card-primary card-outline">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-edit mr-2"></i> <?= $pageTitle ?>
                </h3>
                <div class="card-tools">
                    <a href="<?= base_url('backend/predikat') ?>" class="btn btn-tool">
                        <i class="fas fa-arrow-left"></i> Kembali
                    </a>
                </div>
            </div>
            
            <?php 
                // Jika ada data DAN ada ID, berarti mode EDIT.
                // Jika ada data tapi TIDAK ada ID, berarti mode COPY/CREATE (Pre-filled).
                $isEdit = isset($data) && isset($data['id']);
                $url = $isEdit ? base_url('backend/predikat/update/' . $data['id']) : base_url('backend/predikat/store');
            ?>

            <form action="<?= $url ?>" method="post">
                <?= csrf_field() ?>
                
                <?php if (isset($collision)): ?>
                <div class="alert alert-warning">
                    <h5><i class="icon fas fa-exclamation-triangle"></i> Duplikasi Terdeteksi!</h5>
                    <p>
                        Kriteria dengan Range Nilai <b><?= $collision['min_nilai'] ?> - <?= $collision['max_nilai'] ?></b> 
                        sudah ada untuk grup materi ini (Nama: <?= esc($collision['nama_predikat']) ?>).
                        <br>Apakah Anda yakin ingin menimpa (overwrite) data tersebut dengan data baru ini?
                    </p>
                    <input type="hidden" name="allow_overwrite" value="1">
                    <button type="submit" class="btn btn-warning text-dark font-weight-bold">
                        <i class="fas fa-check-circle"></i> Ya, Overwrite Data Lama
                    </button>
                    <a href="<?= base_url('backend/predikat') ?>" class="btn btn-secondary ml-2">Batal</a>
                </div>
                <?php endif; ?>

                <div class="card-body">
                    <div class="form-group">
                        <label>Peruntukan (Lingkup Kriteria)</label>
                        <select class="form-control" name="id_grup_materi" <?= $isEdit ? 'disabled' : '' ?>>
                            <option value="global" <?= (isset($data) && empty($data['id_grup_materi'])) ? 'selected' : '' ?>>GLOBAL / UMUM (Untuk Penilaian Akhir & Default)</option>
                            <?php foreach ($grupMateri as $grup): ?>
                                <option value="<?= $grup['id'] ?>" <?= (isset($data) && $data['id_grup_materi'] == $grup['id']) ? 'selected' : '' ?>>
                                    Khusus Grup: <?= esc($grup['nama_grup_materi']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <?php if ($isEdit): ?>
                            <input type="hidden" name="id_grup_materi" value="<?= !empty($data['id_grup_materi']) ? $data['id_grup_materi'] : 'global' ?>">
                            <small class="text-danger"><i class="fas fa-lock"></i> Lingkup Kriteria terkunci saat Edit (Copy jika ingin mengubah ke grup lain).</small>
                        <?php else: ?>
                            <small class="text-muted">Pilih <b>GLOBAL</b> jika kriteria ini digunakan untuk semua atau sebagai kriteria akhir. Pilih <b>Grup Materi</b> jika kriteria ini hanya berlaku untuk rubrik grup tersebut.</small>
                        <?php endif; ?>
                    </div>

                    <div class="form-group">
                        <label>Nama Predikat</label>
                        <input type="text" class="form-control" name="nama_predikat" value="<?= isset($data) ? $data['nama_predikat'] : '' ?>" required placeholder="Contoh: Sangat Baik">
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Min Nilai</label>
                                <input type="number" class="form-control" name="min_nilai" value="<?= isset($data) ? $data['min_nilai'] : '' ?>" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Max Nilai</label>
                                <input type="number" class="form-control" name="max_nilai" value="<?= isset($data) ? $data['max_nilai'] : '' ?>" required>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Deskripsi Global (Panduan)</label>
                        <textarea class="form-control" name="deskripsi_global" rows="3" required placeholder="Contoh: Gerakan dan bacaan sangat tepat..."><?= isset($data) ? $data['deskripsi_global'] : '' ?></textarea>
                    </div>

                    <div class="form-group">
                        <label>Warna Indikator (CSS Class)</label>
                        <select class="form-control" name="class_css">
                            <option value="success" <?= (isset($data) && $data['class_css'] == 'success') ? 'selected' : '' ?>>Success (Hijau)</option>
                            <option value="info" <?= (isset($data) && $data['class_css'] == 'info') ? 'selected' : '' ?>>Info (Biru)</option>
                            <option value="warning" <?= (isset($data) && $data['class_css'] == 'warning') ? 'selected' : '' ?>>Warning (Kuning)</option>
                            <option value="danger" <?= (isset($data) && $data['class_css'] == 'danger') ? 'selected' : '' ?>>Danger (Merah)</option>
                            <option value="secondary" <?= (isset($data) && $data['class_css'] == 'secondary') ? 'selected' : '' ?>>Secondary (Abu-abu)</option>
                            <option value="primary" <?= (isset($data) && $data['class_css'] == 'primary') ? 'selected' : '' ?>>Primary (Biru Tua)</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Urutan</label>
                        <input type="number" class="form-control" name="urutan" value="<?= isset($data) ? $data['urutan'] : '0' ?>" required>
                    </div>
                </div>
                <div class="card-footer">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save mr-1"></i> Simpan
                    </button>
                    <a href="<?= base_url('backend/predikat') ?>" class="btn btn-secondary">Batal</a>
                </div>
            </form>
        </div>
    </div>
    
    <div class="col-md-6">
        <div class="callout callout-info">
            <h5><i class="fas fa-info-circle"></i> Informasi</h5>
            <p>Data Kriteria Skoring (Predikat) ini digunakan secara global untuk menentukan:</p>
            <ul>
                <li>Label skor (Sangat Baik, Baik, dll).</li>
                <li>Rentang nilai yang valid untuk label tersebut.</li>
                <li>Warna visualisasi pada dashboard.</li>
                <li>Menjadi kolom header pada tabel Rubrik Penilaian.</li>
            </ul>
        </div>
    </div>
</div>
<?= $this->endSection(); ?>
