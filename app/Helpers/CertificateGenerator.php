<?php

namespace App\Helpers;

use Dompdf\Dompdf;
use Dompdf\Options;
use setasign\Fpdi\Fpdi;
use setasign\Fpdi\PdfParser\StreamReader;

class CertificateGenerator
{
    protected $template;
    protected $fields;
    protected $data;
    protected $dompdf;
    protected $mergedPdfContent = null;

    public function __construct($template, $fields)
    {
        $this->template = $template;
        $this->fields = $fields;

        // Configure DomPDF
        $options = new Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', true);
        $options->set('isPhpEnabled', true);
        $options->set('defaultFont', 'Arial');
        
        $this->dompdf = new Dompdf($options);
    }

    /**
     * Set data untuk field
     */
    public function setData($data)
    {
        $this->data = $data;
        return $this;
    }

    /**
     * Generate PDF
     */
    /**
     * Generate PDF (Single Page)
     */
    public function generate()
    {
        $html = $this->buildHtml();
        $this->dompdf->loadHtml($html);
        $this->setPaper();
        $this->dompdf->render();
        return $this;
    }

    /**
     * Generate PDF (Front + Back Page)
     * @param array $backTemplate Template data for back page
     * @param array $backFields Field configurations for back page
     * @param array $scoreData Data for the score table
     */
    public function generateWithBackPage($backTemplate, $backFields, $scoreData)
    {
        // 1. Generate Front Page PDF (Landscape)
        $htmlFrontContent = $this->buildHtml();
        // Calculate dimensions in points (pt) for Front Page
        $fWidthPx = (float) $this->template['width'];
        $fHeightPx = (float) $this->template['height'];
        $fWidthPt = ($fWidthPx * 72) / 96;
        $fHeightPt = ($fHeightPx * 72) / 96;

        $htmlFront = '<html><head><style>
            @page { size: ' . $fWidthPt . 'pt ' . $fHeightPt . 'pt; margin: 0; }
            body { margin: 0; padding: 0; font-family: Arial, sans-serif; }
            .certificate-container { position: relative; width: 100%; height: 100%; overflow: hidden; }
            .template-image { position: absolute; top: 0; left: 0; width: 100%; height: 100%; z-index: -1; }
            .field { position: absolute; white-space: nowrap; }
        </style></head><body>';
        $htmlFront .= $this->getContainerHtml($this->template, $htmlFrontContent, false);
        $htmlFront .= '</body></html>';

        $options = $this->dompdf->getOptions();
        $dompdfFront = new Dompdf($options);
        $dompdfFront->loadHtml($htmlFront);
        $dompdfFront->setPaper([0, 0, $fWidthPt, $fHeightPt], 'landscape'); // Ensure landscape
        $dompdfFront->render();
        $pdfFrontString = $dompdfFront->output();

        // 2. Generate Back Page PDF (Portrait)
        $htmlBackContent = $this->buildBackPageHtml($backTemplate, $backFields, $scoreData);
        // Calculate dimensions in points (pt) for Back Page
        $bWidthPx = (float) $backTemplate['width'];
        $bHeightPx = (float) $backTemplate['height'];
        $bWidthPt = ($bWidthPx * 72) / 96;
        $bHeightPt = ($bHeightPx * 72) / 96;

        $htmlBack = '<html><head><style>
            @page { size: ' . $bWidthPt . 'pt ' . $bHeightPt . 'pt; margin: 0; }
            body { margin: 0; padding: 0; font-family: Arial, sans-serif; }
            .certificate-container { position: relative; width: 100%; height: 100%; overflow: hidden; }
            .template-image { position: absolute; top: 0; left: 0; width: 100%; height: 100%; z-index: -1; }
            .field { position: absolute; white-space: nowrap; }
            /* Table Styles for Back Page */
            .score-table { border-collapse: collapse; }
            .score-table th, .score-table td { border: 1px solid #000; padding: 10px; }
            .score-table th { background-color: #f0f0f0; text-align: center; font-weight: bold; }
            .text-center { text-align: center; }
            .text-right { text-align: right; }
            .font-bold { font-weight: bold; }
        </style></head><body>';
        $htmlBack .= $this->getContainerHtml($backTemplate, $htmlBackContent, true);
        $htmlBack .= '</body></html>';

        $dompdfBack = new Dompdf($options);
        $dompdfBack->loadHtml($htmlBack);
        $dompdfBack->setPaper([0, 0, $bWidthPt, $bHeightPt], 'portrait'); // Ensure portrait
        $dompdfBack->render();
        $pdfBackString = $dompdfBack->output();

        // 3. Merge PDFs using FPDI
        $fpdi = new Fpdi();
        
        // Import Front Page
        $pageCount1 = $fpdi->setSourceFile(StreamReader::createByString($pdfFrontString));
        for ($i = 1; $i <= $pageCount1; $i++) {
            $tplId = $fpdi->importPage($i);
            $size = $fpdi->getTemplateSize($tplId);
            $fpdi->AddPage($size['width'] > $size['height'] ? 'L' : 'P', [$size['width'], $size['height']]);
            $fpdi->useTemplate($tplId);
        }

        // Import Back Page
        $pageCount2 = $fpdi->setSourceFile(StreamReader::createByString($pdfBackString));
        for ($i = 1; $i <= $pageCount2; $i++) {
            $tplId = $fpdi->importPage($i);
            $size = $fpdi->getTemplateSize($tplId);
            $fpdi->AddPage($size['width'] > $size['height'] ? 'L' : 'P', [$size['width'], $size['height']]);
            $fpdi->useTemplate($tplId);
        }

        // Output merged content to string
        $this->mergedPdfContent = $fpdi->Output('S');

        return $this;
    }

    protected function setPaper() 
    {
        // Set paper size to match template dimensions exactly
        $widthPx = (float) $this->template['width'];
        $heightPx = (float) $this->template['height'];
        $widthPt = ($widthPx * 72) / 96;
        $heightPt = ($heightPx * 72) / 96;
        $this->dompdf->setPaper([0, 0, $widthPt, $heightPt], 'portrait');
    }

    protected function getContainerHtml($template, $content, $isBack)
    {
        $templatePath = FCPATH . 'uploads/' . $template['file_template'];
        $width = $template['width'];
        $height = $template['height'];

        if (file_exists($templatePath)) {
            $imageData = base64_encode(file_get_contents($templatePath));
            $imageSrc = 'data:image/' . pathinfo($templatePath, PATHINFO_EXTENSION) . ';base64,' . $imageData;
        } else {
            $imageSrc = ''; 
        }

        $html = '<div class="certificate-container" style="width: '.$width.'px; height: '.$height.'px;">';
        $html .= '<img src="' . $imageSrc . '" class="template-image">';
        
        // Overlay Content
        if ($isBack) {
            // Back page content is mostly the table, already built with inline styles if needed
             $html .= $content;
        } else {
             // Front page content is the fields <div>s
             $html .= $content;
        }
        
        $html .= '</div>';
        return $html;
    }

    protected function buildBackPageHtml($template, $fields, $scoreData)
    {
        // Check Design Style
        $designStyle = $template['design_style'] ?? 'option1';

        if ($designStyle == 2 || $designStyle === 'option2') {
            return $this->buildOption2Html($template, $fields, $scoreData);
        }

        // Option 1 (Default Summary Table) logic follows...
        // ... (Existing logic for Option 1) ...
        
        $html = '';

        foreach ($fields as $field) {
            // Handle Table Block
            if ($field['field_name'] == 'block_table') {
                 // Table position
                $tableTop = $field['pos_y'];
                $tableLeft = $field['pos_x'];
                
                // Simplified Logic from previous iteration, but tailored to dynamic placement
                 $tableWidth = $template['width'] - ($tableLeft * 2);
                 if ($tableWidth < 200) $tableWidth = $template['width'] - 100; // Fallback

                 // Get mapped font family
                $fontFamily = $this->getFontMapping($field['font_family']);
                
                $html .= '<table class="score-table" style="position: absolute; top: ' . $tableTop . 'px; left: ' . $tableLeft . 'px; width: ' . $tableWidth . 'px; font-size: ' . $field['font_size'] . 'px; font-family: ' . $fontFamily . ';">
                    <thead>
                        <tr>
                            <th width="5%">No</th>
                            <th>Materi Ujian</th>
                            <th width="15%">Nilai Angka</th>
                            <th width="15%">Nilai Huruf</th>
                            <th width="30%">Terbilang</th>
                        </tr>
                    </thead>
                    <tbody>';
                
                $no = 1;
                foreach ($scoreData['scores'] as $materi => $data) {
                    $nilai = $data['nilai'];
                    $huruf = $data['huruf'];
                    
                    $html .= '<tr>
                        <td class="text-center">' . $no++ . '</td>
                        <td>' . $materi . '</td>
                        <td class="text-center">' . number_format($nilai, 0) . '</td>
                        <td class="text-center">' . $huruf . '</td>
                        <td class="text-center" style="font-style: italic;">' . $this->terbilang(round($nilai)) . '</td>
                    </tr>';
                }

                // Footer Rows (Total, Average)
                $html .= '<tr>
                    <td colspan="2" class="text-right font-bold">Jumlah</td>
                    <td class="text-center font-bold">' . number_format($scoreData['total'], 0) . '</td>
                    <td colspan="2" style="background-color: #eee;"></td>
                </tr>';
                
                $html .= '<tr>
                    <td colspan="2" class="text-right font-bold">Rata-Rata</td>
                    <td class="text-center font-bold">' . number_format($scoreData['avg'], 1) . '</td>
                    <td class="text-center font-bold">' . ($scoreData['nilai_huruf'] ?? '-') . '</td>
                    <td class="text-center font-bold" style="font-style: italic;">' . $this->terbilang($scoreData['avg']) . '</td>
                </tr>';

                $html .= '</tbody></table>';
                continue;
            }

            // Skip block_header/footer if they still exist in data but not in model (safety)
            if (in_array($field['field_name'], ['block_header', 'block_footer'])) continue;

            // Handle Text Fields (Dynamic Mapped Data)
            $text = isset($scoreData[$field['field_name']]) ? $scoreData[$field['field_name']] : '{'.$field['field_name'].'}';
            
            // Generate Style
            $style = "position: absolute;";
            $style .= "left: {$field['pos_x']}px;";
            $style .= "top: {$field['pos_y']}px;";
            $style .= "font-family: {$field['font_family']};";
            $style .= "font-size: {$field['font_size']}px;";
            $style .= "color: {$field['text_color']};";
            $style .= "line-height: 1;"; 
            $style .= "margin: 0; padding: 0;";
            
            if ($field['font_style'] == 'B') $style .= "font-weight: bold;";
            if ($field['font_style'] == 'I') $style .= "font-style: italic;";
            if ($field['font_style'] == 'U') $style .= "text-decoration: underline;";

            // Horizontal alignment only
            if ($field['text_align'] == 'C') {
                $style .= "transform: translateX(-50%);"; 
            } elseif ($field['text_align'] == 'R') {
                $style .= "transform: translateX(-100%);";
            }
            
            $html .= "<div style=\"{$style}\">{$text}</div>";
        }

        return $html;
    }

    /**
     * Build HTML for Option 2 (Detailed Tables)
     */
    protected function buildOption2Html($template, $fields, $scoreData)
    {
        $html = '';

        // 1. Render Fixed Fields first (Text fields)
        foreach ($fields as $field) {
            // Skip Block fields
            if (strpos($field['field_name'], 'block_materi_') === 0) continue; 
            if ($field['field_name'] == 'block_table') continue; 
            if (in_array($field['field_name'], ['block_header', 'block_footer'])) continue;

            $text = isset($scoreData[$field['field_name']]) ? $scoreData[$field['field_name']] : '';
            if ($text === '') continue; // Skip empty? Optional.
            
            $style = $this->buildFieldStyle($field);
            // ... (Same rendering logic for text)
            $html .= "<div class=\"field\" style=\"{$style}\">{$text}</div>";
        }

        // 2. Render Detailed Tables based on Configured Fields
        $detailedScores = $scoreData['detailed_scores'] ?? [];
        
        // Index score data by Materi ID (Need to sure we have IDs in detailedScores keys or separate)
        // Currently `detailedScores` uses Name as key: [ 'Tahfizh' => ... ]
        // But the Config Fields use ID: `block_materi_5`
        // We need a way to map ID to Name, or Name to ID.
        // In `CetakSertifikat`, we built detailedScores using Names.
        // Let's check `CetakSertifikat`. If we included IDs there, it would be easier.
        // Re-checking `CetakSertifikat`...
        // ... It joins `tbl_munaqosah_materi_ujian`.
        // We should probably key `detailedScores` by ID for robust mapping, OR include ID in the data.
        
        // Let's assume for now we need to match by ID.
        // I will do a quick fuzzy match or update CetakSertifikat to include ID in key?
        // Updating CetakSertifikat is safer. But let's work with what we have if possible.
        // In `CetakSertifikat`:
        // foreach ($activeMateri as $m) { $materiName = $m['nama_materi']; ... $detailedScores[$materiName] = ... }
        // The ID is available in `$m['id']`.
        // I should update `CetakSertifikat` to key by ID or include ID.
        // Let's look at `CetakSertifikat` again in next step if generic matching fails.
        // Actually, let's assume I will update `CetakSertifikat` to key by ID, e.g. $detailedScores[12] = ...
        // Wait, current implementation uses Names to display Title.
        // Let's support both or check.
        
        foreach ($fields as $field) {
            if (strpos($field['field_name'], 'block_materi_') === 0) {
                // Parse ID
                $materiId = (int) str_replace('block_materi_', '', $field['field_name']);
                
                // Find data for this ID
                // We need to look through $detailedScores to find matching ID.
                $materiData = null;
                $materiName = '';
                
                // Optimized search
                if (isset($detailedScores[$materiId])) {
                     $materiData = $detailedScores[$materiId];
                     $materiName = $materiData['nama_materi'] ?? 'Materi';
                } else {
                    // Fallback search if keys are names (Legacy/Current compatibility)
                    foreach ($detailedScores as $key => $val) {
                        if (isset($val['id']) && $val['id'] == $materiId) {
                            $materiData = $val;
                            $materiName = $val['nama_materi'];
                            break;
                        }
                    }
                }

                if (!$materiData) continue; // No data for this configured block

                // Render Table at X/Y
                $tableTop = $field['pos_y'];
                $tableLeft = $field['pos_x'];
                // Use defined width or calculate
                $tableWidth = ($field['max_width'] > 0) ? $field['max_width'] : ($template['width'] - $tableLeft - 50);

                // Use field font settings
                $fontFamily = $this->getFontMapping($field['font_family']);
                $fontSize = $field['font_size'];
                
                $html .= '<div style="position: absolute; top: ' . $tableTop . 'px; left: ' . $tableLeft . 'px; width: ' . $tableWidth . 'px;">';
                
                // Header (Optional, maybe user wants it?)
                $html .= '<div style="font-family: '.$fontFamily.'; font-size: '.($fontSize+2).'px; font-weight: bold; margin-bottom: 5px;">' . $materiName . '</div>';
                
                $html .= '<table class="score-table" style="width: 100%; font-size: '.$fontSize.'px; font-family: '.$fontFamily.'; border-collapse: collapse;">
                    <thead>
                        <tr>
                            <th width="5%">No</th>
                            <th>Kriteria Penilaian</th>
                            <th width="15%">Bobot</th>
                            <th width="15%">Nilai</th>
                            <th width="20%">BxN</th>
                        </tr>
                    </thead>
                    <tbody>';
                
                $no = 1;
                foreach (($materiData['kriteria'] ?? []) as $k) {
                    $html .= '<tr>
                        <td class="text-center">' . $no++ . '</td>
                        <td>' . $k['nama'] . '</td>
                        <td class="text-center">' . $k['bobot'] . '%</td>
                        <td class="text-center">' . number_format($k['nilai'], 1) . '</td>
                        <td class="text-center">' . number_format($k['score'], 1) . '</td>
                    </tr>';
                }
                
                // Total Row
                $html .= '<tr>
                    <td colspan="4" class="text-right font-bold">Total</td>
                    <td class="text-center font-bold" style="background-color: #eee;">' . number_format($materiData['total'], 1) . '</td>
                </tr>';
                
                $html .= '</tbody></table>';
                $html .= '</div>';
            } elseif (strpos($field['field_name'], 'block_group_') === 0) {
                 // GROUP TABLE Logic
                 $groupId = (int) str_replace('block_group_', '', $field['field_name']);
                 // Use field_label instead of label (DB Column mismatch)
                 $groupName = str_replace('Tabel Grup: ', '', $field['field_label']);

                 // Filter Materi by Group ID
                 $groupMateri = array_filter($detailedScores, function($m) use ($groupId) {
                     return isset($m['id_grup_materi']) && $m['id_grup_materi'] == $groupId;
                 });
                 
                 // If no materi in this group, skip?
                 if (empty($groupMateri)) continue;

                 // Render Table
                $tableTop = $field['pos_y'];
                $tableLeft = $field['pos_x'];
                $tableWidth = ($field['max_width'] > 0) ? $field['max_width'] : ($template['width'] - $tableLeft - 50);

                $fontFamily = $this->getFontMapping($field['font_family']);
                $fontSize = $field['font_size'];
                
                $html .= '<div style="position: absolute; top: ' . $tableTop . 'px; left: ' . $tableLeft . 'px; width: ' . $tableWidth . 'px;">';
                
                // Header
                $html .= '<div style="font-family: '.$fontFamily.'; font-size: '.($fontSize+2).'px; font-weight: bold; margin-bottom: 5px; background-color: #e0f7fa; padding: 5px; border: 1px solid #006064;">' . $groupName . '</div>';
                
                $html .= '<table class="score-table" style="width: 100%; font-size: '.$fontSize.'px; font-family: '.$fontFamily.'; border-collapse: collapse;">
                    <thead>
                        <tr>
                            <th width="5%">No</th>
                            <th>Materi Ujian</th>
                            <th width="15%">Nilai</th>
                            <th width="15%">Huruf</th>
                        </tr>
                    </thead>
                    <tbody>';
                
                $no = 1;
                $groupTotal = 0;
                $materiCount = 0;
                foreach ($groupMateri as $m) {
                    $nilai = $m['total'];
                    // Calculate Grade Letter locally if not passed? 
                    // detailedScores doesn't have letter? 
                    // scores array has it.
                    // We can try to fetch it from scores array or recalculate.
                    // Recalculating is safer if we don't have access to globalPredikats here easily without passing it.
                    // But wait, `getGradeLetter` method exists in this class (helper).
                    $huruf = $this->getGradeLetter($nilai);
                    
                    $html .= '<tr>
                        <td class="text-center">' . $no++ . '</td>
                        <td>' . $m['nama_materi'] . '</td>
                        <td class="text-center">' . number_format($nilai, 0) . '</td>
                        <td class="text-center">' . $huruf . '</td>
                    </tr>';
                    
                    $groupTotal += $nilai;
                    $materiCount++;
                }

                 // Average Row for Group?
                 if ($materiCount > 0) {
                     $groupAvg = $groupTotal / $materiCount;
                     $html .= '<tr>
                        <td colspan="2" class="text-right font-bold">Rata-Rata</td>
                        <td class="text-center font-bold" style="background-color: #eee;">' . number_format($groupAvg, 1) . '</td>
                        <td class="text-center font-bold">' . $this->getGradeLetter($groupAvg) . '</td>
                    </tr>';
                 }
                
                $html .= '</tbody></table>';
                $html .= '</div>';
            }
        }
        
        return $html;
    }

    protected function getGradeLetter($nilai) {
        if ($nilai >= 85) return 'A';
        if ($nilai >= 75) return 'B';
        if ($nilai >= 60) return 'C';
        return 'D';
    }

    protected function terbilang($nilai) {
        $nilaiStr = number_format((float)$nilai, 1, '.', ''); // Ensure 1 decimal only
        $parts = explode('.', $nilaiStr);
        $integerPart = (int)$parts[0];
        
        // Use original logic for integer part
        $text = $this->terbilangInteger($integerPart);
        
        // Handle decimal part
        if (isset($parts[1]) && (int)$parts[1] > 0) {
             $decimals = str_split($parts[1]);
             $text .= " Koma";
             foreach($decimals as $d) {
                 $text .= " " . $this->terbilangInteger((int)$d);
             }
        }

        return trim($text);
    }

    protected function terbilangInteger($nilai) {
        $nilai = abs($nilai);
        $huruf = array("", "Satu", "Dua", "Tiga", "Empat", "Lima", "Enam", "Tujuh", "Delapan", "Sembilan", "Sepuluh", "Sebelas");
        $temp = "";
        if ($nilai < 12) {
            $temp = " ". $huruf[$nilai];
        } else if ($nilai <20) {
            $temp = $this->terbilangInteger($nilai - 10). " Belas";
        } else if ($nilai < 100) {
            $temp = $this->terbilangInteger((int)($nilai/10))." Puluh ". $this->terbilangInteger($nilai % 10);
        } else if ($nilai < 200) {
            $temp = " Seratus " . $this->terbilangInteger($nilai - 100);
        } else if ($nilai < 1000) {
            $temp = $this->terbilangInteger((int)($nilai/100)) . " Ratus " . $this->terbilangInteger($nilai % 100);
        }
        return trim($temp);
    }

    /**
     * Output PDF
     * @param string $filename
     * @param string $dest D=Download, I=Inline, F=File
     */
    public function output($filename = 'certificate.pdf', $dest = 'D')
    {
        $output = $this->dompdf->output();
        
        switch ($dest) {
            case 'D': // Download
                header('Content-Type: application/pdf');
                header('Content-Disposition: attachment; filename="' . $filename . '"');
                header('Cache-Control: private, max-age=0, must-revalidate');
                header('Pragma: public');
                echo $output;
                exit;
                
            case 'I': // Inline
                header('Content-Type: application/pdf');
                header('Content-Disposition: inline; filename="' . $filename . '"');
                echo $output;
                exit;
                
            case 'F': // File
                file_put_contents($filename, $output);
                return true;
                
            case 'S': // String (Return)
                return $output;
        }
    }

    /**
     * Stream PDF directly
     */
    public function stream($filename = 'certificate.pdf', $options = [])
    {
        if (ob_get_length()) {
            ob_end_clean();
        }

        if ($this->mergedPdfContent) {
            $attachment = $options['Attachment'] ?? true;
            
            header('Content-Type: application/pdf');
            header('Content-Length: ' . strlen($this->mergedPdfContent));
            if ($attachment) {
                header('Content-Disposition: attachment; filename="' . $filename . '"');
            } else {
                header('Content-Disposition: inline; filename="' . $filename . '"');
            }
            header('Cache-Control: private, max-age=0, must-revalidate');
            header('Pragma: public');
            
            echo $this->mergedPdfContent;
            exit;
        }
        
        $attachment = $options['Attachment'] ?? true;
        $this->dompdf->stream($filename, ['Attachment' => $attachment]);
        exit;
    }

    /**
     * Build HTML for PDF
     */
    protected function buildHtml()
    {
        // New schema: file_template
        $templatePath = FCPATH . 'uploads/' . $this->template['file_template'];
        // New schema: width, height
        $width = $this->template['width'];
        $height = $this->template['height'];

        // Convert image to base64 for embedding (Reliable for DomPDF)
        if (file_exists($templatePath)) {
            $imageData = base64_encode(file_get_contents($templatePath));
            $imageSrc = 'data:image/' . pathinfo($templatePath, PATHINFO_EXTENSION) . ';base64,' . $imageData;
        } else {
            // Fallback or error placeholder
            $imageSrc = ''; 
        }

        $html = '<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        @page { margin: 0; }
        body { margin: 0; padding: 0; font-family: Arial, sans-serif; width: 100%; height: 100%; overflow: hidden; }
        .certificate-container { position: relative; width: ' . $width . 'px; height: ' . $height . 'px; }
        .template-image { position: absolute; top: 0; left: 0; width: 100%; height: 100%; z-index: -1; }
        .field { position: absolute; white-space: nowrap; }
    </style>
</head>
<body>
    <div class="certificate-container">
        <img src="' . $imageSrc . '" class="template-image">
        ';

        // Add fields
        foreach ($this->fields as $field) {
            // New schema: field_name
            $value = $this->getFieldValue($field['field_name']);
            
            if ($value) {
                $style = $this->buildFieldStyle($field);
                
                // Special handling for QR Code or Image fields
                $isImage = false;
                $fieldName = $field['field_name'];
                
                // Allow dynamic image fields if they end with _qr or _image
                if (str_ends_with($fieldName, '_qr') || str_ends_with($fieldName, '_image') || $fieldName === 'foto_peserta' || $fieldName === 'qr_code') {
                    $isImage = true;
                }

                if ($isImage) {
                    // Check if value is a valid file path or base64
                    // Note: If using "qr_code", we expect $value to be a path to a generated QR image or DATA URI
                    
                    if (file_exists($value)) {
                         $ext = pathinfo($value, PATHINFO_EXTENSION);
                         $base64 = base64_encode(file_get_contents($value));
                         $mime = ($ext === 'svg') ? 'image/svg+xml' : 'image/' . $ext;
                         $src = "data:$mime;base64,$base64";
                         
                         // Use font_size as height reference
                         $imgHeight = (int)$field['font_size'];
                         $imgStyle = 'height: ' . $imgHeight . 'px; width: auto;';
                         
                         $html .= '<div class="field" style="' . $style . '"><img src="' . $src . '" style="' . $imgStyle . '"></div>';
                    } elseif (strpos($value, 'data:image') === 0) {
                         // It is already a data URI (e.g. generated dynamically)
                         $imgHeight = (int)$field['font_size'];
                         $imgStyle = 'height: ' . $imgHeight . 'px; width: auto;';
                         $html .= '<div class="field" style="' . $style . '"><img src="' . $value . '" style="' . $imgStyle . '"></div>';
                    }
                } else {
                    // Text - Check if border is enabled
                    // Parse border_settings JSON
                    $borderEnabled = false;
                    $borderWidth = 1;
                    $borderColor = '#000000';
                    
                    if (!empty($field['border_settings'])) {
                        $borderSettings = is_string($field['border_settings']) 
                            ? json_decode($field['border_settings'], true) 
                            : $field['border_settings'];
                        
                        if ($borderSettings && is_array($borderSettings)) {
                            // Strict boolean check - only true if explicitly set to true
                            $borderEnabled = isset($borderSettings['enabled']) && $borderSettings['enabled'] === true;
                            $borderColor = $borderSettings['color'] ?? '#000000';
                            $borderWidth = (int)($borderSettings['width'] ?? 1);
                        }
                    }
                    
                    if ($borderEnabled) {
                        // Layered approach: render border layers underneath main text
                        // Create container for layered text
                        $containerStyle = 'position: absolute; left: ' . $field['pos_x'] . 'px; top: ' . $field['pos_y'] . 'px;';
                        $html .= '<div style="' . $containerStyle . '">';
                        
                        // Generate smooth circular offsets (32 points for smoother border)
                        $offsets = [];
                        $numPoints = 32; // More points = smoother circle
                        for ($i = 0; $i < $numPoints; $i++) {
                            $angle = ($i / $numPoints) * 2 * M_PI;
                            $x = round($borderWidth * cos($angle), 2);
                            $y = round($borderWidth * sin($angle), 2);
                            $offsets[] = [$x, $y];
                        }
                        
                        // Build style without position (will be in container)
                        $textStyle = $this->buildFieldStyleWithoutPosition($field, $borderColor);
                        
                        // Render border layers
                        foreach ($offsets as $offset) {
                            $offsetStyle = 'position: absolute; left: ' . $offset[0] . 'px; top: ' . $offset[1] . 'px; ' . $textStyle;
                            $html .= '<div style="' . $offsetStyle . '">' . htmlspecialchars($value) . '</div>';
                        }
                        
                        // Render main text on top
                        $mainStyle = 'position: relative; ' . $textStyle;
                        $mainStyle = str_replace('color: ' . $borderColor, 'color: ' . $field['text_color'], $mainStyle);
                        $html .= '<div style="' . $mainStyle . '">' . htmlspecialchars($value) . '</div>';
                        
                        $html .= '</div>';
                    } else {
                        // Normal text without border
                        $html .= '<div class="field" style="' . $style . '">' . htmlspecialchars($value) . '</div>';
                    }
                }
            }
        }

        $html .= '
    </div>
</body>
</html>';

        return $html;
    }

    /**
     * Build CSS style for field
     */
    protected function buildFieldStyle($field)
    {
        $style = [];
        
        // New schema: pos_x, pos_y
        $style[] = 'left: ' . $field['pos_x'] . 'px';
        $style[] = 'top: ' . $field['pos_y'] . 'px';
        
        // Font Mapping
        $fontFamily = $this->getFontMapping($field['font_family']);
        $style[] = "font-family: {$fontFamily}";
        $style[] = 'font-size: ' . $field['font_size'] . 'px';
        
        // Font style
        if (strpos($field['font_style'], 'B') !== false) {
            $style[] = 'font-weight: bold';
        }
        if (strpos($field['font_style'], 'I') !== false) {
            $style[] = 'font-style: italic';
        }
        
        // Text align
        $textAlign = 'left';
        if ($field['text_align'] === 'C') {
            $textAlign = 'center';
            $style[] = 'transform: translateX(-50%)';
        } elseif ($field['text_align'] === 'R') {
            $textAlign = 'right';
            $style[] = 'transform: translateX(-100%)';
        }
        $style[] = 'text-align: ' . $textAlign;
        
        // Color
        $style[] = 'color: ' . $field['text_color'];

        // Border / Stroke logic is now handled in buildHtml() using Layered approach for better PDF compatibility
        // Legacy logic removed to prevent conflicts with stale data
        
        // Max width
        if ($field['max_width'] > 0) {
            $style[] = 'max-width: ' . $field['max_width'] . 'px';
        }
        
        return implode('; ', $style);
    }

    /**
     * Build CSS style for field without position (for layered rendering)
     */
    protected function buildFieldStyleWithoutPosition($field, $overrideColor = null)
    {
        $style = [];
        
        // Font Mapping
        $fontFamily = $this->getFontMapping($field['font_family']);
        $style[] = "font-family: {$fontFamily}";
        $style[] = 'font-size: ' . $field['font_size'] . 'px';
        
        // Font style
        if (strpos($field['font_style'], 'B') !== false) {
            $style[] = 'font-weight: bold';
        }
        if (strpos($field['font_style'], 'I') !== false) {
            $style[] = 'font-style: italic';
        }
        
        // Text align
        $textAlign = 'left';
        if ($field['text_align'] === 'C') {
            $textAlign = 'center';
            $style[] = 'transform: translateX(-50%)';
        } elseif ($field['text_align'] === 'R') {
            $textAlign = 'right';
            $style[] = 'transform: translateX(-100%)';
        }
        $style[] = 'text-align: ' . $textAlign;
        
        // Color (use override if provided, otherwise use field color)
        $color = $overrideColor ?? $field['text_color'];
        $style[] = 'color: ' . $color;
        
        // Max width
        if ($field['max_width'] > 0) {
            $style[] = 'max-width: ' . $field['max_width'] . 'px';
        }
        
        // White-space
        $style[] = 'white-space: nowrap';
        
        return implode('; ', $style);
    }

    /**
     * Map friendly font names
     */
    protected function getFontMapping($friendlyName)
    {
        $map = [
            'Arial' => "'Helvetica', sans-serif",
            'Times New Roman' => "'Times-Roman', serif",
            'Courier New' => "'Courier', monospace",
            'Dejavu Sans' => "'DejaVu Sans', sans-serif",
        ];

        return $map[$friendlyName] ?? "'Helvetica', sans-serif";
    }

    /**
     * Get field value from data
     */
    protected function getFieldValue($fieldName)
    {
        return $this->data[$fieldName] ?? '';
    }
}
