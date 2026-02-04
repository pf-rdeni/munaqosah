<?= $this->extend('backend/template/template'); ?>

<?= $this->section('content'); ?>
<!-- Halaman Profil User -->
<!-- Cropper CSS -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.css" crossorigin="anonymous" referrerpolicy="no-referrer" />
<style>
    .img-container img {
        max-width: 100%;
    }
</style>

<div class="row">
    <div class="col-md-6 offset-md-3">
        <div class="card card-primary">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-user-edit mr-2"></i>
                    Update Profil Saya
                </h3>
            </div>
            
            <form action="<?= base_url('backend/profil/update') ?>" method="post">
                <?= csrf_field() ?>
                
                <div class="card-body">
                    <!-- Alert Validasi Error -->
                    <?php if (session()->getFlashdata('errors')): ?>
                        <div class="alert alert-danger alert-dismissible fade show">
                            <button type="button" class="close" data-dismiss="alert">&times;</button>
                            <h5><i class="icon fas fa-ban"></i> Kesalahan</h5>
                            <ul class="mb-0">
                                <?php foreach (session()->getFlashdata('errors') as $error): ?>
                                    <li><?= esc($error) ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>

                    <?php if (session()->getFlashdata('success')): ?>
                        <div class="alert alert-success alert-dismissible fade show">
                            <button type="button" class="close" data-dismiss="alert">&times;</button>
                            <i class="icon fas fa-check"></i> <?= session()->getFlashdata('success') ?>
                        </div>
                    <?php endif; ?>

                    <!-- Foto Profil -->
                    <div class="text-center mb-4">
                        <?php 
                        $fotoUrl = (!empty($userData['user_image']) && file_exists(FCPATH . $userData['user_image'])) 
                            ? base_url($userData['user_image']) . '?t=' . time() 
                            : 'https://ui-avatars.com/api/?name=' . urlencode($userData['fullname'] ?? $userData['username']) . '&background=random&size=200';
                        ?>
                        <img id="previewPhoto" 
                             src="<?= $fotoUrl ?>" 
                             class="profile-user-img img-fluid img-circle"
                             style="width: 150px; height: 150px; object-fit: cover; border: 3px solid #007bff;"
                             onerror="this.onerror=null;this.src='https://ui-avatars.com/api/?name=<?= urlencode($userData['fullname'] ?? $userData['username']) ?>&background=random&size=200'">
                        
                        <div class="mt-3">
                            <button type="button" class="btn btn-info btn-sm" id="btnEditFoto">
                                <i class="fas fa-edit mr-1"></i> Edit Foto
                            </button>
                            <button type="button" class="btn btn-primary btn-sm" id="btnUploadFoto">
                                <i class="fas fa-upload mr-1"></i> Ganti Foto
                            </button>
                            <input type="file" id="inputPhoto" accept="image/png, image/jpeg, image/jpg" style="display: none;">
                        </div>
                    </div>
                    
                    <!-- Username (Read Only) -->
                    <div class="form-group">
                        <label for="username">Username</label>
                        <input type="text" class="form-control" id="username" value="<?= esc($userData['username']) ?>" disabled>
                    </div>

                    <!-- Nama Lengkap -->
                    <div class="form-group">
                        <label for="fullname">Nama Lengkap <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="fullname" name="fullname" 
                               value="<?= old('fullname', $userData['fullname']) ?>" placeholder="Nama Lengkap" required>
                    </div>

                    <hr>
                    <p class="text-muted text-sm"><i class="fas fa-lock mr-1"></i> Ganti Password (Opsional)</p>
                    
                    <!-- Password Baru -->
                    <div class="form-group">
                        <label for="password">Password Baru</label>
                        <input type="password" class="form-control" id="password" name="password" placeholder="Kosongkan jika tidak ingin mengubah password">
                    </div>

                    <!-- Konfirmasi Password -->
                    <div class="form-group">
                        <label for="pass_confirm">Konfirmasi Password</label>
                        <input type="password" class="form-control" id="pass_confirm" name="pass_confirm" placeholder="Ulangi password baru">
                    </div>

                </div>
                
                <div class="card-footer text-right">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save mr-1"></i> Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Crop Photo Profil -->
