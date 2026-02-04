<?= $this->extend('backend/template/template') ?>

<?= $this->section('content') ?>
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">Dokumentasi Data Siswa</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="<?= base_url('backend/dashboard') ?>">Home</a></li>
                    <li class="breadcrumb-item"><a href="<?= base_url('backend/dokumentasi') ?>">Dokumentasi</a></li>
                    <li class="breadcrumb-item active">Data Siswa</li>
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
                <h3 class="card-title">Alur Manajemen Data Siswa</h3>
            </div>
            <div class="card-body">
                <div class="mermaid" style="text-align: center;">
                    flowchart LR
                        A[Start Input] --> B{Pilih Metode}
                        B -->|Manual| C[Form Input Siswa]
                        B -->|Import Excel| D[Upload File Excel]
                        
                        C --> E[Data Tersimpan]
                        D --> D1{Cek Format}
                        D1 -->|Template Sederhana| D2[Preview Data]
                        D1 -->|Format Dapodik| D3[Preview Data]
                        D2 --> F[Pilih Data Valid]
                        D3 --> F
                        F --> E
                        
                        E --> G[Update Data Hafalan]
                        G --> H[Siap Registrasi Ujian]
                </div>
                <div class="mt-4">
                    <h5>Penjelasan Proses:</h5>
                    <ol>
                        <li><strong>Input Manual:</strong> Digunakan untuk input satuan siswa baru atau edit data siswa yang sudah ada.</li>
                        <li><strong>Import Excel:</strong>
                            <ul>
                                <li><strong>Template Sederhana:</strong> Gunakan template yang disediakan sistem untuk import massal yang cepat.</li>
                                <li><strong>Format Dapodik:</strong> Support file unduhan dari Dapodik tanpa perlu ubah format (mapping otomatis kolom NISN, Nama, dll).</li>
                            </ul>
                        </li>
                        <li><strong>Update Hafalan:</strong> Setelah siswa masuk, Guru/Admin mengupdate data hafalan (Juz dan Surah) sebagai syarat penentuan materi ujian nantinya.</li>
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
                        <code>tbl_munaqosah_siswa</code><br>
                        <small>Column <code>hafalan</code> menyimpan JSON struktur hafalan siswa.</small>
                    </dd>

                    <dt class="col-sm-3">Controller</dt>
                    <dd class="col-sm-9"><code>app/Controllers/Backend/Munaqosah/Siswa.php</code></dd>

                    <dt class="col-sm-3">Model</dt>
                    <dd class="col-sm-9"><code>app/Models/Munaqosah/SiswaModel.php</code></dd>

                    <dt class="col-sm-3">Key Functions</dt>
                    <dd class="col-sm-9">
                        <ul>
                            <li><code>import()</code>: Menghandle upload dan parsing Excel (PhpSpreadsheet). Mendeteksi otomatis format template vs Dapodik berdasarkan struktur kolom.</li>
                            <li><code>saveImport()</code>: Menyimpan row yang dicentang user pada halaman Preview.</li>
                            <li><code>updateHafalan()</code>: Menyimpan data hafalan siswa dalam format JSON. Relasi surah menggunakan lookup ke <code>tbl_alquran</code>.</li>
                        </ul>
                    </dd>

                    <dt class="col-sm-3">View Path</dt>
                    <dd class="col-sm-9"><code>app/Views/backend/siswa/</code></dd>
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
