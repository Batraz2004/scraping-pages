<?php

require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/App/Services/Scraping.php';

use App\Services\Scraping;

header('Content-Type: application/json; charset=utf-8');

$url = 'https://books.toscrape.com/';
$scrapingObject = new Scraping;
$result = $scrapingObject->procces($url);
echo json_encode($result);
return json_encode($result);
