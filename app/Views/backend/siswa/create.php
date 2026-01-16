<?= $this->extend('backend/template/template'); ?>

<?= $this->section('content'); ?>
<!-- Halaman Form Tambah Siswa -->

<div class="row">
    <div class="col-md-8 offset-md-2">
        <div class="card card-primary">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-user-plus mr-2"></i>
                    Form Tambah Siswa Baru
                </h3>
            </div>
            
            <form action="<?= base_url('backend/siswa/store') ?>" method="post">
                <?= csrf_field() ?>
                
                <div class="card-body">
                    <!-- Alert Validasi Error -->
                    <?php if (session()->getFlashdata('errors')): ?>
                        <div class="alert alert-danger alert-dismissible fade show">
                            <button type="button" class="close" data-dismiss="alert">&times;</button>
                            <h5><i class="icon fas fa-ban"></i> Kesalahan Validasi</h5>
                            <ul class="mb-0">
                                <?php foreach (session()->getFlashdata('errors') as $error): ?>
                                    <li><?= esc($error) ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>
                    
                    <!-- Data Identitas -->
                    <h5 class="text-primary mb-3">
                        <i class="fas fa-id-card mr-2"></i>
                        Data Identitas
                    </h5>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="nisn">NISN <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="nisn" name="nisn" 
                                       value="<?= old('nisn') ?>" placeholder="Masukkan NISN" required>
                                <small class="form-text text-muted">Nomor Induk Siswa Nasional</small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="nama_siswa">Nama Lengkap <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="nama_siswa" name="nama_siswa" 
                                       value="<?= old('nama_siswa') ?>" placeholder="Masukkan nama lengkap" required>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="jenis_kelamin">Jenis Kelamin <span class="text-danger">*</span></label>
                                <select class="form-control" id="jenis_kelamin" name="jenis_kelamin" required>
                                    <option value="">-- Pilih --</option>
                                    <option value="L" <?= old('jenis_kelamin') == 'L' ? 'selected' : '' ?>>Laki-laki</option>
                                    <option value="P" <?= old('jenis_kelamin') == 'P' ? 'selected' : '' ?>>Perempuan</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="tempat_lahir">Tempat Lahir</label>
                                <input type="text" class="form-control" id="tempat_lahir" name="tempat_lahir" 
                                       value="<?= old('tempat_lahir') ?>" placeholder="Kota/Kabupaten">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="tanggal_lahir">Tanggal Lahir</label>
                                <input type="date" class="form-control" id="tanggal_lahir" name="tanggal_lahir" 
                                       value="<?= old('tanggal_lahir') ?>">
                            </div>
                        </div>
                    </div>
                    
                    <hr>
                    
                    <!-- Data Orang Tua -->
                    <h5 class="text-primary mb-3">
                        <i class="fas fa-users mr-2"></i>
                        Data Orang Tua
                    </h5>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="nama_ayah">Nama Ayah</label>
                                <input type="text" class="form-control" id="nama_ayah" name="nama_ayah" 
                                       value="<?= old('nama_ayah') ?>" placeholder="Nama lengkap ayah">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="nama_ibu">Nama Ibu</label>
                                <input type="text" class="form-control" id="nama_ibu" name="nama_ibu" 
                                       value="<?= old('nama_ibu') ?>" placeholder="Nama lengkap ibu">
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="alamat">Alamat Lengkap</label>
                        <textarea class="form-control" id="alamat" name="alamat" rows="3" 
                                  placeholder="Masukkan alamat lengkap"><?= old('alamat') ?></textarea>
                    </div>
                </div>
                
                <div class="card-footer">
                    <div class="d-flex justify-content-between">
                        <a href="<?= base_url('backend/siswa') ?>" class="btn btn-secondary">
                            <i class="fas fa-arrow-left mr-1"></i> Kembali
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save mr-1"></i> Simpan
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<?= $this->endSection(); ?>
