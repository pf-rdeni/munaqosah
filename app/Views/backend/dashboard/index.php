<?= $this->extend('backend/template/template'); ?>

<?= $this->section('content'); ?>
<!-- Halaman Dashboard - Statistik Ringkasan -->
<?php $groups = $user['groups'] ?? []; ?>

<!-- Informasi Selamat Datang -->
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-info-circle mr-2"></i>
                    Selamat Datang
                </h3>
            </div>
            <div class="card-body">
                <div class="callout callout-info">
                    <h5><i class="fas fa-graduation-cap mr-2"></i> Sistem Penilaian Ujian Munaqosah</h5>
                    <p>
                        Selamat datang di Sistem Penilaian Ujian Munaqosah SDIT An-Nahl. 
                        Sistem ini digunakan untuk mengelola data siswa, peserta ujian, 
                        dan proses penilaian ujian munaqosah.
                    </p>
                </div>
                
                <div class="row mt-4">
                    <?php if (in_array('admin', $groups) || in_array('panitia', $groups)): ?>
                    <div class="col-md-4">
                        <div class="info-box bg-light">
                            <span class="info-box-icon bg-info"><i class="fas fa-users"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Data Siswa</span>
                                <span class="info-box-number">Kelola data siswa SDIT An-Nahl</span>
                            </div>
                            <a href="<?= base_url('backend/siswa') ?>" class="stretched-link"></a>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="info-box bg-light">
                            <span class="info-box-icon bg-success"><i class="fas fa-clipboard-list"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Peserta Ujian</span>
                                <span class="info-box-number">Registrasi peserta ujian</span>
                            </div>
                            <a href="<?= base_url('backend/peserta') ?>" class="stretched-link"></a>
                        </div>
                    </div>
                     <div class="col-md-4">
                        <div class="info-box bg-light">
                            <span class="info-box-icon bg-danger"><i class="fas fa-book"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Materi & Kriteria</span>
                                <span class="info-box-number">Kelola data materi ujian</span>
                            </div>
                            <a href="<?= base_url('backend/materi') ?>" class="stretched-link"></a>
                        </div>
                    </div>

                    <!-- Grup Materi -->
                    <div class="col-md-4">
                        <div class="info-box bg-light">
                             <span class="info-box-icon bg-secondary"><i class="fas fa-layer-group"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Grup Materi</span>
                                <span class="info-box-number">Kelola pengelompokan materi</span>
                            </div>
                            <a href="<?= base_url('backend/grup-materi') ?>" class="stretched-link"></a>
                        </div>
                    </div>

                    <!-- Manajemen Juri -->
                    <div class="col-md-4">
                        <div class="info-box bg-light">
                             <span class="info-box-icon bg-primary"><i class="fas fa-gavel"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Manajemen Juri</span>
                                <span class="info-box-number">Kelola data juri & plotting</span>
                            </div>
                            <a href="<?= base_url('backend/juri') ?>" class="stretched-link"></a>
                        </div>
                    </div>

                    <?php if (in_array('admin', $groups)): ?>
                    <!-- Pengaturan User (Admin Only) -->
                    <div class="col-md-4">
                        <div class="info-box bg-light">
                             <span class="info-box-icon bg-dark"><i class="fas fa-users-cog"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Pengaturan User</span>
                                <span class="info-box-number">Kelola akun pengguna</span>
                            </div>
                            <a href="<?= base_url('backend/users') ?>" class="stretched-link"></a>
                        </div>
                    </div>
                    <?php endif; ?>

                    <?php endif; ?>

                    <?php if (in_array('juri', $groups)): ?>
                    <div class="col-md-4">
                        <div class="info-box bg-light">
                            <span class="info-box-icon bg-warning"><i class="fas fa-star"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Input Nilai</span>
                                <span class="info-box-number">Penilaian oleh juri</span>
                            </div>
                            <a href="<?= base_url('backend/munaqosah/input-nilai') ?>" class="stretched-link"></a>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>



