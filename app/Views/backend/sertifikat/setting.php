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
                                                <img src="<?= base_url('writable/uploads/sertifikat/' . $template_depan['file_template']) ?>" 
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
                                                <img src="<?= base_url('writable/uploads/sertifikat/' . $template_belakang['file_template']) ?>" 
                                                     id="preview-belakang"
                                                     class="img-fluid border" 
                                                     style="max-height: 200px;">
                                                <p class="mt-2 text-muted" id="info-belakang">
                                                    <?= $template_belakang['width'] ?> x <?= $template_belakang['height'] ?> px
                                                </p>
                                            </div>
                                            <div class="mb-3">
                                                <label class="d-block text-muted small mb-2">Gaya Desain Isi:</label>
                                                <div class="btn-group btn-group-toggle" data-toggle="buttons">
                                                    <label class="btn btn-outline-primary btn-sm <?= ($template_belakang['design_style'] ?? 'option1') == 'option1' ? 'active' : '' ?>">
                                                        <input type="radio" name="design_style" value="option1" autocomplete="off" onchange="updateDesignStyle(<?= $template_belakang['id'] ?>, 'option1')" <?= ($template_belakang['design_style'] ?? 'option1') == 'option1' ? 'checked' : '' ?>> 
                                                        Opsi 1 (Ringkasan)
                                                    </label>
                                                    <label class="btn btn-outline-primary btn-sm <?= ($template_belakang['design_style'] ?? 'option1') == 'option2' ? 'active' : '' ?>">
                                                        <input type="radio" name="design_style" value="option2" autocomplete="off" onchange="updateDesignStyle(<?= $template_belakang['id'] ?>, 'option2')" <?= ($template_belakang['design_style'] ?? 'option1') == 'option2' ? 'checked' : '' ?>> 
                                                        Opsi 2 (Detail Bobot)
                                                    </label>
                                                </div>
                                            </div>
                                            <div class="btn-group">
                                                <a href="<?= base_url('backend/sertifikat/configure/' . $template_belakang['id']) ?>" class="btn btn-primary" id="btn-config-belakang">
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

// Data for switching views
var templates = {
    'option1': <?= json_encode($template_belakang_1) ?>,
    'option2': <?= json_encode($template_belakang_2) ?>
};

function updateDesignStyle(templateId, style) {
    // Current templateId in the call is legacy, relying on style now.
    
    $.post('<?= base_url('backend/sertifikat/save-design-style') ?>', {
        design_style: style
    }, function(response) {
        if (response.success) {
            Swal.fire({
                icon: 'success',
                title: 'Tersimpan',
                text: response.message,
                toast: true, // Use toast to be less intrusive
                position: 'top-end',
                showConfirmButton: false,
                timer: 2000
            });
            
            // Update UI
            var tmpl = templates[style];
            var imgUrl = '<?= base_url('assets/img/default.png') ?>';
            var infoText = 'Belum ada template';
            var configUrl = '#'; // Disable if no template
            
            if (tmpl && tmpl.file_template) {
                imgUrl = '<?= base_url('writable/uploads/sertifikat/') ?>' + tmpl.file_template;
                infoText = tmpl.width + ' x ' + tmpl.height + ' px';
                configUrl = '<?= base_url('backend/sertifikat/configure/') ?>' + tmpl.id; // Use ID explicitly
            }
            
            $('#preview-belakang').attr('src', imgUrl);
            $('#info-belakang').text(infoText);
            
            // Update Configure Button HREF
            $('#btn-config-belakang').attr('href', configUrl);
            
            // Reload page might be safer to ensure all context is correct, but let's try dynamic update
            // Actually, simply reloading after a short delay is robust.
            setTimeout(function() {
                location.reload(); 
            }, 1000);

        } else {
            Swal.fire('Gagal', response.message, 'error');
        }
    }).fail(function() {
         Swal.fire('Error', 'Gagal menghubungi server', 'error');
    });
}
</script>
<?= $this->endSection(); ?>
