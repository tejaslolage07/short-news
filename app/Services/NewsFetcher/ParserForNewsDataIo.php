<?php

namespace App\Services\NewsFetcher;

use Carbon\Carbon;

class ParserForNewsDataIo
{
    public function getParsedData(array $response): array
    {
        $articles = $response['results'];
        $parsedData = [];
        foreach($articles as $article) {
            $parsedArticle = $this->parseArticle($article);
            $parsedData[] = $parsedArticle;
        }
        return $parsedData;
    }

    public function getNextPage(string $response): string
    {
        $data = json_decode($response, true);
        return $data['nextPage'];
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

    private function formatDate(string $date): string
    {
        $formattedDate = new Carbon($date);
        return $formattedDate->addHours(9)->format('Y-m-d H:i:s');
    }
}
