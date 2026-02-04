<?= $this->extend('backend/template/template') ?>

<?= $this->section('content') ?>
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">Dokumentasi Registrasi Peserta</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="<?= base_url('backend/dashboard') ?>">Home</a></li>
                    <li class="breadcrumb-item"><a href="<?= base_url('backend/dokumentasi') ?>">Dokumentasi</a></li>
                    <li class="breadcrumb-item active">Registrasi Peserta</li>
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
                <h3 class="card-title">Alur Registrasi Ujian (Undian)</h3>
            </div>
            <div class="card-body">
                <div class="mermaid" style="text-align: center;">
                    flowchart TD
                        Start([Mulai]) --> CekSiswa{"Siswa Aktif & Punya Hafalan?"}
                        CekSiswa -->|Tidak| Stop([Tidak Bisa Ikut])
                        CekSiswa -->|Ya| Undian[Proses Undian Otomatis]
                        
                        subgraph LogicUndian [System Logic]
                            direction TB
                            L1[Filter Belum Terdaftar] --> L2[Generate No Peserta]
                            L2 --> L3[Acak Surah Sholat]
                            L3 --> L4[Acak Surah Tahfidz Wajib]
                        end
                        
                        Undian -.-> L1
                        L4 --> Table[Tabel Peserta Terdaftar]
                        
                        Table --> Action{Aksi}
                        Action -->|Cetak Semua| PrintAll["Halaman Cetak (Semua)"]
                        Action -->|Cetak Per Siswa| PrintOne["Halaman Cetak (Individual)"]
                        
                        PrintAll --> WinPrint[Window Print Browser]
                        PrintOne --> WinPrint
                        WinPrint --> Output[Kartu Peserta Fisik]
                </div>
                <div class="mt-4">
                    <h5>Penjelasan Proses:</h5>
                    <ol>
                        <li><strong>Undian Otomatis:</strong> Sistem akan mengambil siswa aktif yang belum terdaftar, memberikan nomor urut lanjutan, dan mengacak soal (Surah Sholat & Tahfidz) secara otomatis.</li>
                        <li><strong>Cetak Kartu:</strong>
                            <ul>
                                <li><strong>Cetak Semua:</strong> Tombol ini akan membuka halaman print untuk SELURUH peserta periode ini. Cocok untuk pencetakan massal oleh panitia.</li>
                                <li><strong>Cetak Per Siswa:</strong> Ikon printer pada baris siswa digunakan untuk mencetak kartu spesifik satu siswa saja (misal jika kartu hilang atau rusak).</li>
                            </ul>
                        </li>
                        <li><strong>Tampilan Cetak:</strong> Sistem menggunakan tampilan khusus <em>(Print Friendly)</em> yang menghapus navigasi/sidebar dan langsung memicu dialog print browser (`window.print()`).</li>
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
                    <dd class="col-sm-9">
                        <code>tbl_munaqosah_peserta</code><br>
                        <small>Data peserta bersifat per Tahun Ajaran. Column <code>surah</code> menyimpan JSON soal ujian hasil undian.</small>
                    </dd>

                    <dt class="col-sm-3">Controller</dt>
                    <dd class="col-sm-9"><code>app/Controllers/Backend/Munaqosah/Peserta.php</code></dd>

                    <dt class="col-sm-3">Model</dt>
                    <dd class="col-sm-9"><code>app/Models/Munaqosah/PesertaModel.php</code></dd>

                    <dt class="col-sm-3">Key Functions</dt>
                    <dd class="col-sm-9">
                        <ul>
                            <li><code>undian()</code>: Fungsi inti dengan logic pengacakan kompleks. Mengambil siswa eligible -> Filter yang registered -> Shuffle array siswa & array nomor -> Assign random surah -> Insert Batch transaction.</li>
                            <li><code>saveSettings()</code>: Mengatur range surah sholat (Start - End) per tahun ajaran.</li>
                            <li><code>printKartu()</code>: Menghasilkan tampilan cetak (Print Friendly) untuk kartu ujian peserta.</li>
                        </ul>
                    </dd>

                    <dt class="col-sm-3">View Path</dt>
                    <dd class="col-sm-9"><code>app/Views/backend/peserta/</code></dd>
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
