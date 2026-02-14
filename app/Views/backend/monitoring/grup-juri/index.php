<?= $this->extend('backend/template/template'); ?>

<?= $this->section('content'); ?>
<div class="row">
    <div class="col-12">
        <div class="card card-primary card-outline">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-users-cog mr-1"></i>
                    Monitoring Pasangan Grup Juri
                </h3>
                <div class="card-tools">
                    <button type="button" class="btn btn-tool" onclick="location.reload()">
                        <i class="fas fa-sync-alt"></i>
                    </button>
                </div>
            </div>
            <div class="card-body">
                <?php if (empty($grupData)): ?>
                    <div class="text-center text-muted py-5">
                        <i class="fas fa-info-circle fa-3x mb-3"></i>
                        <p>Belum ada Grup Juri yang terkonfigurasi atau belum ada data penilaian.</p>
                    </div>
                <?php else: ?>

                    <!-- Summary Info Boxes -->
                    <div class="row mb-4">
                        <?php 
                        $totalAllPeserta = 0;
                        $totalAllLengkap = 0;
                        $totalAllBelum = 0;
                        foreach ($grupData as $gd) {
                            $totalAllPeserta += $gd['totalPeserta'];
                            $totalAllLengkap += $gd['countLengkap'];
                            $totalAllBelum += $gd['countBelum'];
                        }
                        ?>
                        <div class="col-md-3 col-sm-6">
                            <div class="info-box bg-info">
                                <span class="info-box-icon"><i class="fas fa-users"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Total Grup Juri</span>
                                    <span class="info-box-number"><?= count($grupData) ?></span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 col-sm-6">
                            <div class="info-box bg-primary">
                                <span class="info-box-icon"><i class="fas fa-clipboard-list"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Total Penilaian</span>
                                    <span class="info-box-number"><?= $totalAllPeserta ?></span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 col-sm-6">
                            <div class="info-box bg-success">
                                <span class="info-box-icon"><i class="fas fa-check-circle"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Lengkap</span>
                                    <span class="info-box-number"><?= $totalAllLengkap ?></span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 col-sm-6">
                            <div class="info-box bg-warning">
                                <span class="info-box-icon"><i class="fas fa-exclamation-triangle"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Belum Lengkap</span>
                                    <span class="info-box-number"><?= $totalAllBelum ?></span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Filter Row -->
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label for="filterGrupMateri" class="text-sm font-weight-bold">Filter Grup Materi:</label>
                            <select id="filterGrupMateri" class="form-control form-control-sm">
                                <option value="">-- Semua Grup Materi --</option>
                                <?php
                                    $uniqueGrupMateri = [];
                                    foreach ($grupData as $gd) {
                                        $key = $gd['grupMateriId'];
                                        if (!isset($uniqueGrupMateri[$key])) {
                                            $uniqueGrupMateri[$key] = $gd['grupMateriName'];
                                        }
                                    }
                                    ksort($uniqueGrupMateri);
                                ?>
                                <?php foreach ($uniqueGrupMateri as $gmId => $gmName): ?>
                                    <option value="<?= $gmId ?>"><?= esc($gmName) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="filterGrupJuri" class="text-sm font-weight-bold">Filter Grup Juri:</label>
                            <select id="filterGrupJuri" class="form-control form-control-sm">
                                <option value="">-- Semua Grup Juri --</option>
                                <?php foreach ($grupData as $gId => $gd): ?>
                                    <option value="<?= $gId ?>">Grup <?= $gId ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="filterStatus" class="text-sm font-weight-bold">Filter Status:</label>
                            <select id="filterStatus" class="form-control form-control-sm">
                                <option value="">-- Semua Status --</option>
                                <option value="lengkap">✅ Lengkap</option>
                                <option value="belum">⚠️ Belum Lengkap</option>
                            </select>
                        </div>
                        <div class="col-md-2 d-flex align-items-end">
                            <button type="button" class="btn btn-sm btn-outline-secondary" id="btnResetFilter">
                                <i class="fas fa-undo mr-1"></i> Reset Filter
                            </button>
                        </div>
                    </div>

                    <!-- Grup Juri Cards -->
                    <?php foreach ($grupData as $grupId => $gData): ?>
                    <?php
                        // Badge color mapping (same as sidebar)
                        $badges = [
                            1 => 'badge-primary', 2 => 'badge-secondary', 3 => 'badge-success',
                            4 => 'badge-danger', 5 => 'badge-warning', 6 => 'badge-info',
                            7 => 'badge-dark', 8 => 'bg-indigo', 9 => 'bg-lightblue',
                            10 => 'bg-navy', 11 => 'bg-olive', 12 => 'bg-lime',
                            13 => 'bg-fuchsia', 14 => 'bg-maroon', 15 => 'bg-purple'
                        ];
                        $badgeClass = $badges[$grupId] ?? 'badge-secondary';

                        $juriNames = array_map(function($j) { return $j['nama_juri']; }, $gData['juris']);
                    ?>
                    <div class="card card-outline <?= $gData['countBelum'] > 0 ? 'card-warning' : 'card-success' ?> mb-4 grup-juri-card" data-grup-juri="<?= $grupId ?>" data-grup-materi="<?= $gData['grupMateriId'] ?>" data-status="<?= $gData['countBelum'] > 0 ? 'belum' : 'lengkap' ?>">
                        <div class="card-header">
                            <h3 class="card-title">
                                <span class="badge <?= $badgeClass ?> mr-2" style="font-size: 1rem;">
                                    <i class="fas fa-users mr-1"></i> Grup <?= $grupId ?>
                                </span>
                                <span class="badge badge-light mr-2" style="font-size: 0.9rem;">
                                    <i class="fas fa-book mr-1"></i> <?= esc($gData['grupMateriName']) ?>
                                </span>
                                <small class="text-muted ml-2">
                                    Juri: <strong><?= esc(implode(', ', $juriNames)) ?></strong>
                                </small>
                            </h3>
                            <div class="card-tools">
                                <!-- Statistik mini -->
                                <span class="badge badge-success mr-1" title="Lengkap">
                                    <i class="fas fa-check"></i> <?= $gData['countLengkap'] ?>
                                </span>
                                <span class="badge badge-warning mr-1" title="Belum Lengkap">
                                    <i class="fas fa-exclamation"></i> <?= $gData['countBelum'] ?>
                                </span>
                                <span class="badge badge-info" title="Total Peserta">
                                    <i class="fas fa-users"></i> <?= $gData['totalPeserta'] ?>
                                </span>
                                <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                    <i class="fas fa-minus"></i>
                                </button>
                            </div>
                        </div>
                        <div class="card-body p-2">
                            <?php if (empty($gData['matrix'])): ?>
                                <p class="text-muted text-center py-3">Belum ada peserta yang dinilai oleh grup ini.</p>
                            <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-bordered table-striped table-hover table-sm text-sm tbl-grup-juri" style="width:100%">
                                    <thead class="bg-light">
                                        <tr>
                                            <th class="text-center" width="40">No</th>
                                            <th class="text-center" width="120">No Peserta</th>
                                            <th style="min-width: 180px;">Nama Peserta</th>
                                            <?php foreach ($gData['juris'] as $j): ?>
                                            <th class="text-center" style="min-width: 130px;">
                                                <i class="fas fa-gavel mr-1 text-muted"></i>
                                                <?= esc($j['nama_juri']) ?>
                                            </th>
                                            <?php endforeach; ?>
                                            <th class="text-center" width="140">Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php $no = 1; foreach ($gData['matrix'] as $np => $mRow): ?>
                                        <tr class="<?= !$mRow['complete'] ? 'table-warning' : '' ?>">
                                            <td class="text-center"><?= $no++ ?></td>
                                            <td class="text-center font-weight-bold"><?= esc($np) ?></td>
                                            <td><?= esc($mRow['nama']) ?></td>
                                            <?php foreach ($gData['juris'] as $j): ?>
                                            <td class="text-center">
                                                <?php if ($mRow['scores'][$j['id']]): ?>
                                                    <span class="badge badge-success" style="font-size: 1rem;">
                                                        <i class="fas fa-check"></i>
                                                    </span>
                                                <?php else: ?>
                                                    <span class="badge badge-danger" style="font-size: 1rem;">
                                                        <i class="fas fa-times"></i>
                                                    </span>
                                                <?php endif; ?>
                                            </td>
                                            <?php endforeach; ?>
                                            <td class="text-center">
                                                <?php if ($mRow['complete']): ?>
                                                    <span class="badge badge-success"><i class="fas fa-check-circle mr-1"></i> Lengkap</span>
                                                <?php else: ?>
                                                    <span class="badge badge-warning"><i class="fas fa-exclamation-triangle mr-1"></i> Belum Lengkap</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>

                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection(); ?>

