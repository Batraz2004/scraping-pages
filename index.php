<?php

require __DIR__ . '/vendor/autoload.php';

use App\Services\Crawling;
use App\Services\PageСrawlerObserver;
use App\Services\Scraping;
use GuzzleHttp\Client; //from autoload.php

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
        throw new Exception('Method Not Allowed', 405);
    }

    //настройка базовых переменных
    $url = $_GET['url'];

    $scrawlingObserver = new PageСrawlerObserver();
    $scrawlingObj = new Crawling(new Client, $scrawlingObserver);

    $errorScrapingLogsFilePath = 'App/Logs/Scraping.Log';

    //обход и сбор всех страниц сайта по указанному url
    /** @var PageСrawlerObserver $pageObserverBeScrawling */
    $pageObserverBeScrawling = $scrawlingObj->proccess($url);
    $pageUrlsByScrawling = $pageObserverBeScrawling->pageUrls;

    $resultByScraping = [];

    //парсинг по всем страницам
    foreach ($pageUrlsByScrawling['urls'] as $key => $url) {
        try {
            $scrapingObject = new Scraping(new Client);

            $resultByScraping[$url] = $scrapingObject->procces($url);
        } catch (Throwable $th) {
            $message = "не удалось спарсить страницу: {$url} под номером {$key} , ошибка:{$ex->getMessage()}. код:{$ex->getCode()} в файле:{$ex->getFile()} на строке: {$ex->getLine()} \n";
            file_put_contents($errorScrapingLogsFilePath, $message, FILE_APPEND);
        }
    }

    $dataWordsFilePath = 'App/Data/';
    $name = str_replace(['https://', 'http://'], '', $_GET['url']);
    $time = date("Y-m-d_h:i:s");
    $resultByScrapingPath = $dataWordsFilePath . "{$name}_{$time}.json";

    file_put_contents($resultByScrapingPath, [json_encode($resultByScraping)]);

    header('Content-Type: application/json; charset=utf-8');

    echo json_encode(['data' => $resultByScraping]);
} catch (Throwable $ex) {
    echo "ошибка:{$ex->getMessage()}. код:{$ex->getCode()} в файле:{$ex->getFile()} на строке: {$ex->getLine()}";
}
