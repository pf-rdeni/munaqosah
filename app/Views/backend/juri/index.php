<?= $this->extend('backend/template/template'); ?>

<?= $this->section('content'); ?>
<div class="row mb-3">
    <!-- STATS CARD -->
    <div class="col-md-6">
        <div class="card card-info card-outline">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-chart-pie mr-1"></i> Info Statistik Juri</h3>
                <div class="card-tools">
                    <button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-minus"></i></button>
                </div>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-6">
                        <div class="info-box shadow-none bg-light">
                            <span class="info-box-icon"><i class="fas fa-users text-info"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Total Juri</span>
                                <span class="info-box-number"><?= $stats['total_juri'] ?? 0 ?></span>
                            </div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="info-box shadow-none bg-light">
                            <span class="info-box-icon"><i class="fas fa-layer-group text-success"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Total Grup</span>
                                <span class="info-box-number"><?= $stats['total_grup'] ?? 0 ?></span>
                            </div>
                        </div>
                    </div>
                </div>
                <hr>
                <h6>Juri per Grup Materi:</h6>
                <div style="max-height: 150px; overflow-y: auto;">
                    <ul class="list-group list-group-flush list-group-sm">
                        <?php if(!empty($stats['detail_grup'])): ?>
                            <?php foreach($stats['detail_grup'] as $s): ?>
                                <li class="list-group-item d-flex justify-content-between align-items-center py-1">
                                    <?= esc($s['nama_grup_materi']) ?>
                                    <span class="badge badge-info badge-pill"><?= $s['jumlah'] ?></span>
                                </li>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <li class="list-group-item text-muted">Belum ada data</li>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <!-- MANUAL FORM CARD -->
    <div class="col-md-6">
        <div class="card card-primary card-outline">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-file-alt mr-1"></i> Download Form Manual</h3>
                <div class="card-tools">
                     <button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-minus"></i></button>
                </div>
            </div>
            <div class="card-body">
                <p class="text-muted">Form penilaian manual (backup) berbentuk tabel kosong sesuai kriteria.</p>
                
                <div class="form-group">
                    <label>Pilih Grup Materi:</label>
                    <select class="form-control" id="selectGrupMateriForm">
                        <option value="">-- Pilih Grup Materi --</option>
                        <?php foreach($grupMateriList as $gm): ?>
                            <option value="<?= $gm['id'] ?>"><?= esc($gm['nama_grup_materi']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Preview Table Container -->
                <div id="previewFormContainer" class="mb-3 d-none">
                     <div class="table-responsive" style="max-height: 200px; border: 1px solid #ddd;">
                        <table class="table table-bordered table-sm table-head-fixed text-nowrap" id="previewTable">
                            <thead>
                                <tr id="previewHeaderRow">
                                    <!-- Dynamic Headers -->
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Only show 1 example row -->
                                <tr><td colspan="5" class="text-center text-muted"><em>Preview (Header Saja)</em></td></tr>
                            </tbody>
                        </table>
                     </div>
                </div>

                <div class="btn-group w-100">
                    <button type="button" class="btn btn-default" id="btnPreviewForm" onclick="previewForm()">
                        <i class="fas fa-eye mr-1"></i> Preview
                    </button>
                    <button type="button" class="btn btn-danger" onclick="downloadForm('pdf')">
                        <i class="fas fa-file-pdf mr-1"></i> PDF
                    </button>
                    <button type="button" class="btn btn-success" onclick="downloadForm('excel')">
                        <i class="fas fa-file-excel mr-1"></i> Excel
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-users mr-2"></i>
                    Data Juri
                </h3>
                <div class="card-tools">
                    <a href="<?= base_url('backend/juri/create') ?>" class="btn btn-primary btn-sm">
                        <i class="fas fa-plus mr-1"></i> Tambah Juri
                    </a>
                </div>
            </div>
            <div class="card-body">
                <!-- Alert Success Special for Juri Credential -->
                <?php if (session()->getFlashdata('success')): ?>
                    <div class="alert alert-success alert-dismissible fade show">
                        <button type="button" class="close" data-dismiss="alert">&times;</button>
                        <i class="icon fas fa-check"></i> <?= session()->getFlashdata('success') ?>
                    </div>
                <?php endif; ?>

                <table id="tabelJuri" class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th width="5%">No</th>
                            <th width="8%">Foto</th>
                            <th>Nama Juri</th>
                            <th>Grup Juri</th>
                            <th>Materi & Kriteria</th>
                            <th class="text-center" style="min-width: 280px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $no = 1; foreach ($juriList as $juri): ?>
                            <tr>
                                <td class="text-center"><?= $no++ ?></td>
                                <td class="text-center">
                                    <?php 
                                        // LOGIC AVATAR
                                        // 1. Check if user_image exists
                                        if (!empty($juri['user_image']) && file_exists(FCPATH . $juri['user_image'])) {
                                            $fotoUrl = base_url($juri['user_image']);
                                        } else {
                                            // 2. Generate Avatar Code
                                            // Prefer id_juri if available (e.g., JPS01, TT01)
                                            $avatarCode = $juri['id_juri'] ?? '';
                                            
                                            // FIX: Remove leading zero from number (JPS04 -> JPS4)
                                            if (!empty($avatarCode)) {
                                                $avatarCode = preg_replace('/([A-Za-z]+)0+(\d+)/', '$1$2', $avatarCode);
                                            }
                                            
                                            if (empty($avatarCode)) {
                                                // Fallback: Initials from Nama Grup Materi + Number from Username
                                                $grupName = $juri['nama_grup_materi'] ?? '';
                                                $words = explode(' ', $grupName);
                                                $initials = '';
                                                foreach($words as $w) {
                                                    $initials .= strtoupper(substr($w, 0, 1));
                                                }
                                                
                                                // Rule: Single letter -> Double it (T -> TT)
                                                if (strlen($initials) === 1) {
                                                    $initials .= $initials;
                                                }

                                                // Number from Username (last part)
                                                // username format: juri_tahfidz_1
                                                $parts = explode('_', $juri['username']);
                                                $number = end($parts);
                                                if (!is_numeric($number)) $number = '';

                                                $avatarCode = $initials . $number;
                                            }

                                            // Ensure length is sufficient for 3-4 chars
                                            $length = strlen($avatarCode);
                                            if ($length < 2) $length = 2; // Min length

                                            $fotoUrl = 'https://ui-avatars.com/api/?name=' . urlencode($avatarCode) . '&background=random&size=200&length=' . $length . '&font-size=0.35';
                                        }

                                        $fotoUrlLarge = $fotoUrl; // For modal view
                                    ?>
                                    <img src="<?= $fotoUrl ?>" 
                                         alt="Foto" 
                                         class="img-rounded" 
                                         style="width: 30px; height: 40px; object-fit: cover; cursor: pointer;" 
                                         data-id="<?= $juri['id'] ?>"
                                         data-nama="<?= esc($juri['nama_juri']) ?>"
                                         data-foto="<?= $fotoUrlLarge ?>"
                                         ondblclick="openViewPhotoModal(this)"
                                         title="Double-klik untuk edit foto">
                                </td>
                                <td>
                                    <strong><?= esc($juri['nama_juri']) ?></strong><br>
                                    <code class="text-muted"><?= esc($juri['username']) ?></code>
                                </td>
                                <td>
                                    <?php 
                                        $grupId = $juri['id_grup_juri'] ?? 0;
                                        $badges = [
                                            0 => 'badge-secondary',
                                            1 => 'badge-primary',
                                            2 => 'badge-success',
                                            3 => 'badge-danger',
                                            4 => 'badge-warning',
                                            5 => 'badge-info',
                                            6 => 'badge-indigo',
                                            7 => 'badge-lightblue',
                                            8 => 'badge-navy',
                                            9 => 'badge-purple',
                                            10 => 'badge-pink',
                                            11 => 'badge-primary', 12 => 'badge-success', 13=>'badge-danger', 14=>'badge-warning', 15=>'badge-info',
                                            16 => 'badge-indigo', 17 => 'badge-lightblue', 18=>'badge-navy', 19=>'badge-purple', 20=>'badge-pink'
                                        ];
                                        $badgeClass = $badges[$grupId] ?? 'badge-secondary';
                                        $grupName = ($grupId > 0) ? "Grup $grupId" : "Tanpa Grup";
                                    ?>
                                    <span class="badge <?= $badgeClass ?>" style="font-size: 1em;"><?= $grupName ?></span>
                                </td>
                                <td>
                                    <h6 class="font-weight-bold text-info mb-2"><?= esc($juri['nama_grup_materi'] ?? '-') ?></h6>
                                    
                                    <?php if (!empty($juri['kriteria_custom']) && !empty($juri['kriteria_names'])): ?>
                                        <div style="line-height:1.6;">
                                        <?php foreach ($juri['kriteria_names'] as $kName): ?>
                                            <span class="badge badge-light border border-secondary text-secondary font-weight-normal mr-1"><?= esc($kName) ?></span>
                                        <?php endforeach; ?>
                                        </div>
                                        <small class="text-muted d-block mt-1"><i class="fas fa-filter mr-1"></i> (<?= $juri['kriteria_count'] ?>/<?= $juri['kriteria_total'] ?> Kriteria)</small>
                                    <?php else: ?>
                                        <span class="badge badge-success"><i class="fas fa-check mr-1"></i> Semua Kriteria</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <div class="btn-group btn-group-sm">
                                        <a href="<?= base_url('backend/juri/edit/' . $juri['id']) ?>" class="btn btn-warning" title="Edit">
                                            <i class="fas fa-edit mr-1"></i>Edit
                                        </a>
                                        <button type="button" class="btn btn-info btn-set-kriteria" 
                                                data-id="<?= $juri['id'] ?>"
                                                data-nama="<?= esc($juri['nama_juri']) ?>"
                                                title="Setting Kriteria">
                                            <i class="fas fa-cog mr-1"></i>Set
                                        </button>
                                        <button type="button" class="btn btn-primary btn-set-grup"
                                                data-id="<?= $juri['id'] ?>"
                                                data-nama="<?= esc($juri['nama_juri']) ?>"
                                                data-grup="<?= $juri['id_grup_juri'] ?? 0 ?>"
                                                title="Setting Grup Juri">
                                            <i class="fas fa-users mr-1"></i>Grup
                                        </button>
                                        <button type="button" class="btn btn-secondary" onclick="confirmReset('<?= base_url('backend/juri/reset-password/' . $juri['id']) ?>')" title="Reset Password">
                                            <i class="fas fa-key"></i>
                                        </button>
                                        <button type="button" class="btn btn-danger" onclick="confirmDelete('<?= base_url('backend/juri/delete/' . $juri['id']) ?>')" title="Hapus">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal Setting Kriteria Juri -->
<div class="modal fade" id="modalSetKriteria" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title"><i class="fas fa-cog"></i> Setting Kriteria Juri</h5>
                <button type="button" class="close text-white" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <form id="formSetKriteria">
                <div class="modal-body">
                    <input type="hidden" name="juri_id" id="kriteriaJuriId">
                    
                    <div class="alert alert-info">
                        <strong>Juri:</strong> <span id="kriteriaJuriNama"></span>
                    </div>
                    
                    <div class="form-group">
                        <div class="custom-control custom-checkbox mb-3">
                            <input type="checkbox" class="custom-control-input" id="useDefaultKriteria" name="use_default" value="1">
                            <label class="custom-control-label" for="useDefaultKriteria">
                                <strong>Semua Kriteria (Default)</strong>
                            </label>
                            <small class="form-text text-muted">Juri akan menilai semua kriteria cabang lomba</small>
                        </div>
                    </div>
                    
                    <hr>
                    
                    <div id="kriteriaListContainer">
                        <p class="text-center"><i class="fas fa-spinner fa-spin"></i> Memuat kriteria...</p>
                    </div>
                    
                    <div id="kriteriaInfo" class="mt-3" style="display: none;">
                        <span class="badge badge-info">Dipilih: <span id="selectedCount">0</span> / <span id="totalCount">0</span></span>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-info">
                        <i class="fas fa-save"></i> Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Setting Grup Juri -->
<div class="modal fade" id="modalSetGrupJuri" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-sm" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title"><i class="fas fa-users"></i> Set Grup Juri</h5>
                <button type="button" class="close text-white" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <form id="formSetGrupJuri">
                <div class="modal-body">
                    <input type="hidden" name="juri_id" id="grupJuriId">
                    
                    <div class="alert alert-info py-2 px-3 mb-3">
                        <small>Juri:</small><br>
                        <strong id="grupJuriNama"></strong>
                    </div>
                    
                    <div class="form-group">
                        <label>Pilih Grup Juri (1-20)</label>
                        <select class="form-control" name="id_grup_juri" id="idGrupJuriSelect">
                            <option value="0">Tanpa Grup</option>
                            <?php for($i=1; $i<=20; $i++): ?>
                                <option value="<?= $i ?>">Grup <?= $i ?></option>
                            <?php endfor; ?>
                        </select>
                        <small class="text-muted">Grup ini digunakan untuk logika giliran/statistik.</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary btn-sm">
                        <i class="fas fa-save"></i> Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal View Photo Profil -->
<div class="modal fade" id="modalViewPhoto" tabindex="-1" role="dialog" aria-labelledby="modalViewPhotoLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="modalViewPhotoLabel">
                    <i class="fas fa-image"></i> Foto Profil Juri
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="text-center mb-3">
                    <h6 id="viewPhotoNamaJuri" class="mb-3"></h6>
                    <img id="viewPhotoImage" src="" alt="Foto Profil"
                        class="img-thumbnail mx-auto d-block"
                        style="max-width: 100%; max-width: 300px; height: auto; min-height: 400px; object-fit: cover;">
                    <input type="hidden" id="viewPhotoIdJuri" value="">
                </div>
            </div>
            <div class="modal-footer justify-content-center">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">
                    <i class="fas fa-times"></i> Tutup
                </button>
                <button type="button" class="btn btn-warning" id="btnEditPhotoFromView">
                    <i class="fas fa-edit"></i> Edit Foto
                </button>
                <button type="button" class="btn btn-primary" id="btnUploadNewPhoto">
                    <i class="fas fa-upload"></i> Upload Foto Baru
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Crop Photo Profil -->
<div class="modal fade" id="modalCropPhoto" tabindex="-1" role="dialog" aria-labelledby="modalCropPhotoLabel" aria-hidden="true" data-backdrop="static" data-keyboard="false">
    <div class="modal-dialog modal-lg modal-dialog-scrollable modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="modalCropPhotoLabel">
                    <i class="fas fa-crop"></i> Crop Foto Profil Juri
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
                            <img id="imageToCrop" src="" alt="Foto untuk di-crop" style="max-width: 100%; display: block;">
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
                <input type="hidden" id="cropPhotoIdJuri" value="">
                <input type="file" id="inputPhotoUpload" accept="image/png, image/jpeg, image/jpg" style="display: none;">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">
                    <i class="fas fa-times"></i> Batal
                </button>
                <button type="button" class="btn btn-primary" id="btnSaveCroppedPhoto">
                    <i class="fas fa-check"></i> Simpan Foto
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Cropper CSS -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.css" crossorigin="anonymous" referrerpolicy="no-referrer" />
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
    #modalCropPhoto .modal-body {
        max-height: calc(100vh - 200px);
        overflow-y: auto;
    }
