<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak Kartu Ujian Munaqosah</title>
    <style>
        @page {
            size: A4;
            margin: 0.5cm;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 0;
            font-size: 11px;
            background: #e0e0e0;
        }

        @media print {
            body {
                background: white;
            }
            .no-print {
                display: none;
            }
        }

        .page-container {
            width: 21cm;
            min-height: 29.7cm;
            background: white;
            margin: 0 auto;
            padding: 0.5cm;
            box-sizing: border-box;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }

        .kartu {
            width: 9.5cm;
            height: auto;
            min-height: 5.5cm;
            border: 1px solid #000;
            padding: 10px;
            margin: 5px;
            float: left;
            box-sizing: border-box;
            position: relative;
        }

        .header {
            text-align: center;
            border-bottom: 2px solid #000;
            padding-bottom: 3px;
            margin-bottom: 8px;
        }

        .header-title {
            font-weight: bold;
            font-size: 13px;
            text-transform: uppercase;
        }
        .header-subtitle {
            font-size: 9px;
            margin-top: 2px;
        }

        /* Top Layout: Photo (Left) - Data (Right) */
        .top-section {
            display: flex;
            margin-bottom: 5px;
        }

        .photo-area {
            width: 2.2cm;
            margin-right: 10px;
        }

        .photo-box {
            width: 2cm;
            height: 2.5cm;
            border: 1px solid #999;
            background: #f9f9f9;
        }
        
        .photo-box img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .photo-label {
            text-align: center; 
            color: #ccc; 
            line-height: 2.5cm; 
            font-size: 10px;
        }

        .data-area {
            flex-grow: 1;
            position: relative;
        }

        .data-row {
            font-size: 11px;
            margin-bottom: 3px;
        }
        
        .data-label {
            display: inline-block;
            width: 40px;
            font-weight: bold;
        }

        /* Huge Number in Center */
        .big-number {
            font-size: 64px;
            font-weight: bold;
            text-align: center;
            margin: 10px 0 15px 0;
            line-height: 1;
        }

        /* Table Bottom */
        .nilai-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 10px;
            margin-top: 5px;
            border: 1px solid #000;
        }
        
        .nilai-table th, .nilai-table td {
            border: 1px solid #000;
            padding: 4px;
            text-align: center;
        }

        .nilai-table th {
            background-color: #f0f0f0;
            font-weight: bold;
        }

        .nilai-table td {
            height: 35px;
            vertical-align: middle;
        }
        
        .paraf-placeholder {
            color: #ccc;
            font-size: 9px;
        }

    </style>
</head>
<body>

    <div class="no-print" style="padding: 10px; text-align: center; background: #333; color: white; position: sticky; top: 0; z-index: 100;">
        <button onclick="window.print()" style="padding: 10px 20px; font-weight: bold; cursor: pointer;">CETAK KARTU (PDF)</button>
        <span style="margin-left: 20px;">Gunakan kertas A4, Margin Minimum.</span>
    </div>

    <?php 
        $chunks = array_chunk($peserta, 8); 
        foreach($chunks as $pageIndex => $chunk): 
    ?>
    <div class="page-container">
        <?php foreach($chunk as $p): ?>
            <div class="kartu">
                <!-- 1. Header -->
                <div class="header">
                    <div class="header-title">NO. KARTU PESERTA MUNAQOSAH</div>
                    <div class="header-subtitle">Tahun Ajaran <?= $tahunAjaran ?></div>
                </div>

                <!-- 2. Top Section: Photo & Identity -->
                <div class="top-section">
                    <!-- Left: Photo -->
                    <div class="photo-area">
                        <div class="photo-box">
                            <?php if(!empty($p['foto']) && file_exists(FCPATH . $p['foto'])): ?>
                                <img src="<?= base_url($p['foto']) ?>">
                            <?php else: ?>
                                <div class="photo-label">FOTO</div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Right: Identity & Big Number -->
                    <div class="data-area">
                        <div class="data-row">
                            <span class="data-label">Nama</span> : <strong><?= $p['nama_siswa'] ?></strong>
                        </div>
                        <div class="data-row">
                            <span class="data-label">NISN</span> : <?= $p['nisn'] ?>
                        </div>

                        <!-- 3. Big Number Center -->
                        <div class="big-number">
                            <?= $p['no_peserta'] ?>
                        </div>
                    </div>
                </div>

                <!-- 4. Bottom Table -->
                <table class="nilai-table">
                    <thead>
                        <tr>
                            <th width="33%">Praktek Wudhu</th>
                            <th width="33%">Praktek Sholat</th>
                            <th width="33%">Tahfidz</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><span class="paraf-placeholder">Paraf</span></td>
                            <td><span class="paraf-placeholder">Paraf</span></td>
                            <td><span class="paraf-placeholder">Paraf</span></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        <?php endforeach; ?>
        <div style="clear: both;"></div>
    </div>
    
    <!-- Page Break Logic -->
    <?php if($pageIndex < count($chunks) - 1): ?>
        <div style="page-break-after: always; clear: both;"></div>
    <?php endif; ?>

    <?php endforeach; ?>

</body>
</html>
