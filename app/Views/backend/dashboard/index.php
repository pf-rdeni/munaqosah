<?= $this->extend('backend/template/template'); ?>

<?= $this->section('content'); ?>
<!-- Halaman Dashboard - Statistik Ringkasan -->

<!-- Statistik Cards -->
<div class="row">
    <!-- Total Siswa Aktif -->
    <div class="col-lg-3 col-6">
        <div class="small-box bg-info">
            <div class="inner">
                <h3><?= number_format($statistik['totalSiswa'] ?? 0) ?></h3>
                <p>Siswa Aktif</p>
            </div>
            <div class="icon">
                <i class="fas fa-users"></i>
            </div>
            <a href="<?= base_url('backend/siswa') ?>" class="small-box-footer">
                Lihat Detail <i class="fas fa-arrow-circle-right"></i>
            </a>
        </div>
    </div>
    
    <!-- Total Peserta Ujian -->
    <div class="col-lg-3 col-6">
        <div class="small-box bg-success">
            <div class="inner">
                <h3><?= number_format($statistik['totalPeserta'] ?? 0) ?></h3>
                <p>Peserta Ujian</p>
            </div>
            <div class="icon">
                <i class="fas fa-clipboard-list"></i>
            </div>
            <a href="<?= base_url('backend/peserta') ?>" class="small-box-footer">
                Lihat Detail <i class="fas fa-arrow-circle-right"></i>
            </a>
        </div>
    </div>
    
    <!-- Peserta Sudah Dinilai -->
    <div class="col-lg-3 col-6">
        <div class="small-box bg-warning">
            <div class="inner">
                <h3><?= number_format($statistik['pesertaDinilai'] ?? 0) ?></h3>
                <p>Sudah Dinilai</p>
            </div>
            <div class="icon">
                <i class="fas fa-check-circle"></i>
            </div>
            <a href="<?= base_url('backend/nilai') ?>" class="small-box-footer">
                Lihat Detail <i class="fas fa-arrow-circle-right"></i>
            </a>
        </div>
    </div>
    
    <!-- Tahun Ajaran -->
    <div class="col-lg-3 col-6">
        <div class="small-box bg-primary">
            <div class="inner">
                <h3 style="font-size: 1.8rem;"><?= esc($statistik['tahunAjaran'] ?? '-') ?></h3>
                <p>Tahun Ajaran</p>
            </div>
            <div class="icon">
                <i class="fas fa-calendar-alt"></i>
            </div>
            <a href="#" class="small-box-footer">
                Aktif <i class="fas fa-check"></i>
            </a>
        </div>
    </div>
</div>

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
                    <div class="col-md-4">
                        <div class="info-box bg-light">
                            <span class="info-box-icon bg-info"><i class="fas fa-users"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Data Siswa</span>
                                <span class="info-box-number">Kelola data siswa SDIT An-Nahl</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="info-box bg-light">
                            <span class="info-box-icon bg-success"><i class="fas fa-clipboard-list"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Peserta Ujian</span>
                                <span class="info-box-number">Registrasi peserta ujian</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="info-box bg-light">
                            <span class="info-box-icon bg-warning"><i class="fas fa-star"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Input Nilai</span>
                                <span class="info-box-number">Penilaian oleh juri</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection(); ?>
