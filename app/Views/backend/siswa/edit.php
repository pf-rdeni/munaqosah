<?= $this->extend('backend/template/template'); ?>

<?= $this->section('content'); ?>
<!-- Halaman Form Edit Siswa -->
<!-- Cropper CSS -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.css" crossorigin="anonymous" referrerpolicy="no-referrer" />
<style>
    .img-container img {
        max-width: 100%;
    }
</style>

<div class="row">
    <div class="col-md-8 offset-md-2">
        <div class="card card-warning">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-user-edit mr-2"></i>
                    Edit Data Siswa
                </h3>
            </div>
            
            <form action="<?= base_url('backend/siswa/update/' . $siswa['id']) ?>" method="post">
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
                    <?php endif; ?>

                    <!-- Foto Profil -->
                    <div class="text-center mb-4">
                        <?php 
                        $fotoUrl = (!empty($siswa['foto']) && file_exists(FCPATH . $siswa['foto'])) 
                            ? base_url($siswa['foto']) . '?t=' . time() 
                            : 'https://ui-avatars.com/api/?name=' . urlencode($siswa['nama_siswa']) . '&background=random&size=200';
                        ?>
                        <img id="previewPhoto" 
                             src="<?= $fotoUrl ?>" 
                             class="profile-user-img img-fluid"
                             style="width: 150px; height: 200px; object-fit: cover; border: 3px solid #adb5bd; border-radius: 10px;"
                             onerror="this.onerror=null;this.src='https://ui-avatars.com/api/?name=<?= urlencode($siswa['nama_siswa']) ?>&background=random&size=200'">
                        
                        <div class="mt-3">
                            <button type="button" class="btn btn-warning btn-sm" id="btnEditFoto">
                                <i class="fas fa-edit mr-1"></i> Edit Foto
                            </button>
                            <button type="button" class="btn btn-primary btn-sm" id="btnUploadFoto">
                                <i class="fas fa-upload mr-1"></i> Upload Foto Baru
                            </button>
                            <input type="file" id="inputPhoto" accept="image/png, image/jpeg, image/jpg" style="display: none;">
                        </div>
                        <small class="text-muted mt-2 d-block">Crop otomatis rasio 3:4</small>
                    </div>
                    
                    <hr>
                    
                    <!-- Data Identitas -->
                    <h5 class="text-warning mb-3">
                        <i class="fas fa-id-card mr-2"></i>
                        Data Identitas
                    </h5>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="nisn">NISN <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="nisn" name="nisn" 
                                       value="<?= old('nisn', $siswa['nisn']) ?>" placeholder="Masukkan NISN" disabled>
                                <small class="form-text text-muted">NISN tidak dapat diubah (Primary Key)</small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="nama_siswa">Nama Lengkap <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="nama_siswa" name="nama_siswa" 
                                       value="<?= old('nama_siswa', $siswa['nama_siswa']) ?>" placeholder="Masukkan nama lengkap" required>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="jenis_kelamin">Jenis Kelamin <span class="text-danger">*</span></label>
                                <select class="form-control" id="jenis_kelamin" name="jenis_kelamin" required>
                                    <option value="">-- Pilih --</option>
                                    <option value="L" <?= old('jenis_kelamin', $siswa['jenis_kelamin']) == 'L' ? 'selected' : '' ?>>Laki-laki</option>
                                    <option value="P" <?= old('jenis_kelamin', $siswa['jenis_kelamin']) == 'P' ? 'selected' : '' ?>>Perempuan</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="tempat_lahir">Tempat Lahir</label>
                                <input type="text" class="form-control" id="tempat_lahir" name="tempat_lahir" 
                                       value="<?= old('tempat_lahir', $siswa['tempat_lahir']) ?>" placeholder="Kota/Kabupaten">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="tanggal_lahir">Tanggal Lahir</label>
                                <input type="date" class="form-control" id="tanggal_lahir" name="tanggal_lahir" 
                                       value="<?= old('tanggal_lahir', $siswa['tanggal_lahir']) ?>">
                            </div>
                        </div>
                    </div>
                    
                    <hr>
                    
                    <!-- Data Orang Tua -->
                    <h5 class="text-warning mb-3">
                        <i class="fas fa-users mr-2"></i>
                        Data Orang Tua
                    </h5>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="nama_ayah">Nama Ayah</label>
                                <input type="text" class="form-control" id="nama_ayah" name="nama_ayah" 
                                       value="<?= old('nama_ayah', $siswa['nama_ayah']) ?>" placeholder="Nama lengkap ayah">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="nama_ibu">Nama Ibu</label>
                                <input type="text" class="form-control" id="nama_ibu" name="nama_ibu" 
                                       value="<?= old('nama_ibu', $siswa['nama_ibu']) ?>" placeholder="Nama lengkap ibu">
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="alamat">Alamat Lengkap</label>
                        <textarea class="form-control" id="alamat" name="alamat" rows="3" 
                                  placeholder="Masukkan alamat lengkap"><?= old('alamat', $siswa['alamat']) ?></textarea>
                    </div>

                    <div class="form-group">
                        <label for="no_hp">No HP / WhatsApp (Opsional)</label>
                        <input type="text" class="form-control" id="no_hp" name="no_hp" 
                               value="<?= old('no_hp', $siswa['no_hp'] ?? '') ?>" placeholder="08xxxxxxxxxx">
                    </div>

                    <hr>

                    <!-- Status -->
                    <h5 class="text-warning mb-3">
                        <i class="fas fa-info-circle mr-2"></i>
                        Status Akademik
                    </h5>
                    <div class="form-group">
                        <label for="status">Status Siswa</label>
                        <select class="form-control" id="status" name="status">
                            <option value="aktif" <?= old('status', $siswa['status']) == 'aktif' ? 'selected' : '' ?>>Aktif</option>
                            <option value="nonaktif" <?= old('status', $siswa['status']) == 'nonaktif' ? 'selected' : '' ?>>Non-Aktif</option>
                            <option value="lulus" <?= old('status', $siswa['status']) == 'lulus' ? 'selected' : '' ?>>Lulus</option>
                            <option value="pindah" <?= old('status', $siswa['status']) == 'pindah' ? 'selected' : '' ?>>Pindah</option>
                        </select>
                    </div>

                </div>
                
                <div class="card-footer">
                    <div class="d-flex justify-content-between">
                        <a href="<?= base_url('backend/siswa') ?>" class="btn btn-secondary">
                            <i class="fas fa-arrow-left mr-1"></i> Kembali
                        </a>
                        <button type="submit" class="btn btn-warning">
                            <i class="fas fa-save mr-1"></i> Perbarui
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
                    <i class="fas fa-check"></i> Simpan Foto
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
<!-- Image Helper -->
<script src="<?= base_url('js/image-upload-helper.js') ?>"></script>

