<?= $this->extend('backend/template/template'); ?>

<?= $this->section('content'); ?>
<!-- Halaman Form Tambah Siswa -->
<!-- Cropper CSS -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.css" crossorigin="anonymous" referrerpolicy="no-referrer" />

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
                <input type="hidden" id="foto_base64" name="foto_base64" value="">
                
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

                    <!-- Foto Profil -->
                    <div class="text-center mb-4">
                        <img id="previewPhoto" 
                             src="https://ui-avatars.com/api/?name=Siswa+Baru&background=random&size=200" 
                             class="profile-user-img img-fluid"
                             style="width: 150px; height: 200px; object-fit: cover; border: 3px solid #adb5bd; border-radius: 10px;">
                        
                        <div class="mt-3">
                            <button type="button" class="btn btn-primary btn-sm" id="btnUploadFoto">
                                <i class="fas fa-upload mr-1"></i> Upload Foto
                            </button>
                            <input type="file" id="inputPhoto" accept="image/png, image/jpeg, image/jpg" style="display: none;">
                        </div>
                        <small class="text-muted mt-2 d-block">Crop otomatis rasio 3:4 (Opsional)</small>
                    </div>
                    
                    <hr>
                    
                    <!-- Data Identitas -->
                    <h5 class="text-primary mb-3">
                        <i class="fas fa-id-card mr-2"></i>
                        Data Identitas
                    </h5>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="nis">NIS</label>
                                <input type="text" class="form-control" id="nis" name="nis" 
                                       value="<?= old('nis') ?>" placeholder="Masukkan NIS">
                                <small class="form-text text-muted">Nomor Induk Siswa (Lokal)</small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="nisn">NISN <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="nisn" name="nisn" 
                                       value="<?= old('nisn') ?>" placeholder="Masukkan NISN" required>
                                <small class="form-text text-muted">Nomor Induk Siswa Nasional</small>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                         <div class="col-md-12">
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

                    <div class="form-group">
                        <label for="no_hp">No HP / WhatsApp (Opsional)</label>
                        <input type="text" class="form-control" id="no_hp" name="no_hp" 
                               value="<?= old('no_hp') ?>" placeholder="08xxxxxxxxxx">
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

<!-- Modal Crop Photo Profil -->
<div class="modal fade" id="modalCrop" tabindex="-1" role="dialog" aria-labelledby="modalCropLabel" aria-hidden="true" data-backdrop="static" data-keyboard="false">
    <div class="modal-dialog modal-lg modal-dialog-scrollable modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="modalCropLabel">
                    <i class="fas fa-crop"></i> Crop Foto Profil Siswa
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="alert alert-info" role="alert">
                    <h6 class="alert-heading mb-2" style="cursor: pointer;" data-toggle="collapse" data-target="#petunjukCrop" aria-expanded="false" aria-controls="petunjukCrop">
                        <i class="fas fa-info-circle"></i> Petunjuk Crop Foto Profil 
                        <i class="fas fa-chevron-down float-right"></i>
                    </h6>
                    <div class="collapse" id="petunjukCrop">
                        <ul class="mb-0">
                            <li><strong>Geser dan sesuaikan posisi foto</strong> dengan mengklik dan menyeret area crop</li>
                            <li><strong>Zoom in/out</strong> dengan menggunakan scroll mouse atau tombol zoom</li>
                            <li><strong>Rasio foto 3:4</strong> - Pastikan wajah berada di tengah dan terlihat jelas</li>
                        </ul>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <div class="img-container-crop" style="height: 350px; background-color: #f4f4f4;">
                            <img id="image-to-crop" src="" alt="Foto untuk di-crop" style="max-width: 100%; display: block;">
                        </div>
                    </div>
                </div>
                <div class="row mt-3">
                    <div class="col-md-12 text-center">
                        <div class="btn-group" role="group" aria-label="Kontrol Crop">
                            <button type="button" class="btn btn-outline-primary" id="btnZoomIn" title="Zoom In">
                                <i class="fas fa-search-plus"></i> Zoom In
                            </button>
                            <button type="button" class="btn btn-outline-primary" id="btnZoomOut" title="Zoom Out">
                                <i class="fas fa-search-minus"></i> Zoom Out
                            </button>
                            <button type="button" class="btn btn-outline-primary" id="btnMove" title="Geser Foto">
                                <i class="fas fa-arrows-alt"></i> Geser
                            </button>
                            <button type="button" class="btn btn-outline-secondary" id="btnReset" title="Reset">
                                <i class="fas fa-redo"></i> Reset
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">
                    <i class="fas fa-times"></i> Batal
                </button>
                <button type="button" class="btn btn-primary" id="cropBtn">
                    <i class="fas fa-check"></i> Gunakan Foto
                </button>
            </div>
        </div>
    </div>
