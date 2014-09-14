<?php
$start = microtime(true);

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'http://thinkphp.com.ua/');
curl_setopt($ch, CURLOPT_USERAGENT, 'thinkPHP Bot v1, made by P@vel Polyak0v');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

$content = curl_exec($ch);

// get all the content between the h2 tags
preg_match_all("/<h2>(.*?)<\/h2>/si", $content, $matches);

$eventsHeaders = [];
// iterating throught the content
foreach ($matches[1] as $match) {
    // removing the date, if it's present
    $match = preg_replace('/<span>(.*?)<\/span>/si', '', $match);

    // trying to find the href
    preg_match('/href="(.*?)"/si', $match, $hrefMatches);

    // if href is there, let's assign it
    $link = isset($hrefMatches[1]) ? $hrefMatches[1] : null;

    $eventsHeaders[] = [
        'title' => strip_tags($match),
        'link' => $link
    ];
}

$finish = microtime(true);

header('Content-Type: text/plain; charset=utf-8');
var_dump($eventsHeaders);

echo "\n\nIt took: " . round($finish - $start, 4) . " seconds";