</style>

<?= $this->endSection(); ?>

<?= $this->section('scripts'); ?>
<!-- Cropper JS -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.js"></script>

<script>
    $(document).ready(function() {
        $('#tabelJuri').DataTable({
            responsive: true,
            autoWidth: false,
            columnDefs: [
                { orderable: false, targets: [1, 5] } 
            ]
        });

        // ==================== GRUP JURI LOGIC ====================
        $(document).on('click', '.btn-set-grup', function() {
            var id = $(this).data('id');
            var nama = $(this).data('nama');
            var grup = $(this).data('grup'); // 0 if null

            $('#grupJuriId').val(id);
            $('#grupJuriNama').text(nama);
            $('#idGrupJuriSelect').val(grup);
            
            $('#modalSetGrupJuri').modal('show');
        });

        $('#formSetGrupJuri').submit(function(e) {
            e.preventDefault();
            var btn = $(this).find('button[type="submit"]');
            btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i>');

            $.ajax({
                url: '<?= base_url('backend/juri/updateGrupJuri') ?>',
                type: 'POST',
                data: $(this).serialize(),
                success: function(res) {
                    if(res.success) {
                        $('#modalSetGrupJuri').modal('hide');
                        Swal.fire({
                            icon: 'success', 
                            title: 'Sukses', 
                            text: res.message,
                            timer: 1500,
                            showConfirmButton: false
                        }).then(() => location.reload());
                    } else {
                        Swal.fire('Gagal', res.message, 'error');
                        btn.prop('disabled', false).html('<i class="fas fa-save"></i> Simpan');
                    }
                },
                error: function() {
                    Swal.fire('Error', 'Terjadi kesalahan sistem', 'error');
                    btn.prop('disabled', false).html('<i class="fas fa-save"></i> Simpan');
                }
            });
        });
    });

    // Confirm Delete
    function confirmDelete(url) {
        Swal.fire({
            title: 'Yakin hapus juri ini?',
            text: 'Data yang dihapus tidak dapat dikembalikan!',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            confirmButtonText: 'Ya, Hapus!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = url;
            }
        });
    }

    // Confirm Reset Password
    function confirmReset(url) {
        Swal.fire({
            title: 'Reset Password?',
            text: 'Password akan direset ke default: JuriMunaqosah123',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Ya, Reset!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = url;
            }
        });
    }

    // ==================== KRITERIA SETTINGS ====================
    
    // Open Kriteria Modal
    $(document).on('click', '.btn-set-kriteria', function() {
        var juriId = $(this).data('id');
        var juriNama = $(this).data('nama');
        
        $('#kriteriaJuriId').val(juriId);
        $('#kriteriaJuriNama').text(juriNama);
        $('#kriteriaListContainer').html('<p class="text-center"><i class="fas fa-spinner fa-spin"></i> Memuat kriteria...</p>');
        $('#kriteriaInfo').hide();
        $('#useDefaultKriteria').prop('checked', false);
        
        // Load kriteria
        $.ajax({
            url: '<?= base_url('backend/juri/getJuriKriteria') ?>/' + juriId,
            type: 'GET',
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    renderKriteriaList(response.data);
                } else {
                    $('#kriteriaListContainer').html('<div class="alert alert-danger">' + response.message + '</div>');
                }
            },
            error: function() {
                $('#kriteriaListContainer').html('<div class="alert alert-danger">Gagal memuat data</div>');
            }
        });
        
        $('#modalSetKriteria').modal('show');
    });
    
    function renderKriteriaList(data) {
        if (data.kriteria.length === 0) {
            $('#kriteriaListContainer').html('<div class="alert alert-warning">Belum ada kriteria untuk materi ini. Silakan tambah kriteria terlebih dahulu.</div>');
            return;
        }
        
        var html = '<div class="list-group">';
        var hasCustom = data.has_custom_setting;
        
        data.kriteria.forEach(function(k) {
            var checked = hasCustom ? (k.assigned ? 'checked' : '') : '';
            var disabled = k.used_by_others && !k.assigned ? 'disabled' : '';
            var labelClass = k.used_by_others && !k.assigned ? 'list-group-item bg-light text-muted' : 'list-group-item';
            
            html += '<label class="' + labelClass + '">';
            html += '<input type="checkbox" class="kriteria-check" name="kriteria_ids[]" value="' + k.id + '" ' + checked + ' ' + disabled + '> ';
            html += '<strong>' + k.nama_kriteria + '</strong> ';
            html += '<span class="badge badge-secondary">' + k.bobot + '%</span>';
            
            // Tampilkan info juri yang menggunakan kriteria ini
            if (k.used_by_others && !k.assigned) {
                html += ' <span class="badge badge-warning ml-2"><i class="fas fa-user"></i> ' + k.used_by + '</span>';
            }
            
            html += '</label>';
        });
        html += '</div>';
        
        $('#kriteriaListContainer').html(html);
        $('#totalCount').text(data.total_count);
        $('#kriteriaInfo').show();
        
        // Set default checkbox jika tidak ada custom setting
        if (!hasCustom) {
            $('#useDefaultKriteria').prop('checked', true);
            $('.kriteria-check:not(:disabled)').prop('disabled', true);
        }
        
        updateSelectedCount();
    }
    
    function updateSelectedCount() {
        var count = $('.kriteria-check:checked').length;
        $('#selectedCount').text(count);
    }
    
    // Toggle kriteria checkboxes when default is checked
    $('#useDefaultKriteria').change(function() {
        if ($(this).is(':checked')) {
            $('.kriteria-check').prop('disabled', true).prop('checked', false);
        } else {
            $('.kriteria-check').prop('disabled', false);
        }
        updateSelectedCount();
    });
    
    // Update count on checkbox change
    $(document).on('change', '.kriteria-check', function() {
        updateSelectedCount();
        // Uncheck default if any kriteria is manually checked
        if ($('.kriteria-check:checked').length > 0) {
            $('#useDefaultKriteria').prop('checked', false);
        }
    });
    
    // Submit Kriteria Form
    $('#formSetKriteria').submit(function(e) {
        e.preventDefault();
        
        var btn = $(this).find('button[type="submit"]');
        btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Menyimpan...');
        
        $.ajax({
            url: '<?= base_url('backend/juri/saveJuriKriteria') ?>',
            type: 'POST',
            data: $(this).serialize(),
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    $('#modalSetKriteria').modal('hide');
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil',
                        text: response.message,
                        timer: 1500,
                        showConfirmButton: false
                    }).then(() => location.reload());
                } else {
                    Swal.fire('Gagal', response.message, 'error');
                }
                btn.prop('disabled', false).html('<i class="fas fa-save"></i> Simpan');
            },
            error: function() {
                Swal.fire('Error', 'Terjadi kesalahan', 'error');
                btn.prop('disabled', false).html('<i class="fas fa-save"></i> Simpan');
            }
        });
    });

    // =================== PHOTO VIEW/EDIT MODAL LOGIC ===================
    var cropperInstance = null;
    var currentPhotoIdJuri = null;

    // Open View Photo Modal (double-click on photo)
    function openViewPhotoModal(imgElement) {
        var id = $(imgElement).data('id');
        var nama = $(imgElement).data('nama');
        var foto = $(imgElement).data('foto');
        
        currentPhotoIdJuri = id;
        $('#viewPhotoIdJuri').val(id);
        $('#viewPhotoNamaJuri').text(nama);
        $('#viewPhotoImage').attr('src', foto);
        
        $('#modalViewPhoto').modal('show');
    }

    // Edit Foto Button - Open existing photo in cropper
    $('#btnEditPhotoFromView').click(function() {
        var foto = $('#viewPhotoImage').attr('src');
        var id = $('#viewPhotoIdJuri').val();
        
        $('#modalViewPhoto').modal('hide');
        
        // Set ID and open crop modal with existing image
        $('#cropPhotoIdJuri').val(id);
        $('#imageToCrop').attr('src', foto);
        $('#modalCropPhoto').modal('show');
    });

    // Upload New Photo Button
    $('#btnUploadNewPhoto').click(function() {
        $('#inputPhotoUpload').click();
    });

    // Handle file input change
    $('#inputPhotoUpload').on('change', function(e) {
        var files = e.target.files;
        if (files && files.length > 0) {
            // TRANSFER ID TO CROP MODAL
            var id = $('#viewPhotoIdJuri').val();
            $('#cropPhotoIdJuri').val(id);

            var file = files[0];
            var reader = new FileReader();
            reader.onload = function(e) {
                $('#modalViewPhoto').modal('hide');
                $('#imageToCrop').attr('src', e.target.result);
                $('#modalCropPhoto').modal('show');
            };
            reader.readAsDataURL(file);
        }
    });

    // Initialize Cropper when modal shown
    $('#modalCropPhoto').on('shown.bs.modal', function() {
        var image = document.getElementById('imageToCrop');
        if (cropperInstance) {
            cropperInstance.destroy();
        }
        cropperInstance = new Cropper(image, {
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
    });

    // Destroy Cropper when modal hidden
    $('#modalCropPhoto').on('hidden.bs.modal', function() {
        if (cropperInstance) {
            cropperInstance.destroy();
            cropperInstance = null;
        }
        // Reset file input
        $('#inputPhotoUpload').val('');
    });

    // Zoom In
    $('#btnZoomIn').click(function() {
        if (cropperInstance) cropperInstance.zoom(0.1);
    });

    // Zoom Out
    $('#btnZoomOut').click(function() {
        if (cropperInstance) cropperInstance.zoom(-0.1);
    });

    // Move/Drag Mode
    $('#btnMove').click(function() {
        if (cropperInstance) cropperInstance.setDragMode('move');
    });

    // Reset
    $('#btnReset').click(function() {
        if (cropperInstance) cropperInstance.reset();
    });

    // Save Cropped Photo
    $('#btnSaveCroppedPhoto').click(function() {
        if (!cropperInstance) {
            Swal.fire('Error', 'Cropper tidak tersedia', 'error');
            return;
        }
        
        var canvas = cropperInstance.getCroppedCanvas({
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
                
                // Ajax Upload
                $.ajax({
                    url: '<?= base_url('backend/juri/updateFoto') ?>',
                    method: 'POST',
                    data: {
                        id_juri: $('#cropPhotoIdJuri').val(),
                        image: base64data
                    },
                    success: function(response) {
                        if (response.success) {
                            $('#modalCropPhoto').modal('hide');
                            Swal.fire({
                                icon: 'success',
                                title: 'Berhasil',
                                text: response.message,
                                showConfirmButton: false,
                                timer: 1500
                            }).then(function() {
                                location.reload();
                            });
                        } else {
                            Swal.fire('Gagal', response.message, 'error');
                        }
                    },
                    error: function() {
                        Swal.fire('Error', 'Gagal mengupload gambar', 'error');
                    }
                });
            }
        });
    });
    // ==================== MANUAL FORM LOGIC ====================
    function downloadForm(type) {
        var id = $('#selectGrupMateriForm').val();
        if (!id) {
            Swal.fire('Peringatan', 'Silakan pilih Grup Materi terlebih dahulu', 'warning');
            return;
        }
        
        var url = '';
        if (type === 'pdf') {
            url = '<?= base_url('backend/juri/downloadManualFormPdf') ?>/' + id;
        } else {
            url = '<?= base_url('backend/juri/downloadManualFormExcel') ?>/' + id;
        }
        
        window.open(url, '_blank');
    }

    function previewForm() {
        var id = $('#selectGrupMateriForm').val();
         if (!id) {
            Swal.fire('Peringatan', 'Silakan pilih Grup Materi terlebih dahulu', 'warning');
            return;
        }

        Swal.fire({
            title: 'Info',
            text: 'Silakan download PDF/Excel untuk melihat form lengkap.',
            icon: 'info'
        });
    }
</script>
<?= $this->endSection(); ?>
