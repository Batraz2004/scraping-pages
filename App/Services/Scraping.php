<?php

namespace App\Services;

use DOMDocument;
use DOMXPath;
use Exception;
use Throwable;

class Scraping
{
    public function __construct(public \GuzzleHttp\Client $httpClient) {}

    public function procces($url): array
    {
        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            echo "не корректный url";
            throw new Exception("не корректный url", 422);
        }

        $httpClient = $this->httpClient;

        $response = $httpClient->get($url);
        $htmlString = (string) $response->getBody(); // содержит документ в виде строки

        //навигация по тегам
        libxml_use_internal_errors(true);
        $doc = new DOMDocument();
        $doc->preserveWhiteSpace = false;
        $doc->loadHTML($htmlString);
        $xpath = new DOMXPath($doc);

        $query = "//body/*";

        $allElements = $xpath->query($query);

        $extractedWords = [];

        // предложения

        foreach ($allElements as $el) {
            try {
                if (in_array($el->nodeName, ['script', 'style'])) {
                    continue;
                }

                //текст или предложение
                $textContentOfNode = $el->textContent;
                $textContentOfNode = preg_replace("/(^[\r\n]*|[\r\n]+)[\s\t]*[\r\n]+/", "", $textContentOfNode); //убрать пустые строки в тексте
                $textContentOfNode = preg_replace('/\s\s+/', ' ', $textContentOfNode); //убрать лишние отступы

                //по словам
                $textContentOfNodeArr = explode("\n\r", $textContentOfNode);

                foreach ($textContentOfNodeArr as $word) {
                    $word = trim($word); //удалить пробелы у слова
                    $textContentOfNodeArr = explode(" ", $word);
                    foreach ($textContentOfNodeArr as $val) {
                        if (
                            strlen($val) > 0
                            && preg_match("/^(?=.*[A-Za-z])[\dA-Za-z!@.,;:=_-]*$/", $val) //нужно что бы строка начиналась ли цифры или с англиской буквы а потом может содержать разные знаки разделители, стркоа обязательно должна иметь буквы
                        ) {
                            // $word = preg_replace('/\s+/', "\n\r", $word);
                            // $word = trim($word, " \n\t\r\v");
                            $extractedWords[] = $val;
                        }
                    }
                }
            } catch (Throwable $ex) {
                $errorScrapingLogsFilePath = 'App/Logs/Scraping.Log';
                $message = "не удалось спарсить элемент {$el?->nodeName} типа {$el?->nodeType} со значением {$el?->nodeValue} на странице: {$url}, ошибка:{$ex?->getMessage()}. код:{$ex?->getCode()} в файле:{$ex?->getFile()} на строке: {$ex?->getLine()} \n";
                file_put_contents($errorScrapingLogsFilePath, $message, FILE_APPEND);
            }
        }

        // sort($extractedWords, SORT_STRING);

        return $extractedWords;
    }
}
