<style>
    @font-face {
        font-family: 'LPMQ';
        src: url('<?= base_url('assets/fonts/lpmq.otf') ?>') format('opentype');
        font-weight: normal;
        font-style: normal;
    }
    .text-arab {
        font-family: 'LPMQ', serif;
        font-size: 1.8em;
        line-height: 2.2;
        text-align: right;
    }
    .quran-verse {
        border-bottom: 1px solid #eee;
        padding: 10px 0;
    }
    .verse-number {
        display: inline-block;
        width: 30px;
        height: 30px;
        line-height: 30px;
        text-align: center;
        border: 1px solid #ccc;
        border-radius: 50%;
        margin-left: 10px;
        font-size: 0.8em;
    }
</style>
<div class="row">
    <div class="col-md-12">
        <div class="card card-outline card-success">
            <div class="card-header">
                <div class="user-block">
                    <img class="img-circle" 
                         src="<?= (!empty($peserta['foto'])) ? base_url($peserta['foto']) : 'https://ui-avatars.com/api/?name=' . urlencode($peserta['nama_siswa']) . '&background=random&color=fff&bold=true&length=2' ?>" 
                         onerror="this.onerror=null; this.src='https://ui-avatars.com/api/?name=<?= urlencode($peserta['nama_siswa']) ?>&background=random&color=fff&bold=true&length=2';"
                         alt="User Image">
                    <span class="username"><a href="#"><?= $peserta['nama_siswa'] ?></a></span>
                    <span class="description">No Peserta: <strong style="font-size: 1.2em; color: black;"><?= $peserta['no_peserta'] ?></strong> | NISN: <?= $peserta['nisn'] ?></span>
                </div>
            </div>
            
            <?php if(isset($isGraded) && $isGraded): ?>
                <div class="alert alert-warning text-center">
                    <i class="fas fa-check-circle mr-2"></i> <strong>Sudah Dinilai</strong><br>
                    Anda sudah melakukan penilaian untuk peserta ini. Data tidak dapat diedit kembali.
                </div>
            <?php endif; ?>

            <?php if(isset($lockedByOther) && $lockedByOther): ?>
                <div class="alert alert-danger text-center">
                    <i class="fas fa-lock mr-2"></i> <strong>Akses Dikunci</strong><br>
                    Peserta ini sudah dinilai oleh Juri lain: <strong><?= esc($otherJuriName) ?></strong>.<br>
                    Anda tidak dapat mengubah nilai ini.
                </div>
            <?php endif; ?>

            <form id="form-input-nilai" data-mode="<?= $juri['kondisional_set'] ?>">
                <input type="hidden" name="no_peserta" value="<?= $peserta['no_peserta'] ?>">
                
                <?php if(isset($lockedByOther) && $lockedByOther): ?>
                    <!-- Form Hidden for Privacy -->
                    <div class="card-body text-center text-muted py-5">
                        <i class="fas fa-lock fa-5x mb-3 text-secondary"></i>
                        <h5>Formulir tidak tersedia.</h5>
                        <p>Peserta ini sedang/sudah dinilai oleh Juri lain.</p>
                    </div>
                <?php else: ?>
                    <div class="card-body">
                        <!-- Tabs Header -->
                        <ul class="nav nav-tabs" id="custom-content-below-tab" role="tablist">
                            <?php foreach($items as $index => $item): ?>
                            <li class="nav-item">
                                <a class="nav-link <?= $index === 0 ? 'active' : '' ?>" 
                                   id="tab-<?= $item['key'] ?>" 
                                   data-toggle="pill" 
                                   href="#content-<?= $item['key'] ?>" 
                                   role="tab" 
                                   aria-controls="content-<?= $item['key'] ?>" 
                                   aria-selected="<?= $index === 0 ? 'true' : 'false' ?>">
                                    <?= $item['objek'] ?>
                                </a>
                            </li>
                        <?php endforeach; ?>
                    </ul>

                    <!-- Tabs Content -->
                    <div class="tab-content pt-3" id="custom-content-below-tabContent">
                        <?php foreach($items as $index => $item): ?>
                            <div class="tab-pane fade <?= $index === 0 ? 'show active' : '' ?>" 
                                 id="content-<?= $item['key'] ?>" 
                                 role="tabpanel" 
                                 aria-labelledby="tab-<?= $item['key'] ?>">
                                
                                <div class="callout callout-info d-flex justify-content-between align-items-center">
                                    <h5>Materi: <strong><?= $item['label'] ?></strong></h5>
                                    
                                    <?php if(isset($item['objek_id']) && $item['objek_id'] > 0): ?>
                                    <div>
                                        <div class="btn-group btn-group-sm mr-2" role="group">
                                            <button type="button" class="btn btn-outline-secondary btn-zoom-out" title="Perkecil Text">
                                                <i class="fas fa-search-minus"></i>
                                            </button>
                                            <button type="button" class="btn btn-outline-secondary btn-zoom-in" title="Perbesar Text">
                                                <i class="fas fa-search-plus"></i>
                                            </button>
                                        </div>
                                        <button type="button" class="btn btn-sm btn-outline-info btn-view-ayat" 
                                                data-surah="<?= $item['objek_id'] ?>" 
                                                data-target="#quran-container-<?= $item['key'] ?>">
                                            <i class="fas fa-eye mr-1"></i> Lihat Ayat
                                        </button>
                                    </div>
                                    <?php endif; ?>
                                </div>
                                
                                <!-- Quran Container (Hidden Default) -->
                                <div id="quran-container-<?= $item['key'] ?>" class="collapse mb-3 border p-3 bg-light rounded" style="max-height: 400px; overflow-y: auto;">
                                    <div class="text-center text-muted loading-ayat">
                                        <i class="fas fa-spinner fa-spin mr-2"></i> Memuat Ayat...
                                    </div>
                                    <div class="ayat-content"></div>
                                </div>

                                <table class="table table-bordered table-hover">
                                    <thead class="thead-light">
                                        <tr>

                                            <th style="width: 40%">Kriteria</th>
                                            <th style="width: 25%">
                                                <?= ($juri['kondisional_set'] == 'nilai_pengurangan') ? 'Input Kesalahan' : 'Nilai' ?>
                                            </th>
                                            <th>Info</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if(empty($kriteriaList)): ?>
                                            <tr><td colspan="3" class="text-center text-muted">Belum ada kriteria yang disetting untuk Juri ini.</td></tr>
                                        <?php else: ?>
                                            <?php foreach($kriteriaList as $kIndex => $k): ?>
                                            <?php 
                                                 // Logic for Labels & Limits
                                                 $isPengurangan = ($juri['kondisional_set'] == 'nilai_pengurangan');
                                                 $maxVal = $k['nilai_maksimal'] ?? 100;
                                                 
                                                 $suffix = $isPengurangan ? 'x Salah' : '/ ' . $maxVal;
                                                 // Max limit applies to BOTH modes now
                                                 $maxAttr = 'max="' . $maxVal . '"';
                                                 
                                                 $hintText = $isPengurangan 
                                                    ? 'Isi jumlah kesalahan (Max ' . $maxVal . ').' 
                                                    : 'Isi nilai (0-' . $maxVal . ').';

                                                 // Existing Value Logic
                                                 $currentVal = 0;
                                                 $disabledAttr = ((isset($isGraded) && $isGraded) || (isset($lockedByOther) && $lockedByOther)) ? 'disabled' : '';
                                                 
                                                 $lookupKey = ($item['key'] === 'general') ? 'General' : ($item['objek_id'] ?? 'General');
                                                 
                                                 if (isset($existingScores[$lookupKey][$k['id']])) {
                                                     $currentVal = $existingScores[$lookupKey][$k['id']];
                                                 }
                                            ?>
                                            <tr>

                                                <td>
                                                    <strong><?= $k['nama_kriteria'] ?></strong>
                                                </td>
                                                <td style="min-width: 200px;">
                                                    <div class="input-group flex-nowrap">
                                                        <div class="input-group-prepend">
                                                            <button type="button" class="btn btn-danger btn-min" <?= $disabledAttr ?>><i class="fas fa-minus"></i></button>
                                                        </div>
                                                        <input type="number" 
                                                               class="form-control input-nilai-field text-center font-weight-bold" 
                                                               style="font-size: 1.2em; min-width: 60px;"
                                                               name="nilai[<?= $item['key'] ?>][<?= $k['id'] ?>]" 
                                                               min="0" 
                                                               <?= $maxAttr ?>
                                                               data-max="<?= $maxVal ?>"
                                                               value="<?= $currentVal ?>"
                                                               required
                                                               <?= $disabledAttr ?>>
                                                        <div class="input-group-append">
                                                            <button type="button" class="btn btn-success btn-plus" <?= $disabledAttr ?>><i class="fas fa-plus"></i></button>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td class="text-muted text-sm">
                                                    <?= $hintText ?>
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </tbody>
                                </table>

                                <div class="form-group mt-3">
                                    <label>Catatan Khusus (Opsional)</label>
                                    <textarea name="catatan[<?= $item['key'] ?>]" class="form-control" rows="2" placeholder="Catatan untuk materi ini..." <?= $disabledAttr ?>></textarea>
                                </div>

                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>
                
                <div class="card-footer d-flex justify-content-between">
                    <div>
                        <?php if(isset($lockedByOther) && $lockedByOther): ?>
                             <!-- No Cancel needed if locked strictly -->
                             <button type="button" class="btn btn-secondary" onclick="location.reload()">Kembali</button>
                        <?php else: ?>
                             <button type="button" class="btn btn-secondary" onclick="confirmCancel()"><i class="fas fa-arrow-left mr-1"></i> Kembali</button>
                        <?php endif; ?>
                    </div>

                    <div>
                        <?php if(isset($lockedByOther) && $lockedByOther): ?>
                            <button type="button" class="btn btn-secondary" onclick="location.reload()">Tutup</button>
                        <?php elseif(!isset($isGraded) || !$isGraded): ?>
                            <button type="submit" class="btn btn-success btn-lg"><i class="fas fa-save mr-2"></i> Simpan Nilai</button>
                        <?php else: ?>
                            <button type="button" class="btn btn-warning" id="btn-unlock"><i class="fas fa-lock mr-1"></i> Edit Nilai (Otorisasi)</button>
                            <!-- Secondary close button on right is redundant if we have 'Kembali' on left, but keeps UI balanced -->
                        <?php endif; ?>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Otorisasi -->
