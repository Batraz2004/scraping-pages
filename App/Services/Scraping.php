<?php

namespace App\Services;

use DOMDocument;
use DOMXPath;
use Exception;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Pool;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;

class Scraping
{
    public function __construct(public \GuzzleHttp\Client $httpClient) {}

    public function procces($urls, &$pageFailUrlsByScraping): array
    {
        $requests = function () use ($urls) {
            foreach ($urls as $url) {
                yield new Request('GET', $url);
            }
        };

        $extractedWords = [];
        $errorScrapingLogsFilePath = 'App/Logs/Scraping.Log';

        $pool = new Pool($this->httpClient, $requests(), [
            'concurrency' => 10,
            'fulfilled' => function (Response $response, $index) use ($urls, &$extractedWords) {
                $htmlString = (string)$response->getBody();

                //навигация по тегам
                libxml_use_internal_errors(true);
                $doc = new DOMDocument();
                $doc->preserveWhiteSpace = false;
                $doc->loadHTML($htmlString);
                $xpath = new DOMXPath($doc);

                $query = "//body//text()[not(ancestor::script) and not(ancestor::style)]"; //не являющиеся потомками script и style

                $allElements = $xpath->query($query);

                //весь текст тегов
                $fullTextContent = "";

                foreach ($allElements as $el) {
                    $fullTextContent .= $el->textContent;
                }

                $fullTextContent = preg_replace('/\s+/', ' ', $fullTextContent); //убрать лишние отступы и нормализовать пробелы
                $fullTextContent = explode(" ", $fullTextContent);

                foreach ($fullTextContent as $word) {
                    if (
                        strlen($word) > 0
                        // && preg_match("/^(?=.*[A-Za-z])[\dA-Za-z\"'!@.,;:=_-]*$/", $word) //нужно что бы строка начиналась ли цифры или с англиской буквы а потом может содержать разные знаки разделители, стркоа обязательно должна иметь буквы
                        && preg_match("/^(?=.*[A-Za-z])[\dA-Za-z[:punct:]]*$/", $word) //нужно что бы строка начиналась ли цифры или с англиской буквы а потом может содержать разные спец символы(!"#$%&'()*+,-./:;<=>?@[\]^_\`{|}~), стркоа обязательно должна иметь буквы
                    ) {
                        // $extractedWords[$index][$urls[$index]][] = $word;
                        $extractedWords[$urls[$index]][] = $word;
                    }
                }
            },
            'rejected' => function (RequestException $ex, $index) use ($urls, $errorScrapingLogsFilePath, &$pageFailUrlsByScraping) {
                $url = $urls[$index];
                $message = "не удалось спарсить страницу: {$url} под номером {$index} , ошибка:{$ex?->getMessage()}. код:{$ex?->getCode()} в файле:{$ex?->getFile()} на строке: {$ex?->getLine()} \n";
                $pageFailUrlsByScraping[$url] = $message;

                file_put_contents($errorScrapingLogsFilePath, $message, FILE_APPEND);
            }
        ]);

        $pool->promise()->wait();

        // ksort($extractedWords);

        return $extractedWords;
    }
}