<!-- Charts Row -->
<!-- Statistik Row (Small Boxes) -->
<div class="row">
    <!-- 1. Juri Comparison (My Count vs Others) -->
    <?php if (isset($juriComparison) && !empty($juriComparison)): ?>
    <div class="col-lg-3 col-6">
        <div class="small-box bg-purple">
            <div class="inner">
                <h3><?= $juriComparison['my_count'] ?></h3>
                <p>Juri: Saya Menilai</p>
            </div>
            <div class="icon">
                <i class="fas fa-user-check"></i>
            </div>
            <div class="small-box-footer" style="padding: 3px 0;">
                <span style="font-size: 0.9em;">
                    vs Juri Lain: <strong><?= $juriComparison['others_count'] ?></strong>
                    (<?= $juriComparison['others_label'] ?>)
                </span>
            </div>
        </div>
    </div>
    <?php else: ?>
        <!-- Placeholder for Admin/Panitia if needed, or empty -->
        <div class="col-lg-3 col-6">
            <div class="small-box bg-secondary">
                 <div class="inner">
                    <h3><?= $statistik['totalPeserta'] ?></h3>
                    <p>Total Peserta</p>
                </div>
                <div class="icon">
                    <i class="fas fa-users"></i>
                </div>
                <div class="small-box-footer">&nbsp;</div>
            </div>
        </div>
    <?php endif; ?>

    <!-- 2. Progress Penilaian -->
    <div class="col-lg-3 col-6">
        <div class="small-box bg-info">
            <div class="inner">
                <h3><?= $statistik['progressPercent'] ?><sup style="font-size: 20px">%</sup></h3>
                <p>Progress Total</p>
            </div>
            <div class="icon">
                <i class="ion ion-stats-bars"></i>
            </div>
             <a href="<?= base_url('backend/monitoring/nilai') ?>" class="small-box-footer">
                Lihat Monitoring <i class="fas fa-arrow-circle-right"></i>
            </a>
        </div>
    </div>

    <!-- 3. Selesai -->
    <div class="col-lg-3 col-6">
        <div class="small-box bg-success">
            <div class="inner">
                <h3><?= $statistik['statSelesai'] ?></h3>
                <p>Peserta Selesai</p>
            </div>
            <div class="icon">
                <i class="ion ion-checkmark-circled"></i>
            </div>
             <a href="<?= base_url('backend/monitoring/nilai') ?>" class="small-box-footer">
                Lihat Data <i class="fas fa-arrow-circle-right"></i>
            </a>
        </div>
    </div>

    <!-- 4. Belum -->
    <div class="col-lg-3 col-6">
        <div class="small-box bg-warning">
            <div class="inner">
                <h3><?= $statistik['statMenunggu'] ?></h3>
                <p>Belum Dinilai</p>
            </div>
            <div class="icon">
                <i class="ion ion-person-add"></i>
            </div>
             <a href="<?= base_url('backend/monitoring/nilai') ?>" class="small-box-footer">
                Lihat Data <i class="fas fa-arrow-circle-right"></i>
            </a>
        </div>
    </div>
</div>

