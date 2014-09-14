<?php

$input = ['name'=>'Тимошенко Юлія Володимирівна'];
$input = json_encode($input, JSON_UNESCAPED_UNICODE);

// you should state here the full path to the phantomjs
$cmd = "/usr/local/bin/phantomjs ".__DIR__."/irc-gov-ua.js '{$input}'";
$result = shell_exec($cmd);

header('Content-Type: text/plain; charset=utf-8');
echo $cmd."\n\n";

var_dump(json_decode($result));