<!-- (Duplicated from Siswa module) -->
<div class="modal fade" id="modalCrop" tabindex="-1" role="dialog" aria-labelledby="modalCropLabel" aria-hidden="true" data-backdrop="static" data-keyboard="false">
    <div class="modal-dialog modal-lg modal-dialog-scrollable modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="modalCropLabel">
                    <i class="fas fa-crop"></i> Crop Foto Profil
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
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
                                <i class="fas fa-search-plus"></i>
                            </button>
                            <button type="button" class="btn btn-outline-primary" id="btnZoomOut" title="Zoom Out">
                                <i class="fas fa-search-minus"></i>
                            </button>
                            <button type="button" class="btn btn-outline-primary" id="btnMove" title="Geser">
                                <i class="fas fa-arrows-alt"></i>
                            </button>
                            <button type="button" class="btn btn-outline-secondary" id="btnReset" title="Reset">
                                <i class="fas fa-redo"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-primary" id="cropBtn">
                    <i class="fas fa-check"></i> Simpan
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
</style>

<?= $this->endSection(); ?>

<?= $this->section('scripts'); ?>
<!-- Cropper JS -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.js"></script>

<script>
    var bs_modal = $('#modalCrop');
    var image = document.getElementById('image-to-crop');
    var cropper = null;

    // Edit Foto Button - Crop existing photo
    $('#btnEditFoto').click(function() {
        var currentPhoto = $('#previewPhoto').attr('src');
        if(currentPhoto.includes('ui-avatars.com')) {
            Swal.fire('Info', 'Silakan upload foto baru terlebih dahulu.', 'info');
            return;
        }
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
            aspectRatio: 1, // 1:1 for User Profile (Circle)
            viewMode: 1,
            dragMode: 'move',
            autoCropArea: 0.8,
            responsive: true,
            center: true,
        });
    }).on('hidden.bs.modal', function() {
        if (cropper) {
            cropper.destroy();
            cropper = null;
        }
        $('#inputPhoto').val('');
    });

    // Zoom & Move Controls
    $('#btnZoomIn').click(function() { if (cropper) cropper.zoom(0.1); });
    $('#btnZoomOut').click(function() { if (cropper) cropper.zoom(-0.1); });
    $('#btnMove').click(function() { if (cropper) cropper.setDragMode('move'); });
    $('#btnReset').click(function() { if (cropper) cropper.reset(); });

    // Crop Button Click
    $("#cropBtn").click(function() {
        if (!cropper) return;

        var canvas = cropper.getCroppedCanvas({
            width: 400,
            height: 400,
        });

        canvas.toBlob(function(blob) {
            var reader = new FileReader();
            reader.readAsDataURL(blob);
            reader.onloadend = function() {
                var base64data = reader.result;
                
                Swal.fire({
                    title: 'Menyimpan Foto...',
                    allowOutsideClick: false,
                    didOpen: () => { Swal.showLoading(); }
                });

                $.ajax({
                    type: "POST",
                    dataType: "json",
                    url: "<?= base_url('backend/profil/updateFoto') ?>",
                    data: {
                        photo_cropped: base64data,
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
                            $('#previewPhoto').attr('src', response.foto_url + '?t=' + Date.now());
                        } else {
                            Swal.fire('Gagal', response.message, 'error');
                        }
                    },
                    error: function(xhr, status, error) {
                        Swal.close();
                        Swal.fire('Error', 'Terjadi kesalahan sistem', 'error');
                    }
                });
            };
        }, 'image/jpeg', 0.9);
    });
</script>
<?= $this->endSection(); ?>
