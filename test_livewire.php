<?php
$html = file_get_contents('resources/views/livewire/lecturer/class-show.blade.php');
// Simulate Livewire's regex
$html = preg_replace('/<script\b[^>]*>.*?<\/script>/si', '', $html);
$html = preg_replace('/<style\b[^>]*>.*?<\/style>/si', '', $html);

$dom = new DOMDocument();
@$dom->loadHTML('<?xml encoding="utf-8" ?>' . $html, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
$count = 0;
foreach ($dom->childNodes as $node) {
    if ($node->nodeType === XML_ELEMENT_NODE && $node->tagName !== '?xml') {
        $count++;
        echo 'Root: ' . $node->tagName . PHP_EOL;
    }
}
echo 'Root count: ' . $count . PHP_EOL;
