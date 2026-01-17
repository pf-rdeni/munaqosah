<?= $this->extend('backend/template/template'); ?>

<?= $this->section('content'); ?>
<!-- Halaman Daftar Siswa -->

<!-- Tombol Aksi dan Filter -->
<div class="row mb-3">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-center">
                    <h3 class="card-title">
                        <i class="fas fa-users mr-2"></i>
                        Daftar Siswa SDIT An-Nahl
                    </h3>
                    <div class="card-tools">
                        <a href="<?= base_url('backend/siswa/create') ?>" class="btn btn-primary btn-sm">
                            <i class="fas fa-plus mr-1"></i> Tambah Siswa
                        </a>
                        <button type="button" class="btn btn-success btn-sm ml-2" data-toggle="modal" data-target="#modal-import">
                            <i class="fas fa-file-excel mr-1"></i> Import Excel
                        </button>
                    </div>
                </div>
            </div>
                <!-- Alert Validasi Error (Tetap ditampilkan karena berupa List) -->
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

                <!-- Tabel Data Siswa -->
                <div class="table-responsive">
                    <table id="tabelSiswa" class="table table-bordered table-striped table-hover">
                        <thead class="bg-light">
                            <tr>
                                <th width="5%">No</th>
                                <th width="8%">Foto</th>
                                <th width="12%">NISN</th>
                                <th width="25%">Nama Siswa</th>
                                <th width="8%">JK</th>
                                <th width="10%">Status</th>
                                <th width="20%">Hafalan</th>
                                <th width="15%">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($siswaList)): ?>
                                <?php $no = 1; foreach ($siswaList as $siswa): ?>
                                    <tr>
                                        <td class="text-center"><?= $no++ ?></td>
                                        <td class="text-center">
                                            <?php 
                                            $fotoUrl = (!empty($siswa['foto']) && file_exists(FCPATH . $siswa['foto'])) 
                                                ? base_url($siswa['foto']) 
                                                : 'https://ui-avatars.com/api/?name=' . urlencode($siswa['nama_siswa']) . '&background=random&size=200';
                                            $fotoUrlLarge = (!empty($siswa['foto']) && file_exists(FCPATH . $siswa['foto'])) 
                                                ? base_url($siswa['foto']) 
                                                : 'https://ui-avatars.com/api/?name=' . urlencode($siswa['nama_siswa']) . '&background=random&size=400';
                                            ?>
                                            <img src="<?= $fotoUrl ?>" 
                                                 alt="Foto" 
                                                 class="img-rounded img-siswa-profil" 
                                                 style="width: 30px; height: 40px; object-fit: cover; cursor: pointer;" 
                                                 data-id="<?= $siswa['id'] ?>"
                                                 data-nama="<?= esc($siswa['nama_siswa']) ?>"
                                                 data-foto="<?= $fotoUrlLarge ?>"
                                                 ondblclick="openViewPhotoModal(this)"
                                                 title="Double-klik untuk edit foto"
                                                 onerror="this.onerror=null;this.src='https://ui-avatars.com/api/?name=<?= urlencode($siswa['nama_siswa']) ?>&background=random&size=40'">
                                        </td>
                                        <td><?= esc($siswa['nisn']) ?></td>
                                        <td><?= esc($siswa['nama_siswa']) ?></td>
                                        <td class="text-center">
                                            <?php if ($siswa['jenis_kelamin'] == 'L'): ?>
                                                <span class="badge badge-info">Laki-laki</span>
                                            <?php else: ?>
                                                <span class="badge badge-pink" style="background-color: #e83e8c; color: white;">Perempuan</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-center">
                                            <?php
                                            $statusClass = [
                                                'aktif'    => 'success',
                                                'nonaktif' => 'secondary',
                                                'lulus'    => 'primary',
                                                'pindah'   => 'warning',
                                            ];
                                            $status = $siswa['status'] ?? 'aktif';
                                            ?>
                                            <span class="badge badge-<?= $statusClass[$status] ?? 'secondary' ?>">
                                                <?= ucfirst($status) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php 
                                            // Data sudah diproses di controller
                                            $hafalan = $siswa['processed_hafalan'] ?? [];
                                            if (!empty($hafalan)): 
                                                foreach($hafalan as $h):
                                            ?>
                                                <div class="mb-1 border-bottom pb-1">
                                                    <small class="d-block p-1 bg-light border rounded text-left font-weight-normal">
                                                        <strong>Juz</strong> <?= esc($h['juz']) ?>: 
                                                        <?= esc($h['display_mulai']) ?> - <?= esc($h['display_akhir']) ?>
                                                    </small>
                                                </div>
                                            <?php 
                                                endforeach;
                                            else: 
                                            ?>
                                                <small class="text-muted">- Belum diset -</small>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-center">
                                            <div class="btn-group" role="group">
                                                <button type="button" class="btn btn-info btn-sm btn-hafalan"
                                                        data-id="<?= $siswa['id'] ?>"
                                                        data-nama="<?= esc($siswa['nama_siswa']) ?>"
                                                        data-hafalan="<?= htmlspecialchars($siswa['hafalan'] ?? '[]', ENT_QUOTES, 'UTF-8') ?>"
                                                        data-toggle="modal" data-target="#modalHafalan"
                                                        title="Set Hafalan">
                                                    <i class="fas fa-book-open"></i>
                                                </button>
                                                <button type="button" class="btn btn-primary btn-sm btn-view-detail"
                                                        data-id="<?= $siswa['id'] ?>"
                                                        data-nisn="<?= esc($siswa['nisn']) ?>"
                                                        data-nama="<?= esc($siswa['nama_siswa']) ?>"
                                                        data-jenis-kelamin="<?= $siswa['jenis_kelamin'] == 'L' ? 'Laki-laki' : 'Perempuan' ?>"
                                                        data-tempat-lahir="<?= esc($siswa['tempat_lahir'] ?? '-') ?>"
                                                        data-tanggal-lahir="<?= !empty($siswa['tanggal_lahir']) ? date('d F Y', strtotime($siswa['tanggal_lahir'])) : '-' ?>"
                                                        data-alamat="<?= esc($siswa['alamat'] ?? '-') ?>"
                                                        data-nama-ayah="<?= esc($siswa['nama_ayah'] ?? '-') ?>"
                                                        data-nama-ibu="<?= esc($siswa['nama_ibu'] ?? '-') ?>"
                                                        data-no-hp="<?= esc($siswa['no_hp'] ?? '-') ?>"
                                                        data-status="<?= ucfirst($siswa['status'] ?? 'aktif') ?>"
                                                        data-foto="<?= $fotoUrlLarge ?>"
                                                        data-hafalan="<?= htmlspecialchars(json_encode($siswa['processed_hafalan'] ?? []), ENT_QUOTES, 'UTF-8') ?>"
                                                        title="Lihat Detail">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                                <a href="<?= base_url('backend/siswa/edit/' . $siswa['id']) ?>" 
                                                   class="btn btn-warning btn-sm" 
                                                   title="Edit">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <a href="<?= base_url('backend/siswa/delete/' . $siswa['id']) ?>" 
                                                   class="btn btn-danger btn-sm btn-delete" 
                                                   data-name="<?= esc($siswa['nama_siswa']) ?>"
                                                   title="Hapus">
                                                    <i class="fas fa-trash"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="7" class="text-center text-muted">
                                        <i class="fas fa-inbox fa-3x mb-3 d-block"></i>
                                        Belum ada data siswa. 
                                        <a href="<?= base_url('backend/siswa/create') ?>">Tambah siswa baru</a>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Import -->
