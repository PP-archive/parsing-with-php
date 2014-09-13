<?php
// get the website content
$content = file_get_contents('http://thinkphp.com.ua/');

// looks like  all needed headers are in the h2 tags
$oldEvents = explode("</h2>", $content);

$oldEventHeaders = [];

foreach($oldEvents as $oldEvent) {
    
    $tmp = explode("<h2>", $oldEvent);
    
    // some notice appears during the iterations, I don't like it
    $oldEventHeaders[] = @strip_tags($tmp[1]);
}

// the last element is empty, I don't need it
unset($oldEventHeaders[count($oldEventHeaders)-1]);

var_dump($oldEventHeaders);