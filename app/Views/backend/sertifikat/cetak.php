<?= $this->extend('backend/template/template'); ?>
<?= $this->section('content'); ?>
<section class="content">
    <div class="container-fluid">
        <div class="card card-primary card-outline">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h3 class="card-title"><i class="fas fa-print"></i> Cetak Sertifikat</h3>
                <div class="card-tools">
                    <a href="<?= base_url('backend/cetak-sertifikat/print-batch') ?>" class="btn btn-success btn-sm" target="_blank">
                        <i class="fas fa-file-archive"></i> Cetak Semua (ZIP)
                    </a>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table id="tableSertifikat" class="table table-bordered table-striped table-hover table-sm">
                        <thead class="thead-light text-center">
                            <tr>
                                <th width="5%" rowspan="2" class="align-middle">No</th>
                                <th width="5%" rowspan="2" class="align-middle">Aksi</th>
                                <th rowspan="2" class="align-middle">Nama Peserta</th>
                                <th rowspan="2" class="align-middle">No Peserta</th>
                                
                                <!-- Materi Columns -->
                                <?php foreach ($materiList as $m): ?>
                                    <th class="align-middle"><?= $m['nama_materi'] ?></th>
                                <?php endforeach; ?>
                                
                                <th rowspan="2" class="align-middle">Total</th>
                                <th rowspan="2" class="align-middle">Rata-Rata</th>
                                <th rowspan="2" class="align-middle">Nilai Huruf</th>
                                <th rowspan="2" class="align-middle">Predikat</th>
                                <th rowspan="2" class="align-middle">Status</th>
                                <th rowspan="2" class="align-middle">Peringkat</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $no = 1; foreach ($pesertaList as $p): 
                                $np = $p['no_peserta'];
                                $data = $finalData[$np];
                                $rank = $rankMap[$np];
                            ?>
                            <tr>
                                <td class="text-center"><?= $no++ ?></td>
                                <td class="text-center">
                                    <?php if ($data['is_complete'] && $data['status'] == 'LULUS'): ?>
                                    <a href="<?= base_url('backend/cetak-sertifikat/print/' . $p['id']) ?>" 
                                       target="_blank" 
                                       class="btn btn-sm btn-info"
                                       title="Cetak Sertifikat">
                                        <i class="fas fa-print"></i>
                                    </a>
                                    <?php else: ?>
                                    <button class="btn btn-sm btn-secondary" disabled title="Belum Lengkap / Tidak Lulus"><i class="fas fa-print"></i></button>
                                    <?php endif; ?>
                                </td>
                                <td><?= esc($p['nama_siswa']) ?></td>
                                <td class="text-center"><?= esc($p['no_peserta']) ?></td>
                                
                                <?php foreach ($materiList as $m): ?>
                                    <td class="text-center">
                                        <?= number_format($data[$m['id']] ?? 0, 1) ?>
                                    </td>
                                <?php endforeach; ?>

                                <td class="text-center font-weight-bold"><?= number_format($data['grand_total'], 1) ?></td>
                                <td class="text-center font-weight-bold <?= ($data['rata_rata'] >= 65) ? 'text-success' : 'text-danger' ?>">
                                    <?= number_format($data['rata_rata'], 1) ?>
                                </td>
                                <td class="text-center font-weight-bold">
                                    <?= esc($data['nilai_huruf']) ?>
                                </td>
                                <td class="text-center">
                                    <?php if ($data['is_complete']): ?>
                                        <?= esc($data['predikat_label']) ?>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <?php if (!$data['is_complete']): ?>
                                        <span class="badge badge-warning">Progres</span>
                                    <?php elseif ($data['status'] == 'LULUS'): ?>
                                        <span class="badge badge-success">Lulus</span>
                                    <?php else: ?>
                                        <span class="badge badge-danger">Tdk Lulus</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <span class="badge badge-info"><?= $rank ?></span>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</section>
<?= $this->endSection(); ?>

<?= $this->section('scripts'); ?>
<script>
    $(document).ready(function() {
        $('#tableSertifikat').DataTable({
            "responsive": true,
            "autoWidth": false,
            "pageLength": 50,
        });
    });
</script>
<?= $this->endSection(); ?>