<div class="modal fade" id="modal-import">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Import Data Siswa</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="<?= base_url('backend/siswa/import') ?>" method="post" enctype="multipart/form-data" id="form-import">
                <div class="modal-body">
                    <div class="form-group">
                        <label>Format Import</label>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="import_type" id="type_template" value="template" checked>
                            <label class="form-check-label" for="type_template">
                                Template Sistem (Sederhana)
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="import_type" id="type_dapodik" value="dapodik">
                            <label class="form-check-label" for="type_dapodik">
                                Format Dapodik (Original)
                            </label>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>File Excel</label>
                        <div class="custom-file">
                            <input type="file" class="custom-file-input" name="file_excel" id="file_excel" 
                                   accept=".xls,.xlsx" required>
                            <label class="custom-file-label" for="file_excel">Pilih file</label>
                        </div>
                        <small class="text-muted">Format file harus .xls atau .xlsx</small>
                    </div>
                    <div class="form-group" id="template-link-container">
                        <p>Belum punya template? <a href="<?= base_url('backend/siswa/downloadTemplate') ?>">Download Template</a></p>
                    </div>
                </div>
                <div class="modal-footer justify-content-between">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Tutup</button>
                    <button type="submit" class="btn btn-primary" id="btn-import-submit">
                        <i class="fas fa-upload mr-1"></i> Import
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Set Hafalan -->
<div class="modal fade" id="modalHafalan">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Set Hafalan Siswa</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="<?= base_url('backend/siswa/updateHafalan') ?>" method="post">
                <?= csrf_field() ?>
                <input type="hidden" name="id_siswa" id="hafalanIdSiswa">
                <div class="modal-body">
                    <div class="alert alert-info p-2">
                        <i class="fas fa-user mr-1"></i> Siswa: <strong id="hafalanNamaSiswa">-</strong>
                    </div>
                    
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered" id="tblHafalanInput">
                            <thead>
                                <tr class="bg-light">
                                    <th width="20%">Juz</th>
                                    <th width="35%">Mulai</th>
                                    <th width="35%">Akhir</th>
                                    <th width="10%">#</th>
                                </tr>
                            </thead>
                            <tbody id="containerHafalan">
                                <!-- Dynamic Rows -->
                            </tbody>
                        </table>
                    </div>
                    <button type="button" class="btn btn-success btn-xs" id="btnAddHafalan">
                        <i class="fas fa-plus"></i> Tambah Baris
                    </button>
                    
                    <small class="text-muted d-block mt-2">Pastikan tabel Al-Qur'an sudah terisi untuk melihat daftar surah.</small>
                </div>
                <div class="modal-footer justify-content-between">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan Hafalan</button>
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
                    <i class="fas fa-image"></i> Foto Profil Siswa
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="text-center mb-3">
                    <h6 id="viewPhotoNamaSiswa" class="mb-3"></h6>
                    <img id="viewPhotoImage" src="" alt="Foto Profil"
                        class="img-thumbnail mx-auto d-block"
                        style="max-width: 100%; max-width: 300px; height: auto; min-height: 400px; object-fit: cover;">
                    <input type="hidden" id="viewPhotoIdSiswa" value="">
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

