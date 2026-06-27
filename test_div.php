<?php
$html = file_get_contents('resources/views/livewire/student/attendance-history.blade.php');
$html = preg_replace('/{{--.*?--}}/s', '', $html);
$openCount = substr_count($html, '<div');
$closeCount = substr_count($html, '</div');
echo 'Open: ' . $openCount . PHP_EOL;
echo 'Close: ' . $closeCount . PHP_EOL;
