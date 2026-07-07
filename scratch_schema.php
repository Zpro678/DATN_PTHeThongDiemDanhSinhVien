<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\Schema;

$tables = Schema::getTables();
$output = "# Tài liệu Cấu trúc Cơ sở dữ liệu\n\n";

foreach($tables as $tbl) {
    $table = $tbl['name'];
    if (in_array($table, ['migrations', 'password_reset_tokens', 'failed_jobs', 'personal_access_tokens', 'cache', 'cache_locks', 'jobs', 'job_batches'])) continue;
    $output .= '### Bảng: `' . $table . "`\n";
    $output .= "**Dùng để:** [Mô tả]\n\n";
    $output .= '| Tên thuộc tính | Kiểu dữ liệu | Dùng làm gì | Sử dụng ở đâu |' . "\n";
    $output .= '| --- | --- | --- | --- |' . "\n";
    
    $columns = Schema::getColumns($table);
    foreach($columns as $col) {
        $output .= '| `' . $col['name'] . '` | `' . $col['type_name'] . '` | [Mô tả] | [Mô tả] |' . "\n";
    }
    $output .= "\n";
}

file_put_contents(__DIR__ . '/schema_dump.md', $output);
echo "Done!";