<!-- Modal Detail Siswa -->
<div class="modal fade" id="modalDetailSiswa" tabindex="-1" role="dialog" aria-labelledby="modalDetailSiswaLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="modalDetailSiswaLabel">
                    <i class="fas fa-user"></i> Detail Siswa
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <!-- Left Column: Photo & Basic Info -->
                    <div class="col-md-4">
                        <div class="card card-primary card-outline">
                            <div class="card-body box-profile text-center">
                                <img id="detailFoto" class="profile-user-img img-fluid" src="" alt="Foto Profil"
                                     style="width: 150px; height: 200px; object-fit: cover; border: 3px solid #adb5bd; border-radius: 10px;">
                                <h5 id="detailNama" class="profile-username mt-3"></h5>
                                <p id="detailNisn" class="text-muted"></p>
                            </div>
                        </div>
                    </div>
                    <!-- Right Column: Details -->
                    <div class="col-md-8">
                        <div class="card">
                            <div class="card-header">
                                <h6 class="mb-0"><i class="fas fa-id-card mr-2"></i>Informasi Lengkap</h6>
                            </div>
                            <div class="card-body p-0">
                                <table class="table table-striped mb-0">
                                    <tbody>
                                        <tr><th width="35%">Jenis Kelamin</th><td id="detailJenisKelamin"></td></tr>
                                        <tr><th>Tempat Lahir</th><td id="detailTempatLahir"></td></tr>
                                        <tr><th>Tanggal Lahir</th><td id="detailTanggalLahir"></td></tr>
                                        <tr><th>Alamat</th><td id="detailAlamat"></td></tr>
                                        <tr><th>Nama Ayah</th><td id="detailNamaAyah"></td></tr>
                                        <tr><th>Nama Ibu</th><td id="detailNamaIbu"></td></tr>
                                        <tr><th>No. HP</th><td id="detailNoHp"></td></tr>
                                        <tr><th>Status</th><td><span id="detailStatus" class="badge"></span></td></tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="card mt-3">
                            <div class="card-header">
                                <h6 class="mb-0"><i class="fas fa-book-quran mr-2"></i>Hafalan Al-Qur'an</h6>
                            </div>
                            <div class="card-body p-0">
                                <table class="table table-bordered table-striped mb-0">
                                    <thead>
                                        <tr>
                                            <th width="100px">Juz</th>
                                            <th>Mulai Surah</th>
                                            <th>Akhir Surah</th>
                                        </tr>
                                    </thead>
                                    <tbody id="detailHafalanTable">
                                        <!-- Dynamic Content -->
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">
                    <i class="fas fa-times"></i> Tutup
                </button>
                <a href="#" id="btnEditFromDetail" class="btn btn-warning">
                    <i class="fas fa-edit"></i> Edit Profil
                </a>
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
                <input type="hidden" id="cropPhotoIdSiswa" value="">
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
    /* Ensure modal doesn't overflow screen */
    #modalCropPhoto .modal-body {
        max-height: calc(100vh - 200px);
        overflow-y: auto;
    }
