<!DOCTYPE html>
<html>
<head>
    <title>Form Penilaian Juri</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
        }
        .header h2 {
            margin: 0;
            padding: 0;
        }
        .header p {
            margin: 5px 0 0;
            font-size: 14px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            border: 1px solid #000;
            padding: 8px;
            font-size: 11px;
        }
        th {
            background-color: #f2f2f2;
            text-align: center;
        }
        .text-center {
            text-align: center;
        }
        .no-col {
            width: 30px;
        }
        .peserta-col {
            width: 150px;
        }
        /* Adjust column widths based on count, but auto is usually fine for PDF */
    </style>
</head>
<body>
    <div class="header">
        <h2>FORM PENILAIAN JURI</h2>
        <p>Grup Materi: <strong><?= strtoupper($grupMateri['nama_grup_materi']) ?></strong></p>
    </div>

    <table>
        <thead>
            <tr>
                <th class="no-col">No</th>
                <th class="peserta-col">No Peserta</th>
                <?php foreach($headers as $h): ?>
                    <th><?= $h ?></th>
                <?php endforeach; ?>
            </tr>
        </thead>
        <tbody>
            <?php for($i = 1; $i <= $rows; $i++): ?>
            <tr>
                <td class="text-center"><?= $i ?></td>
                <td>&nbsp;</td> <!-- No Peserta -->
                <?php foreach($headers as $h): ?>
                    <td>&nbsp;</td>
                <?php endforeach; ?>
            </tr>
            <?php endfor; ?>
        </tbody>
    </table>

    <?php if(!empty($useAttachment) && $useAttachment): ?>
        <div style="page-break-before: always;"></div>

        <div class="header">
            <h2>LAMPIRAN: <?= $attachmentTitle ?></h2>
            <p>Grup Materi: <strong><?= strtoupper($grupMateri['nama_grup_materi']) ?></strong></p>
        </div>

        <table>
            <thead>
                <tr>
                    <th class="no-col">No</th>
                    <th class="peserta-col">No Peserta</th>
                    <th>Nama Peserta</th>
                    <th>Materi / Surah Undian</th>
                </tr>
            </thead>
            <tbody>
                <?php if(!empty($attachmentData)): ?>
                    <?php $no = 1; foreach($attachmentData as $d): ?>
                    <tr>
                        <td class="text-center"><?= $no++ ?></td>
                        <td class="text-center"><?= $d['no_peserta'] ?></td>
                        <td><?= strtoupper($d['nama_siswa']) ?></td>
                        <td><?= $d['materi'] ?></td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="4" class="text-center">Belum ada data peserta undian.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    <?php endif; ?>
</body>
</html>
