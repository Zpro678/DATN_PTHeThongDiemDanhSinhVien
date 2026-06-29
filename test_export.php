<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$export = new \App\Exports\StudentsExport(1, '4', 'active', '', '(c+m)/t*100');
\Maatwebsite\Excel\Facades\Excel::store($export, 'public/test_export.xlsx');
echo "Done\n";
