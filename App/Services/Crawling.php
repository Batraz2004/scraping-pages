<?php

namespace App\Services;

use Exception;
use Spatie\Crawler\Crawler;
use Spatie\Crawler\CrawlObservers\CrawlObserver;
use Spatie\Crawler\CrawlProfiles\CrawlInternalUrls;

class Crawling
{
    public function __construct(public \GuzzleHttp\Client $httpClient, public PageCrawlerObserver $pageObserver) {}

    public function proccess($url): CrawlObserver
    {
        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            echo "не корректный url";
            throw new Exception("не корректный url", 422);
        }

        $dataFilePath = 'App/Data/siteUrlsByCrawling.json';
        if (file_exists($dataFilePath)) {
            file_put_contents($dataFilePath, "");
        }

        $pageObserver = $this->pageObserver;

        Crawler::create()
            ->setCrawlObserver($pageObserver)
            ->setCrawlProfile(new CrawlInternalUrls($url))//что бы не переходить на ссылки переходящие на другой сайт
            ->startCrawling($url);

        return $pageObserver;
    }
}
