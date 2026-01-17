<?php
require 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

try {
    $inputFileType = IOFactory::identify('dapodik_sample.xlsx');
    $reader = IOFactory::createReader($inputFileType);
    $reader->setReadDataOnly(true);
    $spreadsheet = $reader->load('dapodik_sample.xlsx');
    $sheet = $spreadsheet->getActiveSheet();
    
    // Read row 5 (potential headers)
    $headers = [];
    foreach ($sheet->getRowIterator(5, 5) as $row) {
        $cellIterator = $row->getCellIterator();
        $cellIterator->setIterateOnlyExistingCells(false);
        foreach ($cellIterator as $cell) {
            $headers[$cell->getColumn()] = $cell->getValue();
        }
    }
    
    // Read row 6 for data
    $firstData = [];
    foreach ($sheet->getRowIterator(6, 6) as $row) {
        $cellIterator = $row->getCellIterator();
        $cellIterator->setIterateOnlyExistingCells(false);
        foreach ($cellIterator as $cell) {
            $firstData[$cell->getColumn()] = $cell->getValue();
        }
    }

    echo "HEADERS:\n";
    print_r($headers);
    echo "\nFIRST ROW DATA:\n";
    print_r($firstData);

} catch (Throwable $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
