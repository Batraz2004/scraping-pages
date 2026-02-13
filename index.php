<?php

require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/App/Services/Scraping.php';
require __DIR__ . '/App/Services/Scrawling.php';
require __DIR__ . '/App/Services/PageScrawlerObserver.php';

use App\Services\PageScrawlerObserver;
use App\Services\Scraping;
use App\Services\Scrawling;
use GuzzleHttp\Client; //from autoload.php

try {
    //настройка базовых переменных
    $url = 'https://books.toscrape.com/';

    $scrawlingObserver = new PageScrawlerObserver;
    $scrawlingObj = new Scrawling(new Client, $scrawlingObserver);

    $errorScrapingLogsFilePath = 'App/Logs/Scraping.Log';
    $dataWordsFilePath = 'App/Data/wordsByScraping.json';

    file_put_contents($dataWordsFilePath, "");

    //обход и сбор всех страниц сайта по указанному url
    /** @var PageScrawlerObserver $pageObserverBeScrawling */
    $pageObserverBeScrawling = $scrawlingObj->proccess($url);
    $pageUrlsByScrawling = $pageObserverBeScrawling->pageUrls;

    $resultByScraping = [];

    //парсинг по всем страницам
    foreach ($pageUrlsByScrawling['urls'] as $key => $url) {
        try {
            $scrapingObject = new Scraping(new Client);

            $resultByScraping[] = $scrapingObject->procces($url);

            echo $key . "\n\r";

            // file_put_contents($dataWordsFilePath, json_encode($resultByScraping), FILE_APPEND);
        } catch (Throwable $th) {
            $message = "не удалось спарсить страницу: {$url} под номером {$key} , ошибка:{$ex->getMessage()}. код:{$ex->getCode()} в файле:{$ex->getFile()} на строке: {$ex->getLine()} \n";
            file_put_contents($errorScrapingLogsFilePath, $message, FILE_APPEND);
        }
    }

    header('Content-Type: application/json; charset=utf-8');

    echo json_encode($resultByScraping);

    return json_encode($resultByScraping);
} catch (Throwable $ex) {
    echo "ошибка:{$ex->getMessage()}. код:{$ex->getCode()} в файле:{$ex->getFile()} на строке: {$ex->getLine()}";
}
