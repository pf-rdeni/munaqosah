<?= $this->extend('backend/template/template'); ?>

<?= $this->section('content'); ?>
<!-- Halaman Daftar Siswa -->

<!-- Tombol Aksi dan Filter -->
<div class="row mb-3">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-center">
                    <h3 class="card-title">
                        <i class="fas fa-users mr-2"></i>
                        Daftar Siswa SDIT An-Nahl
                    </h3>
                    <a href="<?= base_url('backend/siswa/create') ?>" class="btn btn-primary btn-sm">
                        <i class="fas fa-plus mr-1"></i> Tambah Siswa
                    </a>
                </div>
            </div>
            <div class="card-body">
                <!-- Alert Validasi Error -->
                <?php if (session()->getFlashdata('errors')): ?>
                    <div class="alert alert-danger alert-dismissible fade show">
                        <button type="button" class="close" data-dismiss="alert">&times;</button>
                        <h5><i class="icon fas fa-ban"></i> Kesalahan Validasi</h5>
                        <ul class="mb-0">
                            <?php foreach (session()->getFlashdata('errors') as $error): ?>
                                <li><?= esc($error) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>
                
                <!-- Tabel Data Siswa -->
                <div class="table-responsive">
                    <table id="tabelSiswa" class="table table-bordered table-striped table-hover">
                        <thead class="thead-dark">
                            <tr>
                                <th width="5%">No</th>
                                <th width="12%">NISN</th>
                                <th width="25%">Nama Siswa</th>
                                <th width="8%">JK</th>
                                <th width="15%">Tanggal Lahir</th>
                                <th width="10%">Status</th>
                                <th width="15%">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($siswaList)): ?>
                                <?php $no = 1; foreach ($siswaList as $siswa): ?>
                                    <tr>
                                        <td class="text-center"><?= $no++ ?></td>
                                        <td><?= esc($siswa['nisn']) ?></td>
                                        <td><?= esc($siswa['nama_siswa']) ?></td>
                                        <td class="text-center">
                                            <?php if ($siswa['jenis_kelamin'] == 'L'): ?>
                                                <span class="badge badge-info">Laki-laki</span>
                                            <?php else: ?>
                                                <span class="badge badge-pink" style="background-color: #e83e8c; color: white;">Perempuan</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if (!empty($siswa['tanggal_lahir'])): ?>
                                                <?= date('d-m-Y', strtotime($siswa['tanggal_lahir'])) ?>
                                            <?php else: ?>
                                                <span class="text-muted">-</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-center">
                                            <?php
                                            $statusClass = [
                                                'aktif'    => 'success',
                                                'nonaktif' => 'secondary',
                                                'lulus'    => 'primary',
                                                'pindah'   => 'warning',
                                            ];
                                            $status = $siswa['status'] ?? 'aktif';
                                            ?>
                                            <span class="badge badge-<?= $statusClass[$status] ?? 'secondary' ?>">
                                                <?= ucfirst($status) ?>
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            <div class="btn-group" role="group">
                                                <a href="<?= base_url('backend/siswa/edit/' . $siswa['id']) ?>" 
                                                   class="btn btn-warning btn-sm" 
                                                   title="Edit">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <a href="<?= base_url('backend/siswa/delete/' . $siswa['id']) ?>" 
                                                   class="btn btn-danger btn-sm btn-delete" 
                                                   data-name="<?= esc($siswa['nama_siswa']) ?>"
                                                   title="Hapus">
                                                    <i class="fas fa-trash"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="7" class="text-center text-muted">
                                        <i class="fas fa-inbox fa-3x mb-3 d-block"></i>
                                        Belum ada data siswa. 
                                        <a href="<?= base_url('backend/siswa/create') ?>">Tambah siswa baru</a>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection(); ?>

<?= $this->section('scripts'); ?>
<script>
$(document).ready(function() {
    // Inisialisasi DataTables
    $('#tabelSiswa').DataTable({
        responsive: true,
        autoWidth: false,
        columnDefs: [
            { orderable: false, targets: [6] } // Kolom aksi tidak bisa diurutkan
        ]
    });
});
</script>
<?= $this->endSection(); ?>
