<?php

namespace App\Helpers;

use Dompdf\Dompdf;
use Dompdf\Options;

class CertificateGenerator
{
    protected $template;
    protected $fields;
    protected $data;
    protected $dompdf;

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
        $htmlFront = $this->buildHtml(); // Front Page HTML (using current template/fields)
        $htmlBack = $this->buildBackPageHtml($backTemplate, $backFields, $scoreData); // Back Page HTML

        // Combine HTML with Page Break
        $html = '<html><head><style>
            @page { margin: 0; }
            body { margin: 0; padding: 0; font-family: Arial, sans-serif; }
            .page-break { page-break-before: always; }
            .certificate-container { position: relative; width: 100%; height: 100%; overflow: hidden; }
            .template-image { position: absolute; top: 0; left: 0; width: 100%; height: 100%; z-index: -1; }
            .field { position: absolute; white-space: nowrap; }
            
            /* Table Styles for Back Page */
            .score-table {
                border-collapse: collapse;
            }
            .score-table th, .score-table td {
                border: 1px solid #000;
                padding: 10px;
            }
            .score-table th {
                background-color: #f0f0f0;
                text-align: center;
                font-weight: bold;
            }
            .text-center { text-align: center; }
            .text-right { text-align: right; }
            .font-bold { font-weight: bold; }
        </style></head><body>';

        // Page 1
        $html .= $this->getContainerHtml($this->template, $htmlFront, false);
        
        // Page 2
        $html .= '<div class="page-break"></div>';
        $html .= $this->getContainerHtml($backTemplate, $htmlBack, true);

        $html .= '</body></html>';

        $this->dompdf->loadHtml($html);
        $this->setPaper();
        $this->dompdf->render();

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
        // Sort fields by ID or order if available, though array order is usually fine
        
        $html = '';

        foreach ($fields as $field) {
             // Handle Table Block
            if ($field['field_name'] == 'block_table') {
                 // Table position
                $tableTop = $field['pos_y'];
                $tableLeft = $field['pos_x'];
                
                // Calculate width: default to full width minus margins, or use logic
                // If aligned Center, spread it? 
                // Let's rely on standard X position. If X=0, full width?
                // Better: Use a fixed width or calculate dynamic width. 
                // For now, let's use: Width = TemplateWidth - (2 * X) if X < TemplateWidth/2
                // Or just use a standard 80% width centered if X is small.
                
                // Simplified Logic from previous iteration, but tailored to dynamic placement
                 $tableWidth = $template['width'] - ($tableLeft * 2);
                 if ($tableWidth < 200) $tableWidth = $template['width'] - 100; // Fallback

                $html .= '<table class="score-table" style="position: absolute; top: ' . $tableTop . 'px; left: ' . $tableLeft . 'px; width: ' . $tableWidth . 'px; font-size: ' . $field['font_size'] . 'px;">
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
            // Need to map field_name to scoreData keys.
            // Note: scoreData contains mapped data from CetakSertifikat
            
            $text = isset($scoreData[$field['field_name']]) ? $scoreData[$field['field_name']] : '{'.$field['field_name'].'}';
            
            // Generate Style
             $style = "position: absolute;";
            $style .= "left: {$field['pos_x']}px;";
            $style .= "top: {$field['pos_y']}px;";
            $style .= "font-family: {$field['font_family']};";
            $style .= "font-size: {$field['font_size']}px;";
            $style .= "color: {$field['text_color']};";
            
            if ($field['font_style'] == 'B') $style .= "font-weight: bold;";
            if ($field['font_style'] == 'I') $style .= "font-style: italic;";
            if ($field['font_style'] == 'U') $style .= "text-decoration: underline;";

            // Alignment (Canvas centers text at X if align=C. CSS needs adjustment)
            if ($field['text_align'] == 'C') {
                $style .= "transform: translateX(-50%);"; 
            } elseif ($field['text_align'] == 'R') {
                 $style .= "transform: translateX(-100%);";
            }
            
            $html .= "<div style=\"{$style}\">{$text}</div>";
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
                        
                        if ($borderSettings) {
                            $borderEnabled = $borderSettings['enabled'] ?? false;
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

        // Border / Stroke (Text Outline)
        if (!empty($field['has_border']) && $field['has_border']) {
            $borderWidth = (int)($field['border_width'] ?? 1);
            $borderColor = $field['border_color'] ?? '#000000';
            
            // 1. Try standard/webkit stroke
            $style[] = "-webkit-text-stroke: " . ($borderWidth) . "px " . $borderColor;
            
            // 2. Add text-shadow emulation (Backup for PDF engines that ignore stroke)
            // Simulating stroke with multiple shadows
            $shadows = [];
            // Use 8 points for > 1px to ensure coverage
            if ($borderWidth > 0) {
                 $w = $borderWidth;
                 // Cardinals
                 $shadows[] = "$w" . "px 0 0 $borderColor";
                 $shadows[] = "-$w" . "px 0 0 $borderColor";
                 $shadows[] = "0 $w" . "px 0 $borderColor";
                 $shadows[] = "0 -$w" . "px 0 $borderColor";
                 
                 // Diagonals for smoother look if width > 1
                 if ($w > 1) {
                     $d = number_format($w * 0.707, 1, '.', ''); // sin(45) approx
                     $shadows[] = "$d" . "px $d" . "px 0 $borderColor";
                     $shadows[] = "$d" . "px -$d" . "px 0 $borderColor";
                     $shadows[] = "-$d" . "px $d" . "px 0 $borderColor";
                     $shadows[] = "-$d" . "px -$d" . "px 0 $borderColor";
                 }
            }
            if (!empty($shadows)) {
                $style[] = "text-shadow: " . implode(', ', $shadows);
            }
        }
        
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