<?php if (in_array('admin', $groups) || in_array('panitia', $groups) || in_array('juri', $groups)): ?>
<div class="row">
    <!-- Progress Penilaian -->
    <div class="col-md-6">
        <div class="card card-dark">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-chart-line mr-1"></i> Progress Penilaian Munaqosah</h3>
                <div class="card-tools">
                    <button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-minus"></i></button>
                    <button type="button" class="btn btn-tool" data-card-widget="remove"><i class="fas fa-times"></i></button>
                </div>
            </div>
            <div class="card-body text-center">
                <h5>Progres Peserta Dinilai</h5>
                <div style="min-height: 250px; display: flex; flex-direction: column; justify-content: center; align-items: center;">
                    <div class="chart-responsive">
                         <canvas id="progressChart" height="150"></canvas>
                    </div>
                    <div class="d-flex justify-content-center align-items-center mt-2">
                         <span class="mr-3 text-success"><i class="fas fa-check-circle"></i> Selesai: <strong><?= $statistik['statSelesai'] ?></strong></span>
                         <span class="text-warning"><i class="fas fa-spinner"></i> Proses: <strong><?= $statistik['statProses'] ?></strong></span>
                    </div>
                    <p class="text-muted mt-2">
                        Total: <?= $statistik['totalPeserta'] ?> Peserta
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistik Gender Peserta -->
    <div class="col-md-6">
        <div class="card card-dark">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-venus-mars mr-1"></i> Komposisi Peserta</h3>
                <div class="card-tools">
                    <button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-minus"></i></button>
                    <button type="button" class="btn btn-tool" data-card-widget="remove"><i class="fas fa-times"></i></button>
                </div>
            </div>
            <div class="card-body">
                <div class="chart-responsive">
                    <canvas id="genderChart" height="150"></canvas>
                </div>
                <!-- Legend -->
                <div class="d-flex justify-content-around mt-4">
                    <div class="text-primary">
                        <i class="fas fa-male fa-2x"></i><br>
                        <strong>Laki-laki</strong><br>
                        <h4><?= $statistik['genderL'] ?></h4>
                    </div>
                    <div class="text-pink">
                        <i class="fas fa-female fa-2x" style="color: #e83e8c;"></i><br>
                        <strong>Perempuan</strong><br>
                        <h4><?= $statistik['genderP'] ?></h4>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Rubrik Penilaian (Juri Only) -->
<?php if (in_array('juri', $groups) && !empty($rubrikData)): ?>

    <?php foreach ($rubrikData as $rData): ?>
    <?php 
        $materi   = $rData['materi'];
        $kriteria = $rData['kriteria'];
        $map      = $rData['map'];
    ?>
    <div class="row">
        <div class="col-md-12">
            <div class="card card-outline card-info collapsed-card">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-book-open mr-2"></i> Panduan & Rubrik Penilaian: <?= esc($materi['nama_materi']) ?></h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-plus"></i></button>
                        <button type="button" class="btn btn-tool" data-card-widget="remove"><i class="fas fa-times"></i></button>
                    </div>
                </div>
                <div class="card-body" style="display: none;">
                    <div class="row">
                        <!-- Tabel 1: Aspek dan Bobot -->
                        <div class="col-md-6">
                            <h5><i class="fas fa-list-ol mr-2"></i> Aspek dan Bobot Penilaian</h5>
                            <table class="table table-bordered table-sm">
                                <thead class="bg-light">
                                    <tr>
                                        <th style="width: 10px">No</th>
                                        <th>Aspek Penilaian</th>
                                        <th>Kriteria Utama</th>
                                        <th style="width: 100px">Skor Maks</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php $no = 1; foreach ($kriteria as $k): ?>
                                    <tr>
                                        <td><?= $no++ ?></td>
                                        <td><?= esc($k['nama_kriteria']) ?></td>
                                        <td><?= esc($k['deskripsi']) ?></td>
                                        <td class="text-center"><strong><?= intval($k['bobot']) ?></strong></td>
                                    </tr>
                                    <?php endforeach; ?>
                                    <tr class="bg-light">
                                        <td colspan="3" class="text-right"><strong>Total Skor</strong></td>
                                        <td class="text-center"><strong><?= esc($materi['nilai_maksimal']) ?></strong></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- Panduan Skor (Global Predikats) -->
                        <div class="col-md-6">
                            <h5><i class="fas fa-star mr-2"></i> Kriteria Skor (Panduan Juri)</h5>
                            <?php foreach ($predikats as $p): ?>
                            <div class="callout callout-<?= esc($p['class_css']) ?>">
                                <strong><?= esc($p['nama_predikat']) ?> (<?= $p['min_nilai'] ?>-<?= $p['max_nilai'] ?>)</strong><br>
                                <?= esc($p['deskripsi_global']) ?>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <hr>

                    <!-- Tabel 2: Detail Rubrik -->
                    <div class="row mt-4">
                         <div class="col-md-12">
                            <h5><i class="fas fa-table mr-2"></i> Detail Rubrik Penilaian</h5>
                            <div class="table-responsive">
                                <table class="table table-bordered table-striped text-sm">
                                    <thead class="bg-navy">
                                        <tr>
                                            <th style="width: 5%">No</th>
                                            <th style="width: 20%">Aspek</th>
                                            <?php foreach ($predikats as $p): ?>
                                            <th>
                                                <?= esc($p['nama_predikat']) ?><br>
                                                <small>(<?= $p['min_nilai'] ?>-<?= $p['max_nilai'] ?>)</small>
                                            </th>
                                            <?php endforeach; ?>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php $no = 1; foreach ($kriteria as $k): ?>
                                        <tr>
                                            <td><?= $no++ ?></td>
                                            <td><strong><?= esc($k['nama_kriteria']) ?></strong></td>
                                            <?php foreach ($predikats as $p): ?>
                                                <td><?= nl2br(esc($map[$k['id']][$p['id']] ?? '-')) ?></td>
                                            <?php endforeach; ?>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                         </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endforeach; ?>

