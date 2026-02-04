<?= $this->extend('backend/template/template') ?>

<?= $this->section('content') ?>
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">Dokumentasi Sistem Penilaian</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="<?= base_url('backend/dashboard') ?>">Home</a></li>
                    <li class="breadcrumb-item"><a href="<?= base_url('backend/dokumentasi') ?>">Dokumentasi</a></li>
                    <li class="breadcrumb-item active">Sistem Penilaian</li>
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
                <h3 class="card-title">Alur & Validasi Penilaian</h3>
            </div>
            <div class="card-body">
                <div class="mermaid" style="text-align: center;">
                    flowchart TD
                        Start([Mulai]) --> Sumber{Pilih Peserta}
                        
                        Sumber -->|Input Manual| CekJuri
                        Sumber -->|Ambil Antrian| CekJuri
                        
                        CekJuri{Status Penilaian?}
                        
                        CekJuri -->|Sudah Menilai| ModeEdit[Mode Edit / Update]
                        CekJuri -->|Belum Menilai| CekKonflik{Ada Juri Lain?}
                        
                        ModeEdit --> Blokir
                        
                        CekKonflik -->|Tidak| FormInput[Form Penilaian]
                        CekKonflik -->|Ya| CekRoom{Satu Ruangan?}
                        
                        CekRoom -->|Ya| FormInput
                        CekRoom -->|Tidak| Blokir[Terkunci / Butuh Otorisasi]
                        
                        Blokir --> InputAuth[Input Credential Admin/Kepala]
                        InputAuth --> CekAuth{Validasi?}
                        
                        CekAuth -->|Valid| FormInput
                        CekAuth -->|Invalid| Blokir
                        
                        FormInput --> Simpan[Simpan Nilai]
                        Simpan --> Selesai([Selesai])
                </div>
                <div class="mt-4">
                    <h5>Detail Validasi & Kondisi:</h5>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="callout callout-info">
                                <h6>1. Validasi Eksklusivitas Juri (Konflik)</h6>
                                <p>Sistem mencegah dua juri menilai satu peserta yang sama untuk materi yang sama, KECUALI:</p>
                                <ul>
                                    <li>Juri tersebut berada dalam <strong>Grup Juri (Ruangan) yang SAMA</strong>.</li>
                                    <li>Jika beda ruangan, sistem akan mengunci form.</li>
                                </ul>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="callout callout-warning">
                                <h6>2. Otorisasi Edit / Buka Kunci</h6>
                                <p>Jika form terkunci karena konflik, Juri harus meminta validasi:</p>
                                <ul>
                                    <li>Memasukkan Username & Password Hak Akses <strong>Admin</strong> atau <strong>Kepala</strong>.</li>
                                    <li>Jika valid, form akan terbuka sementara untuk penilaian.</li>
                                </ul>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="callout callout-success">
                                <h6>3. Logika 'Nilai Pengurangan' (Fashohah/Tajwid)</h6>
                                <p>Untuk materi tipe pengurangan (misal: Tajwid Minus):</p>
                                <ul>
                                    <li><strong>Tampilan Form:</strong> Juri menginput jumlah kesalahan (pengurang).</li>
                                    <li><strong>Database:</strong> Sistem menyimpan hasil akhir (<code>Nilai Maksimal - Pengurang</code>).</li>
                                    <li>Saat data diload kembali, sistem otomatis membalik perhitungan agar Juri tetap melihat angka pengurangnya.</li>
                                </ul>
                            </div>
                        </div>
                    </div>
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
                    <dt class="col-sm-3">Controllers</dt>
                    <dd class="col-sm-9">
                        <code>app/Controllers/Backend/Munaqosah/InputNilai.php</code> (Handler Utama)<br>
                        <code>app/Controllers/Backend/Munaqosah/MonitoringNilai.php</code> (Rekapitulasi)
                    </dd>

                    <dt class="col-sm-3">Models</dt>
                    <dd class="col-sm-9">
                        <code>app/Models/Munaqosah/NilaiUjianModel.php</code><br>
                        <code>app/Models/Munaqosah/JuriModel.php</code><br>
                        <code>app/Models/Munaqosah/JuriKriteriaModel.php</code><br>
                        <code>app/Models/Munaqosah/KriteriaModel.php</code><br>
                        <code>app/Models/Munaqosah/GrupMateriModel.php</code>
                    </dd>

                    <dt class="col-sm-3">Key Methods</dt>
                    <dd class="col-sm-9">
                        <ul>
                            <li><code>loadForm()</code>: Melakukan validasi peserta, cek konflik juri, dan generate item penilaian dynamic.</li>
                            <li><code>authorizeEdit()</code>: Endpoint AJAX untuk validasi kredensial Admin/Kepala saat bypass kunci.</li>
                            <li><code>save()</code>: Menyimpan nilai dengan logika kondisional (Normal vs Pengurangan).</li>
                        </ul>
                    </dd>

                    <dt class="col-sm-3">Database Tables</dt>
                    <dd class="col-sm-9">
                        <code>tbl_munaqosah_nilai_ujian</code>, 
                        <code>tbl_munaqosah_juri</code>, 
                        <code>tbl_munaqosah_grup_materi</code>
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
