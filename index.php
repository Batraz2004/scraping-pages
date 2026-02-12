<?php

require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/App/Services/Scraping.php';

use App\Services\Scraping;

$url = 'https://books.toscrape.com/';

$scrapingObject = new Scraping(new \GuzzleHttp\Client());

$result = $scrapingObject->procces($url);

header('Content-Type: application/json; charset=utf-8');

echo json_encode($result);

return json_encode($result);
