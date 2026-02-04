<?= $this->extend('backend/template/template'); ?>

<?= $this->section('content'); ?>
<div class="row">
    <div class="col-md-12">
        <div class="card card-primary card-outline">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-edit mr-2"></i> <?= $pageTitle ?>
                </h3>
                <div class="card-tools">
                    <a href="<?= base_url('backend/rubrik') ?>" class="btn btn-tool" title="Kembali">
                        <i class="fas fa-arrow-left"></i> Kembali
                    </a>
                </div>
            </div>
            
            <form action="<?= base_url('backend/rubrik/save') ?>" method="post">
                <?= csrf_field() ?>
                <input type="hidden" name="id_materi" value="<?= $materi['id'] ?>">

                <div class="card-body">
                    <?php if (session()->getFlashdata('success')): ?>
                        <div class="alert alert-success alert-dismissible">
                            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                            <h5><i class="icon fas fa-check"></i> Sukses!</h5>
                            <?= session()->getFlashdata('success') ?>
                        </div>
                    <?php endif; ?>

                    <div class="alert alert-info">
                        <h5><i class="icon fas fa-info"></i> Petunjuk</h5>
                        Isi deskripsi rubrik untuk setiap kriteria dan predikat. Deskripsi ini akan ditampilkan pada Dashboard Juri sebagai panduan penilaian.
                        Kosongkan jika tidak ada deskripsi khusus.
                    </div>

                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead class="bg-navy text-center">
                                <tr>
                                    <th style="width: 50px; vertical-align: middle;">No</th>
                                    <th style="width: 15%; vertical-align: middle;">Aspek / Kriteria</th>
                                    <?php foreach ($predikats as $p): ?>
                                    <th style="vertical-align: middle;">
                                        <?= esc($p['nama_predikat']) ?><br>
                                        <small>(<?= $p['min_nilai'] ?> - <?= $p['max_nilai'] ?>)</small>
                                    </th>
                                    <?php endforeach; ?>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $no = 1; foreach ($kriteria as $k): ?>
                                <tr>
                                    <td class="text-center align-middle"><strong><?= $no++ ?></strong></td>
                                    <td class="align-middle">
                                        <strong><?= esc($k['nama_kriteria']) ?></strong><br>
                                        <small class="text-muted"><?= esc($k['deskripsi']) ?></small>
                                    </td>
                                    <?php foreach ($predikats as $p): ?>
                                    <td>
                                        <?php 
                                            $val = $rubrikMap[$k['id']][$p['id']] ?? ''; 
                                        ?>
                                        <textarea class="form-control form-control-sm" 
                                                  name="rubrik[<?= $k['id'] ?>][<?= $p['id'] ?>]" 
                                                  rows="4"
                                                  placeholder="Deskripsi..."><?= esc($val) ?></textarea>
                                    </td>
                                    <?php endforeach; ?>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="card-footer">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save mr-1"></i> Simpan Rubrik
                    </button>
                    <a href="<?= base_url('backend/rubrik') ?>" class="btn btn-secondary">Batal</a>
                </div>
            </form>
        </div>
    </div>
</div>
<?= $this->endSection(); ?>