</style>

<?= $this->endSection(); ?>

<?= $this->section('scripts'); ?>
<script>
$(document).ready(function() {
    // Data Al-Quran dari Controller (use proper escaping for JS)
    const alquranData = <?= json_encode($dataAlquran ?? [], JSON_HEX_TAG | JSON_HEX_QUOT | JSON_HEX_APOS | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE) ?>;

    // Custom file input label
    $('.custom-file-input').on('change', function() {
        var fileName = $(this).val().split('\\').pop();
        $(this).next('.custom-file-label').addClass("selected").html(fileName);
    });

    // Toggle Template Link
    $('input[name="import_type"]').change(function() {
        if ($(this).val() === 'template') {
            $('#template-link-container').show();
        } else {
            $('#template-link-container').hide();
        }
    });

    // Handle Import Form Submit
    $('#form-import').on('submit', function() {
        $('#modal-import').modal('hide');
        Swal.fire({
            title: 'Sedang Memproses...',
            text: 'Mohon tunggu, sedang membaca file Excel.',
            allowOutsideClick: false,
            showConfirmButton: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });
    });

    // Inisialisasi DataTables
    $('#tabelSiswa').DataTable({
        responsive: true,
        autoWidth: false,
        columnDefs: [
            { orderable: false, targets: [1, 6, 7] } // Index geser karena ada kolom Foto di index 1
        ]
    });
    
    // --- LOGIC HAFALAN ---

    // Handle Button Hafalan Click (Buka Modal)
    $(document).on('click', '.btn-hafalan', function() {
        let id = $(this).data('id');
        let nama = $(this).data('nama');
        let hafalan = $(this).data('hafalan'); // JSON object
        
        $('#hafalanIdSiswa').val(id);
        $('#hafalanNamaSiswa').text(nama);
        
        // Reset container
        $('#containerHafalan').empty();
        
        if (hafalan && hafalan.length > 0) {
            hafalan.forEach(function(item) {
                // Add row and set values
                let mulai = item.nama_surah_mulai || item.mulai;
                let akhir = item.nama_surah_akhir || item.akhir;
                let row = addHafalanRow(item.juz, mulai, akhir);
            });
        } else {
            addHafalanRow();
        }
    });

    // Add Row Button
    $('#btnAddHafalan').click(function() {
        addHafalanRow();
    });

    // Remove Row Button
    $(document).on('click', '.btn-remove-row', function() {
        $(this).closest('tr').remove();
    });

    // Function to add row
    function addHafalanRow(defaultJuz = '', defaultMulai = '', defaultAkhir = '') {
        // Build Juz Options
        let juzOptions = '<option value="">Pilih</option>';
        for(let i=1; i<=30; i++) {
            let selected = (i == defaultJuz) ? 'selected' : '';
            juzOptions += `<option value="${i}" ${selected}>${i}</option>`;
        }

        let rowHtml = `
            <tr class="hafalan-row">
                <td>
                    <select name="juz[]" class="form-control form-control-sm select-juz" style="width: 100%;" required>
                        ${juzOptions}
                    </select>
                </td>
                <td>
                    <select name="mulai[]" class="form-control form-control-sm select-surah select-mulai" style="width: 100%;">
                        <option value="">Pilih Juz Dulu</option>
                    </select>
                </td>
                <td>
                    <select name="akhir[]" class="form-control form-control-sm select-surah select-akhir" style="width: 100%;">
                        <option value="">Pilih Juz Dulu</option>
                    </select>
                </td>
                <td class="text-center">
                    <button type="button" class="btn btn-danger btn-xs btn-remove-row">
                        <i class="fas fa-trash"></i>
                    </button>
                </td>
            </tr>
        `;
        
        let $row = $(rowHtml);
        $('#containerHafalan').append($row);
        
        // Initialize Select2
        $row.find('.select-juz').select2({
            theme: 'bootstrap4',
            placeholder: "Pilih Juz"
        });
        
        $row.find('.select-surah').select2({
            theme: 'bootstrap4',
            placeholder: "Pilih Surah"
        });

        // Event Listener for Juz Change
        $row.find('.select-juz').on('change', function() {
            let selectedJuz = $(this).val();
            updateSurahOptions($row, selectedJuz);
        });

        // Trigger change to populate surahs if defaultJuz is provided
        if (defaultJuz) {
            // Kita set manual optionnya agar Select2 tidak bingung saat init
            updateSurahOptions($row, defaultJuz, defaultMulai, defaultAkhir);
        }

        return $row;
    }

    // Function to update Surah dropdowns based on Juz
    function updateSurahOptions($row, juz, selectedMulai = null, selectedAkhir = null) {
        let $selectMulai = $row.find('.select-mulai');
        let $selectAkhir = $row.find('.select-akhir');
        
        $selectMulai.empty().append('<option value="">Pilih</option>');
        $selectAkhir.empty().append('<option value="">Pilih</option>');
        
        if (!juz) return;

        // Filter alquranData based on Juz
        // Note: Satu juz bisa punya banyak baris (mapping ayat), kita butuh unique nama_surah
        // Asumsi dataAlquran sorted by no_surah/urutan
        
        let uniqueSurahs = [];
        let seenSurahs = new Set();
        
        alquranData.forEach(item => {
            if (item.juz == juz) {
                if (!seenSurahs.has(item.nama_surah)) {
                    seenSurahs.add(item.nama_surah);
                    uniqueSurahs.push(item);
                }
            }
        });

        uniqueSurahs.forEach(surah => {
            let option1 = new Option(surah.nama_surah, surah.nama_surah, false, false);
            let option2 = new Option(surah.nama_surah, surah.nama_surah, false, false);
            
            $selectMulai.append(option1);
            $selectAkhir.append(option2);
        });

        // Restore selected values if provided
        if (selectedMulai) $selectMulai.val(selectedMulai).trigger('change');
        if (selectedAkhir) $selectAkhir.val(selectedAkhir).trigger('change');
    }
});
</script>