<?php endif; ?>

<!-- Monitoring Grup Juri Card (Juri Only) -->
<?php if (in_array('juri', $groups) && !empty($grupJuriMonitoring)): ?>
<?php
    $gjm = $grupJuriMonitoring;
    $juriNames = array_map(function($j) { return $j['nama_juri']; }, $gjm['juris']);
?>
<div class="row">
    <div class="col-md-12">
        <div class="card card-outline <?= $gjm['countBelum'] > 0 ? 'card-warning' : 'card-success' ?> collapsed-card" id="cardGrupJuriMonitoring">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-users-cog mr-2"></i>
                    Monitoring Grup Juri <?= $gjm['grupId'] ?>
                    <span class="badge badge-light ml-2"><?= esc($gjm['grupMateriName']) ?></span>
                    <small class="text-muted ml-2">Juri: <strong><?= esc(implode(', ', $juriNames)) ?></strong></small>
                </h3>
                <div class="card-tools">
                    <span class="badge badge-success mr-1" title="Lengkap"><i class="fas fa-check"></i> <?= $gjm['countLengkap'] ?></span>
                    <span class="badge badge-warning mr-1" title="Belum Lengkap"><i class="fas fa-exclamation"></i> <?= $gjm['countBelum'] ?></span>
                    <span class="badge badge-info mr-1" title="Total"><i class="fas fa-users"></i> <?= $gjm['totalPeserta'] ?></span>
                    <span class="badge badge-light" id="gjmRefreshCountdown" title="Auto refresh"></span>
                    <button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-plus"></i></button>
                    <button type="button" class="btn btn-tool" data-card-widget="remove"><i class="fas fa-times"></i></button>
                </div>
            </div>
            <div class="card-body p-2" style="display: none;">
                <?php if (empty($gjm['matrix'])): ?>
                    <p class="text-muted text-center py-3">Belum ada peserta yang dinilai oleh grup ini.</p>
                <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-bordered table-striped table-hover table-sm text-sm" id="tblGrupJuriDashboard" style="width:100%">
                        <thead class="bg-light">
                            <tr>
                                <th class="text-center" width="40">No</th>
                                <th class="text-center" width="120">No Peserta</th>
                                <th style="min-width: 180px;">Nama Peserta</th>
                                <?php foreach ($gjm['juris'] as $j): ?>
                                <th class="text-center" style="min-width: 130px;">
                                    <i class="fas fa-gavel mr-1 text-muted"></i>
                                    <?= esc($j['nama_juri']) ?>
                                </th>
                                <?php endforeach; ?>
                                <th class="text-center" width="140">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $no = 1; foreach ($gjm['matrix'] as $np => $mRow): ?>
                            <tr class="<?= !$mRow['complete'] ? 'table-warning' : '' ?>">
                                <td class="text-center"><?= $no++ ?></td>
                                <td class="text-center font-weight-bold"><?= esc($np) ?></td>
                                <td><?= esc($mRow['nama']) ?></td>
                                <?php foreach ($gjm['juris'] as $j): ?>
                                <td class="text-center">
                                    <?php if ($mRow['scores'][$j['id']]): ?>
                                        <span class="badge badge-success" style="font-size: 1rem;"><i class="fas fa-check"></i></span>
                                    <?php else: ?>
                                        <span class="badge badge-danger" style="font-size: 1rem;"><i class="fas fa-times"></i></span>
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
    </div>
