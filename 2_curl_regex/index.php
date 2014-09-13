<?php
$start = microtime(true);

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'http://thinkphp.com.ua/');
curl_setopt($ch, CURLOPT_USERAGENT, 'thinkPHP Bot v1, made by P@vel Polyak0v');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

$content = curl_exec($ch);

preg_match_all("/<h2>(.*?)<\/h2>/si", $content, $matches);

$eventsHeaders = [];
foreach($matches[1] as $match) {
    $eventsHeaders[] = strip_tags($match);
}

$finish = microtime(true);

header('Content-Type: text/plain; charset=utf-8');
var_dump($eventsHeaders);

echo "\n\nIt took: ".round($finish - $start, 4)." seconds";

