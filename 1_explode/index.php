<?php
$start = microtime(true);

// get the website content
$content = file_get_contents('http://thinkphp.com.ua/');

// looks like  all needed headers are in the h2 tags
$events = explode("</h2>", $content);

$eventHeaders = [];

foreach($events as $event) {
    
    $tmp = explode("<h2>", $event);
    
    // some notice appears during the iterations, I don't like it
    $eventsHeaders[] = @strip_tags($tmp[1]);
}

// the last element is empty, I don't need it
unset($eventsHeaders[count($eventsHeaders)-1]);

$finish = microtime(true);

header('Content-Type: text/plain; charset=utf-8');
var_dump($eventsHeaders);

echo "\n\nIt took: ".round($finish - $start, 4)." seconds";