<?php

namespace App\Services;

use GuzzleHttp\Exception\RequestException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\UriInterface;
use Spatie\Crawler\CrawlObservers\CrawlObserver;

class PageСrawlerObserver extends CrawlObserver
{
    private array $pageUrls = [];
    private array $failedUrls = [];

    public function getPageUrls(): array
    {
        return $this->pageUrls;
    }

    public function getFailUrls(): array
    {
        return $this->failedUrls;
    }

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

        //избежать получение картин        
        $urlExtension = pathinfo($url->__toString(), PATHINFO_EXTENSION);
        $exceptionUrlEnds = ['jpg', 'jpeg', 'png', 'gif', 'svg', 'webp', 'bmp', 'ico'];

        if (in_array($urlExtension, $exceptionUrlEnds)) {
            return;
        };

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
        // echo $failMessage . "<br>";

        $failMessage = "не удалось получить страницу:{$url}\n\r";
        file_put_contents('App/Logs/Crawling.log', $failMessage, FILE_APPEND);
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
