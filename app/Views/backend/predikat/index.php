<?= $this->extend('backend/template/template'); ?>

<?= $this->section('content'); ?>
<div class="row">
    <div class="col-md-12">
        <div class="card card-primary card-outline">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-list-ol mr-2"></i> <?= $pageTitle ?>
                </h3>
                <div class="card-tools">
                    <a href="<?= base_url('backend/predikat/create') ?>" class="btn btn-primary btn-sm">
                        <i class="fas fa-plus"></i> Tambah Kriteria
                    </a>
                </div>
            </div>
            <div class="card-body">
                <?php if (session()->getFlashdata('success')): ?>
                    <div class="alert alert-success alert-dismissible">
                         <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                        <h5><i class="icon fas fa-check"></i> Sukses!</h5>
                        <?= session()->getFlashdata('success') ?>
                    </div>
                <?php endif; ?>

                <div class="table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th style="width: 10px">No</th>
                                <th>Peruntukan</th>
                                <th>Nama Predikat</th>
                                <th>Huruf</th>
                                <th>Range Nilai</th>
                                <th>Deskripsi Global</th>
                                <th>Warna (CSS)</th>
                                <th>Urutan</th>
                                <th style="width: 120px">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $no = 1; foreach ($listData as $row): ?>
                            <tr>
                                <td><?= $no++ ?></td>
                                <td>
                                    <?php if (empty($row['nama_grup_materi'])): ?>
                                        <span class="badge badge-primary">GLOBAL / UMUM</span>
                                    <?php else: ?>
                                        <span class="badge badge-info"><?= esc($row['nama_grup_materi']) ?></span>
                                    <?php endif; ?>
                                </td>
                                <td><?= esc($row['nama_predikat']) ?></td>
                                <td class="text-center font-weight-bold"><?= esc($row['predikat_huruf']) ?></td>
                                <td>
                                    <span class="badge badge-<?= esc($row['class_css']) ?>"><?= $row['min_nilai'] ?> - <?= $row['max_nilai'] ?></span>
                                </td>
                                <td><?= esc($row['deskripsi_global']) ?></td>
                                <td><code><?= esc($row['class_css']) ?></code></td>
                                <td><?= esc($row['urutan']) ?></td>
                                <td>
                                    <?php if (empty($row['id_grup_materi'])): ?>
                                        <a href="<?= base_url('backend/predikat/copy/' . $row['id']) ?>" class="btn btn-info btn-xs" title="Copy / Duplicate">
                                            <i class="fas fa-copy"></i>
                                        </a>
                                    <?php endif; ?>
                                    <a href="<?= base_url('backend/predikat/edit/' . $row['id']) ?>" class="btn btn-warning btn-xs">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="<?= base_url('backend/predikat/delete/' . $row['id']) ?>" class="btn btn-danger btn-xs" onclick="return confirm('Yakin hapus data ini?')">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection(); ?>
