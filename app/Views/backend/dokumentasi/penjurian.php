<?= $this->extend('backend/template/template') ?>

<?= $this->section('content') ?>
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">Dokumentasi Sistem Penjurian</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="<?= base_url('backend/dashboard') ?>">Home</a></li>
                    <li class="breadcrumb-item"><a href="<?= base_url('backend/dokumentasi') ?>">Dokumentasi</a></li>
                    <li class="breadcrumb-item active">Sistem Penjurian</li>
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
                <h3 class="card-title">Alur Manajemen Juri</h3>
            </div>
            <div class="card-body">
                <div class="mermaid" style="text-align: center;">
                    flowchart LR
                        A[Admin] -->|Input Data Juri| B[Database Juri]
                        b1[Generate Username] -.-> B
                        B --> C[Mapping Juri vs Kriteria/Materi]
                        C --> D[Akun Juri Siap Digunakan]
                </div>
                <div class="mt-4">
                    <h5>Penjelasan Proses:</h5>
                    <ol>
                        <li><strong>Input Data Juri:</strong> Admin mendaftarkan juri baru. Sistem akan otomatis membantu generate Username unik.</li>
                        <li><strong>Mapping Juri (Opsional):</strong> Pada kasus tertentu, Juri bisa dibatasi hanya menilai materi tertentu atau kriteria tertentu. Namun secara default, sistem ini memungkinkan semua Juri menilai semua peserta (Sistem Pool).</li>
                        <li><strong>Reset Password:</strong> Jika Juri lupa password, Admin dapat melakukan reset password melalui menu Manajemen Juri.</li>
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
                    <dt class="col-sm-3">Database Tables</dt>
                    <dd class="col-sm-9">
                        <code>tbl_munaqosah_juri</code> (Menyimpan profil juri),<br>
                        <code>auth_groups_users</code> (Mapping user ID ke group 'juri'),<br>
                        <code>tbl_munaqosah_juri_kriteria</code> (Mapping spesialisas juri jika ada)
                    </dd>

                    <dt class="col-sm-3">Controller</dt>
                    <dd class="col-sm-9"><code>app/Controllers/Backend/Munaqosah/Juri.php</code></dd>

                    <dt class="col-sm-3">Model</dt>
                    <dd class="col-sm-9"><code>app/Models/Munaqosah/JuriModel.php</code></dd>

                    <dt class="col-sm-3">View Path</dt>
                    <dd class="col-sm-9"><code>app/Views/backend/munaqosah/juri/</code></dd>
                    
                    <dt class="col-sm-3">Logic Notes</dt>
                    <dd class="col-sm-9">
                        Data Juri terhubung dengan tabel <code>users</code> untuk autentikasi login. Saat membuat Juri baru, kontroler juga membuat record baru di tabel users dan assign group 'juri'.
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
