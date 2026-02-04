<?= $this->extend('backend/template/template') ?>

<?= $this->section('content') ?>
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">Dokumentasi Sistem Munaqosah</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="<?= base_url('backend/dashboard') ?>">Home</a></li>
                    <li class="breadcrumb-item active">Dokumentasi</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="card card-outline card-primary">
                    <div class="card-header">
                        <h3 class="card-title">Selamat Datang di Dokumentasi Sistem</h3>
                    </div>
                    <div class="card-body">
                        <p>Selamat datang di halaman dokumentasi Sistem Informasi Munaqosah. Halaman ini berisi panduan penggunaan sistem untuk berbagai role user (Admin, Kepala, Juri, dan Panitia).</p>
                        <p>Silakan pilih menu di sebelah kiri (Sidebar) pada bagian <strong>Dokumentasi</strong> untuk melihat detail alur kerja setiap modul.</p>
                        
                        <div class="row mt-4">
                            <div class="col-md-3 col-sm-6 col-12">
                                <div class="info-box shadow-none">
                                    <span class="info-box-icon bg-info"><i class="fas fa-users"></i></span>
                                    <div class="info-box-content">
                                        <a href="<?= base_url('backend/dokumentasi/siswa') ?>" class="text-dark">
                                            <span class="info-box-text">Modul Siswa</span>
                                            <span class="info-box-number">Data Master</span>
                                        </a>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3 col-sm-6 col-12">
                                <div class="info-box shadow-none">
                                    <span class="info-box-icon bg-success"><i class="fas fa-user-edit"></i></span>
                                    <div class="info-box-content">
                                        <a href="<?= base_url('backend/dokumentasi/peserta') ?>" class="text-dark">
                                            <span class="info-box-text">Modul Peserta</span>
                                            <span class="info-box-number">Registrasi & Undian</span>
                                        </a>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3 col-sm-6 col-12">
                                <div class="info-box shadow-none">
                                    <span class="info-box-icon bg-warning"><i class="fas fa-gavel"></i></span>
                                    <div class="info-box-content">
                                        <a href="<?= base_url('backend/dokumentasi/penilaian') ?>" class="text-dark">
                                            <span class="info-box-text">Modul Penilaian</span>
                                            <span class="info-box-number">Juri & Scoring</span>
                                        </a>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3 col-sm-6 col-12">
                                <div class="info-box shadow-none">
                                    <span class="info-box-icon bg-danger"><i class="fas fa-chart-line"></i></span>
                                    <div class="info-box-content">
                                        <a href="<?= base_url('backend/dokumentasi/monitoring') ?>" class="text-dark">
                                            <span class="info-box-text">Modul Monitoring</span>
                                            <span class="info-box-number">Real-time Stats</span>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card card-outline card-info">
                    <div class="card-header">
                        <h3 class="card-title">Alur Proses Sistem</h3>
                    </div>
                    <div class="card-body">
                        <p class="lead">Berikut adalah gambaran umum alur proses Sistem Aplikasi Munaqosah mulai dari Input, Proses, hingga Output.</p>
                        
                        <div class="mermaid" style="text-align: center;">
                            flowchart LR
                                %% Styling classes
                                classDef box fill:#ffffe0,stroke:#d3d3d3,color:#000;
                                classDef blue fill:#e6e6fa,stroke:#b0b0e0,color:#000;
                                
                                %% PENGATURAN
                                subgraph Settings [PENGATURAN]
                                    direction TB
                                    F["Setting Data Master"]:::blue
                                    F1["Setting Materi"]:::blue
                                    F2["Setting Kriteria"]:::blue
                                    F3["Setting Juri"]:::blue
                                    F4["Setting User Admin"]:::blue
                                    
                                    F --> F1
                                    F --> F2
                                    F --> F3
                                    F --> F4
                                end

                                %% INPUT
                                subgraph Input [INPUT]
                                    direction TB
                                    A["Pendaftaran Siswa"]:::blue
                                    A1["Input Manual"]:::blue
                                    A2["Import Simple Template"]:::blue
                                    A3["Import Excel Dapodik"]:::blue
                                    
                                    A --> A1
                                    A --> A2
                                    A --> A3
                                end

                                %% DETAIL REGISTRASI
                                subgraph DetailReg [DETAIL REGISTRASI]
                                    direction TB
                                    G["Registrasi Peserta"]:::blue
                                    G1["Proses Undian"]:::blue
                                    G2["Cetak Kartu Peserta"]:::blue
                                    
                                    G --> G1
                                    G --> G2
                                end

                                %% PROSES
                                subgraph Process [PROSES]
                                    direction TB
                                    B["Registrasi Peserta"]:::blue
                                    C["Proses Penilaian"]:::blue
                                    D{"Sistem Antrian"}:::blue
                                    
                                    B --> C
                                    B -.->|Opsional| D
                                    D -.-> C
                                end

                                %% OUTPUT
                                subgraph Output [OUTPUT]
                                    direction TB
                                    E["Report"]:::blue
                                    E1["Monitoring Nilai"]:::blue
                                    E2["Export Nilai"]:::blue
                                    E3["E-Certificat Plan"]:::blue
                                    
                                    E --> E1
                                    E --> E2
                                    E --> E3
                                end
                                
                                %% Connections
                                %% Arrange Input and DetailReg to feed into Process
                                Input ==> Process
                                DetailReg ==> Process
                                Settings -.-> Process
                                Process ==> Output
                                
                                %% Apply Styles
                                class Settings,Input,DetailReg,Process,Output box;
                        </div>
                    </div>
                </div>

                <!-- Developer Info Overview -->
                <div class="card card-outline card-danger">
                    <div class="card-header">
                        <h3 class="card-title">Informasi Pengembang (Developer Info) - Overview</h3>
                        <div class="card-tools">
                            <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                <i class="fas fa-minus"></i>
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <p>Gambaran umum struktur teknis aplikasi untuk pengembang:</p>
                        <dl class="row">
                            <dt class="col-sm-3">Database Tables</dt>
                            <dd class="col-sm-9">
                                <p>Tabel spesifik aplikasi menggunakan prefix <code>tbl_munaqosah_</code>:</p>
                                <ul>
                                    <li><code>tbl_munaqosah_peserta</code>: Data peserta ujian.</li>
                                    <li><code>tbl_munaqosah_grup_materi</code>: Kelompok materi (misal: Tahfidz, Tilawati).</li>
                                    <li><code>tbl_munaqosah_materi_ujian</code>: Detail materi (misal: Juz 30, Jilid 1).</li>
                                    <li><code>tbl_munaqosah_kriteria_materi_ujian</code>: Kriteria penilaian per materi.</li>
                                    <li><code>tbl_munaqosah_juri</code>: Data master juri.</li>
                                    <li><code>tbl_munaqosah_juri_kriteria</code>: Mapping juri ke kriteria penilaian.</li>
                                    <li><code>tbl_munaqosah_nilai_ujian</code>: Menyimpan nilai hasil ujian.</li>
                                    <li><code>tbl_munaqosah_antrian</code>: Data antrian peserta saat ujian.</li>
                                </ul>
                                <p class="text-muted text-sm mt-2"><em>Note: Auth tables menggunakan default MythAuth (users, auth_groups, etc).</em></p>
                            </dd>

                            <dt class="col-sm-3">Controllers</dt>
                            <dd class="col-sm-9">
                                <ul>
                                    <li>Namespace: <code>App\Controllers\Backend\Munaqosah</code></li>
                                    <li>Semua controller backend turunan dari <code>BaseController</code> dan menerapkan pengecekan login/role.</li>
                                </ul>
                            </dd>

                            <dt class="col-sm-3">Models</dt>
                            <dd class="col-sm-9">
                                <ul>
                                    <li>Namespace: <code>App\Models\Munaqosah</code></li>
                                    <li>Lokasi: <code>app/Models/Munaqosah/</code></li>
                                </ul>
                            </dd>

                            <dt class="col-sm-3">Views</dt>
                            <dd class="col-sm-9">
                                <ul>
                                    <li>Lokasi: <code>app/Views/backend/</code></li>
                                    <li>Template Utama: <code>app/Views/backend/template/template.php</code></li>
                                </ul>
                            </dd>

                            <dt class="col-sm-3">Libraries/Helpers</dt>
                            <dd class="col-sm-9">
                                <ul>
                                    <li><strong>Mermaid JS:</strong> Digunakan untuk merender flowchart dokumentasi (CDN).</li>
                                    <li><strong>AdminLTE 3:</strong> Template backend framework.</li>
                                    <li><strong>DataTables & Select2:</strong> Plugin utama untuk interaksi tabel dan form.</li>
                                </ul>
                            </dd>
                        </dl>
                        <div class="callout callout-info">
                            <strong>Tip:</strong> Lihat detail teknis spesifik (seperti nama tabel detail dan method kunci) di halaman dokumentasi masing-masing modul.
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</section>
<!-- Include Mermaid JS -->
<script type="module">
    import mermaid from 'https://cdn.jsdelivr.net/npm/mermaid@10/dist/mermaid.esm.min.mjs';
    mermaid.initialize({ startOnLoad: false });
    await mermaid.run({
        querySelector: '.mermaid',
    });
</script>
<?= $this->endSection() ?>
