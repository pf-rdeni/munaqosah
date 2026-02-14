<?= $this->extend('backend/template/template') ?>

<?= $this->section('content') ?>

<!-- 1. Statistik Info Box -->
<div class="row">
    <div class="col-md-3">
         <div class="small-box bg-info">
            <div class="inner" style="padding: 10px;">
                <h3 style="font-size: 1.8rem; margin-bottom: 0;"><?= $total_peserta ?></h3>
                <p style="margin-bottom: 0;">Total Peserta</p>
                <small style="font-size: 0.7rem;">Total terdaftar hari ini</small>
            </div>
            <div class="icon" style="top: 10px;">
                <i class="fas fa-users" style="font-size: 50px;"></i>
            </div>
        </div>
    </div>
    <div class="col-md-3">
         <div class="small-box bg-success">
            <div class="inner" style="padding: 10px;">
                <h3 style="font-size: 1.8rem; margin-bottom: 0;"><?= $stats[2] ?></h3>
                <p style="margin-bottom: 0;">Sudah Diuji</p>
                <small style="font-size: 0.7rem;"><?= $progress ?>% Selesai</small>
            </div>
            <div class="icon" style="top: 10px;">
                <i class="fas fa-check-circle" style="font-size: 50px;"></i>
            </div>
        </div>
    </div>
    <div class="col-md-3">
         <div class="small-box bg-warning">
            <div class="inner" style="padding: 10px;">
                <h3 style="font-size: 1.8rem; margin-bottom: 0;"><?= $stats[1] ?></h3>
                <p style="margin-bottom: 0;">Sedang Ujian</p>
                <small style="font-size: 0.7rem;"><?= $stats[0] ?> Menunggu</small>
            </div>
            <div class="icon" style="top: 10px;">
                <i class="fas fa-clock" style="font-size: 50px;"></i>
            </div>
        </div>
    </div>
    <div class="col-md-3">
         <div class="small-box bg-primary">
            <div class="inner" style="padding: 10px;">
                <h3 style="font-size: 1.8rem; margin-bottom: 0;"><?= $progress ?><sup style="font-size: 15px">%</sup></h3>
                <p style="margin-bottom: 0;">Progress</p>
                <small style="font-size: 0.7rem;">Tingkat Penyelesaian</small>
            </div>
            <div class="icon" style="top: 10px;">
                <i class="fas fa-chart-line" style="font-size: 50px;"></i>
            </div>
        </div>
    </div>
</div>

