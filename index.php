<?php

require __DIR__ . '/vendor/autoload.php';

use App\Services\Crawling;
use App\Services\PageCrawlerObserver;
use App\Services\Scraping;
use GuzzleHttp\Client; //from autoload.php

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
        throw new Exception('Method Not Allowed', 405);
    }

    //настройка базовых переменных
    ignore_user_abort(true);
    set_time_limit(0);

    $url = $_GET['url'];

    $crawlingObserver = new PageCrawlerObserver;
    $crawlingObj = new Crawling(new Client, $crawlingObserver);

    //обход и сбор всех страниц сайта по указанному url
    /** @var PageCrawlerObserver $pageObserverBeCrawling */
    $pageObserverBeCrawling = $crawlingObj->proccess($url);
    $pageSuccesUrlsByCrawling = $pageObserverBeCrawling->getPageUrls();
    $pageFailUrlsByCrawling = $pageObserverBeCrawling->getFailUrls();

    //парсинг собранных страниц
    $resultByScraping = [];
    $pageFailUrlsByScraping = [];

    $scrapingObject = new Scraping(new Client);

    $resultByScraping[] = $scrapingObject->procces($pageSuccesUrlsByCrawling['urls'], $pageFailUrlsByScraping);

    //формирование названия файла
    $dataWordsFilePath = 'App/Data/';
    $name = str_replace(['https://', 'http://', '/'], '', $_GET['url']);
    $time = date("Y-m-d-h-i-s");
    $resultByScrapingPath = $dataWordsFilePath . "{$name}_{$time}.json";

    file_put_contents($resultByScrapingPath, [json_encode($resultByScraping)]);

    header('Content-Type: application/json; charset=utf-8');

    echo json_encode([
        'data'      => $resultByScraping,
        'couldnt_recieve_urls' => $pageFailUrlsByCrawling, //страницы которые не удалось спарсить
        'fail_urls' => $pageFailUrlsByScraping, //страницы которые не удалось получить
    ]);
} catch (Throwable $ex) {
    echo "ошибка:{$ex->getMessage()}. код:{$ex->getCode()} в файле:{$ex->getFile()} на строке: {$ex->getLine()}";
}