</div>
<?php endif; ?>

<!-- List Penilaian Juri -->
<?php if (!empty($listDinilai)): ?>
<div class="row">
    <div class="col-md-12">
        <div class="card card-primary card-outline">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-history mr-2"></i> Riwayat Penilaian Anda</h3>
                <div class="card-tools">
                    <button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-minus"></i></button>
                    <button type="button" class="btn btn-tool" data-card-widget="remove"><i class="fas fa-times"></i></button>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th style="width: 50px" class="text-center">No</th>
                                <th>Nama Peserta</th>
                                <th>Nomor Peserta</th>
                                <th>Waktu Penilaian</th>
                                <th>Lama Pengujian</th>
                                <th class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $no = 1; foreach ($listDinilai as $row): ?>
                            <tr>
                                <td class="text-center"><?= $no++ ?></td>
                                <td><?= esc($row['nama_siswa']) ?></td>
                                <td><span class="badge badge-info"><?= esc($row['no_peserta']) ?></span></td>
                                <td><?= \CodeIgniter\I18n\Time::parse($row['tgl_nilai'])->humanize() ?></td>
                                <td>
                                    <?php if ($row['lama_ujian'] !== '-'): ?>
                                        <span class="badge badge-warning"><?= esc($row['lama_ujian']) ?></span>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <a href="<?= base_url('backend/munaqosah/input-nilai?peserta=' . $row['no_peserta']) ?>" class="btn btn-sm btn-primary">
                                        <i class="fas fa-edit mr-1"></i> Edit / Lihat
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer clearfix">
                <a href="<?= base_url('backend/munaqosah/input-nilai') ?>" class="btn btn-sm btn-secondary float-right">Lihat Semua di Halaman Penilaian</a>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>



<?= $this->endSection(); ?>

<?= $this->section('scripts'); ?>
<!-- ChartJS -->
<script src="<?= base_url('template/backend/plugins/chart.js/Chart.min.js') ?>"></script>
<script>
    $(function () {
        /* ChartJS
         * -------
         * Here we will create a few charts using ChartJS
         */

        // --- GENDER CHART ---
        var donutChartCanvas = $('#genderChart').get(0).getContext('2d')
        var genderData        = {
            labels: [
                'Laki-laki', 
                'Perempuan', 
            ],
            datasets: [
                {
                    data: [<?= $statistik['genderL'] ?>, <?= $statistik['genderP'] ?>],
                    backgroundColor : ['#007bff', '#e83e8c'],
                }
            ]
        }
        var donutOptions     = {
            maintainAspectRatio : false,
            responsive : true,
            legend: {
                display: false
            }
        }
        new Chart(donutChartCanvas, {
            type: 'doughnut',
            data: genderData,
            options: donutOptions
        })

        // --- PROGRESS CHART ---
        // We'll use a fast doughnut chart as a percentage indicator
        // --- PROGRESS CHART ---
        // 3 Segments: Selesai, Sedang Dinilai, Belum
        var progressCanvas = $('#progressChart').get(0).getContext('2d')
        var pSelesai = <?= $statistik['progressPercent'] ?>;
        var pProses  = <?= $statistik['percentProses'] ?>;
        // Remaining to make 100%
        var pBelum   = 100 - (pSelesai + pProses);
        // Avoid negative rounding errors
        if (pBelum < 0) pBelum = 0;

        var progressData = {
            labels: ['Selesai', 'Sedang Dinilai', 'Belum'],
            datasets: [{
                data: [pSelesai, pProses, pBelum],
                backgroundColor: ['#28a745', '#ffc107', '#d6d8d9'], // Green, Yellow, Gray
                borderWidth: 0
            }]
        }
        new Chart(progressCanvas, {
            type: 'doughnut',
            data: progressData,
            options: {
                maintainAspectRatio: false,
                responsive: true,
                rotation: -1.0 * Math.PI, // Start from top (half circle)
                circumference: 1 * Math.PI, // Half circle
                legend: { 
                    display: true,
                    position: 'bottom' 
                },
                cutoutPercentage: 70
            }
        })
    })
