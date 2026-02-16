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
                        CetakPage --> Filter["Search Filter"]
                        Filter --> Preview["Preview Data"]
                        
                        Preview -->|Cetak Admin| GenPDF["Generate PDF (Admin)"]
                        Preview -->|Cetak Semua| GenZip["Generate PDF Batch / Zip"]
                        Preview -->|Bagikan| Share["Kirim via WhatsApp / Copy Link"]
                        
                        GenPDF --> Download(["Download / Print"])
                        Share --> PublicAccess(["Wali Murid Download (No Login)"])
                </div>
                
                <div class="mt-4">
                    <h5>Penjelasan Alur:</h5>
                    <ol>
                        <li><strong>Upload Template:</strong> Siapkan desain sertifikat kosong (tanpa nama/nilai) dalam format JPG/PNG. Upload di menu <em>Pengaturan Sertifikat</em>.</li>
                        <li><strong>Mapping Layout:</strong> Tentukan posisi (koordinat X, Y) untuk setiap data yang akan dicetak (Nama, NISN, Nilai).</li>
                        <li><strong>Cetak & Bagikan:</strong> Masuk ke menu <em>Cetak Sertifikat</em>. Administrator dapat mencetak PDF (fisik) atau <strong>membagikan link download</strong> langsung ke wali murid via WhatsApp (tanpa login).</li>
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



    </div>

        <!-- Fitur Baru (v1.1) -->
        <div class="card card-outline card-success">
            <div class="card-header">
                <h3 class="card-title">Fitur Baru & Pembaruan (v1.1)</h3>
            </div>
            <div class="card-body">
                <dl class="row">
                    <dt class="col-sm-3">1. Download Publik via WhatsApp</dt>
                    <dd class="col-sm-9">
                        <p>Kini wali murid dapat mengunduh sertifikat langsung tanpa login melalui link terenkripsi yang dikirim via WhatsApp.</p>
                        <ul>
                            <li><strong>Link Aman:</strong> Menggunakan token HMAC-SHA256 (aman dari manipulasi).</li>
                            <li><strong>Format Pesan:</strong> Pesan WhatsApp otomatis disusun rapi dengan footer sekolah.</li>
                            <li><strong>Fitur WhatsApp:</strong> Tombol kirim langsung tersedia di halaman cetak sertifikat.</li>
                        </ul>
                    </dd>

                    <dt class="col-sm-3">2. Pengurutan Peserta (Ranking)</dt>
                    <dd class="col-sm-9">
                        <p>Daftar peserta di halaman Cetak Sertifikat otomatis diurutkan berdasarkan prestasi:</p>
                        <ol>
                            <li><strong>Nilai Rata-rata Tertinggi</strong> (Prioritas Utama).</li>
                            <li><strong>Nilai Tahfidz Tertinggi</strong> (Jika rata-rata sama).</li>
                            <li><strong>Nama Peserta (A-Z)</strong> (Jika kedua nilai di atas sama).</li>
                        </ol>
                    </dd>

                    <dt class="col-sm-3">3. Foto Profil & Avatar</dt>
                    <dd class="col-sm-9">
                        <ul>
                            <li><strong>Foto:</strong> Menampilkan foto siswa jika tersedia.</li>
                            <li><strong>Avatar Otomatis:</strong> Jika foto kosong, sistem membuat avatar inisial (Huruf Depan + Belakang Nama) dengan warna acak.</li>
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