<!-- Cropper JS -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.js"></script>

<script>
// =================== PHOTO VIEW/EDIT MODAL LOGIC ===================
var cropperInstance = null;
var currentPhotoIdSiswa = null;

// Open View Photo Modal (double-click on photo)
function openViewPhotoModal(imgElement) {
    var id = $(imgElement).data('id');
    var nama = $(imgElement).data('nama');
    var foto = $(imgElement).data('foto');
    
    currentPhotoIdSiswa = id;
    $('#viewPhotoIdSiswa').val(id);
    $('#viewPhotoNamaSiswa').text(nama);
    $('#viewPhotoImage').attr('src', foto);
    
    $('#modalViewPhoto').modal('show');
}

// Edit Foto Button - Open existing photo in cropper
$('#btnEditPhotoFromView').click(function() {
    var foto = $('#viewPhotoImage').attr('src');
    var id = $('#viewPhotoIdSiswa').val();
    
    $('#modalViewPhoto').modal('hide');
    
    // Set ID and open crop modal with existing image
    $('#cropPhotoIdSiswa').val(id);
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
            var idSiswa = $('#cropPhotoIdSiswa').val() || currentPhotoIdSiswa;
            
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
                    id_siswa: idSiswa,
                    <?= csrf_token() ?>: '<?= csrf_hash() ?>'
                },
                success: function(response) {
                    Swal.close();
                    $('#modalCropPhoto').modal('hide');
                    
                    if (response.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil',
                            text: response.message,
                            timer: 1500,
                            showConfirmButton: false
                        }).then(function() {
                            // Reload page to update photos
                            location.reload();
                        });
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

// =================== VIEW DETAIL MODAL LOGIC ===================
$('.btn-view-detail').click(function() {
    var id = $(this).data('id');
    var nisn = $(this).data('nisn');
    var nama = $(this).data('nama');
    var jenisKelamin = $(this).data('jenis-kelamin');
    var tempatLahir = $(this).data('tempat-lahir');
    var tanggalLahir = $(this).data('tanggal-lahir');
    var alamat = $(this).data('alamat');
    var namaAyah = $(this).data('nama-ayah');
    var namaIbu = $(this).data('nama-ibu');
    var noHp = $(this).data('no-hp');
    var status = $(this).data('status');
    var foto = $(this).data('foto');
    var hafalan = $(this).data('hafalan');
    
    // Populate modal
    $('#detailFoto').attr('src', foto);
    $('#detailNama').text(nama);
    $('#detailNisn').text(nisn);
    $('#detailJenisKelamin').text(jenisKelamin);
    $('#detailTempatLahir').text(tempatLahir);
    $('#detailTanggalLahir').text(tanggalLahir);
    $('#detailAlamat').text(alamat);
    $('#detailNamaAyah').text(namaAyah);
    $('#detailNamaIbu').text(namaIbu);
    $('#detailNoHp').text(noHp);
    
    // Status badge
    var statusBadge = $('#detailStatus');
    statusBadge.text(status);
    statusBadge.removeClass('badge-success badge-secondary badge-primary badge-warning');
    if (status.toLowerCase() === 'aktif') {
        statusBadge.addClass('badge-success');
    } else if (status.toLowerCase() === 'lulus') {
        statusBadge.addClass('badge-primary');
    } else if (status.toLowerCase() === 'pindah') {
        statusBadge.addClass('badge-warning');
    } else {
        statusBadge.addClass('badge-secondary');
    }
    
    // Hafalan table
    var hafalanHtml = '';
    if (hafalan && hafalan.length > 0) {
        hafalan.forEach(function(h) {
            hafalanHtml += '<tr>';
            hafalanHtml += '<td class="text-center"><strong>Juz ' + h.juz + '</strong></td>';
            hafalanHtml += '<td>' + (h.display_mulai || '-') + '</td>';
            hafalanHtml += '<td>' + (h.display_akhir || '-') + '</td>';
            hafalanHtml += '</tr>';
        });
    } else {
        hafalanHtml = '<tr><td colspan="3" class="text-center text-muted">Belum ada data hafalan</td></tr>';
    }
    $('#detailHafalanTable').html(hafalanHtml);
    
    // Set edit link
    $('#btnEditFromDetail').attr('href', '<?= base_url('backend/siswa/edit/') ?>' + id);
    
    // Show modal
    $('#modalDetailSiswa').modal('show');
});
</script>
<?= $this->endSection(); ?>