</script>
<script>
// --- Dashboard: Monitoring Grup Juri Auto Refresh ---
$(function() {
    var $card = $('#cardGrupJuriMonitoring');
    if ($card.length === 0) return;

    var GJM_INTERVAL = 30000;

    // Init DataTable
    if ($('#tblGrupJuriDashboard').length && !$.fn.DataTable.isDataTable('#tblGrupJuriDashboard')) {
        $('#tblGrupJuriDashboard').DataTable({
            "paging": false, "searching": true, "ordering": true, "info": false,
            "autoWidth": false, "responsive": true,
            "language": { "search": "Cari:", "zeroRecords": "Tidak ada data" }
        });
    }

    // Auto refresh
    setInterval(function() {
        var isCollapsed = $card.hasClass('collapsed-card');
        $.ajax({
            url: window.location.href,
            type: 'GET',
            dataType: 'html',
            success: function(resp) {
                var $new = $(resp).find('#cardGrupJuriMonitoring');
                if ($new.length) {
                    var scrollTop = $(window).scrollTop();
                    // Update card body & header badges
                    $card.find('.card-body').html($new.find('.card-body').html());
                    $card.find('.card-tools .badge-success').first().html($new.find('.card-tools .badge-success').first().html());
                    $card.find('.card-tools .badge-warning').first().html($new.find('.card-tools .badge-warning').first().html());
                    $card.find('.card-tools .badge-info').first().html($new.find('.card-tools .badge-info').first().html());

                    // Re-init DataTable
                    if ($('#tblGrupJuriDashboard').length && !$.fn.DataTable.isDataTable('#tblGrupJuriDashboard')) {
                        $('#tblGrupJuriDashboard').DataTable({
                            "paging": false, "searching": true, "ordering": true, "info": false,
                            "autoWidth": false, "responsive": true,
                            "language": { "search": "Cari:", "zeroRecords": "Tidak ada data" }
                        });
                    }

                    // Restore collapse state
                    if (isCollapsed && !$card.hasClass('collapsed-card')) {
                        $card.find('.card-body').hide();
                        $card.addClass('collapsed-card');
                    } else if (!isCollapsed && $card.hasClass('collapsed-card')) {
                        $card.find('.card-body').show();
                        $card.removeClass('collapsed-card');
                    }
                    $(window).scrollTop(scrollTop);
                }
            }
        });
    }, GJM_INTERVAL);

    // Countdown
    var countdown = GJM_INTERVAL / 1000;
    setInterval(function() {
        countdown--;
        if (countdown <= 0) countdown = GJM_INTERVAL / 1000;
        $('#gjmRefreshCountdown').html('<i class="fas fa-clock mr-1"></i>' + countdown + 's');
    }, 1000);
});
</script>
<?= $this->endSection(); ?>
