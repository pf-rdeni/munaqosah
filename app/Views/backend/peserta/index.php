<?= $this->extend('backend/template/template'); ?>

<?= $this->section('content'); ?>
<div class="row">
    <!-- Stats Cards -->
    <div class="col-md-3">
        <div class="info-box bg-info">
            <span class="info-box-icon"><i class="fas fa-users"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Total Siswa Aktif</span>
                <span class="info-box-number"><?= $totalSiswaAktif ?></span>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="info-box bg-purple">
            <span class="info-box-icon"><i class="fas fa-book-open"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Punya Hafalan</span>
                <span class="info-box-number"><?= $siswaWithHafalan ?></span>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="info-box bg-success">
            <span class="info-box-icon"><i class="fas fa-check-circle"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Sudah Punya No Tes</span>
                <span class="info-box-number"><?= $pesertaTerdaftar ?></span>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="info-box bg-warning">
            <span class="info-box-icon"><i class="fas fa-clock"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Belum Terdaftar</span>
                <span class="info-box-number"><?= $belumTerdaftar ?></span>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        
        <!-- Settings Card -->
        <div class="card collapsed-card">
            <div class="card-header bg-secondary">
                <h3 class="card-title"><i class="fas fa-cog"></i> Pengaturan Undian</h3>
                <div class="card-tools">
                    <button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-plus"></i></button>
                </div>
            </div>
            <div class="card-body">
                <form action="<?= base_url('backend/peserta/saveSettings') ?>" method="post" class="form-inline">
                    <?= csrf_field() ?>
                    <label class="mr-2">Rentang Surah Praktek Sholat:</label>
                    <select name="surah_sholat_start" class="form-control mr-2" required>
                        <?php foreach ($alquranList as $s): ?>
                            <option value="<?= $s['no_surah'] ?>" <?= ($settings['surah_sholat_start'] ?? 78) == $s['no_surah'] ? 'selected' : '' ?>>
                                <?= $s['no_surah'] ?> - <?= $s['nama_surah'] ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <span class="mr-2">s/d</span>
                    <select name="surah_sholat_end" class="form-control mr-2" required>
                        <?php foreach ($alquranList as $s): ?>
                            <option value="<?= $s['no_surah'] ?>" <?= ($settings['surah_sholat_end'] ?? 114) == $s['no_surah'] ? 'selected' : '' ?>>
                                <?= $s['no_surah'] ?> - <?= $s['nama_surah'] ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Simpan Pengaturan</button>
                </form>
                <small class="text-muted mt-2 d-block">* Surah Tahfidz akan ditentukan otomatis berdasarkan hafalan siswa (juz terakhir yang dihafal).</small>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-random mr-2"></i>
                    Undian No Tes - Tahun Ajaran <?= $tahunAjaran ?>
                </h3>
                <div class="card-tools">
                    <button type="button" class="btn btn-default btn-sm mr-2" onclick="location.reload()">
                        <i class="fas fa-sync-alt"></i> Refresh Data
                    </button>

                    <?php if ($pesertaTerdaftar == 0): ?>
                        <button type="button" class="btn btn-success btn-sm" id="btnUndian">
                            <i class="fas fa-dice mr-1"></i> Mulai Undian
                        </button>
                    <?php else: ?>
                        <?php if($belumTerdaftar > 0): ?>
                            <button type="button" class="btn btn-primary btn-sm mr-2" id="btnUndianTambahan">
                                <i class="fas fa-plus-circle mr-1"></i> Undian Tambahan (<?= $belumTerdaftar ?> Siswa)
                            </button>
                        <?php endif; ?>
                        
                        <button type="button" class="btn btn-danger btn-sm" id="btnReset">
                            <i class="fas fa-redo mr-1"></i> Reset Undian
                        </button>
                    <?php endif; ?>
                </div>
            </div>
            <div class="card-body">
                <?php if (session()->getFlashdata('success')): ?>
                    <div class="alert alert-success alert-dismissible fade show">
                        <button type="button" class="close" data-dismiss="alert">&times;</button>
                        <i class="icon fas fa-check"></i> <?= session()->getFlashdata('success') ?>
                    </div>
                <?php endif; ?>

                <?php if ($pesertaTerdaftar == 0): ?>
                    <div class="alert alert-info">
                        <h5><i class="icon fas fa-info"></i> Belum Ada Data Peserta</h5>
                        <p class="mb-0">Klik tombol <strong>"Mulai Undian"</strong> untuk mengundi No Tes secara random untuk <?= $siswaWithHafalan ?> siswa yang memiliki hafalan.</p>
                        <hr>
                        <ul class="mb-0">
                            <li>Hanya siswa dengan data hafalan yang diikutkan</li>
                            <li>Setiap siswa mendapat 1 Surah Sholat (Random)</li>
                            <li>Setiap siswa mendapat 3 Surah Tahfidz Wajib (Random sesuai hafalan)</li>
                            <li>Siswa dapat memilih 1 Surah Tahfidz Pilihan</li>
                        </ul>
                    </div>
                <?php else: ?>
                    <table id="tabelPeserta" class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th width="8%">No Tes</th>
                                <th>Nama Siswa</th>
                                <th>Surah Sholat</th>
                                <th>Tahfidz Wajib (3 acak)</th>
                                <th>Tahfidz Pilihan (1)</th>
                                <th width="5%">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($pesertaList as $peserta): ?>
                                <?php 
                                    $surahData = json_decode($peserta['surah'] ?? '{}', true); 
                                    $wajib = $surahData['tahfidz_wajib'] ?? [];
                                    $pilihan = $surahData['tahfidz_pilihan'] ?? null;

                                    // Construct WA Message
                                    $noHp = $peserta['no_hp'] ?? '';
                                    if ($noHp && substr($noHp, 0, 1) == '0') {
                                        $noHp = '62' . substr($noHp, 1);
                                    }
                                    
                                    // Tahfidz Wajib Text
                                    $wajibText = [];
                                    foreach($wajib as $wno) {
                                        foreach($alquranList as $qs) {
                                            if($qs['no_surah'] == $wno) {
                                                $wajibText[] = "- Juz " . $qs['juz'] . " QS. $wno : " . $qs['nama_surah'];
                                                break;
                                            }
                                        }
                                    }
                                    $wajibStr = implode("\n", $wajibText);

                                    // Pilihan Text
                                    $pilihanStr = '-';
                                    if($pilihan) {
                                        foreach($alquranList as $qs) {
                                            if($qs['no_surah'] == $pilihan) {
                                                $pilihanStr = "- Juz " . $qs['juz'] . " QS. $pilihan : " . $qs['nama_surah'];
                                                break;
                                            }
                                        }
                                    }

                                    // Construct Message (Plain Text first)
                                    $msgRaw = "Assalamu'alaikum Wr. Wb.\n\n";
                                    $msgRaw .= "Informasi Munaqosah ananda\n";
                                    $msgRaw .= "Nama: *" . $peserta['nama_siswa'] . "*\n";
                                    $msgRaw .= "No Tes: *" . $peserta['no_peserta'] . "*\n\n";
                                    $msgRaw .= "*Materi Ujian Tahfidz:*\n";
                                    $msgRaw .= "1. Tahfidz Wajib:\n" . $wajibStr . "\n";
                                    $msgRaw .= "2. Tahfidz Pilihan:\n" . $pilihanStr . "\n\n";
                                    $msgRaw .= "Mohon dipersiapkan dengan baik. Terima kasih.";
                                    
                                    // Encode entire message
                                    $waLink = "https://wa.me/" . $noHp . "?text=" . urlencode($msgRaw);
                                ?>
                                <tr>
                                    <td class="text-center">
                                        <span class="badge badge-primary" style="font-size: 1.2em;"><?= esc($peserta['no_peserta']) ?></span>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <?php 
                                                $foto = !empty($peserta['foto']) && file_exists(FCPATH . $peserta['foto']) 
                                                    ? base_url($peserta['foto']) 
                                                    : 'https://ui-avatars.com/api/?name=' . urlencode($peserta['nama_siswa']) . '&background=random&size=32';
                                            ?>
                                            <img src="<?= $foto ?>" class="img-circle mr-2" style="width:32px; height:32px; object-fit:cover;">
                                            <div>
                                                <?= esc($peserta['nama_siswa']) ?>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <?= esc($peserta['nama_surah_sholat'] ?? '-') ?>
                                    </td>
                                    <td>
                                        <?php if (!empty($wajib)): ?>
                                            <?php foreach ($wajib as $noS): ?>
                                                <?php 
                                                    $namaS = '-'; $juzS = '-'; $noS_Display = $noS;
                                                    foreach($alquranList as $qs) {
                                                        if($qs['no_surah'] == $noS) { 
                                                            $namaS = $qs['nama_surah']; 
                                                            $juzS = $qs['juz'];
                                                            break; 
                                                        }
                                                    }
                                                ?>
                                                <div class="mb-1">
                                                    <span class="badge badge-info" style="font-size: 0.9em; font-weight: normal;">
                                                        <span class="badge badge-light mr-1" style="color: #333;">Juz <?= $juzS ?></span>
                                                        QS. <?= $noS ?> : <strong><?= $namaS ?></strong>
                                                    </span>
                                                </div>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            -
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <!-- Dropdown for Pilihan -->
                                        <select class="form-control form-control-sm select-pilihan" data-id="<?= $peserta['id'] ?>">
                                            <option value="">-- Pilih --</option>
                                            <?php 
                                                // Determine range for this student based on hafalan
                                                $hafalanJson = json_decode($peserta['hafalan'], true);
                                                $targetJuzList = [30]; // default at least 30
                                                
                                                if(json_last_error() === JSON_ERROR_NONE && is_array($hafalanJson)) {
                                                    $juzList = [];
                                                    foreach($hafalanJson as $h) {
                                                        if(isset($h['juz'])) $juzList[] = (int)$h['juz'];
                                                    }
                                                    if(!empty($juzList)) {
                                                        $targetJuzList = $juzList;
                                                        // Ensure juz 30 is at end (or sort asc)
                                                        sort($targetJuzList);
                                                    }
                                                }
                                                
                                                foreach ($alquranList as $qs):
                                                    if (in_array($qs['juz'], $targetJuzList)):
                                            ?>
                                                <option value="<?= $qs['no_surah'] ?>" <?= $pilihan == $qs['no_surah'] ? 'selected' : '' ?>>
                                                    (Juz <?= $qs['juz'] ?>) <?= $qs['nama_surah'] ?>
                                                </option>
                                            <?php 
                                                    endif;
                                                endforeach; 
                                            ?>
                                        </select>
                                    </td>
                                    <td class="text-center">
                                        <?php if($peserta['no_hp']): ?>
                                            <a href="<?= $waLink ?>" target="_blank" class="btn btn-success btn-sm" title="Kirim Info via WhatsApp">
                                                <i class="fab fa-whatsapp"></i>
                                            </a>
                                        <?php else: ?>
                                            <button class="btn btn-secondary btn-sm" disabled title="No HP tidak tersedia">
                                                <i class="fab fa-whatsapp"></i>
                                            </button>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection(); ?>

