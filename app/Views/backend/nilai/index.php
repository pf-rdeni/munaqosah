<?= $this->extend('backend/template/template') ?>

<?= $this->section('content') ?>
<div class="row">
    <!-- Step 1: Input Peserta -->
    <div class="col-md-12">
        <div class="card card-primary card-outline">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-search mr-2"></i>Cari Peserta</h3>
                <div class="card-tools">
                    <button type="button" class="btn btn-tool" data-card-widget="collapse" title="Tampilkan/Sembunyikan Pencarian">
                        <i class="fas fa-eye"></i>
                    </button>
                </div>
            </div>
            <div class="card-body">
                <form id="form-search-peserta" class="form-inline justify-content-center">
                    <div class="input-group input-group-lg mb-2 mr-sm-2">
                        <div class="input-group-prepend">
                            <div class="input-group-text"><i class="fas fa-id-card"></i></div>
                        </div>
                        <input type="text" class="form-control" id="no_peserta" name="no_peserta" placeholder="Masukkan No Peserta" autofocus required autocomplete="off">
                    </div>
                    <button type="submit" class="btn btn-primary btn-lg mb-2"><i class="fas fa-search"></i> Cari / Mulai</button>
                </form>
                <div class="text-center text-muted mt-2">
                    <small>Masukkan Nomor Peserta (Contoh: 101) lalu tekan Enter</small>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Step 2: Form Penilaian (AJAX Container) -->
<div class="row">
    <div class="col-md-12">
        <div id="penilaian-container"></div>
    </div>
</div>

<div class="row mt-3">
    <div class="col-md-12">
        <div class="card collapsed-card card-outline card-secondary" id="card-history">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-history mr-2"></i> Riwayat Penilaian 
                    <span class="badge badge-info ml-2" id="history-badge"><?= count($listDinilai ?? []) ?> Peserta</span>
                </h3>
                <div class="card-tools">
                    <button type="button" class="btn btn-tool" data-card-widget="collapse">
                        <i class="fas fa-plus"></i>
                    </button>
                </div>
            </div>
            <div class="card-body p-0">
                <table class="table table-striped table-sm">
                    <thead>
                        <tr>
                            <th class="text-center" width="5%">No</th>
                            <th>Nama Peserta</th>
                            <th>No Peserta</th>
                            <th>Waktu Penilaian</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="history-tbody">
                        <?php if(empty($listDinilai)): ?>
                            <tr><td colspan="5" class="text-center text-muted py-3">Belum ada data penilaian.</td></tr>
                        <?php else: ?>
                            <?php $no=1; foreach($listDinilai as $d): ?>
                                <tr>
                                    <td class="text-center"><?= $no++ ?></td>
                                    <td><?= esc($d['nama_siswa']) ?></td>
                                    <td><strong><?= esc($d['no_peserta']) ?></strong></td>
                                    <td><?= \CodeIgniter\I18n\Time::parse($d['tgl_nilai'])->humanize() ?></td>
                                    <td class="text-center">
                                        <button type="button" class="btn btn-xs btn-primary btn-pilih-history" data-nopeserta="<?= $d['no_peserta'] ?>">
                                            <i class="fas fa-eye mr-1"></i> Pilih
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>



<?= $this->section('scripts') ?>
<script>
    $(document).ready(function() {
        $('#form-search-peserta').submit(function(e) {
            e.preventDefault();
            const noPeserta = $('#no_peserta').val();
            
            if(!noPeserta) return;

            // Loading state
            $('#penilaian-container').html('<div class="text-center py-5"><i class="fas fa-spinner fa-spin fa-3x text-primary"></i><br>Memuat Data...</div>');

            $.ajax({
                url: '<?= base_url('backend/munaqosah/input-nilai/load-form') ?>',
                type: 'POST',
                data: { no_peserta: noPeserta },
                success: function(response) {
                    if(response.success) {
                        $('#penilaian-container').html(response.html);
                        // Auto Collapse Search Card
                        $('.card-primary').CardWidget('collapse');
                        // Auto Collapse History Card if open
                        $('#card-history').CardWidget('collapse');
                    } else {
                        $('#penilaian-container').html('<div class="alert alert-danger text-center">'+response.message+'</div>');
                    }
                },
                error: function() {
                    $('#penilaian-container').html('<div class="alert alert-danger text-center">Terjadi kesalahan server.</div>');
                }
            });
        });

        // History Select Handler
        $(document).on('click', '.btn-pilih-history', function() {
            var noPeserta = $(this).data('nopeserta');
            $('#no_peserta').val(noPeserta);
            $('#form-search-peserta').submit();
        });
    });

    // Global function to refresh history
    window.loadHistory = function() {
        $.ajax({
            url: '<?= base_url('backend/munaqosah/input-nilai/refresh-history') ?>',
            type: 'POST',
            success: function(res) {
                 if(res.success) {
                     $('#history-tbody').html(res.html);
                     $('#history-badge').text(res.count + ' Peserta');
                 }
            }
        });
    }
</script>
<?= $this->endSection() ?>
