<?php

namespace App\Services;

use GuzzleHttp\Exception\RequestException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\UriInterface;
use Spatie\Crawler\CrawlObservers\CrawlObserver;

class PageScrawlerObserver extends CrawlObserver
{
    /*
     * Called when the crawler will crawl the url.
     */
    public function willCrawl(UriInterface $url, ?string $linkText): void
    {
        echo $url."<br>";
    }

    /*
     * Called when the crawler has crawled the given url successfully.
     */
    public function crawled(
        UriInterface $url,
        ResponseInterface $response,
        ?UriInterface $foundOnUrl = null,
        ?string $linkText = null,
    ): void {
        echo $url."<br>";
    }

    /*
     * Called when the crawler had a problem crawling the given url.
     */
    public function crawlFailed(
        UriInterface $url,
        RequestException $requestException,
        ?UriInterface $foundOnUrl = null,
        ?string $linkText = null,
    ): void {
        echo $url."<br>";
    }

    /*
     * Called when the crawl has ended.
     */
    public function finishedCrawling(): void {
        echo "<br>finish crawling!";
    }
}
