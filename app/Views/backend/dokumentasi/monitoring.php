<?= $this->extend('backend/template/template') ?>

<?= $this->section('content') ?>
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">Dokumentasi Monitoring Nilai</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="<?= base_url('backend/dashboard') ?>">Home</a></li>
                    <li class="breadcrumb-item"><a href="<?= base_url('backend/dokumentasi') ?>">Dokumentasi</a></li>
                    <li class="breadcrumb-item active">Monitoring Nilai</li>
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
                <h3 class="card-title">Alur Monitoring & Rekap Nilai</h3>
            </div>
            <div class="card-body">
                <div class="mermaid" style="text-align: center;">
                    flowchart TD
                        A[Nilai Masuk dari Juri] --> B{Agregasi Nilai}
                        B --> C[Hitung Rata-Rata per Kriteria]
                        C --> D[Hitung Subtotal per Materi]
                        D --> E[Hitung Grand Total & Rata-Rata Akhir]
                        E --> F{Cek Kelulusan}
                        F -->|>= 65| G[LULUS]
                        F -->|< 65| H[TDK LULUS]
                        F -->|Nilai Belum Lengkap| I[PROGRES]
                        
                        G --> J[Tampil di Dashboard]
                        H --> J
                        I --> J
                        J --> K[Export Excel / Cetak Rapor]
                </div>
                <div class="mt-4">
                    <h5>Logika Perhitungan:</h5>
                    <ul>
                        <li><strong>Nilai Per Kriteria:</strong> Jika dinilai oleh lebih dari 1 Juri, sistem mengambil nilai <strong>Rata-Rata</strong> dari juri-juri tersebut.</li>
                        <li><strong>Subtotal Materi:</strong>
                            <ul>
                                <li><em>Mode Normal:</em> (Rata-Rata Kriteria × Bobot %).</li>
                                <li><em>Mode Pengurangan:</em> Rata-Rata Kriteria (Tanpa Bobot). Biasanya digunakan untuk materi tajwid/fashohah minus.</li>
                            </ul>
                        </li>
                        <li><strong>Status Progres:</strong> Jika ada satu saja kriteria wajib yang belum dinilai (kosong), maka status peserta dianggap "PROGRES" (Belum selesai).</li>
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
                        <code>tbl_munaqosah_nilai_ujian</code> (Data mentah nilai per juri),<br>
                        <code>tbl_munaqosah_materi_ujian</code> (Konfigurasi Bobot & Jenis Hitungan),<br>
                        <code>tbl_munaqosah_kriteria_ujian</code> (Detail Kriteria)
                    </dd>

                    <dt class="col-sm-3">Controller</dt>
                    <dd class="col-sm-9"><code>app/Controllers/Backend/Munaqosah/MonitoringNilai.php</code></dd>

                    <dt class="col-sm-3">Logic Highlights</dt>
                    <dd class="col-sm-9">
                        <ul>
                            <li><strong>Dynamic Columns:</strong> Kolom tabel monitoring bersifat dinamis mengikuti jumlah Materi dan Kriteria yang aktif di database.</li>
                            <li><strong>Multi-Juri Support:</strong> Sistem mampu mendeteksi jika satu peserta dinilai oleh banyak juri di kriteria yang sama, dan otomatis merender kolom tambahan jika diperlukan (walaupun logika default adalah mengambil rata-ratanya).</li>
                            <li><strong>Export:</strong> Fitur export biasanya menggunakan library <code>PhpSpreadsheet</code> untuk menghasilkan laporan dalam format .xlsx.</li>
                        </ul>
                    </dd>

                    <dt class="col-sm-3">View Path</dt>
                    <dd class="col-sm-9"><code>app/Views/backend/monitoring/nilai/</code></dd>
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
