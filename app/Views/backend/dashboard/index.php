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
<?php if (in_array('juri', $groups) && isset($rubrikType)): ?>

    <!-- Rubrik SHOLAT -->
    <?php if ($rubrikType == 'sholat'): ?>
    <div class="row">
        <div class="col-md-12">
            <div class="card card-outline card-info collapsed-card">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-book-open mr-2"></i> Panduan & Rubrik Penilaian Munaqosah Shalat</h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-plus"></i></button>
                        <button type="button" class="btn btn-tool" data-card-widget="remove"><i class="fas fa-times"></i></button>
                    </div>
                </div>
                <div class="card-body" style="display: none;">
                    <div class="row">
                        <!-- Tabel 1: Aspek dan Kriteria -->
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
                                    <tr>
                                        <td>1</td>
                                        <td>Niat Salat</td>
                                        <td>Niat benar, jelas, dan sesuai jenis salat</td>
                                        <td class="text-center"><strong>10</strong></td>
                                    </tr>
                                    <tr>
                                        <td>2</td>
                                        <td>Bacaan Salat</td>
                                        <td>Bacaan benar, tartil, dan sesuai urutan</td>
                                        <td class="text-center"><strong>25</strong></td>
                                    </tr>
                                    <tr>
                                        <td>3</td>
                                        <td>Gerakan Salat</td>
                                        <td>Gerakan benar sesuai tuntunan (rukun lengkap)</td>
                                        <td class="text-center"><strong>25</strong></td>
                                    </tr>
                                    <tr>
                                        <td>4</td>
                                        <td>Tuma'ninah</td>
                                        <td>Tenang dan tidak tergesa-gesa dalam gerakan</td>
                                        <td class="text-center"><strong>15</strong></td>
                                    </tr>
                                    <tr>
                                        <td>5</td>
                                        <td>Dzikir & Doa</td>
                                        <td>Sikap sopan, fokus, dan penuh penghayatan</td>
                                        <td class="text-center"><strong>25</strong></td>
                                    </tr>
                                    <tr class="bg-light">
                                        <td colspan="3" class="text-right"><strong>Total Skor</strong></td>
                                        <td class="text-center"><strong>100</strong></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- Panduan Skor -->
                        <div class="col-md-6">
                            <h5><i class="fas fa-star mr-2"></i> Kriteria Skor (Panduan Juri)</h5>
                            <div class="callout callout-success">
                                <strong>Sangat Baik (86–100)</strong><br>
                                Gerakan dan bacaan sangat tepat, tuma’ninah baik, sikap khusyuk.
                            </div>
                            <div class="callout callout-info">
                                <strong>Baik (76–85)</strong><br>
                                Gerakan dan bacaan cukup tepat, terdapat kesalahan kecil.
                            </div>
                            <div class="callout callout-warning">
                                <strong>Cukup (66–75)</strong><br>
                                Masih ada beberapa kesalahan bacaan atau gerakan.
                            </div>
                            <div class="callout callout-danger">
                                <strong>Perlu Bimbingan (<65)</strong><br>
                                Banyak kesalahan bacaan dan gerakan salat.
                            </div>
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
                                            <th style="width: 15%">Aspek</th>
                                            <th style="width: 20%">Sangat Baik (86-100)</th>
                                            <th style="width: 20%">Baik (76-85)</th>
                                            <th style="width: 20%">Cukup (66-75)</th>
                                            <th style="width: 20%">Perlu Bimbingan (<65)</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>1</td>
                                            <td><strong>Niat Salat</strong></td>
                                            <td>Niat benar, lancar, sesuai jenis salat</td>
                                            <td>Niat benar, kurang lancar</td>
                                            <td>Niat kurang tepat</td>
                                            <td>Tidak dapat melafalkan niat</td>
                                        </tr>
                                        <tr>
                                            <td>2</td>
                                            <td><strong>Bacaan Salat</strong></td>
                                            <td>Bacaan lengkap, benar, tartil</td>
                                            <td>Bacaan cukup benar, ada kesalahan kecil</td>
                                            <td>Banyak kesalahan bacaan</td>
                                            <td>Bacaan tidak sesuai</td>
                                        </tr>
                                        <tr>
                                            <td>3</td>
                                            <td><strong>Gerakan Salat</strong></td>
                                            <td>Gerakan lengkap dan benar sesuai tuntunan</td>
                                            <td>Gerakan cukup benar</td>
                                            <td>Gerakan kurang tepat</td>
                                            <td>Gerakan banyak salah</td>
                                        </tr>
                                        <tr>
                                            <td>4</td>
                                            <td><strong>Tuma'ninah</strong></td>
                                            <td>Setiap gerakan tenang dan tidak tergesa</td>
                                            <td>Sebagian besar sudah tenang</td>
                                            <td>Masih sering tergesa</td>
                                            <td>Tidak tuma'ninah</td>
                                        </tr>
                                        <tr>
                                            <td>5</td>
                                            <td><strong>Dzikir Sholat</strong></td>
                                            <td>Fokus, sopan, penuh penghayatan</td>
                                            <td>Cukup fokus</td>
                                            <td>Kurang fokus</td>
                                            <td>Tidak menunjukkan kekhusyukan</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                         </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Rubrik WUDHU -->
    <?php if ($rubrikType == 'wudhu'): ?>
    <div class="row">
        <div class="col-md-12">
            <div class="card card-outline card-info collapsed-card">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-book-open mr-2"></i> Panduan & Rubrik Penilaian Munaqosah Wudhu</h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-plus"></i></button>
                        <button type="button" class="btn btn-tool" data-card-widget="remove"><i class="fas fa-times"></i></button>
                    </div>
                </div>
                <div class="card-body" style="display: none;">
                    <div class="row">
                        <!-- Tabel 1: Aspek dan Kriteria Wudhu -->
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
                                    <tr>
                                        <td>1</td>
                                        <td>Niat Wudhu</td>
                                        <td>Niat benar, jelas, dan sesuai</td>
                                        <td class="text-center"><strong>25</strong></td>
                                    </tr>
                                    <tr>
                                        <td>2</td>
                                        <td>Gerakan Wudhu</td>
                                        <td>Gerakan benar sesuai tuntunan (rukun lengkap)</td>
                                        <td class="text-center"><strong>50</strong></td>
                                    </tr>
                                    <tr>
                                        <td>3</td>
                                        <td>Doa Sesudah Wudhu</td>
                                        <td>Benar dalam pelafalan dan penghayatan</td>
                                        <td class="text-center"><strong>25</strong></td>
                                    </tr>
                                    <tr class="bg-light">
                                        <td colspan="3" class="text-right"><strong>Total Skor</strong></td>
                                        <td class="text-center"><strong>100</strong></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- Panduan Skor Wudhu (Sama dengan Sholat) -->
                        <div class="col-md-6">
                            <h5><i class="fas fa-star mr-2"></i> Kriteria Skor (Panduan Juri)</h5>
                            <div class="callout callout-success">
                                <strong>Sangat Baik (86–100)</strong><br>
                                Gerakan dan bacaan sangat tepat, tertib dan berurutan, sikap khusyuk.
                            </div>
                            <div class="callout callout-info">
                                <strong>Baik (76–85)</strong><br>
                                Gerakan dan bacaan cukup tepat, terdapat kesalahan kecil.
                            </div>
                            <div class="callout callout-warning">
                                <strong>Cukup (66–75)</strong><br>
                                Masih ada beberapa kesalahan bacaan atau gerakan.
                            </div>
                            <div class="callout callout-danger">
                                <strong>Perlu Bimbingan (<65)</strong><br>
                                Banyak kesalahan bacaan dan gerakan.
                            </div>
                        </div>
                    </div>

                    <hr>

                    <!-- Tabel 2: Detail Rubrik Wudhu -->
                    <div class="row mt-4">
                         <div class="col-md-12">
                            <h5><i class="fas fa-table mr-2"></i> Detail Rubrik Penilaian</h5>
                            <div class="table-responsive">
                                <table class="table table-bordered table-striped text-sm">
                                    <thead class="bg-navy">
                                        <tr>
                                            <th style="width: 5%">No</th>
                                            <th style="width: 25%">Aspek</th>
                                            <th style="width: 17%">Sangat Baik (86-100)</th>
                                            <th style="width: 17%">Baik (76-85)</th>
                                            <th style="width: 17%">Cukup (66-75)</th>
                                            <th style="width: 19%">Perlu Bimbingan (<65)</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>1</td>
                                            <td><strong>Niat Wudhu</strong></td>
                                            <td>Niat benar dan lancar</td>
                                            <td>Niat benar, kurang lancar</td>
                                            <td>Niat kurang tepat</td>
                                            <td>Tidak dapat melafalkan niat</td>
                                        </tr>
                                        <tr>
                                            <td>2</td>
                                            <td><strong>Gerakan Wudhu</strong></td>
                                            <td>Gerakan wudhu berurutan dan benar sesuai tuntunan</td>
                                            <td>Gerakan cukup benar</td>
                                            <td>Gerakan kurang tepat</td>
                                            <td>Gerakan banyak salah</td>
                                        </tr>
                                        <tr>
                                            <td>3</td>
                                            <td><strong>Doa Sesudah Wudhu</strong></td>
                                            <td>Doa benar bacaannya dan penuh penghayatan</td>
                                            <td>Ada sedikit kesalahan pada bacaan doa</td>
                                            <td>Banyak salah dalam pelafalan doa</td>
                                            <td>Tidak hafal doa</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                         </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

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
<?= $this->endSection(); ?>
