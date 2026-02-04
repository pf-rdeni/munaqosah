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
            <style>
                @keyframes blink-animation {
                    0% { opacity: 1; transform: scale(1); }
                    50% { opacity: 0.7; transform: scale(1.05); box-shadow: 0 0 10px rgba(40, 167, 69, 0.5); }
                    100% { opacity: 1; transform: scale(1); }
                }
                .btn-blink {
                    animation: blink-animation 1s infinite ease-in-out;
                    font-weight: bold;
                }
            </style>
            <div class="card-body">
                <form id="form-search-peserta" class="form-inline justify-content-center">
                    <div class="input-group input-group-lg mb-2 mr-sm-2">
                        <div class="input-group-prepend">
                            <div class="input-group-text"><i class="fas fa-id-card"></i></div>
                        </div>
                        <input type="text" class="form-control" id="no_peserta" name="no_peserta" placeholder="Masukkan No Peserta" autofocus required autocomplete="off">
                            <div class="input-group-append">
                                 <button class="btn btn-success btn-blink" type="button" id="btnPesertaAntrian" style="display: none;">
                                    <i class="fas fa-bell mr-1"></i> <span id="btnPesertaAntrianText">Dipanggil: 101</span>
                                </button>
                            </div>
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

        // Auto-Load from URL Parameter
        const urlParams = new URLSearchParams(window.location.search);
        const pesertaParam = urlParams.get('peserta');
        if (pesertaParam) {
            $('#no_peserta').val(pesertaParam);
            // Delay slightly to ensure everything loaded
            setTimeout(() => {
                $('#form-search-peserta').submit();
            }, 500);
        }
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

    // ==================== CHECK ANTRIAN PESERTA ====================
    let checkAntrianTimer = null;
    let currentRecommendedNoPeserta = null;

    function checkAntrianPeserta() {
        // Cek apakah input kosong? Jika user sedang mengetik sebaiknya jangan ganggu?
        // Tapi requirement says: "show recommendation button"
        
        $.ajax({
            url: '<?= base_url('backend/munaqosah/input-nilai/get-next-peserta-from-antrian') ?>',
            type: 'GET',
            dataType: 'json',
            success: function(response) {
                if (response.success && response.hasPeserta && response.NoPeserta) {
                    if (currentRecommendedNoPeserta !== response.NoPeserta) {
                        currentRecommendedNoPeserta = response.NoPeserta;
                        showPesertaAntrianButton(response.NoPeserta);
                    }
                } else {
                    if (currentRecommendedNoPeserta !== null) {
                        currentRecommendedNoPeserta = null;
                        hidePesertaAntrianButton();
                    }
                }
            },
            error: function(xhr, status, error) {
                console.error('Error checking antrian:', error);
            }
        });
    }

    function showPesertaAntrianButton(noPeserta) {
        // Show clean number (e.g. 101 from Peserta-101)
        const cleanNo = noPeserta.includes('-') ? noPeserta.split('-').pop() : noPeserta;
        $('#btnPesertaAntrianText').text('Peserta: ' + cleanNo);
        $('#btnPesertaAntrian').fadeIn(300);
        // Optional: Notify sound or toast
    }

    function hidePesertaAntrianButton() {
        $('#btnPesertaAntrian').fadeOut(300);
    }

    // Button Handler
    $('#btnPesertaAntrian').on('click', function() {
        if (currentRecommendedNoPeserta) {
            $('#no_peserta').val(currentRecommendedNoPeserta);
            $('#form-search-peserta').submit();
        }
    });

    // Start Polling
    function startCheckAntrian() {
        if (checkAntrianTimer) clearInterval(checkAntrianTimer);
        checkAntrianPeserta(); // Run immediately
        checkAntrianTimer = setInterval(checkAntrianPeserta, 3000); // 3s for fast response
    }
    
    // Stop Polling (e.g. when form is loaded)
    // Sebaiknya tetap polling jika ingin tahu update? 
    // Tapi jika sedang menilai (Input Form Active), mungkin tidak perlu polling untuk current user,
    // KECUALI status berubah jadi waiting lagi? 
    // Tapi logikanya: Juri sedang menilai SATU orang. 
    // Jadi mungkin pause polling saat form loaded, dan resume saat selesai/cancel.
    
    // Hook into existing logic
    // Pada loadForm success -> stop polling
    // Tapi di script atas, logic ada di $(document).ready.
    // Kita bisa override atau inject logic tambahan.
    
    startCheckAntrian();

    // Override original submit logic slightly to pause polling?
    // Or just let it run. If 'sedang_ujian' status persists until grading done? 
    // Antrian status usually updated to 'selesai' AFTER grading saved?
    // Or updated to 'sedang_ujian' via Monitoring Dashboard?
    // Current logic: Juri assesses. Queue status depends on external operator OR auto-update.
    // If we want auto-update status when Juri starts grading?
    // For now, let's keep polling. It won't hurt much.
    // But button might confuse if shown while grading another student.
    // Maybe hide button if #penilaian-container is not empty?
    
    // Let's refine checkAntrianPeserta to only show if #penilaian-container is empty (or showing search form)
    // Actually, the search form is always visible (collapsible).
    // If form is collapsed (CardWidget), maybe we shouldn't show button?
    // Let's stick to basic requirement first.

</script>
<?= $this->endSection() ?>
