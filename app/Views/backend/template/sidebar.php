<!-- Sidebar - Menu Navigasi Kiri -->
<aside class="main-sidebar sidebar-dark-primary elevation-4">
    <!-- Brand Logo -->
    <a href="<?= base_url('/') ?>" class="brand-link">
        <i class="fas fa-graduation-cap ml-3 mr-2" style="font-size: 1.5rem;"></i>
        <span class="brand-text font-weight-light">Munaqosah</span>
    </a>

    <!-- Sidebar -->
    <div class="sidebar">
        <!-- User Panel -->
        <div class="user-panel mt-3 pb-3 mb-3 d-flex">
            <div class="image">
                <i class="fas fa-user-circle" style="font-size: 2rem; color: #6c757d;"></i>
            </div>
            <div class="info">
                <a href="<?= base_url('backend/profil') ?>" class="d-block">
                    <?= esc($user['fullname'] ?? $user['username'] ?? 'User') ?>
                </a>
                <small class="text-muted">
                    <?= esc(ucfirst($user['groups'][0] ?? 'Guest')) ?>
                </small>
            </div>
        </div>

        <!-- Sidebar Menu -->
        <nav class="mt-2">
            <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
                
                <!-- Dashboard -->
                <li class="nav-item">
                    <a href="<?= base_url('backend/dashboard') ?>" class="nav-link <?= uri_string() == 'backend/dashboard' ? 'active' : '' ?>">
                        <i class="nav-icon fas fa-tachometer-alt"></i>
                        <p>Dashboard</p>
                    </a>
                </li>
                
                <!-- Data Siswa -->
                <li class="nav-item">
                    <a href="<?= base_url('backend/siswa') ?>" class="nav-link <?= strpos(uri_string(), 'backend/siswa') !== false ? 'active' : '' ?>">
                        <i class="nav-icon fas fa-users"></i>
                        <p>Data Siswa</p>
                    </a>
                </li>
                
                <?php 
                // Menu tambahan berdasarkan role (untuk pengembangan selanjutnya)
                $userGroups = $user['groups'] ?? [];
                ?>
                
                <?php if (in_array('admin', $userGroups) || in_array('panitia', $userGroups)): ?>
                <!-- Peserta Ujian (Admin & Panitia) -->
                <li class="nav-item">
                    <a href="<?= base_url('backend/peserta') ?>" class="nav-link <?= strpos(uri_string(), 'backend/peserta') !== false ? 'active' : '' ?>">
                        <i class="nav-icon fas fa-clipboard-list"></i>
                        <p>Peserta Ujian</p>
                    </a>
                </li>
                <?php endif; ?>
                
                <?php if (in_array('admin', $userGroups) || in_array('juri', $userGroups)): ?>
                <!-- Input Nilai (Admin & Juri) -->
                <li class="nav-item">
                    <a href="<?= base_url('backend/nilai') ?>" class="nav-link <?= strpos(uri_string(), 'backend/nilai') !== false ? 'active' : '' ?>">
                        <i class="nav-icon fas fa-star"></i>
                        <p>Input Nilai</p>
                    </a>
                </li>
                <?php endif; ?>
                
                <!-- Separator -->
                <li class="nav-header">AKUN</li>
                
                <!-- Profil -->
                <li class="nav-item">
                    <a href="<?= base_url('backend/profil') ?>" class="nav-link <?= strpos(uri_string(), 'backend/profil') !== false ? 'active' : '' ?>">
                        <i class="nav-icon fas fa-user"></i>
                        <p>Profil</p>
                    </a>
                </li>
                
                <!-- Logout -->
                <li class="nav-item">
                    <a href="<?= base_url('logout') ?>" class="nav-link">
                        <i class="nav-icon fas fa-sign-out-alt text-danger"></i>
                        <p class="text-danger">Logout</p>
                    </a>
                </li>
                
            </ul>
        </nav>
    </div>
</aside>
