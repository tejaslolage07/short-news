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
            $parsedArticle = $this->parseArticle($article);
            $parsedData[] = $parsedArticle;
        }
        return $parsedData;
    }

    private function parseArticle(array $article): array
    {
        $formattedDate = $this->formatDate($article['pubDate']);
        $currentTime = date('Y-m-d H:i:s');
        $newsWebsite = $this->getNewsWebsiteName($article);
        return [
            'headline' => $article['title'],
            'article_url' => $article['link'],
            'author' => null,                   // The NewsDataIo API doesn't send author data, only the source website.
            'content' => $article['content'],
            'image_url' => $article['image_url'],
            'news_website' => $newsWebsite,
            'published_at' => $formattedDate,
            'fetched_at' => $currentTime         // This is not the exact time the article was fetched, but rather the time when it was parsed. (Close enough to be acceptable)
        ];
    }

    private function getNewsWebsiteName(array $article): ?string
    {
        if($article['creator'])
        return $article['creator'][0];
        return null;
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
