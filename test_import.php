<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$import = new \App\Imports\StudentsImport(4);
\Maatwebsite\Excel\Facades\Excel::import($import, 'public/test_30_buoi.csv');
echo "Success count: " . $import->successCount . "\n";
echo "Errors:\n";
print_r($import->errors);
