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
                $isEdit = isset($data); 
                $url = $isEdit ? base_url('backend/predikat/update/' . $data['id']) : base_url('backend/predikat/store');
            ?>

            <form action="<?= $url ?>" method="post">
                <?= csrf_field() ?>
                <div class="card-body">
                    <div class="form-group">
                        <label>Nama Predikat</label>
                        <input type="text" class="form-control" name="nama_predikat" value="<?= $isEdit ? $data['nama_predikat'] : '' ?>" required placeholder="Contoh: Sangat Baik">
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Min Nilai</label>
                                <input type="number" class="form-control" name="min_nilai" value="<?= $isEdit ? $data['min_nilai'] : '' ?>" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Max Nilai</label>
                                <input type="number" class="form-control" name="max_nilai" value="<?= $isEdit ? $data['max_nilai'] : '' ?>" required>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Deskripsi Global (Panduan)</label>
                        <textarea class="form-control" name="deskripsi_global" rows="3" required placeholder="Contoh: Gerakan dan bacaan sangat tepat..."><?= $isEdit ? $data['deskripsi_global'] : '' ?></textarea>
                    </div>

                    <div class="form-group">
                        <label>Warna Indikator (CSS Class)</label>
                        <select class="form-control" name="class_css">
                            <option value="success" <?= ($isEdit && $data['class_css'] == 'success') ? 'selected' : '' ?>>Success (Hijau)</option>
                            <option value="info" <?= ($isEdit && $data['class_css'] == 'info') ? 'selected' : '' ?>>Info (Biru)</option>
                            <option value="warning" <?= ($isEdit && $data['class_css'] == 'warning') ? 'selected' : '' ?>>Warning (Kuning)</option>
                            <option value="danger" <?= ($isEdit && $data['class_css'] == 'danger') ? 'selected' : '' ?>>Danger (Merah)</option>
                            <option value="secondary" <?= ($isEdit && $data['class_css'] == 'secondary') ? 'selected' : '' ?>>Secondary (Abu-abu)</option>
                            <option value="primary" <?= ($isEdit && $data['class_css'] == 'primary') ? 'selected' : '' ?>>Primary (Biru Tua)</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Urutan</label>
                        <input type="number" class="form-control" name="urutan" value="<?= $isEdit ? $data['urutan'] : '0' ?>" required>
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