<?= $this->section('scripts'); ?>
<script>
    $(document).ready(function() {
        $('#tabelPeserta').DataTable({
            responsive: true,
            autoWidth: false,
            order: [[0, 'asc']]
        });
        
        // Save Tahfidz Pilihan on Change
        $('.select-pilihan').change(function() {
            var id = $(this).data('id');
            var val = $(this).val();
            
            $.ajax({
                url: '<?= base_url('backend/peserta/saveTahfidzPilihan') ?>',
                type: 'POST',
                data: {
                    <?= csrf_token() ?>: '<?= csrf_hash() ?>',
                    id_peserta: id,
                    surah_pilihan: val
                },
                success: function(res) {
                    if(res.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil!',
                            text: 'Pilihan surah berhasil disimpan',
                            timer: 1500,
                            showConfirmButton: false,
                            toast: true,
                            position: 'top-end'
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal',
                            text: 'Gagal menyimpan pilihan',
                            toast: true,
                            position: 'top-end',
                            showConfirmButton: false,
                            timer: 3000
                        });
                    }
                }
            });
        });
    });

    // Mulai Undian & Undian Tambahan
    $('#btnUndian, #btnUndianTambahan').click(function() {
        var isTambahan = $(this).attr('id') == 'btnUndianTambahan';
        var title = isTambahan ? 'Undian Tambahan?' : 'Mulai Undian No Tes?';
        var html = isTambahan 
            ? 'Sistem akan mengundi untuk <strong><?= $belumTerdaftar ?? 0 ?> siswa baru</strong> yang belum terdaftar.'
            : 'Sistem akan mengundi No Tes & Surah secara <strong>random</strong> untuk <?= $siswaWithHafalan ?> siswa yang punya hafalan.<br><br>Proses ini tidak dapat dibatalkan.';

        Swal.fire({
            title: title,
            html: html,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#28a745',
            confirmButtonText: '<i class="fas fa-dice"></i> Ya, Mulai Undian!',
            cancelButtonText: 'Batal',
            showLoaderOnConfirm: true,
            preConfirm: () => {
                return $.ajax({
                    url: '<?= base_url('backend/peserta/undian') ?>',
                    type: 'POST',
                    data: { <?= csrf_token() ?>: '<?= csrf_hash() ?>' },
                    dataType: 'json'
                }).then(response => response)
                  .catch(error => {
                      Swal.showValidationMessage('Terjadi kesalahan');
                  });
            },
            allowOutsideClick: () => !Swal.isLoading()
        }).then((result) => {
            if (result.isConfirmed && result.value) {
                if (result.value.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Undian Berhasil!',
                        text: result.value.message,
                        timer: 2000,
                        showConfirmButton: false
                    }).then(() => location.reload());
                } else {
                    Swal.fire('Gagal', result.value.message, 'error');
                }
            }
        });
    });

    // Reset Undian
    $('#btnReset').click(function() {
        Swal.fire({
            title: 'Reset Undian?',
            html: '<strong class="text-danger">PERINGATAN!</strong><br>Semua data peserta tahun ini akan dihapus!',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            confirmButtonText: '<i class="fas fa-trash"></i> Ya, Reset!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '<?= base_url('backend/peserta/reset') ?>',
                    type: 'POST',
                    data: { <?= csrf_token() ?>: '<?= csrf_hash() ?>' },
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Reset Berhasil!',
                                text: response.message,
                                timer: 1500,
                                showConfirmButton: false
                            }).then(() => location.reload());
                        } else {
                            Swal.fire('Gagal', response.message, 'error');
                        }
                    },
                    error: function() {
                        Swal.fire('Error', 'Terjadi kesalahan', 'error');
                    }
                });
            }
        });
    });
</script>
<?= $this->endSection(); ?>
