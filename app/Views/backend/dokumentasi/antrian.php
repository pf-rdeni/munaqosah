<?= $this->extend('backend/template/template') ?>

<?= $this->section('content') ?>
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">Dokumentasi Sistem Antrian</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="<?= base_url('backend/dashboard') ?>">Home</a></li>
                    <li class="breadcrumb-item"><a href="<?= base_url('backend/dokumentasi') ?>">Dokumentasi</a></li>
                    <li class="breadcrumb-item active">Sistem Antrian</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
        <!-- User Guide -->
        <div class="card card-outline card-primary">
            <div class="card-header">
                <h3 class="card-title">Alur Pengguna (User Flow)</h3>
            </div>
            <div class="card-body">
                <div class="mermaid" style="text-align: center;">
                    flowchart LR
                        Input[Input Peserta] --> Cek{Validasi?}
                        Cek -->|Gagal| Tolak[Tolak Input]
                        Cek -->|Sukses| List[Masuk List Antrian]
                        
                        List --> Aksi{Aksi Admin}
                        
                        Aksi -->|Tombol Panggil| Call[Status: Dipanggil]
                        Aksi -->|Tombol Mulai| Run[Status: Sedang Ujian]
                        Aksi -->|Tombol Reset| Wait[Status: Menunggu]
                        Aksi -->|Tombol Selesai| Done[Status: Selesai]
                        
                        Call --> Monitor[Tampil di TV]
                        Run --> Room[Masuk Ruangan]
                        Done --> Finish([Selesai / History])
                </div>
                <div class="mt-4">
                    <h5>Penjelasan Proses & Aksi:</h5>
                    <ol>
                        <li><strong>Input & Validasi:</strong> Sistem menolak peserta yang sedang aktif di antrian lain. Jika lolos, peserta masuk ke daftar "Menunggu".</li>
                        <li><strong>Aksi Admin/Operator:</strong> Pada daftar antrian, tersedia tombol aksi cepat:
                            <ul>
                                <li><strong>Panggil (Call):</strong> Mengubah status ke "Dipanggil" dan membunyikan notifikasi di layar monitoring.</li>
                                <li><strong>Mulai (Start):</strong> Menandakan peserta sudah masuk ruangan dan mulai ujian (Status: "Sedang Ujian").</li>
                                <li><strong>Reset:</strong> Mengembalikan status ke "Menunggu" jika terjadi kesalahan panggil.</li>
                                <li><strong>Selesai:</strong> Mengakhiri sesi antrian peserta tersebut.</li>
                            </ul>
                        </li>
                    </ol>
                </div>
            </div>
        </div>

        <!-- Developer Info -->
        <div class="card card-outline card-danger">
            <div class="card-header">
                <h3 class="card-title">Informasi Pengembang (Developer Info)</h3>
            </div>
            <div class="card-body">
                <dl class="row">
                    <dt class="col-sm-3">Database Table</dt>
                    <dd class="col-sm-9"><code>tbl_munaqosah_antrian</code> (Menyimpan data antrian aktif hari ini)</dd>

                    <dt class="col-sm-3">Controller</dt>
                    <dd class="col-sm-9"><code>app/Controllers/Backend/Antrian.php</code></dd>

                    <dt class="col-sm-3">Models</dt>
                    <dd class="col-sm-9">
                        <code>app/Models/Munaqosah/AntrianModel.php</code><br>
                        <code>app/Models/Munaqosah/PesertaModel.php</code><br>
                        <code>app/Models/Munaqosah/GrupMateriModel.php</code>
                    </dd>

                    <dt class="col-sm-3">View Path</dt>
                    <dd class="col-sm-9"><code>app/Views/backend/antrian/</code></dd>
                    
                    <dt class="col-sm-3">Key Functions</dt>
                    <dd class="col-sm-9">
                        <ul>
                            <li><code>index()</code>: Halaman utama manajemen antrian admin.</li>
                            <li><code>monitoring()</code>: Halaman publik untuk layar TV monitoring.</li>
                            <li><code>getQueueData()</code>: API JSON untuk mengambil data antrian real-time (polling AJAX).</li>
                            <li><code>register()</code>: Menambahkan siswa ke tabel antrian.</li>
                            <li><code>updateStatus()</code>: Mengubah status antrian (waiting, called, done, skipped).</li>
                        </ul>
                    </dd>
                </dl>
            </div>
        </div>
    </div>
</section>

<!-- Include Mermaid JS -->
<script type="module">
    import mermaid from 'https://cdn.jsdelivr.net/npm/mermaid@10/dist/mermaid.esm.min.mjs';
    mermaid.initialize({ startOnLoad: true });
</script>

<?= $this->endSection() ?>
