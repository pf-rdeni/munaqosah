<?= $this->extend('backend/template/template') ?>

<?= $this->section('content') ?>
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">Dokumentasi Sistem Konfigurasi</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="<?= base_url('backend/dashboard') ?>">Home</a></li>
                    <li class="breadcrumb-item"><a href="<?= base_url('backend/dokumentasi') ?>">Dokumentasi</a></li>
                    <li class="breadcrumb-item active">Sistem Konfigurasi</li>
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
                <h3 class="card-title">Alur Konfigurasi Data Master</h3>
            </div>
            <div class="card-body">
                <div class="mermaid" style="text-align: center;">
                    flowchart LR
                        A[Grup Materi] --> B[Materi Ujian]
                        B --> C[Kriteria Penilaian]
                        
                        subgraph Example [Contoh Struktur]
                            direction TB
                            G1[Grup: Tahfidz]
                            M1[Materi: Juz 30]
                            K1[Kriteria: Kelancaran]
                            K2[Kriteria: Tajwid]
                            
                            G1 -.-> M1
                            M1 -.-> K1
                            M1 -.-> K2
                        end
                </div>
                <div class="mt-4">
                    <h5>Penjelasan Hierarki:</h5>
                    <ul>
                        <li><strong>Grup Materi:</strong> Kelompok besar ujian, misal: <em>Tahfidz, Tilawah, Doa Harian</em>.</li>
                        <li><strong>Materi Ujian:</strong> Sub-materi spesifik yang diujikan, misal: <em>Juz 30, Juz 29, Surat Pilihan</em>. Setiap materi terhubung ke satu Grup Materi.</li>
                        <li><strong>Kriteria Penilaian:</strong> Aspek detail yang dinilai untuk setiap materi. Setiap materi bisa memiliki kriteria penilaian yang berbeda (misal: Tajwid, Fasohah, Makhorijul Huruf). Bobot atau rentang nilai diatur di sini.</li>
                    </ul>
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
                    <dt class="col-sm-3">Database Tables</dt>
                    <dd class="col-sm-9">
                        <code>tbl_munaqosah_grup_materi</code>,<br>
                        <code>tbl_munaqosah_materi_ujian</code>,<br>
                        <code>tbl_munaqosah_kriteria_materi_ujian</code>
                    </dd>

                    <dt class="col-sm-3">Controllers</dt>
                    <dd class="col-sm-9">
                        <code>app/Controllers/Backend/Munaqosah/GrupMateri.php</code>,<br>
                        <code>app/Controllers/Backend/Munaqosah/Materi.php</code>,<br>
                        <code>app/Controllers/Backend/Munaqosah/Kriteria.php</code>
                    </dd>

                    <dt class="col-sm-3">Models</dt>
                    <dd class="col-sm-9">
                        <code>app/Models/Munaqosah/GrupMateriModel.php</code>,<br>
                        <code>app/Models/Munaqosah/MateriModel.php</code>,<br>
                        <code>app/Models/Munaqosah/KriteriaModel.php</code>
                    </dd>

                    <dt class="col-sm-3">View Path</dt>
                    <dd class="col-sm-9">
                        <code>app/Views/backend/munaqosah/grup_materi/</code>,<br>
                        <code>app/Views/backend/munaqosah/materi/</code>,<br>
                        <code>app/Views/backend/munaqosah/kriteria/</code>
                    </dd>

                    <dt class="col-sm-3">Important Note</dt>
                    <dd class="col-sm-9">
                        Hapus data (Delete) menggunakan <em>Soft Delete</em> atau validasi ketat. Jangan menghapus Materi yang sudah memiliki data Nilai dari peserta, karena akan merusak integritas data historis.
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
