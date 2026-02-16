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
                                <th width="5%" class="align-middle">No</th>
                                <th width="12%" class="align-middle">Aksi</th>
                                <th width="5%" class="align-middle">Foto</th>
                                <th class="align-middle">Nama Peserta</th>
                                <th class="align-middle">No Peserta</th>
                                
                                <!-- Materi Columns -->
                                <?php foreach ($materiList as $m): ?>
                                    <th class="align-middle"><?= $m['nama_materi'] ?></th>
                                <?php endforeach; ?>
                                
                                <th class="align-middle">Total</th>
                                <th class="align-middle">Rata-Rata</th>
                                <th class="align-middle">Nilai Huruf</th>
                                <th class="align-middle">Predikat</th>
                                <th class="align-middle">Status</th>
                                <th class="align-middle">Peringkat</th>
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
                                <td class="text-center text-nowrap">
                                    <?php if ($data['is_complete'] && $data['status'] == 'LULUS'): ?>
                                    <!-- Cetak PDF -->
                                    <a href="<?= base_url('backend/cetak-sertifikat/print/' . $p['id']) ?>" 
                                       target="_blank" 
                                       class="btn btn-xs btn-info"
                                       title="Cetak Sertifikat">
                                        <i class="fas fa-print"></i>
                                    </a>
                                    <?php if (!empty($data['sertifikat_link'])): 
                                        // Format No HP for WhatsApp (08xx -> 628xx)
                                        $noHp = $p['no_hp'] ?? '';
                                        $waNumber = '';
                                        if (!empty($noHp)) {
                                            $noHp = preg_replace('/[^0-9]/', '', $noHp); // Remove non-numeric
                                            if (substr($noHp, 0, 2) == '08') {
                                                $waNumber = '62' . substr($noHp, 1);
                                            } elseif (substr($noHp, 0, 3) == '628') {
                                                $waNumber = $noHp;
                                            }
                                        }
                                    ?>
                                    <!-- Kirim via WhatsApp -->
                                    <?php
                                        // Base message without emojis for JS to handle
                                        $pesanBase = "Assalamu'alaikum Warahmatullahi Wabarakatuh, Ayah/Bunda.\n\n"
                                            . "Alhamdulillah, Ananda *" . esc($p['nama_siswa']) . "* telah menyelesaikan Munaqosah dengan predikat *" . esc($data['predikat_label']) . "*.\n\n"
                                            . "Jazakumullahu Khairan Katsiran atas segala doa, dukungan, dan pendampingan Ayah/Bunda yang luar biasa, sehingga Ananda dapat mengikuti tahapan ujian ini dengan baik.\n\n"
                                            . "Berikut kami lampirkan e-sertifikat sebagai tanda cinta dan apresiasi atas perjuangan Ananda:\n"
                                            . $data['sertifikat_link'] . "\n\n"
                                            . "Mari terus kita dampingi dan motivasi Ananda untuk selalu mencintai Al-Qur'an dan memberikan yang terbaik. Semoga lelah Ayah/Bunda menjadi lillah dan pahala jariyah. Aamiin.\n\n"
                                            . "SDIT AN NAHL Kec. Seri Kuala Lobam";
                                    ?>
                                    <button type="button" 
                                            class="btn btn-xs btn-success btn-wa-share"
                                            data-phone="<?= $waNumber ?>"
                                            data-message="<?= esc($pesanBase) ?>"
                                            title="Kirim via WhatsApp <?= $waNumber ? '('.$waNumber.')' : '' ?>">
                                        <i class="fab fa-whatsapp"></i>
                                    </button>
                                    <!-- Copy Link -->
                                    <button type="button" 
                                            class="btn btn-xs btn-warning btn-copy-link" 
                                            data-link="<?= esc($data['sertifikat_link']) ?>"
                                            title="Copy Link Sertifikat">
                                        <i class="fas fa-copy"></i>
                                    </button>
                                    <?php endif; ?>
                                    <?php else: ?>
                                    <button class="btn btn-xs btn-secondary" disabled title="Belum Lengkap / Tidak Lulus"><i class="fas fa-print"></i></button>
                                    <?php endif; ?>
                                </td>
                                <!-- Foto Column -->
                                <td class="text-center align-middle">
                                    <?php if (!empty($p['foto']) && file_exists(FCPATH . $p['foto'])): ?>
                                        <img src="<?= base_url($p['foto']) ?>" class="img-circle elevation-1" style="width: 35px; height: 35px; object-fit: cover;">
                                    <?php else: 
                                        // Generate Avatar: 2 chars (Belakang + Depan)
                                        $names = explode(' ', trim($p['nama_siswa']));
                                        $initials = '';
                                        if (count($names) > 0) {
                                            $first = strtoupper(substr($names[0], 0, 1));
                                            $last = strtoupper(substr(end($names), 0, 1));
                                            // User requested: Depan + Belakang
                                            $initials = $first . $last;
                                        } else {
                                            $initials = '?';
                                        }
                                        // Random bg color based on name length
                                        $colors = ['#007bff', '#6610f2', '#6f42c1', '#e83e8c', '#dc3545', '#fd7e14', '#ffc107', '#28a745', '#20c997', '#17a2b8'];
                                        $color = $colors[strlen($p['nama_siswa']) % count($colors)];
                                    ?>
                                        <div style="width: 35px; height: 35px; background-color: <?= $color ?>; color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: bold; font-size: 14px; margin: 0 auto;">
                                            <?= $initials ?>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td class="align-middle"><?= esc($p['nama_siswa']) ?></td>
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
            "pageLength": 25,
            "ordering": true,
            "searching": true,
        });

        // Copy link to clipboard
        $(document).on('click', '.btn-copy-link', function() {
            var link = $(this).data('link');
            var $btn = $(this);
            
            if (navigator.clipboard && navigator.clipboard.writeText) {
                navigator.clipboard.writeText(link).then(function() {
                    showCopySuccess($btn);
                }).catch(function() {
                    fallbackCopy(link, $btn);
                });
            } else {
                fallbackCopy(link, $btn);
            }
        });

        function fallbackCopy(text, $btn) {
            var $temp = $('<input>');
            $('body').append($temp);
            $temp.val(text).select();
            document.execCommand('copy');
            $temp.remove();
            showCopySuccess($btn);
        }

        function showCopySuccess($btn) {
            var originalHtml = $btn.html();
            $btn.html('<i class="fas fa-check"></i>').removeClass('btn-warning').addClass('btn-primary');
            
            // Toastr notification if available
            if (typeof toastr !== 'undefined') {
                toastr.success('Link sertifikat berhasil disalin!', '', { timeOut: 2000 });
            }

            setTimeout(function() {
                $btn.html(originalHtml).removeClass('btn-primary').addClass('btn-warning');
            }, 1500);
        }

        // WhatsApp Share Handler
        $(document).on('click', '.btn-wa-share', function() {
            var phone = $(this).data('phone');
            var messageBase = $(this).data('message');
            
            // Send plain message without emojis
            var url = "https://wa.me/" + phone + "?text=" + encodeURIComponent(messageBase);
            window.open(url, '_blank');
        });
    });
</script>
<?= $this->endSection(); ?>
