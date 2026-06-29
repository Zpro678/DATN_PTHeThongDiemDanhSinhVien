<?php
require 'vendor/autoload.php';
$spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load('storage/app/private/public/test_export2.xlsx');
$worksheet = $spreadsheet->getActiveSheet();
$rows = [];
$i = 0;
foreach ($worksheet->getRowIterator() as $row) {
    if ($i++ > 7) break;
    $cellIterator = $row->getCellIterator();
    $cellIterator->setIterateOnlyExistingCells(false);
    $data = [];
    foreach ($cellIterator as $cell) {
        $data[] = $cell->getValue();
    }
    echo implode(' | ', $data) . "\n";
}