<!-- 2. Input Registrasi (Horizontal) & Filter -->
<div class="row mb-3">
    <div class="col-md-12">
         <form id="form-tambah-antrian" class="form-inline bg-white p-3 border rounded shadow-sm justify-content-between">
             
             <div class="input-group" style="width: 80%;">
                 <input type="text" class="form-control form-control-lg mr-2" name="no_peserta" id="input_no_peserta" placeholder="Ketik atau scan QR no peserta..." style="flex: 1;" required autocomplete="off">
                 
                     <div class="input-group-append">
                        <select class="form-control form-control-lg mr-2" name="id_grup" id="input_id_grup" required style="max-width: 200px;" onchange="window.location.href='<?= base_url('backend/antrian') ?>?grup='+this.value">
                            <option value="">-- Pilih Grup --</option>
                            <?php foreach ($grup_materi as $g): ?>
                                <option value="<?= $g['id'] ?>" <?= ($filter_grup == $g['id']) ? 'selected' : '' ?>>
                                    <?= $g['nama_grup_materi'] ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                         <button type="button" class="btn btn-warning btn-lg font-weight-bold mr-1"><i class="fas fa-qrcode mr-1"></i> Scan</button>
                         <button type="submit" class="btn btn-primary btn-lg font-weight-bold"><i class="fas fa-plus mr-1"></i> Registrasi</button>
                     </div>
                 </div>

                 <div class="btn-group">
                     <a href="<?= base_url('backend/antrian/monitoring') . ($filter_grup ? '?grup=' . $filter_grup : '') ?>" target="_blank" class="btn btn-info btn-lg" title="Monitoring TV">
                         <i class="fas fa-tv"></i>
                     </a>
                     <button type="button" class="btn btn-default btn-lg" onclick="location.reload()" title="Refresh">
                         <i class="fas fa-sync"></i>
                     </button>
                 </div>
             </form>
        </div>
    </div>

    <!-- 3. Status Ruangan -->
    <h5 class="mb-2 text-muted">Status Ruangan</h5>
    <div class="row">
        <?php 
            $roomCount = count($room_status);
            $roomCount = count($room_status);
            // Dynamic Column Sizing
            $colClass = 'col-md-4'; 
            if($roomCount == 1) $colClass = 'col-md-12';
            elseif($roomCount == 2) $colClass = 'col-md-6';
            elseif($roomCount == 4) $colClass = 'col-md-3';

            // Color Palette for Groups (Avoid: Yellow/Warning, Red/Danger, Green/Success/Olive)
            $groupColors = ['bg-primary', 'bg-info', 'bg-navy', 'bg-purple', 'bg-pink', 'bg-teal', 'bg-indigo', 'bg-lightblue', 'bg-secondary', 'bg-maroon', 'bg-fuchsia'];
        ?>
        <?php foreach ($room_status as $room): ?>
            <?php 
                $isActive = $room['is_active'];
                $occupant = $room['occupant'] ?? null;
                $statusAntrian = $occupant['status_antrian'] ?? 'kosong';
                
                // Default Empty State (Green)
                $cardClass = 'bg-success';
                $badgeIcon = '<i class="fas fa-door-open mr-1"></i>';
                $badgeText = 'Kosong';
                $badgeClass = 'text-success';
                $textClass = 'text-white'; // Default text color
                
                $occupantText = 'Kosong';
                $occupantNo = '';
                $occupantId = 0;
                $occupantGrupId = 0;
                
                $btnAction = ''; // HTML for the main action button

                $occupantPhoto = '';
                
                if ($isActive && $occupant) {
                    $occupantNo = $occupant['no_peserta'];
                    $occupantName = $occupant['nama_siswa'] ?? '';
                    $occupantText = "Peserta: <b>$occupantNo</b> - $occupantName";
                    $occupantId = $occupant['id'];
                    $occupantGrupId = $occupant['id_grup_materi']; 
                    
                    // Photo Logic
                    $fotoName = $occupant['foto'] ?? '';
                    $photoPath = '';
                    
                    if (!empty($fotoName) && $fotoName != 'default.png') {
                        // Fix: Use base_url($fotoName) directly as DB likely stores the path 'assets/img/siswa/...'
                        $photoPath = base_url($fotoName);
                    } else {
                        // Use UI Avatars for 2-letter initials if photo is missing/default
                        $photoPath = 'https://ui-avatars.com/api/?name=' . urlencode($occupantName) . '&background=random&color=fff&bold=true&length=2';
                    }
                    
                    $occupantPhoto = '<img src="'.$photoPath.'" class="img-circle elevation-2 mr-2" style="width: 40px; height: 40px; object-fit: cover; border: 2px solid white;">';

                    if ($statusAntrian == 'dipanggil') {
                        // State: Dipanggil (Calling) -> Yellow Card
                        $cardClass = 'bg-warning';
                        $badgeIcon = '<i class="fas fa-bullhorn mr-1"></i>';
                        $badgeText = 'Memanggil';
                        $badgeClass = 'text-warning';
                        $textClass = 'text-dark'; // Change text to dark for contrast
                        
                        // Button: Mulai (Blue)
                         $btnAction = '<button type="button" class="btn btn-primary btn-block rounded-0 font-weight-bold btn-sm" onclick="updateStatus('.$occupantId.', \'sedang_ujian\', '.$occupantGrupId.')">
                                        <i class="fas fa-play mr-1"></i> Mulai
                                      </button>';
                        
                    } elseif ($statusAntrian == 'sedang_ujian') {
                        // State: Sedang Ujian (Exam) -> Red Card
                        $cardClass = 'bg-danger';
                        $badgeIcon = '<i class="fas fa-users mr-1"></i>';
                        $badgeText = 'Penuh';
                        $badgeClass = 'text-danger';
                        
                         // Button: Selesai (Green)
                         $btnAction = '<button type="button" class="btn btn-success btn-block rounded-0 font-weight-bold btn-sm" onclick="updateStatus('.$occupantId.', \'selesai\', '.$occupantGrupId.')">
                                        <i class="fas fa-check mr-1"></i> Selesai
                                      </button>';
                    }
                }
            ?>
            <div class="<?= $colClass ?>">
                <div class="card <?= $cardClass ?>" style="min-height: 110px; position: relative; overflow: hidden;">
                    <div class="card-body pt-2 pl-2 pr-2 pb-0">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <h5 class="font-weight-bold m-0 <?= $textClass ?>"><?= $room['room_name'] ?></h5>
                            <?php 
                                $grupId = $room['id_grup_materi'] ?? 0;
                                $grupName = $room['nama_grup_materi'] ?? 'Grup';
                                $colorIndex = $grupId % count($groupColors);
                                $colorClass = $groupColors[$colorIndex];
                            ?>
                             <span class="badge <?= $colorClass ?> shadow-sm mx-1 text-truncate" style="font-size: 0.8rem; max-width: 40%; color: white;" title="<?= $grupName ?>"><?= $grupName ?></span>
                            <span class="badge badge-light <?= $badgeClass ?>"><?= $badgeIcon . $badgeText ?></span>
                        </div>
                        
                        <div class="d-flex align-items-center mb-2">
                             <?php if(!empty($occupantPhoto)) echo $occupantPhoto; ?>
                             <p class="<?= $textClass ?> mb-0" style="font-size: 0.9rem; line-height: 1.2;"><?= $occupantText ?></p>
                        </div>
                        
                    </div>
                    
                    <?php if($isActive): ?>
                    <div class="" style="position: absolute; bottom: 0; width: 100%;">
                        <div class="row no-gutters">
                            <div class="col-6">
                                <?= $btnAction ?>
                            </div>
                            <div class="col-6">
                                <button type="button" class="btn btn-secondary btn-block rounded-0 font-weight-bold btn-sm" onclick="updateStatus(<?= $occupantId ?>, 'menunggu')">
                                    <i class="fas fa-undo mr-1"></i> Batal
                                </button>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <hr>

    <!-- 4. Tabel Antrian -->
    <div class="row">
        <div class="col-md-12">
            <div class="card card-outline card-info">
                <div class="card-header">
                     <h3 class="card-title">Daftar Antrian</h3>
                     <!-- Dropdown Removed from Here -->
                </div>
                <div class="card-body table-responsive">
                <table id="tableAntrian" class="table table-bordered table-striped table-hover">
                    <thead>
                        <tr>
                            <th width="5%" class="text-center">No</th>
                            <th class="text-center" width="30%">Aksi</th>
                            <th>Peserta</th>
                            <th>Materi</th>
                            <th class="text-center">Ruang</th>
                            <th class="text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(!empty($antrian)): ?>
                            <?php $no=1; foreach($antrian as $a): ?>
                            <tr id="row-<?= $a['id'] ?>">
                                <td class="text-center font-weight-bold" style="vertical-align: middle; font-size: 1.2rem;">
                                    <?= str_replace('Peserta-', '', $a['no_peserta']) ?>
                                </td>
                                <td class="text-center">
                                    <div class="btn-group">
                                        <?php if($a['status_antrian'] == 'menunggu'): ?>
                                            <button type="button" class="btn btn-warning" onclick="updateStatus(<?= $a['id'] ?>, 'dipanggil', '<?= $a['id_grup_materi'] ?>')" title="Panggil">
                                                <i class="fas fa-bullhorn mr-1"></i> Panggil
                                            </button>
                                            <button type="button" class="btn btn-primary" onclick="updateStatus(<?= $a['id'] ?>, 'sedang_ujian', '<?= $a['id_grup_materi'] ?>')" title="Mulai Ujian">
                                                 <i class="fas fa-play mr-1"></i> Mulai
                                            </button>
                                            <button type="button" class="btn btn-danger" onclick="hapusAntrian(<?= $a['id'] ?>)" title="Hapus">
                                                <i class="fas fa-trash mr-1"></i> Hapus
                                            </button>
                                        <?php elseif($a['status_antrian'] == 'dipanggil'): ?>
                                             <button type="button" class="btn btn-primary" onclick="updateStatus(<?= $a['id'] ?>, 'sedang_ujian', '<?= $a['id_grup_materi'] ?>')" title="Mulai Ujian">
                                                 <i class="fas fa-play mr-1"></i> Mulai
                                            </button>
                                            <button type="button" class="btn btn-secondary" onclick="updateStatus(<?= $a['id'] ?>, 'menunggu')" title="Kembali Menunggu">
                                                 <i class="fas fa-undo mr-1"></i> Reset
                                            </button>
                                        <?php elseif($a['status_antrian'] == 'sedang_ujian'): ?>
                                            <button type="button" class="btn btn-success" onclick="updateStatus(<?= $a['id'] ?>, 'selesai')" title="Selesai">
                                                <i class="fas fa-check mr-1"></i> Selesai
                                            </button>
                                             <button type="button" class="btn btn-secondary" onclick="updateStatus(<?= $a['id'] ?>, 'menunggu')" title="Reset">
                                                 <i class="fas fa-undo mr-1"></i> Reset
                                            </button>
                                        <?php else: ?>
                                            <span class="text-success mr-2"><i class="fas fa-check-circle mr-1"></i> Selesai</span>
                                            <button type="button" class="btn btn-sm text-warning font-weight-bold" style="background: transparent; border: none; box-shadow: none;" onclick="updateStatus(<?= $a['id'] ?>, 'menunggu')" title="Reset">
                                                <i class="fas fa-undo mr-1"></i> Reset
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td>
                                    <div class="user-block">
                                        <img class="img-circle img-bordered-sm" src="<?= base_url($a['Foto'] ? $a['Foto'] : 'template/backend/dist/img/default-150x150.png') ?>" alt="user image" style="width:40px; height:40px; object-fit:cover;">
                                        <span class="username" style="font-size: 1rem;">
                                            <span class="d-block"><?= esc($a['NamaSiswa']) ?></span>
                                        </span>
                                        <span class="description">NISN: <?= esc($a['nisn']) ?></span>
                                    </div>
                                </td>
                                <td style="vertical-align: middle;">
                                    <?php 
                                        $grupId = $a['id_grup_materi'] ?? 0;
                                        $colorIndex = $grupId % count($groupColors);
                                        $colorClass = $groupColors[$colorIndex];
                                    ?>
                                    <span class="badge <?= $colorClass ?>"><?= esc($a['NamaGrup']) ?></span>
                                    <br>
                                    <small class="text-muted"><i class="fas fa-clock mr-1"></i> <?= $a['created_at'] ?></small>
                                </td>
                                <td class="text-center" style="vertical-align: middle;">
                                    <?php if($a['id_grup_juri']): ?>
                                        <span class="badge badge-info" style="font-size: 1rem;">Ruang <?= $a['id_grup_juri'] ?></span>
                                    <?php else: ?>
                                        -
                                    <?php endif; ?>
                                </td>
                                <td class="text-center status-column" style="vertical-align: middle;">
                                    <?php
                                        $badges = [
                                            'menunggu' => 'badge-secondary',
                                            'dipanggil' => 'badge-warning',
                                            'sedang_ujian' => 'badge-primary',
                                            'selesai' => 'badge-success'
                                        ];
                                        $labels = [
                                           'menunggu' => 'Menunggu',
                                           'dipanggil' => 'Dipanggil',
                                           'sedang_ujian' => 'Sedang Ujian',
                                           'selesai' => 'Selesai' 
                                        ];
                                        $statusClass = $badges[$a['status_antrian']] ?? 'badge-secondary';
                                        $statusLabel = $labels[$a['status_antrian']] ?? $a['status_antrian'];
                                    ?>
                                    <span class="badge <?= $statusClass ?>"><?= $statusLabel ?></span>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    $(document).ready(function() {
        $('#tableAntrian').DataTable({
            "responsive": true,
            "autoWidth": false,
            "ordering": false // Keep original sorting
        });

        $('#form-tambah-antrian').submit(function(e) {
            e.preventDefault();
            // Validasi Input
            if($('#input_id_grup').val() == '') {
                Swal.fire('Error', 'Pilih Grup Materi terlebih dahulu', 'warning');
                return;
            }

            $.ajax({
                url: '<?= base_url('backend/antrian/register') ?>',
                type: 'POST',
                data: $(this).serialize(),
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        Swal.fire({
                             icon: 'success',
                             title: 'Berhasil',
                             text: response.message,
                             timer: 1000,
                             showConfirmButton: false
                        }).then(() => {
                            // Gunakan ID Grup dari response server untuk redirect yang akurat
                            const grupId = response.id_grup;
                            if (grupId) {
                                window.location.href = '<?= base_url('backend/antrian') ?>?grup=' + grupId;
                            } else {
                                location.reload();
                            }
                        });
                        $('#input_no_peserta').val('');
                        $('#input_no_peserta').focus();
                    } else {
                        Swal.fire('Gagal', response.message, 'error');
                    }
                },
                error: function() {
                    Swal.fire('Error', 'Terjadi kesalahan server', 'error');
                }
            });
        });
        
        // Auto focus input
        $('#input_no_peserta').focus();
    });

    function updateStatus(id, status, grupId = null) {
        // Skip confirmation for 'dipanggil' (Panggil) and 'sedang_ujian' (Mulai)
        if (status === 'dipanggil' || status === 'sedang_ujian') {
            processUpdateStatus(id, status);
            return;
        }

        let label = '';
        if(status == 'selesai') label = 'Selesaikan Ujian?';
        else if(status == 'menunggu') label = 'Batalkan dan Reset ke Menunggu?';

        Swal.fire({
            title: 'Konfirmasi',
            text: label,
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Ya',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                 processUpdateStatus(id, status);
            }
        });
    }

    function processUpdateStatus(id, status) {
         $.ajax({
            url: '<?= base_url('backend/antrian/update-status') ?>',
            type: 'POST',
            data: { id: id, status: status }, 
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    location.reload();
                } else {
                    Swal.fire('Gagal', response.message, 'error');
                }
            },
            error: function() {
                Swal.fire('Error', 'Terjadi kesalahan server', 'error');
            }
        });
    }

    function hapusAntrian(id) {
        Swal.fire({
            title: 'Hapus Antrian?',
            text: "Data antrian akan dihapus.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            confirmButtonText: 'Hapus',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                 $.ajax({
                    url: '<?= base_url('backend/antrian/delete') ?>',
                    type: 'POST',
                    data: { id: id },
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                             location.reload();
                        } else {
                            Swal.fire('Gagal', response.message, 'error');
                        }
                    },
                    error: function() {
                        Swal.fire('Error', 'Terjadi kesalahan server', 'error');
                    }
                });
            }
        });
    }
</script>
<?= $this->endSection() ?>
