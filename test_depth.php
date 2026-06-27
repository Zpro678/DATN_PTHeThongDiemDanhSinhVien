<?php
$html = file_get_contents('resources/views/livewire/student/attendance-history.blade.php');
$html = preg_replace('/{{--.*?--}}/s', '', $html);
$lines = explode("\n", $html);
$depth = 0;
foreach ($lines as $i => $line) {
    $open = substr_count($line, '<div');
    $close = substr_count($line, '</div');
    $depth += $open - $close;
    if ($depth < 0) {
        echo 'Negative depth at line ' . ($i + 1) . PHP_EOL;
        echo $line . PHP_EOL;
        break;
    }
}
