<?= $this->extend('backend/template/template'); ?>

<?= $this->section('content'); ?>
<div class="row">
    <div class="col-md-12">
        <div class="card card-primary card-outline">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-book-open mr-2"></i> <?= $pageTitle ?>
                </h3>
            </div>
            <div class="card-body">
                <div class="callout callout-info">
                    <h5><i class="fas fa-info-circle"></i> Info</h5>
                    <p>Silakan pilih Materi Ujian di bawah ini untuk mengatur Rubrik Penilaian (Deskripsi per Predikat).</p>
                </div>

                <div class="row">
                    <?php foreach ($listData as $grup): ?>
                    <div class="col-md-12 mt-3">
                        <h5 class="text-primary border-bottom pb-2">
                            <i class="fas fa-layer-group"></i> <?= esc($grup['nama_grup_materi']) ?>
                        </h5>
                    </div>
                    
                    <?php if (!empty($grup['materi_list'])): ?>
                        <?php foreach ($grup['materi_list'] as $materi): ?>
                        <div class="col-md-4 col-sm-6 mb-3">
                            <div class="small-box bg-light">
                                <div class="inner">
                                    <h5><?= esc($materi['nama_materi']) ?></h5>
                                    <p>Kelola Rubrik Penilaian</p>
                                </div>
                                <div class="icon">
                                    <i class="fas fa-edit"></i>
                                </div>
                                <a href="<?= base_url('backend/rubrik/manage/' . $materi['id']) ?>" class="small-box-footer text-primary">
                                    Kelola Rubrik <i class="fas fa-arrow-circle-right"></i>
                                </a>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="col-md-12">
                            <p class="text-muted font-italic">Belum ada materi dalam grup ini.</p>
                        </div>
                    <?php endif; ?>

                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection(); ?>
