<?= $this->extend('backend/template/template'); ?>
<?= $this->section('content'); ?>
<section class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card card-primary">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-certificate"></i> Pengaturan Sertifikat Munaqosah</h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <!-- Template Depan -->
                            <div class="col-md-6">
                                <div class="card card-outline card-success h-100">
                                    <div class="card-header">
                                        <h3 class="card-title">Halaman Depan</h3>
                                    </div>
                                    <div class="card-body text-center">
                                        <?php if ($template_depan): ?>
                                            <div class="mb-3">
                                                <img src="<?= base_url('uploads/' . $template_depan['file_template']) ?>" 
                                                     class="img-fluid border" 
                                                     style="max-height: 200px;">
                                                <p class="mt-2 text-muted">
                                                    <?= $template_depan['width'] ?> x <?= $template_depan['height'] ?> px
                                                </p>
                                            </div>
                                            <div class="btn-group">
                                                <a href="<?= base_url('backend/sertifikat/configure/depan') ?>" class="btn btn-primary">
                                                    <i class="fas fa-cog"></i> Konfigurasi
                                                </a>
                                                <button class="btn btn-warning" onclick="openUploadModal('depan')">
                                                    <i class="fas fa-edit"></i> Ganti
                                                </button>
                                                 <a href="<?= base_url('backend/sertifikat/delete/depan') ?>" class="btn btn-danger" onclick="return confirm('Yakin hapus template ini?')">
                                                    <i class="fas fa-trash"></i> Hapus
                                                </a>
                                            </div>
                                        <?php else: ?>
                                            <div class="alert alert-warning">
                                                Belum ada template. Silakan upload.
                                            </div>
                                            <button class="btn btn-success" onclick="openUploadModal('depan')">
                                                <i class="fas fa-upload"></i> Upload Template Depan
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>

                            <!-- Template Belakang -->
                            <div class="col-md-6">
                                <div class="card card-outline card-info h-100">
                                    <div class="card-header">
                                        <h3 class="card-title">Halaman Belakang</h3>
                                    </div>
                                    <div class="card-body text-center">
                                        <?php if ($template_belakang): ?>
                                            <div class="mb-3">
                                                <img src="<?= base_url('uploads/' . $template_belakang['file_template']) ?>" 
                                                     class="img-fluid border" 
                                                     style="max-height: 200px;">
                                                <p class="mt-2 text-muted">
                                                    <?= $template_belakang['width'] ?> x <?= $template_belakang['height'] ?> px
                                                </p>
                                            </div>
                                            <div class="btn-group">
                                                <a href="<?= base_url('backend/sertifikat/configure/belakang') ?>" class="btn btn-primary">
                                                    <i class="fas fa-cog"></i> Konfigurasi
                                                </a>
                                                <button class="btn btn-warning" onclick="openUploadModal('belakang')">
                                                    <i class="fas fa-edit"></i> Ganti
                                                </button>
                                                <a href="<?= base_url('backend/sertifikat/delete/belakang') ?>" class="btn btn-danger" onclick="return confirm('Yakin hapus template ini?')">
                                                    <i class="fas fa-trash"></i> Hapus
                                                </a>
                                            </div>
                                        <?php else: ?>
                                            <div class="alert alert-warning">
                                                Belum ada template. Silakan upload.
                                            </div>
                                            <button class="btn btn-info" onclick="openUploadModal('belakang')">
                                                <i class="fas fa-upload"></i> Upload Template Belakang
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Upload -->
    <div class="modal fade" id="modalUpload" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Upload Template <span id="uploadTitle"></span></h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <form id="formUpload" enctype="multipart/form-data">
                    <div class="modal-body">
                        <input type="hidden" name="halaman" id="inputHalaman">
                        
                        <div class="form-group">
                            <label>File Template (JPG/PNG)</label>
                            <div class="custom-file">
                                <input type="file" class="custom-file-input" name="template_file" accept="image/jpeg,image/png" required>
                                <label class="custom-file-label">Pilih file...</label>
                            </div>
                            <small class="text-muted">Resolusi rekomendasi: 2000px width (Landscape) atau Height (Portrait)</small>
                        </div>

                        <div class="form-group">
                            <label>Orientasi</label>
                            <select class="form-control" name="orientation">
                                <option value="landscape">Landscape (Horizontal)</option>
                                <option value="portrait">Portrait (Vertical)</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Start Upload</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</section>
<?= $this->endSection(); ?>

<?= $this->section('scripts'); ?>
<script>
function openUploadModal(halaman) {
    $('#inputHalaman').val(halaman);
    $('#uploadTitle').text(halaman.charAt(0).toUpperCase() + halaman.slice(1));
    $('#modalUpload').modal('show');
}

// Custom file input
$('.custom-file-input').on('change', function() {
    let fileName = $(this).val().split('\\').pop();
    $(this).next('.custom-file-label').addClass("selected").html(fileName);
});

// Ajax Upload
$('#formUpload').submit(function(e) {
    e.preventDefault();
    var formData = new FormData(this);
    var btn = $(this).find('button[type="submit"]');
    var originalText = btn.text();
    
    btn.prop('disabled', true).text('Uploading...');

    $.ajax({
        url: '<?= base_url('backend/sertifikat/upload') ?>',
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                Swal.fire('Berhasil', response.message, 'success').then(() => location.reload());
            } else {
                Swal.fire('Gagal', response.message, 'error');
                btn.prop('disabled', false).text(originalText);
            }
        },
        error: function() {
            Swal.fire('Error', 'Terjadi kesalahan sistem', 'error');
            btn.prop('disabled', false).text(originalText);
        }
    });
});
</script>
<?= $this->endSection(); ?>