<script>
    var bs_modal = $('#modalCrop');
    var image = document.getElementById('image-to-crop');
    var cropper = null;

    // Edit Foto Button - Crop existing photo
    $('#btnEditFoto').click(function() {
        var currentPhoto = $('#previewPhoto').attr('src');
        image.src = currentPhoto;
        bs_modal.modal('show');
    });

    // Upload Foto Baru Button
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

    // Crop Button Click
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

        canvas.toBlob(function(blob) {
            var reader = new FileReader();
            reader.readAsDataURL(blob);
            reader.onloadend = function() {
                var base64data = reader.result;
                
                // Show Loading
                Swal.fire({
                    title: 'Menyimpan Foto...',
                    text: 'Mohon tunggu sebentar',
                    allowOutsideClick: false,
                    showConfirmButton: false,
                    didOpen: function() {
                        Swal.showLoading();
                    }
                });

                // AJAX ke Controller
                $.ajax({
                    type: "POST",
                    dataType: "json",
                    url: "<?= base_url('backend/siswa/updateFoto') ?>",
                    data: {
                        photo_cropped: base64data,
                        id_siswa: '<?= $siswa['id'] ?>',
                        <?= csrf_token() ?>: '<?= csrf_hash() ?>'
                    },
                    success: function(response) {
                        Swal.close();
                        bs_modal.modal('hide');
                        
                        if(response.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Berhasil',
                                text: response.message,
                                timer: 1500,
                                showConfirmButton: false
                            });
                            // Update Image Preview with cache bust
                            $('#previewPhoto').attr('src', response.foto_url + '?t=' + Date.now());
                        } else {
                            Swal.fire('Gagal', response.message, 'error');
                        }
                    },
                    error: function(xhr, status, error) {
                        Swal.close();
                        Swal.fire('Error', 'Terjadi kesalahan sistem: ' + error, 'error');
                        console.error(error);
                    }
                });
            };
        }, 'image/jpeg', 0.9);
    });
</script>
<?= $this->endSection(); ?>
