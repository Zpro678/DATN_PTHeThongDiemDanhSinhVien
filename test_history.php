<?php
$html = file_get_contents('resources/views/livewire/student/attendance-history.blade.php');
$html = preg_replace('/<script\b[^>]*>.*?<\/script>/si', '', $html);
$html = preg_replace('/<style\b[^>]*>.*?<\/style>/si', '', $html);

// Remove blade comments
$html = preg_replace('/{{--.*?--}}/s', '', $html);
// Remove @php ... @endphp
$html = preg_replace('/@php.*?@endphp/s', '', $html);
// Remove other blade directives
$html = preg_replace('/@(?:if|foreach|endif|endforeach|class|empty|forelse|endforelse).*?(\n|\r\n|$)/s', '', $html);

$dom = new DOMDocument();
@$dom->loadHTML('<?xml encoding="utf-8" ?>' . $html, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
$count = 0;
foreach ($dom->childNodes as $node) {
    if ($node->nodeType === XML_ELEMENT_NODE && $node->tagName !== '?xml') {
        $count++;
        echo 'Root: ' . $node->tagName . ' => ' . substr($dom->saveHTML($node), 0, 50) . PHP_EOL;
    }
}
echo 'Root count: ' . $count . PHP_EOL;