</div>

<style>
    .img-container-crop {
        width: 100%;
        height: 350px;
        max-height: 350px;
        display: flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;
    }
    .img-container-crop > img {
        max-width: 100%;
        max-height: 100%;
    }
    #modalCrop .modal-body {
        max-height: calc(100vh - 200px);
        overflow-y: auto;
    }
</style>

<?= $this->endSection(); ?>

<?= $this->section('scripts'); ?>
<!-- Cropper JS -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.js"></script>

<script>
    var bs_modal = $('#modalCrop');
    var image = document.getElementById('image-to-crop');
    var cropper = null;

    // Upload Foto Button
    $('#btnUploadFoto').click(function() {
        $('#inputPhoto').click();
    });

    // Trigger Input File
    $("body").on("change", "#inputPhoto", function(e) {
        var files = e.target.files;
        var done = function(url) {
            image.src = url;
            bs_modal.modal('show');
        };

        if (files && files.length > 0) {
            var file = files[0];

            if (URL) {
                done(URL.createObjectURL(file));
            } else if (FileReader) {
                var reader = new FileReader();
                reader.onload = function(e) {
                    done(reader.result);
                };
                reader.readAsDataURL(file);
            }
        }
    });

    // Modal Show -> Init Cropper
    bs_modal.on('shown.bs.modal', function() {
        if (cropper) {
            cropper.destroy();
        }
        cropper = new Cropper(image, {
            aspectRatio: 3 / 4,
            viewMode: 1,
            dragMode: 'move',
            autoCropArea: 0.8,
            responsive: true,
            restore: false,
            guides: true,
            center: true,
            highlight: true,
            cropBoxMovable: true,
            cropBoxResizable: true
        });
    }).on('hidden.bs.modal', function() {
        if (cropper) {
            cropper.destroy();
            cropper = null;
        }
        $('#inputPhoto').val('');
    });

    // Zoom In
    $('#btnZoomIn').click(function() {
        if (cropper) cropper.zoom(0.1);
    });

    // Zoom Out
    $('#btnZoomOut').click(function() {
        if (cropper) cropper.zoom(-0.1);
    });

    // Move/Drag Mode
    $('#btnMove').click(function() {
        if (cropper) cropper.setDragMode('move');
    });

    // Reset
    $('#btnReset').click(function() {
        if (cropper) cropper.reset();
    });

    // Crop Button Click - Store base64 in hidden field
    $("#cropBtn").click(function() {
        if (!cropper) {
            Swal.fire('Error', 'Cropper tidak tersedia', 'error');
            return;
        }

        var canvas = cropper.getCroppedCanvas({
            width: 600,
            height: 800,
        });

        if (!canvas) {
            Swal.fire('Error', 'Gagal membuat canvas', 'error');
            return;
        }

        // Convert to base64 and store in hidden field
        var base64data = canvas.toDataURL('image/jpeg', 0.9);
        $('#foto_base64').val(base64data);
        
        // Update preview image
        $('#previewPhoto').attr('src', base64data);
        
        // Close modal
        bs_modal.modal('hide');
        
        // Show success toast
        Swal.fire({
            icon: 'success',
            title: 'Foto siap digunakan',
            timer: 1500,
            showConfirmButton: false
        });
    });

    // Update preview avatar when name changes
    $('#nama_siswa').on('input', function() {
        var nama = $(this).val() || 'Siswa Baru';
        var currentSrc = $('#previewPhoto').attr('src');
        // Only update if using avatar (not a cropped photo)
        if (currentSrc.includes('ui-avatars.com')) {
            $('#previewPhoto').attr('src', 'https://ui-avatars.com/api/?name=' + encodeURIComponent(nama) + '&background=random&size=200');
        }
    });
</script>
<?= $this->endSection(); ?>

