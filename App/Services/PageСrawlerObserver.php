<?php

namespace App\Services;

use GuzzleHttp\Exception\RequestException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\UriInterface;
use Spatie\Crawler\CrawlObservers\CrawlObserver;

class PageСrawlerObserver extends CrawlObserver
{
    public function __construct(public ?array $pageUrls = null) {}

    /*
     * Called when the crawler will crawl the url.
     */
    public function willCrawl(UriInterface $url, ?string $linkText): void
    {
        // echo "crawling process:" . $url . "<br>";
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
        // echo "crawling succes:" . $url . "<br>";
        $this->pageUrls['urls'][] = $url->__toString();
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
        $failMessage = "failed with url:{$url}\n\r";
        file_put_contents('App/Logs/Crawling.log', $failMessage, FILE_APPEND);
        // echo $failMessage . "<br>";
    }

    /*
     * Called when the crawl has ended.
     */
    public function finishedCrawling(): void
    {
        // echo "<br>finish crawling!";
        file_put_contents('App/Data/siteUrlsByCrawling.json', json_encode($this->pageUrls));
    }
}
