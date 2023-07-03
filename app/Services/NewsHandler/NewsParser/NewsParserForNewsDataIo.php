<?php

namespace App\Services\NewsHandler\NewsParser;

use Carbon\Carbon;

class NewsParserForNewsDataIo
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
        $formattedDate = $article['pubDate'] ? $this->formatDate($article['pubDate']) : null;
        $currentTime = now()->format('Y-m-d H:i:s');
        $author = $this->getAuthor($article);

        return [
            'headline' => $article['title'],
            'article_url' => $article['link'],
            'author' => $author,
            'content' => $article['content'],
            'image_url' => $article['image_url'],
            'news_website' => $article['source_id'],
            'published_at' => $formattedDate,
            'fetched_at' => $currentTime,
        ];
    }

    private function getAuthor(array $article): ?string
    {
        return $article['creator'][0] ?? null;
    }

    private function formatDate(string $date): string
    {
        return Carbon::parse($date, 'UTC')->tz('Asia/Tokyo')->format('Y-m-d H:i:s');
    }
}
