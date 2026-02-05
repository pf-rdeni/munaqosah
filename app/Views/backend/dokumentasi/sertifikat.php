<?= $this->extend('backend/template/template') ?>

<?= $this->section('content') ?>
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">Dokumentasi Sertifikat</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="<?= base_url('backend/dashboard') ?>">Home</a></li>
                    <li class="breadcrumb-item"><a href="<?= base_url('backend/dokumentasi') ?>">Dokumentasi</a></li>
                    <li class="breadcrumb-item active">Sertifikat</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
        <!-- Workflow -->
        <div class="card card-outline card-primary">
            <div class="card-header">
                <h3 class="card-title">Alur Pembuatan & Cetak Sertifikat</h3>
            </div>
            <div class="card-body">
                <div class="mermaid" style="text-align: center;">
                    flowchart TD
                        Start([Mulai]) --> Upload[1. Upload Template Gambar]
                        Upload --> Layout[2. Atur Tata Letak / Mapping]
                        
                        Layout --> DataBind{Mapping Data}
                        DataBind -->|Nama, NISN, SK| TextFields[Posisi X, Y, Font]
                        DataBind -->|Nilai & Predikat| TableBlock[Block Table / Nilai]
                        
                        TextFields --> SaveConfig[Simpan Konfigurasi]
                        TableBlock --> SaveConfig
                        
                        SaveConfig --> CetakPage[3. Halaman Cetak Sertifikat]
                        CetakPage --> Filter[Filter Peserta / Kelas]
                        Filter --> Preview[Preview Data]
                        
                        Preview -->|Cetak Satu| GenPDF[Generate PDF]
                        Preview -->|Cetak Semua| GenZip[Generate PDF Batch / Zip]
                        
                        GenPDF --> Download([Download / Print])
                </div>
                
                <div class="mt-4">
                    <h5>Penjelasan Alur:</h5>
                    <ol>
                        <li><strong>Upload Template:</strong> Siapkan desain sertifikat kosong (tanpa nama/nilai) dalam format JPG/PNG. Upload di menu <em>Pengaturan Sertifikat</em>.</li>
                        <li><strong>Mapping Layout:</strong> Tentukan posisi (koordinat X, Y) untuk setiap data yang akan dicetak (Nama, NISN, Nilai).</li>
                        <li><strong>Cetak:</strong> Masuk ke menu <em>Cetak Sertifikat</em>, pilih peserta, dan sistem akan menempelkan data ke atas template gambar menjadi PDF.</li>
                    </ol>
                </div>
            </div>
        </div>
        
        <!-- Detail Pengaturan -->
        <div class="card card-outline card-info">
            <div class="card-header">
                <h3 class="card-title">Panduan Konfigurasi (Mapping)</h3>
            </div>
            <div class="card-body">
                <dl class="row">
                    <dt class="col-sm-3">Koordinat (X, Y)</dt>
                    <dd class="col-sm-9">
                        Sistem menggunakan satuan <strong>Pixel (px)</strong>. Titik <code>0,0</code> berada di <strong>Pojok Kiri Atas</strong> gambar template.
                        <ul>
                            <li><strong>X:</strong> Jarak dari kiri ke kanan.</li>
                            <li><strong>Y:</strong> Jarak dari atas ke bawah.</li>
                        </ul>
                    </dd>

                    <dt class="col-sm-3">Field Data</dt>
                    <dd class="col-sm-9">
                        Field yang tersedia untuk diletakkan di sertifikat:
                        <ul>
                            <li><code>nama_siswa</code>: Nama lengkap peserta (Jelas).</li>
                            <li><code>nisn</code> / <code>no_peserta</code>: Identitas peserta.</li>
                            <li><code>tempat_lahir</code>, <code>tgl_lahir</code>: Data kelahiran.</li>
                            <li><code>nomor_surat</code>, <code>tgl_terbit</code>: Atribut sertifikat.</li>
                            <li><code>qr_code</code>: QR Code validasi (otomatis digenerate).</li>
                            <li><code>foto_peserta</code>: Foto profil peserta.</li>
                        </ul>
                    </dd>
                    
                    <dt class="col-sm-3">Block Table (Nilai)</dt>
                    <dd class="col-sm-9">
                        Fitur khusus untuk menampikan tabel nilai secara otomatis.
                        <br>Cukup tentukan posisi awal (X, Y), sistem akan membuat tabel berisi: <em>Materi, Nilai Angka, Nilai Huruf, Terbilang</em>.
                    </dd>
                </dl>
                
                <div class="callout callout-warning">
                    <h5><i class="fas fa-lightbulb mr-1"></i> Tips Mapping</h5>
                    <p>Gunakan fitur <strong>Drag & Drop</strong> pada halaman Pengaturan Sertifikat untuk menentukan posisi teks dengan mudah tanpa harus menebak koordinat angkanya.</p>
                </div>
            </div>
        </div>

        <!-- Technical Info -->
        <div class="card card-outline card-danger">
            <div class="card-header">
                <h3 class="card-title">Informasi Teknis (Untuk Developer)</h3>
            </div>
            <div class="card-body">
                <dl class="row">
                    <dt class="col-sm-3">Library PDF</dt>
                    <dd class="col-sm-9">
                        Menggunakan <strong>Dompdf</strong>. Pastikan ekstensi PHP <code>dom</code>, <code>xml</code>, dan <code>mbstring</code> aktif di server.
                    </dd>

                    <dt class="col-sm-3">Helper Class</dt>
                    <dd class="col-sm-9">
                        <code>app/Helpers/CertificateGenerator.php</code>
                        <br>
                        Class ini menangani logika penggabungan gambar template dengan teks menggunakan HTML/CSS absolute positioning sebelum dirender menjadi PDF.
                    </dd>
                    
                    <dt class="col-sm-3">Penanganan Font</dt>
                    <dd class="col-sm-9">
                        Dompdf mendukung font standar (Helvetica, Times, Courier). Untuk font kustom, perlu load font file (<code>.ttf</code>) secara manual di konfigurasi Dompdf.
                    </dd>
                    
                    <dt class="col-sm-3">Troubleshooting</dt>
                    <dd class="col-sm-9">
                        Jika terjadi error <em>"Class Dompdf\Options not found"</em>:
                        <ul>
                            <li>Pastikan folder <code>vendor</code> diupload lengkap ke server.</li>
                            <li>Cek file <code>vendor/autoload.php</code> dan <code>vendor/composer/autoload_psr4.php</code> update.</li>
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
