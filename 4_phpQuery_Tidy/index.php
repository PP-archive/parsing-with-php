<?php

require 'vendor/autoload.php';

/**
 * http client for interactions with thinkPHP website
 */
class ThinkPHPHttpClient {

    protected $_baseUrl = 'http://thinkphp.com.ua/';
    protected $_userAgent = 'Mozilla/5.0 (Windows NT 6.3; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/37.0.2049.0 Safari/537.36';

    /**
     * receive the index page of the thinkPHP website
     * 
     * @return string
     */
    public function getIndexContent() {
        // receiving the content of the index page
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->_baseUrl);
        curl_setopt($ch, CURLOPT_USERAGENT, $this->_userAgent);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $content = curl_exec($ch);

        // fix the dom tree
        $content = $this->_tidyFix($content);

        return $content;
    }

    /**
     * receive the html content, fix/format the dom tree and return it
     * 
     * @param string $content
     * @return string
     */
    protected function _tidyFix($content) {
        $config = ['input-xml' => true,
            'output-xml' => true,
            'wrap' => false];

        $tidy = new Tidy();
        $tidy->parseString($content, $config, 'utf8');
        $tidy->cleanRepair();

        $content = (string) $tidy;

        return $content;
    }

}

$start = microtime(true);

// create the client
$thinkPhpHttpClient = new ThinkPHPHttpClient();

// receive the content
$content = $thinkPhpHttpClient->getIndexContent();

// load the content to the phpQuery library
phpQuery::newDocument($content);

$eventsHeaders = [];

// iterating over the h2 tags
foreach (pq('h2') as $title) {
    // removing the span if present
    pq($title)->find('span')->remove();

    if (pq($title)->find('a')->is('*')) {
        $link = pq($title)->find('a')->attr('href');
    } else {
        $link = null;
    }

    $eventsHeaders[] = [
        'title' => trim(pq($title)->text()),
        'link' => $link
    ];
}

$finish = microtime(true);

header('Content-Type: text/plain; charset=utf-8');
var_dump($eventsHeaders);

echo "\n\nIt took: " . round($finish - $start, 4) . " seconds";