<?= $this->section('scripts'); ?>
<script>
$(document).ready(function() {
    var LS_KEY = 'monitoringGrupJuri_filters';
    var AUTO_REFRESH_INTERVAL = 30000; // 30 detik

    // Init DataTables
    $('.tbl-grup-juri').each(function() {
        $(this).DataTable({
            "paging": false,
            "searching": true,
            "ordering": true,
            "info": false,
            "autoWidth": false,
            "responsive": true,
            "language": {
                "search": "Cari:",
                "zeroRecords": "Tidak ada data ditemukan",
            }
        });
    });

    // --- LocalStorage: Restore filters ---
    function restoreFilters() {
        try {
            var saved = JSON.parse(localStorage.getItem(LS_KEY));
            if (saved) {
                if (saved.materi) $('#filterGrupMateri').val(saved.materi);
                if (saved.juri) $('#filterGrupJuri').val(saved.juri);
                if (saved.status) $('#filterStatus').val(saved.status);
                applyFilters();
            }
        } catch(e) {}
    }

    // --- LocalStorage: Save filters ---
    function saveFilters() {
        var data = {
            materi: $('#filterGrupMateri').val(),
            juri: $('#filterGrupJuri').val(),
            status: $('#filterStatus').val()
        };
        localStorage.setItem(LS_KEY, JSON.stringify(data));
    }

    // Filter logic
    function applyFilters() {
        var filterMateri = $('#filterGrupMateri').val();
        var filterJuri = $('#filterGrupJuri').val();
        var filterStatus = $('#filterStatus').val();

        $('.grup-juri-card').each(function() {
            var card = $(this);
            var matchMateri = !filterMateri || card.data('grup-materi') == filterMateri;
            var matchJuri = !filterJuri || card.data('grup-juri') == filterJuri;
            var matchStatus = !filterStatus || card.data('status') == filterStatus;

            if (matchMateri && matchJuri && matchStatus) {
                card.show();
            } else {
                card.hide();
            }
        });

        saveFilters();
    }

    $('#filterGrupMateri, #filterGrupJuri, #filterStatus').on('change', applyFilters);

    $('#btnResetFilter').on('click', function() {
        $('#filterGrupMateri').val('');
        $('#filterGrupJuri').val('');
        $('#filterStatus').val('');
        $('.grup-juri-card').show();
        localStorage.removeItem(LS_KEY);
    });

    // Restore filter saat halaman dimuat
    restoreFilters();

    // --- Auto Refresh Background ---
    var refreshTimer = setInterval(function() {
        $.ajax({
            url: window.location.href,
            type: 'GET',
            dataType: 'html',
            success: function(response) {
                // Parse response dan ambil konten card-body utama
                var $newContent = $(response);
                var $newCardBody = $newContent.find('.card-primary .card-body');
                if ($newCardBody.length) {
                    // Simpan scroll position
                    var scrollTop = $(window).scrollTop();

                    // Replace konten
                    $('.card-primary .card-body').html($newCardBody.html());

                    // Re-init DataTables
                    $('.tbl-grup-juri').each(function() {
                        if (!$.fn.DataTable.isDataTable(this)) {
                            $(this).DataTable({
                                "paging": false,
                                "searching": true,
                                "ordering": true,
                                "info": false,
                                "autoWidth": false,
                                "responsive": true,
                                "language": {
                                    "search": "Cari:",
                                    "zeroRecords": "Tidak ada data ditemukan",
                                }
                            });
                        }
                    });

                    // Re-apply filters & restore scroll
                    restoreFilters();
                    $(window).scrollTop(scrollTop);
                }
            }
        });
    }, AUTO_REFRESH_INTERVAL);

    // Tampilkan countdown di header
    var $refreshBadge = $('<span class="badge badge-light ml-2" id="refreshCountdown" title="Auto refresh aktif"></span>');
    $('.card-primary .card-header .card-tools').prepend($refreshBadge);
    var countdown = AUTO_REFRESH_INTERVAL / 1000;
    setInterval(function() {
        countdown--;
        if (countdown <= 0) countdown = AUTO_REFRESH_INTERVAL / 1000;
        $('#refreshCountdown').html('<i class="fas fa-clock mr-1"></i>' + countdown + 's');
    }, 1000);
});
</script>
<?= $this->endSection(); ?>
