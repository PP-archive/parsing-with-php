<?php
$start = microtime(true);

// get the website content
$content = file_get_contents('http://thinkphp.com.ua/');

// looks like  all needed headers are in the h2 tags
$events = explode("</h2>", $content);

$eventHeaders = [];

foreach ($events as $event) {

    $tmp = explode("<h2>", $event);

    // some notice appears during the iterations, I don't like it
    $spanStart = @strpos($tmp[1], "<span>");

    // if span is there - adjust the header variable
    if ($spanStart) {
        $header = @substr($tmp[1], 0, $spanStart);
    } else {
        //I don't like the notice here as well
        $header = @$tmp[1];
    }

    // searching for the href
    if (strpos($header, 'href')) {
        $link = substr($header, strpos($header, 'href="') + 6 /* length of the href=" */, strpos($header, '">') - 9 /* length of the <a href=" */);
    } else {
        $link = null;
    }

    $eventsHeaders[] = [
        'title' => strip_tags($header),
        'link' => $link
    ];
}

// the last element is empty, I don't need it
unset($eventsHeaders[count($eventsHeaders) - 1]);

$finish = microtime(true);

header('Content-Type: text/plain; charset=utf-8');
var_dump($eventsHeaders);

echo "\n\nIt took: " . round($finish - $start, 4) . " seconds";
