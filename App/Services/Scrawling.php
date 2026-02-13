<?php

namespace App\Services;

use Spatie\Crawler\Crawler;

class Scrawling
{
    public function __construct(public \GuzzleHttp\Client $httpClient, public PageScrawlerObserver $pageObserver) {}

    public function proccess($url)
    {
        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            echo "не корректный url";
            return;
        }

        $pageObserver = $this->pageObserver;

        Crawler::create()
            ->setCrawlObserver($pageObserver)
            ->startCrawling($url);

        return $pageObserver;
    }
}
