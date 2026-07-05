<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$headers = [
    0 => 'Họ và tên',
    1 => 'Email',
    2 => '02/10'
];

$emailColIndex = -1;
$nameColIndex = -1;
$codeColIndex = -1;

foreach ($headers as $colIndex => $colValue) {
    $colValueLower = mb_strtolower(trim((string) $colValue));
    if (str_contains($colValueLower, 'email')) {
        $emailColIndex = $colIndex;
        continue;
    }
    if (str_contains($colValueLower, 'họ và tên') || str_contains($colValueLower, 'họ tên') || $colValueLower === 'tên' || $colValueLower === 'ten' || $colValueLower === 'name' || $colValueLower === 'full name' || $colValueLower === 'fullname') {
        $nameColIndex = $colIndex;
        continue;
    }
    if (str_contains($colValueLower, 'mã') || str_contains($colValueLower, 'mssv') || str_contains($colValueLower, 'ms') || str_contains($colValueLower, 'stt')) {
        $codeColIndex = $colIndex;
        continue;
    }
}

echo "emailColIndex: $emailColIndex\n";
echo "nameColIndex: $nameColIndex\n";
echo "codeColIndex: $codeColIndex\n";

$rows = [
    [0 => 'Sinh viên 1', 1 => 'sv001@email.com', 2 => 'c']
];

foreach ($rows as $row) {
    $studentCode = $codeColIndex !== -1 ? trim((string) ($row[$codeColIndex] ?? '')) : null;
    $fullName = $nameColIndex !== -1 ? trim((string) ($row[$nameColIndex] ?? '')) : null;
    $email = $emailColIndex !== -1 ? trim((string) ($row[$emailColIndex] ?? '')) : null;

    echo "studentCode: $studentCode\n";
    echo "fullName: $fullName\n";
    echo "email: $email\n";
}
