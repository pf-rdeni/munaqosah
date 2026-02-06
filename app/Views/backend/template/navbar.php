<!-- Navbar - Navigasi Atas -->
<nav class="main-header navbar navbar-expand navbar-white navbar-light">
    <!-- Tombol Sidebar (Left) -->
    <ul class="navbar-nav">
        <li class="nav-item">
            <a class="nav-link" data-widget="pushmenu" href="#" role="button">
                <i class="fas fa-bars"></i>
            </a>
        </li>
        <li class="nav-item d-none d-sm-inline-block">
            <a href="<?= base_url('/backend/dashboard') ?>" class="nav-link">Dashboard</a>
        </li>
    </ul>

    <!-- Navbar Kanan -->
    <ul class="navbar-nav ml-auto">
        <!-- Toggle Dark Mode -->
        <li class="nav-item">
            <a class="nav-link" href="#" id="darkModeToggle" role="button" title="Mode Gelap/Terang">
                <i class="fas fa-moon" id="darkModeIcon"></i>
            </a>
        </li>
        
        <!-- Help -->
        <li class="nav-item">
            <a class="nav-link" href="#" data-toggle="modal" data-target="#helpModal" title="Bantuan">
                <i class="fas fa-question-circle"></i>
            </a>
        </li>
        
        <!-- Academic Year Selector -->
        <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle" href="#" id="tahunAjaranDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" title="Pilih Tahun Ajaran">
                <i class="far fa-calendar-alt mr-1"></i>
                <span id="currentTahunAjaran" class="d-none d-md-inline"><?= $tahunAjaran ?? '2025/2026' ?></span>
            </a>
            <div class="dropdown-menu dropdown-menu-right" aria-labelledby="tahunAjaranDropdown">
                <h6 class="dropdown-header"><i class="fas fa-calendar-alt mr-2"></i>Tahun Ajaran</h6>
                <?php 
                $availableYears = $availableTahunAjaran ?? [
                    'previous' => '2024/2025',
                    'current' => '2025/2026',
                    'next' => '2026/2027'
                ];
                $currentYear = $tahunAjaran ?? '2025/2026';
                ?>
                <?php foreach ($availableYears as $key => $year): ?>
                    <?php $isSelected = ($year === $currentYear); ?>
                    <a class="dropdown-item tahun-ajaran-option <?= $isSelected ? 'active' : '' ?>" 
                       href="#" 
                       data-year="<?= $year ?>">
                        <?php if ($isSelected): ?>
                            <i class="fas fa-check text-success mr-2"></i>
                        <?php endif; ?>
                        <?= $year ?>
                        <?php if ($key === 'current'): ?>
                            <span class="badge badge-primary badge-sm ml-2">Saat Ini</span>
                        <?php endif; ?>
                    </a>
                <?php endforeach; ?>
            </div>
        </li>
        
        <!-- User Dropdown Menu -->
        <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                <i class="fas fa-user-circle mr-1"></i>
                <span class="d-none d-md-inline"><?= esc($user['fullname'] ?? $user['username'] ?? 'User') ?></span>
            </a>
            <div class="dropdown-menu dropdown-menu-right" aria-labelledby="userDropdown">
                <a class="dropdown-item" href="<?= base_url('backend/profil') ?>">
                    <i class="fas fa-user mr-2"></i> Profil
                </a>
                <div class="dropdown-divider"></div>
                <a class="dropdown-item text-danger" href="<?= base_url('logout') ?>">
                    <i class="fas fa-sign-out-alt mr-2"></i> Logout
                </a>
            </div>
        </li>
    </ul>
</nav>

<!-- Modal Help -->
<div class="modal fade" id="helpModal" tabindex="-1" role="dialog" aria-labelledby="helpModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="helpModalLabel">
                    <i class="fas fa-question-circle text-info mr-2"></i>
                    Bantuan
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <h6><strong>Sistem Penilaian Ujian Munaqosah</strong></h6>
                <p>SDIT An-Nahl</p>
                <hr>
                <p><strong>Fitur Utama:</strong></p>
                <ul>
                    <li><i class="fas fa-tachometer-alt text-primary mr-2"></i> Dashboard - Ringkasan statistik</li>
                    <li><i class="fas fa-users text-success mr-2"></i> Data Siswa - Kelola data siswa</li>
                    <li><i class="fas fa-clipboard-list text-warning mr-2"></i> Peserta - Kelola peserta ujian</li>
                    <li><i class="fas fa-star text-info mr-2"></i> Nilai - Input dan kelola nilai</li>
                </ul>
                <hr>
                <p class="text-muted small">
                    Versi 1.0.0 | &copy; <?= date('Y') ?> SDIT An-Nahl
                </p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>
