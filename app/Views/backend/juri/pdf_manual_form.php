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
</body>
</html>
