<?php

namespace App\Services\NewsFetcher;

use Carbon\Carbon;

class ParserForNewsDataIo
{
    public function getParsedData(array $response): array
    {
        $articles = $response['results'];
        $parsedData = [];
        foreach ($articles as $article) {
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
        $formattedDate = $this->checkIfExistsAndFormatDate($article['pubDate']);
        $currentTime = date('Y-m-d H:i:s');
        $newsWebsite = $this->getNewsWebsiteName($article);

        return [
            'headline' => $article['title'],
            'article_url' => $article['link'],
            // The NewsDataIo API doesn't send author data
            'author' => null,
            'content' => $article['content'],
            'image_url' => $article['image_url'],
            'news_website' => $newsWebsite,
            'published_at' => $formattedDate,
            'fetched_at' => $currentTime
        ];
    }

    private function getNewsWebsiteName(array $article): ?string
    {
        return $article['creator'][0] ?? null;
    }

    private function checkIfExistsAndFormatDate(?string $date): ?string
    {
        $date ? $formattedDate = (new Carbon($date))->addHours(9)->format('Y-m-d H:i:s') : $formattedDate = null;
        return $formattedDate;
    }
}
