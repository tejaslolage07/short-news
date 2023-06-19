<?php

namespace App\Services\NewsFetcher;

use DateTime;

class ParserForBing
{
    public function getParsedData(string $response): array
    {
        $articles = $this->getJsonData($response);
        $result = [];
        foreach($articles as $article) {
            $formattedDate = $this->formatDate($article['datePublished']);
            $imageURL = $this->getImageUrlFromData($article);
            $currentTime = date('Y-m-d H:i:s');
            array_push($result, $this->parseArticle($article, $imageURL, $formattedDate, $currentTime));
        }
        return $result;
    }

    private function parseArticle(array $article, string $imageURL, string $formattedDate, string $currentTime): array
    {
        return [
            'headline' => $article['name'],
            'url' => $article['url'],
            'author' => null,                                       // The Bing API doesn't send author data, only the source website.
            'content' => $article['description'],
            'imageURL' => $imageURL,
            'sourceWebsite' => $article['provider'][0]['name'],
            'publishedAt' => $formattedDate,
            'publishedAt' => $article['datePublished'],
            'fetchedAt' => $currentTime
        ];
    }

    private function getImageUrlFromData(array $article): ?string   // This is to ensure that errors are not caused when image is missing from the data.
    {
        if(isset($article['image']['thumbnail']['contentUrl'])) {
            return $article['image']['thumbnail']['contentUrl'];
        }
        return null;
    }

    private function formatDate(string $date): string
    {
        $formattedDate = new DateTime($date);
        return $formattedDate->format('Y-m-d H:i:s');
    }

    public function getJsonData(string $response): array
    {
        $data = json_decode($response, true);
        return $data['value'];
    }
}
