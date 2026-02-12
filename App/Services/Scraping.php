<?php

namespace App\Services;

use DOMDocument;
use DOMXPath;

class Scraping
{
    public function __construct(public \GuzzleHttp\Client $httpClient) {}

    public function procces($url): array
    {
        $httpClient = $this->httpClient;

        $response = $httpClient->get($url);
        $htmlString = (string) $response->getBody(); // содержит документ в виде строки

        //навигация по тегам
        $doc = new DOMDocument();
        $doc->preserveWhiteSpace = false;
        $doc->loadHTML($htmlString);
        $xpath = new DOMXPath($doc);

        $query = '//body/*';
        $allElements = $xpath->query($query);

        $extractedWords = [];

        // предложения
        foreach ($allElements as $key => $el) {
            // echo '<pre>'.htmlentities(print_r($el->nodeName, true)).'</pre>';exit();
            if (strpos($el->nodeName, 'script') !== false) {
                $el->parentElement->removeChild($el);
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
                // foreach ($textContentOfNodeArr as $val) {
                    if (
                        strlen($val) > 0
                        && preg_match("/^[A-Za-z]+/", $val)
                    ) {
                        // $word = preg_replace('/\s+/', "\n\r", $word);
                        // $word = trim($word, " \n\t\r\v");
                        $extractedWords[] = $val;
                    }
                }
                // if (strlen($word) > 0) {
                //     preg_match_all('/[A-Za-z]+/', $word, $matches);
                //     foreach ($matches as $match) {
                //         $extractedWords[] = $match;
                //     }
                // }
            }
        }

        $result = array_unique($extractedWords);

        return $result;
    }
}
