<?php

namespace App\Services\NewsFetcher;

use Carbon\Carbon;

class ParserForBing
{
    public function getParsedData(string $response): array
    {
        $articles = $this->getJsonData($response);
        $result = [];
        foreach($articles as $article) {
            $parsedArticle = $this->parseArticle($article);
            $result[] = $parsedArticle;
        }
        return $result;
    }

    private function parseArticle(array $article): array
    {
        $formattedDate = $this->formatDate($article['datePublished']);
        $imageURL = $this->getImageUrlFromData($article);
        $currentTime = date('Y-m-d H:i:s');
        return [
            'headline' => $article['name'],
            'article_url' => $article['url'],
            'author' => null,                                       // The Bing API doesn't send author data, only the source website.
            'content' => $article['description'],
            'image_url' => $imageURL,
            'news_website' => $article['provider'][0]['name'],
            'published_at' => $formattedDate,
            'fetched_at' => $currentTime
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
        $formattedDate = new Carbon($date);
        return $formattedDate->addHours(9)->format('Y-m-d H:i:s');
    }

    private function getJsonData(string $response): array
    {
        $data = json_decode($response, true);
        return $data['value'];
    }
}
