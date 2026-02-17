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

        $query = "//body//text()[not(ancestor::script) and not(ancestor::style)]"; //не являющиеся потомками script и style

        $allElements = $xpath->query($query);

        $extractedWords = [];

        //весь текст тегов
        $fullTextContent = "";

        foreach ($allElements as $el) {
            $fullTextContent .= $el->textContent;
        }

        $fullTextContent = preg_replace('/\s\s+/', ' ', $fullTextContent); //убрать лишние отступы
        $fullTextContent = explode(" ", $fullTextContent);

        foreach ($fullTextContent as $word) {
            $word = trim($word); //удалить пробелы у слова
            $textContentOfNodeArr = explode(" ", $word);
            foreach ($textContentOfNodeArr as $val) {
                if (
                    strlen($val) > 0
                    && preg_match("/^(?=.*[A-Za-z])[\dA-Za-z!@.,;:=_-]*$/", $val) //нужно что бы строка начиналась ли цифры или с англиской буквы а потом может содержать разные знаки разделители, стркоа обязательно должна иметь буквы
                ) {
                    $extractedWords[] = $val;
                }
            }
        }

        // sort($extractedWords, SORT_STRING);

        return $extractedWords;
    }
}