<div class="modal fade" id="modal-auth" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-sm" role="document">
        <div class="modal-content">
            <div class="modal-header bg-warning">
                <h5 class="modal-title">Otorisasi Kepala/Admin</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="form-auth">
                    <div class="form-group">
                        <label>Username</label>
                        <input type="text" class="form-control" name="username" required placeholder="User Kepala/Admin">
                    </div>
                    <div class="form-group">
                        <label>Password</label>
                        <input type="password" class="form-control" name="password" required>
                    </div>
                    <button type="submit" class="btn btn-primary btn-block">Verifikasi</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    // Logic View Ayat
    $('.btn-view-ayat').click(function() {
        let surahId = $(this).data('surah');
        let targetId = $(this).data('target');
        let container = $(targetId);
        let contentDiv = container.find('.ayat-content');
        let loadingDiv = container.find('.loading-ayat');

        // Toggle visibilitas
        container.collapse('toggle');
        
        // Jika kosong, ambil data
        if (contentDiv.is(':empty')) {
            loadingDiv.show();
            contentDiv.hide();
            
            $.ajax({
                url: '<?= base_url("assets/quran/json/") ?>' + surahId + '.json',
                dataType: 'json',
                success: function(res) {
                    loadingDiv.hide();
                    if(res && res[surahId]) {
                        let html = '';
                        let verses = res[surahId].text; 
                        
                        if(verses) {
                             $.each(verses, function(ayatNo, ayatText) {
                                 html += `<div class="quran-verse">
                                    <div class="d-flex justify-content-between">
                                        <div class="verse-number">${ayatNo}</div>
                                        <div class="text-arab w-100" style="font-size: ${currentFontSize}em;">${ayatText}</div>
                                    </div>
                                 </div>`;
                             });
                             contentDiv.html(html).show();
                             // Apply font size globally just in case
                             applyFontSize();
                        }
                    } else {
                        contentDiv.html('<div class="text-danger text-center">Gagal memuat ayat. Struktur data tidak dikenali.</div>').show();
                    }
                },
                error: function() {
                    loadingDiv.hide();
                    contentDiv.html('<div class="text-danger text-center">Gagal mengambil data ayat (File JSON tidak ditemukan).</div>').show();
                }
            });
        }
    });

    // Zoom Logic
    // Default size
    let currentFontSize = parseFloat(localStorage.getItem('quranFontSize')) || 1.8;
    
    // Apply saved size immediately to styled elements (if any exist static) or dynamic ones
    function applyFontSize() {
        $('.text-arab').css('font-size', currentFontSize + 'em');
        // Save to local storage
        localStorage.setItem('quranFontSize', currentFontSize);
    }

    $('.btn-zoom-in').click(function() {
        currentFontSize += 0.2;
        applyFontSize();
    });

    $('.btn-zoom-out').click(function() {
        if (currentFontSize > 0.8) { // Minimum limit
            currentFontSize -= 0.2;
            applyFontSize();
        }
    });

    // Unlock Logic
    $('#btn-unlock').click(function() {
        $('#modal-auth').modal('show');
    });

    $('#form-auth').submit(function(e) {
        e.preventDefault();
        $.ajax({
            url: '<?= base_url('backend/munaqosah/input-nilai/authorize-edit') ?>',
            type: 'POST',
            data: $(this).serialize(),
            success: function(res) {
                if(res.success) {
                    $('#modal-auth').modal('hide');
                    Swal.fire({
                        icon: 'success',
                        title: 'Sukses',
                        text: res.message,
                        timer: 1500,
                        showConfirmButton: false
                    });
                    
                    // Buka Kunci Form (Input, Textarea, dan Button)
                    $('#form-input-nilai input, #form-input-nilai textarea, #form-input-nilai button').prop('disabled', false);
                    $('.alert-warning').fadeOut();
                    
                    // Replace buttons
                    $('.card-footer').html(`
                        <div class="d-flex justify-content-between w-100">
                             <div>
                                <button type="button" class="btn btn-secondary" onclick="confirmCancel()"><i class="fas fa-arrow-left mr-1"></i> Kembali</button>
                             </div>
                             <div>
                                <button type="submit" class="btn btn-success btn-lg"><i class="fas fa-save mr-2"></i> Simpan Nilai</button>
                             </div>
                        </div>
                    `);
                } else {
                    Swal.fire('Gagal', res.message, 'error');
                }
            }
        });
    });

    $('#form-input-nilai').submit(function(e) {
        e.preventDefault();
        
        let mode = $(this).data('mode'); // 'nilai_pengurangan' or 'nilai_default' (or other)

        // 1. Validasi: Cek input kosong dan cek NOL untuk mode default
        let isValid = true;
        let emptyCount = 0;
        let zeroCount = 0;
        
        $('.input-nilai-field').each(function() {
            let val = $(this).val();
            
            // Cek kosong
            if (val === '') {
                isValid = false;
                emptyCount++;
                $(this).addClass('is-invalid');
            } 
            // Cek Nol untuk mode Non-Pengurangan
            else if (mode !== 'nilai_pengurangan' && parseFloat(val) == 0) {
                isValid = false;
                zeroCount++;
                $(this).addClass('is-invalid');
            }
            else {
                $(this).removeClass('is-invalid');
            }
        });

        if (!isValid) {
            let msg = 'Harap periksa input data.';
            if (emptyCount > 0) msg += ' Ada ' + emptyCount + ' kolom kosong.';
            if (zeroCount > 0) msg += ' Nilai 0 tidak diperbolehkan untuk materi ini.';
            
            Swal.fire({
                icon: 'warning',
                title: 'Validasi Gagal',
                text: msg,
            });
            return; 
        }

        // 2. Buat HTML Ringkasan
        let summaryHtml = '<div class="text-left" style="font-size: 0.9em;">';
        summaryHtml += '<table class="table table-sm table-bordered mt-2"><thead><tr class="bg-light"><th>Materi</th><th>Detail Input</th></tr></thead><tbody>';
        
        // Loop melalui setiap tab-pane untuk dikelompokkan berdasarkan Item
        $('.tab-pane').each(function() {
            let itemLabel = $(this).find('h5 strong').text();
            let inputs = $(this).find('.input-nilai-field');
            
            if (inputs.length > 0) {
                summaryHtml += '<tr>';
                summaryHtml += '<td style="vertical-align: middle;"><strong>' + itemLabel + '</strong></td>';
                summaryHtml += '<td>';
                
                inputs.each(function() {
                    let kriteriaName = $(this).closest('tr').find('td:first strong').text();
                    let val = parseFloat($(this).val()) || 0;
                    let maxVal = parseFloat($(this).data('max')) || 100;
                    let displayVal = val;
                    let suffix = ''; 
                    
                    if (mode === 'nilai_pengurangan') {
                        let finalScore = maxVal - val;
                        // Show: Kesalahan (15) => Nilai (85)
                        displayVal = `Kesalahan: ${val} <i class="fas fa-arrow-right mx-1"></i> Nilai: <strong>${finalScore}</strong>`;
                    } else {
                        // Nilai Biasa
                        displayVal = `<strong>${val}</strong>`;
                    }
                    
                    summaryHtml += '<div>- ' + kriteriaName + ': ' + displayVal + '</div>';
                });
                
                summaryHtml += '</td></tr>';
            }
        });
        summaryHtml += '</tbody></table></div>';
        summaryHtml += '<p class="mt-2 text-muted">Pastikan data di atas sudah sesuai.</p>';

        // 3. Konfirmasi
        Swal.fire({
            title: 'Konfirmasi Simpan',
            html: summaryHtml,
            icon: 'question',
            width: '600px',
            showCancelButton: true,
            confirmButtonColor: '#28a745',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Ya, Simpan!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '<?= base_url('backend/munaqosah/input-nilai/save') ?>',
                    type: 'POST',
                    data: $(this).serialize(),
                    success: function(res) {
                        if(res.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Berhasil!',
                                text: res.message,
                                timer: 1500,
                                showConfirmButton: false
                            }).then(() => {
                                // Tutup Form & Reset Pencarian
                                $('#penilaian-container').empty();
                                $('.card-primary').CardWidget('expand');
                                $('#no_peserta').val('').focus();
                                
                                // Segarkan Riwayat
                                if(window.loadHistory) window.loadHistory();
                            });
                        } else {
                            Swal.fire('Gagal!', res.message, 'error');
                        }
                    },
                    error: function() {
                        Swal.fire('Error!', 'Terjadi kesalahan server.', 'error');
                    }
                });
            }
        });
    });
    
    // Pilih konten otomatis saat fokus
    $('.input-nilai-field').focus(function() {
        $(this).select();
    });

    // Custom Input Logic (+/- Buttons)
    $('.btn-plus').click(function() {
        let input = $(this).closest('.input-group').find('input.input-nilai-field');
        let currentVal = parseInt(input.val()) || 0;
        let maxVal = parseInt(input.attr('max')) || 100;
        
        if (currentVal < maxVal) {
            input.val(currentVal + 1).trigger('change');
        }
    });

    $('.btn-min').click(function() {
        let input = $(this).closest('.input-group').find('input.input-nilai-field');
        let currentVal = parseInt(input.val()) || 0;
        let minVal = parseInt(input.attr('min')) || 0;
        
        if (currentVal > minVal) {
            input.val(currentVal - 1).trigger('change');
        }
    });

    // Dirty State Tracking
    let formIsDirty = false;
    $('.input-nilai-field, textarea').on('change keyup', function() {
        formIsDirty = true;
    });

    // Custom Cancel Confirmation
    window.confirmCancel = function() {
        if (formIsDirty) {
            Swal.fire({
                title: 'Konfirmasi',
                text: "Anda telah mengubah nilai. Data yang belum disimpan akan hilang. Yakin ingin kembali?",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Ya, Kembali',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    location.reload();
                }
            });
        } else {
            location.reload();
        }
    };
</script>
