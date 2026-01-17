<div class="row">
    <div class="col-md-12">
        <div class="card card-outline card-success">
            <div class="card-header">
                <div class="user-block">
                    <img class="img-circle" src="<?= base_url($peserta['foto'] ?? 'assets/img/default-user.png') ?>" alt="User Image">
                    <span class="username"><a href="#"><?= $peserta['nama_siswa'] ?></a></span>
                    <span class="description">No Peserta: <strong style="font-size: 1.2em; color: black;"><?= $peserta['no_peserta'] ?></strong> | NISN: <?= $peserta['nisn'] ?></span>
                </div>
                <div class="card-tools">
                    <span class="badge badge-info" style="font-size: 1em;"><?= $juri['nama_grup_materi'] ?></span>
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

            <form id="form-input-nilai">
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
                                
                                <div class="callout callout-info">
                                    <h5>Materi: <strong><?= $item['label'] ?></strong></h5>
                                    <?php if(isset($item['objek_id']) && $item['objek_id'] > 0): ?>
                                    <a href="https://quran.com/<?= $item['objek_id'] ?>" target="_blank" class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-book-open mr-1"></i> Buka Surah (Ayat 1)
                                    </a>
                                    <?php endif; ?>
                                </div>

                                <table class="table table-bordered table-hover">
                                    <thead class="thead-light">
                                        <tr>
                                            <th style="width: 5%">No</th>
                                            <th style="width: 40%">Kriteria Penilaian</th>
                                            <th style="width: 25%">Input Kesalahan</th>
                                            <th>Keterangan</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if(empty($kriteriaList)): ?>
                                            <tr><td colspan="4" class="text-center text-muted">Belum ada kriteria yang disetting untuk Juri ini.</td></tr>
                                        <?php else: ?>
                                            <?php foreach($kriteriaList as $kIndex => $k): ?>
                                            <?php 
                                                 // Check for existing value
                                                 // Key mapping: existingScores[objek][id_kriteria]
                                                 // item['objek'] is the name (e.g. 'An-Naba'), but 'objek_penilaian' in DB is often SurahNo (e.g. '78') for Tahfidz or 'General'.
                                                 
                                                 $currentVal = 0;
                                                 $currentVal = 0;
                                                 $disabledAttr = ((isset($isGraded) && $isGraded) || (isset($lockedByOther) && $lockedByOther)) ? 'disabled' : '';
                                                 
                                                 // Try to find the value
                                                 // Logic in Controller: $objekPenilaian stored as '78' or 'General'
                                                 // ItemObjekId holds '78'. ItemObjek holds 'An-Naba'.
                                                 
                                                 $lookupKey = ($item['key'] === 'general') ? 'General' : ($item['objek_id'] ?? 'General');
                                                 
                                                 if (isset($existingScores[$lookupKey][$k['id']])) {
                                                     $currentVal = $existingScores[$lookupKey][$k['id']];
                                                 }
                                            ?>
                                            <tr>
                                                <td class="text-center"><?= $kIndex + 1 ?></td>
                                                <td>
                                                    <strong><?= $k['nama_kriteria'] ?></strong>
                                                    <?php if(!empty($k['deskripsi'])): ?>
                                                        <br><small class="text-muted"><?= $k['deskripsi'] ?></small>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <div class="input-group">
                                                        <input type="number" 
                                                               class="form-control input-nilai-field" 
                                                               name="nilai[<?= $item['key'] ?>][<?= $k['id'] ?>]" 
                                                               min="0" 
                                                               value="<?= $currentVal ?>"
                                                               required
                                                               <?= $disabledAttr ?>>
                                                        <div class="input-group-append">
                                                            <span class="input-group-text">x Salah</span>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td class="text-muted text-sm">
                                                    Isi jumlah kesalahan (0 jika sempurna).
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
                
                <div class="card-footer text-right">
                    <?php if(isset($lockedByOther) && $lockedByOther): ?>
                        <button type="button" class="btn btn-secondary" onclick="location.reload()">Tutup</button>
                    <?php elseif(!isset($isGraded) || !$isGraded): ?>
                    <button type="button" class="btn btn-secondary mr-2" onclick="location.reload()">Batal</button>
                    <button type="submit" class="btn btn-success btn-lg"><i class="fas fa-save mr-2"></i> Simpan Nilai</button>
                    <?php else: ?>
                    <button type="button" class="btn btn-warning" id="btn-unlock"><i class="fas fa-lock mr-1"></i> Edit Nilai (Otorisasi)</button>
                    <button type="button" class="btn btn-secondary" onclick="location.reload()">Tutup</button>
                    <?php endif; ?>
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
                    
                    // Unlock Form
                    $('#form-input-nilai input, #form-input-nilai textarea').prop('disabled', false);
                    $('.alert-warning').fadeOut();
                    
                    // Replace buttons
                    $('.card-footer').html(`
                        <button type="button" class="btn btn-secondary mr-2" onclick="location.reload()">Batal</button>
                        <button type="submit" class="btn btn-success btn-lg"><i class="fas fa-save mr-2"></i> Simpan Nilai</button>
                    `);
                } else {
                    Swal.fire('Gagal', res.message, 'error');
                }
            }
        });
    });

    $('#form-input-nilai').submit(function(e) {
        e.preventDefault();
        
        // Confirmation
        Swal.fire({
            title: 'Simpan Nilai?',
            text: "Pastikan semua data sudah benar.",
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#28a745',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Ya, Simpan!'
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
                                // Close Form & Reset Search
                                $('#penilaian-container').empty();
                                $('.card-primary').CardWidget('expand');
                                $('#no_peserta').val('').focus();
                                
                                // Refresh History
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
    
    // Auto select content on focus
    $('.input-nilai-field').focus(function() {
        $(this).select();
    });
</script>
