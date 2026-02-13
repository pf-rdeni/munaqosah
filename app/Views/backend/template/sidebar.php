<!-- Sidebar - Menu Navigasi Kiri -->
<aside class="main-sidebar sidebar-dark-primary elevation-4">
    <!-- Brand Logo -->
    <a href="<?= base_url('/') ?>" class="brand-link">
        <img src="<?= base_url('assets/icon/logo_sdit_annahl.png') ?>" alt="Munaqosah Logo" class="brand-image img-circle elevation-3" style="opacity: .8">
        <span class="brand-text font-weight-light">Munaqosah</span>
    </a>

    <!-- Sidebar -->
    <div class="sidebar">
        <!-- User Panel -->
        <div class="user-panel mt-3 pb-3 mb-3 d-flex">
            <div class="image">
                <?php
                $usernameParam = $user['username'] ?? 'User';
                $fullnameParam = $user['fullname'] ?? '';
                $userImage     = $user['user_image'] ?? '';
                
                // Logic Fallback Avatar
                // 1. Ambil angka di belakang (Last Number dari Username)
                preg_match('/(\d+)$/', $usernameParam, $matches);
                $lastNum = $matches[1] ?? '';

                // 2. Ambil 2 huruf (Initials dari Nama Lengkap jika ada, jika tidak Username)
                $sourceName = !empty($fullnameParam) ? $fullnameParam : $usernameParam;
                
                // Bersihkan angka di akhir (jika source dari username)
                $textOnly = preg_replace('/[0-9]+$/', '', $sourceName);
                $textOnly = str_replace(['_', '.', '-'], ' ', $textOnly);
                $words    = preg_split('/\s+/', trim($textOnly), -1, PREG_SPLIT_NO_EMPTY);

                $initials = '';
                if (count($words) >= 2) {
                    $initials = strtoupper(substr($words[0], 0, 1) . substr($words[1], 0, 1));
                } elseif (!empty($words)) {
                    $initials = strtoupper(substr($words[0], 0, 2));
                } else {
                    $initials = 'US';
                }
                $avatarCode = $initials . $lastNum;
                ?>

                <?php if (!empty($userImage) && file_exists(FCPATH . $userImage)): ?>
                    <!-- Tampilkan Foto Profil -->
                    <img src="<?= base_url($userImage) . '?t=' . time() ?>" class="img-circle elevation-2" alt="User Image" style="width: 34px; height: 34px; object-fit: cover;">
                <?php else: ?>
                    <!-- Tampilkan Avatar Inisial -->
                    <div class="img-circle elevation-2 d-flex justify-content-center align-items-center bg-info text-white font-weight-bold" 
                         style="width: 34px; height: 34px; font-size: 0.85rem; user-select: none;">
                        <?= $avatarCode ?>
                    </div>
                <?php endif; ?>
            </div>
            <div class="info">
                <a href="<?= base_url('backend/profil') ?>" class="d-block" style="white-space: normal;">
                    <?= esc(!empty($user['fullname']) ? $user['fullname'] : $user['username']) ?>
                </a>
                <small class="text-muted" style="display: block; margin-top: 2px;">
                    <?php 
                        $role = ucfirst($user['groups'][0] ?? 'User');
                        echo esc($role . ' - ' . $user['username']); 
                    ?>
                </small>
                <?php if (!empty($user['juri_group_id'])): ?>
                    <div class="mt-1">
                        <?php
                            $guriId = $user['juri_group_id'];
                            $badges = [
                                1 => 'badge-primary',
                                2 => 'badge-secondary',
                                3 => 'badge-success',
                                4 => 'badge-danger',
                                5 => 'badge-warning',
                                6 => 'badge-info',
                                7 => 'badge-dark',
                                8 => 'bg-indigo',
                                9 => 'bg-lightblue',
                                10 => 'bg-navy',
                                11 => 'bg-olive',
                                12 => 'bg-lime',
                                13 => 'bg-fuchsia',
                                14 => 'bg-maroon',
                                15 => 'bg-purple'
                            ];
                            $badgeClass = $badges[$guriId] ?? 'badge-secondary';
                        ?>
                        <span class="badge <?= $badgeClass ?> elevation-1">
                            <i class="fas fa-users mr-1"></i> Grup <?= esc($guriId) ?>
                        </span>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Sidebar Menu -->
        <nav class="mt-2">
            <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
                
                <?php $userGroups = $user['groups'] ?? []; ?>

                <!-- Dashboard -->
                <li class="nav-item">
                    <a href="<?= base_url('backend/dashboard') ?>" class="nav-link <?= uri_string() == 'backend/dashboard' ? 'active' : '' ?>">
                        <i class="nav-icon fas fa-tachometer-alt"></i>
                        <p>Dashboard</p>
                    </a>
                </li>
                
                <?php if (in_array('admin', $userGroups) || in_array('panitia', $userGroups)): ?>
                <!-- Data Siswa (Admin & Panitia) -->
                <li class="nav-item">
                    <a href="<?= base_url('backend/siswa') ?>" class="nav-link <?= strpos(uri_string(), 'backend/siswa') !== false ? 'active' : '' ?>">
                        <i class="nav-icon fas fa-users"></i>
                        <p>Data Siswa</p>
                    </a>
                </li>
                <?php endif; ?>
                
                <?php if (in_array('admin', $userGroups) || in_array('panitia', $userGroups)): ?>
                <!-- Peserta Ujian (Admin & Panitia) -->
                <li class="nav-item">
                    <a href="<?= base_url('backend/peserta') ?>" class="nav-link <?= strpos(uri_string(), 'backend/peserta') !== false ? 'active' : '' ?>">
                        <i class="nav-icon fas fa-clipboard-list"></i>
                        <p>Peserta Ujian</p>
                    </a>
                </li>
                <?php endif; ?>
                
                <?php if (in_array('admin', $userGroups) || in_array('operator', $userGroups) || in_array('kepala', $userGroups) || in_array('panitia', $userGroups)): ?>
                <!-- Monitoring Nilai -->
                <li class="nav-item">
                    <a href="<?= base_url('backend/monitoring/nilai') ?>" class="nav-link <?= strpos(uri_string(), 'monitoring/nilai') !== false ? 'active' : '' ?>">
                        <i class="nav-icon fas fa-chart-line"></i>
                        <p>Monitoring Nilai</p>
                    </a>
                </li>
                <!-- Cetak Sertifikat -->
                <li class="nav-item">
                    <a href="<?= base_url('backend/cetak-sertifikat') ?>" class="nav-link <?= strpos(uri_string(), 'cetak-sertifikat') !== false ? 'active' : '' ?>">
                        <i class="nav-icon fas fa-print"></i>
                        <p>Cetak Sertifikat</p>
                    </a>
                </li>
                <?php endif; ?>

                <?php if (in_array('admin', $userGroups) || in_array('panitia', $userGroups)): ?>
                <li class="nav-header">ANTRIAN UJIAN</li>
                <li class="nav-item">
                    <a href="<?= base_url('backend/antrian') ?>" class="nav-link <?= strpos(uri_string(), 'backend/antrian') !== false && strpos(uri_string(), 'monitoring') === false ? 'active' : '' ?>">
                        <i class="nav-icon fas fa-bullhorn"></i>
                        <p>Antrian</p>
                    </a>
                </li>

                <?php endif; ?>

                <?php if (in_array('admin', $userGroups) || in_array('panitia', $userGroups)): ?>
                <!-- Separator Data Master -->
                <li class="nav-header">DATA REFERENSI</li>
                
                <!-- Grup Materi -->
                <li class="nav-item">
                    <a href="<?= base_url('backend/grup-materi') ?>" class="nav-link <?= strpos(uri_string(), 'backend/grup-materi') !== false ? 'active' : '' ?>">
                        <i class="nav-icon fas fa-layer-group"></i>
                        <p>Grup Materi</p>
                    </a>
                </li>

                <!-- Materi Ujian -->
                <li class="nav-item">
                    <a href="<?= base_url('backend/materi') ?>" class="nav-link <?= strpos(uri_string(), 'backend/materi') !== false ? 'active' : '' ?>">
                        <i class="nav-icon fas fa-book"></i>
                        <p>Materi Ujian</p>
                    </a>
                </li>

                <!-- Manajemen Rubrik -->
                <li class="nav-item">
                    <a href="<?= base_url('backend/rubrik') ?>" class="nav-link <?= strpos(uri_string(), 'backend/rubrik') !== false ? 'active' : '' ?>">
                        <i class="nav-icon fas fa-list-alt"></i>
                        <p>Manajemen Rubrik</p>
                    </a>
                </li>

                <!-- Kriteria Skor (Predikat) -->
                <li class="nav-item">
                    <a href="<?= base_url('backend/predikat') ?>" class="nav-link <?= strpos(uri_string(), 'backend/predikat') !== false ? 'active' : '' ?>">
                        <i class="nav-icon fas fa-star-half-alt"></i>
                        <p>Kriteria Skor</p>
                    </a>
                </li>

                <!-- Pengaturan Sertifikat -->
                <li class="nav-item">
                    <a href="<?= base_url('backend/sertifikat') ?>" class="nav-link <?= strpos(uri_string(), 'backend/sertifikat') !== false ? 'active' : '' ?>">
                        <i class="nav-icon fas fa-certificate"></i>
                        <p>Pengaturan Sertifikat</p>
                    </a>
                </li>
                <?php endif; ?>

                <?php if (in_array('admin', $userGroups) || in_array('panitia', $userGroups)): ?>
                <!-- Manajemen Juri (Admin & Panitia) -->
                <li class="nav-item">
                    <a href="<?= base_url('backend/juri') ?>" class="nav-link <?= strpos(uri_string(), 'backend/juri') !== false ? 'active' : '' ?>">
                        <i class="nav-icon fas fa-gavel"></i>
                        <p>Manajemen Juri</p>
                    </a>
                </li>
                <?php endif; ?>
                
                <?php if (in_array('juri', $userGroups)): ?>
                <!-- Input Nilai (Juri Only) -->
                <li class="nav-item">
                    <a href="<?= base_url('backend/munaqosah/input-nilai') ?>" class="nav-link <?= strpos(uri_string(), 'input-nilai') !== false ? 'active' : '' ?>">
                        <i class="nav-icon fas fa-star"></i>
                        <p>Input Nilai</p>
                    </a>
                </li>
                <?php endif; ?>

                <?php if (in_array('admin', $userGroups)): ?>
                <!-- Pengaturan User (Admin Only) -->
                <li class="nav-item">
                    <a href="<?= base_url('backend/users') ?>" class="nav-link <?= strpos(uri_string(), 'backend/users') !== false ? 'active' : '' ?>">
                        <i class="nav-icon fas fa-users-cog"></i>
                        <p>Pengaturan User</p>
                    </a>
                </li>
                <!-- Reset Nilai (Admin Only) -->
                <li class="nav-item">
                    <a href="<?= base_url('backend/setting/reset-nilai') ?>" class="nav-link <?= strpos(uri_string(), 'reset-nilai') !== false ? 'active' : '' ?>">
                        <i class="nav-icon fas fa-trash-restore"></i>
                        <p>Reset Nilai</p>
                    </a>
                </li>
                <?php endif; ?>
                
                <?php if (in_array('admin', $userGroups) || in_array('panitia', $userGroups)): ?>
                <!-- Separator Dokumentasi -->
                <li class="nav-header">DOKUMENTASI</li>
                
                <!-- Dokumentasi -->
                <li class="nav-item <?= strpos(uri_string(), 'backend/dokumentasi') !== false ? 'menu-open' : '' ?>">
                    <a href="#" class="nav-link <?= strpos(uri_string(), 'backend/dokumentasi') !== false ? 'active' : '' ?>">
                        <i class="nav-icon fas fa-book-reader"></i>
                        <p>
                            Dokumentasi Sistem
                            <i class="right fas fa-angle-left"></i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <a href="<?= base_url('backend/dokumentasi') ?>" class="nav-link <?= uri_string() == 'backend/dokumentasi' ? 'active' : '' ?>">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Overview</p>
                            </a>
                        </li>

                        <li class="nav-item">
                            <a href="<?= base_url('backend/dokumentasi/siswa') ?>" class="nav-link <?= uri_string() == 'backend/dokumentasi/siswa' ? 'active' : '' ?>">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Data Siswa</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="<?= base_url('backend/dokumentasi/peserta') ?>" class="nav-link <?= uri_string() == 'backend/dokumentasi/peserta' ? 'active' : '' ?>">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Registrasi Peserta</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="<?= base_url('backend/dokumentasi/monitoring') ?>" class="nav-link <?= uri_string() == 'backend/dokumentasi/monitoring' ? 'active' : '' ?>">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Monitoring Nilai</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="<?= base_url('backend/dokumentasi/antrian') ?>" class="nav-link <?= uri_string() == 'backend/dokumentasi/antrian' ? 'active' : '' ?>">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Sistem Antrian</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="<?= base_url('backend/dokumentasi/penilaian') ?>" class="nav-link <?= uri_string() == 'backend/dokumentasi/penilaian' ? 'active' : '' ?>">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Sistem Penilaian</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="<?= base_url('backend/dokumentasi/konfigurasi') ?>" class="nav-link <?= uri_string() == 'backend/dokumentasi/konfigurasi' ? 'active' : '' ?>">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Sistem Konfigurasi</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="<?= base_url('backend/dokumentasi/penjurian') ?>" class="nav-link <?= uri_string() == 'backend/dokumentasi/penjurian' ? 'active' : '' ?>">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Sistem Penjurian</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="<?= base_url('backend/dokumentasi/sertifikat') ?>" class="nav-link <?= uri_string() == 'backend/dokumentasi/sertifikat' ? 'active' : '' ?>">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Cetak Sertifikat</p>
                            </a>
                        </li>
                    </ul>
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
