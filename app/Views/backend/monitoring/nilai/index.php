<?= $this->extend('backend/template/template'); ?>

<?= $this->section('content'); ?>
<div class="row">
    <div class="col-12">
        <div class="card card-primary card-outline">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-chart-line mr-1"></i>
                    Monitoring Nilai & Ranking
                </h3>
                <div class="card-tools">
                    <button type="button" class="btn btn-tool" data-card-widget="maximize">
                        <i class="fas fa-expand"></i>
                    </button>
                    <button type="button" class="btn btn-tool" onclick="location.reload()">
                        <i class="fas fa-sync-alt"></i>
                    </button>
                </div>
            </div>
            <div class="card-body p-1">
                <div class="table-responsive">
                    <table id="tblMonitoring" class="table table-bordered table-striped table-hover table-sm text-sm" style="width:100%">
                        <thead class="bg-light">
                            <!-- BARIS HEADER 1: MATERI UJIAN -->
                            <tr>
                                <th rowspan="3" class="text-center align-middle" width="30">No</th>
                                <th rowspan="3" class="align-middle" style="min-width:200px">Nama Peserta</th>
                                <th rowspan="3" class="text-center align-middle">No Tes</th>
                                <th rowspan="3" class="text-center align-middle">L/P</th>

                                <?php foreach($structure as $mId => $mData): ?>
                                    <?php 
                                        $mInfo = $mData['info'];
                                        $kriterias = $mData['kriteria'];
                                        $maxJuri = $materiColumns[$mId] ?? 1; // 1 atau 2
                                        
                                        // Hitung Colspan
                                        // Setiap Kriteria membutuhkan: (MaxJuri > 1 ? (MaxJuri + 1 Avg) : 1) + (Pengurangan ? 0 : 1 BB)
                                        // Logika Disempurnakan:
                                        // Jika MaxJuri > 1: Tampilkan NJ_A, NJ_B (..), AVG. 
                                        // Kemudian Kolom Bobot (jika bukan Pengurangan).
                                        // Total Kolom per Kriteria = (MaxJuri > 1 ? MaxJuri + 1 : 1) + ($isPengurangan ? 0 : 1)
                                        
                                        $isPengurangan = ($mInfo['kondisional_set'] == 'nilai_pengurangan');
                                        $totalMateriCols = 0;
                                        
                                        foreach($kriterias as $k) {
                                            $colsPerKriteria = ($maxJuri > 1 ? ($maxJuri + 1) : 1); 
                                            // Tambah Kolom Bobot
                                            if (!$isPengurangan) {
                                                $colsPerKriteria += 1; // Kolom 'BB_X'
                                            }
                                            $totalMateriCols += $colsPerKriteria;
                                        }
                                        
                                        // Tambah Kolom Subtotal per Materi
                                        $totalMateriCols += 1; 
                                    ?>
                                    <th colspan="<?= $totalMateriCols ?>" class="text-center align-middle border-bottom-0" style="background-color: #f4f6f9; border-left: 2px solid #6c757d;">
                                        <strong><?= esc($mInfo['nama_materi']) ?></strong>
                                    </th>
                                <?php endforeach; ?>

                                <th rowspan="3" class="text-center align-middle font-weight-bold text-success" style="font-size:1.1em;">GRAND TOTAL</th>
                                <th rowspan="3" class="text-center align-middle">RATA-RATA</th>
                                <th rowspan="3" class="text-center align-middle">PREDIKAT</th>
                            </tr>
                            
                            <!-- BARIS HEADER 2: KRITERIA -->
                            <tr>
                                <?php foreach($structure as $mId => $mData): ?>
                                    <?php 
                                        $mInfo = $mData['info'];
                                        $kriterias = $mData['kriteria'];
                                        $maxJuri = $materiColumns[$mId] ?? 1;
                                        $isPengurangan = ($mInfo['kondisional_set'] == 'nilai_pengurangan');
                                    ?>
                                    
                                    <?php foreach($kriterias as $k): ?>
                                        <?php 
                                            // Penggunaan ulang logika kolom
                                            $colsPerKriteria = ($maxJuri > 1 ? ($maxJuri + 1) : 1);
                                            if (!$isPengurangan) $colsPerKriteria += 1;
                                        ?>
                                        <th colspan="<?= $colsPerKriteria ?>" class="text-center align-middle text-muted small border-left" style="font-size: 0.85em;">
                                            <?= esc($k['nama_kriteria']) ?>
                                        </th>
                                    <?php endforeach; ?>
                                    
                                    <!-- Header Subtotal -->
                                    <th rowspan="2" class="text-center align-middle font-weight-bold text-orange small">SUB TOTAL</th>
                                <?php endforeach; ?>
                            </tr>

                            <!-- BARIS HEADER 3: KOLOM DETAIL (NJ, BB, AVG) -->
                            <tr>
                                <?php foreach($structure as $mId => $mData): ?>
                                    <?php 
                                        $mInfo = $mData['info'];
                                        $kriterias = $mData['kriteria'];
                                        $maxJuri = $materiColumns[$mId] ?? 1;
                                        $isPengurangan = ($mInfo['kondisional_set'] == 'nilai_pengurangan');
                                    ?>
                                    
                                    <?php foreach($kriterias as $k): ?>
                                        <?php if ($maxJuri > 1): ?>
                                            <!-- Kolom Multi Juri -->
                                            <?php for($j=1; $j<=$maxJuri; $j++): ?>
                                                <th class="text-center text-xs text-muted">Juri <?= $j ?></th>
                                            <?php endfor; ?>
                                            <th class="text-center text-xs font-weight-bold bg-light">AVG</th>
                                        <?php else: ?>
                                            <!-- Juri Tunggal -->
                                            <th class="text-center text-xs text-muted">Nilai</th>
                                        <?php endif; ?>
                                        
                                        <?php if(!$isPengurangan): ?>
                                            <th class="text-center text-xs font-weight-bold" title="Bobot: <?= $k['bobot'] ?>">BB</th>
                                        <?php endif; ?>
                                        
                                    <?php endforeach; ?>
                                <?php endforeach; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $no=1; foreach($pesertaList as $p): ?>
                                <?php 
                                    $np = $p['no_peserta'];
                                    $pData = $finalData[$np] ?? []; 
                                ?>
                                <tr>
                                    <td class="text-center"><?= $no++ ?></td>
                                    <td>
                                        <div class="d-flex flex-column">
                                            <span class="font-weight-bold"><?= esc($p['nama_siswa']) ?></span>
                                            <!-- <small class="text-muted"><?= $p['nisn'] ?></small> -->
                                        </div>
                                    </td>
                                    <td class="text-center"><?= $p['no_peserta'] ?></td>
                                    <td class="text-center"><?= $p['jenis_kelamin'] ?></td>

                                    <!-- Loop Data Nilai -->
                                    <?php foreach($structure as $mId => $mData): ?>
                                        <?php 
                                            $mInfo = $mData['info'];
                                            $kriterias = $mData['kriteria'];
                                            $maxJuri = $materiColumns[$mId] ?? 1;
                                            $isPengurangan = ($mInfo['kondisional_set'] == 'nilai_pengurangan');
                                            
                                            $mRes = $pData[$mId] ?? ['subtotal' => 0, 'details' => []];
                                            $details = $mRes['details'];
                                        ?>
                                        
                                        <?php foreach($kriterias as $k): ?>
                                            <?php 
                                                $kid = $k['id'];
                                                $kRes = $details[$kid] ?? ['avg' => 0, 'bb' => 0, 'raw' => []];
                                                $raws = $kRes['raw']; // Nilai Juri
                                            ?>
                                            
                                            <!-- Render Kolom Juri -->
                                            <?php if ($maxJuri > 1): ?>
                                                <?php for($j=0; $j<$maxJuri; $j++): ?>
                                                    <?php $val = $raws[$j] ?? '-'; ?>
                                                    <td class="text-center text-muted"><?= $val ?></td>
                                                <?php endfor; ?>
                                                <!-- Rata-rata -->
                                                <td class="text-center font-weight-bold bg-light" style="background-color:#f9f9f9">
                                                    <?= round($kRes['avg'], 1) ?>
                                                </td>
                                            <?php else: ?>
                                                <!-- Nilai Tunggal -->
                                                <td class="text-center">
                                                    <?= !empty($raws) ? $raws[0] : '-' ?>
                                                </td>
                                            <?php endif; ?>
                                            
                                            <!-- Render Bobot -->
                                            <?php if(!$isPengurangan): ?>
                                                <td class="text-center font-weight-bold text-primary">
                                                    <?= floatval($kRes['bb']) > 0 ? round($kRes['bb'], 1) : '-' ?>
                                                </td>
                                            <?php endif; ?>
                                            
                                        <?php endforeach; // Akhir Loop Kriteria ?>
                                        
                                        <!-- Sel Subtotal -->
                                        <td class="text-center font-weight-bold text-orange">
                                            <?= round($mRes['subtotal'], 1) ?>
                                        </td>
                                        
                                    <?php endforeach; // Akhir Loop Materi ?>

                                    <!-- Kolom Grand Total -->
                                    <td class="text-center font-weight-bold text-success" style="font-size:1.1em">
                                        <?= round($pData['grand_total'] ?? 0, 1) ?>
                                    </td>
                                    <td class="text-center">
                                        <?= round($pData['rata_rata'] ?? 0, 1) ?>
                                    </td>
                                    <td class="text-center">
                                        <?php 
                                            $status = $pData['status'] ?? 'TDK LULUS';
                                            if ($status == 'LULUS'): ?>
                                                <span class="badge badge-success">LULUS</span>
                                            <?php elseif ($status == 'PROGRES'): ?>
                                                <span class="badge badge-warning">PROGRES</span>
                                            <?php else: ?>
                                                <span class="badge badge-danger">TDK LULUS</span>
                                            <?php endif; ?>
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

<?= $this->section('scripts'); ?>
<script>
$(document).ready(function() {
    var table = $('#tblMonitoring').DataTable({
        "scrollY": "600px",
        "scrollX": true,
        "scrollCollapse": true,
        "paging": true,
        "fixedColumns": {
            leftColumns: 2 // Lock No & Nama Peserta
        },
        "lengthMenu": [[10, 25, 50, -1], [10, 25, 50, "Semua"]],
        "dom": 'lBfrtip',
        "buttons": [
            { extend: 'excelHtml5', title: 'Data Nilai Munaqosah', className: 'btn btn-success btn-sm' },
            { extend: 'pdfHtml5', title: 'Data Nilai Munaqosah', className: 'btn btn-danger btn-sm' },
            { extend: 'print', className: 'btn btn-info btn-sm' }
        ]
    });
});
</script>
<?= $this->endSection(); ?>
