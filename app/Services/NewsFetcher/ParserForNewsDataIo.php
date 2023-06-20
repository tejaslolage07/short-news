<?php

namespace App\Services\NewsFetcher;

use DateTime;

class ParserForNewsDataIo
{
    public function getParsedData(string $response): array
    {
        $articles = $this->getJsonData($response);
        $parsedData = [];
        foreach($articles as $article) {
            $formattedDate = $this->formatDate($article['pubDate']);
            $currentTime = date('Y-m-d H:i:s');
            $parsedArticle = $this->parseArticle($article, $formattedDate, $currentTime);
            array_push($parsedData, $parsedArticle);
        }
        return $parsedData;
    }

    public function getNextPage(string $response): string
    {
        $data = json_decode($response, true);
        return $data['nextPage'];
    }

    public function getPublishedAt(string $response, int $newsIndex): string
    {
        $data = json_decode($response, true);
        $date = $data['results'][$newsIndex]['pubDate'];
        $parsedDate = $this->formatDate($date);
        return $parsedDate;
    }

    private function parseArticle(array $article, string $formattedDate, string $currentTime): array
    {
        return [
            'headline' => $article['title'],
            'url' => $article['link'],
            'author' => null,                   // The NewsDataIo API doesn't send author data, only the source website.
            'content' => $article['content'],
            'imageURL' => $article['image_url'],
            'sourceWebsite' => $article['creator'],
            'publishedAt' => $formattedDate,
            'fetchedAt' => $currentTime         // This is not the exact time the article was fetched, but rather the time when it was parsed. (Close enough to be acceptable)
        ];
    }

    private function getJsonData(string $response): array
    {
        $data = json_decode($response, true);
        return $data['results'];
    }

    private function formatDate(string $date): string
    {
        $formattedDate = new DateTime($date);
        return $formattedDate->format('Y-m-d H:i:s');
    }
}
