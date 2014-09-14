<?php
$start = microtime(true);

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'http://thinkphp.com.ua/');
// we want to pretend the Chrome
curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.3; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/37.0.2049.0 Safari/537.36');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

$content = curl_exec($ch);

// fix the document, as it's xml
$tidy = new Tidy();
$tidy->parseString($content, ['input-xml' => true,
    'output-xml' => true,
    'wrap' => false], 'utf8');
$tidy->cleanRepair();

$content = (string) $tidy;

// load the string as simplexml object
$xml = simplexml_load_string($content);

// registering the namespace, so we can search
$xml->registerXPathNamespace('xmlns', 'http://www.w3.org/1999/xhtml');

$eventsHeaders = [];
foreach ($xml->xpath('//xmlns:h2') as $node) {
    // remove if present
    unset($node->span);

    // if the href is there, let's parse it
    if (isset($node->a['href'])) {
        $link = (string) $node->a['href'];
    } else {
        $link = null;
    }

    $eventsHeaders[] = [
        'title' => trim(strip_tags($node->asXml())),
        'link' => $link
    ];
}

$finish = microtime(true);

header('Content-Type: text/plain; charset=utf-8');
var_dump($eventsHeaders);

echo "\n\nIt took: " . round($finish - $start, 4) . " seconds";

